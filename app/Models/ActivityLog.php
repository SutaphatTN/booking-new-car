<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ประวัติการเปลี่ยนแปลงข้อมูลแบบรวมศูนย์ (ดูรายละเอียดการใช้งานที่ Traits\LogsActivity)
 *
 * เก็บเฉพาะ "ฟิลด์ที่เปลี่ยน" (old→new) ใน column `changes` (JSON) — ไม่เก็บทั้งแถว
 * subject_type = ชื่อ model แบบสั้น เช่น 'Salecar' , subject_id = id ของแถวนั้น
 */
class ActivityLog extends Model
{
    // ใช้แค่ created_at (DB default CURRENT_TIMESTAMP) ไม่มี updated_at
    public $timestamps = false;

    protected $table = 'activity_logs';

    protected $fillable = [
        'subject_type',
        'subject_id',
        'event',
        'changes',
        'user_id',
        'brand',
        'ip',
        'url',
    ];

    protected $casts = [
        'changes'    => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
