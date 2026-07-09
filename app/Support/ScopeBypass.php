<?php

namespace App\Support;

/**
 * สวิตช์ปิด global scope ชั่วคราวภายใน request เดียว
 *
 * ใช้กับ flow อนุมัติผ่านลิงก์ในอีเมล (token) ที่ผู้กดอาจล็อกอินอยู่คนละ brand
 * กับข้อมูล (เช่น GM/MD ที่ดูแลหลาย brand หรือมีการสลับ brand) — ถ้าไม่ปิด
 * BrandScope จะกรอง relation ข้าม brand ออกจนข้อมูลกลายเป็น "-" ทั้งหมด
 *
 * ตั้งค่าเป็น true ที่ต้นทางของ controller action → ครอบทุก query ใน request นั้น
 * (eager / lazy / fresh) โดยอัตโนมัติ. ค่า reset เองทุก request เพราะเป็น static
 * ต่อ process — ไม่ต้องคืนค่าเอง.
 */
class ScopeBypass
{
    public static bool $brand = false;
}
