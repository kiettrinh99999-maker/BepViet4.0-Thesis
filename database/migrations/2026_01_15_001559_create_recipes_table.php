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
        Schema::create('recipes', function (Blueprint $table) {
           $table->id();
            $table->string('title'); 
            $table->string('title_slug')->unique(); 
            
            $table->text('description')->nullable(); 
            
            $table->string('image_path')->nullable();
            $table->integer('cooking_time')->nullable(); 
            $table->integer('serving')->nullable(); 
            
            $table->foreignId('region_id')->nullable()->constrained('regions');
            $table->foreignId('difficulty_id')->nullable()->constrained('difficulties');
            $table->foreignId('event_id')->nullable()->constrained('events');
            $table->foreignId('recipe_category_id')->constrained('recipe_categories');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
