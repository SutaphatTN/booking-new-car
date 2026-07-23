<?php

namespace App\Models;

use App\Support\ScopeBypass;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class TbLicensePlate extends Model
{
	protected $table = 'tb_license_plate';

	protected $fillable = [
		'number',
		'is_used',
		'plate_status',
		'userZone',
		'brand',
		'branch',
	];

	/**
	 * สถานะของตัวป้ายเอง (คนละเรื่องกับ is_used ที่บอกว่าผูกงานขายอยู่ไหม)
	 * normal = ใช้งานได้ตามปกติ — อีก 3 สถานะคือป้ายที่หยิบมาใช้ไม่ได้
	 */
	public const STATUS_NORMAL = 'normal';

	public const PLATE_STATUSES = [
		'normal'   => 'ปกติ',
		'lost'     => 'สูญหาย',
		'damaged'  => 'ชำรุด',
		'tracking' => 'ระหว่างติดตาม',
	];

	// สถานะที่ทำให้ป้ายใช้ไม่ได้ — ยืมไม่ได้ และเลือกผูกงานขายใหม่ไม่ได้
	public const BLOCKED_STATUSES = ['lost', 'damaged', 'tracking'];

	public function getPlateStatusValueAttribute(): string
	{
		return $this->plate_status ?: self::STATUS_NORMAL;
	}

	public function getPlateStatusLabelAttribute(): string
	{
		return self::PLATE_STATUSES[$this->plate_status_value] ?? self::PLATE_STATUSES[self::STATUS_NORMAL];
	}

	public function isBlocked(): bool
	{
		return in_array($this->plate_status_value, self::BLOCKED_STATUSES, true);
	}

	// เฉพาะป้ายที่สถานะปกติ (รองรับแถวเก่าที่ plate_status ยังเป็น NULL)
	public function scopeUsable($query)
	{
		return $query->where(function ($q) {
			$q->whereNull('plate_status')
				->orWhereNotIn('plate_status', self::BLOCKED_STATUSES);
		});
	}

	// ป้ายแดงแยกกองตามแบรนด์ (เลิกแชร์ตามกลุ่ม) — แบรนด์อื่นใช้ได้ผ่านการกดยืมเท่านั้น
	// เห็น: ป้ายของแบรนด์ตัวเอง + ป้ายที่แบรนด์ตัวเองยืมอยู่ (ยังไม่กรอกวันที่คืน)
	protected static function booted()
	{
		static::addGlobalScope('brandAccess', function ($query) {
			if (ScopeBypass::$brand) return;
			if (!Auth::check()) return;

			$user = Auth::user();
			if (!$user->brand) return;

			$query->where(function ($q) use ($user) {
				$q->where('tb_license_plate.brand', $user->brand)
					->orWhereExists(function ($sub) use ($user) {
						$sub->selectRaw(1)
							->from('license_plate_loan')
							->whereColumn('license_plate_loan.license_plate_id', 'tb_license_plate.id')
							->where('license_plate_loan.borrower_brand', $user->brand)
							->whereNull('license_plate_loan.return_date');
					});
			});
		});
	}

	public function histories()
	{
		return $this->hasMany(LicensePlateHistory::class, 'licenseID', 'id');
	}

	public function latestHistory()
	{
		return $this->hasOne(LicensePlateHistory::class, 'licenseID', 'id')->latestOfMany();
	}

	public function currentHistory()
	{
		return $this->hasOne(LicensePlateHistory::class, 'licenseID', 'id')
			->whereNull('finance_approved')
			->latestOfMany();
	}

	public function loans()
	{
		return $this->hasMany(LicensePlateLoan::class, 'license_plate_id', 'id');
	}

	// รายการยืมที่ยังไม่คืน (มีได้ครั้งละ 1 รายการต่อป้าย)
	public function activeLoan()
	{
		return $this->hasOne(LicensePlateLoan::class, 'license_plate_id', 'id')
			->whereNull('return_date')
			->latestOfMany();
	}
}
