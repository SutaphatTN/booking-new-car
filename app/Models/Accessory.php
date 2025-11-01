<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Accessory
 * 
 * @property int $id
 * @property int|null $Car_ID
 * @property string|null $AccessorySource
 * @property string|null $AccessoryDetail
 * @property string $Active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Accessory extends Model
{
	protected $table = 'accessorys';

	protected $casts = [
		'Car_ID' => 'int'
	];

	protected $fillable = [
		'Car_ID',
		'AccessorySource',
		'AccessoryDetail',
		'Active'
	];

	public function salecars()
	{
		return $this->belongsToMany(Salecar::class, 'saleaccessory')
			->withPivot(['price_type', 'price', 'commission'])
			->withTimestamps();
	}
}
