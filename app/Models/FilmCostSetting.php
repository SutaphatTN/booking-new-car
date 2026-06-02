<?php

namespace App\Models;

use App\Models\Traits\BrandScope;
use Illuminate\Database\Eloquent\Model;

class FilmCostSetting extends Model
{
    use BrandScope;

    protected $table = 'tb_film_cost_settings';

    protected $fillable = [
        'film_brand_id',
        'roll_price',
        'discount',
        'brand',
        'branch',
        'userZone',
        'userInsert',
    ];

    protected $casts = [
        'roll_price' => 'decimal:2',
        'discount'   => 'decimal:2',
    ];

    public function filmBrand()
    {
        return $this->belongsTo(FilmBrand::class, 'film_brand_id');
    }

    public function getFinalCostAttribute(): float
    {
        return (float) $this->roll_price + (float) $this->discount;
    }

    public function getCostPerSqftAttribute(float $rollSize): float
    {
        return $rollSize > 0 ? $this->final_cost / $rollSize : 0;
    }
}
