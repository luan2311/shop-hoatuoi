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
        foreach (['users', 'products'] as $collection) {
            if (! $schema->hasCollection($collection)) {
                $schema->create($collection);
            }
        }
        $schema->table('users', function (Blueprint $table): void {
            $table->unique('email');
            $table->unique('username');
        });
        $schema->table('products', function (Blueprint $table): void {
            $table->unique('code');
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        // Shared identity indexes are retained to protect existing data.
    }
};
