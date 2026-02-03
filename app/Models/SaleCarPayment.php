<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleCarPayment extends Model
{
    use SoftDeletes;
    
    protected $table = 'salecars_payment';

    protected $fillable = [
        'SaleID',
        'type',
        'cost',
        'date',
        'userZone',
    ];

    protected $dates = ['deleted_at'];

}
