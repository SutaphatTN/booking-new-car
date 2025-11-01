<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Accessorysaleprice
 * 
 * @property int $id
 * @property int|null $accessoryID
 * @property float|null $AccessorySalePrice
 * @property float|null $AccessoryCom
 * @property float|null $AccessoryComAmount
 * @property Carbon|null $StartDate
 * @property Carbon|null $EndDate
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Accessorysaleprice extends Model
{
	protected $table = 'accessorysaleprice';

	protected $casts = [
		'accessoryID' => 'int',
		'AccessorySalePrice' => 'float',
		'AccessoryCom' => 'float',
		'AccessoryComAmount' => 'float',
		'StartDate' => 'datetime',
		'EndDate' => 'datetime'
	];

	protected $fillable = [
		'accessoryID',
		'AccessorySalePrice',
		'AccessoryCom',
		'AccessoryComAmount',
		'StartDate',
		'EndDate'
	];
}
