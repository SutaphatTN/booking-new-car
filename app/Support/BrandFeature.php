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
}
