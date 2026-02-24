<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TbSalePurchaseType extends Model
{
    protected $table = 'tb_sale_purchase_type';

    protected $fillable = [
        'name',
    ];
}
