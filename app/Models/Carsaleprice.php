<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Carsaleprice
 * 
 * @property int $id
 * @property int|null $CarID
 * @property float|null $CarSalePrice
 * @property Carbon|null $StartDate
 * @property Carbon|null $EndDate
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Carsaleprice extends Model
{
	protected $table = 'carsaleprice';

	protected $casts = [
		'CarID' => 'int',
		'CarSalePrice' => 'float',
		'StartDate' => 'datetime',
		'EndDate' => 'datetime'
	];

	protected $fillable = [
		'CarID',
		'CarSalePrice',
		'StartDate',
		'EndDate'
	];
}
