<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Ingredient
 * 
 * @property int $id
 * @property string $name
 * @property string $name_slug
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Recipe[] $recipes
 *
 * @package App\Models
 */
class Ingredient extends Model
{
	protected $table = 'ingredients';

	protected $fillable = [
		'name',
		'name_slug'
	];

	public function recipes()
	{
		return $this->belongsToMany(Recipe::class, 'recipe_ingredients')
					->withPivot('id', 'quantity', 'unit')
					->withTimestamps();
	}
}
