<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Region
 * 
 * @property int $id
 * @property string $name
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Profile[] $profiles
 * @property Collection|Recipe[] $recipes
 *
 * @package App\Models
 */
class Region extends Model
{
	protected $table = 'regions';

	protected $fillable = [
		'name',
		'status'
	];

	public function profiles()
	{
		return $this->hasMany(Profile::class);
	}

	public function recipes()
	{
		return $this->hasMany(Recipe::class);
	}
}
