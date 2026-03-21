<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TbThailand extends Model
{
    protected $table = 'tb_thailand';

    protected $fillable = [
        'Postcode_pro',
        'Tambon_pro',
        'District_pro',
        'Province_pro',
        'Zone_pro',
        'Catalog_id',
        'Code',
        'Address_Score',
    ];
}
