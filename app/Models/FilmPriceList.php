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
        'sqft',
        'sqft_windshield',
        'sqft_rear',
        'sqft_door_front',
        'sqft_door_rear1',
        'sqft_quarter',
        'sqft_around',
        'has_door_rear2',
        'sqft_door_rear2',
        'has_sunroof',
        'sqft_sunroof',
        'has_3window',
        'sqft_3window',
        'price',
        'commission',
        'price_sunroof',
        'commission_sunroof',
        'price_3window',
        'commission_3window',
        'brand',
        'branch',
        'userZone',
        'userInsert',
    ];

    protected $casts = [
        'sqft'                => 'decimal:2',
        'sqft_windshield'     => 'decimal:2',
        'sqft_rear'           => 'decimal:2',
        'sqft_door_front'     => 'decimal:2',
        'sqft_door_rear1'     => 'decimal:2',
        'sqft_quarter'        => 'decimal:2',
        'sqft_around'         => 'decimal:2',
        'has_door_rear2'      => 'boolean',
        'sqft_door_rear2'     => 'decimal:2',
        'has_sunroof'         => 'boolean',
        'sqft_sunroof'        => 'decimal:2',
        'has_3window'         => 'boolean',
        'sqft_3window'        => 'decimal:2',
        'price'               => 'decimal:2',
        'commission'          => 'decimal:2',
        'price_sunroof'       => 'decimal:2',
        'commission_sunroof'  => 'decimal:2',
        'price_3window'       => 'decimal:2',
        'commission_3window'  => 'decimal:2',
    ];

    public function model()
    {
        return $this->belongsTo(TbCarmodel::class, 'model_id');
    }

    public function filmBrand()
    {
        return $this->belongsTo(FilmBrand::class, 'film_brand_id');
    }
}
