<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TbIntestcampaigntyp
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
class TbIntestcampaigntyp extends Model
{
	protected $table = 'tb_intestcampaigntyp';

	protected $fillable = [
		'Name_TH',
		'Name_EN',
		'Active'
	];
}
