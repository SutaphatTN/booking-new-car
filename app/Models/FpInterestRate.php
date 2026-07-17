<?php

namespace App\Models;

use App\Models\Traits\TracksUserActions;
use Illuminate\Database\Eloquent\Model;

/**
 * ส่วนต่างอัตราดอกเบี้ยวงเงิน (spread) แยกตาม brand + รายเดือน
 * อัตราดอกเบี้ยจริง = TISCO's MOR (FpMorRate) - spread ของแต่ละช่วง aging
 *
 * เก็บแยกแต่ละเดือน (period = 'YYYY-MM') ห้ามทับของเดิม
 * เดือนที่ยังไม่มีข้อมูล จะใช้ค่าของเดือนล่าสุดก่อนหน้าของ brand นั้น (fallback)
 */
class FpInterestRate extends Model
{
    use TracksUserActions;

    protected $table = 'fp_interest_rates';

    protected $fillable = [
        'brand',
        'period',
        'spread_1_60',
        'spread_61_120',
        'spread_121_180',
        'spread_181_up',
        'UserInsert',
        'UserUpdate',
    ];

    protected $casts = [
        'spread_1_60'    => 'decimal:2',
        'spread_61_120'  => 'decimal:2',
        'spread_121_180' => 'decimal:2',
        'spread_181_up'  => 'decimal:2',
    ];

    // ช่วง aging (คงที่) : key = คอลัมน์ spread, value = ป้ายกำกับช่วงวัน
    const BUCKETS = [
        'spread_1_60'    => '1-60',
        'spread_61_120'  => '61-120',
        'spread_121_180' => '121-180',
        'spread_181_up'  => '181 ขึ้นไป',
    ];

    // ค่า spread เริ่มต้น (ใช้เมื่อ brand ยังไม่เคยตั้งค่าเดือนใดเลย)
    const DEFAULT_SPREADS = [
        'spread_1_60'    => 3.95,
        'spread_61_120'  => 2.95,
        'spread_121_180' => 1.95,
        'spread_181_up'  => 0.95,
    ];

    // cache spreads ราย brand (per-request) — กัน N+1 ตอนคำนวณดอกเบี้ยหลายคัน/หลาย segment
    protected static array $spreadCache = [];

    /**
     * spreads ที่มีผลกับ brand + เดือน $period — ใช้ค่าเดือนล่าสุดที่ <= $period
     * ถ้าไม่มีเลย คืนค่า default
     */
    public static function effectiveForMonth(int $brand, string $period): array
    {
        if (!array_key_exists($brand, self::$spreadCache)) {
            // โหลดครั้งเดียวต่อ brand เรียงตาม period จากน้อยไปมาก
            self::$spreadCache[$brand] = static::where('brand', $brand)
                ->orderBy('period')
                ->get(['period', 'spread_1_60', 'spread_61_120', 'spread_121_180', 'spread_181_up'])
                ->all();
        }

        $best = null;
        foreach (self::$spreadCache[$brand] as $row) {
            if ($row->period <= $period) {
                $best = $row;
            } else {
                break; // period เรียง asc — เจอตัวที่เกินแล้วหยุด
            }
        }

        if (!$best) {
            return self::DEFAULT_SPREADS;
        }

        return [
            'spread_1_60'    => (float) $best->spread_1_60,
            'spread_61_120'  => (float) $best->spread_61_120,
            'spread_121_180' => (float) $best->spread_121_180,
            'spread_181_up'  => (float) $best->spread_181_up,
        ];
    }

    // ล้าง cache (เรียกหลังบันทึกค่าใหม่ ถ้าต้องอ่านซ้ำในคำขอเดียวกัน)
    public static function clearCache(): void
    {
        self::$spreadCache = [];
    }
}
