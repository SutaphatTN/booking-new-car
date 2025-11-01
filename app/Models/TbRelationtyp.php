<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TbRelationtyp
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
class TbRelationtyp extends Model
{
	protected $table = 'tb_relationtyp';

	protected $fillable = [
		'Name_TH',
		'Name_EN',
		'Active'
	];
}
