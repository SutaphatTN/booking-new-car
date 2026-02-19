<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class FinancesConfirm extends Model
{
    use SoftDeletes;

    protected $table = 'finances_confirm';

    protected $fillable = [
        'SaleID',
        'net_price',
        'down',
        'excellent',
        'com_fin',
        'com_extra',
        'com_kickback',
        'com_subsidy',
        'advance_installment',
        'total',
        'actually_received',
        'diff',
        'firm_date',
        'date',
        'userZone',
		'brand',
    ];

    protected $dates = ['deleted_at'];

    public function getDateAttribute($value)
	{
		return $value ? Carbon::parse($value)->format('Y-m-d') : null;
	}

    public function getFormatDateAttribute()
	{
		return $this->date ? Carbon::parse($this->date)->format('d-m-Y') : null;
	}

    public function getFormatFirmDateAttribute()
	{
		return $this->firm_date ? Carbon::parse($this->firm_date)->format('d-m-Y') : null;
	}
}
