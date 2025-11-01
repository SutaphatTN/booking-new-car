<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Accessorysale
 * 
 * @property int $id
 * @property int|null $SaleID
 * @property int|null $AccessoryID
 * @property int|null $AccessoryType
 * @property string|null $AccessoryPaidSource
 * @property float|null $AccessoryPrice
 * @property int|null $AccessoryCom
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Accessorysale extends Model
{
	protected $table = 'accessorysale';

	protected $casts = [
		'SaleID' => 'int',
		'AccessoryID' => 'int',
		'AccessoryType' => 'int',
		'AccessoryPrice' => 'float',
		'AccessoryCom' => 'int'
	];

	protected $fillable = [
		'SaleID',
		'AccessoryID',
		'AccessoryType',
		'AccessoryPaidSource',
		'AccessoryPrice',
		'AccessoryCom'
	];
}
