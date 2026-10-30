<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MongoDB\Laravel\Schema\Blueprint;

return new class extends Migration
{
    protected $connection = 'mongodb';

    public function up(): void
    {
        Schema::connection('mongodb')->table('orders', function (Blueprint $table): void {
            // Legacy orders have no checkout_key; sparse keeps those documents valid.
            $table->unique('checkout_key', null, null, ['sparse' => true]);
        });
    }

    public function down(): void
    {
        Schema::connection('mongodb')->table('orders', fn (Blueprint $table) => $table->dropIndex('checkout_key_1'));
    }
};
