<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MealPlan
 * 
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $user_id
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property User $user
 * @property Collection|Recipe[] $recipes
 *
 * @package App\Models
 */
class MealPlan extends Model
{
	protected $table = 'meal_plans';

	protected $casts = [
		'user_id' => 'int'
	];

	protected $fillable = [
		'name',
		'description',
		'user_id',
		'status'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function recipes()
	{
		return $this->belongsToMany(Recipe::class, 'meal_plans_recipes')
					->withPivot('id', 'meal_day_id', 'meal_time_id', 'status')
					->withTimestamps();
	}
}
