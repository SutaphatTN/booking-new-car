<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Accessorypromoprice
 * 
 * @property int $id
 * @property int|null $accessoryID
 * @property float|null $AccessoryPromoPrice
 * @property float|null $AccessoryCom
 * @property float|null $AccessoryComAmount
 * @property Carbon|null $StartDate
 * @property Carbon|null $EndDate
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Accessorypromoprice extends Model
{
	protected $table = 'accessorypromoprice';

	protected $casts = [
		'accessoryID' => 'int',
		'AccessoryPromoPrice' => 'float',
		'AccessoryCom' => 'float',
		'AccessoryComAmount' => 'float',
		'StartDate' => 'datetime',
		'EndDate' => 'datetime'
	];

	protected $fillable = [
		'accessoryID',
		'AccessoryPromoPrice',
		'AccessoryCom',
		'AccessoryComAmount',
		'StartDate',
		'EndDate'
	];
}
