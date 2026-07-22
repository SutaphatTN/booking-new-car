<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

/**
 * เติมชื่อ brand ไว้หน้าชื่อไฟล์ export
 *
 *   ExportFilename::withBrand('ข้อมูลการจอง.xlsx')  →  'Mitsubishi-ข้อมูลการจอง.xlsx'
 *
 * เวลาโหลดรายงานจากหลาย brand มาเก็บรวมกันในเครื่องเดียวจะได้รู้ว่าไฟล์ไหนของ brand ไหน
 * ไม่ต้องเปิดดูก่อน
 *
 * ใช้ brand ที่กำลังทำงานอยู่ (effective brand = ค่าหลังสลับ brand) ไม่ใช่ home brand
 * เพราะรายงานถูก scope ตาม brand ที่สลับอยู่
 *
 * ถ้าไม่ได้ล็อกอิน หรือ brand id ไม่มีใน config/brand.php ('names') → คืนชื่อเดิม ไม่เติมอะไร
 */
class ExportFilename
{
    public static function withBrand(string $filename): string
    {
        $brand = (int) (Auth::user()->brand ?? 0);
        $name  = config("brand.names.{$brand}");

        return $name ? "{$name}-{$filename}" : $filename;
    }
}
