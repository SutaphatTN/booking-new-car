<?php

/**
 * อีเมลผู้อนุมัติใบจอง แยกตาม brand
 * - brand 3 ใช้ผู้อนุมัติชุดเดียวกับ brand 1 (ใส่เลข brand ที่ต้องการ "ชี้ไป")
 * - role 'gm' ไม่มีในตาราง users จริง จึงกำหนดเป็นอีเมลตรงนี้
 *
 * หมายเหตุ: ค่าด้านล่างยกมาจาก hardcode เดิม — โปรด "ตรวจสอบ/แก้ไข" ให้ถูกต้องก่อนใช้งานจริง
 */

return [

    // ── brand 1 ──
    1 => [
        // มีแก้
        'manager' => ['mitsuchookiat.programmer@gmail.com'],
        'gm'      => [],                          // brand 1: ไม่มีขั้น gm (manager → md)
        'md'      => ['mitsuchookiat.programmer@gmail.com'],
        'audit'   => ['daw.mitsuchookiatkrabi@gmail.com'],   // แจ้งเมื่ออนุมัติเสร็จ
    ],

    // ── brand 2 ──
    2 => [
        'manager' => ['mitsuchookiat.programmer@gmail.com'],
        'gm'      => ['mitsuchookiat.programmer@gmail.com'],      // TODO: ยืนยันว่า gm ของ brand 2 คือใคร
        'md'      => ['mitsuchookiat.programmer@gmail.com'],      // TODO: ยืนยัน md ของ brand 2
        'audit'   => ['admingwm@chookiat.org'],                  // แจ้งเมื่ออนุมัติเสร็จ
    ],

    // // ── brand 1 ──
    // 1 => [
    //     'manager' => ['Phung.mitsuchookiatkrabi@gmail.com'],
    //     'gm'      => [],                          // brand 1: ไม่มีขั้น gm (manager → md)
    //     'md'      => ['ketsudap@chookiat.org'],
    // 'audit'   => ['daw.mitsuchookiatkrabi@gmail.com'],   // แจ้งเมื่ออนุมัติเสร็จ
    // ],

    // // ── brand 2 ──
    // 2 => [
    //     'manager' => ['SasithornK@chookiat.org'],
    //     'gm'      => ['JirapornK@Chookiat.org'],      // TODO: ยืนยันว่า gm ของ brand 2 คือใคร
    //     'md'      => ['danut@chookiat.org'],      // TODO: ยืนยัน md ของ brand 2
    // 'audit'   => ['admingwm@chookiat.org'],                  // แจ้งเมื่ออนุมัติเสร็จ
    // ],

    // ── brand 3 ── ใช้ชุดเดียวกับ brand 1 (ใส่เลข brand ที่จะชี้ไป)
    3 => 1,

];
