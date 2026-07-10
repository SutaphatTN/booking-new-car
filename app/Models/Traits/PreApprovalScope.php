<?php

namespace App\Models\Traits;

/**
 * ซ่อน record "ขออนุมัติเกินงบล่วงหน้า (ยังไม่จอง)" ออกจากทุก query ของ salecars โดยอัตโนมัติ
 * → หน้า/รายงาน/สรุปคอมเดิมทั้งหมดไม่ต้องแก้อะไรเลย
 *
 *   is_pre_approval = 1 → ยังเป็นคำขอล่วงหน้า (ยังไม่จอง)      → ซ่อน
 *   is_pre_approval = 0 → ใบจองปกติ (รวมที่แปลงมาจากคำขอแล้ว) → เห็นตามปกติ
 *
 * โมดูลขออนุมัติล่วงหน้าเรียก ->withoutGlobalScope('preApproval') เพื่อดึงมาแสดง
 *
 * หมายเหตุ: query ที่ใช้ withoutGlobalScopes() (ปลดทุก scope) จะเห็น record นี้ด้วย
 *   - หน้าอนุมัติทางเมล (where approval_token) → ตั้งใจให้เห็น เพื่อกดอนุมัติได้
 *   - คอมมิชชั่น/คอมกั๊ก (whereNotNull DeliveryInCKDate) → ไม่เข้าอยู่แล้ว เพราะยังไม่ส่งมอบ
 */
trait PreApprovalScope
{
    protected static function bootPreApprovalScope()
    {
        static::addGlobalScope('preApproval', function ($query) {
            $table = $query->getModel()->getTable();
            $query->where($table . '.is_pre_approval', 0);
        });
    }
}
