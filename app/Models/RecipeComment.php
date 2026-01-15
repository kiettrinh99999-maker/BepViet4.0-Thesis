<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class RecipeComment
 * 
 * @property int $id
 * @property string $content
 * @property int $recipe_id
 * @property int|null $parent_id
 * @property int $user_id
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property RecipeComment|null $recipe_comment
 * @property Recipe $recipe
 * @property User $user
 * @property Collection|RecipeComment[] $recipe_comments
 *
 * @package App\Models
 */
class RecipeComment extends Model
{
	protected $table = 'recipe_comments';

	protected $casts = [
		'recipe_id' => 'int',
		'parent_id' => 'int',
		'user_id' => 'int'
	];

	protected $fillable = [
		'content',
		'recipe_id',
		'parent_id',
		'user_id',
		'status'
	];

	public function recipe_comment()
	{
		return $this->belongsTo(RecipeComment::class, 'parent_id');
	}

	public function recipe()
	{
		return $this->belongsTo(Recipe::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function recipe_comments()
	{
		return $this->hasMany(RecipeComment::class, 'parent_id');
	}
}
