<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Campaigncar
 * 
 * @property int $id
 * @property int|null $CarID
 * @property int|null $CampaignTYP
 * @property string|null $SubCampaignID
 * @property string|null $SubCampaignType
 * @property float|null $CashSupport
 * @property float|null $CashSupportDeduct
 * @property float|null $CashSupportFinal
 * @property Carbon|null $StartDate
 * @property Carbon|null $EndDate
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Campaigncar extends Model
{
	protected $table = 'campaigncars';

	protected $casts = [
		'CarID' => 'int',
		'CampaignTYP' => 'int',
		'CashSupport' => 'float',
		'CashSupportDeduct' => 'float',
		'CashSupportFinal' => 'float',
		'StartDate' => 'datetime',
		'EndDate' => 'datetime'
	];

	protected $fillable = [
		'CarID',
		'CampaignTYP',
		'SubCampaignID',
		'SubCampaignType',
		'CashSupport',
		'CashSupportDeduct',
		'CashSupportFinal',
		'StartDate',
		'EndDate'
	];

	public function campaignType()
	{
		return $this->belongsTo(TbCampaigntyp::class, 'CampaignTYP', 'id');
	}
}
