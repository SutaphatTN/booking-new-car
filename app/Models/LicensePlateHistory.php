<?php

namespace App\Models;

use App\Models\Traits\BrandScope;
use App\Models\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class LicensePlateHistory extends Model
{
	use SoftDeletes;
	use BrandScope;
	// เรื่องเงินคืนป้ายแดง (ยอด/วันที่/เอกสาร/คืนป้ายก่อน) ต้องตามได้ว่าใครแก้ — ดู activity_logs
	use LogsActivity;

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
		'refund_slip_url',
		'plate_returned_at',
		'plate_returned_by',
		'plate_return_note',
		'note',
		'userZone',
		'brand',
		'branch',
	];

	protected $dates = ['deleted_at'];

	// สลิปคืนเงินลูกค้า — เก็บเป็น JSON [{url, name}] แบบเดียวกับไฟล์แนบที่อื่นในระบบ
	protected $casts = [
		'refund_slip_url' => 'array',
		'plate_returned_at' => 'datetime',
	];

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

	/** คนที่กด "คืนป้ายก่อน" (ปลดป้ายคืนสต็อกทั้งที่ยังไม่ปิดเงิน) */
	public function plateReturnUser()
	{
		return $this->belongsTo(User::class, 'plate_returned_by', 'id')->withTrashed();
	}

	/** คืนป้ายไปก่อนแล้วแต่ยังไม่ปิดเงิน — รายการที่ต้องไปตามเก็บในหน้า "ค้างคืนเงินป้ายแดง" */
	public function scopePendingRefund($query)
	{
		return $query->whereNotNull('plate_returned_at')->whereNull('finance_approved');
	}

	public function getFormatPlateReturnedAtAttribute(): ?string
	{
		return $this->plate_returned_at ? Carbon::parse($this->plate_returned_at)->format('d-m-Y') : null;
	}
	public function financeUser()
	{
		return $this->belongsTo(User::class, 'finance_approved', 'id')->withTrashed();
	}

	public function getCustRefundDateAttribute($value)
	{
		return $value ? Carbon::parse($value)->format('Y-m-d') : null;
	}

	public function getFormatCustRefundDateAttribute()
	{
		return $this->cust_refund_date ? Carbon::parse($this->cust_refund_date)->format('d-m-Y') : null;
	}

	/**
	 * ช่องที่ยังกรอกไม่ครบ ก่อนกด "ยืนยันการจ่ายเงินจริง" (กดแล้วย้อนไม่ได้ + ปลดป้ายคืนสต็อก)
	 * ใช้ร่วมกันทั้งปุ่มในตาราง (ดักก่อนเปิด dialog) และด่านฝั่ง server ใน approveFinance
	 *  - เอกสารป้ายแดง : ขอแค่ช่องใดช่องหนึ่ง (ติ๊ก "คืนครบทุกรายการ" = ครบทั้ง 3 อยู่แล้ว)
	 *  - การคืนเงิน : ครบทุกช่อง ยกเว้นหมายเหตุที่ไม่บังคับ
	 *  - ไฟล์แนบ : หลักฐานการโอนเงินค่าป้ายแดง (อยู่บนใบขาย) + สลิปคืนเงินลูกค้า อย่างละ 1 ไฟล์
	 */
	public function approveFinanceMissing(): array
	{
		$missing = [];

		if (!$this->license_red_front && !$this->license_red_back && !$this->license_red_book) {
			$missing[] = 'เอกสารป้ายแดง (ติ๊กอย่างน้อย 1 รายการ)';
		}
		if (!$this->cust_refund_date) {
			$missing[] = 'วันที่คืนเงินลูกค้า';
		}
		// คืน 0 บาทเป็นเคสที่มีจริง — นับว่า "กรอกแล้ว" ; เฉพาะปล่อยว่างเท่านั้นที่ถือว่ายังไม่กรอก
		if ($this->refund_amount === null || $this->refund_amount === '') {
			$missing[] = 'ยอดคืนเงิน';
		}
		if (!$this->type_refund) {
			$missing[] = 'ประเภทการคืนเงิน';
		}

		// หลักฐานการโอนเงินค่าป้ายแดง — ไฟล์อยู่บนใบขาย (แนบได้ทั้งหน้าใบจอง/หน้าประวัติ/หน้านี้)
		// ใบที่ยังไม่ผูกใบขายจะแนบไม่ได้เลย จึงไม่เอามาดัก ไม่งั้นกดยืนยันไม่ได้ตลอดไป
		// (saleCarLic ถูก eager load มาแล้วตอนวาดตาราง จึงไม่เพิ่ม query ต่อแถว)
		if ($this->saleCarLic && empty($this->saleCarLic->red_license_slip_url)) {
			$missing[] = 'หลักฐานการโอนเงิน (ค่าป้ายแดง)';
		}

		if (empty($this->refund_slip_url)) {
			$missing[] = 'สลิปคืนเงินลูกค้า';
		}

		return $missing;
	}
}
