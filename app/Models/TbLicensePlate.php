<?php

namespace App\Models;

use App\Models\Traits\UserAccessScope;
use Illuminate\Database\Eloquent\Model;

class TbLicensePlate extends Model
{
	use UserAccessScope;
	
	protected $table = 'tb_license_plate';

	protected $fillable = [
		'number',
		'is_used',
		'userZone',
		'brand'
	];

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
}
