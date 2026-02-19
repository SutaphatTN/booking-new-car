<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CampaignName extends Model
{
	use SoftDeletes;

	protected $table = 'campaign_name';

	protected $fillable = [
		'name',
		'userZone',
		'brand',
	];

	protected $dates = ['deleted_at'];
}
