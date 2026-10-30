<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use MongoDB\Collection;
use MongoDB\Laravel\Schema\Blueprint;

return new class extends Migration
{
    protected $connection = 'mongodb';

    public function up(): void
    {
        $schema = Schema::connection('mongodb');
        if (! $schema->hasTable('categories')) {
            $schema->create('categories', function (Blueprint $collection) {
                $collection->jsonSchema([
                    'bsonType' => 'object', 'required' => ['code', 'name', 'slug'],
                    'properties' => [
                        'code' => ['bsonType' => 'string'], 'name' => ['bsonType' => 'string'],
                        'slug' => ['bsonType' => 'string'],
                    ],
                ]);
            });
        }
        if (! $schema->hasTable('products')) {
            $schema->create('products', function (Blueprint $collection) {
                $collection->jsonSchema([
                    'bsonType' => 'object', 'required' => ['code', 'name', 'slug', 'price', 'category'],
                    'properties' => [
                        'code' => ['bsonType' => 'string'], 'name' => ['bsonType' => 'string'],
                        'slug' => ['bsonType' => 'string'],
                        'price' => ['bsonType' => ['int', 'double', 'long', 'decimal'], 'minimum' => 0],
                        'stock' => ['bsonType' => ['int', 'long'], 'minimum' => 0],
                        'category' => [
                            'bsonType' => 'object', 'required' => ['code', 'name', 'slug'],
                            'properties' => [
                                'code' => ['bsonType' => 'string'], 'name' => ['bsonType' => 'string'],
                                'slug' => ['bsonType' => 'string'],
                            ],
                        ],
                    ],
                ]);
            });
        }
        // Same keys/options as mongodb_init.js. Existing documents/validators are untouched.
        $database = DB::connection('mongodb')->getDatabase();
        foreach (['products', 'categories'] as $collection) {
            $this->ensureIndex($database->selectCollection($collection), ['code' => 1], ['unique' => true]);
            $this->ensureIndex($database->selectCollection($collection), ['slug' => 1], ['unique' => true]);
        }
        $this->ensureIndex($database->products, ['category.code' => 1]);
        $this->ensureIndex($database->products, ['category.slug' => 1, 'price' => 1]);
        $this->ensureIndex($database->products, ['price' => 1]);
        $this->ensureIndex($database->products, ['status' => 1]);
    }

    private function ensureIndex(Collection $collection, array $keys, array $options = []): void
    {
        foreach ($collection->listIndexes() as $index) {
            if ($index->getKey() === $keys && $index->isUnique() === ($options['unique'] ?? false)
                && ! $index->isSparse() && ! isset($index['partialFilterExpression'])
                && ! isset($index['collation'])) {
                return;
            }
        }
        // Conflicting options or duplicate existing data fail without dropping indexes/documents.
        $collection->createIndex($keys, $options);
    }

    public function down(): void
    {
        // These collections/indexes may predate this migration; never delete shared data.
        throw new LogicException('Migration catalog chỉ bổ sung; rollback cần kế hoạch thủ công để bảo vệ dữ liệu.');
    }
};
