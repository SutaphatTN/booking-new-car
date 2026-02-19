<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class TbCampaignType extends Model
{
	protected $table = 'tb_campaign_type';

	protected $fillable = [
		'name',
		'userZone',
		'brand'
	];
}
