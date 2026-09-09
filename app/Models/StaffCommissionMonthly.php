<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ค่าที่ "กรอกเอง" ของค่าคอมฝ่ายสนับสนุน ต่อคน ต่อเดือน
 *
 * ส่วนที่คิดจากยอดขายอยู่ใน StaffCommissionQuery + config/staff_commission.php
 * ตารางนี้เก็บเฉพาะช่องที่คนกรอก (LAS / PDS / Lepas 0.5% ฯลฯ) กับหมายเหตุ
 * เก็บเป็น JSON เพราะช่องของแต่ละคนไม่เหมือนกัน และเปลี่ยนได้จาก config โดยไม่ต้องแก้ schema
 *
 * @property int $id
 * @property int $user_id
 * @property int $year
 * @property int $month
 * @property array|null $extras
 * @property string|null $note
 */
class StaffCommissionMonthly extends Model
{
    protected $table = 'staff_commission_monthly';

    protected $fillable = [
        'user_id',
        'year',
        'month',
        'extras',
        'note',
    ];

    protected $casts = [
        'user_id' => 'int',
        'year'    => 'int',
        'month'   => 'int',
        'extras'  => 'array',
    ];
}
