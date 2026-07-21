<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** บริษัทประกัน — lookup table ให้เลือกในหน้า PO (คู่กับ salecars.insurance_id) */
class Insurance extends Model
{
    use SoftDeletes;

    protected $table = 'tb_insurance';

    protected $fillable = [
        'name',
    ];

    public function salecars()
    {
        return $this->hasMany(Salecar::class, 'insurance_id');
    }
}
