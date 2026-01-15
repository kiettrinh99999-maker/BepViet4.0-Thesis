<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class RecipeReport
 * 
 * @property int $id
 * @property string $content
 * @property int $user_id
 * @property int $recipe_id
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Recipe $recipe
 * @property User $user
 *
 * @package App\Models
 */
class RecipeReport extends Model
{
	protected $table = 'recipe_reports';

	protected $casts = [
		'user_id' => 'int',
		'recipe_id' => 'int'
	];

	protected $fillable = [
		'content',
		'user_id',
		'recipe_id',
		'status'
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
