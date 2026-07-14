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

    /** budget ยกมา = รถส่งมอบจริง (con_status=5) ตาม DeliveryDate เดือนก่อน × 1,000 */
    public static function carried(int $saleId, int $year, int $month): float
    {
        $prev = Carbon::create($year, $month, 1)->subMonthNoOverflow();

        $count = Salecar::withoutGlobalScope('userAccess')
            ->where('SaleID', $saleId)
            ->where('brand', 2)
            ->where('con_status', 5)
            ->whereNotNull('DeliveryInCKDate')   // ต้องมีวัน CK
            ->whereNotNull('DeliveryDate')       // และวัน DD (ส่งมอบจริง)
            ->whereBetween('DeliveryDate', [
                $prev->copy()->startOfMonth()->toDateString(),
                $prev->copy()->endOfMonth()->toDateString(),
            ])
            ->count();

        return $count * self::PER_CAR;
    }

    /** budget ที่ถูกหักไปแล้วในเดือนคอมนี้ (ผลรวม budget_deduct ของคันที่ DeliveryInCKDate อยู่เดือนนี้) */
    public static function used(int $saleId, int $year, int $month, ?int $excludeCarId = null): float
    {
        return (float) Salecar::withoutGlobalScope('userAccess')
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
