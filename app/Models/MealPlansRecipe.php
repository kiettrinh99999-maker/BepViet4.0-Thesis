<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MealPlansRecipe
 * 
 * @property int $id
 * @property int $meal_plan_id
 * @property int $recipe_id
 * @property int $meal_day_id
 * @property int $meal_time_id
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property MealDay $meal_day
 * @property MealPlan $meal_plan
 * @property MealTime $meal_time
 * @property Recipe $recipe
 *
 * @package App\Models
 */
class MealPlansRecipe extends Model
{
	protected $table = 'meal_plans_recipes';

	protected $casts = [
		'meal_plan_id' => 'int',
		'recipe_id' => 'int',
		'meal_day_id' => 'int',
		'meal_time_id' => 'int'
	];

	protected $fillable = [
		'meal_plan_id',
		'recipe_id',
		'meal_day_id',
		'meal_time_id',
		'status'
	];

	public function meal_day()
	{
		return $this->belongsTo(MealDay::class);
	}

	public function meal_plan()
	{
		return $this->belongsTo(MealPlan::class);
	}

	public function meal_time()
	{
		return $this->belongsTo(MealTime::class);
	}

	public function recipe()
	{
		return $this->belongsTo(Recipe::class);
	}
}
