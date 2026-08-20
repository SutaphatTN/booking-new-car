<?php

namespace App\Services;

use App\Models\CarOrder;
use App\Models\Salecar;
use Illuminate\Support\Facades\Auth;

class BookingReportQuery
{
  /**
   * GWM(2) เป็นแบรนด์เดียวที่แยกสาขาจริง — รายงานนี้ต้องเห็นรถรวมทั้ง 2 สาขา
   * (ชีทรายคันมีคอลัมน์ "สาขา" แยกให้แล้ว) จึงปลด scope สาขาทิ้งแล้วล็อก brand เองแทน
   * brand อื่นสาขาเดียวอยู่แล้ว ปล่อยให้ scope เดิมทำงานตามปกติ
   */
  private static function scoped(string $model)
  {
    $user = Auth::user();

    if ((int) ($user->brand ?? 0) === 2) {
      return $model::withoutGlobalScope('userAccess')->where('brand', 2);
    }

    return $model::query();
  }

  // base query
  public static function baseCarOrders()
  {
    return self::scoped(CarOrder::class)
      ->with([
        'model',
        'subModel',
        'orderStatus',
        'purchaseType',
        'gwmColor',
        'interiorColor',
        'branchInfo',
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
    return self::scoped(Salecar::class)
      ->with([
      'customer.prefix',
      'saleUser',
      'conStatus',
      'subModel',
      'remainingPayment',
      'branchInfo',
    ])
      ->whereNull('CarOrderID')
      ->where('model_id', $modelId)
      ->whereNotIn('con_status', [5, 7, 8, 9]);
  }

  public static function agingCars()
  {
    return self::stockCars()
      ->whereNotNull('order_stock_date');
  }
}
