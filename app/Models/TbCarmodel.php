<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use App\Models\Traits\UserAccessScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TbCarmodel
 * 
 * @property int $id
 * @property string|null $Name_TH
 * @property string|null $Name_EN
 * @property string $Active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class TbCarmodel extends Model
{
	use SoftDeletes;
	use UserAccessScope;
	
	protected $table = 'tb_carmodels';

	protected $fillable = [
		'Name_TH',
		'Name_EN',
		'Active',
		'over_budget',
		'money_min',
		'userZone',
		'brand'
	];

	protected $dates = ['deleted_at'];
}
