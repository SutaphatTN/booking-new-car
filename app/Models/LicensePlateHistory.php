<?php

namespace App\Models;

use App\Models\Traits\UserAccessScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class LicensePlateHistory extends Model
{
	use SoftDeletes;
	use UserAccessScope;

	protected $table = 'license_plate_history';

	protected $fillable = [
		'saleID',
		'licenseID',
		'cust_refund_date',
		'type_refund',
		'date',
		'UserInsert',
		'license_red_front',
		'license_red_back',
		'license_red_book',
		'finance_approved',
		'finance_approved_date',
		'refund_amount',
		'note',
		'userZone',
		'brand'
	];

	protected $dates = ['deleted_at'];

	public function saleCarLic()
	{
		return $this->belongsTo(Salecar::class, 'saleID', 'id');
	}

	public function licenseLic()
	{
		return $this->belongsTo(TbLicensePlate::class, 'licenseID', 'id');
	}

	public function financeUser()
	{
		return $this->belongsTo(User::class, 'finance_approved', 'id');
	}

	public function getCustRefundDateAttribute($value)
	{
		return $value ? Carbon::parse($value)->format('Y-m-d') : null;
	}

	public function getFormatCustRefundDateAttribute()
	{
		return $this->cust_refund_date ? Carbon::parse($this->cust_refund_date)->format('d-m-Y') : null;
	}
}
