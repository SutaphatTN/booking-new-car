<?php

namespace App\Models;

use App\Models\Traits\UserAccessScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class VehicleLicense extends Model
{
	use SoftDeletes;
	use UserAccessScope;

	protected $table = 'vehicle_license';

	protected $fillable = [
		'SaleID',
		'withdrawal_date',
		'backup_clear_date',
		'registration_date',
		'license_name',
		'license_number',
		'license_province',
		'withdrawal_check',
		'withdrawal_channel',
		'withdrawal_bill',
		'withdrawal_total',
		'receipt_check',
		'receipt_channel',
		'receipt_bill',
		'receipt_total',
		'diff',
		'labe_status',
		'note',
		'userZone',
		'brand'
	];

	protected $dates = ['deleted_at'];

	public function saleCar()
	{
		return $this->belongsTo(Salecar::class, 'SaleID', 'id');
	}

	public function provincesV()
	{
		return $this->belongsTo(TbProvinces::class, 'license_province', 'id');
	}

	public function getWithdrawalDateAttribute($value)
	{
		return $value ? Carbon::parse($value)->format('Y-m-d') : null;
	}

	public function getFormatWithdrawalDateAttribute()
	{
		return $this->withdrawal_date ? Carbon::parse($this->withdrawal_date)->format('d-m-Y') : null;
	}

	public function getBackupClearDateAttribute($value)
	{
		return $value ? Carbon::parse($value)->format('Y-m-d') : null;
	}

	public function getFormatBackupClearDateAttribute()
	{
		return $this->backup_clear_date ? Carbon::parse($this->backup_clear_date)->format('d-m-Y') : null;
	}
}
