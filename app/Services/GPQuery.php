<?php

namespace App\Services;

use App\Models\Salecar;

class GPQuery
{
    public static function base($month = null, $year = null)
    {
        $month = $month ?? now()->month;
        $year  = $year  ?? now()->year;

        return Salecar::with([
            'customer.prefix',
            'carOrder.model',
            'carOrder.subModel',
            'carOrder',
            'financeConfirm'
        ])
            ->whereNotNull('DeliveryInCKDate')
            ->whereNotNull('CarOrderID')
            ->whereMonth('DeliveryInCKDate', $month)
            ->whereYear('DeliveryInCKDate', $year);
    }
}
