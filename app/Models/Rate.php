<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Rate
 * 
 * @property int $id
 * @property int $score
 * @property int $user_id
 * @property int $recipe_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Recipe $recipe
 * @property User $user
 *
 * @package App\Models
 */
class Rate extends Model
{
	protected $table = 'rates';

	protected $casts = [
		'score' => 'int',
		'user_id' => 'int',
		'recipe_id' => 'int'
	];

	protected $fillable = [
		'score',
		'user_id',
		'recipe_id'
	];

	public function recipe()
	{
		return $this->belongsTo(Recipe::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
