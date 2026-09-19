<?php

namespace App\Models;

use App\Models\Traits\BrandScope;
use App\Models\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class VehicleLicense extends Model
{
	use SoftDeletes;
	use BrandScope;
	// ยอดตั้งเบิก/เคลียร์ + เลขป้ายขาว ต้องตามได้ว่าใครแก้ — ดู activity_logs
	use LogsActivity;

	protected $table = 'vehicle_license';

	protected $fillable = [
		'SaleID',
		'reg_by',
		'reg_by_marked_at',
		'reg_by_marked_by',
		'withdrawal_date',
		'withdrawal_batch',
		'backup_clear_date',
		'clear_batch',
		'registration_date',
		'license_name',
		'license_number',
		'license_province',
		'withdrawal_check',
		'withdrawal_channel',
		'withdrawal_bill',
		'withdrawal_other',
		'withdrawal_other_note',
		'withdrawal_total',
		'receipt_check',
		'receipt_channel',
		'receipt_bill',
		'receipt_other',
		'receipt_other_note',
		'receipt_total',
		'diff',
		'labe_status',
		'note',
		'userZone',
		'brand',
		'branch',
	];

	protected $dates = ['deleted_at'];

	/** บริษัทจดทะเบียนให้ตามปกติ — ต้องผ่านด่านส่งเบิกก่อนถึงกรอกป้ายขาวได้ */
	public const REG_BY_COMPANY = 'company';

	/** ลูกค้าไปจดทะเบียนเอง — ข้ามด่านส่งเบิก/เคลียร์ ไม่มียอดเบิก ไม่มียอดเคลียร์ มีแต่เลขป้ายขาว */
	public const REG_BY_CUSTOMER = 'customer';

	public function isSelfRegistered(): bool
	{
		return $this->reg_by === self::REG_BY_CUSTOMER;
	}

	public function markedByUser()
	{
		return $this->belongsTo(User::class, 'reg_by_marked_by', 'id');
	}

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
