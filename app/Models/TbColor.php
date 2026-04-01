<?php

namespace App\Models;

use App\Models\Traits\BrandScope;
use Illuminate\Database\Eloquent\Model;

class TbColor extends Model
{
    use BrandScope;

    protected $table = 'tb_color';

    protected $fillable = [
        'name',
        'brand'
    ];

    public function subModels()
    {
        return $this->belongsToMany(
            TbSubcarmodel::class,
            'color_submodel',
            'color_id',
            'subcarmodel_id'
        )->using(ColorSubmodel::class)
            ->withPivot('brand');
    }
}
