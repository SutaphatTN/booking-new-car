<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TbInteriorColor extends Model
{
    protected $table = 'tb_interior_color';

    protected $fillable = [
        'name',
    ];

    public function models()
    {
        return $this->belongsToMany(TbCarmodel::class, 'tb_interior_color_model', 'interior_color_id', 'model_id');
    }
}
