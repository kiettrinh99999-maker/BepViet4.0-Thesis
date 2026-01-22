<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
/**
 * Class User
 * 
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string $email
 * @property string $role
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Answer[] $answers
 * @property Collection|BlogComment[] $blog_comments
 * @property Collection|Blog[] $blogs
 * @property Collection|Collection[] $collections
 * @property Collection|Follow[] $follows
 * @property Collection|MealPlan[] $meal_plans
 * @property Profile|null $profile
 * @property Collection|Question[] $questions
 * @property Collection|Rate[] $rates
 * @property Collection|RecipeComment[] $recipe_comments
 * @property Collection|RecipeReport[] $recipe_reports
 * @property Collection|Recipe[] $recipes
 *
 * @package App\Models
 */
class User extends Authenticatable
{
	use HasApiTokens, HasFactory, Notifiable; 
	protected $table = 'users';

	protected $hidden = [
		'password'
	];

	protected $fillable = [
		'username',
		'password',
		'email',
		'role',
		'status'
	];

	public function answers()
	{
		return $this->hasMany(Answer::class);
	}

	public function blog_comments()
	{
		return $this->hasMany(BlogComment::class);
	}

	public function blogs()
	{
		return $this->hasMany(Blog::class);
	}

	public function collections()
	{
		return $this->hasMany(Collection::class);
	}

	public function follows()
	{
		return $this->hasMany(Follow::class, 'following_id');
	}

	public function meal_plans()
	{
		return $this->hasMany(MealPlan::class);
	}

	public function profile()
	{
		return $this->hasOne(Profile::class);
	}

	public function questions()
	{
		return $this->hasMany(Question::class);
	}

	public function rates()
	{
		return $this->hasMany(Rate::class);
	}

	public function recipe_comments()
	{
		return $this->hasMany(RecipeComment::class);
	}

	public function recipe_reports()
	{
		return $this->hasMany(RecipeReport::class);
	}

	public function recipes()
	{
		return $this->hasMany(Recipe::class);
	}
}
