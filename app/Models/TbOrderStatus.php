<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TbOrderStatus extends Model
{
    protected $table = 'tb_orderstatus';

	protected $fillable = [
		'name',
	];
}
