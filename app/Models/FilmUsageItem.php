<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilmUsageItem extends Model
{
    protected $table = 'tb_film_usage_items';

    protected $fillable = [
        'film_usage_id',
        'position',
        'shade',
        'film_stock_id',
        'stock_no',
        'sqft_used',
        'price',
        'commission',
    ];

    protected $casts = [
        'sqft_used'  => 'decimal:2',
        'price'      => 'decimal:2',
        'commission' => 'decimal:2',
    ];

    public function filmStock()
    {
        return $this->belongsTo(FilmStock::class, 'film_stock_id');
    }
}
