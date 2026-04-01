<?php

namespace App\Models;

use App\Models\Traits\BrandScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TbPricelistCar extends Model
{
	use SoftDeletes;
	use BrandScope;

	protected $table = 'tb_pricelist_car';

	protected $fillable = [
		'model_id',
		'subModel_id',
		'option',
		'year',
		'color',
		'msrp',
		'dm',
		'dnp',
		'ri',
		'ws',
		'userZone',
		'brand',
		'userInsert',
		'branch',
	];

	protected $dates = ['deleted_at'];
}
