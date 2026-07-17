<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class User
 * 
 * @property int $id
 * @property string $name
 * @property string|null $email
 * @property string|null $username
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $password_de
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property int|null $current_team_id
 * @property string|null $profile_photo_path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class User extends Authenticatable
{
	use Notifiable;
	use SoftDeletes;

	protected $casts = [
		'email_verified_at' => 'datetime',
		'two_factor_confirmed_at' => 'datetime',
		'current_team_id' => 'int'
	];

	protected $hidden = [
		'password',
		'two_factor_secret',
		'remember_token'
	];

	protected $fillable = [
		'name',
		'email',
		'username',
		'email_verified_at',
		'role',
		'role_display',
		'branch',
		'brand',
		'cardID',
		'password',
		'password_plain',
		'two_factor_secret',
		'two_factor_recovery_codes',
		'two_factor_confirmed_at',
		'remember_token',
		'current_team_id',
		'profile_photo_path',
		'phone',
		'userZone'
	];

	protected $dates = ['deleted_at'];

	/**
	 * กันบั๊ก: BrandSwitcher/BranchSwitcher เขียนทับ $user->brand/$user->branch ที่ runtime (จาก session สลับ)
	 * แล้ว Laravel เผลอ save() ทั้ง model ตอน cycle remember-token (เช่นตอน logout / re-login ด้วย remember cookie)
	 * ทำให้ brand/branch ที่สลับชั่วคราวถูกเขียนลง DB ถาวร ทั้งที่ไม่มีใครตั้งใจแก้
	 *
	 * ถ้ากำลัง save ระหว่างมี session สลับอยู่ ให้คืนค่า home (original) ก่อนเขียน แล้วคืนค่า effective ให้ request ที่เหลือ
	 */
	public function save(array $options = [])
	{
		$restore = [];

		foreach (['brand' => 'brand_switch', 'branch' => 'branch_switch'] as $col => $sessionKey) {
			if ($this->isDirty($col) && session()->has($sessionKey)) {
				$restore[$col] = $this->attributes[$col];
				$this->attributes[$col] = $this->getOriginal($col);
			}
		}

		$result = parent::save($options);

		foreach ($restore as $col => $effectiveValue) {
			$this->attributes[$col] = $effectiveValue;
		}

		return $result;
	}

	public function getFormatCardIdAttribute()
	{
		$id = $this->cardID;
		return substr($id, 0, 1) . '-' . substr($id, 1, 4) . '-' . substr($id, 5, 5) . '-' . substr($id, 10, 2) . '-' . substr($id, 12, 1);
	}

	public function branchInfo()
	{
		return $this->belongsTo(TbBranch::class, 'branch', 'id');
	}

	public function brandInfo()
	{
		return $this->belongsTo(TbBrand::class, 'brand', 'id');
	}

	public function getUserZoneNameAttribute()
	{
		$zones = [
			10 => 'ปัตตานี',
			40 => 'กระบี่',
		];

		return $zones[$this->userZone] ?? '-';
	}

	public function getFormattedPhoneAttribute()
	{
		$mobile = $this->phone;

		if (empty($mobile)) {
			return '-';
		}

		return substr($mobile, 0, 3) . '-' . substr($mobile, 3, 4) . '-' . substr($mobile, 7, 3);
	}

	/**
	 * รายการ brand id ที่ user นี้ "สลับไปได้" (ใช้ที่ปุ่มสลับ navbar + กันใน BrandSwitcher middleware)
	 *  - admin/gm/md/account/registration/adminPage → ทุก brand
	 *  - marketing/cro/sp/bp/cs/lead_sale           → ทุก brand ยกเว้น GWM(2)
	 *  - sale/audit/manager                         → ตาม config brand.sale_switch_scope[home brand]
	 * ใช้ home brand (getOriginal) เสมอ เพราะ BrandSwitcher เขียนทับ $this->brand ตอน runtime
	 */
	public function switchableBrandIds(): array
	{
		$all = array_map('intval', array_keys(config('brand.names', [])));

		if (in_array($this->role, ['admin', 'gm', 'md', 'account', 'registration', 'adminPage', 'audit_lead', 'audit_dp', 'audit_internal'], true)) {
			$base = $all;
		} elseif (in_array($this->role, ['sale', 'audit', 'manager'], true)) {
			$home = (int) $this->getOriginal('brand');
			$base = array_map('intval', config("brand.sale_switch_scope.$home", [$home]));
		} else {
			// marketing/cro/sp/bp/cs/lead_sale และอื่นๆ → ทุก brand ยกเว้น GWM(2)
			$base = array_values(array_diff($all, [2]));
		}

		// สิทธิ์ขาย brand เสริม "ราย user" (config brand.sale_switch_extra[user id])
		$extra = array_map('intval', (array) config("brand.sale_switch_extra.{$this->id}", []));

		return array_values(array_unique(array_merge($base, $extra)));
	}

	/**
	 * user id ที่ได้สิทธิ์ขาย brand นี้ "แบบราย user" (config brand.sale_switch_extra)
	 * ใช้เสริม dropdown เซลล์ตอนทำงานใต้ brand นั้น (คู่กับ sale_pool ที่เป็นระดับ brand)
	 */
	public static function extraSaleUserIdsForBrand(int $brand): array
	{
		$ids = [];
		foreach (config('brand.sale_switch_extra', []) as $userId => $brands) {
			if (in_array($brand, array_map('intval', (array) $brands), true)) {
				$ids[] = (int) $userId;
			}
		}
		return $ids;
	}
}
