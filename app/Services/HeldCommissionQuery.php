<?php

namespace App\Services;

use App\Models\Salecar;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * คอมกั๊ก (held commission) — เฉพาะ brand 1 — กั๊กเฉพาะ "ค่าคอมรายคัน" (CarCommissionQuery)
 *
 * โมเดล (อิง 2 วันที่: DeliveryInCKDate = CK, DeliveryDate = DD):
 *  - รอบหลักของ CK = วันที่ 10 ของเดือนถัดจาก CK
 *  - DD ≤ รอบหลัก CK (รับรถทัน) → ไม่กั๊ก จ่ายเต็มที่ "งวด 10 ตามวัน DD" (วันที่≤10→10 เดือนนั้น, ≥11→10 เดือนถัดไป)
 *  - DD > รอบหลัก CK (รับรถช้า) → กั๊ก: (C−2000) จ่ายรอบหลัก CK, 2000 จ่ายตามงวด DD (1–10→10 / 11–20→20 / 21+→10 เดือนถัดไป)
 *  - DD ว่าง → กั๊ก 2000 พักไว้ (ยังไม่จ่ายจนกว่าจะมี DD), ที่เหลือจ่ายรอบหลัก CK
 *  - ยอดกั๊ก H = min(2000, ค่าคอมรายคัน) ไม่ติดลบ
 */
class HeldCommissionQuery
{
    public const BRAND        = 1;    // เฉพาะ brand 1
    public const HOLD_PER_CAR = 2000; // กั๊กคันละ 2,000 (cap ที่ค่าคอมรายคัน)
    public const CUTOFF_DAY   = 10;   // (legacy) — โมเดลเก่าใช้ "ส่งมอบหลังวันที่ 10"

    /** รอบหลักของ CK = วันที่ 10 ของเดือนถัดจาก CK */
    public static function mainPaydayCK(Carbon $ck): Carbon
    {
        return $ck->copy()->startOfMonth()->addMonthNoOverflow()->day(10);
    }

    /** งวดจ่ายวันที่ 10 ตามวันที่ — วันที่ ≤10 → 10 เดือนนั้น ; ≥11 → 10 เดือนถัดไป */
    public static function installment10(Carbon $d): Carbon
    {
        return $d->day <= 10 ? $d->copy()->day(10) : $d->copy()->addMonthNoOverflow()->day(10);
    }

    /** รอบจ่ายยอดกั๊ก (2000) ตามวัน DD — 1–10 → 10 / 11–20 → 20 / 21+ → 10 เดือนถัดไป */
    public static function heldPayday(?Carbon $dd): ?Carbon
    {
        if ($dd === null) {
            return null;
        }
        $d = (int) $dd->day;
        if ($d <= 10) {
            return $dd->copy()->day(10);
        }
        if ($d <= 20) {
            return $dd->copy()->day(20);
        }
        return $dd->copy()->addMonthNoOverflow()->day(10);
    }

    /**
     * เข้าเงื่อนไขกั๊กไหม — กั๊กเมื่อ DD ว่าง (ยังไม่รับรถ) หรือ DD มา "หลัง" รอบหลักของ CK
     * (รับรถหลังวันจ่ายรอบหลัก → ยังจ่ายเต็มไม่ได้ กั๊ก 2000 ไว้)
     */
    public static function isHeld(Carbon $ck, ?Carbon $dd): bool
    {
        return $dd === null || $dd->gt(self::mainPaydayCK($ck));
    }

    /** ยอดกั๊ก = min(2000, ค่าคอมรายคัน) — ไม่ติดลบ */
    public static function heldAmount(bool $held, float $carCommission): float
    {
        return $held ? min((float) self::HOLD_PER_CAR, max(0.0, $carCommission)) : 0.0;
    }

    /**
     * แผนจ่ายค่าคอมรายคันของรถ 1 คัน (CK, DD, ค่าคอม C)
     * @return array{held:bool, held_amount:float, main_amount:float, main_payday:Carbon, held_payday:?Carbon}
     */
    public static function paymentFor(Carbon $ck, ?Carbon $dd, float $C): array
    {
        $mainCK = self::mainPaydayCK($ck);

        // ยังไม่รับรถ (DD ว่าง) → พักทั้งก้อนไว้จนกว่าจะมี DD
        if ($dd === null) {
            return ['held' => false, 'held_amount' => 0.0, 'main_amount' => $C, 'main_payday' => null, 'held_payday' => null];
        }

        // รับรถทันรอบหลัก (DD ≤ รอบหลัก CK) → จ่ายเต็มที่รอบหลัก CK
        if ($dd->lte($mainCK)) {
            return ['held' => false, 'held_amount' => 0.0, 'main_amount' => $C, 'main_payday' => $mainCK, 'held_payday' => null];
        }

        // รับรถช้า (DD > รอบหลัก CK) → กั๊ก 2000: (C−2000) จ่ายรอบหลัก CK, 2000 จ่ายงวด 10 ถัดจากรับรถ
        $H = self::heldAmount(true, $C);
        return ['held' => true, 'held_amount' => $H, 'main_amount' => $C - $H, 'main_payday' => $mainCK, 'held_payday' => self::installment10($dd)];
    }

    /**
     * รายละเอียดคอมกั๊ก "รายคัน" ของรถที่ตัด CK ในเดือนที่เลือก (brand 1, qualifying)
     * @return Collection ของ array ต่อคัน: salecar_id, SaleID, car_commission(C), ck, dd,
     *   held, held_amount(H), main_amount(C−H), main_payday, held_payday(null=พักไว้)
     */
    public static function perCarForCkMonth(int $year, int $month): Collection
    {
        $from = Carbon::create($year, $month, 1)->startOfMonth();
        $to   = Carbon::create($year, $month, 1)->endOfMonth();

        // เรตคอมรายคันต่อเซลล์ (rate/mode) — คิดตามเดือน CK
        $perSale = CarCommissionQuery::forMonth($year, $month)['perSale'];

        return Salecar::withoutGlobalScopes()
            ->where('brand', self::BRAND)
            ->whereNotNull('DeliveryInCKDate')
            ->whereNotNull('CarOrderID')
            ->salesQualifying()
            ->whereBetween('DeliveryInCKDate', [$from, $to])
            ->get(['id', 'SaleID', 'DeliveryInCKDate', 'DeliveryDate', 'model_id', 'brand'])
            ->map(function ($r) use ($perSale) {
                $saleId = (int) $r->SaleID;
                // entry ของ (SaleID + brand ของรถคันนี้) — เซลล์ที่ขายหลาย brand จะไม่ปนกัน
                $entry  = CarCommissionQuery::entry($perSale, $saleId, (int) $r->brand);
                $C      = self::carCommissionOf($r, $entry);

                $ck = Carbon::parse($r->DeliveryInCKDate);
                $dd = $r->DeliveryDate ? Carbon::parse($r->DeliveryDate) : null;
                $p  = self::paymentFor($ck, $dd, $C);

                return [
                    'salecar_id'  => (int) $r->id,
                    'SaleID'      => $saleId,
                    'car_commission' => $C,
                    'ck'          => $ck->format('Y-m-d'),
                    'dd'          => $dd?->format('Y-m-d'),
                    'held'        => $p['held'],
                    'held_amount' => $p['held_amount'],
                    'main_amount' => $p['main_amount'],
                    'main_payday' => $p['main_payday']?->format('Y-m-d'), // null = DD ว่าง = พักไว้
                    'held_payday' => $p['held_payday']?->format('Y-m-d'),
                ];
            });
    }

    /**
     * มุมมอง "เดือนที่จ่ายเงิน" — คืนทุก payment (ทุกเดือน CK) ที่ครบกำหนดจ่ายในเดือน M
     * (รอบ 10/M และ 20/M) เรตคิดตามเดือน CK ของแต่ละคัน
     * @return Collection ต่อ payment: salecar_id, SaleID, ck, dd, kind(main|held), amount, payday, round(10|20)
     */
    public static function paymentsInMonth(int $year, int $month): Collection
    {
        $mStart = Carbon::create($year, $month, 1)->startOfMonth();
        $mEnd   = Carbon::create($year, $month, 1)->endOfMonth();
        $ymM    = $mStart->format('Y-m');

        // ครอบทุก payment ที่ตกเดือน M: CK ย้อน 2 เดือน..M  หรือ  DD ย้อน 1 เดือน..M
        $ckFrom = $mStart->copy()->subMonthsNoOverflow(2)->startOfMonth();
        $ddFrom = $mStart->copy()->subMonthNoOverflow()->startOfMonth();

        $cars = Salecar::withoutGlobalScopes()
            ->where('brand', self::BRAND)
            ->whereNotNull('DeliveryInCKDate')
            ->whereNotNull('CarOrderID')
            ->salesQualifying()
            ->where(function ($q) use ($ckFrom, $mEnd, $ddFrom) {
                $q->whereBetween('DeliveryInCKDate', [$ckFrom, $mEnd])
                    ->orWhereBetween('DeliveryDate', [$ddFrom, $mEnd]);
            })
            ->get(['id', 'SaleID', 'DeliveryInCKDate', 'DeliveryDate', 'model_id', 'brand']);

        $ckCache = [];
        $payments = collect();
        foreach ($cars as $r) {
            $ck = Carbon::parse($r->DeliveryInCKDate);
            $dd = $r->DeliveryDate ? Carbon::parse($r->DeliveryDate) : null;

            $ckKey = $ck->format('Y-m');
            if (!isset($ckCache[$ckKey])) {
                [$y, $m] = array_map('intval', explode('-', $ckKey));
                $ckCache[$ckKey] = CarCommissionQuery::forMonth($y, $m)['perSale'];
            }
            $C = self::carCommissionOf($r, CarCommissionQuery::entry($ckCache[$ckKey], (int) $r->SaleID, (int) $r->brand));
            $p = self::paymentFor($ck, $dd, $C);

            // ส่วนหลัก (C−H) ที่ตกเดือน M (ข้าม DD ว่าง = พักไว้ ยังไม่จ่าย)
            if ($p['main_amount'] != 0.0 && $p['main_payday'] !== null && $p['main_payday']->format('Y-m') === $ymM) {
                $payments->push([
                    'salecar_id' => (int) $r->id, 'SaleID' => (int) $r->SaleID,
                    'ck' => $ck->format('Y-m-d'), 'dd' => $dd?->format('Y-m-d'),
                    'kind' => 'main', 'amount' => $p['main_amount'],
                    'payday' => $p['main_payday']->format('Y-m-d'),
                    'round' => (int) $p['main_payday']->day === 20 ? 20 : 10,
                ]);
            }
            // ยอดกั๊ก 2000 ที่ตกเดือน M
            if ($p['held_payday'] !== null && $p['held_payday']->format('Y-m') === $ymM) {
                $payments->push([
                    'salecar_id' => (int) $r->id, 'SaleID' => (int) $r->SaleID,
                    'ck' => $ck->format('Y-m-d'), 'dd' => $dd?->format('Y-m-d'),
                    'kind' => 'held', 'amount' => $p['held_amount'],
                    'payday' => $p['held_payday']->format('Y-m-d'),
                    'round' => (int) $p['held_payday']->day === 20 ? 20 : 10,
                ]);
            }
        }
        return $payments;
    }

    /** ค่าคอมรายคัน C ของรถคันหนึ่ง จาก perSale ของเดือน CK ของมัน */
    private static function carCommissionOf($r, ?array $entry): float
    {
        if (!$entry) {
            return 0.0;
        }
        return ($entry['mode'] ?? 'volume') === 'model'
            ? CarCommissionQuery::modelRate((int) $r->brand, $r->model_id !== null ? (int) $r->model_id : null)
            : (float) ($entry['rate'] ?? 0);
    }

    /**
     * ยอดกั๊ก "ยกมา" ที่ถึงกำหนดจ่ายในรอบของเดือน M (จ่ายจริงวันที่ 10/20 ของเดือน M+1)
     * = รถกั๊กของเดือน CK ก่อนหน้า (CK < M) ที่ held_payday ตกวันที่ 10 หรือ 20 ของเดือน M+1
     * @return Collection ต่อคัน: SaleID, salecar_id, H, round(10|20)
     */
    public static function carriedInPerCar(int $year, int $month): Collection
    {
        $payMain = self::mainPaydayCK(Carbon::create($year, $month, 1)); // 10 ของเดือน M+1
        $pay20   = $payMain->copy()->day(20);

        // held_payday จะตกในรอบนี้เมื่อ DeliveryDate อยู่ในช่วง [21 ของเดือน M .. 20 ของเดือน M+1]
        $ddFrom = Carbon::create($year, $month, 1)->day(21)->startOfDay();
        $ddTo   = $payMain->copy()->day(20)->endOfDay();
        $ckEnd  = Carbon::create($year, $month, 1)->startOfMonth(); // CK ต้องก่อนเดือน M

        $cars = Salecar::withoutGlobalScopes()
            ->where('brand', self::BRAND)
            ->whereNotNull('DeliveryInCKDate')
            ->whereNotNull('CarOrderID')
            ->whereNotNull('DeliveryDate')
            ->salesQualifying()
            ->where('DeliveryInCKDate', '<', $ckEnd)
            ->whereBetween('DeliveryDate', [$ddFrom, $ddTo])
            ->get(['id', 'SaleID', 'DeliveryInCKDate', 'DeliveryDate', 'model_id', 'brand']);

        $ckCache = [];
        return $cars->map(function ($r) use (&$ckCache, $pay20) {
            $ckKey = Carbon::parse($r->DeliveryInCKDate)->format('Y-m');
            if (!isset($ckCache[$ckKey])) {
                [$y, $m] = array_map('intval', explode('-', $ckKey));
                $ckCache[$ckKey] = CarCommissionQuery::forMonth($y, $m)['perSale'];
            }
            $C = self::carCommissionOf($r, CarCommissionQuery::entry($ckCache[$ckKey], (int) $r->SaleID, (int) $r->brand));
            $H = self::heldAmount(true, $C);
            $payday = self::heldPayday(Carbon::parse($r->DeliveryDate));

            return [
                'SaleID'     => (int) $r->SaleID,
                'salecar_id' => (int) $r->id,
                'H'          => $H,
                'round'      => ($payday && $payday->isSameDay($pay20)) ? 20 : 10,
            ];
        });
    }

    /**
     * สรุปคอมกั๊ก "ต่อเซลล์" สำหรับรายงานเดือน M (มุมมองเดือน CK) — จ่ายจริงรอบ 10/20 ของเดือน M+1
     * @return Collection SaleID => [
     *   car_total, car_main(ΣC−H),
     *   held_round10, held_round20, held_carry(ยกไปเดือนถัดๆ), held_pending(DD ว่าง),
     *   carried_10, carried_20 (ยกมาจากเดือนก่อน)
     * ]
     */
    public static function paymentBreakdown(int $year, int $month): Collection
    {
        $payMain = self::mainPaydayCK(Carbon::create($year, $month, 1))->format('Y-m-d');
        $pay20   = self::mainPaydayCK(Carbon::create($year, $month, 1))->day(20)->format('Y-m-d');

        $own     = self::perCarForCkMonth($year, $month)->groupBy('SaleID');
        $carried = self::carriedInPerCar($year, $month)->groupBy('SaleID');

        $saleIds = $own->keys()->merge($carried->keys())->unique();

        return $saleIds->mapWithKeys(function ($sid) use ($own, $carried, $payMain, $pay20) {
            $ownCars = $own->get($sid, collect());
            $carr    = $carried->get($sid, collect());

            $carTotal = (float) $ownCars->sum('car_commission');
            $carMain = $h10 = $h20 = $hCarry = $hPending = 0.0;
            foreach ($ownCars as $c) {
                $carMain += $c['main_amount'];
                if (!$c['held']) {
                    continue;
                }
                $pd = $c['held_payday'];
                if ($pd === null) {
                    $hPending += $c['held_amount'];
                } elseif ($pd === $payMain) {
                    $h10 += $c['held_amount'];
                } elseif ($pd === $pay20) {
                    $h20 += $c['held_amount'];
                } else {
                    $hCarry += $c['held_amount'];
                }
            }

            return [$sid => [
                'car_total'    => $carTotal,
                'car_main'     => $carMain,
                'held_round10' => $h10,
                'held_round20' => $h20,
                'held_carry'   => $hCarry,
                'held_pending' => $hPending,
                'carried_10'   => (float) $carr->where('round', 10)->sum('H'),
                'carried_20'   => (float) $carr->where('round', 20)->sum('H'),
            ]];
        });
    }

    /** จำนวนรถ "ส่งมอบหลังวันที่ 10" ต่อเซลล์ ในเดือนที่กำหนด (brand 1) => Collection SaleID => count */
    public static function lateCounts(int $year, int $month): Collection
    {
        $from = Carbon::create($year, $month, 1)->startOfMonth();
        $to   = Carbon::create($year, $month, 1)->endOfMonth();

        return Salecar::withoutGlobalScopes()
            ->whereNotNull('DeliveryInCKDate')
            ->whereNotNull('CarOrderID')
            ->where('brand', self::BRAND)
            ->salesQualifying() // กั๊กเฉพาะยอดขายจริง — ไม่นับ TestDrive/dealer
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
        $thisFrom = Carbon::create($year, $month, 1)->startOfMonth();
        $thisTo   = Carbon::create($year, $month, 1)->endOfMonth();
        $prevFrom = $thisFrom->copy()->subMonthNoOverflow()->startOfMonth();

        // ดึงรถ brand 1 "ส่งมอบหลังวันที่ 10" ของเดือนนี้ + เดือนก่อน ในควิวรีเดียว แล้วแยกเดือนใน PHP
        $late = Salecar::withoutGlobalScopes()
            ->whereNotNull('DeliveryInCKDate')
            ->whereNotNull('CarOrderID')
            ->where('brand', self::BRAND)
            ->salesQualifying() // กั๊กเฉพาะยอดขายจริง — ไม่นับ TestDrive/dealer
            ->whereBetween('DeliveryInCKDate', [$prevFrom, $thisTo])
            ->whereDay('DeliveryInCKDate', '>', self::CUTOFF_DAY)
            ->get(['SaleID', 'DeliveryInCKDate'])
            ->groupBy(fn($r) => Carbon::parse($r->DeliveryInCKDate)->format('Y-m'));

        $thisMonth = ($late[$thisFrom->format('Y-m')] ?? collect())->groupBy('SaleID')->map->count();
        $prevMonth = ($late[$prevFrom->format('Y-m')] ?? collect())->groupBy('SaleID')->map->count();

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
