<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class StepImage
 * 
 * @property int $id
 * @property string $image_path
 * @property int $step_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Step $step
 *
 * @package App\Models
 */
class StepImage extends Model
{
	protected $table = 'step_images';

	protected $casts = [
		'step_id' => 'int'
	];

	protected $fillable = [
		'image_path',
		'step_id'
	];

	public function step()
	{
		return $this->belongsTo(Step::class);
	}
}
