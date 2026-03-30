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
		'userZone',
		'brand',
		'branch',
	];
}
