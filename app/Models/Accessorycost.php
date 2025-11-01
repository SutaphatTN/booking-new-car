<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Accessorycost
 * 
 * @property int $id
 * @property int|null $accessoryID
 * @property float|null $accessoryCost
 * @property float|null $AccessoryCom
 * @property float|null $AccessoryComAmount
 * @property Carbon|null $StartDate
 * @property Carbon|null $EndDate
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Accessorycost extends Model
{
	protected $table = 'accessorycosts';

	protected $casts = [
		'accessoryID' => 'int',
		'accessoryCost' => 'float',
		'AccessoryCom' => 'float',
		'AccessoryComAmount' => 'float',
		'StartDate' => 'datetime',
		'EndDate' => 'datetime'
	];

	protected $fillable = [
		'accessoryID',
		'accessoryCost',
		'AccessoryCom',
		'AccessoryComAmount',
		'StartDate',
		'EndDate'
	];
}
