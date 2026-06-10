<?php

namespace App\Models;

use App\Models\Traits\BrandScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FilmUsage extends Model
{
    use SoftDeletes, BrandScope;

    protected $table = 'tb_film_usage';

    protected $fillable = [
        'type',
        'order_date',
        'vin',
        'car_order_id',
        'salecar_id',
        'customer_name',
        'sale_person',
        'model_id',
        'film_brand_id',
        'brand',
        'branch',
        'userZone',
        'userInsert',
    ];

    protected $casts = [
        'order_date' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(FilmUsageItem::class, 'film_usage_id');
    }

    public function model()
    {
        return $this->belongsTo(TbCarmodel::class, 'model_id');
    }

    public function filmBrand()
    {
        return $this->belongsTo(FilmBrand::class, 'film_brand_id');
    }
}
