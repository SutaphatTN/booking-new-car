<?php

namespace App\Services;

use App\Models\Salecar;
use Illuminate\Support\Carbon;

class GPQuery
{
    public static function base($fromDate = null)
    {
        $date = $fromDate
            ? Carbon::createFromFormat('Y-m', $fromDate)->startOfMonth()
            : now()->startOfMonth();

        return Salecar::with([
            'customer.prefix',
            'carOrder.model',
            'carOrder.subModel',
            'carOrder',
            'financeConfirm',
            'remainingPayment.financeInfo',
            'salePurType',
            'saleUser.branchInfo',
            'conStatus',
            'campaigns.campaign.type',
            'campaigns.campaign.appellation',
            'accessories'
        ])
            ->whereNotNull('DeliveryInDMSDate')
            ->whereNotNull('CarOrderID')
            // ->where('con_status', 5) // เดิม: เฉพาะที่ส่งมอบแล้ว — ปิดไว้ให้รถที่มีวัน DMS แล้วแต่ยังรอส่งมอบขึ้นรายงานด้วย
            ->whereNotIn('con_status', [7, 8, 9]) // ตัดถอนจอง/ยึดเงินจอง ออกจากรายงาน
            ->whereMonth('DeliveryInDMSDate', $date->month)
            ->whereYear('DeliveryInDMSDate', $date->year);
    }

    /**
     * "คอมขาย" รายคันของรายงาน GP — ค่าคอมตัวรถจริงของเซลล์เจ้าของใบ
     *
     * เดิมเป็นเลขตายตัว 3500 (หรือค่าที่กรอกเองในหน้าตั้งค่า GP) ซึ่งไม่ตรงกับที่จ่ายจริง
     * ตอนนี้ดึงจากสูตรกลาง CarCommissionQuery ตัวเดียวกับหน้าค่าคอมมิชชั่น/รายงานคอมรายคัน
     * เรตขึ้นกับจำนวนคันของเซลล์คนนั้นใน "เดือน CK" ของรถคันนั้น + แบรนด์ถึงเป้าหรือไม่
     * (brand ที่คิดตามรุ่นก็ใช้สูตรเดียวกัน — amountForCar แยกให้แล้ว)
     *
     * คันที่ไม่เข้าเงื่อนไขคอมตัวรถ = 0 (ไม่ใช่ Retail / type_sale ไม่ใช่ Normal / รถ dealer
     * ของแบรนด์ที่ไม่ให้คอม / ยังไม่มีวัน CK) เพราะเซลล์ไม่ได้คอมคันนั้นจริง ๆ
     *
     * เงื่อนไข "รถแบบไหนนับ" ยืมจาก scope salesQualifying ตัวเดียวกับหน้าค่าคอม (ยิง 1 query)
     * ห้ามก๊อปเงื่อนไขมาเขียนใหม่ที่นี่ เดี๋ยวแก้กติกาแล้วสองที่ไม่ตรงกันอีก
     *
     * @param  \Illuminate\Support\Collection  $rows  ผลของ GPQuery::base()->get()
     * @return array<int,float>  salecars.id => ยอดคอมขาย
     */
    public static function commissionSaleMap($rows): array
    {
        $ids = $rows->pluck('id')->all();

        if (empty($ids)) {
            return [];
        }

        $qualifying = Salecar::salesQualifying()->whereIn('id', $ids)->pluck('id')->flip();

        $ckCache = [];
        $map = [];

        foreach ($rows as $r) {
            if (!isset($qualifying[$r->id]) || empty($r->DeliveryInCKDate)) {
                $map[(int) $r->id] = 0.0;
                continue;
            }

            $ckKey = Carbon::parse($r->DeliveryInCKDate)->format('Y-m');

            if (!isset($ckCache[$ckKey])) {
                [$y, $m] = array_map('intval', explode('-', $ckKey));
                $ckCache[$ckKey] = CarCommissionQuery::forMonth($y, $m)['perSale'];
            }

            $entry = CarCommissionQuery::entry($ckCache[$ckKey], (int) $r->SaleID, (int) $r->brand);
            $map[(int) $r->id] = CarCommissionQuery::amountForCar($r, $entry);
        }

        return $map;
    }
}
