<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

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

	protected $table = 'salecars';

	protected $casts = [
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
		'model_id',
		'Color',
		'Year',
		'option',
		'payment_mode',
		'subModel_id',
		'SaleConsultantID',
		'FinanceID',
		'TurnCarID',
		'BookingDate',
		'DeliveryDate',
		'DeliveryInDMSDate',
		'DeliveryInCKDate',
		'RegistrationProvince',
		'RedPlateReceived',
		'RedPlateAmount',
		'CarSalePrice',
		'MarkupPrice',
		'Markup90',
		'CarSalePriceFinal',
		'DownPayment',
		'DownPaymentPercentage',
		'DownPaymentDiscount',
		'PaymentDiscount',
		'CashDeposit',
		'TradeinAddition',
		'AdditionFromCustomer',
		'TotalPaymentatDelivery',
		'ReferentPersonID',
		'CashSupportFromMarkup',
		'TotalSaleCampaign',
		'balanceCampaign',
		'CashSupportInterestPlus',
		'TotalCashSupport',
		'TotalAccessoryGift',
		'AccessoryGiftCom',
		'TotalAccessoryExtra',
		'AccessoryExtraCom',
		'TotalCashSupportUsed',
		'RemainingCashSuuportShared',
		'SCCommissionIntPlus',
		'TradeinComAmount',
		'CommissionSale',
		'CommissionDeduct',
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
		'Note',
		'ReferrerID',
		'ReferrerAmount',
		'balance',
		'balanceFinance',
		'con_status'
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

	public function getBookingDateAttribute($value)
	{
		return $value ? Carbon::parse($value)->format('Y-m-d') : null;
	}

	public function getFormatBookingDateAttribute()
	{
		return $this->BookingDate ? Carbon::parse($this->BookingDate)->format('d-m-Y') : null;
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

	public function getGMApprovalSignatureDateAttribute($value)
	{
		return $value ? Carbon::parse($value)->format('Y-m-d') : null;
	}
}
