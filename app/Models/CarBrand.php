<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarBrand extends Model
{
    protected $table = 'tb_car_brands';

    protected $fillable = ['name', 'is_active', 'sort_order'];
}
