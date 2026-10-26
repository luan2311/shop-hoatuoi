<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

try {
    if (! extension_loaded('mongodb') || config('database.default') !== 'mongodb') {
        throw new RuntimeException('MongoDB extension and default connection are required.');
    }
    $db = DB::connection('mongodb')->getDatabase();
    $hello = $db->command(['hello' => 1])->toArray()[0];
    if (! isset($hello->setName) && ($hello->msg ?? '') !== 'isdbgrid') {
        throw new RuntimeException('Checkout requires a replica set or sharded cluster; standalone is unsupported.');
    }
    $indexes = [];
    foreach ($db->selectCollection('orders')->listIndexes() as $index) {
        $indexes[$index->getName()] = $index;
    }
    if (! isset($indexes['checkout_key_1']) || ! $indexes['checkout_key_1']->isUnique()) {
        throw new RuntimeException('Apply the additive database/migrations/mongodb migrations before checkout.');
    }
    echo json_encode([
        'result' => 'PASS',
        'database' => $db->getDatabaseName(),
        'replica_set' => $hello->setName ?? 'sharded',
        'checkout_index' => 'unique sparse',
        'php_mongodb' => phpversion('mongodb'),
    ], JSON_UNESCAPED_SLASHES).PHP_EOL;
} catch (Throwable $exception) {
    // Connection exceptions may include credentials. Only expose our own diagnostics.
    $message = $exception::class === RuntimeException::class
        ? $exception->getMessage() : 'MongoDB preflight failed. Check the local environment and server availability.';
    fwrite(STDERR, $message.PHP_EOL);
    exit(1);
}
