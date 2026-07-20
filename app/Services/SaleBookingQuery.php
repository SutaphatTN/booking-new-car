<?php

namespace App\Services;

use App\Models\CarOrder;
use App\Models\Salecar;

class SaleBookingQuery
{
    /**
     * base สำหรับ "รายงานข้อมูลประมาณการ" เท่านั้น
     *  - นับเฉพาะประเภทการขาย Normal (ตัด Test Drive / Dealer)
     *  - ถ้าผูกรถแล้ว ต้องเป็นรถประเภท Retail ; ใบจองที่ยังไม่ผูกรถยังนับอยู่ (เป็นยอดที่ต้องพยากรณ์)
     *  หมายเหตุ: แยกจาก base() เพราะ base() ถูกใช้กับรายงานการจอง/ส่งมอบรายเดือนด้วย
     */
    /**
     * base สำหรับ "รายงานประมาณการเซลล์"
     *  - นับประเภทการขาย Normal + Test Drive (ตัด Dealer)
     *  - ประเภทการซื้อรถใน CarOrder นับทั้งหมด (ไม่กรอง)
     */
    public static function estimatedSale()
    {
        return self::base()
            ->whereIn('type_sale', [Salecar::TYPE_SALE_NORMAL, Salecar::TYPE_SALE_TEST_DRIVE]);
    }

    /** เลือก base query ตามชนิดรายงาน : 'sale' = ประมาณการเซลล์ , อื่น ๆ = ข้อมูลประมาณการ */
    public static function forReport(string $mode)
    {
        return $mode === 'sale' ? self::estimatedSale() : self::estimated();
    }

    /** คอลัมน์วันที่ที่ใช้กรองเดือน ตามชนิดรายงาน */
    public static function dateColumnFor(string $mode): string
    {
        return $mode === 'sale' ? 'DeliveryInCKDate' : 'DeliveryEstimateDate';
    }

    public static function estimated()
    {
        return self::base()
            ->where('type_sale', Salecar::TYPE_SALE_NORMAL)
            ->where(function ($q) {
                $q->whereNull('CarOrderID')
                    ->orWhereHas('carOrder', function ($c) {
                        // ปลด userAccess scope ของ CarOrder — ใบจองถูก scope มาแล้ว
                        // กันเคสรถถูกสั่งคนละสาขากับที่ขาย แล้วโดนตัดทิ้งผิด ๆ
                        $c->withoutGlobalScope('userAccess')
                            ->where('purchase_type', CarOrder::PURCHASE_TYPE_RETAIL);
                    });
            });
    }

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
