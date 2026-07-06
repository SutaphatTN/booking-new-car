<?php

namespace App\Services;

use App\Models\Salecar;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * คอมกั๊ก (held commission) — เฉพาะ brand 1
 *  - รถที่ส่งมอบ (DeliveryInCKDate) หลังวันที่ 10 ของเดือน → กั๊กคันละ 2,000
 *    หักออกจากยอดของ "เดือนที่ส่งมอบ" แล้วยกไปจ่ายคืนใน "เดือนถัดไป"
 *  - net ต่อเซลล์ในเดือน M = (ยอดกั๊กยกมาจากเดือน M−1) − (ยอดกั๊กของเดือน M)
 *  - นับจากชุดรถเดียวกับหน้า commission (ส่งมอบจริง + มี CarOrderID)
 */
class HeldCommissionQuery
{
    public const BRAND        = 1;    // เฉพาะ brand 1
    public const HOLD_PER_CAR = 2000; // กั๊กคันละ 2,000
    public const CUTOFF_DAY   = 10;   // ส่งมอบหลังวันที่ 10 (ตั้งแต่วันที่ 11) เข้าเงื่อนไข

    /** จำนวนรถ "ส่งมอบหลังวันที่ 10" ต่อเซลล์ ในเดือนที่กำหนด (brand 1) => Collection SaleID => count */
    public static function lateCounts(int $year, int $month): Collection
    {
        $from = Carbon::create($year, $month, 1)->startOfMonth();
        $to   = Carbon::create($year, $month, 1)->endOfMonth();

        return Salecar::withoutGlobalScopes()
            ->whereNotNull('DeliveryInCKDate')
            ->whereNotNull('CarOrderID')
            ->where('brand', self::BRAND)
            ->whereBetween('DeliveryInCKDate', [$from, $to])
            ->whereDay('DeliveryInCKDate', '>', self::CUTOFF_DAY)
            ->get(['SaleID'])
            ->groupBy('SaleID')
            ->map->count();
    }

    /**
     * ข้อมูลคอมกั๊กของเดือน (ต่อเซลล์)
     *
     * @return array{active:bool, perSale:Collection}
     *   perSale: SaleID => [
     *     'held_count'  => จำนวนคันที่โดนกั๊กเดือนนี้ (ส่งมอบหลังวันที่ 10),
     *     'held'        => ยอดกั๊กเดือนนี้ (หักออก),
     *     'carry_count' => จำนวนคันที่ยกมาจากเดือนก่อน,
     *     'carried'     => ยอดกั๊กยกมาจากเดือนก่อน (จ่ายคืน),
     *     'net'         => carried − held,
     *   ]
     */
    public static function forMonth(int $year, int $month): array
    {
        $thisMonth = self::lateCounts($year, $month);

        $prev      = Carbon::create($year, $month, 1)->subMonthNoOverflow();
        $prevMonth = self::lateCounts($prev->year, $prev->month);

        $saleIds = $thisMonth->keys()->merge($prevMonth->keys())->unique();

        $perSale = $saleIds->mapWithKeys(function ($saleId) use ($thisMonth, $prevMonth) {
            $heldCount  = (int) ($thisMonth[$saleId] ?? 0);
            $carryCount = (int) ($prevMonth[$saleId] ?? 0);
            $held    = $heldCount * self::HOLD_PER_CAR;
            $carried = $carryCount * self::HOLD_PER_CAR;

            return [$saleId => [
                'held_count'  => $heldCount,
                'held'        => (float) $held,
                'carry_count' => $carryCount,
                'carried'     => (float) $carried,
                'net'         => (float) ($carried - $held),
            ]];
        });

        return ['active' => true, 'perSale' => $perSale];
    }

    /** net คอมกั๊กของเซลล์คนเดียว (0 ถ้าไม่ใช่ brand 1 / ไม่มีรายการ) */
    public static function netFor(int $year, int $month, int $saleId, int $brand): float
    {
        if ($brand !== self::BRAND) {
            return 0.0;
        }

        return (float) (self::forMonth($year, $month)['perSale'][$saleId]['net'] ?? 0);
    }
}
