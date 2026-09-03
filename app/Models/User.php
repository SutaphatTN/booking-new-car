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
use Illuminate\Support\Facades\Auth;

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
		'userZone',
		'sale_team_id'
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

		// คืนค่า home ได้เฉพาะ "แถวของคนที่ล็อกอินอยู่ ตอน update" เท่านั้น
		//  - ตอน create ยังไม่มีค่า original → คืนค่าแล้วได้ null (เคยทำให้สมัคร user ใหม่ตอนสลับ brand แล้ว brand หาย)
		//  - แถวของ user คนอื่น middleware ไม่เคยเขียนทับ → admin ต้องแก้ brand ให้คนอื่นได้ แม้ตัวเองกำลังสลับ brand อยู่
		$guarded = $this->exists && Auth::check() && (int) Auth::id() === (int) $this->getKey()
			? ['brand' => 'brand_switch', 'branch' => 'branch_switch']
			: [];

		foreach ($guarded as $col => $sessionKey) {
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

	/**
	 * ทีมขายที่สังกัด — "หน่วยทำยอด" แยกจาก brand/branch
	 * (คนละตัวกับ current_team_id ของ Jetstream ที่ระบบไม่ได้ใช้)
	 */
	public function saleTeam()
	{
		return $this->belongsTo(SaleTeam::class, 'sale_team_id', 'id');
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

	/** role ที่ย้ายลูกค้าไปให้เซลล์คนอื่นได้ (แก้ผู้ขายบนใบจอง + บนการติดตาม) */
	public const REASSIGN_SALE_ROLES = ['admin', 'manager', 'audit', 'audit_lead', 'audit_dp', 'gm'];

	public function canReassignSale(): bool
	{
		return in_array($this->role, self::REASSIGN_SALE_ROLES, true);
	}

	/**
	 * role ที่แก้ "แหล่งที่มา / สถานที่ / คลิปที่ยิงแอด" ของการติดตามย้อนหลังได้
	 * แยก const เพราะเป็นการแก้ที่กระทบรายงาน (ยอด PP รายสถานที่ / การนับผลแอด)
	 * ใส่ adminPage ด้วยเพราะเป็นคนคีย์ลูกค้า online และเป็นคนเดียวที่เลือกคลิปแอดตอนสร้าง
	 */
	public const EDIT_TRACKING_SOURCE_ROLES = ['admin', 'adminPage', 'manager', 'audit', 'audit_lead', 'audit_dp', 'gm', 'md', 'sale', 'lead_sale'];

	/**
	 * role ที่แก้ได้เฉพาะการติดตาม "ของตัวเอง" (sale_id = ตัวเอง)
	 * เซลล์เห็นการติดตามทั้งสาขาตาม userAccess scope ถ้าไม่จำกัดตรงนี้จะไปแก้แหล่งที่มาของเพื่อนได้
	 * เอาออกได้โดยทำ const นี้เป็น [] ถ้าอยากให้เซลล์แก้ของใครก็ได้
	 */
	public const EDIT_TRACKING_SOURCE_OWN_ONLY_ROLES = ['sale', 'lead_sale'];

	public function canEditTrackingSource(): bool
	{
		return in_array($this->role, self::EDIT_TRACKING_SOURCE_ROLES, true);
	}

	public function editsTrackingSourceOwnOnly(): bool
	{
		return in_array($this->role, self::EDIT_TRACKING_SOURCE_OWN_ONLY_ROLES, true);
	}

	/** เลือก "คลิปที่ยิงแอด" ได้ — ตรงกับหน้าเพิ่มการติดตามที่เปิดช่องนี้ให้เฉพาะสองคนนี้ */
	public const EDIT_TRACKING_AD_ROLES = ['admin', 'adminPage'];

	public function canEditTrackingAd(): bool
	{
		return in_array($this->role, self::EDIT_TRACKING_AD_ROLES, true);
	}

	/**
	 * role ที่แก้ "ราคารถ" (price_sub) ในหน้าใบจองได้
	 * แยก const จาก REASSIGN_SALE_ROLES แม้รายชื่อจะตรงกัน เพราะเป็นคนละสิทธิ์
	 * แก้อันนึงต้องไม่ลากอีกอันไปด้วย
	 */
	public const EDIT_CAR_PRICE_ROLES = ['admin', 'manager', 'audit', 'audit_lead', 'audit_dp', 'gm'];

	public function canEditCarPrice(): bool
	{
		return in_array($this->role, self::EDIT_CAR_PRICE_ROLES, true);
	}

	/**
	 * role ที่ "ห้าม" เห็นราคาทุนรถ (car_DNP บนคลังรถ/ใบจอง และ dnp บน Price List)
	 * มติ 2026-08-29 : ผู้จัดการดูราคาขาย/ส่วนลดได้ แต่ไม่ให้เห็นทุนรถทุกช่องทาง
	 * ใช้คู่กับการกันฝั่ง server — ซ่อนช่องแล้วค่าจะไม่ถูกส่งกลับมา ต้องคงค่าเดิมไว้เสมอ
	 */
	public const HIDE_CAR_COST_ROLES = ['manager'];

	public function canViewCarCost(): bool
	{
		return !in_array($this->role, self::HIDE_CAR_COST_ROLES, true);
	}

	/** role ที่กำหนด "ป้ายแดง" ให้ใบจองได้ (ทั้งในหน้าใบจอง และหน้าประวัติที่ส่งมอบแล้ว) */
	public const RED_PLATE_ROLES = ['admin', 'audit', 'audit_lead', 'audit_dp', 'gm', 'manager', 'md'];

	public function canManageRedPlate(): bool
	{
		return in_array($this->role, self::RED_PLATE_ROLES, true);
	}

	/**
	 * role ที่ติ๊ก "ตรวจสอบรายการ (IA)" ได้
	 * เป็นด่านบังคับก่อนเปลี่ยนสถานะเป็น "ส่งมอบ" (con_status = 5)
	 * role อื่นยังเห็นการ์ดใบนี้ได้ แต่ติ๊กไม่ได้ (ช่องถูก disable ไว้)
	 */
	public const IA_CHECK_ROLES = ['admin', 'gm', 'md'];

	public function canIaCheck(): bool
	{
		return in_array($this->role, self::IA_CHECK_ROLES, true);
	}

	/**
	 * role ที่แก้ "ลายเซ็นอนุมัติ" 3 ตัวบนใบจองด้วยมือได้
	 * (SMSignature / ApprovalSignature / GMApprovalSignature)
	 * ทางปกติของการอนุมัติคือกดลิงก์ในอีเมล — สวิตช์พวกนี้เป็นทางลัดไว้แก้เคสที่หลุด
	 * role อื่นยังเห็นการ์ดอยู่ แต่ช่องถูก disable และ server ไม่รับค่าจากฟอร์ม (คงค่าใน DB ไว้)
	 * ไม่รวม manager: เป็นคนเซ็นเองในสายอนุมัติ ถ้าติ๊กเองได้จะข้ามด่าน GM/MD ได้
	 */
	public const APPROVAL_SIGNATURE_ROLES = ['admin', 'gm', 'md'];

	public function canEditApprovalSignature(): bool
	{
		return in_array($this->role, self::APPROVAL_SIGNATURE_ROLES, true);
	}

	/**
	 * role ที่ผูก/ปลดรถ (CarOrderID) บนใบจองของ brand 2 ได้
	 * brand 2 คุมการจ่ายรถจากส่วนกลาง — role อื่นเห็นข้อมูลรถได้แต่แตะไม่ได้
	 */
	public const BIND_CAR_ORDER_ROLES_BRAND2 = ['md', 'gm', 'admin', 'audit', 'manager'];

	/**
	 * ผูก/ปลดรถบนใบจองของ brand นี้ได้ไหม
	 * brand อื่นใช้เกณฑ์เดิม — ทุก role ที่เห็น section ยกเว้น sale/adminPage
	 * (sale/adminPage ไม่เห็น section ข้อมูล Car Order ในฟอร์ม → ไม่ส่ง CarOrderID กลับมา
	 *  ต้อง false เพื่อให้ตอนบันทึกคงค่าเดิม ไม่งั้นรถที่ผูกไว้หลุด)
	 */
	public function canBindCarOrder(int $brand): bool
	{
		if ($brand === 2) {
			return in_array($this->role, self::BIND_CAR_ORDER_ROLES_BRAND2, true);
		}

		return !in_array($this->role, ['sale', 'adminPage'], true);
	}

	/**
	 * เซลล์ที่เลือกได้ของ brand นี้ (sale_pool ระดับ brand + สิทธิ์ขายราย user)
	 * $includeId = เซลล์เจ้าของงานเดิม ให้ติดมาด้วยเสมอ กันหลุดจาก dropdown
	 * ตอนเจ้าตัวย้ายแบรนด์หรือเปลี่ยน role ไปแล้ว
	 */
	public static function salePoolForBrand(int $brand, ?int $includeId = null)
	{
		$saleBrands   = config("brand.sale_pool.$brand", [$brand]);
		$extraSaleIds = self::extraSaleUserIdsForBrand($brand);

		return self::where(function ($q) use ($saleBrands, $extraSaleIds, $includeId) {
			$q->where(function ($qq) use ($saleBrands, $extraSaleIds) {
				$qq->whereIn('role', ['sale', 'lead_sale'])
					->where(function ($qqq) use ($saleBrands, $extraSaleIds) {
						$qqq->whereIn('brand', $saleBrands)
							->orWhereIn('id', $extraSaleIds);
					});
			});

			if ($includeId) {
				$q->orWhere('id', $includeId);
			}
		})
			->orderBy('name')
			->get();
	}
}
