<?php

namespace App\Models;

use App\Models\Traits\UserAccessScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TbSubcarmodel extends Model
{
	use SoftDeletes;
	use UserAccessScope;

	protected $table = 'tb_subcarmodels';

	protected $fillable = [
		'model_id',
		'code',
		'name',
		'detail',
		'year',
		'active',
		'over_budget',
		'type_carOrder',
		'userZone',
		'brand'
	];

	protected $dates = ['deleted_at'];

	public function model()
	{
		return $this->belongsTo(TbCarmodel::class, 'model_id', 'id');
	}

	public function typeCar()
	{
		return $this->belongsTo(TbCaroderType::class, 'type_carOrder', 'id');
	}

	public function colors()
	{
		return $this->belongsToMany(
			TbColor::class,
			'color_submodel',
			'subcarmodel_id',
			'color_id'
		);
	}
}
