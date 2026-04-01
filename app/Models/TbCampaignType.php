<?php

namespace App\Models;

use App\Models\Traits\BrandScope;
use Illuminate\Database\Eloquent\Model;

class TbCampaignType extends Model
{
	use BrandScope;
	
	protected $table = 'tb_campaign_type';

	protected $fillable = [
		'name',
		'type',
		'userZone',
		'brand',
		'branch',
	];

	//1: ri, 2: on-top, 3: other, 4: CK
}
