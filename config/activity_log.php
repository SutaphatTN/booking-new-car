<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ประวัติการแก้ไข (activity_logs) — หน้ารวมสำหรับ admin
    |--------------------------------------------------------------------------
    | key = activity_logs.subject_type (ชื่อ model แบบสั้น ที่ Traits\LogsActivity เขียนไว้)
    |
    | เพิ่ม model ใหม่: use LogsActivity ที่ model แล้วมาเติม key ตรงนี้
    | (ถ้าลืมเติม หน้ารวมยังแสดงได้ แต่จะขึ้นชื่อ class ดิบ ๆ แทนชื่อไทย)
    |
    | label   = ชื่อที่คนอ่านรู้เรื่อง
    | subject = วิธีหา "ชื่อรายการ" ให้แต่ละแถว [ตาราง, คอลัมน์ที่เอามาโชว์] — null = โชว์แค่ #id
    */
    'subjects' => [
        'Salecar' => [
            'label'   => 'ใบจอง',
            'class'   => 'bg-primary',
            'subject' => ['salecars', 'CusID'],   // แปลงต่อเป็นชื่อลูกค้าใน controller
        ],
        'CarOrder' => [
            'label'   => 'รถ',
            'class'   => 'bg-info',
            'subject' => ['car_order', 'vin_number'],
        ],
        'TbLicensePlate' => [
            'label'   => 'ป้ายแดง',
            'class'   => 'bg-danger',
            'subject' => ['tb_license_plate', 'number'],
        ],
        'LicensePlateLoan' => [
            'label'   => 'ยืม-คืนป้ายแดง',
            'class'   => 'bg-warning',
            'subject' => null,
        ],
        'SourcePlaceClaim' => [
            'label'   => 'เงินเคลม',
            'class'   => 'bg-success',
            'subject' => ['tb_source_place_claim', 'place_id'],   // แปลงต่อเป็นชื่อสถานที่ใน controller
        ],
        'FpMorRate' => [
            'label'   => 'MOR (Floor Plan)',
            'class'   => 'bg-dark',
            'subject' => null,
        ],
        'FpInterestRate' => [
            'label'   => 'ดอกเบี้ย Floor Plan',
            'class'   => 'bg-dark',
            'subject' => null,
        ],
    ],

    'events' => [
        'created'  => ['label' => 'เพิ่ม',  'class' => 'bg-success'],
        'updated'  => ['label' => 'แก้ไข',  'class' => 'bg-warning'],
        'deleted'  => ['label' => 'ลบ',     'class' => 'bg-danger'],
        'restored' => ['label' => 'กู้คืน', 'class' => 'bg-info'],
    ],

    /*
    | ชื่อไทยของคอลัมน์ — '*' ใช้ร่วมทุกประเภท ที่เหลือแยกตาม subject_type
    | คอลัมน์ที่ไม่ได้ใส่ไว้จะโชว์ชื่อคอลัมน์ดิบ (ยังอ่านออก ไม่ได้หายไป)
    | ตารางใหญ่อย่าง salecars (144 คอลัมน์) ตั้งใจไม่ไล่ใส่ครบ — เติมเฉพาะตัวที่ตรวจบ่อย
    */
    'fields' => [
        '*' => [
            'brand'  => 'แบรนด์',
            'branch' => 'สาขา',
            'status' => 'สถานะ',
            'note'   => 'หมายเหตุ',
        ],
        'Salecar' => [
            'con_status' => 'สถานะใบจอง',   // แปลงเลขเป็นชื่อจาก tb_constatus ให้อัตโนมัติ
        ],
        'CarOrder' => [
            'car_status'  => 'สถานะรถ',
            'vin_number'  => 'เลขตัวถัง',
            'j_number'    => 'J Number',
        ],
        'TbLicensePlate' => [
            'number'       => 'เลขป้าย',
            'plate_status' => 'สถานะป้าย',
            'is_used'      => 'ถูกใช้งาน',
        ],
        'LicensePlateLoan' => [
            'license_plate_id' => 'ป้ายแดง',
            'owner_brand'      => 'แบรนด์เจ้าของ',
            'borrower_brand'   => 'แบรนด์ผู้ยืม',
            'borrow_date'      => 'วันที่ยืม',
            'return_date'      => 'วันที่คืน',
        ],
        'SourcePlaceClaim' => [
            'form_b'        => 'Form B',
            'form_b_half'   => 'Form B (50%)',
            'actual_amount' => 'ยอดเงินจริงในบัญชี',
            'received_date' => 'วันที่ได้รับเงิน',
            'check_status'  => 'สถานะการตรวจสอบ',
        ],
    ],

    /*
    | คอลัมน์ที่เก็บเป็น "รหัส" — ชี้ไปที่ config ที่มีชื่อจริง จะได้ไม่โชว์รหัสดิบใน log
    | รับได้ทั้งรูปแบบ [key => 'ชื่อ'] และ [key => ['label' => 'ชื่อ', ...]]
    | (สถานะใบจองไม่ได้อยู่ตรงนี้เพราะชื่ออยู่ใน DB ตาราง tb_constatus — controller ดึงเอง)
    */
    'value_maps' => [
        'SourcePlaceClaim' => [
            'status'       => 'source.claim_statuses',
            'check_status' => 'source.claim_check_statuses',
        ],
    ],

    /*
    | ประเภทที่เลือกไว้ตอนเปิดหน้า — หน้านี้ "ต้องเลือกประเภทเสมอ" ไม่มีตัวเลือกทุกประเภท
    | เพราะใบจอง/รถ มี log รวมกันหลายพันแถว เปิดมาแล้วท่วมจนหาอะไรไม่เจอ
    | ตั้งไว้ที่ MOR เพราะข้อมูลน้อยสุด — เปลี่ยนเป็น key อื่นใน subjects ได้ตามต้องการ
    */
    'default_subject' => 'FpMorRate',

    // จำนวนบรรทัด "สิ่งที่เปลี่ยน" ที่โชว์ในตาราง (ที่เหลือกดดูทั้งหมดใน modal)
    'preview_lines' => 3,
];
