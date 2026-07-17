<?php

namespace App\Models\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

/**
 * บันทึกประวัติการเปลี่ยนแปลงลงตาราง activity_logs อัตโนมัติผ่าน Eloquent model events
 *
 * เก็บเฉพาะ "ฟิลด์ที่เปลี่ยน" (old→new) ไม่เก็บทั้งแถว เพื่อประหยัดพื้นที่ (salecars มี ~150 คอลัมน์)
 * ครอบคลุม event : created / updated / deleted (soft delete) / restored
 *
 * ⚠️ ดักได้เฉพาะการแก้ผ่าน "model instance" (create / save / update / delete)
 *    - mass update แบบ Model::where(...)->update([...]) และ DB::table(...)->update([...]) จะ "ไม่" ถูกบันทึก
 *
 * ปรับคอลัมน์ที่ไม่อยากเก็บเพิ่มได้ โดยประกาศ property $activityExclude ในแต่ละ model
 */
trait LogsActivity
{
    /** คอลัมน์ noise ที่ไม่ต้องเก็บลง log (บวกกับ $activityExclude ของ model ถ้ามี) */
    protected function activityExcluded(): array
    {
        return array_merge([
            'created_at', 'updated_at', 'deleted_at',
            'UserInsert', 'UserUpdate', 'UserDelete',
            'approval_token', 'remember_token', 'password',
        ], property_exists($this, 'activityExclude') ? $this->activityExclude : []);
    }

    protected static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            $model->writeActivityLog('created', $model->activityCreatedChanges());
        });

        static::updated(function ($model) {
            $changes = $model->activityDiff();
            if (!empty($changes)) {
                $model->writeActivityLog('updated', $changes);
            }
        });

        static::deleted(function ($model) {
            // ข้าม force delete (ลบถาวร) — เก็บเฉพาะ soft delete
            if (method_exists($model, 'isForceDeleting') && $model->isForceDeleting()) {
                return;
            }
            $model->writeActivityLog('deleted', null);
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                $model->writeActivityLog('restored', null);
            });
        }
    }

    /** diff ตอน update : {col: {old, new}} ตัด noise ออก */
    protected function activityDiff(): array
    {
        $excluded = $this->activityExcluded();
        $changes  = [];

        foreach ($this->getChanges() as $key => $new) {
            if (in_array($key, $excluded, true)) {
                continue;
            }
            $changes[$key] = ['old' => $this->getOriginal($key), 'new' => $new];
        }

        return $changes;
    }

    /** ค่าเริ่มต้นตอน create : เก็บเฉพาะ attribute ที่ไม่ null (baseline ของแถว) */
    protected function activityCreatedChanges(): array
    {
        $excluded = $this->activityExcluded();
        $changes  = [];

        foreach ($this->getAttributes() as $key => $new) {
            if ($new === null || in_array($key, $excluded, true)) {
                continue;
            }
            $changes[$key] = ['old' => null, 'new' => $new];
        }

        return $changes;
    }

    protected function writeActivityLog(string $event, ?array $changes): void
    {
        ActivityLog::create([
            'subject_type' => class_basename($this),
            'subject_id'   => $this->getKey(),
            'event'        => $event,
            'changes'      => $changes,
            'user_id'      => Auth::id(),
            'brand'        => $this->brand ?? null,
            'ip'           => app()->runningInConsole() ? null : request()->ip(),
            'url'          => app()->runningInConsole() ? null : mb_substr(request()->fullUrl(), 0, 255),
        ]);
    }
}
