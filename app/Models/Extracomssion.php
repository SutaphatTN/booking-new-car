<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Extracomssion
 * 
 * @property int $id
 * @property int|null $InterestCampaignID
 * @property int|null $FinanceID
 * @property float|null $ExtraCommissionAmount
 * @property Carbon|null $StartDate
 * @property Carbon|null $EndDate
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Extracomssion extends Model
{
	protected $table = 'extracomssions';

	protected $casts = [
		'InterestCampaignID' => 'int',
		'FinanceID' => 'int',
		'ExtraCommissionAmount' => 'float',
		'StartDate' => 'datetime',
		'EndDate' => 'datetime'
	];

	protected $fillable = [
		'InterestCampaignID',
		'FinanceID',
		'ExtraCommissionAmount',
		'StartDate',
		'EndDate'
	];
}
