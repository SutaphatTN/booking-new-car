<?php

namespace App\Services;

use App\Models\Salecar;
use Illuminate\Support\Carbon;

class SaleCommissionQuery
{
    public static function base($user, $filterByUser = false, $fromDate = null, $toDate = null)
    {
        $fromDate = $fromDate
            ? Carbon::parse($fromDate)->startOfDay()
            : now()->startOfMonth();

        $toDate = $toDate
            ? Carbon::parse($toDate)->endOfDay()
            : now()->endOfDay();

        return Salecar::with([
            'saleUser.branchInfo',
            'customer.prefix',
            'carOrder.model',
            'carOrder.subModel',
            'carOrder',
            'remainingPayment',
            'turnCar',
            'model', // ใช้คิดเคสอนุมัติ (over_budget/per_budget) ใน effectiveBalanceCommission()
        ])
            ->whereNotNull('DeliveryInCKDate')
            ->whereNotNull('CarOrderID')
            ->salesQualifying() // นับเฉพาะยอดขายจริง (Retail + Normal + ไม่ใช่ dealer) — ตัด TestDrive/dealer ออกจากคอม
            ->whereBetween('DeliveryInCKDate', [
                $fromDate,
                $toDate
            ])
            ->when($filterByUser && in_array($user->role, ['sale', 'lead_sale']), function ($q) use ($user) {
                $q->where('SaleID', $user->id);
            });
    }
}
