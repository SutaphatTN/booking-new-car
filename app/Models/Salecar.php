<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Models\Traits\UserAccessScope;
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

	protected $table = 'salecars';

	protected $casts = [
		'attachment_url' => 'array',
		'withdraw_attachment_url' => 'array',
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
		'RedPlateReceived',
		'RedPlateAmount',
		'CarSalePrice',
		'MarkupPrice',
		'Markup90',
		'CarSalePriceFinal',
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
		'approval_type',
		'approval_requested_at',
		'userZone',
		'brand',
		'UserInsert',
		'branch'
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

	public function customerReferrer()
	{
		return $this->belongsTo(Customer::class, 'ReferrerID', 'id');
	}

	public function carOrder()
	{
		return $this->belongsTo(CarOrder::class, 'CarOrderID', 'id');
	}

	public function carOrderHistories()
	{
		return $this->hasOne(CarOrderHistory::class, 'SaleID')->latest();
	}

	public function accessories()
	{
		return $this->belongsToMany(AccessoryPrice::class, 'saleaccessory', 'salecar_id', 'accessory_id')
			->withPivot(['price_type', 'price', 'commission', 'type'])
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
		return $this->belongsTo(TbLicensePlate::class, 'red_license', 'id');
	}

	public function type()
	{
		return $this->belongsTo(TbSalecarType::class, 'type', 'id');
	}

	public function salePurType()
	{
		return $this->belongsTo(TbSalePurchaseType::class, 'type_sale', 'id');
	}

	public function provinces()
	{
		return $this->belongsTo(TbProvinces::class, 'RegistrationProvince', 'id');
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

	public function getDisplayColorAttribute()
	{
		if ($this->brand == 2 || $this->brand == 3) {
			return $this->gwmColor?->name ?? '-';
		}

		return $this->Color ?? '-';
	}

	public function interiorColor()
	{
		return $this->belongsTo(TbInteriorColor::class, 'interior_color', 'id');
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
}
