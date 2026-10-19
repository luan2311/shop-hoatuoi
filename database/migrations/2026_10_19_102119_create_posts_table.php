<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('thumbnail', 255)->nullable();   // ANHBIA
            $table->string('title', 500);                  // TIEUDE
            $table->string('slug', 600)->unique();
            $table->longText('content')->nullable();        // MOTA
            $table->date('published_at')->nullable();       // NGAYTHEM
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
