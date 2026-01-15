<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Step
 * 
 * @property int $id
 * @property string $step_name
 * @property int $recipe_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Recipe $recipe
 * @property Collection|StepImage[] $step_images
 *
 * @package App\Models
 */
class Step extends Model
{
	protected $table = 'steps';

	protected $casts = [
		'recipe_id' => 'int'
	];

	protected $fillable = [
		'step_name',
		'recipe_id'
	];

	public function recipe()
	{
		return $this->belongsTo(Recipe::class);
	}

	public function step_images()
	{
		return $this->hasMany(StepImage::class);
	}
}
