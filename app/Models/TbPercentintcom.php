<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TbPercentintcom
 * 
 * @property int $id
 * @property string|null $PercentIntCom
 * @property float|null $CashSupportInterestPlus
 * @property float|null $SCCommissionIntPlus
 * @property string $Active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class TbPercentintcom extends Model
{
	protected $table = 'tb_percentintcom';

	protected $casts = [
		'CashSupportInterestPlus' => 'float',
		'SCCommissionIntPlus' => 'float'
	];

	protected $fillable = [
		'PercentIntCom',
		'CashSupportInterestPlus',
		'SCCommissionIntPlus',
		'Active'
	];
}
