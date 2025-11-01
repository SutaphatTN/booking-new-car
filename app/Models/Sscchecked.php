<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Sscchecked
 * 
 * @property int $id
 * @property int|null $sscID
 * @property string|null $DisciplineCheckedDeliveryDoc
 * @property string|null $DisciplineCheckedOnlineClip
 * @property string|null $DisciplineCheckedPP
 * @property string|null $DiscilplineCheckSummary
 * @property string|null $DisciplineCompleted
 * @property Carbon|null $StartDate
 * @property Carbon|null $EndDate
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Sscchecked extends Model
{
	protected $table = 'ssccheckeds';

	protected $casts = [
		'sscID' => 'int',
		'StartDate' => 'datetime',
		'EndDate' => 'datetime'
	];

	protected $fillable = [
		'sscID',
		'DisciplineCheckedDeliveryDoc',
		'DisciplineCheckedOnlineClip',
		'DisciplineCheckedPP',
		'DiscilplineCheckSummary',
		'DisciplineCompleted',
		'StartDate',
		'EndDate'
	];
}
