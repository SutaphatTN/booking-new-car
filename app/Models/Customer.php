<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Models\Traits\TracksUserActions;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

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
	use TracksUserActions;

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
		'OriginalName',
		'IDNumber',
		'NewCardDate',
		'ExpireCard',
		'Birthday',
		'Gender',
		'Nationality',
		'religion',
		'LineID',
		'FacebookName',
		'career',
		'RelationST',
		'FirstNameofRelation',
		'LastNameofRelation',
		'PhoneofRelation',
		'Note',
		'Mobilephone1',
		'Mobilephone2',
		'salary',
		'userZone',
		'brand',
		'UserInsert',
		'UserUpdate',
		'UserDelete',
		'branch',
	];

	protected $dates = ['deleted_at'];

	protected $appends = ['formatted_id_number', 'formatted_mobile'];

	public function prefix()
	{
		return $this->belongsTo(TbPrefixname::class, 'PrefixName', 'id');
	}

	/**
	 * ค้นหาด้วยชื่อ: รองรับพิมพ์ "คำนำหน้า ชื่อ สกุล", "ชื่อ สกุล" หรือเฉพาะชื่อ/สกุล
	 * แตกคำค้นเป็นคำย่อยด้วยช่องว่าง แล้วแต่ละคำต้องตรงกับ คำนำหน้า/ชื่อ/ชื่อกลาง/สกุล อย่างใดอย่างหนึ่ง (AND ระหว่างคำ)
	 */
	public function scopeSearchFullName($query, ?string $term)
	{
		$term = trim((string) $term);
		if ($term === '') {
			return $query;
		}

		$tokens = preg_split('/\s+/', $term, -1, PREG_SPLIT_NO_EMPTY);

		return $query->where(function ($outer) use ($tokens) {
			foreach ($tokens as $token) {
				$outer->where(function ($q) use ($token) {
					$q->where('FirstName', 'like', "%{$token}%")
						->orWhere('LastName', 'like', "%{$token}%")
						->orWhere('MiddleName', 'like', "%{$token}%")
						->orWhereHas('prefix', fn($p) => $p->where('Name_TH', 'like', "%{$token}%"));
				});
			}
		});
	}

	public function salecars()
	{
		return $this->hasMany(Salecar::class, 'CusID', 'id');
	}

	public function salecarsRef()
	{
		return $this->hasMany(Salecar::class, 'ReferrerID', 'id');
	}

	public function userInsert()
	{
		return $this->belongsTo(User::class, 'UserInsert', 'id');
	}

	public function userUpdate()
	{
		return $this->belongsTo(User::class, 'UserUpdate', 'id');
	}

	public function userDelete()
	{
		return $this->belongsTo(User::class, 'UserDelete', 'id');
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
