<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class AccessoryPartner
 * 
 * @property int $id
 * @property string|null $name
 * @property string|null $userZone
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class AccessoryPartner extends Model
{
	use SoftDeletes;
	
	protected $table = 'accessory_partner';

	protected $fillable = [
		'name',
		'userZone',
		'brand'
	];

	protected $dates = ['deleted_at'];
}
