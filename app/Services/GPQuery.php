<?php

namespace App\Services;

use App\Models\Salecar;
use Illuminate\Support\Carbon;

class GPQuery
{
    public static function base($fromDate = null, $toDate = null)
    {
        $fromDate = $fromDate
            ? Carbon::parse($fromDate)->startOfDay()
            : now()->startOfMonth();

        $toDate = $toDate
            ? Carbon::parse($toDate)->endOfDay()
            : now()->endOfDay();

        return Salecar::with([
            'customer.prefix',
            'carOrder.model',
            'carOrder.subModel',
            'carOrder',
            'financeConfirm'
        ])
            ->whereNotNull('DeliveryInCKDate')
            ->whereNotNull('CarOrderID')
            ->whereBetween('DeliveryInCKDate', [
                $fromDate,
                $toDate
            ]);
    }
}
