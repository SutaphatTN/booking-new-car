<?php

namespace App\Models;

use App\Models\Traits\BrandScope;
use Illuminate\Database\Eloquent\Model;

class FilmGlobalSetting extends Model
{
    use BrandScope;

    protected $table = 'tb_film_global_settings';

    protected $fillable = [
        'roll_size',
        'waste_pct',
        'gp_pct',
        'commission_pct',
        'brand',
        'branch',
        'userZone',
        'userInsert',
    ];

    protected $casts = [
        'roll_size'      => 'decimal:2',
        'waste_pct'      => 'decimal:2',
        'gp_pct'         => 'decimal:2',
        'commission_pct' => 'decimal:2',
    ];

    public static function current(): self
    {
        return static::first() ?? new self([
            'roll_size'      => 500,
            'waste_pct'      => 15,
            'gp_pct'         => 65,
            'commission_pct' => 15,
        ]);
    }

    public function calcPrice(float $sqft, float $costPerSqft): array
    {
        $filmCost     = $sqft * (1 + $this->waste_pct / 100) * $costPerSqft;
        $priceExVat   = $filmCost / (1 - $this->gp_pct / 100);
        $priceIncVat  = round($priceExVat * 1.07, -2); // round to nearest 100
        $commission   = round($priceIncVat * $this->commission_pct / 100, -1); // round to nearest 10
        return [
            'price'      => $priceIncVat,
            'commission' => $commission,
        ];
    }
}
