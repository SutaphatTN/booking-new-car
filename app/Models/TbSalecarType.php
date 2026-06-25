<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TbSalecarType extends Model
{
    use SoftDeletes;

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
