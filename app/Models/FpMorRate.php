<?php

namespace App\Models;

use App\Models\Traits\TracksUserActions;
use Illuminate\Database\Eloquent\Model;

/**
 * TISCO's MOR รายเดือน — ค่ากลางค่าเดียวใช้ร่วมกันทุก brand
 * เก็บแยกแต่ละเดือน (period = 'YYYY-MM') ห้ามทับของเดิม
 * เดือนที่ยังไม่มีข้อมูล จะใช้ค่าของเดือนล่าสุดก่อนหน้า (fallback)
 */
class FpMorRate extends Model
{
    use TracksUserActions;

    protected $table = 'fp_mor_rates';

    protected $fillable = [
        'period',
        'mor',
        'UserInsert',
        'UserUpdate',
    ];

    protected $casts = [
        'mor' => 'decimal:2',
    ];

    // ค่าเริ่มต้น TISCO's MOR (ใช้เมื่อยังไม่เคยตั้งค่าเดือนใดเลย)
    const DEFAULT_MOR = 7.10;

    /**
     * MOR ที่มีผลกับเดือน $period — ใช้ค่าเดือนล่าสุดที่ <= $period
     * ถ้าไม่มีเลย คืนค่า default
     */
    public static function effectiveForMonth(string $period): float
    {
        $row = static::where('period', '<=', $period)
            ->orderBy('period', 'desc')
            ->first();

        return $row ? (float) $row->mor : self::DEFAULT_MOR;
    }
}
