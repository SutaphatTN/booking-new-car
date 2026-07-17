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

    // cache ทั้งตาราง (per-request) — กัน N+1 ตอนคำนวณดอกเบี้ยหลายคัน/หลาย segment
    protected static ?array $morCache = null;

    /**
     * MOR ที่มีผลกับเดือน $period — ใช้ค่าเดือนล่าสุดที่ <= $period
     * ถ้าไม่มีเลย คืนค่า default
     */
    public static function effectiveForMonth(string $period): float
    {
        if (self::$morCache === null) {
            // โหลดครั้งเดียวทั้งตาราง เรียงตาม period จากน้อยไปมาก
            self::$morCache = static::orderBy('period')->pluck('mor', 'period')->all();
        }

        $best = null;
        foreach (self::$morCache as $p => $mor) {
            if ($p <= $period) {
                $best = $mor;
            } else {
                break; // period เรียง asc — เจอตัวที่เกินแล้วหยุด
            }
        }

        return $best !== null ? (float) $best : self::DEFAULT_MOR;
    }

    // ล้าง cache (เรียกหลังบันทึกค่าใหม่ ถ้าต้องอ่านซ้ำในคำขอเดียวกัน)
    public static function clearCache(): void
    {
        self::$morCache = null;
    }
}
