<?php

namespace App\Models;

use App\Models\Traits\BrandScope;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ColorSubmodel extends Pivot
{
    use BrandScope;

    protected $table = 'color_submodel';

    protected $fillable = [
        'color_id',
        'subcarmodel_id',
        'brand'
    ];

    public function gwmColor()
	{
		return $this->belongsTo(TbColor::class, 'color_id', 'id');
	}

    public function subModel()
	{
		return $this->belongsTo(TbSubcarmodel::class, 'subcarmodel_id', 'id');
	}
}
