<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\DB;
use RuntimeException;

// Thay cho RefreshDatabase: mỗi test chạy trên database shop_hoatuoi_test đã được dọn sạch
trait UsesMongoDatabase
{
    protected function setUpUsesMongoDatabase(): void
    {
        $database = config('database.connections.mongodb.database');
        if (! app()->environment('testing') || $database !== 'shop_hoatuoi_test') {
            throw new RuntimeException('Test chỉ được chạy trên database shop_hoatuoi_test.');
        }

        $db = DB::connection('mongodb')->getDatabase();
        foreach ($db->listCollectionNames() as $collection) {
            $db->dropCollection($collection);
        }
    }
}
