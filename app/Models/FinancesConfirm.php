<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class FinancesConfirm extends Model
{
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
        'date',
    ];

    public function getDateAttribute($value)
	{
		return $value ? Carbon::parse($value)->format('Y-m-d') : null;
	}

    public function getFormatDateAttribute()
	{
		return $this->date ? Carbon::parse($this->date)->format('d-m-Y') : null;
	}
}
