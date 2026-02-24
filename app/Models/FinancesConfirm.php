<?php

namespace App\Models;

use App\Models\Traits\UserAccessScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class FinancesConfirm extends Model
{
    use SoftDeletes;
    use UserAccessScope;

    protected $table = 'finances_confirm';

    protected $fillable = [
        'SaleID',
        'net_price',
        'down',
        'excellent',
        'excellent_accept',
        'excellent_diff',
        'com_fin',
        'com_fin_accept',
        'com_fin_diff',
        'com_extra',
        'com_extra_accept',
        'com_extra_diff',
        'com_kickback',
        'com_kickback_accept',
        'com_kickback_diff',
        'com_subsidy',
        'com_subsidy_accept',
        'com_subsidy_diff',
        'advance_installment',
        'advance_installment_accept',
        'advance_installment_diff',
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
