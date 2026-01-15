<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Difficulty
 * 
 * @property int $id
 * @property string $name
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Recipe[] $recipes
 *
 * @package App\Models
 */
class Difficulty extends Model
{
	protected $table = 'difficulties';

	protected $fillable = [
		'name',
		'status'
	];

	public function recipes()
	{
		return $this->hasMany(Recipe::class);
	}
}
