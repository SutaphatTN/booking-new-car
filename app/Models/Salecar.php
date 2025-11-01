<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Salecar
 * 
 * @property int $id
 * @property int|null $CusID
 * @property Carbon|null $KeyInDate
 * @property int|null $SaleID
 * @property int|null $CarModelID
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
 * @property string|null $TotalSaleCampaign
 * @property float|null $CashSupportInterestPlus
 * @property float|null $TotalCashSupport
 * @property float|null $TotalAccessoryGift
 * @property float|null $TotalAccessoryExtra
 * @property float|null $TotalCashSupportUsed
 * @property float|null $RemainingCashSuuportShared
 * @property float|null $SCCommissionIntPlus
 * @property float|null $AccessoryComAmount
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
	protected $table = 'salecars';

	protected $casts = [
		'CusID' => 'int',
		'KeyInDate' => 'datetime',
		'SaleID' => 'int',
		'CarModelID' => 'int',
		'CarID' => 'int',
		'SaleConsultantID' => 'int',
		'FinanceID' => 'int',
		'TurnCarID' => 'int',
		'BookingDate' => 'datetime',
		'DeliveryDate' => 'datetime',
		'DeliveryInDMSDate' => 'datetime',
		'DeliveryInCKDate' => 'datetime',
		'RegistrationProvince' => 'datetime',
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
		'AccessoryComAmount' => 'float',
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
		'KeyInDate',
		'SaleID',
		'CarModelID',
		'Color',
		'CarID',
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
		'CashDeposit',
		'TradeinAddition',
		'AdditionFromCustomer',
		'TotalPaymentatDelivery',
		'ReferentPersonID',
		'CashSupportFromMarkup',
		'TotalSaleCampaign',
		'CashSupportInterestPlus',
		'TotalCashSupport',
		'TotalAccessoryGift',
		'TotalAccessoryExtra',
		'TotalCashSupportUsed',
		'RemainingCashSuuportShared',
		'SCCommissionIntPlus',
		'AccessoryComAmount',
		'TradeinComAmount',
		'CommissionDeduct',
		'ApprovalSignature',
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
		'Note'
	];

	public function turnCar()
	{
		return $this->belongsTo(TurnCar::class, 'TurnCarID', 'id');
	}

	public function customer()
	{
		return $this->belongsTo(Customer::class, 'CusID', 'id');
	}

	public function accessories()
	{
		return $this->belongsToMany(Accessory::class, 'saleaccessory', 'salecar_id', 'accessory_id')
			->withPivot(['price_type', 'price', 'commission', 'type'])
			->withTimestamps();
	}

	public function campaigns()
	{
		return $this->hasMany(Salecampaign::class, 'SaleID', 'SaleID');
	}

	public function carModel()
	{
		return $this->belongsTo(TbCarmodel::class, 'CarModelID', 'id');
	}
}
