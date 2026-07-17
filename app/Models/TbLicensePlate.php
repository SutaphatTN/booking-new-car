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
		'userZone',
		'brand',
		'branch',
	];

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
