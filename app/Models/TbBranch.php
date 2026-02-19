<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TbBranch extends Model
{
	protected $table = 'tb_branch';

	protected $fillable = [
		'name',
		'code'
	];
}
