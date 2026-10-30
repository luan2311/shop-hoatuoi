<?php

use App\Http\Controllers\WishlistController;
use App\Models\Product;
use App\Models\User;
use App\Services\ProductLifecycle;
use App\Services\SalesService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
[$script, $action, $userId, $productId, $startAt] = $argv;
$remaining = (float) $startAt - microtime(true);
if ($remaining > 0) {
    usleep((int) ($remaining * 1000000));
}
try {
    $product = Product::findOrFail($productId);
    $user = User::findOrFail($userId);
    if ($action === 'delete') {
        app(ProductLifecycle::class)->delete($product);
    } elseif ($action === 'wishlist') {
        $request = Request::create('/wishlist/toggle', 'POST', ['product_code' => $product->code]);
        $request->setUserResolver(fn () => $user);
        app(WishlistController::class)->toggle($request);
    } elseif ($action === 'stop') {
        Product::whereKey($productId)->update(['is_selling' => false]);
    } elseif ($action === 'checkout') {
        app(SalesService::class)->checkout($user, [
            'checkout_token' => (string) Str::uuid(), 'receiver_name' => 'AN12 QA',
            'receiver_phone' => '0901234567', 'receiver_address' => 'QA address', 'payment_method' => 'COD',
        ]);
    } else {
        app(SalesService::class)->change($user, 'add', $productId);
    }
    echo 'success';
} catch (ValidationException|\Illuminate\Database\Eloquent\ModelNotFoundException|HttpException $exception) {
    echo 'rejected';
}
