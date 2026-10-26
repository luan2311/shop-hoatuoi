<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\DB;
use RuntimeException;

trait UsesMongoDatabase
{
    protected function setUp(): void
    {
        parent::setUp();
        $database = config('database.connections.mongodb.database');
        if (! app()->environment('testing') || ! str_starts_with($database, 'shop_hoatuoi_test_')) {
            throw new RuntimeException('Tests require an isolated shop_hoatuoi_test_* database.');
        }
        // Each test gets a new database. Retain it for audit; never drop a database.
        $database .= '_'.getmypid().'_'.bin2hex(random_bytes(4));
        config(['database.connections.mongodb.database' => $database]);
        DB::purge('mongodb');
        $connection = DB::connection('mongodb');
        $schemas = json_decode(file_get_contents(base_path('tests/Fixtures/mongodb-validators.json')), true, flags: JSON_THROW_ON_ERROR);
        foreach ($schemas as $collection => $options) {
            $connection->getDatabase()->createCollection($collection, $options);
        }
        $this->artisan('migrate', ['--path' => 'database/migrations/mongodb', '--force' => true])->assertExitCode(0);
        $this->withoutVite();
    }
}
