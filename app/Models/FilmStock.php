<?php

namespace App\Models;

use App\Models\Traits\BrandScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FilmStock extends Model
{
    use SoftDeletes, BrandScope;

    protected $table = 'tb_film_stocks';

    protected $fillable = [
        'stock_no',
        'part_no',
        'brand_group',
        'brand',
        'branch',
        'userZone',
        'userInsert',
        'film_brand_id',
        'shade',
        'withdrawal_date',
        'initial_qty',
        'used_qty',
        'inspection_date',
        'inspection_qty',
        'inspection_result',
        'inspection_by',
        'audit_completed_at',
        'audit_completed_by',
    ];

    protected $casts = [
        'withdrawal_date'    => 'date',
        'inspection_date'    => 'date',
        'initial_qty'        => 'decimal:2',
        'used_qty'           => 'decimal:2',
        'inspection_qty'     => 'decimal:2',
        'audit_completed_at' => 'datetime',
    ];

    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspection_by');
    }

    const BRAND_GROUPS = [
        'M' => 'Mitsubishi / Wuling',
        'G' => 'GWM',
    ];

    public function filmBrand()
    {
        return $this->belongsTo(FilmBrand::class, 'film_brand_id');
    }

    public function getRemainingQtyAttribute(): float
    {
        return (float) $this->initial_qty - (float) $this->used_qty;
    }

    public function getInspectionDiffAttribute(): ?float
    {
        if ($this->inspection_qty === null) return null;
        return $this->remaining_qty - (float) $this->inspection_qty;
    }

    public function getStatusAttribute(): string
    {
        return $this->remaining_qty > 0 ? 'active' : 'inactive';
    }

    public static function brandGroupFromUserBrand(?int $userBrand): string
    {
        return $userBrand === 2 ? 'G' : 'M';
    }

    public static function generateStockNo(string $brandGroup, string $filmCode, string $shade, string $date): string
    {
        $dt = \Carbon\Carbon::parse($date);
        return $brandGroup . $filmCode . $shade . $dt->format('ymd');
    }
}
