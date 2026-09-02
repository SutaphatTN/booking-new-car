<?php

namespace App\Services;

use App\Models\Salecar;
use Illuminate\Support\Carbon;

/**
 * "กระเป๋าตังค์ budget ยกมา" ของฝ่ายขาย brand 2 (รายเดือน)
 *
 * กติกา (ยืนยันกับผู้ใช้):
 *  - budget ยกมา = จำนวนรถ "ส่งมอบจริง" (con_status = 5) ตาม DeliveryDate ของ "เดือนก่อน" × 1,000
 *    (ต่อฝ่ายขาย 1 คน)
 *  - budget ถูก "หัก" ทีละคันผ่าน salecars.budget_deduct (admin กรอกเอง) เพื่อกลบคันที่คอมติดลบ
 *  - คันจะดึง budget จากกระเป๋าของ "เดือนคอม" ของตัวเอง = เดือนของ DeliveryInCKDate
 *  - คงเหลือ = ยกมา − ผลรวม budget_deduct ของคันในเดือนคอมนั้น
 *  - ใช้เฉพาะ brand 2
 */
class BudgetWallet
{
    public const PER_CAR = 1000;

    /** budget ที่เหลือปลายเดือน จ่ายคืนเซลล์ 30% (รวมเข้ายอดค่าคอมสุทธิ) */
    public const BONUS_RATE = 0.30;

    /** ใบที่ถอนจอง/ยกเลิก — ไม่นับเป็นยอดขาย (ตรงกับที่ระบบใช้ทั่วไป) */
    public const CANCELLED_STATUSES = [7, 8, 9];

    /**
     * เดือนสุดท้ายที่ใช้ระบบ budget (YYYY-MM) — หลังจากนี้ตัดทิ้งทั้งก้อน
     * ไม่มีกล่อง budget / ไม่มีช่อง budget หัก / ไม่มีโบนัส 30%
     * ตั้ง null = ใช้ต่อไปเรื่อย ๆ (เผื่ออนาคตเปิดใช้อีก)
     */
    public const LAST_MONTH = '2026-08';

    /** เดือนคอมนี้ยังใช้ระบบ budget อยู่ไหม */
    public static function activeFor(int $year, int $month): bool
    {
        if (self::LAST_MONTH === null) {
            return true;
        }

        return sprintf('%04d-%02d', $year, $month) <= self::LAST_MONTH;
    }

    /** โบนัส budget ที่เหลือ × 30% — ยอดที่เซลล์ได้เพิ่มเข้าค่าคอมเดือนนั้น */
    public static function bonus(int $saleId, int $year, int $month): float
    {
        if (!self::activeFor($year, $month)) {
            return 0.0;
        }

        return max(0.0, self::remaining($saleId, $year, $month)) * self::BONUS_RATE;
    }

    /**
     * โบนัส 30% ของหลายเซลล์พร้อมกัน (หน้ารายชื่อ) — 2 query รวม ไม่ใช่ 2 query ต่อคน
     * @return array SaleID => โบนัส
     */
    public static function bonusPerSale(int $year, int $month, array $saleIds): array
    {
        if (!$saleIds || !self::activeFor($year, $month)) {
            return [];
        }

        $prev = Carbon::create($year, $month, 1)->subMonthNoOverflow();

        // ต้องใช้เงื่อนไขชุดเดียวกับ carried() เป๊ะ ๆ ไม่งั้นหน้ารายชื่อกับหน้ารายละเอียดยอดไม่ตรงกัน
        $carriedCount = Salecar::withoutGlobalScopes(['userAccess', 'saleTeam'])
            ->whereIn('SaleID', $saleIds)
            ->where('brand', 2)
            ->whereNotIn('con_status', self::CANCELLED_STATUSES)
            ->whereBetween('DeliveryInCKDate', [
                $prev->copy()->startOfMonth()->startOfDay(),
                $prev->copy()->endOfMonth()->endOfDay(),
            ])
            ->selectRaw('SaleID, count(*) n')
            ->groupBy('SaleID')
            ->pluck('n', 'SaleID');

        $used = Salecar::withoutGlobalScopes(['userAccess', 'saleTeam'])
            ->whereIn('SaleID', $saleIds)
            ->where('brand', 2)
            ->whereYear('DeliveryInCKDate', $year)
            ->whereMonth('DeliveryInCKDate', $month)
            ->selectRaw('SaleID, sum(budget_deduct) s')
            ->groupBy('SaleID')
            ->pluck('s', 'SaleID');

        $out = [];
        foreach ($saleIds as $id) {
            $remaining = ((float) ($carriedCount[$id] ?? 0) * self::PER_CAR) - (float) ($used[$id] ?? 0);
            $out[(int) $id] = max(0.0, $remaining) * self::BONUS_RATE;
        }

        return $out;
    }

    /**
     * budget ยกมา = จำนวนรถที่ "ขายเดือนก่อน" × 1,000
     *
     * 2026-09-02 (ตามที่ผู้ใช้สั่ง): นับตามเดือนของ DeliveryInCKDate = เดือนคอมของคันนั้น
     * ให้ตรงกับจำนวนคันที่เห็นในตารางค่าคอมเดือนก่อนเป๊ะ ๆ
     * เดิมนับตาม DeliveryDate (วันรับรถจริง) ทำให้คันที่ CK สิ้นเดือนแต่รับรถต้นเดือนถัดไป
     * ตกไปอยู่คนละเดือนกับค่าคอมของมัน แล้วยอดไม่ตรงกับที่ผู้ใช้นับเอง
     *
     * นับรถทุกประเภท (รวม dealer/TestDrive) ตัดเฉพาะใบที่ถอนจอง/ยกเลิก (con_status 7,8,9)
     */
    public static function carried(int $saleId, int $year, int $month): float
    {
        $prev = Carbon::create($year, $month, 1)->subMonthNoOverflow();

        $count = Salecar::withoutGlobalScopes(['userAccess', 'saleTeam'])
            ->where('SaleID', $saleId)
            ->where('brand', 2)
            ->whereNotIn('con_status', self::CANCELLED_STATUSES)
            ->whereBetween('DeliveryInCKDate', [
                $prev->copy()->startOfMonth()->startOfDay(),
                $prev->copy()->endOfMonth()->endOfDay(),
            ])
            ->count();

        return $count * self::PER_CAR;
    }

    /** budget ที่ถูกหักไปแล้วในเดือนคอมนี้ (ผลรวม budget_deduct ของคันที่ DeliveryInCKDate อยู่เดือนนี้) */
    public static function used(int $saleId, int $year, int $month, ?int $excludeCarId = null): float
    {
        return (float) Salecar::withoutGlobalScopes(['userAccess', 'saleTeam'])
            ->where('SaleID', $saleId)
            ->where('brand', 2)
            ->whereYear('DeliveryInCKDate', $year)
            ->whereMonth('DeliveryInCKDate', $month)
            ->when($excludeCarId, fn($q) => $q->where('id', '!=', $excludeCarId))
            ->sum('budget_deduct');
    }

    /** budget คงเหลือ = ยกมา − ใช้ไป */
    public static function remaining(int $saleId, int $year, int $month, ?int $excludeCarId = null): float
    {
        return self::carried($saleId, $year, $month) - self::used($saleId, $year, $month, $excludeCarId);
    }
}
