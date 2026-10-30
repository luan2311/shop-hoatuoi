<?php

use App\Models\User;
use App\Services\SalesService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

// Real independent PHP process used by SalesConcurrencyTest.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

[$script, $action, $userId, $productId, $startAt] = $argv;
$remaining = (float) $startAt - microtime(true);
if ($remaining > 0) {
    usleep((int) ($remaining * 1000000));
}

$sales = $app->make(SalesService::class);
$user = User::findOrFail($userId);
try {
    if ($action === 'add') {
        $sales->change($user, 'add', $productId, 1);
    } elseif ($action === 'complete') {
        $sales->transition($productId, 'Completed');
    } elseif ($action === 'cancel') {
        $sales->transition($productId, 'Cancelled');
    } else {
        $sales->checkout($user, [
            'checkout_token' => $argv[5] ?? (string) Str::uuid(),
            'receiver_name' => 'Concurrent buyer',
            'receiver_phone' => '0901234567',
            'receiver_address' => 'Test address',
            'payment_method' => 'COD',
        ]);
    }
    echo 'success';
} catch (ValidationException) {
    echo 'rejected';
}
