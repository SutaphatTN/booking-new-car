<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Models\Traits\BrandScope;
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
	use BrandScope;

	protected $table = 'accessory_price';

	protected $casts = [
		'model_id' => 'int',
		'subModel_id' => 'int',
		'accessoryType_id' => 'int',
		'accessoryPartner_id' => 'int',
		'is_standard' => 'boolean',
		'is_registration' => 'boolean',
		'is_red_plate' => 'boolean',
		'allow_custom_price' => 'boolean',
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
		'is_standard',
		'is_registration',
		'allow_custom_price',
		'accessoryPartner_id',
		'cost_spare',
		'cost',
		'sale',
		'comSale',
		'promo',
		'userZone',
		'brand',
		'branch',
		'startDate',
		'endDate',
		'active',
	];

	protected $dates = ['deleted_at'];

	/**
	 * ตั้งค่าหลังบ้านเท่านั้น: is_red_plate ไม่อยู่ใน $fillable โดยตั้งใจ
	 * (AccessoryController::update() ใช้ $request->except() แบบเหมา — ถ้าใส่ใน fillable จะโดนยิงแก้ผ่านฟอร์มได้)
	 * ใช้ mark ว่ารายการประดับยนต์นี้คือ "ป้ายแดง" → หน้า PO จะบังคับเลือกป้ายแดงก่อนส่งมอบเฉพาะเมื่อมีรายการนี้
	 */
	public function scopeRedPlate($query)
	{
		return $query->where('is_red_plate', 1);
	}

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
			->withPivot(['price_type', 'price', 'commission', 'cost_spare'])
			->withTimestamps();
	}

	/**
	 * ราคาทุนอะไหล่ที่ใช้จริงของใบขายใบนั้น
	 * อ่านจาก snapshot ใน pivot ก่อน (แถวใหม่) — ถ้าไม่มีค่อย fallback ค่าปัจจุบันใน master (แถวเก่าก่อนเพิ่มคอลัมน์)
	 * ต้องเรียกจาก collection ที่โหลดผ่าน relation accessories() เท่านั้น
	 */
	public function usedCostSpare(): float
	{
		return (float) ($this->pivot->cost_spare ?? $this->cost_spare ?? 0);
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
