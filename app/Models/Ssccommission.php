<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Ssccommission
 * 
 * @property int $id
 * @property int|null $saleID
 * @property int|null $sscID
 * @property float|null $RemainingCashSuuportShared
 * @property float|null $AccessoryComAmount
 * @property float|null $SCCommissionIntPlus
 * @property float|null $TradeinComAmount
 * @property string|null $OtherCommission
 * @property float|null $TotalCommissionRecieved
 * @property float|null $CommissionDeduct
 * @property float|null $OtherCommissionDeduct
 * @property float|null $TotalCommissionDeduct
 * @property float|null $NetCommission
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Ssccommission extends Model
{
	protected $table = 'ssccommission';

	protected $casts = [
		'saleID' => 'int',
		'sscID' => 'int',
		'RemainingCashSuuportShared' => 'float',
		'AccessoryComAmount' => 'float',
		'SCCommissionIntPlus' => 'float',
		'TradeinComAmount' => 'float',
		'TotalCommissionRecieved' => 'float',
		'CommissionDeduct' => 'float',
		'OtherCommissionDeduct' => 'float',
		'TotalCommissionDeduct' => 'float',
		'NetCommission' => 'float'
	];

	protected $fillable = [
		'saleID',
		'sscID',
		'RemainingCashSuuportShared',
		'AccessoryComAmount',
		'SCCommissionIntPlus',
		'TradeinComAmount',
		'OtherCommission',
		'TotalCommissionRecieved',
		'CommissionDeduct',
		'OtherCommissionDeduct',
		'TotalCommissionDeduct',
		'NetCommission'
	];
}
