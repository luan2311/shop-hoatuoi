<?php

use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/items/{code}', [CartController::class, 'update'])->name('cart.update');
    Route::match(['get', 'delete'], '/cart/items/{code}', [CartController::class, 'remove'])->name('cart.remove');
    Route::match(['get', 'delete'], '/cart/remove/{code}', [CartController::class, 'remove']);
    Route::match(['get', 'delete'], '/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
    
    // Tương thích các URL từ codebase C# ShopFlower
    Route::match(['get', 'post'], '/Cart/ThemGioHang', function (\Illuminate\Http\Request $request, CartController $controller) {
        $id = $request->input('ms') ?: $request->input('productId') ?: $request->input('product_id');
        $qty = (int) ($request->input('txtSoLuong') ?: $request->input('quantity') ?: 1);
        $request->merge(['product_id' => $id, 'quantity' => $qty]);
        return app()->call([$controller, 'add']);
    });
    Route::match(['get', 'post'], '/Cart/XoaGioHang', [CartController::class, 'remove']);
    Route::match(['get', 'post'], '/Cart/XoaGioHang_All', [CartController::class, 'clear']);
    Route::match(['get', 'post'], '/Cart/CapNhatGioHang', function (\Illuminate\Http\Request $request, CartController $controller) {
        $code = $request->input('MaSP') ?: $request->input('code');
        $qty = (int) ($request->input('txtSoLuong') ?: $request->input('quantity') ?: 1);
        $request->merge(['quantity' => $qty]);
        return app()->call([$controller, 'update'], ['code' => $code]);
    });
    Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout');
    Route::post('/checkout', [OrderController::class, 'store'])->name('checkout.store');
    Route::get('/orders/success', [OrderController::class, 'success'])->name('orders.success');
    Route::get('/account/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/account/orders/{order}', [OrderController::class, 'show'])
        ->where('order', '[a-fA-F0-9]{24}')->name('orders.show');
});
