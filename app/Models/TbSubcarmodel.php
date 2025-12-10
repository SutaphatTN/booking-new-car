<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TbSubcarmodel extends Model
{
	use SoftDeletes;

	protected $table = 'tb_subcarmodels';

	protected $fillable = [
		'model_id',
		'code',
		'name',
		'detail',
		'userZone',
		'active',
		'over_budget'
	];

	protected $dates = ['deleted_at'];

	public function model()
	{
		return $this->belongsTo(TbCarmodel::class, 'model_id', 'id');
	}
}
