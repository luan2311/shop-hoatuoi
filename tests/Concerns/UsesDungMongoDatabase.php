<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

trait UsesDungMongoDatabase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! extension_loaded('mongodb')) {
            $this->markTestSkipped('BLOCKED: PHP chưa bật extension MongoDB.');
        }
        $configured = config('database.connections.mongodb.database');
        if (! app()->environment('testing') || config('database.default') !== 'mongodb'
            || ! is_string($configured) || ! str_starts_with($configured, 'shop_hoatuoi_test_')) {
            throw new RuntimeException('QA chỉ chạy trong APP_ENV=testing và database shop_hoatuoi_test_*.');
        }
        $database = 'shop_hoatuoi_test_dung_'.getmypid().'_'.bin2hex(random_bytes(6));
        config(['database.connections.mongodb.database' => $database]);
        DB::purge('mongodb');

        try {
            $db = DB::connection('mongodb')->getDatabase();
            $hello = $db->command(['hello' => 1])->toArray()[0];
        } catch (Throwable) {
            $this->markTestSkipped('BLOCKED: không kết nối được MongoDB test; không dùng SQL thay thế.');
        }
        if (! isset($hello->setName) && ($hello->msg ?? '') !== 'isdbgrid') {
            $this->markTestSkipped('BLOCKED: QA nghiệp vụ cần replica set hoặc sharded cluster.');
        }
        $client = DB::connection('mongodb')->getMongoClient();
        if (in_array($database, iterator_to_array($client->listDatabaseNames()), true)) {
            throw new RuntimeException('Refusing to use a pre-existing QA test database.');
        }
        $ownerToken = bin2hex(random_bytes(16));
        $db->selectCollection('_test_owner')->insertOne(['token' => $ownerToken]);
        $this->beforeApplicationDestroyed(function () use ($db, $database, $ownerToken): void {
            if (! env('KEEP_TEST_DB', false)) {
                if ($db->getDatabaseName() !== $database
                    || ! preg_match('/^shop_hoatuoi_test_dung_[0-9]+_[a-f0-9]{12}$/', $database)
                    || $db->selectCollection('_test_owner')->findOne(['token' => $ownerToken]) === null) {
                    throw new RuntimeException('Unowned QA database cleanup refused.');
                }
                $db->drop();
            }
        });
        $schemas = json_decode(file_get_contents(base_path('tests/Fixtures/mongodb-validators.json')), true, flags: JSON_THROW_ON_ERROR);
        foreach ($schemas as $name => $options) {
            $db->createCollection($name, $options);
        }
        $this->artisan('migrate', ['--path' => 'database/migrations/mongodb', '--force' => true])->assertExitCode(0);
        $this->withoutVite();
        // Giữ database riêng để đối chiếu bằng chứng; không drop/reset database nào.
    }
}
