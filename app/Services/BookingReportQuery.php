<?php

namespace App\Services;

use App\Models\CarOrder;
use App\Models\Salecar;

class BookingReportQuery
{
  // base query
  public static function baseCarOrders()
  {
    return CarOrder::query()
      ->with([
        'model',
        'subModel',
        'orderStatus',
        'salecars.customer.prefix',
        'salecars.saleUser',
        'salecars.carOrderHistories',
        'salecars.remainingPayment',
        'salecars.conStatus',
      ])
      ->whereIn('status', ['approved', 'finished'])
      ->whereNot('car_status', 'Delivered');
  }

  public static function stockCars()
  {
    return self::baseCarOrders()
      ->where('purchase_type', 2);
  }

  public static function testDriveCars()
  {
    return self::baseCarOrders()
      ->where('purchase_type', 1);
  }

  // Query ตาม Model
  public static function carsByModel($modelId)
  {
    return self::stockCars()
      ->where('model_id', $modelId);
  }

  // รถยังไม่ผูก
  public static function orphanSalesByModel($modelId)
  {
    return Salecar::with([
      'customer.prefix',
      'saleUser',
      'conStatus',
      'subModel'
    ])
      ->whereNull('CarOrderID')
      ->where('model_id', $modelId)
      ->whereNotIn('con_status', [5, 9]);
  }

  public static function agingCars()
  {
    return self::stockCars()
      ->whereNotNull('order_stock_date');
  }
}
