<?php

namespace App\Models;

use App\Models\Traits\BrandScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FilmPriceList extends Model
{
    use SoftDeletes, BrandScope;

    protected $table = 'tb_film_price_list';

    protected $fillable = [
        'model_id',
        'film_brand_id',
        'position',
        'front_shade',
        'body_shade',
        'sunroof_shade',
        'sqft',
        'price',
        'commission',
        'brand',
        'branch',
        'userZone',
        'userInsert',
    ];

    protected $casts = [
        'sqft'       => 'decimal:2',
        'price'      => 'decimal:2',
        'commission' => 'decimal:2',
    ];

    public function model()
    {
        return $this->belongsTo(TbCarmodel::class, 'model_id');
    }

    public function filmBrand()
    {
        return $this->belongsTo(FilmBrand::class, 'film_brand_id');
    }

    public function getShadeDisplayAttribute(): string
    {
        if ($this->position === 'sunroof') {
            return 'ซันรูฟ ' . $this->sunroof_shade;
        }
        if ($this->front_shade) {
            return "บานหน้า {$this->front_shade} รอบคัน {$this->body_shade}";
        }
        return 'รอบคัน ' . $this->body_shade;
    }
}
