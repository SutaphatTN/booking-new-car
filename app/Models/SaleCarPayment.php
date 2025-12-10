<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleCarPayment extends Model
{
    protected $table = 'salecars_payment';

    protected $fillable = [
        'SaleID',
        'type',
        'cost',
        'date',
        'userZone',
    ];

}
