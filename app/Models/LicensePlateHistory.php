<?php

namespace App\Models;

use App\Models\Traits\BrandScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class LicensePlateHistory extends Model
{
	use SoftDeletes;
	use BrandScope;

	protected $table = 'license_plate_history';

	// ประวัติป้ายแดงแชร์ตามกลุ่ม brand เช่นเดียวกับตัวป้าย (ดู config/brand.php)
	public $sharedByBrandGroup = true;

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
		'brand',
		'branch',
	];

	protected $dates = ['deleted_at'];

	public function saleCarLic()
	{
		return $this->belongsTo(Salecar::class, 'saleID', 'id');
	}

	public function licenseLic()
	{
		// ข้าม brand scope ของป้าย — ประวัติอาจอ้างป้ายที่ยืมมาแล้วคืนเจ้าของไปแล้ว
		return $this->belongsTo(TbLicensePlate::class, 'licenseID', 'id')
			->withoutGlobalScope('brandAccess');
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
