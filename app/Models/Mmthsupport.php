<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Mmthsupport
 * 
 * @property int $id
 * @property int|null $CarID
 * @property string|null $OtherSupportName
 * @property float|null $OtherSupportAmount
 * @property Carbon|null $StartDate
 * @property Carbon|null $EndDate
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Mmthsupport extends Model
{
	protected $table = 'mmthsupport';

	protected $casts = [
		'CarID' => 'int',
		'OtherSupportAmount' => 'float',
		'StartDate' => 'datetime',
		'EndDate' => 'datetime'
	];

	protected $fillable = [
		'CarID',
		'OtherSupportName',
		'OtherSupportAmount',
		'StartDate',
		'EndDate'
	];
}
