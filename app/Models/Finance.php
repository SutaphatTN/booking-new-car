<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Finance
 * 
 * @property int $id
 * @property string|null $FinanceCompany
 * @property string $Active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Finance extends Model
{
	protected $table = 'finances';

	protected $fillable = [
		'FinanceCompany',
		'tax',
		'max_year',
		'Active'
	];

	public function getFormatUpdatedAttribute()
	{
		return $this->updated_at ? Carbon::parse($this->updated_at)->format('d-m-Y') : null;
	}
}
