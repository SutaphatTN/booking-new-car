<?php

namespace App\Services;

use App\Models\MonthlySaleTarget;
use App\Models\Salecar;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * คอมตัวรถรายคัน (รายเดือน) — เรตต่อคันตามตาราง config/car_commission.php
 *  - นับเฉพาะ Retail (purchase_type=2) + type_sale Normal (=1) ตาม DeliveryInCKDate ในเดือน
 *  - ตัดรถ dealer (purchase_source=OTHDealer) ออก ไม่คิดคอมตัวรถ
 *  - "บรรลุเป้า 120%" = ยอดรวมทั้ง brand ในเดือน >= เป้า × target_multiplier
 *  - ยอดต่อเซลล์ = เรต × จำนวนคันที่ได้คอม (paidCount)
 *  - คันที่ "เกินงบทะลุเพดาน" (MD/GM อนุมัติ) ไม่ได้คอมตัวรถ แต่ยังนับจำนวนคัน (เรต/เป้าเหมือนเดิม)
 */
class CarCommissionQuery
{
    public const SALE_TYPE_NORMAL   = 1; // salecars.type_sale
    public const PURCHASE_TYPE_RETAIL = 2; // carOrder.purchase_type
    public const SOURCE_DEALER      = 'OTHDealer'; // carOrder.purchase_source — รถ dealer ไม่คิดคอม

    /** เริ่มใช้ตั้งแต่เดือน config('car_commission.start') เท่านั้น */
    public static function isActiveMonth(int $year, int $month): bool
    {
        $start = config('car_commission.start');
        if (!$start) {
            return true;
        }
        return sprintf('%04d-%02d', $year, $month) >= $start;
    }

    /** brand นี้คิดเรต "ตามรุ่น" ไหม (เช่น brand 3 ที่ไม่มีเป้า) */
    public static function isModelBased(int $brand): bool
    {
        return !empty(config("car_commission.model_rates.$brand"));
    }

    /** เรตต่อคัน จากตาราง (เกินแถวสุดท้าย = ใช้แถวสุดท้าย) — ใช้กับ brand ที่มีเป้า */
    public static function rate(int $brand, int $count, bool $achieved): float
    {
        $table = config("car_commission.rates.$brand", []);
        if (empty($table) || $count < 1) {
            return 0.0;
        }
        $maxRow = max(array_keys($table));
        $row = $table[min($count, $maxRow)] ?? [0, 0];
        return (float) ($achieved ? ($row[1] ?? 0) : ($row[0] ?? 0));
    }

    /** เรตต่อคัน "ตามรุ่นหลัก" (model_id) */
    public static function modelRate(int $brand, ?int $modelId): float
    {
        return (float) (config("car_commission.model_rates.$brand.$modelId", 0));
    }

    /** ดึง entry คอมตัวรถของ (SaleID + brand) จาก perSale (nested) — null ถ้าไม่มี */
    public static function entry($perSale, int $saleId, int $brand): ?array
    {
        $sale = $perSale[$saleId] ?? null;
        return $sale ? ($sale[$brand] ?? null) : null;
    }

    /**
     * @return array{
     *   active:bool,
     *   perSale:Collection,        // SaleID => ['brand','count','achieved','rate','amount']
     *   brandCount:Collection,     // brand => จำนวนคันรวมทั้ง brand
     *   targets:Collection,        // brand => เป้า
     *   achievedByBrand:Collection // brand => bool
     * }
     */
    /**
     * memo ต่อ request (key = "brand|Y-m") — forMonth ถูกเรียกซ้ำหลายรอบต่อ 1 หน้า
     * (controller 1 ครั้ง + HeldCommissionQuery อีกเดือนละครั้งย้อนหลัง 2-3 เดือน)
     *
     * ต้องมี brand ของผู้ใช้อยู่ใน key ด้วย เพราะ relation model (tb_carmodels) มี brand global scope
     * → ผลของ isOverBudgetCeiling() ต่างกันตามคนที่เปิด ; ใน request จริงมีผู้ใช้คนเดียวจึงไม่ต่าง
     * แต่ process ที่สลับผู้ใช้ (queue/artisan/สคริปต์ทดสอบ) จะได้ค่าปนกันถ้าไม่ใส่ brand
     */
    private static array $memo = [];

    /** ล้าง memo (ใช้เมื่อมีการเขียนข้อมูลแล้วต้องอ่านใหม่ใน request เดียวกัน) */
    public static function flush(): void
    {
        self::$memo = [];
    }

    public static function forMonth(int $year, int $month): array
    {
        $key = sprintf('%d|%04d-%02d', (int) (auth()->user()->brand ?? 0), $year, $month);
        if (isset(self::$memo[$key])) {
            return self::$memo[$key];
        }

        $empty = [
            'active'          => false,
            'perSale'         => collect(),
            'brandCount'      => collect(),
            'targets'         => collect(),
            'achievedByBrand' => collect(),
        ];

        if (!self::isActiveMonth($year, $month)) {
            return self::$memo[$key] = $empty;
        }

        $from = Carbon::create($year, $month, 1)->startOfMonth();
        $to   = Carbon::create($year, $month, 1)->endOfMonth();

        // with('model') + balanceCampaign : ใช้เช็ค "เกินงบทะลุเพดาน" (คันแบบนี้ไม่ได้คอมตัวรถ)
        $cars = Salecar::withoutGlobalScopes(['userAccess', 'saleTeam'])
            ->with('model')
            ->whereNotNull('DeliveryInCKDate')
            ->whereBetween('DeliveryInCKDate', [$from, $to])
            // ใช้ scope ตัวเดียวกับคอมพื้นฐาน (Salecar::scopeSalesQualifying) — เดิมก๊อปเงื่อนไขมาไว้ที่นี่
            // ทำให้กติกา "รถแบบไหนได้คอม" มี 2 ชุดต้องแก้พร้อมกัน พลาดทีเดียวคอมตัวรถกับคอมพื้นฐานไม่ตรงกัน
            ->salesQualifying()
            // DeliveryInCKDate : ใช้เทียบวันตัดใน earnsCarCommission()
            ->get(['id', 'SaleID', 'brand', 'model_id', 'balanceCampaign', 'DeliveryInCKDate']);

        if ($cars->isEmpty()) {
            return self::$memo[$key] = array_merge($empty, ['active' => true]);
        }

        // ยอดรวมต่อ brand (ใช้ตัดสินบรรลุเป้า)
        $brandCount = $cars->groupBy('brand')->map->count();

        $targets = MonthlySaleTarget::where('year', $year)
            ->where('month', $month)
            ->pluck('target', 'brand');

        $mult = (float) config('car_commission.target_multiplier', 1.2);

        $achievedByBrand = $brandCount->map(function ($cnt, $brand) use ($targets, $mult) {
            $target = $targets[$brand] ?? null;
            return $target !== null && $target > 0 && $cnt >= $target * $mult;
        });

        // แบรนด์นี้ "ตั้งเป้า" ไว้ในเดือนนี้ไหม — แยกจาก achieved เพราะแบรนด์ที่ไม่มีเป้า (เช่น brand 4)
        // จะได้ achieved = false เสมอ แล้วหน้าจอขึ้นป้าย "ไม่บรรลุเป้า" ทั้งที่ไม่เคยมีเป้าให้บรรลุ
        $hasTargetByBrand = $brandCount->map(function ($cnt, $brand) use ($targets) {
            $target = $targets[$brand] ?? null;
            return $target !== null && $target > 0;
        });

        // แยกคอมตาม (SaleID + brand) — เซลล์ที่ขายหลาย brand (เช่น brand 3 ใช้ทีมขายร่วมกับ brand 1)
        // จะได้คอมของแต่ละ brand แยกกัน ไม่ปนกัน → perSale[SaleID][brand]
        $perSale = $cars->groupBy('SaleID')->map(function (Collection $g) use ($achievedByBrand, $hasTargetByBrand) {
            return $g->groupBy('brand')->map(function (Collection $bg) use ($achievedByBrand, $hasTargetByBrand) {
                $brand = (int) $bg->first()->brand;
                $count = $bg->count();

                // คันที่เกินงบทะลุเพดาน "ก่อนวันตัด" : ยังนับจำนวนคัน (เรต/เป้าเหมือนเดิม) แต่ไม่ได้ยอดคอมตัวรถ
                // ตั้งแต่วันตัดเป็นต้นไปได้คอมตัวรถทุกคัน (ดู Salecar::earnsCarCommission)
                $paidCars  = $bg->filter(fn($c) => $c->earnsCarCommission());
                $paidCount = $paidCars->count();

                // brand ที่ไม่มีเป้า (เช่น brand 3) → คิดเรตตามรุ่นหลัก รวมทุกคัน
                if (self::isModelBased($brand)) {
                    $amount = (float) $paidCars->sum(fn($c) => self::modelRate($brand, $c->model_id !== null ? (int) $c->model_id : null));
                    return [
                        'brand'     => $brand,
                        'mode'      => 'model',
                        'count'     => $count,
                        'paidCount' => $paidCount,
                        'achieved'  => null,
                        'hasTarget' => false,
                        'rate'      => null,
                        'amount'    => $amount,
                    ];
                }

                // brand ที่มีเป้า (1/2) → เรตตามจำนวนคัน × บรรลุเป้า (จ่ายเฉพาะคันที่ไม่ทะลุเพดาน)
                $achieved = (bool) ($achievedByBrand[$brand] ?? false);
                $rate     = self::rate($brand, $count, $achieved);

                return [
                    'brand'     => $brand,
                    'mode'      => 'volume',
                    'count'     => $count,
                    'paidCount' => $paidCount,
                    'achieved'  => $achieved,
                    'hasTarget' => (bool) ($hasTargetByBrand[$brand] ?? false),
                    'rate'      => $rate,
                    'amount'    => $rate * $paidCount,
                ];
            });
        });

        return self::$memo[$key] = [
            'active'          => true,
            'perSale'         => $perSale,
            'brandCount'      => $brandCount,
            'targets'         => $targets,
            'achievedByBrand' => $achievedByBrand,
        ];
    }
}
