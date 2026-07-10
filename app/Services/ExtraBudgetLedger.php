<?php

namespace App\Services;

use App\Models\Salecar;

/**
 * "เก็บงบเพิ่มเติม" (running deduction) — หนี้ที่ผู้จัดการกรอกตอนอนุมัติเกินงบ
 * (salecars.approval_extra_budget) จะติดตัวฝ่ายขาย แล้วไปหักจาก "งบเหลือ" (เคสงบปกติ)
 * ของคันอื่น จนกว่าจะหักครบ
 *
 * กติกา (ยืนยันกับผู้ใช้):
 *  - ขอบเขต: ฝ่ายขายคนเดียวกัน (SaleID) + brand เดียวกัน
 *  - ลำดับ: เรียงตาม DeliveryDate (เก่า→ใหม่) ; หนี้เข้ากอง ณ ตำแหน่งคันที่สร้างหนี้
 *           → เฉพาะคันที่ "ส่งมอบหลัง" คันที่สร้างหนี้เท่านั้นที่โดนหัก
 *  - หักยอดเต็ม: งบเต็ม = balanceCampaign × 2 (balanceCampaign เก็บค่าหาร 2 ไว้แล้ว)
 *               โดนหัก = min(งบเต็ม, หนี้คงเหลือ)
 *
 * ไม่ต้องมีคอลัมน์ใหม่ — คำนวณสดจาก approval_extra_budget ที่เก็บอยู่แล้ว
 */
class ExtraBudgetLedger
{
    /** cache ต่อ (saleId-brand) ภายใน request เดียว */
    private static array $cache = [];

    /**
     * คืน map [salecarId => ['absorbed' => ยอดโดนหัก, 'debtBefore' => หนี้คงเหลือก่อนถึงคันนี้]]
     * (debtBefore ใช้ในหน้า edit เพื่อคำนวณสดฝั่ง JS เวลาแก้งบของคันนั้น)
     */
    public static function ledgerMap(int $saleId, int $brand): array
    {
        $key = $saleId . '-' . $brand;
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key];
        }

        // bypass เฉพาะ UserAccessScope (กัน filter ตาม brand ผู้ดู) แต่คง SoftDeletes ไว้
        // (ใบที่ถูกยกเลิก/ลบ soft-delete จะไม่ถูกนับในกองหนี้/การหัก)
        $cars = Salecar::withoutGlobalScope('userAccess')
            ->where('SaleID', $saleId)
            ->where('brand', $brand)
            ->whereNotNull('DeliveryDate')
            ->salesQualifying()
            ->orderBy('DeliveryDate')
            ->orderBy('id')
            ->get(['id', 'balanceCampaign', 'approval_extra_budget']);

        $debt = 0.0; // กองหนี้คงเหลือ
        $map  = [];

        foreach ($cars as $c) {
            $balance = (float) ($c->balanceCampaign ?? 0);
            $extra   = (float) ($c->approval_extra_budget ?? 0);

            // ── คันเกินงบ (balance < 0) ──
            //  - ถ้ามีเก็บงบเพิ่มเติม → บวกเข้ากองหนี้ ณ ตำแหน่ง DeliveryDate ของมัน
            //    (นับเฉพาะตอนที่ยัง "เกินงบจริง" — ถ้าข้อมูลถูกแก้จนไม่เกินงบแล้ว ไม่ถือเป็นหนี้)
            //  - คันเกินงบไม่ใช่คัน "งบเหลือ" → ไม่ดูดหนี้ (หักจากคันอื่นที่ส่งมอบทีหลังเท่านั้น)
            if ($balance < 0) {
                if ($extra > 0) {
                    $debt += $extra;
                }
                continue;
            }

            // ── คันงบปกติ (balance ≥ 0) → ดูดหนี้จากงบเต็ม ──
            $debtBefore  = max(0.0, $debt);
            $full        = $balance * 2;
            $absorbed    = min($full, $debtBefore);
            $map[$c->id] = ['absorbed' => $absorbed, 'debtBefore' => $debtBefore];
            $debt -= $absorbed;
        }

        return self::$cache[$key] = $map;
    }

    private static function entryFor(Salecar $car): ?array
    {
        if (!$car->SaleID || !$car->brand || $car->DeliveryDate === null) {
            return null;
        }

        $map = self::ledgerMap((int) $car->SaleID, (int) $car->brand);

        return $map[$car->id] ?? null;
    }

    /** ยอดที่คันนี้โดนหัก (เก็บงบเพิ่มเติม) */
    public static function absorbedFor(Salecar $car): float
    {
        return (float) (self::entryFor($car)['absorbed'] ?? 0);
    }

    /** หนี้คงเหลือก่อนถึงคันนี้ (สำหรับคำนวณสดฝั่ง JS หน้า edit) */
    public static function debtBeforeFor(Salecar $car): float
    {
        return (float) (self::entryFor($car)['debtBefore'] ?? 0);
    }

    /** ล้าง cache (เผื่อใช้ในเทสต์/หลังแก้ข้อมูลใน request เดียวกัน) */
    public static function flush(): void
    {
        self::$cache = [];
    }
}
