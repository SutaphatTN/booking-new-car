<?php

namespace App\Services;

use App\Models\Salecar;
use App\Models\StaffCommissionMonthly;
use Illuminate\Support\Carbon;

/**
 * ค่าคอมฝ่ายสนับสนุน (ผู้จัดการ / แอดมิน / ทะเบียน / การตลาด)
 *
 * ต่างจากค่าคอมฝ่ายขายตรงที่ไม่ได้คิดรายคันของตัวเอง แต่คิดจาก "ยอดขายรวม" ของแบรนด์
 * ที่คนนั้นดูแล ตามขั้นบันไดใน config/staff_commission.php
 *
 * ฐานการนับรถ = ชุดเดียวกับค่าคอมฝ่ายขาย (ตัด CK ในเดือนนั้น + ผ่าน scope salesQualifying)
 * ไม่งั้นจำนวนคันที่ผู้จัดการเห็นจะไม่ตรงกับที่เซลล์เห็น
 * ยกเว้นคนที่ตั้ง date_field ไว้ใน config (เช่น ผจก. GWM นับตาม DeliveryInDMSDate)
 */
class StaffCommissionQuery
{
    /** จำนวนคันต่อเดือน memo ต่อ request — ถูกเรียกซ้ำทุกคนในตาราง */
    private static array $countMemo = [];

    public static function flush(): void
    {
        self::$countMemo = [];
    }

    /** เริ่มใช้ตั้งแต่เดือน config('staff_commission.start') */
    public static function isActiveMonth(int $year, int $month): bool
    {
        $start = config('staff_commission.start');

        return !$start || sprintf('%04d-%02d', $year, $month) >= $start;
    }

    /** user id ทั้งหมดที่มีสิทธิ์ได้ค่าคอมฝ่ายสนับสนุน */
    public static function staffIds(): array
    {
        return array_map('intval', array_keys(config('staff_commission.staff', [])));
    }

    public static function isStaff(int $userId): bool
    {
        return in_array($userId, self::staffIds(), true);
    }

    /**
     * ช่องกรอกเองของคนนั้น = extras ของตัวเอง (เช่น LAS/PDS) + common_extras ที่ทุกคนมี
     * ใช้ตัวนี้ทั้งตอนคิดยอดและตอนบันทึก (controller) จะได้รับ key ชุดเดียวกัน
     */
    public static function extrasConfigFor(int $userId): array
    {
        return array_merge(
            (array) config("staff_commission.staff.$userId.extras", []),
            (array) config('staff_commission.common_extras', [])
        );
    }

    /**
     * นับรถที่เข้าเกณฑ์คอมของเดือนนั้น ตามเงื่อนไขของก้อน (brand / รุ่น)
     * ปลด userAccess + saleTeam เพราะเป็นยอดระดับบริษัท ไม่ใช่ของคนเปิดดู
     */
    /** คอลัมน์วันที่ที่ยอมให้ตั้งใน config (กันพิมพ์ชื่อคอลัมน์ผิด/แปลกปลอมเข้า query) */
    private const DATE_FIELDS = ['DeliveryInCKDate', 'DeliveryInDMSDate'];

    public static function countCars(int $year, int $month, array $bucket, string $dateField = 'DeliveryInCKDate'): int
    {
        if (!in_array($dateField, self::DATE_FIELDS, true)) {
            $dateField = 'DeliveryInCKDate';
        }

        $key = $year . '-' . $month . '|' . md5(json_encode([
            $bucket['brands'] ?? [],
            $bucket['models'] ?? [],
            $bucket['models_not'] ?? [],
            $dateField,
        ]));

        if (isset(self::$countMemo[$key])) {
            return self::$countMemo[$key];
        }

        $from = Carbon::create($year, $month, 1)->startOfMonth();
        $to   = Carbon::create($year, $month, 1)->endOfMonth();

        $q = Salecar::withoutGlobalScopes(['userAccess', 'saleTeam'])
            ->whereNotNull($dateField)
            ->whereNotNull('CarOrderID')
            ->salesQualifying()
            ->whereBetween($dateField, [$from, $to]);

        if (!empty($bucket['brands'])) {
            $q->whereIn('brand', $bucket['brands']);
        }
        if (!empty($bucket['models'])) {
            $q->whereIn('model_id', $bucket['models']);
        }
        if (!empty($bucket['models_not'])) {
            $q->whereNotIn('model_id', $bucket['models_not']);
        }

        return self::$countMemo[$key] = $q->count();
    }

    /**
     * ขั้นที่ใช้ = ขั้นสูงสุดที่ min <= จำนวนคัน (ต่ำกว่าขั้นแรก = ไม่ได้)
     * เกินขั้นสุดท้ายให้คงเรตขั้นสุดท้าย (กติกาเดียวกับตารางเรตคอมตัวรถ)
     */
    public static function tierFor(array $tiers, int $count): ?array
    {
        $hit = null;
        foreach ($tiers as $t) {
            if ($count >= (int) $t['min']) {
                $hit = $t;
            }
        }

        return $hit;
    }

    /**
     * คิดค่าคอมของคนเดียว 1 เดือน
     *
     * @return array{
     *   active:bool, label:string, buckets:array, extras:array,
     *   car_total:float, extra_total:float, total:float
     * }
     */
    public static function forStaff(int $userId, int $year, int $month): array
    {
        $conf = config("staff_commission.staff.$userId");

        $empty = [
            'active' => false, 'label' => '-', 'buckets' => [], 'extras' => [],
            'car_total' => 0.0, 'extra_total' => 0.0, 'total' => 0.0,
        ];

        if (!$conf || !self::isActiveMonth($year, $month)) {
            return $empty;
        }

        $ym = sprintf('%04d-%02d', $year, $month);
        $saved = StaffCommissionMonthly::where([
            'user_id' => $userId,
            'year'    => $year,
            'month'   => $month,
        ])->first();

        // ── ก้อนที่คิดจากยอดขาย ──
        $buckets = [];
        $carTotal = 0.0;

        // นับทุกก้อนก่อน — ก้อนที่มี requires ต้องดูจำนวนคันของก้อนอื่น (ซึ่งอาจอยู่ลำดับหลัง)
        // วันที่ที่ใช้ตัดเดือน : ระดับก้อน > ระดับคน > DeliveryInCKDate
        $counts = [];
        foreach ($conf['buckets'] ?? [] as $b) {
            $counts[$b['name']] = self::countCars($year, $month, $b, $b['date_field'] ?? $conf['date_field'] ?? 'DeliveryInCKDate');
        }

        foreach ($conf['buckets'] ?? [] as $b) {
            $count = $counts[$b['name']];

            // เงื่อนไขพ่วง : ก้อนอื่นต้องถึงจำนวนคันก่อน ก้อนนี้ถึงจะได้
            $req = $b['requires'] ?? null;
            if ($req && ($counts[$req['bucket']] ?? 0) < (int) $req['min']) {
                $buckets[] = [
                    'name'   => $b['name'],
                    'count'  => $count,
                    'mode'   => $b['mode'] ?? 'per_car',
                    'rate'   => null,
                    'amount' => 0.0,
                    'note'   => 'ไม่ได้ — ' . $req['bucket'] . ' ต้องถึง ' . (int) $req['min'] . ' คัน (ได้ ' . ($counts[$req['bucket']] ?? 0) . ')',
                ];
                continue;
            }

            // การันตี : เดือนที่ยังไม่เกินวันตัด ได้ยอดคงที่แทนการคิดตามขั้น
            $guarantee = $b['guarantee'] ?? null;
            if ($guarantee && $ym <= $guarantee['until']) {
                $buckets[] = [
                    'name'      => $b['name'],
                    'count'     => $count,
                    'mode'      => 'guarantee',
                    'rate'      => null,
                    'amount'    => (float) $guarantee['amount'],
                    'note'      => 'คอมการันตีถึงเดือน ' . $guarantee['until'],
                ];
                $carTotal += (float) $guarantee['amount'];
                continue;
            }

            $tier = self::tierFor($b['tiers'] ?? [], $count);
            $rate = $tier ? (float) $tier['rate'] : 0.0;
            $mode = $b['mode'] ?? 'per_car';
            $amount = $tier ? ($mode === 'per_car' ? $rate * $count : $rate) : 0.0;

            // เพดานของก้อน (ถ้ากำหนดไว้) — คิดเรตตามปกติก่อน แล้วค่อยตัดที่เพดาน
            $cap = $b['cap'] ?? null;
            $capped = $cap !== null && $amount > (float) $cap;
            if ($capped) {
                $amount = (float) $cap;
            }

            $note = $tier
                ? ($mode === 'per_car'
                    ? 'ขั้น ' . $tier['min'] . '+ คัน × ' . number_format($rate, 0) . '/คัน'
                    : 'ขั้น ' . $tier['min'] . '+ คัน (เหมาจ่าย)')
                : 'ยังไม่ถึงขั้นต่ำ';
            if ($capped) {
                $note .= ' — ตัดที่เพดาน ' . number_format((float) $cap, 0);
            }

            $buckets[] = [
                'name'   => $b['name'],
                'count'  => $count,
                'mode'   => $mode,
                'rate'   => $tier ? $rate : null,
                'cap'    => $cap,
                'capped' => $capped,
                'amount' => $amount,
                'note'   => $note,
            ];
            $carTotal += $amount;
        }

        // ── ช่องที่กรอก/ติ๊กเอง ──
        $savedExtras = (array) ($saved->extras ?? []);
        $extras = [];
        $extraTotal = 0.0;

        foreach (self::extrasConfigFor($userId) as $e) {
            if (($e['type'] ?? 'money') === 'bool') {
                $on = (bool) ($savedExtras[$e['key']] ?? false);
                // ต้อง array_merge ไม่ใช่ $e + [...] เพราะ config มี key 'amount' อยู่แล้ว
                // ตัวดำเนินการ + จะเก็บค่าฝั่งซ้ายไว้ ทำให้ได้ยอดเต็มทั้งที่ไม่ได้ติ๊ก
                $extras[] = array_merge($e, [
                    'value'  => $on,
                    // ยอดถ้าติ๊ก — เก็บแยกไว้ให้หน้าจอใช้ (amount จะเป็น 0 ตอนไม่ติ๊ก)
                    'bonus'  => (float) ($e['amount'] ?? 0),
                    'amount' => $on ? (float) ($e['amount'] ?? 0) : 0.0,
                ]);
            } else {
                // ยังไม่เคยบันทึก → ใช้ค่าตั้งต้นจาก config
                $value = array_key_exists($e['key'], $savedExtras)
                    ? (float) $savedExtras[$e['key']]
                    : (float) ($e['default'] ?? 0);
                // ช่องหัก (deduct) กรอกเป็นบวก → amount ติดลบ เพื่อให้ยอดรวมลบออกเอง
                $extras[] = array_merge($e, [
                    'value'      => $value,
                    'amount'     => !empty($e['deduct']) && $value != 0 ? -abs($value) : $value,
                    'note_value' => (string) ($savedExtras[$e['key'] . '_note'] ?? ''),
                ]);
            }
            $extraTotal += end($extras)['amount'];
        }

        return [
            'active'      => true,
            'label'       => $conf['label'] ?? '-',
            'buckets'     => $buckets,
            'extras'      => $extras,
            'car_total'   => $carTotal,
            'extra_total' => $extraTotal,
            'total'       => $carTotal + $extraTotal,
            'note'        => $saved->note ?? '',
        ];
    }

    /**
     * ทุกคนในเดือนนั้น (เรียงตามยอด) — ใช้ในหน้ารายชื่อ
     * @param array|null $onlyIds จำกัดเฉพาะ user id เหล่านี้ (คนที่ไม่ใช่ admin/md/gm เห็นแค่ของตัวเอง)
     */
    public static function forMonth(int $year, int $month, ?array $onlyIds = null): array
    {
        $ids = self::staffIds();
        if ($onlyIds !== null) {
            $ids = array_values(array_intersect($ids, array_map('intval', $onlyIds)));
        }

        $out = [];
        foreach ($ids as $id) {
            $out[$id] = self::forStaff($id, $year, $month);
        }

        return $out;
    }
}
