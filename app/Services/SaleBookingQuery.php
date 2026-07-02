<?php

namespace App\Services;

use App\Models\Salecar;

class SaleBookingQuery
{
    public static function base()
    {
        return Salecar::with([
            'customer.prefix',
            'customer.documentAddress',
            'customer.currentAddress',
            'carOrder.model',
            'carOrder.subModel',
            'carOrder.orderStatus',
            'carOrder',
            'gwmColor',
            'interiorColor',
            'financeConfirm',
            'remainingPayment',
            'remainingPayment.financeInfo',
            'type',
        ]);
    }
}
