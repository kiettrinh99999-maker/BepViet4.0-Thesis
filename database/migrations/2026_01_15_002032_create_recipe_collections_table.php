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
        Schema::create('recipe_collections', function (Blueprint $table) {
            $table->foreignId('recipe_id')->constrained()->onDelete('cascade');
            $table->foreignId('collection_id')->constrained()->onDelete('cascade');
            $table->primary(['recipe_id', 'collection_id']);
            $table->string('status')->default('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_collections');
    }
};
