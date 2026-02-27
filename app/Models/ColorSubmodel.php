<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ColorSubmodel extends Model
{
    protected $table = 'color_submodel';

    protected $fillable = [
        'color_id',
        'subcarmodel_id'
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
