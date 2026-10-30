<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MongoDB\Laravel\Schema\Blueprint;

return new class extends Migration
{
    protected $connection = 'mongodb';

    public function up(): void
    {
        $schema = Schema::connection('mongodb');
        foreach (['carts', 'orders'] as $collection) {
            if (! $schema->hasCollection($collection)) {
                $schema->create($collection);
            }
        }
        // Index definitions match mongodb_init.js; no data is dropped or rewritten.
        $schema->table('carts', fn (Blueprint $table) => $table->unique('user_id'));
        $schema->table('orders', function (Blueprint $table): void {
            $table->unique('order_code');
            $table->index('user_id');
            $table->index(['user_id' => 1, 'created_at' => -1, '_id' => -1]);
            $table->index(['status' => 1, 'created_at' => -1, '_id' => -1]);
        });
    }

    public function down(): void
    {
        // Sales records and original indexes are deliberately retained on rollback.
        Schema::connection('mongodb')->table('orders', function (Blueprint $table): void {
            $table->dropIndex(['user_id' => 1, 'created_at' => -1, '_id' => -1]);
            $table->dropIndex(['status' => 1, 'created_at' => -1, '_id' => -1]);
        });
    }
};
