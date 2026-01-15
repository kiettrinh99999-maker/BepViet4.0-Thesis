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
        Schema::create('meal_plans_recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meal_plan_id')->constrained('meal_plans')->onDelete('cascade');
            $table->foreignId('recipe_id')->constrained('recipes')->onDelete('cascade');
            $table->foreignId('meal_day_id')->constrained('meal_days');
            $table->foreignId('meal_time_id')->constrained('meal_times');
            $table->string('status')->default('planned');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_plans_recipes');
    }
};
