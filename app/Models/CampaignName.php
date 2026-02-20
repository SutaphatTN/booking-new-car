<?php

namespace App\Models;

use App\Models\Traits\UserAccessScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CampaignName extends Model
{
	use SoftDeletes;
	use UserAccessScope;

	protected $table = 'campaign_name';

	protected $fillable = [
		'name',
		'userZone',
		'brand',
	];

	protected $dates = ['deleted_at'];
}
