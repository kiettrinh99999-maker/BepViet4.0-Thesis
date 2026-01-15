<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SettingWeb
 * 
 * @property int $id
 * @property string $name
 * @property string|null $image_path
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $copyright
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class SettingWeb extends Model
{
	protected $table = 'setting_webs';

	protected $fillable = [
		'name',
		'image_path',
		'phone',
		'email',
		'copyright',
		'status'
	];
}
