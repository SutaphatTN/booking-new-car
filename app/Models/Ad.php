<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * แอด (คลิปที่ยิงแอด) — โมดูลการตลาด
 *  - จัดการเฉพาะ role admin / adminPage
 *  - แยกตาม brand + branch (เช่น GWM มี 2 สาขา แยกรายการกัน)
 *  - is_active = 1 แสดงเป็นตัวเลือกในหน้าเพิ่มการติดตาม, 0 = "เก็บ" แล้ว (ไม่แสดง)
 */
class Ad extends Model
{
    protected $table = 'tb_ad';

    protected $fillable = [
        'name',
        'url',
        'is_active',
        'brand',
        'branch',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
