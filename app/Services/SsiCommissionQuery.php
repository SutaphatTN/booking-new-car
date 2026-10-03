<?php

namespace App\Services;

use App\Models\SaleCommissionMonthly;
use App\Models\SsiRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * คอม SSI — เฉพาะ brand 1
 *
 * ตั้งแต่ MANUAL_FROM (2026-09) : เลิกคิดอัตโนมัติ — ผู้จัดการ/GM กรอกยอดเองรายเซลล์ เฉพาะเดือน 9
 * ด้านล่างคือสูตรอัตโนมัติเดิม (ใช้กับรอบก่อน MANUAL_FROM) — จ่ายปีละ 2 ครั้ง: เดือน 3 และ เดือน 10
 *  - เดือน 3  : นับยอดส่งมอบ ต.ค.(ปีก่อน)–มี.ค. (DeliveryInCKDate, คำนวณข้ามปี)
 *  - เดือน 10 : นับยอดส่งมอบ เม.ย.–ก.ย.
 *
 * เกณฑ์ (ตามตาราง 2.1):
 *  - นับเฉพาะ "รถขายปกติ (Retail=2) ที่มีคะแนน SSI" — ตัด TestDrive/ActivityCar/Company
 *  - SSI เฉลี่ยคิด "แยกตามสาขา" → ได้เรตต่อคันของสาขานั้น
 *       avg ≥ 95% → 1000 | ≥ 90% → 500 | ≥ 85% → 300 | < 85% → 0   (สเกล 1000 = 995/990/985 − 900)
 *  - เซลล์จะได้รับก็ต่อเมื่อ ขาย ≥ 18 คัน (ใน 6 เดือน) และ มี ≥ 1 คัน "ทุกเดือน"
 *  - ยอดที่ได้ = เรตของสาขา × จำนวนคันของเซลล์คนนั้น
 */
class SsiCommissionQuery
{
    /** ประเภทรถที่นับ (Retail = ขายปกติ RMOS) */
    public const SALE_TYPE_RETAIL = 2;

    /** ขั้นต่ำจำนวนคันต่อเซลล์ใน 6 เดือน */
    public const MIN_CARS = 18;

    /**
     * แยกสาขาสำหรับ SSI ตาม "รายชื่อเซลล์" (users.branch เก็บรวมเป็นสาขาเดียว จึงต้อง map เอง)
     *  - เซลล์ user id เหล่านี้ = สาขาอ่าวลึก ; ที่เหลือ = สำนักงานใหญ่
     * แก้รายชื่อได้ที่นี่จุดเดียว
     */
    public const AOLUEK_SALE_IDS = [7, 9, 10, 11];
    public const BRANCH_AOLUEK = 'อ่าวลึก';
    public const BRANCH_HQ     = 'สำนักงานใหญ่';

    /** สาขา (สำหรับ SSI) ของเซลล์คนนั้น */
    public static function branchOf(int $saleId): string
    {
        return in_array($saleId, self::AOLUEK_SALE_IDS, true)
            ? self::BRANCH_AOLUEK
            : self::BRANCH_HQ;
    }

    /**
     * ตั้งแต่เดือนนี้ (YYYY-MM) เลิกคิด SSI อัตโนมัติ → ผู้จัดการ/GM "กรอกยอดเอง" รายเซลล์
     * (เก็บที่ sale_commission_monthly.com_ssi) และกรอกได้เฉพาะเดือนใน MANUAL_MONTHS
     * รอบก่อนหน้านี้ (เช่น 3/2026) ยังคิดอัตโนมัติเหมือนเดิม — ยอดที่จ่ายไปแล้วไม่ขยับ
     */
    public const MANUAL_FROM   = '2026-09';
    public const MANUAL_MONTHS = [9];

    /** รอบ (year, month) นี้ใช้แบบกรอกเองไหม */
    public static function isManualPeriod(int $year, int $month): bool
    {
        return sprintf('%04d-%02d', $year, $month) >= self::MANUAL_FROM;
    }

    /** เดือนนี้มีคอม SSI ไหม — แบบกรอกเอง = MANUAL_MONTHS ; แบบอัตโนมัติ (รอบเก่า) = เดือน 3 และ 10 */
    public static function isPayoutMonth(int $month, ?int $year = null): bool
    {
        if ($year !== null && self::isManualPeriod($year, $month)) {
            return in_array($month, self::MANUAL_MONTHS, true);
        }
        return in_array($month, [3, 10], true);
    }

    /** มีคอลัมน์ com_ssi แล้วหรือยัง (กันพังถ้า deploy โค้ดก่อนรัน ALTER TABLE) */
    public static function hasManualColumn(): bool
    {
        static $has = null;

        return $has ??= Schema::hasColumn('sale_commission_monthly', 'com_ssi');
    }

    /**
     * SSI แบบกรอกเอง ของรอบ (year, month) — โครงเดียวกับ forPeriod เพื่อให้ผู้เรียกเดิมใช้ต่อได้เลย
     * perSale : SaleID => ['amount', 'manual' => true]
     */
    private static function manualForPeriod(int $year, int $month): array
    {
        $active = in_array($month, self::MANUAL_MONTHS, true);

        $perSale = ($active && self::hasManualColumn())
            ? SaleCommissionMonthly::where('year', $year)
            ->where('month', $month)
            ->where('com_ssi', '>', 0)
            ->get(['SaleID', 'com_ssi'])
            ->mapWithKeys(fn($r) => [(int) $r->SaleID => ['amount' => (float) $r->com_ssi, 'manual' => true]])
            : collect();

        return [
            'active'     => $active,
            'manual'     => true,
            'window'     => [null, null],
            'car_count'  => 0,
            'branchRate' => collect(),
            'perSale'    => $perSale,
        ];
    }

    public static function rate(float $averagePercent): float
    {
        // เทียบบนสเกล % (0–100) : 995→95, 990→90, 985→85
        if ($averagePercent >= 95) {
            return 1000;
        }
        if ($averagePercent >= 90) {
            return 500;
        }
        if ($averagePercent >= 85) {
            return 300;
        }
        return 0;
    }

    /** ช่วงวันที่ (6 เดือน) ของรอบจ่ายคอม SSI */
    public static function window(int $year, int $month): array
    {
        if ($month === 3) {
            return [
                Carbon::create($year - 1, 10, 1)->startOfDay(),
                Carbon::create($year, 3, 1)->endOfMonth()->endOfDay(),
            ];
        }

        return [
            Carbon::create($year, 4, 1)->startOfDay(),
            Carbon::create($year, 9, 1)->endOfMonth()->endOfDay(),
        ];
    }

    /** รายการเดือน (Y-m) ทั้ง 6 เดือนในหน้าต่าง — ใช้เช็ค "มี ≥1 คันทุกเดือน" */
    private static function windowMonths(Carbon $from, Carbon $to): array
    {
        $months = [];
        $cur = $from->copy()->startOfMonth();
        $end = $to->copy()->startOfMonth();
        while ($cur <= $end) {
            $months[] = $cur->format('Y-m');
            $cur->addMonth();
        }
        return $months;
    }

    /**
     * คำนวณคอม SSI ของรอบ (year, month)
     * @return array{
     *   active:bool, window:array, car_count:int,
     *   branchRate: Collection,  // branch => ['average','rate','car_count']
     *   perSale: Collection      // SaleID => ['count','branch','rate','average','eligible','every_month','amount']
     * }
     */
    public static function forPeriod(int $year, int $month): array
    {
        // ตั้งแต่ MANUAL_FROM : ยอดกรอกเอง (สูตรอัตโนมัติด้านล่างเก็บไว้ใช้กับรอบเก่า/เผื่อเปิดคืน)
        if (self::isManualPeriod($year, $month)) {
            return self::manualForPeriod($year, $month);
        }

        $empty = [
            'active'     => false,
            'window'     => [null, null],
            'car_count'  => 0,
            'branchRate' => collect(),
            'perSale'    => collect(),
        ];

        if (!self::isPayoutMonth($month)) {
            return $empty;
        }

        [$from, $to] = self::window($year, $month);

        // รถ brand 1, Retail, ส่งมอบในหน้าต่าง, มี record SSI (unscoped ให้ครอบทั้ง brand จริง)
        $records = SsiRecord::withoutGlobalScope('brandAccess')
            ->with([
                'assessment',
                'salecar' => fn($q) => $q->withoutGlobalScope('userAccess'),
            ])
            ->whereHas('salecar', function ($q) use ($from, $to) {
                $q->withoutGlobalScope('userAccess')
                    ->where('brand', 1)
                    ->whereNotNull('DeliveryInCKDate')
                    ->whereBetween('DeliveryInCKDate', [$from, $to])
                    ->whereHas('carOrder', fn($c) => $c->withoutGlobalScope('userAccess')
                        ->where('purchase_type', self::SALE_TYPE_RETAIL));
            })
            ->get();

        // เฉพาะคันที่มีคะแนน SSI
        $scored = $records->map(function ($rec) {
            $pct = $rec->ssiScorePercent();
            $s = $rec->salecar;
            if ($pct === null || !$s || !$s->SaleID) {
                return null;
            }
            return [
                'saleId' => (int) $s->SaleID,
                'branch' => self::branchOf((int) $s->SaleID), // แยกสาขาตามรายชื่อเซลล์
                'pct'    => (float) $pct,
                'month'  => Carbon::parse($s->DeliveryInCKDate)->format('Y-m'),
            ];
        })->filter()->values();

        if ($scored->isEmpty()) {
            return array_merge($empty, ['active' => true, 'window' => [$from, $to]]);
        }

        // เรตต่อสาขา (SSI เฉลี่ยแยกสาขา)
        $branchRate = $scored->groupBy('branch')->map(function (Collection $g) {
            $avg = round($g->avg('pct'), 2);
            return [
                'average'   => $avg,
                'rate'      => self::rate($avg),
                'car_count' => $g->count(),
            ];
        });

        $windowMonths = self::windowMonths($from, $to);

        // ต่อเซลล์ (เกณฑ์ ≥18 คัน + ≥1 ทุกเดือน)
        $perSale = $scored->groupBy('saleId')->map(function (Collection $g) use ($branchRate, $windowMonths) {
            $count  = $g->count();
            $branch = $g->first()['branch']; // label สาขา (string)
            $rate   = $branchRate[$branch]['rate'] ?? 0;
            $avg    = $branchRate[$branch]['average'] ?? null;

            $monthsHit  = $g->pluck('month')->unique();
            $everyMonth = collect($windowMonths)->every(fn($m) => $monthsHit->contains($m));
            $eligible   = $count >= self::MIN_CARS && $everyMonth;

            return [
                'count'       => $count,
                'branch'      => $branch,
                'rate'        => $rate,
                'average'     => $avg,
                'every_month' => $everyMonth,
                'eligible'    => $eligible,
                'amount'      => $eligible ? $rate * $count : 0.0,
            ];
        });

        return [
            'active'     => true,
            'window'     => [$from, $to],
            'car_count'  => $scored->count(),
            'branchRate' => $branchRate,
            'perSale'    => $perSale,
        ];
    }
}
