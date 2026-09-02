<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Models\Traits\LogsActivity;
use App\Models\Traits\PreApprovalScope;
use App\Models\Traits\TracksUserActions;
use App\Models\Traits\SaleTeamScope;
use App\Models\Traits\UserAccessScope;
use App\Models\CustomerTracking;
use App\Services\ExtraBudgetLedger;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\SoftDeletingScope;

/**
 * Class Salecar
 * 
 * @property int $id
 * @property int|null $CusID
 * @property Carbon|null $KeyInDate
 * @property int|null $SaleID
 * @property int|null $model_id
 * @property string|null $Color
 * @property int|null $CarID
 * @property int|null $SaleConsultantID
 * @property int|null $FinanceID
 * @property int|null $TurnCarID
 * @property Carbon|null $BookingDate
 * @property Carbon|null $DeliveryDate
 * @property Carbon|null $DeliveryInDMSDate
 * @property Carbon|null $DeliveryInCKDate
 * @property Carbon|null $RegistrationProvince
 * @property string|null $RedPlateReceived
 * @property float|null $RedPlateAmount
 * @property float|null $CarSalePrice
 * @property float|null $MarkupPrice
 * @property float|null $Markup90
 * @property bool $markup90_manual
 * @property float|null $CarSalePriceFinal
 * @property float|null $DownPayment
 * @property float|null $DownPaymentPercentage
 * @property float|null $DownPaymentDiscount
 * @property float|null $CashDeposit
 * @property float|null $TradeinAddition
 * @property float|null $AdditionFromCustomer
 * @property float|null $TotalPaymentatDelivery
 * @property int|null $ReferentPersonID
 * @property float|null $CashSupportFromMarkup
 * @property float|null $TotalSaleCampaign
 * @property float|null $CashSupportInterestPlus
 * @property float|null $TotalCashSupport
 * @property float|null $TotalAccessoryGift
 * @property float|null $TotalAccessoryExtra
 * @property float|null $TotalCashSupportUsed
 * @property float|null $RemainingCashSuuportShared
 * @property float|null $SCCommissionIntPlus
 * @property float|null $TradeinComAmount
 * @property float|null $CommissionDeduct
 * @property string|null $ApprovalSignature
 * @property float|null $FinanceAmount
 * @property float|null $InterestRate
 * @property int|null $InterestCampaignID
 * @property int|null $InstallmentPeriod
 * @property float|null $EXC_ALP
 * @property float|null $INC_ALP
 * @property float|null $ALPAmount
 * @property int|null $SMSignature
 * @property Carbon|null $SMCheckedDate
 * @property int|null $AdminSignature
 * @property Carbon|null $AdminCheckedDate
 * @property int|null $CheckerID
 * @property Carbon|null $CheckerCheckedDate
 * @property string|null $Note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Salecar extends Model
{
	use SoftDeletes;
	use UserAccessScope;
	use SaleTeamScope;
	use PreApprovalScope;
	use TracksUserActions;
	use LogsActivity;

	protected $table = 'salecars';

	protected $casts = [
		'attachment_url' => 'array',
		'withdraw_attachment_url' => 'array',
		'approval_files' => 'array',
		'approval_is_vip' => 'bool',
		'approval_is_deduct' => 'bool',
		'is_pre_approval' => 'bool',
		'pre_approval_at' => 'datetime',
		'pre_approval_booked_at' => 'datetime',
		'CusID' => 'int',
		'KeyInDate' => 'datetime',
		'SaleID' => 'int',
		'model_id' => 'int',
		'CarID' => 'int',
		'SaleConsultantID' => 'int',
		'FinanceID' => 'int',
		'TurnCarID' => 'int',
		'BookingDate' => 'datetime',
		'DeliveryDate' => 'datetime',
		'DeliveryInDMSDate' => 'datetime',
		'DeliveryInCKDate' => 'datetime',
		'RedPlateAmount' => 'float',
		'CarSalePrice' => 'float',
		'MarkupPrice' => 'float',
		'Markup90' => 'float',
		'markup90_manual' => 'bool',
		'CarSalePriceFinal' => 'float',
		'gp_cost_price_override' => 'float',
		'gp_accessory_cost' => 'float',
		'gp_commission_sale' => 'float',
		'DownPayment' => 'float',
		'DownPaymentPercentage' => 'float',
		'DownPaymentDiscount' => 'float',
		'CashDeposit' => 'float',
		'TradeinAddition' => 'float',
		'AdditionFromCustomer' => 'float',
		'TotalPaymentatDelivery' => 'float',
		'ReferentPersonID' => 'int',
		'CashSupportFromMarkup' => 'float',
		'CashSupportInterestPlus' => 'float',
		'TotalCashSupport' => 'float',
		'TotalAccessoryGift' => 'float',
		'TotalAccessoryExtra' => 'float',
		'TotalCashSupportUsed' => 'float',
		'RemainingCashSuuportShared' => 'float',
		'SCCommissionIntPlus' => 'float',
		'TradeinComAmount' => 'float',
		'CommissionDeduct' => 'float',
		'FinanceAmount' => 'float',
		'InterestRate' => 'float',
		'InterestCampaignID' => 'int',
		'InstallmentPeriod' => 'int',
		'EXC_ALP' => 'float',
		'INC_ALP' => 'float',
		'ALPAmount' => 'float',
		'SMSignature' => 'int',
		'SMCheckedDate' => 'datetime',
		'AdminSignature' => 'int',
		'AdminCheckedDate' => 'datetime',
		'CheckerID' => 'int',
		'CheckerCheckedDate' => 'datetime'
	];

	protected $fillable = [
		'CusID',
		'original_customer_id',
		'CarOrderID',
		'KeyInDate',
		'SaleID',
		'type',
		'type_sale',
		'model_id',
		'Color',
		'type_color',
		'gwm_color',
		'interior_color',
		'Year',
		'option',
		'payment_mode',
		'subModel_id',
		'price_sub',
		'SaleConsultantID',
		'FinanceID',
		'TurnCarID',
		'BookingDate',
		'DeliveryDate',
		'DeliveryInDMSDate',
		'DeliveryInCKDate',
		'CancelDate',
		'CancelGCIPDate',
		'RefundMotorDate',
		'RefundDate',
		'RegistrationProvince',
		'insurance_id',
		'RedPlateReceived',
		'RedPlateAmount',
		'CarSalePrice',
		'MarkupPrice',
		'Markup90',
		'markup90_manual',
		'CarSalePriceFinal',
		'gp_cost_price_override',
		'gp_accessory_cost',
		'gp_commission_sale',
		'discount',
		'DownPayment',
		'DownPaymentPercentage',
		'DownPaymentDiscount',
		'PaymentDiscount',
		'CashDeposit',
		'attachment_url',
		'withdraw_user',
		'withdraw_date',
		'withdraw_attachment_url',
		'TradeinAddition',
		'AdditionFromCustomer',
		'TotalPaymentatDelivery',
		'ReferentPersonID',
		'CashSupportFromMarkup',
		'TotalSaleCampaign',
		'balanceCampaign',
		'kickback',
		'other_cost',
		'reason_other_cost',
		'other_cost_fi',
		'reason_other_cost_fi',
		'CashSupportInterestPlus',
		'TotalCashSupport',
		'TotalAccessoryGift',
		'AccessoryGiftCom',
		'AccessoryGiftVat',
		'TotalAccessoryExtra',
		'AccessoryExtraCom',
		'AccessoryExtraVat',
		'TotalCashSupportUsed',
		'RemainingCashSuuportShared',
		'SCCommissionIntPlus',
		'TradeinComAmount',
		'CommissionSale',
		'CommissionDeduct',
		'CommissionSpecial',
		'budget_deduct',
		'ApprovalSignature',
		'ApprovalSignatureDate',
		'FinanceAmount',
		'InterestRate',
		'InterestCampaignID',
		'InstallmentPeriod',
		'EXC_ALP',
		'INC_ALP',
		'ALPAmount',
		'SMSignature',
		'SMCheckedDate',
		'AdminSignature',
		'AdminCheckedDate',
		'CheckerID',
		'CheckerCheckedDate',
		'GMApprovalSignature',
		'GMApprovalSignatureDate',
		'DeliveryEstimateDate',
		'reason_campaign',
		'Note',
		'red_license',
		'ReferrerID',
		'ReferrerAmount',
		'balance',
		'balanceFinance',
		'con_status',
		'delivered_notified_at',
		'delivery_location',
		'delivery_province',
		'approval_type',
		'approval_case',
		'is_pre_approval',
		'pre_approval_at',
		'pre_approval_booked_at',
		'approval_requested_at',
		'approval_commission_deduct',
		'approval_extra_budget',
		'approval_is_vip',
		'approval_is_deduct',
		'approval_md_note',
		'approval_return_note',
		'approval_returned_at',
		'approval_remaining',
		'approval_token',
		'approval_final_token',
		'approval_files',
		// คำขอให้ IA ตรวจสอบรายการ (แยกจากสายอนุมัติ — ไม่มีปุ่มอนุมัติในเมล)
		'ia_request_token',
		'ia_requested_at',
		'ia_requested_by',
		'userZone',
		'brand',
		'sale_team_id',
		'UserInsert',
		'UserUpdate',
		'UserDelete',
		'branch',
		'tracking_id',
		'original_tracking_id',
		'dispose_set',
		'dispose_received_date',
		'dispose_reg_withdraw_date',
		'dispose_note',
	];

	protected $dates = ['deleted_at'];

	/**
	 * snapshot ทีมขาย — เขียน sale_team_id จาก "ทีมของผู้ขาย ณ ตอนนั้น" ทุกครั้งที่ SaleID เปลี่ยน
	 *
	 * ต้องเป็น snapshot ไม่ใช่ join สดตอนทำรายงาน เพราะถ้าเซลล์ย้ายทีม ยอดย้อนหลังของทีมเดิม
	 * จะไหลตามไปทีมใหม่ทั้งก้อน (BI ที่เทียบรายปีจะเพี้ยน)
	 *
	 * วางไว้ที่ model แทนการไล่แก้ทุก controller — ครอบทั้งตอนสร้าง ตอนย้ายผู้ขาย
	 * และ write path ที่จะเพิ่มในอนาคตด้วย
	 */
	protected static function booted()
	{
		static::saving(function ($model) {
			if (!$model->isDirty('SaleID')) {
				return;
			}

			$model->sale_team_id = $model->SaleID
				? User::withoutGlobalScopes()->whereKey($model->SaleID)->value('sale_team_id')
				: null;
		});
	}

	public function saleTeam()
	{
		return $this->belongsTo(SaleTeam::class, 'sale_team_id', 'id');
	}
	public function turnCar()
	{
		return $this->belongsTo(TurnCar::class, 'TurnCarID', 'id');
	}

	// withTrashed: ลูกค้าถูกลบแล้วใบจองต้องยังขึ้นชื่อได้ ไม่ใช่ว่างเปล่า
	// (เดิมเคยมีเคสลบลูกค้าที่มีใบจองถอนจอง แล้วใบจองนั้นไม่มีชื่อลูกค้า)
	public function customer()
	{
		return $this->belongsTo(Customer::class, 'CusID', 'id')->withTrashed();
	}

	public function originalCustomer()
	{
		return $this->belongsTo(Customer::class, 'original_customer_id', 'id')->withTrashed();
	}

	public function originalTracking()
	{
		return $this->belongsTo(CustomerTracking::class, 'original_tracking_id', 'id');
	}

	public function customerReferrer()
	{
		return $this->belongsTo(Customer::class, 'ReferrerID', 'id')->withTrashed();
	}

	public function carOrder()
	{
		return $this->belongsTo(CarOrder::class, 'CarOrderID', 'id');
	}

	/**
	 * รถที่นับเป็น "ยอดขาย" (คิดคอม) — ตรงกับเงื่อนไข CarCommissionQuery
	 *  - type_sale Normal (=1)
	 *  - purchase_type Retail (=2)  → ตัด TestDrive / ActivityCar / Company
	 *  - purchase_source ไม่ใช่ OTHDealer → ตัดรถ dealer
	 */
	public function scopeSalesQualifying($query)
	{
		$table = $this->getTable();

		// brand ที่ "ขายให้ดีลเลอร์อื่น (OTHDealer) ก็ได้คอม" — ตั้งที่ config/car_commission.php
		// brand อื่นตัด dealer ออกจากคอมตามเดิม
		$dealerOkBrands = array_map('intval', (array) config('car_commission.dealer_sale_earns_commission_brands', []));

		return $query
			->where($table . '.type_sale', 1)
			->whereHas('carOrder', fn($c) => $c->withoutGlobalScopes()
				->where('purchase_type', 2))
			->where(fn($q) => $q
				->whereHas('carOrder', fn($c) => $c->withoutGlobalScopes()
					->where(fn($w) => $w->where('purchase_source', '!=', 'OTHDealer')
						->orWhereNull('purchase_source')))
				->when($dealerOkBrands, fn($w) => $w->orWhereIn($table . '.brand', $dealerOkBrands)));
	}

	public function carOrderHistories()
	{
		return $this->hasOne(CarOrderHistory::class, 'SaleID')->latest();
	}

	/**
	 * ประดับยนต์ของใบขาย — ปลด soft delete ของ master ทิ้ง
	 * รายการที่ถูกลบในหน้าตั้งค่าทีหลัง ใบเก่าที่เคยใช้ต้องยังเห็นอยู่
	 * ไม่งั้นแถวหายจากหน้าจอ แล้วโดนลบถาวรตอนกดบันทึกใบ
	 * (update() detach ทั้งหมดแล้ว attach เฉพาะที่ฟอร์มส่งมา) และยอด GP/ของแถมย้อนหลังจะเพี้ยน
	 */
	public function accessories()
	{
		return $this->belongsToMany(AccessoryPrice::class, 'saleaccessory', 'salecar_id', 'accessory_id')
			->withoutGlobalScope(SoftDeletingScope::class)
			->withPivot(['price_type', 'price', 'commission', 'cost_spare', 'type', 'note'])
			->withTimestamps();
	}

	public function campaigns()
	{
		return $this->hasMany(Salecampaign::class, 'SaleID', 'id');
	}

	public function model()
	{
		return $this->belongsTo(TbCarmodel::class, 'model_id', 'id');
	}

	public function subModel()
	{
		return $this->belongsTo(TbSubcarmodel::class, 'subModel_id', 'id');
	}

	public function conStatus()
	{
		return $this->belongsTo(TbConStatus::class, 'con_status', 'id');
	}

	public function licensePlateRed()
	{
		// ข้าม brand scope ของป้าย — งานขายอาจผูกป้ายที่ยืมมาแล้วคืนเจ้าของไปแล้ว (ใช้แสดงผลเท่านั้น)
		return $this->belongsTo(TbLicensePlate::class, 'red_license', 'id')
			->withoutGlobalScope('brandAccess');
	}

	public function type()
	{
		// withTrashed: แหล่งที่มาที่ถูกลบไปแล้ว ข้อมูล PO เดิมยังต้องแสดงชื่อได้
		return $this->belongsTo(TbSalecarType::class, 'type', 'id')->withTrashed();
	}

	public function salePurType()
	{
		return $this->belongsTo(TbSalePurchaseType::class, 'type_sale', 'id');
	}

	public function provinces()
	{
		return $this->belongsTo(TbProvinces::class, 'RegistrationProvince', 'id');
	}

	// withTrashed: ประกันที่ถูกลบไปแล้ว PO เดิมที่อ้างอิงยังต้องแสดงชื่อได้
	public function insurance()
	{
		return $this->belongsTo(Insurance::class, 'insurance_id')->withTrashed();
	}

	public function reservationPayment()
	{
		return $this->hasOne(PaymentType::class, 'saleCar_id', 'id')->where('category', 'reservation');
	}

	public function remainingPayment()
	{
		return $this->hasOne(PaymentType::class, 'saleCar_id', 'id')->where('category', 'remaining');
	}

	public function deliveryPayment()
	{
		return $this->hasOne(PaymentType::class, 'saleCar_id', 'id')->where('category', 'delivery');
	}

	public function saleUser()
	{
		return $this->belongsTo(User::class, 'SaleID', 'id');
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

	public function financeConfirm()
	{
		return $this->hasOne(FinancesConfirm::class, 'SaleID', 'id');
	}

	public function vehicleLicense()
	{
		return $this->hasOne(VehicleLicense::class, 'SaleID', 'id')->latestOfMany();
	}

	public function gwmColor()
	{
		return $this->belongsTo(TbColor::class, 'gwm_color');
	}

	public function preDeliveryInspection()
	{
		return $this->hasOne(PreDeliveryInspection::class, 'salecar_id', 'id');
	}

	public function getDisplayColorAttribute()
	{
		if (in_array($this->brand, [2, 3, 4])) {
			return $this->gwmColor?->name ?? '-';
		}

		return $this->Color ?? '-';
	}

	public function interiorColor()
	{
		return $this->belongsTo(TbInteriorColor::class, 'interior_color', 'id');
	}

	/** เคสอนุมัติที่ต้องผ่านโมดูล "ขออนุมัติเกินงบล่วงหน้า" */
	public const PRE_APPROVAL_CASES = ['b1_md', 'b2_gm'];

	/**
	 * อนุมัติแล้ว "ตรงกับข้อมูลปัจจุบัน" ไหม (mirror PurchaseOrderController::isApproved)
	 * ต้อง eager load relation 'model' เพื่อความแม่นของเคส
	 */
	public function isApprovedNow(): bool
	{
		return match ($this->approvalCase()) {
			'normal'         => (bool) $this->SMSignature,
			'b1_manager'     => (bool) $this->ApprovalSignature,
			'b1_md', 'b2_gm' => (bool) $this->GMApprovalSignature,
			default          => false,
		};
	}

	/** ต้องขออนุมัติล่วงหน้าไหม (เกินงบทะลุเพดาน / brand 2 เกินงบ) */
	public function requiresPreApproval(): bool
	{
		return in_array($this->approvalCase(), self::PRE_APPROVAL_CASES, true);
	}

	/** สถานะการจอง (ใช้ในรายงานเกินงบ) */
	public function bookingStatusLabel(): string
	{
		if ($this->is_pre_approval) {
			return 'ยังไม่จอง';
		}
		return $this->pre_approval_at ? 'จองแล้ว (จากคำขอล่วงหน้า)' : 'จองแล้ว';
	}

	/** ประเภทการขาย = Normal (tb_sale_purchase_type.id) */
	public const TYPE_SALE_NORMAL = 1;

	/** ประเภทการขาย = Test Drive (tb_sale_purchase_type.id) */
	public const TYPE_SALE_TEST_DRIVE = 2;

	/** ประเภทการขาย = Dealer (tb_sale_purchase_type.id) → ไม่ต้องขออนุมัติงบ */
	public const TYPE_SALE_DEALER = 3;

	/**
	 * brand ที่บังคับด่าน "ตรวจสอบรายการ (IA)" ก่อนเปลี่ยนสถานะเป็น "ส่งมอบ" (con_status = 5)
	 * — GWM(2) เท่านั้น ; brand อื่นช่อง IA ยังอยู่แต่ใครก็ติ๊กได้และไม่มีด่าน
	 * เช็คผ่าน needsIaCheck() เสมอ ห้ามเทียบ brand == 2 ตรง ๆ กระจายตามที่ต่าง ๆ
	 */
	public const IA_GATE_BRAND = 2;

	public function needsIaCheck(): bool
	{
		return (int) $this->brand === self::IA_GATE_BRAND;
	}

	public function isDealerSale(): bool
	{
		return (int) $this->type_sale === self::TYPE_SALE_DEALER;
	}

	/**
	 * เคสอนุมัติ (brand-aware) — ตรรกะเดียวกับ PurchaseOrderController::approvalCase
	 *  normal     = งบปกติ
	 *  b1_manager = brand1/3 เกิน ≤ over_budget → manager (จบ)
	 *  b1_md      = brand1/3 เกิน > over_budget → manager กรอกค่าคอมที่ได้ → GM อนุมัติจบ (CC md)
	 *  b2_gm      = brand2/4 เกินงบ (ไม่มีเพดาน) → manager กรอกยอดหัก → GM อนุมัติจบ (CC md)
	 *               brand 2 เลือก "ไม่หักเงิน VIP" ได้ → ส่ง MD อนุมัติจบแทน (CC gm) — ดู approval_is_vip
	 */
	public function approvalCase(): string
	{
		$balance = (float) ($this->balanceCampaign ?? 0);
		if ($balance >= 0) {
			return 'normal';
		}
		// brand 4 : คอมงบเหลือคิดแบบ brand 2 → เกินงบวิ่งเข้าสาย GM เหมือนกัน (ไม่มีเพดาน over_budget)
		if (in_array((int) $this->brand, [2, 4], true)) {
			return 'b2_gm';
		}
		// เทียบ "ยอดเต็ม" (balanceCampaign เก็บค่าที่หาร 2 แล้ว → คูณกลับ ×2) กับเพดาน over_budget
		$overBudget = (float) ($this->model?->over_budget ?? 0);
		return abs($balance) * 2 <= $overBudget ? 'b1_manager' : 'b1_md';
	}

	/**
	 * % หักคอมเกินงบที่ใช้จริงของคันนี้ — รุ่นย่อย (tb_subcarmodels.per_budget) ทับรุ่นหลักได้
	 * เช่น Triton (รุ่นหลัก 3/4/5) = 30% แต่รุ่นย่อยเกียร์ AT = 40%
	 * ว่าง/NULL ที่รุ่นย่อย = ใช้ของรุ่นหลัก (tb_carmodels.per_budget)
	 * ต้อง eager load 'subModel' (หรือ 'carOrder.subModel') + 'model' เพื่อกัน N+1
	 */
	public function effectivePerBudget(): float
	{
		$sub = $this->subModel ?? $this->carOrder?->subModel;
		$subPer = $sub?->per_budget;

		if ($subPer !== null && $subPer !== '' && is_numeric($subPer)) {
			return (float) $subPer;
		}
		return (float) ($this->model?->per_budget ?? 0);
	}

	/**
	 * เกินงบ "ทะลุเพดาน" (ต้องให้ MD/GM อนุมัติ แล้วผู้จัดการกรอกค่าคอมให้แทน)
	 * ผลข้างเคียง: คันแบบนี้ไม่ได้ "คอมตัวรถรายคัน" (แต่ยังนับจำนวนคันตามปกติ)
	 * ต้อง eager load relation 'model' เพื่อความแม่นของเคส
	 */
	public function isOverBudgetCeiling(): bool
	{
		return in_array($this->approvalCase(), ['b1_md', 'b2_gm'], true);
	}

	/**
	 * คันนี้ได้ "คอมตัวรถรายคัน" ไหม
	 *
	 * เดิม: เกินงบทะลุเพดาน = ไม่ได้คอมตัวรถ (นับจำนวนคันแต่ไม่ได้ยอด)
	 * ใหม่: เปลี่ยนการอนุมัติเป็น "หักเงิน" แล้ว จึงไม่ตัดคอมตัวรถซ้ำอีก → ได้ทุกคัน
	 *
	 * ลำดับการตัดสิน (ทั้งหมดตั้งค่าที่ config/car_commission.php):
	 *   1. ไม่เกินเพดาน → ได้เสมอ
	 *   2. อยู่ในรายการยกเว้นรายคัน (over_ceiling_car_commission_ids) → ได้
	 *   3. เทียบ DeliveryInCKDate กับวันตัดของแบรนด์ (from_brand) ถ้าไม่ตั้งไว้ใช้วันตัดกลาง (from)
	 *      คันเก่าก่อนวันตัดจ่ายคอมไปแล้ว ห้ามขยับย้อนหลัง
	 *
	 * ต้อง eager load 'model' (ใช้ใน isOverBudgetCeiling) และ select DeliveryInCKDate มาด้วย
	 */
	public function earnsCarCommission(): bool
	{
		if (!$this->isOverBudgetCeiling()) {
			return true;
		}
	
		// ยกเว้นรายคันที่ตกลงกันไว้ (เช่น คันที่โดนหักเงินไปแล้วแต่ CK อยู่ก่อนวันตัด)
		$exceptIds = (array) config('car_commission.over_ceiling_car_commission_ids', []);
		if (in_array((int) $this->id, array_map('intval', $exceptIds), true)) {
			return true;
		}
	
		// ใส่ key ของ brand ไว้แล้วเป็น null = "แบรนด์นี้ไม่มีวันตัด" (ห้ามใช้ ?? เพราะ null จะตกไปใช้ค่ากลาง)
		$perBrand = (array) config('car_commission.over_ceiling_car_commission_from_brand', []);
		$from = array_key_exists((int) $this->brand, $perBrand)
			? $perBrand[(int) $this->brand]
			: config('car_commission.over_ceiling_car_commission_from');
	
		if ($from === null || $from === '') {
			return true;   // ไม่ตั้งวันตัด = ใช้กติกาใหม่กับทุกคัน
		}
		if (!$this->DeliveryInCKDate) {
			return false;  // ยังไม่มีวัน CK = ยังไม่เข้ารอบคอมอยู่แล้ว
		}
	
		return Carbon::parse($this->DeliveryInCKDate)->startOfDay()
			->gte(Carbon::parse($from)->startOfDay());
	}
	
	/**
	 * คันนี้ใช้ "ยอดที่ผู้จัดการ/GM กรอก" แทนสูตรคอมงบเหลือหรือไม่
	 * (เคสเกิน over_budget ที่ MD/GM อนุมัติ แล้ว manager กรอกยอด D มาแล้ว)
	 */
	public function usesApprovedCommission(): bool
	{
		return $this->approval_commission_deduct !== null
			&& in_array($this->approvalCase(), ['b1_md', 'b2_gm'], true);
	}

	/** % ที่ใช้คิด "ยอดหักแนะนำ" ของเคสเกินเพดาน — เกินงบยอดเต็ม × % นี้ (แก้ตัวเลขที่เดียวจบ) */
	public const OVER_BUDGET_DEDUCT_PERCENT = 10;
	
	/**
	 * ยอดหักแนะนำที่ระบบเติมให้ในหน้ากรอกของผู้จัดการ = |เกินงบยอดเต็ม| × 10%
	 * "เกินงบยอดเต็ม" = balanceCampaign × 2 (คอลัมน์เก็บค่าที่หาร 2 ไว้แล้ว) — ตัวเดียวกับบรรทัด
	 * "เกินงบ" ในไฟล์สรุปการขาย ; ผู้จัดการแก้ยอดเองได้ถ้าจะหักมากกว่านี้
	 */
	public function suggestedCommissionDeduct(): float
	{
		$balance = (float) ($this->balanceCampaign ?? 0);
		if ($balance >= 0) {
			return 0.0;
		}
		return abs($balance * 2) * (self::OVER_BUDGET_DEDUCT_PERCENT / 100);
	}
	
	/**
	 * ยอด D ที่ผู้จัดการกรอก มีความหมายเป็น "ยอดหัก" (−D) หรือ "ค่าคอมที่ได้" (+D)
	 *
	 * กติกาใหม่ (ทุก brand) : กรอกเป็น "ยอดที่ต้องหัก" เสมอ → ใบที่บันทึกใหม่ตั้งธง approval_is_deduct = 1
	 * ใบเก่าก่อนเปลี่ยนกติกา : brand 2/4 เก็บเป็นยอดหักอยู่แล้ว ส่วน brand 1/3 เก็บเป็น "ค่าคอมที่ได้" (+D)
	 * จึงต้องคงความหมายเดิมไว้ ห้ามกลับเครื่องหมายย้อนหลัง (คอมที่จ่ายไปแล้วจะเพี้ยน)
	 */
	public function usesDeductAmount(): bool
	{
		if ($this->approval_is_deduct) {
			return true;
		}
		return in_array((int) $this->brand, [2, 4], true);
	}
	
	/** ป้ายชื่อช่องยอดที่ผู้จัดการกรอก (ตามความหมายของใบนั้น) */
	public function approvalDeductLabel(): string
	{
		return $this->usesDeductAmount() ? 'ยอดหักค่าคอมฝ่ายขาย' : 'ค่าคอมฝ่ายขายที่ได้';
	}
	
	/**
	 * แบรนด์นี้ใช้ "เก็บงบเพิ่มเติม" ไหม — brand 1/3 ใช้ , brand 2/4 ไม่ใช้
	 * (แยกจาก usesDeductAmount() แล้ว เพราะตอนนี้ทุกแบรนด์กรอกเป็น "ยอดหัก" เหมือนกันหมด
	 *  แต่ช่องเก็บงบเพิ่มเติมยังมีเฉพาะ brand 1/3 ตามเดิม)
	 */
	public function usesExtraBudget(): bool
	{
		return !in_array((int) $this->brand, [2, 4], true);
	}
	
	/** ผู้จัดการเลือก "ไม่หักเงิน VIP" (เฉพาะ brand 2) → ผู้อนุมัติขั้นสุดท้ายเป็น MD แทน GM */
	/**
	 * ระบบมีคอลัมน์ approval_final_token แล้วหรือยัง
	 *
	 * กันพังกรณี deploy โค้ดก่อนรัน ALTER TABLE — ถ้ายังไม่มีคอลัมน์ ระบบจะทำงาน
	 * แบบเดิม (ลิงก์เดียวใช้ได้ทุกคน) แทนที่จะ error ทั้งสายอนุมัติ
	 * เช็คครั้งเดียวต่อ request (information_schema ยิงทีเดียวพอ)
	 */
	public static function hasFinalTokenColumn(): bool
	{
		static $has = null;

		return $has ??= Schema::hasColumn('salecars', 'approval_final_token');
	}

	public function isVipApproval(): bool
	{
		return (bool) $this->approval_is_vip;
	}
	
	/** แบรนด์นี้ให้ผู้จัดการเลือก VIP ได้ไหม — ตอนนี้เฉพาะ brand 2 */
	public function allowsVipChoice(): bool
	{
		return (int) $this->brand === 2 && $this->approvalCase() === 'b2_gm';
	}
	
	/** ผู้อนุมัติขั้นสุดท้ายของเคสเกินเพดาน — VIP → MD, นอกนั้น → GM */
	public function finalApproverRole(): string
	{
		return $this->isVipApproval() ? 'md' : 'gm';
	}
	
	/**
	 * "คอมที่ได้ / ยอดหักค่าคอม" — ยอด D ที่ผู้จัดการกรอกตอนอนุมัติเกินเพดาน
	 *  · brand 2, 4 → −D (หักเงิน ; VIP = กรอก 0 → ไม่หัก ไม่ได้)
	 *  · brand 1, 3 → +D ("ให้ค่าคอมฝ่ายขายเท่านี้แทน")
	 * เคสอื่น (ยังไม่กรอก / ไม่ใช่เคสเกินเพดาน) = 0 → ไปคิดจากสูตรใน autoBalanceCommission แทน
	 */
	public function approvedCommission(): float
	{
		if (!$this->usesApprovedCommission()) {
			return 0.0;
		}
		$d = (float) $this->approval_commission_deduct;
		return $this->usesDeductAmount() ? -1 * $d : $d;
	}

	/**
	 * "คอมงบเหลือ" ล้วน ๆ (สูตรอัตโนมัติ) — ไม่รวมยอดที่ผู้จัดการกรอก (ดู approvedCommission)
	 *  - งบเหลือ (balance ≥ 0): ได้งบเหลือ เพดาน 2500 (brand 2/4 = 0)
	 *  - เกินงบไม่เกินเพดาน (b1_manager): สูตรอัตโนมัติ balance × 2 × per_budget% (ติดลบ)
	 *  - เกินเพดานแต่ยังไม่อนุมัติ: ยอดชั่วคราว balance × 2 × 10% (เท่ากับยอดหักแนะนำ)
	 *  - เคสที่ใช้ยอดผู้จัดการแล้ว (b1_md / b2_gm) → 0 (ยอดไปอยู่ที่ approvedCommission)
	 * ต้อง eager load relation 'model' เพื่อความแม่นของเคส
	 */
	public function autoBalanceCommission(): float
	{
		if ($this->usesApprovedCommission()) {
			return 0.0;
		}

		$balance = (float) ($this->balanceCampaign ?? 0);

		if ($balance >= 0) {
			// brand 2, 4 : งบเหลือไม่คิดเป็นค่าคอมเซลล์ → 0 (จะได้คอมจากส่วนนี้เฉพาะตอนเกินงบ = −D ที่ GM อนุมัติ)
			if (in_array((int) $this->brand, [2, 4], true)) {
				return 0.0;
			}
			// เคสงบปกติ: หัก "เก็บงบเพิ่มเติม" (running deduction) จากงบเต็มก่อน แล้วค่อยหาร 2 + เพดาน 2500
			$full     = $balance * 2;
			$absorbed = ExtraBudgetLedger::absorbedFor($this);
			return min(max(0.0, $full - $absorbed) / 2, 2500);
		}

		// เกินงบ (balance < 0) — ถึงตรงนี้แปลว่ายังไม่มียอดที่ผู้จัดการอนุมัติ (ยอดชั่วคราว "รอยอดอนุมัติ")
		//  · ทะลุเพดาน (b1_md / b2_gm) : พรีวิวด้วยกติกาใหม่ 10% ให้ตรงกับยอดหักแนะนำที่ผู้จัดการจะกรอก
		//  · ไม่ทะลุเพดาน (b1_manager) : ผู้จัดการอนุมัติจบเอง ไม่มีหน้ากรอกยอด → คงสูตรเดิม per_budget%
		//    (รุ่นย่อย AT ทับรุ่นหลักเป็น 40% ได้)
		$percent = $this->isOverBudgetCeiling()
			? self::OVER_BUDGET_DEDUCT_PERCENT
			: $this->effectivePerBudget();
		return $balance * 2 * ($percent / 100);
	}

	/**
	 * คอมงบเหลือรวม (สูตรอัตโนมัติ + ยอดที่ผู้จัดการอนุมัติ) — ใช้กับรายงาน/ยอดรวมที่ยังนับเป็นก้อนเดียว
	 * หน้าจอที่ต้องแยกช่อง ให้ใช้ autoBalanceCommission() + approvedCommission()
	 */
	public function effectiveBalanceCommission(): float
	{
		return $this->autoBalanceCommission() + $this->approvedCommission();
	}

	/**
	 * "คอมประดับยนต์" = ของแถม (gift) + ซื้อเพิ่ม (extra)
	 *
	 * 2026-09-02 (ตามที่ผู้ใช้สั่ง): แยกกติกาสองก้อน
	 *  - ซื้อเพิ่ม (AccessoryExtraCom) : ลูกค้าจ่ายเงินซื้อเอง = เป็นยอดขายจริง → ได้คอม "ทุกกรณี"
	 *    แม้ใบนั้นจะเกินงบ (เดิมโดนตัดทิ้งไปด้วย ทั้งที่ไม่ได้ใช้งบบริษัท)
	 *  - ของแถม (AccessoryGiftCom) : จ่ายจากงบ → เกินงบ (balance < 0) แล้วไม่ได้คอม เหมือนเดิม
	 */
	public function effectiveAccessoryCommission(): float
	{
		$extra = (float) ($this->AccessoryExtraCom ?? 0);

		if ((float) ($this->balanceCampaign ?? 0) < 0) {
			return $extra;
		}

		return (float) ($this->AccessoryGiftCom ?? 0) + $extra;
	}

	/**
	 * "คอมอื่นๆ" (CommissionSpecial) — ถ้ายังไม่กรอก (=0) ใช้ค่า default ตามรุ่นหลักของ brand นั้น
	 * (config: car_commission.special_by_model[brand][model_id])
	 */
	public function effectiveSpecialCommission(): float
	{
		$stored = (float) ($this->CommissionSpecial ?? 0);
		if ($stored != 0.0) {
			return $stored;
		}
		return (float) config("car_commission.special_by_model.{$this->brand}.{$this->model_id}", 0);
	}

	/**
	 * "budget หัก" (brand 2) — งบจากเดือนก่อน (กระเป๋าตังค์) ที่ admin กรอกมากลบคันนี้ตอนคอมติดลบ
	 * บวกเข้ารวมค่าคอมรถ (−3000 + budget 3000 = 0) ; brand อื่นไม่ใช้ (คืน 0)
	 */
	public function effectiveBudgetDeduct(): float
	{
		return (int) $this->brand === 2 ? (float) ($this->budget_deduct ?? 0) : 0.0;
	}

	/**
	 * รวมค่าคอม Sale (คิดสด) = คอมงบเหลือ + คอมประดับยนต์(gift+extra) + คอมดอกเบี้ย + คอมรถเทิร์น + คอมอื่นๆ
	 *                        + budget หัก (brand 2 : งบเดือนก่อนที่เอามากลบคันติดลบ)
	 * ตรงกับสูตรใน purchase-order.js: calculateCommissionSale()
	 * หมายเหตุ: เกินงบ → คอมประดับยนต์เป็น 0 (ผ่าน effectiveAccessoryCommission)
	 */
	public function effectiveCommissionSale(): float
	{
		$fiCom    = (float) ($this->remainingPayment->total_com ?? 0);
		$turnCom  = (float) ($this->turnCar->com_turn ?? 0);

		// หมายเหตุ: budget หัก (brand 2) ไม่เข้าสูตรนี้ — เป็นการ "ใช้งบ" ที่ไปหักกระเป๋า budget ยกมา
		// ของเดือนนั้น (ดู BudgetWallet::used) แล้วมีผลกับเงินเซลล์ผ่านโบนัส 30% ของงบที่เหลือแทน
		// 2026-09-02: เดิมบวก effectiveBudgetDeduct() เข้ามาด้วย ทำให้กรอก budget หัก แล้วคอมของคันนั้นเพิ่มขึ้นเอง
		return $this->effectiveBalanceCommission()
			+ $this->effectiveAccessoryCommission()
			+ $fiCom + $turnCom + $this->effectiveSpecialCommission();
	}

	public function branchInfo()
	{
		return $this->belongsTo(TbBranch::class, 'branch', 'id');
	}

	public function getBookingDateAttribute($value)
	{
		return $value ? Carbon::parse($value)->format('Y-m-d') : null;
	}

	public function getFormatBookingDateAttribute()
	{
		return $this->BookingDate ? Carbon::parse($this->BookingDate)->format('d-m-Y') : null;
	}

	public function getKeyInDateAttribute($value)
	{
		return $value ? Carbon::parse($value)->format('Y-m-d') : null;
	}

	public function getDeliveryDateAttribute($value)
	{
		return $value ? Carbon::parse($value)->format('Y-m-d') : null;
	}

	public function getAdminCheckedDateAttribute($value)
	{
		return $value ? Carbon::parse($value)->format('Y-m-d') : null;
	}

	public function getCheckerCheckedDateAttribute($value)
	{
		return $value ? Carbon::parse($value)->format('Y-m-d') : null;
	}

	public function getSMCheckedDateAttribute($value)
	{
		return $value ? Carbon::parse($value)->format('Y-m-d') : null;
	}

	public function getApprovalSignatureDateAttribute($value)
	{
		return $value ? Carbon::parse($value)->format('Y-m-d') : null;
	}

	public function getDeliveryInDMSDateAttribute($value)
	{
		return $value ? Carbon::parse($value)->format('Y-m-d') : null;
	}

	public function getDeliveryInCKDateAttribute($value)
	{
		return $value ? Carbon::parse($value)->format('Y-m-d') : null;
	}

	public function getGMApprovalSignatureDateAttribute($value)
	{
		return $value ? Carbon::parse($value)->format('Y-m-d') : null;
	}

	public function getFormatDmsDateAttribute()
	{
		return $this->DeliveryInDMSDate ? Carbon::parse($this->DeliveryInDMSDate)->format('d-m-Y') : null;
	}

	public function getFormatCkDateAttribute()
	{
		return $this->DeliveryInCKDate ? Carbon::parse($this->DeliveryInCKDate)->format('d-m-Y') : null;
	}

	public function getFormatAdminCheckDateAttribute()
	{
		return $this->AdminCheckedDate ? Carbon::parse($this->AdminCheckedDate)->format('d-m-Y') : null;
	}

	public function getFormatCheckerDateAttribute()
	{
		return $this->CheckerCheckedDate ? Carbon::parse($this->CheckerCheckedDate)->format('d-m-Y') : null;
	}

	public function getFormatSmDateAttribute()
	{
		return $this->SMCheckedDate ? Carbon::parse($this->SMCheckedDate)->format('d-m-Y') : null;
	}

	public function getFormatApprovalDateAttribute()
	{
		return $this->ApprovalSignatureDate ? Carbon::parse($this->ApprovalSignatureDate)->format('d-m-Y') : null;
	}

	public function getFormatGmDateAttribute()
	{
		return $this->GMApprovalSignatureDate ? Carbon::parse($this->GMApprovalSignatureDate)->format('d-m-Y') : null;
	}

	public function getFormatKeyDateAttribute()
	{
		return $this->KeyInDate ? Carbon::parse($this->KeyInDate)->format('d-m-Y') : null;
	}

	public function getFormatDeliveryDateAttribute()
	{
		return $this->DeliveryDate ? Carbon::parse($this->DeliveryDate)->format('d-m-Y') : null;
	}

	public function setDeliveryEstimateDateAttribute($value)
	{
		if ($value) {
			$this->attributes['DeliveryEstimateDate'] =
				Carbon::createFromFormat('Y-m', $value)->startOfMonth();
		}
	}

	public function getDeliveryEstimateDateMonthAttribute()
	{
		return $this->DeliveryEstimateDate
			? Carbon::parse($this->DeliveryEstimateDate)->format('Y-m')
			: null;
	}

	public function getFormatDeliveryEstimateDateAttribute()
	{
		return $this->DeliveryEstimateDate ? Carbon::parse($this->DeliveryEstimateDate)->format('m-Y') : null;
	}

	public function getFormatBookingDateSumAttribute()
	{
		return $this->BookingDate ? Carbon::parse($this->BookingDate)->format('d/m/Y') : null;
	}

	public function getFormatDeliveryDateSumAttribute()
	{
		return $this->DeliveryDate ? Carbon::parse($this->DeliveryDate)->format('d/m/Y') : null;
	}

	public function getCancelDateAttribute($value)
	{
		return $value ? Carbon::parse($value)->format('Y-m-d') : null;
	}

	public function getFormatCancelDateAttribute()
	{
		return $this->CancelDate ? Carbon::parse($this->CancelDate)->format('d-m-Y') : null;
	}

	public function getCancelGcipDateAttribute($value)
	{
		return $value ? Carbon::parse($value)->format('Y-m-d') : null;
	}

	public function getFormatCancelGcipDateAttribute()
	{
		return $this->CancelGCIPDate ? Carbon::parse($this->CancelGCIPDate)->format('d-m-Y') : null;
	}

	// ── แจ้งจำหน่าย (Floor Plan) ──
	public function getDisposeReceivedDateAttribute($value)
	{
		return $value ? Carbon::parse($value)->format('Y-m-d') : null;
	}

	public function getFormatDisposeReceivedDateAttribute()
	{
		return $this->dispose_received_date ? Carbon::parse($this->dispose_received_date)->format('d-m-Y') : null;
	}

	public function getDisposeRegWithdrawDateAttribute($value)
	{
		return $value ? Carbon::parse($value)->format('Y-m-d') : null;
	}

	public function getFormatDisposeRegWithdrawDateAttribute()
	{
		return $this->dispose_reg_withdraw_date ? Carbon::parse($this->dispose_reg_withdraw_date)->format('d-m-Y') : null;
	}
}
