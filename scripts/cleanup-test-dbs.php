<?php
require __DIR__ . '/../vendor/autoload.php';

function cleanupDbs(string $uri, string $label, bool $drop = false) {
    echo "=== $label ($uri) ===\n";
    try {
        $client = new MongoDB\Client($uri);
        $testDbs = [];
        foreach ($client->listDatabases() as $dbInfo) {
            $name = $dbInfo->getName();
            if (str_starts_with($name, 'shop_hoatuoi_test')) {
                $testDbs[] = $name;
                if ($drop) {
                    $client->dropDatabase($name);
                    echo " - Dropped test DB: $name\n";
                }
            } else {
                echo " - " . $name . " (" . round($dbInfo->getSizeOnDisk() / 1024, 1) . " KB)\n";
            }
        }
        if (empty($testDbs)) {
            echo " (Không có database test nào tồn đọng)\n";
        }
    } catch (Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

$drop = in_array('--drop', $argv, true);
cleanupDbs('mongodb://127.0.0.1:27017', 'Port 27017', $drop);
cleanupDbs('mongodb://127.0.0.1:27018/?directConnection=true', 'Port 27018', $drop);
