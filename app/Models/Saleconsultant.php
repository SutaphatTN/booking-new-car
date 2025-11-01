<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Saleconsultant
 * 
 * @property int $id
 * @property string|null $FirstName
 * @property string|null $LastName
 * @property int|null $branchID
 * @property string $Active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Saleconsultant extends Model
{
	protected $table = 'saleconsultant';

	protected $casts = [
		'branchID' => 'int'
	];

	protected $fillable = [
		'FirstName',
		'LastName',
		'branchID',
		'Active'
	];
}
