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
            'turnCar'
        ])
            ->whereNotNull('DeliveryInCKDate')
            ->whereNotNull('CarOrderID')
            ->whereBetween('DeliveryInCKDate', [
                $fromDate,
                $toDate
            ])
            ->when($filterByUser && $user->role === 'sale', function ($q) use ($user) {
                $q->where('SaleID', $user->id);
            });
    }
}
