<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Saleaccessory
 * 
 * @property int $id
 * @property int|null $salecar_id
 * @property int|null $accessory_id
 * @property string|null $price_type
 * @property string|null $price
 * @property string|null $commission
 * @property string|null $type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Saleaccessory extends Model
{
	use SoftDeletes;

	protected $table = 'saleaccessory';

	protected $casts = [
		'salecar_id' => 'int',
		'accessory_id' => 'int'
	];

	protected $fillable = [
		'salecar_id',
		'accessory_id',
		'price_type',
		'price',
		'commission',
		'type',
		'userZone',
		'brand'
	];

	protected $dates = ['deleted_at'];
}
