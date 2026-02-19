<?php

namespace App\Services;

use App\Models\Salecar;

class SaleCommissionQuery
{
    public static function base($user, $filterByUser = false, $month = null, $year = null)
    {
        $month = $month ?? now()->month;
        $year  = $year  ?? now()->year;

        return Salecar::with([
            'saleUser.branchInfo',
            'customer.prefix',
            'carOrder.model',
            'carOrder.subModel',
            'carOrder',
            'remainingPayment',
            'turnCar'
        ])
            ->whereNotNull('DeliveryInCKDate')
            ->whereNotNull('CarOrderID')

            ->whereMonth('DeliveryInCKDate', $month)
            ->whereYear('DeliveryInCKDate', $year)

            ->when($filterByUser && $user->role === 'sale', function ($q) use ($user) {
                $q->where('SaleID', $user->id);
            });
    }
}
