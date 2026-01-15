<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Recipe
 * 
 * @property int $id
 * @property string $title
 * @property string $title_slug
 * @property string|null $description
 * @property string|null $image_path
 * @property int|null $cooking_time
 * @property int|null $serving
 * @property int|null $region_id
 * @property int|null $difficulty_id
 * @property int|null $event_id
 * @property int $recipe_category_id
 * @property int $user_id
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Difficulty|null $difficulty
 * @property Event|null $event
 * @property RecipeCategory $recipe_category
 * @property Region|null $region
 * @property User $user
 * @property Collection|MealPlan[] $meal_plans
 * @property Collection|Rate[] $rates
 * @property Collection|Collection[] $collections
 * @property Collection|RecipeComment[] $recipe_comments
 * @property Collection|Ingredient[] $ingredients
 * @property Collection|RecipeReport[] $recipe_reports
 * @property Collection|Step[] $steps
 *
 * @package App\Models
 */
class Recipe extends Model
{
	protected $table = 'recipes';

	protected $casts = [
		'cooking_time' => 'int',
		'serving' => 'int',
		'region_id' => 'int',
		'difficulty_id' => 'int',
		'event_id' => 'int',
		'recipe_category_id' => 'int',
		'user_id' => 'int'
	];

	protected $fillable = [
		'title',
		'title_slug',
		'description',
		'image_path',
		'cooking_time',
		'serving',
		'region_id',
		'difficulty_id',
		'event_id',
		'recipe_category_id',
		'user_id',
		'status'
	];

	public function difficulty()
	{
		return $this->belongsTo(Difficulty::class);
	}

	public function event()
	{
		return $this->belongsTo(Event::class);
	}

	public function recipe_category()
	{
		return $this->belongsTo(RecipeCategory::class);
	}

	public function region()
	{
		return $this->belongsTo(Region::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function meal_plans()
	{
		return $this->belongsToMany(MealPlan::class, 'meal_plans_recipes')
					->withPivot('id', 'meal_day_id', 'meal_time_id', 'status')
					->withTimestamps();
	}

	public function rates()
	{
		return $this->hasMany(Rate::class);
	}

	public function collections()
	{
		return $this->belongsToMany(Collection::class, 'recipe_collections')
					->withPivot('status');
	}

	public function recipe_comments()
	{
		return $this->hasMany(RecipeComment::class);
	}

	public function ingredients()
	{
		return $this->belongsToMany(Ingredient::class, 'recipe_ingredients')
					->withPivot('id', 'quantity', 'unit')
					->withTimestamps();
	}

	public function recipe_reports()
	{
		return $this->hasMany(RecipeReport::class);
	}

	public function steps()
	{
		return $this->hasMany(Step::class);
	}
}
