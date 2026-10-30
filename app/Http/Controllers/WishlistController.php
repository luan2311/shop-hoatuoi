<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use App\Services\ProductLifecycle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $wishlists = Wishlist::ofUser($request->user())
            ->with('product')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('wishlist.index', compact('wishlists'));
    }

    // Thêm/xóa sản phẩm khỏi wishlist, gọi bằng AJAX (Fetch API)
    public function toggle(Request $request)
    {
        $data = $request->validate([
            'product_code' => ['required', 'string', 'max:50'],
        ]);

        $user = $request->user();
        $status = DB::connection('mongodb')->transaction(function () use ($user, $data) {
            $product = Product::where('code', $data['product_code'])->first();
            if (! $product) {
                throw ValidationException::withMessages(['product_code' => 'Sản phẩm không tồn tại.']);
            }
            app(ProductLifecycle::class)->lock($product);
            $item = Wishlist::ofUser($user)->where('product_code', $data['product_code'])->first();

            if ($item) {
                $item->delete();
                $status = 'removed';
            } else {
                Wishlist::create(['user_id' => $user->getAuthIdentifier(), 'product_code' => $data['product_code']]);
                $status = 'added';
            }

            return $status;
        }, attempts: 5);

        return response()->json([
            'status' => $status,
            'count' => Wishlist::ofUser($user)->count(),
        ]);
    }
}
