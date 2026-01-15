<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Collection
 * 
 * @property int $id
 * @property string $name
 * @property string|null $image_path
 * @property string $status
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property User $user
 * @property \Illuminate\Database\Eloquent\Collection|Recipe[] $recipes
 *
 * @package App\Models
 */
class Collection extends Model
{
	protected $table = 'collections';

	protected $casts = [
		'user_id' => 'int'
	];

	protected $fillable = [
		'name',
		'image_path',
		'status',
		'user_id'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function recipes()
	{
		return $this->belongsToMany(Recipe::class, 'recipe_collections')
					->withPivot('status');
	}
}
