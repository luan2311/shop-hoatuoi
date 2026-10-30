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

        $this->beforeApplicationDestroyed(function () use ($database): void {
            if (! env('KEEP_TEST_DB', false) && str_starts_with($database, 'shop_hoatuoi_test_dung_')) {
                try {
                    DB::connection('mongodb')->getDatabase()->drop();
                } catch (Throwable) {
                }
            }
        });
        try {
            $db = DB::connection('mongodb')->getDatabase();
            $hello = $db->command(['hello' => 1])->toArray()[0];
        } catch (Throwable) {
            $this->markTestSkipped('BLOCKED: không kết nối được MongoDB test; không dùng SQL thay thế.');
        }
        if (! isset($hello->setName) && ($hello->msg ?? '') !== 'isdbgrid') {
            $this->markTestSkipped('BLOCKED: QA nghiệp vụ cần replica set hoặc sharded cluster.');
        }
        $schemas = json_decode(file_get_contents(base_path('tests/Fixtures/mongodb-validators.json')), true, flags: JSON_THROW_ON_ERROR);
        foreach ($schemas as $name => $options) {
            $db->createCollection($name, $options);
        }
        $this->artisan('migrate', ['--path' => 'database/migrations/mongodb', '--force' => true])->assertExitCode(0);
        $this->withoutVite();
        // Giữ database riêng để đối chiếu bằng chứng; không drop/reset database nào.
    }
}
