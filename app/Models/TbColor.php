<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TbColor extends Model
{
    protected $table = 'tb_color';

    protected $fillable = [
        'name'
    ];

    public function subModels()
    {
        return $this->belongsToMany(
            TbSubcarmodel::class,
            'color_submodel',
            'color_id',
            'subcarmodel_id'
        );
    }
}
