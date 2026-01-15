<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Question
 * 
 * @property int $id
 * @property string $title
 * @property string $title_slug
 * @property string|null $description
 * @property string|null $image_path
 * @property int $user_id
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property User $user
 * @property Collection|Answer[] $answers
 *
 * @package App\Models
 */
class Question extends Model
{
	protected $table = 'questions';

	protected $casts = [
		'user_id' => 'int'
	];

	protected $fillable = [
		'title',
		'title_slug',
		'description',
		'image_path',
		'user_id',
		'status'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function answers()
	{
		return $this->hasMany(Answer::class);
	}
}
