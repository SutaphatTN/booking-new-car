<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TbProvinces extends Model
{
    protected $table = 'tb_provinces';

	protected $fillable = [
		'name',
	];
}
