<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Customer
 * 
 * @property int $id
 * @property int|null $PrefixName
 * @property string|null $FirstName
 * @property string|null $MiddleName
 * @property string|null $LastName
 * @property string|null $IDNumber
 * @property Carbon|null $NewCardDate
 * @property Carbon $ExpireCard
 * @property Carbon|null $Birthday
 * @property string|null $Gender
 * @property string|null $Nationality
 * @property string|null $religion
 * @property string|null $LineID
 * @property string|null $FacebookName
 * @property int|null $RelationST
 * @property string|null $FirstNameofRelation
 * @property string|null $LastNameofRelation
 * @property string|null $PhoneofRelation
 * @property string|null $Note
 * @property string|null $Address
 * @property string|null $PostAddress
 * @property string|null $Mobilephone1
 * @property string|null $Mobilephone2
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Customer extends Model
{
	use SoftDeletes;

	protected $table = 'customers';

	protected $casts = [
		'PrefixName' => 'int',
		'NewCardDate' => 'datetime',
		'ExpireCard' => 'datetime',
		'Birthday' => 'datetime',
		'RelationST' => 'int'
	];

	protected $fillable = [
		'PrefixName',
		'FirstName',
		'MiddleName',
		'LastName',
		'IDNumber',
		'NewCardDate',
		'ExpireCard',
		'Birthday',
		'Gender',
		'Nationality',
		'religion',
		'LineID',
		'FacebookName',
		'RelationST',
		'FirstNameofRelation',
		'LastNameofRelation',
		'PhoneofRelation',
		'Note',
		'Mobilephone1',
		'Mobilephone2',
		'userZone',
		'brand',
	];

	protected $dates = ['deleted_at'];

	protected $appends = ['formatted_id_number', 'formatted_mobile'];

	public function prefix()
	{
		return $this->belongsTo(TbPrefixname::class, 'PrefixName', 'id');
	}

	public function salecars()
	{
		return $this->hasMany(Salecar::class, 'CusID', 'id');
	}

	public function salecarsRef()
	{
		return $this->hasMany(Salecar::class, 'ReferrerID', 'id');
	}

	public function getFormattedIdNumberAttribute()
	{
		$id = $this->IDNumber;
		return substr($id, 0, 1) . '-' . substr($id, 1, 4) . '-' . substr($id, 5, 5) . '-' . substr($id, 10, 2) . '-' . substr($id, 12, 1);
	}

	public function getFormattedMobileAttribute()
	{
		$mobile = $this->Mobilephone1;

		if (empty($mobile)) {
			return '-';
		}

		return substr($mobile, 0, 3) . '-' . substr($mobile, 3, 4) . '-' . substr($mobile, 7, 3);
	}

	public function getFormattedMobileUpAttribute()
	{
		$mobile = $this->Mobilephone2;

		if (empty($mobile)) {
			return '-';
		}

		return substr($mobile, 0, 3) . '-' . substr($mobile, 3, 4) . '-' . substr($mobile, 7, 3);
	}

	public function getFormattedNewCardDateAttribute()
	{
		return $this->NewCardDate ? Carbon::parse($this->NewCardDate)->format('d-m-Y') : null;
	}

	public function getFormattedExpireCardAttribute()
	{
		return $this->ExpireCard ? Carbon::parse($this->ExpireCard)->format('d-m-Y') : null;
	}

	public function getFormattedBirthdayAttribute()
	{
		return $this->Birthday ? Carbon::parse($this->Birthday)->format('d-m-Y') : null;
	}

	public function getBirthdayAttribute($value)
	{
		return $value ? Carbon::parse($value)->format('Y-m-d') : null;
	}

	public function getExpireCardAttribute($value)
	{
		return $value ? Carbon::parse($value)->format('Y-m-d') : null;
	}

	public function getNewCardDateAttribute($value)
	{
		return $value ? Carbon::parse($value)->format('Y-m-d') : null;
	}

	public function addresses()
	{
		return $this->hasMany(Address::class, 'customer_id', 'id');
	}

	public function currentAddress()
	{
		return $this->hasOne(Address::class, 'customer_id', 'id')->where('type', 'current');
	}

	public function documentAddress()
	{
		return $this->hasOne(Address::class, 'customer_id', 'id')->where('type', 'document');
	}

	public function getGenderThAttribute()
	{
		return match (strtolower($this->Gender)) {
			'female' => 'หญิง',
			'male' => 'ชาย',
			default => '-',
		};
	}

	public function getReligionThAttribute()
	{
		return match (strtolower($this->religion)) {
			'buddhist' => 'พุทธ',
			'islam' => 'อิสลาม',
			'christian' => 'คริสต์',
			'other' => 'อื่นๆ',
			default => '-',
		};
	}
}
