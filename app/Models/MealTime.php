<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MealTime
 * 
 * @property int $id
 * @property string $name
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|MealPlansRecipe[] $meal_plans_recipes
 *
 * @package App\Models
 */
class MealTime extends Model
{
	protected $table = 'meal_times';

	protected $fillable = [
		'name',
		'status'
	];

	public function meal_plans_recipes()
	{
		return $this->hasMany(MealPlansRecipe::class);
	}
}
