<?php

namespace App\Services;

use App\Models\Salecar;

class SaleBookingQuery
{
    public static function base()
    {
        return Salecar::with([
            'customer.prefix',
            'carOrder.model',
            'carOrder.subModel',
            'carOrder.orderStatus',
            'carOrder',
            'gwmColor',
            'interiorColor',
            'financeConfirm',
            'remainingPayment',
            'remainingPayment.financeInfo',
        ]);
    }
}
