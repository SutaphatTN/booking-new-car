<?php

namespace App\Models;

use App\Models\Traits\UserAccessScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class TbCampaignType extends Model
{
	use UserAccessScope;
	
	protected $table = 'tb_campaign_type';

	protected $fillable = [
		'name',
		'userZone',
		'brand'
	];
}
