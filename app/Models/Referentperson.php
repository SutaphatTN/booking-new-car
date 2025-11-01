<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Referentperson
 * 
 * @property int $id
 * @property string|null $FirstName
 * @property string|null $LastName
 * @property string|null $IDCard
 * @property string|null $PhoneNumber
 * @property string|null $LineID
 * @property string|null $Facebook
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Referentperson extends Model
{
	protected $table = 'referentperson';

	protected $fillable = [
		'FirstName',
		'LastName',
		'IDCard',
		'PhoneNumber',
		'LineID',
		'Facebook'
	];
}
