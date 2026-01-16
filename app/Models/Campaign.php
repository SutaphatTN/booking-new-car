<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
	use SoftDeletes;

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
		'startDate',
		'endDate',
		'startYear',
		'endYear',
		'active'
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
		return $this->belongsTo(TbCampaignType::class, 'campaign_type', 'id');
	}

	public function appellation()
	{
		return $this->belongsTo(CampaignName::class, 'camName_id', 'id');
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
