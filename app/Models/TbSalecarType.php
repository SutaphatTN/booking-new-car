<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TbSalecarType extends Model
{
    protected $table = 'tb_salecar_type';

    protected $fillable = [
        'name',
        'main_source',
    ];

    public function places()
    {
        return $this->hasMany(SourcePlace::class, 'salecar_type_id');
    }
}
