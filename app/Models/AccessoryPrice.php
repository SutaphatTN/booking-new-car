<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Models\Traits\UserAccessScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class AccessoryPrice
 * 
 * @property int $id
 * @property int $subModel_id
 * @property string $accessory_id
 * @property string $detail
 * @property int $accessoryType_id
 * @property int $accessoryPartner_id
 * @property float|null $cost
 * @property float|null $sale
 * @property float|null $comSale
 * @property float|null $promo
 * @property string|null $userZone
 * @property Carbon|null $startDate
 * @property Carbon|null $endDate
 * @property string $active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class AccessoryPrice extends Model
{
	use SoftDeletes;
	use UserAccessScope;

	protected $table = 'accessory_price';

	protected $casts = [
		'model_id' => 'int',
		'subModel_id' => 'int',
		'accessoryType_id' => 'int',
		'accessoryPartner_id' => 'int',
		'cost' => 'float',
		'sale' => 'float',
		'comSale' => 'float',
		'promo' => 'float',
		'startDate' => 'datetime',
		'endDate' => 'datetime',
	];

	protected $fillable = [
		'model_id',
		'subModel_id',
		'accessory_id',
		'detail',
		'accessoryType_id',
		'accessoryPartner_id',
		'cost',
		'sale',
		'comSale',
		'promo',
		'userZone',
		'brand',
		'startDate',
		'endDate',
		'active',
	];

	protected $dates = ['deleted_at'];

	public function model()
	{
		return $this->belongsTo(TbCarmodel::class, 'model_id', 'id');
	}

	public function subModel()
	{
		return $this->belongsTo(TbSubcarmodel::class, 'subModel_id', 'id');
	}

	public function type()
	{
		return $this->belongsTo(AccessoryType::class, 'accessoryType_id', 'id');
	}

	public function partner()
	{
		return $this->belongsTo(AccessoryPartner::class, 'accessoryPartner_id', 'id');
	}

	public function salecars()
	{
		return $this->belongsToMany(Salecar::class, 'saleaccessory')
			->withPivot(['price_type', 'price', 'commission'])
			->withTimestamps();
	}

	public function getFormatStartDateAttribute()
	{
		return $this->startDate ? Carbon::parse($this->startDate)->format('d-m-Y') : null;
	}

	public function getFormatEndDateAttribute()
	{
		return $this->endDate ? Carbon::parse($this->endDate)->format('d-m-Y') : null;
	}

	public function getStartDateAttribute($value)
	{
		return $value ? Carbon::parse($value)->format('Y-m-d') : null;
	}

	public function getEndDateAttribute($value)
	{
		return $value ? Carbon::parse($value)->format('Y-m-d') : null;
	}
}
