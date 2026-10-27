<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\DB;
use RuntimeException;

trait UsesMongoDatabase
{
    protected function setUpUsesMongoDatabase(): void
    {
        $this->initializeMongoDatabase();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->initializeMongoDatabase();
    }

    private function initializeMongoDatabase(): void
    {
        static $initialized = [];
        $key = spl_object_hash($this);
        if (isset($initialized[$key])) {
            return;
        }
        $initialized[$key] = true;

        $database = config('database.connections.mongodb.database');
        if (! app()->environment('testing') || ! str_starts_with($database, 'shop_hoatuoi_test')) {
            throw new RuntimeException('Tests require an isolated shop_hoatuoi_test* database.');
        }

        // Each test gets a new database. Retain it for audit; never drop a database.
        $testDb = $database.'_'.getmypid().'_'.bin2hex(random_bytes(4));
        config(['database.connections.mongodb.database' => $testDb]);
        DB::purge('mongodb');
        $connection = DB::connection('mongodb');

        $fixturePath = base_path('tests/Fixtures/mongodb-validators.json');
        if (file_exists($fixturePath)) {
            $schemas = json_decode(file_get_contents($fixturePath), true, flags: JSON_THROW_ON_ERROR);
            foreach ($schemas as $collection => $options) {
                try {
                    $connection->getDatabase()->createCollection($collection, $options);
                } catch (\Throwable $e) {
                }
            }
        }

        $this->artisan('migrate', ['--path' => 'database/migrations/mongodb', '--force' => true])->assertExitCode(0);
        $this->withoutVite();

        $db = $connection->getDatabase();
        try {
            $db->selectCollection('wishlists')->createIndex(['user_id' => 1, 'product_code' => 1], ['unique' => true]);
            $db->selectCollection('contacts')->createIndex(['code' => 1], ['unique' => true, 'partialFilterExpression' => ['code' => ['$type' => 'string']]]);
        } catch (\Throwable $e) {
        }
    }
}
