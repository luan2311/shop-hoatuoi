<?php

use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::group([], function () {
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');
    Route::get('/api/products/search', [ProductController::class, 'searchAjax'])->name('products.search');
    Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:Admin'])->group(function () {
        Route::resource('products', AdminProductController::class)->except('show');
    });
});
