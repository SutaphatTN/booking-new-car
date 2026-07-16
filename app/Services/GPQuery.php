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
}
