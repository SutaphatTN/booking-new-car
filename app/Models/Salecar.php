<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Models\Traits\LogsActivity;
use App\Models\Traits\PreApprovalScope;
use App\Models\Traits\TracksUserActions;
use App\Models\Traits\UserAccessScope;
use App\Models\CustomerTracking;
use App\Services\ExtraBudgetLedger;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
	use PreApprovalScope;
	use TracksUserActions;
	use LogsActivity;

	protected $table = 'salecars';

	protected $casts = [
		'attachment_url' => 'array',
		'withdraw_attachment_url' => 'array',
		'approval_files' => 'array',
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
		'approval_md_note',
		'approval_return_note',
		'approval_returned_at',
		'approval_remaining',
		'approval_token',
		'approval_files',
		'userZone',
		'brand',
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

	public function turnCar()
	{
		return $this->belongsTo(TurnCar::class, 'TurnCarID', 'id');
	}

	public function customer()
	{
		return $this->belongsTo(Customer::class, 'CusID', 'id');
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
		return $this->belongsTo(Customer::class, 'ReferrerID', 'id');
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
		return $query
			->where($this->getTable() . '.type_sale', 1)
			->whereHas('carOrder', fn($c) => $c->withoutGlobalScopes()
				->where('purchase_type', 2)
				->where(fn($q) => $q->where('purchase_source', '!=', 'OTHDealer')
					->orWhereNull('purchase_source')));
	}

	public function carOrderHistories()
	{
		return $this->hasOne(CarOrderHistory::class, 'SaleID')->latest();
	}

	public function accessories()
	{
		return $this->belongsToMany(AccessoryPrice::class, 'saleaccessory', 'salecar_id', 'accessory_id')
			->withPivot(['price_type', 'price', 'commission', 'cost_spare', 'type'])
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

	public function isDealerSale(): bool
	{
		return (int) $this->type_sale === self::TYPE_SALE_DEALER;
	}

	/**
	 * เคสอนุมัติ (brand-aware) — ตรรกะเดียวกับ PurchaseOrderController::approvalCase
	 *  normal     = งบปกติ
	 *  b1_manager = brand1/3 เกิน ≤ over_budget → manager (จบ)
	 *  b1_md      = brand1/3 เกิน > over_budget → manager กรอกหัก → md
	 *  b2_gm      = brand2/4 เกินงบ → gm/md (brand 4 คิดคอมงบเหลือแบบ brand 2)
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
	 * "คอมงบเหลือ" (component เดียวของค่าคอม) — คิดสดจากสถานะปัจจุบัน
	 *  - งบเหลือ (balance ≥ 0): ได้งบเหลือ เพดาน 2500
	 *  - เกินงบไม่เกินเพดาน (b1_manager): สูตรอัตโนมัติ balance × 2 × per_budget%
	 *  - เกิน over_budget ที่ MD/GM อนุมัติ และ manager กรอกยอด D แล้ว:
	 *      · brand 2, 4 (b2_gm) → ใช้ −D (หักเงิน)
	 *      · แบรนด์อื่น (b1_md) → ใช้ +D ("ให้ค่าคอมฝ่ายขายเท่านี้แทน")
	 * ต้อง eager load relation 'model' เพื่อความแม่นของเคส
	 */
	public function effectiveBalanceCommission(): float
	{
		$balance = (float) ($this->balanceCampaign ?? 0);
		$case = $this->approvalCase();

		// brand 2 และ 4 : เกินงบใช้ −D (หักเงิน) ; brand 1/3 : ใช้ +D (ให้ค่าคอมเท่านี้แทน)
		if (in_array($case, ['b1_md', 'b2_gm'], true) && $this->approval_commission_deduct !== null) {
			$d = (float) $this->approval_commission_deduct;
			return in_array((int) $this->brand, [2, 4], true) ? -1 * $d : $d;
		}

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

		// เกินงบ (balance < 0): brand 2 คูณ per_budget (30%) → เคสเกินงบ ส่งเมล GM แล้วใช้ −D ที่อนุมัติ (บล็อกบนสุด)
		$perBudget = (float) ($this->model?->per_budget ?? 0);
		return $balance * 2 * ($perBudget / 100);
	}

	/**
	 * "คอมประดับยนต์" (gift + extra) — เกินงบทุกกรณี (balance < 0) ไม่คิด (คืน 0)
	 */
	public function effectiveAccessoryCommission(): float
	{
		if ((float) ($this->balanceCampaign ?? 0) < 0) {
			return 0.0;
		}
		return (float) ($this->AccessoryGiftCom ?? 0) + (float) ($this->AccessoryExtraCom ?? 0);
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

		return $this->effectiveBalanceCommission()
			+ $this->effectiveAccessoryCommission()
			+ $fiCom + $turnCom + $this->effectiveSpecialCommission()
			+ $this->effectiveBudgetDeduct();
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
