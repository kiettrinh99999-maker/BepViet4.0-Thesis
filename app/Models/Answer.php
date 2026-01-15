<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Answer
 * 
 * @property int $id
 * @property string $content
 * @property int $user_id
 * @property int $question_id
 * @property int|null $parent_id
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Answer|null $answer
 * @property Question $question
 * @property User $user
 * @property Collection|Answer[] $answers
 *
 * @package App\Models
 */
class Answer extends Model
{
	protected $table = 'answers';

	protected $casts = [
		'user_id' => 'int',
		'question_id' => 'int',
		'parent_id' => 'int'
	];

	protected $fillable = [
		'content',
		'user_id',
		'question_id',
		'parent_id',
		'status'
	];

	public function answer()
	{
		return $this->belongsTo(Answer::class, 'parent_id');
	}

	public function question()
	{
		return $this->belongsTo(Question::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function answers()
	{
		return $this->hasMany(Answer::class, 'parent_id');
	}
}
