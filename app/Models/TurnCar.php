<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TurnCar
 * 
 * @property int $id
 * @property string|null $brand
 * @property string|null $model
 * @property string|null $year
 * @property string|null $machine
 * @property string|null $color
 * @property string|null $license_plate
 * @property float|null $cost
 * @property float|null $com
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class TurnCar extends Model
{
	protected $table = 'turn_car';

	protected $casts = [
		'priceCost' => 'float',
		'priceCom' => 'float'
	];

	protected $fillable = [
		'brand',
		'model',
		'year_turn',
		'machine',
		'color_turn',
		'license_plate',
		'cost_turn',
		'com_turn'
	];

	public function saleCar()
	{
		return $this->hasOne(Salecar::class, 'TurnCarID');
	}
}
