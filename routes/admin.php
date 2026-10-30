<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ContactController as AdminContactController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:Admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/revenue-data', [DashboardController::class, 'getRevenueData'])->name('dashboard.revenue');

    // Products
    Route::resource('products', AdminProductController::class)->except('show');
    Route::post('/products/{product}/stop-selling', [AdminProductController::class, 'stopSelling'])->name('products.stop-selling');
    Route::post('/products/{product}/resume-selling', [AdminProductController::class, 'resumeSelling'])->name('products.resume-selling');

    // Orders
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
    Route::post('/orders/{order}/complete', [AdminOrderController::class, 'markCompleted'])->name('orders.complete');

    // Users
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::post('/users/{user}/toggle-active', [AdminUserController::class, 'toggleActive'])->name('users.toggle-active');

    // Posts (Tin tức)
    Route::resource('posts', AdminPostController::class);

    // Contacts (Liên hệ)
    Route::resource('contacts', AdminContactController::class)->only(['index', 'show', 'destroy']);

    // Categories (Danh mục)
    Route::resource('categories', CategoryController::class);

    // Friendly C# ASP.NET MVC 5 URL Compatibility Aliases
    Route::get('/Dashboard/Dashboard', fn () => redirect()->route('admin.dashboard'));
    Route::get('/Dashboard/QL_SanPham', fn (Request $request) => redirect()->route('admin.products.index', $request->query()));
    Route::get('/Dashboard/CreateSanPham', fn () => redirect()->route('admin.products.create'));
    Route::get('/Dashboard/EditSanPham/{id}', fn (string $id) => redirect()->route('admin.products.edit', $id));
    Route::get('/Dashboard/QL_HoaDon', fn (Request $request) => redirect()->route('admin.orders.index', $request->query()));
    Route::get('/Dashboard/DetailsHoaDon/{id}', fn (string $id) => redirect()->route('admin.orders.show', $id));
    Route::get('/Dashboard/QL_TaiKhoan', fn (Request $request) => redirect()->route('admin.users.index', $request->query()));
    Route::get('/Dashboard/Create_Account', fn () => redirect()->route('admin.users.create'));
    Route::get('/Dashboard/QL_TinTuc', fn (Request $request) => redirect()->route('admin.posts.index', $request->query()));
    Route::get('/Dashboard/CreateTinTuc', fn () => redirect()->route('admin.posts.create'));
    Route::get('/Dashboard/EditTinTuc/{id}', fn (string $id) => redirect()->route('admin.posts.edit', $id));
    Route::get('/Dashboard/QL_LienHe', fn (Request $request) => redirect()->route('admin.contacts.index', $request->query()));
    Route::get('/Dashboard/DetailsLienHe/{id}', fn (string $id) => redirect()->route('admin.contacts.show', $id));
});
