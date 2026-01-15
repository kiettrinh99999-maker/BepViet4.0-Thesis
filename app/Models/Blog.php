<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Blog
 * 
 * @property int $id
 * @property string $title
 * @property string $title_slug
 * @property string $description
 * @property string|null $image_path
 * @property int $blog_category_id
 * @property int $user_id
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property BlogCategory $blog_category
 * @property User $user
 * @property Collection|BlogComment[] $blog_comments
 *
 * @package App\Models
 */
class Blog extends Model
{
	protected $table = 'blogs';

	protected $casts = [
		'blog_category_id' => 'int',
		'user_id' => 'int'
	];

	protected $fillable = [
		'title',
		'title_slug',
		'description',
		'image_path',
		'blog_category_id',
		'user_id',
		'status'
	];

	public function blog_category()
	{
		return $this->belongsTo(BlogCategory::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function blog_comments()
	{
		return $this->hasMany(BlogComment::class);
	}
}
