<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use MongoDB\BSON\ObjectId;

class SalesService
{
    // The supplied initializer uses ObjectId; earlier documents may use strings.
    public function owned(Builder $query, User $user): Builder
    {
        return $query->whereIn('user_id', [(string) $user->id, new ObjectId($user->id)]);
    }

    public function cart(User $user): ?Cart
    {
        return $this->owned(Cart::query(), $user)->first();
    }

    public function summary(?Cart $cart): array
    {
        $items = $cart?->items ?? [];
        $codes = array_column($items, 'product_code');
        $products = Product::whereIn('code', $codes)->get();
        $productsMap = [];
        foreach ($products as $p) {
            $productsMap[$p->code] = $p;
            $productsMap[strtolower($p->code)] = $p;
        }

        $lines = [];
        foreach ($items as $item) {
            $code = $item['product_code'];
            $product = $productsMap[$code] ?? $productsMap[strtolower($code)] ?? Product::where('code', 'regex', '/^'.preg_quote($code, '/').'$/i')->first();
            $price = $product ? $this->price($product) : null;
            $lines[] = [
                'code' => $item['product_code'],
                'product' => $product,
                'quantity' => $item['quantity'],
                'unit_price' => $price,
                'subtotal' => $price !== null ? $price * $item['quantity'] : 0,
            ];
        }

        return ['lines' => $lines, 'total' => array_sum(array_column($lines, 'subtotal'))];
    }

    public function change(User $user, string $action, ?string $key = null, int $quantity = 1): void
    {
        // Create outside the transaction so a concurrent first add uses the unique owner index.
        $cart = $this->cart($user) ?? Cart::firstOrCreate(['user_id' => new ObjectId($user->id)], ['items' => []]);

        $this->runTransaction(function () use ($cart, $action, $key, $quantity): void {
            $cart = Cart::findOrFail($cart->id);
            $items = collect($cart->items ?? [])->keyBy('product_code');
            if ($action === 'clear') {
                $items = collect();
            } elseif ($action === 'remove') {
                $matchedKey = $items->keys()->first(fn ($k) => strcasecmp((string) $k, (string) $key) === 0);
                abort_unless($matchedKey !== null, 404);
                $items->forget($matchedKey);
            } else {
                $product = $action === 'add'
                    ? Product::findOrFail($key)
                    : (Product::where('code', $key)->first() ?? Product::where('code', 'regex', '/^'.preg_quote($key, '/').'$/i')->firstOrFail());
                if ($action === 'update') {
                    $matchedKey = $items->keys()->first(fn ($k) => strcasecmp((string) $k, (string) $product->code) === 0);
                    abort_unless($matchedKey !== null, 404);
                }
                $amount = $quantity + ($action === 'add' ? ($items->get($product->code)['quantity'] ?? 0) : 0);
                $this->available($product, $amount);
                $items->put($product->code, ['product_code' => $product->code, 'quantity' => $amount]);
            }
            $cart->items = $items->values()->all();
            $cart->save();
        });
    }

    public function checkout(User $user, array $data): Order
    {
        return $this->runTransaction(function () use ($user, $data): Order {
            $key = hash('sha256', $user->id.':'.$data['checkout_token']);
            $previous = $this->owned(Order::query(), $user)->where('checkout_key', $key)->first();
            if ($previous) {
                // A retried form must never purchase items added after the original checkout.
                return $previous;
            }

            $cart = $this->cart($user);
            if (! $cart || empty($cart->items)) {
                throw ValidationException::withMessages(['cart' => 'Giỏ hàng đang trống.']);
            }

            $snapshots = [];
            foreach ($cart->items as $item) {
                $product = Product::where('code', $item['product_code'])->first();
                if (! $product) {
                    throw ValidationException::withMessages(['cart' => 'Sản phẩm trong giỏ không còn tồn tại.']);
                }
                $quantity = $item['quantity'];
                $this->available($product, $quantity);
                $price = $this->price($product);
                // Conditional decrement also protects stock when two customers buy together.
                $changed = Product::whereKey($product->id)->where('stock', '>=', $quantity)
                    ->where('status', 'Còn hàng')->decrement('stock', $quantity);
                if ($changed !== 1) {
                    throw ValidationException::withMessages(['cart' => 'Tồn kho vừa thay đổi. Vui lòng thử lại.']);
                }
                $snapshots[] = [
                    'product_code' => $product->code,
                    'product_name' => $product->name,
                    'product_slug' => $product->slug,
                    'image' => $product->image,
                    'price' => $price,
                    'quantity' => $quantity,
                    'subtotal' => $price * $quantity,
                ];
            }

            $order = Order::create([
                'order_code' => 'HD-'.Str::ulid(),
                'checkout_key' => $key,
                'user_id' => new ObjectId($user->id),
                'customer' => [
                    'name' => $data['receiver_name'],
                    'phone' => $data['receiver_phone'],
                    'address' => $data['receiver_address'],
                    'email' => $data['email'] ?? $user->email,
                    'note' => $data['note'] ?? null,
                ],
                'items' => $snapshots,
                'total_amount' => array_sum(array_column($snapshots, 'subtotal')),
                'payment_method' => $data['payment_method'],
                'status' => 'Pending',
                'order_date' => now(),
            ]);
            // Updating the same document makes concurrent checkout/cart edits conflict and retry.
            $cart->items = [];
            $cart->save();

            return $order;
        });
    }

    public function transition(string $id, string $status): void
    {
        $this->runTransaction(function () use ($id, $status): void {
            $order = Order::findOrFail($id);
            $this->applyTransition($order, $status);
        });
    }

    public function cancelCustomer(User $user, string $id, ?string $reason = null): void
    {
        $this->runTransaction(function () use ($user, $id, $reason): void {
            $order = $this->owned(Order::query(), $user)->findOrFail($id);
            if (! $order->canCustomerCancel()) {
                throw ValidationException::withMessages(['cancel' => 'Không thể tự hủy: đơn không còn Pending COD chưa thanh toán. Vui lòng liên hệ Admin.']);
            }
            $order->cancelled_at = now();
            $order->cancelled_by = ['type' => 'Customer', 'user_id' => new ObjectId($user->id)];
            $order->cancel_reason = $reason;
            $this->applyTransition($order, 'Cancelled');
        });
    }

    private function applyTransition(Order $order, string $status): void
    {
        if (! in_array($status, Order::TRANSITIONS[$order->status] ?? [], true)) {
            throw ValidationException::withMessages(['status' => 'Không thể chuyển sang trạng thái này.']);
        }
        if ($status === 'Cancelled') {
            foreach ($order->items as $item) {
                if (! is_int($item['quantity']) || $item['quantity'] < 1) {
                    throw ValidationException::withMessages(['status' => 'Số lượng hoàn kho không hợp lệ.']);
                }
                // Never restore stock to a different or missing product silently.
                $changed = Product::where('code', $item['product_code'])->increment('stock', $item['quantity']);
                if ($changed !== 1) {
                    throw ValidationException::withMessages(['status' => 'Không thể hoàn tồn kho: sản phẩm không còn tồn tại.']);
                }
            }
        }
        $order->status = $status;
        $order->save();
    }

    protected function runTransaction(callable $callback)
    {
        // Every stock/order write must stay inside the MongoDB transaction.
        return DB::connection('mongodb')->transaction($callback, attempts: 5);
    }

    private function available(Product $product, mixed $quantity): void
    {
        if (! is_int($quantity) || $quantity < 1 || $quantity > 999
            || $product->status !== 'Còn hàng' || $quantity > ($product->stock ?? 0)) {
            throw ValidationException::withMessages(['quantity' => 'Số lượng không hợp lệ hoặc vượt tồn kho.']);
        }
    }

    private function price(Product $product): int
    {
        $price = (string) $product->price;
        if (! is_numeric($price) || (float) $price < 0 || (float) $price > 1000000000
            || floor((float) $price) != (float) $price) {
            throw ValidationException::withMessages(['cart' => 'Giá sản phẩm không hợp lệ. Vui lòng liên hệ cửa hàng.']);
        }

        return (int) $price;
    }
}
