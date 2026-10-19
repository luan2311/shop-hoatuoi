<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();           // MaSP
            $table->string('name', 100);                    // TenSP
            $table->unsignedBigInteger('price');            // GiaBan (VND)
            $table->string('image', 255)->nullable();       // AnhSP
            $table->longText('description')->nullable();    // MoTaSP
            $table->string('status', 50)->default('Còn hàng'); // TinhTrang
            $table->string('brand', 100)->nullable();       // ThuongHieu
            $table->integer('stock')->default(0);           // SoLuongTon
            $table->string('slug', 200)->unique();
            $table->foreignId('category_id')->constrained('categories')->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
