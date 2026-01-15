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
        Schema::create('recipe_comments', function (Blueprint $table) {
           $table->id();
            $table->text('content'); // Bình luận dài
            $table->foreignId('recipe_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('recipe_comments')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_comments');
    }
};
