<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Auth;

/**
 * เก็บข้อมูล audit อัตโนมัติว่าใครเป็นคนแก้ไข (UserUpdate) และใครเป็นคนลบ (UserDelete)
 *
 * ใช้กับโมเดลที่มีคอลัมน์ UserUpdate / UserDelete อยู่แล้ว
 * - updating : เซ็ต UserUpdate ในคำสั่ง update ชุดเดียวกัน
 * - deleting : เซ็ต UserDelete เฉพาะตอน soft delete (ข้าม force delete)
 */
trait TracksUserActions
{
    protected static function bootTracksUserActions(): void
    {
        static::updating(function ($model) {
            if (Auth::check()) {
                $model->UserUpdate = Auth::id();
            }
        });

        static::deleting(function ($model) {
            // ข้าม force delete (ลบถาวร) — เก็บผู้ลบเฉพาะ soft delete เท่านั้น
            if (method_exists($model, 'isForceDeleting') && $model->isForceDeleting()) {
                return;
            }

            if (Auth::check()) {
                $model->UserDelete = Auth::id();
                // saveQuietly เพราะ SoftDeletes รัน update ของ deleted_at แยกต่างหาก
                // และเพื่อไม่ให้ event updating ทำงานซ้ำ
                $model->saveQuietly();
            }
        });
    }
}
