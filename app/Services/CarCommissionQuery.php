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
 *  - ยอดต่อเซลล์ = เรต × จำนวนคันของเซลล์คนนั้น
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

    /**
     * @return array{
     *   active:bool,
     *   perSale:Collection,        // SaleID => ['brand','count','achieved','rate','amount']
     *   brandCount:Collection,     // brand => จำนวนคันรวมทั้ง brand
     *   targets:Collection,        // brand => เป้า
     *   achievedByBrand:Collection // brand => bool
     * }
     */
    public static function forMonth(int $year, int $month): array
    {
        $empty = [
            'active'          => false,
            'perSale'         => collect(),
            'brandCount'      => collect(),
            'targets'         => collect(),
            'achievedByBrand' => collect(),
        ];

        if (!self::isActiveMonth($year, $month)) {
            return $empty;
        }

        $from = Carbon::create($year, $month, 1)->startOfMonth();
        $to   = Carbon::create($year, $month, 1)->endOfMonth();

        $cars = Salecar::withoutGlobalScopes()
            ->whereNotNull('DeliveryInCKDate')
            ->whereBetween('DeliveryInCKDate', [$from, $to])
            ->where('type_sale', self::SALE_TYPE_NORMAL)
            ->whereHas('carOrder', fn($c) => $c->withoutGlobalScopes()
                ->where('purchase_type', self::PURCHASE_TYPE_RETAIL)
                ->where(fn($q) => $q->where('purchase_source', '!=', self::SOURCE_DEALER)
                    ->orWhereNull('purchase_source')))
            ->get(['id', 'SaleID', 'brand', 'model_id']);

        if ($cars->isEmpty()) {
            return array_merge($empty, ['active' => true]);
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

        $perSale = $cars->groupBy('SaleID')->map(function (Collection $g) use ($achievedByBrand) {
            $brand = (int) $g->first()->brand;
            $count = $g->count();

            // brand ที่ไม่มีเป้า (เช่น brand 3) → คิดเรตตามรุ่นหลัก รวมทุกคัน
            if (self::isModelBased($brand)) {
                $amount = (float) $g->sum(fn($c) => self::modelRate($brand, $c->model_id !== null ? (int) $c->model_id : null));
                return [
                    'brand'    => $brand,
                    'mode'     => 'model',
                    'count'    => $count,
                    'achieved' => null,
                    'rate'     => null,
                    'amount'   => $amount,
                ];
            }

            // brand ที่มีเป้า (1/2) → เรตตามจำนวนคัน × บรรลุเป้า
            $achieved = (bool) ($achievedByBrand[$brand] ?? false);
            $rate     = self::rate($brand, $count, $achieved);

            return [
                'brand'    => $brand,
                'mode'     => 'volume',
                'count'    => $count,
                'achieved' => $achieved,
                'rate'     => $rate,
                'amount'   => $rate * $count,
            ];
        });

        return [
            'active'          => true,
            'perSale'         => $perSale,
            'brandCount'      => $brandCount,
            'targets'         => $targets,
            'achievedByBrand' => $achievedByBrand,
        ];
    }
}
