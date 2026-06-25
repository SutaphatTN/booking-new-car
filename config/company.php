<?php

/*
|--------------------------------------------------------------------------
| ข้อมูลหัวกระดาษบริษัท (ใช้ในเอกสาร PDF เช่น ใบจอง)
|--------------------------------------------------------------------------
| default = ข้อมูลบริษัทหลัก ใช้กับทุก brand ที่ไม่ได้ override
| brands  = override รายแบรนด์ (อย่างน้อยคือโลโก้) — ถ้าแต่ละ brand เป็น
|           คนละนิติบุคคล ให้เพิ่ม name/address/phone ในแต่ละ brand ได้เลย
|
| brand id : 1 = Mitsubishi, 2 = GWM, 3 = Wuling
*/

return [
    'default' => [
        'name'    => 'บริษัท ซูเกียรติ อีวี จำกัด สำนักงานใหญ่',
        'address' => '129 หมู่ที่ 11 ถนน เพชรเกษม ต. กระบี่น้อย อ. เมือง จ. กระบี่ 81000',
        'phone'   => '064-0515561',
        'logo'    => 'assets/img/Wuling_logo.png',
    ],

    'brands' => [
        // Mitsubishi — แก้ name/address/phone ได้ถ้าเป็นคนละนิติบุคคล
        1 => [
            'logo' => 'assets/img/Mitsubishi_logoCrop.png',
        ],
        // GWM
        2 => [
            'logo' => 'assets/img/Gwm_logoCrop.png',
        ],
        // Wuling
        3 => [
            'logo' => 'assets/img/Wuling_logo.png',
        ],
    ],
];
