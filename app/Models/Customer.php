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
		return $this->belongsTo(User::class, 'UserInsert', 'id')->withTrashed();
	}

	public function userUpdate()
	{
		return $this->belongsTo(User::class, 'UserUpdate', 'id')->withTrashed();
	}

	public function userDelete()
	{
		return $this->belongsTo(User::class, 'UserDelete', 'id')->withTrashed();
	}

	/**
	 * ล้างเลขบัตร/พาสปอร์ตให้เหลือแต่ตัวเลข-ตัวอักษร
	 *
	 * ห้ามใช้ preg_replace('/\D/') กับช่องนี้ — ลูกค้าต่างชาติใช้พาสปอร์ตที่มีตัวอักษรปน
	 * (เช่น Z7581877) การตัดตัวอักษรทิ้งจะทำให้เลขเพี้ยนแบบเงียบๆ หาไม่เจอทีหลัง
	 * ตัดได้แค่ขีด เว้นวรรค และ CR/LF ที่ติดมาตอน paste จาก Excel
	 */
	public static function normalizeIdNumber($raw): ?string
	{
		$clean = preg_replace('/[^A-Za-z0-9]/', '', (string) $raw);

		return $clean === '' ? null : strtoupper($clean);
	}

	/**
	 * ล้างค่าช่องติดต่อ (LineID / Facebook) — คืน null ถ้าเป็นค่าที่แปลว่า "ไม่มีข้อมูล"
	 *
	 * เซลชอบพิมพ์ '-' หรือ '.' แทนการเว้นว่าง ซึ่งอันตรายมากเพราะช่องพวกนี้ใช้เช็คลูกค้าซ้ำ
	 * คนแรกที่พิมพ์ '-' จะ "จอง" ค่านั้นไว้ แล้วทุกคนหลังจากนั้นจะโดนเด้งว่าเป็นลูกค้าคนเดิม
	 * ไม่ว่าจะกรอกเบอร์อะไรมาก็ตาม (เคสจริง: ลูกค้า id 7715 จอง '-' ไว้ 2026-08-06)
	 *
	 * เช็คเฉพาะค่าที่เป็นเครื่องหมายวรรคตอนล้วนๆ กับคำว่า "ไม่มี" แบบตรงตัวเท่านั้น
	 * ชื่อ Facebook สั้นๆ อย่าง "U" / "Miso" / "ฐิตา" เป็นชื่อจริง ห้ามลบทิ้ง
	 */
	public static function normalizeContactValue($raw): ?string
	{
		$value = trim((string) $raw);

		if ($value === '') {
			return null;
		}

		// เครื่องหมายวรรคตอน/สัญลักษณ์ล้วน เช่น '-' '.' '--' '_' 'ฺ'
		if (preg_match('/^[\p{P}\p{S}\p{Z}\p{M}]+$/u', $value)) {
			return null;
		}

		$noValue = ['ไม่มี', 'ไม่ระบุ', 'ไม่ทราบ', 'n/a', 'na', 'none', 'null', 'no'];

		return in_array(mb_strtolower($value), $noValue, true) ? null : $value;
	}

	public function getFormattedIdNumberAttribute()
	{
		$id = (string) $this->IDNumber;

		// ฟอร์แมต x-xxxx-xxxxx-xx-x เฉพาะบัตรประชาชนไทย 13 หลัก
		// พาสปอร์ต/เลขรูปแบบอื่นคืนค่าดิบ ไม่งั้นจะถูกหั่นเป็นท่อนมั่ว
		if (!preg_match('/^\d{13}$/', $id)) {
			return $id ?: '-';
		}

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

	// orderByDesc('id') กันกรณีมีที่อยู่ซ้ำหลายแถว ให้หยิบแถวล่าสุดเสมอ ไม่ปล่อยให้ DB เลือกเอง
	public function currentAddress()
	{
		return $this->hasOne(Address::class, 'customer_id', 'id')
			->where('type', 'current')
			->orderByDesc('id');
	}

	public function documentAddress()
	{
		return $this->hasOne(Address::class, 'customer_id', 'id')
			->where('type', 'document')
			->orderByDesc('id');
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
