<?php

namespace App\Services;

use App\Models\CarOrder;
use App\Models\Salecar;
use App\Support\BrandFeature;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * แหล่งข้อมูลของรายงาน "ข้อมูลรถและการจอง" (BookingExport)
 *
 * DB อยู่ remote (RTT ~50 ms/query) → ต้นทุนของรายงานคือ "จำนวน query" ไม่ใช่ปริมาณข้อมูล
 * จึงโหลดทั้งรายงานครั้งเดียวด้วย allCars() + allOrphanSales() แล้วให้แต่ละ sheet
 * กรองเอาเองใน PHP (helper ด้านล่าง) — ห้ามใส่ query รายรุ่น/ราย sheet กลับเข้ามาอีก
 */
class BookingReportQuery
{
  /**
   * brand ที่มีหลายสาขา (config brand.multi_branch_brands) ต้องเห็นรถรวมทุกสาขา
   * (ชีทรายคันมีคอลัมน์ "สาขา" แยกให้แล้ว) จึงปลด scope สาขาทิ้งแล้วล็อก brand เองแทน
   * brand ที่มีสาขาเดียวปล่อยให้ scope เดิมทำงานตามปกติ
   */
  private static function scoped(string $model)
  {
    $brand = (int) (Auth::user()->brand ?? 0);

    if (BrandFeature::hasMultipleBranches($brand)) {
      return $model::withoutGlobalScope('userAccess')->where('brand', $brand);
    }

    return $model::query();
  }

  /**
   * ปลด scope ให้ relation salecars ตรงกับที่ปลดให้ car_order ด้านบน
   * ไม่งั้นจะเจอเคส "รถของอีกสาขาโผล่ในรายงาน แต่ชื่อผู้จอง/เซลล์/สถานะว่าง"
   * เพราะตัวรถหลุด scope มาแล้ว แต่ใบจองยังโดน userAccess กรองสาขาทิ้ง
   */
  private static function salecarsConstraint(): \Closure
  {
    $brand = (int) (Auth::user()->brand ?? 0);

    if (BrandFeature::hasMultipleBranches($brand)) {
      return fn($q) => $q->withoutGlobalScopes(['userAccess', 'saleTeam'])
        ->with('saleTeam')
        ->where('salecars.brand', $brand);
    }

    // ปลดตัวกรอง "ทีม" ทิ้งเสมอ — สต๊อกรถเป็นของกลาง ต้องเห็นครบทุกคัน
    // ถ้าปล่อยให้กรอง คันที่ทีมอื่นจองไว้จะโผล่มาเป็น "รถว่าง" ซึ่งอันตรายกว่าเห็นเยอะไป
    // (รายละเอียดผู้จองของทีมอื่นถูกปิดทับอีกชั้นด้วย hidesBookingDetail() ด้านล่าง)
    return fn($q) => $q->withoutGlobalScope('saleTeam')->with('saleTeam');
  }

  /**
   * ใบจองใบนี้อยู่นอกกลุ่มทีมของคนดูไหม
   * true = โชว์ได้แค่ "มีคนจองแล้ว" ห้ามโชว์ชื่อลูกค้า/ชื่อเซลล์
   */
  public static function hidesBookingDetail($sale): bool
  {
    return $sale ? Salecar::outsideViewerTeams($sale->sale_team_id) : false;
  }

  /** ข้อความแทนชื่อลูกค้า/เซลล์ ของใบจองทีมอื่น */
  public const MASKED = 'ทีมอื่น';

  // base query
  public static function baseCarOrders()
  {
    return self::scoped(CarOrder::class)
      ->with([
        // ต้องประกาศก่อน 'salecars.*' — Laravel ยึด constraint ของ key ที่ประกาศไว้ก่อน
        'salecars' => self::salecarsConstraint(),
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

  /** รถทั้งหมดที่รายงานนี้ใช้ (ทั้ง test drive และ stock) — โหลดครั้งเดียวต่อการ export 1 ครั้ง */
  public static function allCars(): Collection
  {
    return self::baseCarOrders()->get();
  }

  /** ใบจองที่ยังไม่ผูกรถ ทุกรุ่น — โหลดครั้งเดียวเช่นกัน */
  public static function allOrphanSales(): Collection
  {
    return self::scoped(Salecar::class)
      ->with([
        'customer.prefix',
        'saleUser',
        'conStatus',
        'subModel',
        'remainingPayment',
        'branchInfo',
        // ชีทรายรุ่นอ่านสีจากใบจองด้วย ถ้าไม่ eager จะกลายเป็น N+1 รายใบ
        'gwmColor',
        'interiorColor',
        'saleTeam',
      ])
      ->whereNull('CarOrderID')
      ->whereNotIn('con_status', [5, 7, 8, 9])
      ->get();
  }

  // ── helper กรองใน PHP (แทน query เดิมรายชีท) ───────────────────────────────

  /** เทียบเท่า where('purchase_type', 2) */
  public static function stockOnly(Collection $cars): Collection
  {
    return $cars->filter(fn($c) => (int) $c->purchase_type === 2)->values();
  }

  /** เทียบเท่า where('purchase_type', 1) */
  public static function testDriveOnly(Collection $cars): Collection
  {
    return $cars->filter(fn($c) => (int) $c->purchase_type === 1)->values();
  }

  /** เทียบเท่า whereNotNull('order_stock_date') */
  public static function withStockDate(Collection $cars): Collection
  {
    return $cars->filter(fn($c) => !is_null($c->order_stock_date))->values();
  }

  /** เทียบเท่า where('model_id', $modelId) — NULL ไม่เข้าเงื่อนไข เหมือนฝั่ง SQL */
  public static function byModel(Collection $items, $modelId): Collection
  {
    return $items->filter(
      fn($i) => !is_null($i->model_id) && (int) $i->model_id === (int) $modelId
    )->values();
  }
}
