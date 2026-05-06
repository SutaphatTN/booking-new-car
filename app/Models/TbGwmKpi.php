<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TbGwmKpi extends Model
{
    use SoftDeletes;

    protected $table = 'tb_gwm_kpis';

    protected $fillable = [
        'month',
        'year',
        'sale_kpi',
        'ssi',
        'after_sale_kpi',
        'csi',
    ];

    protected $dates = ['deleted_at'];
}
