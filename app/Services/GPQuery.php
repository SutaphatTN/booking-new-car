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
            'campaigns.campaign.type'
        ])
            ->whereNotNull('DeliveryInCKDate')
            ->whereNotNull('CarOrderID')
            ->whereMonth('DeliveryInCKDate', $date->month)
            ->whereYear('DeliveryInCKDate', $date->year);
    }
}
