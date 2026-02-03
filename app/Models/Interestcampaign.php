<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Interestcampaign
 * 
 * @property int $id
 * @property int|null $CarID
 * @property int|null $FinanceID
 * @property int|null $IntestCampaignType
 * @property float|null $CashSupportKickback
 * @property int|null $PercentIntCom
 * @property Carbon|null $StartDate
 * @property Carbon|null $EndDate
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Interestcampaign extends Model
{
	use SoftDeletes;

	protected $table = 'interestcampaigns';

	protected $casts = [
		'CarID' => 'int',
		'FinanceID' => 'int',
		'IntestCampaignType' => 'int',
		'CashSupportKickback' => 'float',
		'PercentIntCom' => 'int',
		'StartDate' => 'datetime',
		'EndDate' => 'datetime'
	];

	protected $fillable = [
		'CarID',
		'FinanceID',
		'IntestCampaignType',
		'CashSupportKickback',
		'PercentIntCom',
		'StartDate',
		'EndDate'
	];

	protected $dates = ['deleted_at'];
}
