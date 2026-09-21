<?php

namespace App\Services;

use App\Mail\CarChangedMail;
use App\Mail\CarDeliveredMail;
use App\Models\CarOrder;
use App\Models\Salecar;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

/**
 * เมลสาย "ส่งมอบรถ" — แจ้งปลายทางให้เอา VIN ไปจบยอดที่ธนาคาร
 *
 * กันยิงซ้ำที่ "คันรถ" ไม่ใช่ "ใบจอง" — delivered_notified_car_order_id บอกว่าเมลฉบับล่าสุด
 * พูดถึงรถคันไหน ถ้าใบจองสลับคันทีหลัง คันใหม่จะได้เมลของตัวเองเสมอ
 * (ของเดิมเช็คแค่ timestamp ใบจองที่สลับคันหลังแจ้งไปแล้วจึงเงียบถาวร)
 *
 * การสลับคันเกิดได้จากหลายหน้า (ใบจอง / ใบสั่งรถ / ลบใบสั่งรถ) จึงรวมไว้ที่เดียว
 * และต้องเรียก "หลัง commit" เสมอ — เมลล้มไม่ควรทำให้การบันทึกล้มตาม
 */
class DeliveryNotifier
{
    /** relation ที่เทมเพลตเมลใช้ — โหลดทีเดียว กัน N+1 */
    private const MAIL_RELATIONS = [
        'customer.prefix',
        'model',
        'subModel',
        'carOrder',
        'saleUser.branchInfo',
        'gwmColor',
        'interiorColor',
        'conStatus',
        'remainingPayment.financeInfo',
    ];

    public static function recipients(): array
    {
        return (array) config('delivery.notify_to', []);
    }

    /**
     * แจ้ง "ส่งมอบรถ" — ยิงเมื่อมีข้อมูลส่งมอบตัวใดตัวหนึ่ง และยังไม่เคยแจ้ง "คันปัจจุบัน"
     */
    public static function notifyDelivered(Salecar $saleCar): bool
    {
        if (!$saleCar->CarOrderID || self::alreadyNotified($saleCar)) {
            return false;
        }

        $triggers = self::deliveryTriggers($saleCar);
        if (empty($triggers)) {
            return false;
        }

        try {
            $saleCar->load(self::MAIL_RELATIONS);
            Mail::to(self::recipients())->send(new CarDeliveredMail($saleCar, $triggers));

            $mark = ['delivered_notified_at' => now()];
            if (self::tracksCarOrder()) {
                $mark['delivered_notified_car_order_id'] = $saleCar->CarOrderID;
            }
            $saleCar->update($mark);

            return true;
        } catch (\Throwable $e) {
            report($e); // ไม่มาร์ค = รอบบันทึกถัดไปลองใหม่เอง
            return false;
        }
    }

    /**
     * แจ้ง "เปลี่ยนคันรถ" — ยิงเฉพาะเมื่อคันเดิมเคยส่งเมลแจ้งส่งมอบออกไปแล้วเท่านั้น
     * ไม่งั้นปลายทางจะค้างอยู่กับ VIN ที่ไม่ได้ส่งมอบ (เคส VIN ...553049 ที่รถกลับเข้าสต็อก)
     * สลับคันก่อนมีเมลแจ้งส่งมอบ = เรื่องปกติของงานขาย ไม่ต้องกวนปลายทาง
     */
    public static function notifyCarChanged(Salecar $saleCar, $oldCarOrderId, $newCarOrderId): bool
    {
        $notifiedCarOrderId = $saleCar->delivered_notified_car_order_id;

        if (!$notifiedCarOrderId
            || (int) $oldCarOrderId === (int) $newCarOrderId
            || (int) $notifiedCarOrderId !== (int) $oldCarOrderId
        ) {
            return false;
        }

        try {
            $saleCar->load(self::MAIL_RELATIONS);
            Mail::to(self::recipients())->send(new CarChangedMail(
                $saleCar,
                self::carOrderForMail($oldCarOrderId),
                self::carOrderForMail($newCarOrderId)
            ));
            return true;
        } catch (\Throwable $e) {
            report($e);
            return false;
        }
    }

    /**
     * เงื่อนไขยิงเมลแจ้งส่งมอบ — คงเดิมทั้ง 4 ตัว (จงใจยิงตั้งแต่มีวันนัด ไม่รอ con_status = 5
     * เพราะต้องรีบปิดรถกับธนาคารเพื่อลดดอกเบี้ย)
     */
    public static function deliveryTriggers(Salecar $saleCar): array
    {
        $triggers = [];
        if ((int) $saleCar->con_status === 5) $triggers[] = 'สถานะ = ส่งมอบ';
        if ($saleCar->DeliveryDate)           $triggers[] = 'วันส่งมอบจริง (แจ้งประกัน)';
        if ($saleCar->DeliveryInDMSDate)      $triggers[] = 'วันส่งมอบของบริษัท (DMS)';
        if ($saleCar->DeliveryInCKDate)       $triggers[] = 'วันส่งมอบของฝ่ายขาย (CK)';

        return $triggers;
    }

    private static function alreadyNotified(Salecar $saleCar): bool
    {
        // ยังไม่ได้รัน SQL เพิ่มคอลัมน์ (เช่นขึ้นโค้ดก่อน) → ถอยไปใช้กติกาเดิม "ยิงครั้งเดียวต่อใบจอง"
        // ดีกว่าปล่อยให้ยิงซ้ำทุกครั้งที่กดบันทึก
        if (!self::tracksCarOrder()) {
            return (bool) $saleCar->delivered_notified_at;
        }

        return $saleCar->delivered_notified_at
            && (int) $saleCar->delivered_notified_car_order_id === (int) $saleCar->CarOrderID;
    }

    /** มีคอลัมน์ delivered_notified_car_order_id แล้วหรือยัง — เช็คครั้งเดียวต่อ request */
    private static function tracksCarOrder(): bool
    {
        static $has = null;

        return $has ??= Schema::hasColumn('salecars', 'delivered_notified_car_order_id');
    }

    private static function carOrderForMail($carOrderId): ?CarOrder
    {
        return $carOrderId
            ? CarOrder::with(['model', 'subModel'])->find($carOrderId)
            : null;
    }
}
