<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartRequest;
use App\Models\Product;
use App\Models\Wishlist;
use App\Services\SalesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private SalesService $sales) {}

    public function index(Request $request): View
    {
        return view('cart.index', $this->sales->summary($this->sales->cart($request->user())));
    }

    public function add(CartRequest $request): \Illuminate\Http\JsonResponse|RedirectResponse
    {
        $this->sales->change($request->user(), 'add', $request->validated('product_id'), (int) $request->validated('quantity'));

        $wishlistCount = null;
        if ($request->boolean('remove_from_wishlist')) {
            $user = $request->user();
            if ($user) {
                $productCode = $request->input('product_code');
                if (! $productCode && $request->validated('product_id')) {
                    $product = Product::find($request->validated('product_id'));
                    $productCode = $product?->code;
                }
                if ($productCode) {
                    Wishlist::ofUser($user)->where('product_code', $productCode)->delete();
                }
                $wishlistCount = Wishlist::ofUser($user)->count();
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            $cart = $this->sales->cart($request->user());
            $cartCount = $cart && is_array($cart->items) ? array_sum(array_column($cart->items, 'quantity')) : 0;
            $data = [
                'success' => true,
                'message' => $request->boolean('remove_from_wishlist')
                    ? 'Đã thêm sản phẩm vào giỏ hàng và xóa khỏi yêu thích.'
                    : 'Đã thêm sản phẩm vào giỏ hàng thành công.',
                'cartCount' => $cartCount,
            ];
            if ($wishlistCount !== null) {
                $data['wishlistCount'] = $wishlistCount;
                $data['removedFromWishlist'] = true;
            }
            return response()->json($data);
        }

        if ($request->filled('buy_now')) {
            return to_route('checkout');
        }

        if ($request->boolean('remove_from_wishlist')) {
            return to_route('wishlist.index')->with('status', 'Đã thêm sản phẩm vào giỏ và xóa khỏi yêu thích.');
        }

        return to_route('cart.index')->with('status', 'Đã thêm sản phẩm vào giỏ.');
    }

    public function update(CartRequest $request, string $code): \Illuminate\Http\JsonResponse|RedirectResponse
    {
        $this->sales->change($request->user(), 'update', $code, (int) $request->validated('quantity'));

        if ($request->wantsJson() || $request->ajax()) {
            $cart = $this->sales->cart($request->user());
            $summary = $this->sales->summary($cart);
            $line = collect($summary['lines'])->firstWhere('code', $code);
            return response()->json([
                'success' => true,
                'itemTotalPrice' => number_format($line['subtotal'] ?? 0, 0, ',', '.') . ' đ',
                'cartSubtotal' => number_format($summary['total'] ?? 0, 0, ',', '.') . ' đ',
                'cartCount' => array_sum(array_column($summary['lines'], 'quantity')),
            ]);
        }

        return to_route('cart.index')->with('status', 'Đã cập nhật giỏ hàng.');
    }

    public function remove(Request $request, ?string $code = null): \Illuminate\Http\JsonResponse|RedirectResponse
    {
        $productCode = $code ?: $request->input('code') ?: $request->input('MaSP');
        if (! $productCode) {
            abort(404);
        }

        $this->sales->change($request->user(), 'remove', $productCode);

        if ($request->wantsJson() || $request->ajax()) {
            $cart = $this->sales->cart($request->user());
            $summary = $this->sales->summary($cart);
            return response()->json([
                'success' => true,
                'cartSubtotal' => number_format($summary['total'] ?? 0, 0, ',', '.') . ' đ',
                'cartCount' => array_sum(array_column($summary['lines'], 'quantity')),
                'isEmpty' => empty($summary['lines']),
            ]);
        }

        return to_route('cart.index')->with('status', 'Đã xóa sản phẩm.');
    }

    public function clear(Request $request): \Illuminate\Http\JsonResponse|RedirectResponse
    {
        $this->sales->change($request->user(), 'clear');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'cartSubtotal' => '0 đ',
                'cartCount' => 0,
                'isEmpty' => true,
            ]);
        }

        return to_route('cart.index')->with('status', 'Đã làm trống giỏ hàng.');
    }
}
