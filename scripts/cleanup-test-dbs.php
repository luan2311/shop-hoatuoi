<?php

use MongoDB\Client;

require __DIR__.'/../vendor/autoload.php';

// Inventory only: a name prefix does not prove ownership of an existing database.
// The test helper may clean up only databases it created in the current test.
function cleanupDbs(string $uri, string $label)
{
    echo "=== $label ($uri) ===\n";
    try {
        $client = new Client($uri);
        $testDbs = [];
        foreach ($client->listDatabases() as $dbInfo) {
            $name = $dbInfo->getName();
            if (str_starts_with($name, 'shop_hoatuoi_test')) {
                $testDbs[] = $name;
                echo ' - '.$name." (retained; ownership not established)\n";
            } else {
                echo ' - '.$name.' ('.round($dbInfo->getSizeOnDisk() / 1024, 1)." KB)\n";
            }
        }
        if (empty($testDbs)) {
            echo " (Không có database test nào tồn đọng)\n";
        }
    } catch (Throwable $e) {
        echo 'Error: '.$e->getMessage()."\n";
    }
    echo "\n";
}

if (in_array('--drop', $argv, true)) {
    fwrite(STDERR, "Bulk deletion refused: only the creating test may clean up its owned database.\n");
    exit(1);
}

cleanupDbs('mongodb://127.0.0.1:27017', 'Port 27017');
cleanupDbs('mongodb://127.0.0.1:27018/?directConnection=true', 'Port 27018');
