<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class RecipeCollection
 * 
 * @property int $recipe_id
 * @property int $collection_id
 * @property string $status
 * 
 * @property Collection $collection
 * @property Recipe $recipe
 *
 * @package App\Models
 */
class RecipeCollection extends Model
{
	protected $table = 'recipe_collections';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'recipe_id' => 'int',
		'collection_id' => 'int'
	];

	protected $fillable = [
		'status'
	];

	public function collection()
	{
		return $this->belongsTo(Collection::class);
	}

	public function recipe()
	{
		return $this->belongsTo(Recipe::class);
	}
}
