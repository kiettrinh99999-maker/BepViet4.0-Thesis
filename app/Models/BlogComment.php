<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BlogComment
 * 
 * @property int $id
 * @property string $content
 * @property int $blog_id
 * @property int|null $parent_id
 * @property int $user_id
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Blog $blog
 * @property BlogComment|null $blog_comment
 * @property User $user
 * @property Collection|BlogComment[] $blog_comments
 *
 * @package App\Models
 */
class BlogComment extends Model
{
	protected $table = 'blog_comments';

	protected $casts = [
		'blog_id' => 'int',
		'parent_id' => 'int',
		'user_id' => 'int'
	];

	protected $fillable = [
		'content',
		'blog_id',
		'parent_id',
		'user_id',
		'status'
	];

	public function blog()
	{
		return $this->belongsTo(Blog::class);
	}

	public function blog_comment()
	{
		return $this->belongsTo(BlogComment::class, 'parent_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function blog_comments()
	{
		return $this->hasMany(BlogComment::class, 'parent_id');
	}
}
