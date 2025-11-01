<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TbCaryear
 * 
 * @property int $id
 * @property int|null $Model_ID
 * @property Carbon|null $year
 * @property string $Active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class TbCaryear extends Model
{
	protected $table = 'tb_caryears';

	protected $casts = [
		'Model_ID' => 'int',
		'year' => 'datetime'
	];

	protected $fillable = [
		'Model_ID',
		'year',
		'Active'
	];
}
