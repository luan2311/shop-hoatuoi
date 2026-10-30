<?php

use App\Models\User;
use App\Services\SalesService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Validation\ValidationException;

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
[$script,$action,$uid,$oid,$start] = $argv;
$remaining = (float) $start - microtime(true);
if ($remaining > 0) {
    usleep((int) ($remaining * 1000000));
}
try {
    $sales = app(SalesService::class);
    if ($action === 'customer') {
        $sales->cancelCustomer(User::findOrFail($uid), $oid);
    } else {
        $sales->transition($oid, 'Processing');
    }
    echo 'success';
} catch (ValidationException) {
    echo 'rejected';
}
