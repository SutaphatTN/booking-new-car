<?php

namespace App\Models;

use App\Models\Traits\BrandScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
	use SoftDeletes;
	use BrandScope;

	protected $table = 'campaign';

	protected $casts = [
		'cashSupport_final' => 'float',
	];

	protected $fillable = [
		'model_id',
		'subModel_id',
		'camName_id',
		'campaign_type',
		'cashSupport',
		'cashSupport_deduct',
		'cashSupport_final',
		'userZone',
		'brand',
		'branch',
		'startDate',
		'endDate',
		'startYear',
		'endYear',
		'active',
		'archived'
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

	/**
	 * subModel_id = NULL คือแคมเปญที่ใช้กับ "ทุกรุ่นย่อย" ของ model_id นั้น
	 * scope นี้จึงดึงทั้งแคมเปญที่ผูกรุ่นย่อยตรง ๆ และแคมเปญคลุมทุกรุ่นย่อยของรุ่นหลักเดียวกัน
	 * ($modelId ต้องมีเสมอ ไม่งั้นแถว NULL ของรุ่นอื่นจะหลุดข้ามรุ่นมา — หาให้เองถ้าไม่ส่งมา)
	 */
	public function scopeForSubModel($query, $subModelId, $modelId = null)
	{
		$modelId = $modelId ?: TbSubcarmodel::where('id', $subModelId)->value('model_id');

		return $query->where(function ($q) use ($subModelId, $modelId) {
			$q->where('subModel_id', $subModelId);

			if ($modelId) {
				$q->orWhere(fn($qq) => $qq->whereNull('subModel_id')->where('model_id', $modelId));
			}
		});
	}

	/** ป้ายรุ่นย่อยสำหรับแสดงผล — NULL = คลุมทุกรุ่นย่อยของรุ่นหลัก */
	public function getSubModelLabelAttribute(): string
	{
		if ($this->subModel) {
			return $this->subModel->name;
		}

		return is_null($this->subModel_id) ? 'ทุกรุ่นย่อย' : '-';
	}

	public function type()
	{
		return $this->belongsTo(TbCampaignType::class, 'campaign_type', 'id');
	}

	public function appellation()
	{
		return $this->belongsTo(CampaignName::class, 'camName_id', 'id');
	}

	public function approvals()
	{
		return $this->hasMany(CampaignApproval::class, 'campaign_id', 'id');
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
