<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
		'brand',
		'branch',
	];

	protected $dates = ['deleted_at'];
	
	// ปลด soft delete ของ master — แคมเปญที่ถูกลบในหน้าตั้งค่าทีหลัง ใบเก่าต้องยังอ่านชื่อ/ประเภทได้
	// (GP กับใบขออนุมัติแยกยอดตาม type ของแคมเปญ ถ้า relation เป็น null ยอดจะหายไปทั้งก้อน)
	public function campaign()
	{
		return $this->belongsTo(Campaign::class, 'CampaignID', 'id')
			->withoutGlobalScope(SoftDeletingScope::class);
	}

	public function saleCar()
	{
		return $this->belongsTo(Salecar::class, 'SaleID', 'id');
	}

	public function campaignType()
	{
		return $this->belongsTo(TbCampaignType::class, 'CampaignType', 'id');
	}

	public function claim()
	{
		return $this->hasOne(CampaignClaim::class, 'salecampaign_id', 'id');
	}
}
