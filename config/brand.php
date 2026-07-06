<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Brand share groups
    |--------------------------------------------------------------------------
    |
    | บาง brand ใช้ทรัพยากรกองเดียวกัน (เช่น ป้ายแดง, stock film) — brand 1
    | (Mitsubishi) กับ brand 3 (Wuling) อยู่กลุ่ม 'M' ส่วน brand 2 (GWM)
    | อยู่กลุ่ม 'G' model ที่ประกาศ $sharedByBrandGroup = true จะมองเห็น
    | ทรัพยากรของทุก brand ในกลุ่มเดียวกัน (ดู App\Models\Traits\BrandScope)
    |
    | group_of  : brand id  => กลุ่มที่สังกัด
    | brands_in : กลุ่ม      => รายชื่อ brand id ในกลุ่มนั้น
    |
    */

    'group_of' => [
        1 => 'M',
        2 => 'G',
        3 => 'M',
        4 => 'M',   // Lepas — กลุ่มเดียวกับ Mitsu/Wuling (แชร์ป้ายแดง + ฟิล์ม)
    ],

    'brands_in' => [
        'M' => [1, 3, 4],
        'G' => [2],
    ],

    // ชื่อแบรนด์สำหรับแสดงผล (เช่น ป้ายที่ถูกผูกโดยอีกแบรนด์ในกลุ่ม)
    'names' => [
        1 => 'Mitsubishi',
        2 => 'GWM',
        3 => 'Wuling',
        4 => 'Lepas',
    ],

    /*
    |--------------------------------------------------------------------------
    | ขอบเขตการสลับ brand ของ sale / audit / manager
    |--------------------------------------------------------------------------
    | key = brand ประจำตัว (home brand) ของ user, value = brand ที่สลับไปได้
    |  - Mitsu(1) ขาย Wuling ได้ → [1, 3]
    |  - Lepas(4) ขาย Wuling ได้ → [3, 4]
    |  - GWM(2)/Wuling(3) เห็นเฉพาะตัวเอง
    | (role อื่น: admin/gm/md/account/registration/adminPage = ทุก brand,
    |  marketing/cro/sp/bp/cs/lead_sale = ทุก brand ยกเว้น GWM(2))
    */
    'sale_switch_scope' => [
        1 => [1, 3],
        2 => [2],
        3 => [3],
        4 => [3, 4],
    ],

];
