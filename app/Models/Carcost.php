<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Carcost
 * 
 * @property int $id
 * @property int|null $CarID
 * @property float|null $CarCost
 * @property Carbon|null $StartDate
 * @property Carbon|null $EndDate
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Carcost extends Model
{
	protected $table = 'carcosts';

	protected $casts = [
		'CarID' => 'int',
		'CarCost' => 'float',
		'StartDate' => 'datetime',
		'EndDate' => 'datetime'
	];

	protected $fillable = [
		'CarID',
		'CarCost',
		'StartDate',
		'EndDate'
	];
}
