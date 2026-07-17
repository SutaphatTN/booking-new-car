<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class LicensePlateLoan extends Model
{
	protected $table = 'license_plate_loan';

	// ไม่ใช้ BrandScope — ทั้งแบรนด์เจ้าของและแบรนด์ที่ยืมต้องเห็นรายการเดียวกัน

	protected $fillable = [
		'license_plate_id',
		'owner_brand',
		'borrower_brand',
		'borrow_date',
		'return_date',
		'note',
		'borrowed_by',
		'returned_by',
	];

	public function plate()
	{
		return $this->belongsTo(TbLicensePlate::class, 'license_plate_id', 'id');
	}

	public function borrowedByUser()
	{
		return $this->belongsTo(User::class, 'borrowed_by', 'id');
	}

	public function returnedByUser()
	{
		return $this->belongsTo(User::class, 'returned_by', 'id');
	}

	// loan ที่ยังไม่คืน = ป้ายยังอยู่กับแบรนด์ที่ยืม
	public function scopeActive($query)
	{
		return $query->whereNull('return_date');
	}

	public function getFormatBorrowDateAttribute()
	{
		return $this->borrow_date ? Carbon::parse($this->borrow_date)->format('d/m/Y') : null;
	}

	public function getFormatReturnDateAttribute()
	{
		return $this->return_date ? Carbon::parse($this->return_date)->format('d/m/Y') : null;
	}
}
