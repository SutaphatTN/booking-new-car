<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Car
 * 
 * @property int $id
 * @property int|null $Model_ID
 * @property int|null $Year_ID
 * @property int|null $Color_ID
 * @property string|null $CarDetail
 * @property string $Active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Car extends Model
{
	protected $table = 'cars';

	protected $casts = [
		'Model_ID' => 'int',
		'Year_ID' => 'int',
		'Color_ID' => 'int'
	];

	protected $fillable = [
		'Model_ID',
		'Year_ID',
		'Color_ID',
		'CarDetail',
		'Active'
	];
}
