<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Follow
 * 
 * @property int $follower_id
 * @property int $following_id
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property User $user
 *
 * @package App\Models
 */
class Follow extends Model
{
	protected $table = 'follows';
	protected $primaryKey = null;
    public $incrementing = false;
	protected $casts = [
		'follower_id' => 'int',
		'following_id' => 'int'
	];

	protected $fillable = [
		'follower_id', 
        'following_id',
		'status'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'following_id');
	}
}
