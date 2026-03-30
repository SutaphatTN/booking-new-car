<?php

namespace App\Models;

use App\Models\Traits\BrandScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CampaignName extends Model
{
	use SoftDeletes;
	use BrandScope;

	protected $table = 'campaign_name';

	protected $fillable = [
		'name',
		'userZone',
		'brand',
		'branch',
	];

	protected $dates = ['deleted_at'];
}
