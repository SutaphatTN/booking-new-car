<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TbGwmIncentive extends Model
{
    use SoftDeletes;

    protected $table = 'tb_gwm_incentives';

    protected $fillable = [
        'subcarmodel_id',
        'month',
        'year',
        'fixed',
        'lt70',
        'gte70_lte85',
        'gt85_lte100',
        'gt100_lte120',
        'gte120',
        'max_val',
        'monthly_target',
    ];

    protected $dates = ['deleted_at'];

    public function subcarmodel()
    {
        return $this->belongsTo(TbSubcarmodel::class, 'subcarmodel_id', 'id');
    }
}
