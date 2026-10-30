<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Regex;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class ProductLifecycle
{
    public const ORDER_MESSAGE = 'Không thể xóa sản phẩm vì đã phát sinh đơn hàng. Bạn có thể chọn Ngừng bán để dừng kinh doanh sản phẩm này.';

    // Must be called inside the same MongoDB transaction as a new reference.
    // All reference writers contend with deletion on the product document.
    public function lock(Product $product): void
    {
        if (! DB::connection('mongodb')->getSession()?->isInTransaction()) {
            throw new \LogicException('Product reference lock requires an active MongoDB transaction.');
        }
        if (Product::whereKey($product->id)->increment('lifecycle_revision') !== 1) {
            throw ValidationException::withMessages(['product' => 'Sản phẩm không còn tồn tại.']);
        }
    }

    public function deletionReason(Product $product): ?string
    {
        if (config('database.default') !== 'mongodb') {
            return 'Chưa xác minh được kho lưu trữ tham chiếu legacy. Không thể xóa sản phẩm.';
        }
        $connection = DB::connection('mongodb');
        $db = $connection->getDatabase();
        // Raw driver reads must explicitly share the Laravel transaction session.
        $options = $connection->getSession() ? ['session' => $connection->getSession()] : [];
        foreach (['orders', 'order_details'] as $name) {
            foreach ($db->selectCollection($name)->find([], $options) as $document) {
                if ($name === 'orders' && (isset($document['product_code']) || isset($document['product_id']))) {
                    $match = $this->reference($document, $product);
                    if ($match !== false) {
                        return self::ORDER_MESSAGE;
                    }
                }
                $rows = $name === 'orders' ? ($document['items'] ?? null) : [$document];
                if (! is_iterable($rows)) {
                    return 'Chưa xác minh được tham chiếu đơn hàng. Không thể xóa sản phẩm.';
                }
                foreach ($rows as $row) {
                    $match = $this->reference($row, $product);
                    if ($match === true) {
                        return self::ORDER_MESSAGE;
                    }
                    if ($match === null) {
                        return 'Chưa xác minh được định dạng tham chiếu đơn hàng. Không thể xóa sản phẩm.';
                    }
                }
            }
        }
        foreach (['carts', 'cart_items', 'wishlists'] as $name) {
            foreach ($db->selectCollection($name)->find([], $options) as $document) {
                $rows = $name === 'carts' ? ($document['items'] ?? null) : [$document];
                if (! is_iterable($rows)) {
                    return 'Chưa xác minh được dữ liệu giỏ hàng. Không thể xóa sản phẩm.';
                }
                foreach ($rows as $row) {
                    $match = $this->reference($row, $product);
                    if ($match !== false) {
                        return 'Không thể xóa: sản phẩm còn tham chiếu Cart/Wishlist hoặc tham chiếu chưa xác minh. Hãy chọn Ngừng bán.';
                    }
                }
            }
        }

        return null;
    }

    private function reference(mixed $row, Product $product): ?bool
    {
        if (! is_array($row) && ! $row instanceof \ArrayAccess) {
            return null;
        }
        $known = false;
        $unresolved = false;
        foreach (['product_code', 'code', 'MaSP', 'product_id', 'productId'] as $field) {
            if (! isset($row[$field])) {
                continue;
            }
            $value = $row[$field];
            if (! is_string($value) && ! $value instanceof ObjectId) {
                return null;
            }
            if ((string) $value === '') {
                return null;
            }
            $known = true;
            if (strcasecmp((string) $value, (string) $product->code) === 0
                || strcasecmp((string) $value, (string) $product->id) === 0) {
                return true;
            }
            // An orphan legacy code/id may belong to a previously renamed product.
            // Do not treat a syntactically valid but unresolved identity as proof of safety.
            $query = Product::where('code', 'regex', new Regex('^'.preg_quote((string) $value).'$', 'i'));
            if (preg_match('/^[a-f0-9]{24}$/i', (string) $value)) {
                $query->orWhere('_id', new ObjectId((string) $value));
            }
            $unresolved = $unresolved || ! $query->exists();
        }

        return $known && ! $unresolved ? false : null;
    }

    public function delete(Product $product): void
    {
        try {
            DB::connection('mongodb')->transaction(function () use ($product): void {
                $current = Product::findOrFail($product->id);
                abort_if($current->image !== $product->image
                    || $current->updated_at != $product->updated_at, 409, 'Sản phẩm đã thay đổi. Vui lòng tải lại trang.');
                $this->lock($current);
                if ($reason = $this->deletionReason($current)) {
                    throw ValidationException::withMessages(['product' => $reason]);
                }
                $current->delete();
            }, attempts: 5);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (HttpException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);
            throw ValidationException::withMessages(['product' => 'Chưa xác minh được điều kiện xóa an toàn. Vui lòng thử lại khi MongoDB replica set sẵn sàng.']);
        }
    }
}
