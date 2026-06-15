<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InsuranceCompany extends Model
{
    protected $table = 'tb_insurance_companies';

    protected $fillable = ['name', 'is_active', 'sort_order'];
}
