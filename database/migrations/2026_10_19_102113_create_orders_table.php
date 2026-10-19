<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->decimal('total', 15, 2);                        // TongTien
            $table->string('receiver_name', 100);                   // TenNguoiNhan
            $table->string('receiver_address', 255);                // DiaChiNhan
            $table->string('receiver_phone', 20);                   // SDTNhan
            $table->string('email', 100)->nullable();
            $table->text('note')->nullable();                       // GhiChu
            $table->string('status', 50)->default('Pending');       // TrangThai: Pending/Processing/Shipped/Completed/Cancelled
            $table->string('payment_method', 50)->nullable();       // PhuongThucThanhToan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
