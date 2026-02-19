<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Salecampaign
 * 
 * @property int $id
 * @property int|null $SaleID
 * @property int|null $CampaignID
 * @property int|null $CampaignType
 * @property float|null $CashSupport
 * @property float|null $CashSupportDeduct
 * @property float|null $CashSupportFinal
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Salecampaign extends Model
{
	use SoftDeletes;

	protected $table = 'salecampaigns';

	protected $casts = [
		'SaleID' => 'int',
		'CampaignID' => 'int',
		'CampaignType' => 'int',
		'CashSupport' => 'float',
		'CashSupportDeduct' => 'float',
		'CashSupportFinal' => 'float'
	];

	protected $fillable = [
		'SaleID',
		'CampaignID',
		'CampaignName',
		'CampaignType',
		'CashSupport',
		'CashSupportDeduct',
		'CashSupportFinal',
		'userZone',
		'brand'
	];

	protected $dates = ['deleted_at'];
	
	public function campaign()
	{
		return $this->belongsTo(Campaign::class, 'CampaignID', 'id');
	}
}
