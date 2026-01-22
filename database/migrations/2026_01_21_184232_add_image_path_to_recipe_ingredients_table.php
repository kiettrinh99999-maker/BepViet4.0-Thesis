<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('recipe_ingredients', function (Blueprint $table) {
           // Thêm cột image_path sau cột name (hoặc cột bất kỳ)
            // nullable() để tránh lỗi nếu các nguyên liệu cũ chưa có ảnh
            $table->string('image_path')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipe_ingredients', function (Blueprint $table) {
            //
        });
    }
};
