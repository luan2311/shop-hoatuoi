<?php

use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group([], function () {
    // Web endpoints cho sản phẩm
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');

    // Friendly alias URLs tương thích với codebase C# Shop-Flower (tat_ca_san_pham, bo_hoa_tuoi, ...)
    Route::get('/tat-ca-san-pham', function (Request $request) {
        return redirect()->route('products.index', $request->query());
    });
    Route::get('/bo-hoa-tuoi', function (Request $request) {
        return redirect()->route('products.index', array_merge($request->query(), ['category_slug' => 'bo-hoa-tuoi']));
    });
    Route::get('/ke-hoa-chuc-mung', function (Request $request) {
        return redirect()->route('products.index', array_merge($request->query(), ['category_slug' => 'ke-chuc-mung']));
    });
    Route::get('/gio-hoa-chuc-mung', function (Request $request) {
        return redirect()->route('products.index', array_merge($request->query(), ['category_slug' => 'gio-hoa-chuc-mung']));
    });
    Route::get('/hoa-cuoi', function (Request $request) {
        return redirect()->route('products.index', array_merge($request->query(), ['category_slug' => 'hoa-cuoi']));
    });
    Route::get('/hoa-sap', function (Request $request) {
        return redirect()->route('products.index', array_merge($request->query(), ['category_slug' => 'hoa-sap']));
    });
    Route::get('/tim-kiem', function (Request $request) {
        return redirect()->route('products.index', $request->query());
    })->name('products.search_page');

    // Route C# fallback: SanPham/chi_tiet_san_pham?id=... hoặc SanPham/chi_tiet_san_pham/{id}
    Route::get('/SanPham/chi_tiet_san_pham/{id?}', function (Request $request, ?string $id = null) {
        $target = $id ?: $request->query('id');
        return $target ? redirect()->route('products.show', $target) : redirect()->route('products.index');
    });

    // API endpoints (tương thích AJAX & RESTful Web API từ C# APIController/SanPhamController)
    Route::get('/api/products/search', [ProductController::class, 'searchAjax'])->name('products.search');
    Route::get('/api/products/{id}/info', [ProductController::class, 'getProductInfo'])->name('products.info');
    Route::get('/api/products', [ProductController::class, 'apiIndex'])->name('products.api.index');
    Route::get('/api/products/{id}', [ProductController::class, 'apiShow'])->name('products.api.show');

    // Admin endpoints
    Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:Admin'])->group(function () {
        Route::resource('products', AdminProductController::class)->except('show');
    });
});
