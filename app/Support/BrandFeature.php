<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

/**
 * ฟีเจอร์ที่เปิดเฉพาะบาง brand
 *
 * รวมเงื่อนไข "brand ไหนมีอะไร" ไว้ที่เดียว แทนการเทียบ brand == 2 กระจาย
 * อยู่ตาม controller / export / blade (ซึ่งพอเพิ่ม brand ทีนึงต้องไล่แก้
 * เป็นสิบไฟล์ และมักตกหล่นในรายงาน) — ค่าจริงอยู่ใน config/brand.php
 */
class BrandFeature
{
    /**
     * brand นี้มี "สีภายใน" (interior color) ไหม
     *
     * ใช้คุมทั้งช่องกรอกในฟอร์มและคอลัมน์ในรายงาน/Excel
     * ไม่ส่ง $brand = ใช้ brand ของ user ที่ล็อกอินอยู่ (effective brand)
     */
    public static function hasInteriorColor($brand = null): bool
    {
        $brand = (int) ($brand ?? (Auth::user()->brand ?? 0));

        return in_array($brand, array_map('intval', (array) config('brand.interior_color_brands', [])), true);
    }

    /**
     * brand นี้มีหลายสาขาจริงไหม
     *
     * ใช้คุมรายงานที่ต้องดึงข้อมูลทั้ง brand ข้ามสาขา แล้วเพิ่มคอลัมน์ "สาขา"
     * ไม่ส่ง $brand = ใช้ brand ของ user ที่ล็อกอินอยู่ (effective brand)
     */
    public static function hasMultipleBranches($brand = null): bool
    {
        $brand = (int) ($brand ?? (Auth::user()->brand ?? 0));

        return in_array($brand, array_map('intval', (array) config('brand.multi_branch_brands', [])), true);
    }

    /**
     * brand นี้ถูกขายโดยหลายทีมไหม
     *
     * ใช้คุมคอลัมน์ "ทีม" ในรายงานฐานใบจอง/การติดตาม — brand ที่มีทีมเดียว
     * ไม่ต้องมีคอลัมน์นี้ ไม่ส่ง $brand = ใช้ brand ของ user ที่ล็อกอินอยู่
     */
    public static function hasMultipleTeams($brand = null): bool
    {
        $brand = (int) ($brand ?? (Auth::user()->brand ?? 0));

        return in_array($brand, array_map('intval', (array) config('brand.multi_team_brands', [])), true);
    }

    /**
     * brand นี้ปิด FP แบ่งเป็น 2 ยอดได้ไหม (ยอดละวันปิด ใช้ Billing date เดียวกัน)
     *
     * ใช้คุมช่องกรอกในโมดัลแก้ไข FP + การคิดดอกเบี้ยรายยอดในหน้า FP/รายงาน Excel
     * ไม่ส่ง $brand = ใช้ brand ของ user ที่ล็อกอินอยู่ (effective brand)
     */
    public static function hasFpSplitClose($brand = null): bool
    {
        $brand = (int) ($brand ?? (Auth::user()->brand ?? 0));

        return in_array($brand, array_map('intval', (array) config('brand.fp_split_close_brands', [])), true);
    }
}
