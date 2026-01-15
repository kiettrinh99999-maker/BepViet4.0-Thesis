<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Profile
 * 
 * @property int $user_id
 * @property string $name
 * @property string|null $phone
 * @property string|null $image_path
 * @property int|null $region_id
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Region|null $region
 * @property User $user
 *
 * @package App\Models
 */
class Profile extends Model
{
	protected $table = 'profiles';
	protected $primaryKey = 'user_id';
	public $incrementing = false;

	protected $casts = [
		'user_id' => 'int',
		'region_id' => 'int'
	];

	protected $fillable = [
		'name',
		'phone',
		'image_path',
		'region_id',
		'status'
	];

	public function region()
	{
		return $this->belongsTo(Region::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
