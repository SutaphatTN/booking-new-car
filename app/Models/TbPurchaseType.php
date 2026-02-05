<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TbPurchaseType extends Model
{
    protected $table = 'tb_purchase_type';

    protected $fillable = [
        'name',
    ];
}
