<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ทีมขาย — "หน่วยที่ทำยอด" แยกจาก brand (สินค้าที่ขาย) และ branch (ที่ตั้งจริง)
 *
 * มีไว้เพราะบางแบรนด์ถูกขายโดยหลายทีมที่นั่งอยู่สาขาเดียวกัน (Wuling ขายโดยทีม Mitsu
 * และทีม Lepas ทั้งคู่อยู่สำนักงานใหญ่) — brand/branch จึงแยกยอดให้ไม่ได้
 *
 * visibility:
 *   shared   = แยกแค่ตอนทำยอด/รายงาน แต่ยังเห็นข้อมูลของทีมอื่นตามปกติ
 *   isolated = manager/audit ของทีมนี้ เห็นเฉพาะงานของเซลล์ในทีมตัวเอง ตอนไปทำงาน
 *              ใต้แบรนด์ที่ใช้ร่วมกับทีมอื่น (ดู App\Models\Traits\UserAccessScope)
 *
 * ไม่มี brand scope — ทีมเป็นข้อมูลตั้งค่ากลาง ใช้อ้างอิงข้ามแบรนด์ได้
 */
class SaleTeam extends Model
{
    use SoftDeletes;

    protected $table = 'sale_teams';

    public const VISIBILITY_SHARED   = 'shared';
    public const VISIBILITY_ISOLATED = 'isolated';

    protected $fillable = [
        'name',
        'code',
        'branch',            // สาขาที่ทีมนี้รายงานยอดเข้า (ใช้แทน hardcode ใน view BI)
        'default_for_brand', // brand ที่ใช้ทีมนี้เป็นค่าเริ่มต้นตอนสร้าง user ใหม่ (1 brand = 1 ทีม)
        'visibility',
        'active',
    ];

    protected $casts = [
        'active' => 'bool',
    ];

    protected $dates = ['deleted_at'];

    public function users()
    {
        return $this->hasMany(User::class, 'sale_team_id', 'id');
    }

    /** ทีมนี้เห็นเฉพาะงานของตัวเองไหม (เมื่อไปทำงานใต้แบรนด์ที่ใช้ร่วมกับทีมอื่น) */
    public function isIsolated(): bool
    {
        return $this->visibility === self::VISIBILITY_ISOLATED;
    }

    /** ทีมที่เลือกได้ใน dropdown (เรียงตามชื่อ) */
    public static function selectable()
    {
        return static::where('active', 1)->orderBy('name')->get();
    }

    /**
     * ทีมเริ่มต้นของ brand นี้ — ใช้ตอนสร้าง user ใหม่ที่ยังไม่ได้เลือกทีม
     * ผูกไว้ที่ข้อมูล (คอลัมน์ default_for_brand) ไม่ใช่ config → ย้ายทีมเริ่มต้นได้โดยไม่ต้อง deploy
     */
    public static function defaultIdForBrand($brand): ?int
    {
        if (!$brand) {
            return null;
        }

        return static::where('active', 1)
            ->where('default_for_brand', (int) $brand)
            ->value('id');
    }

    public function branchInfo()
    {
        return $this->belongsTo(TbBranch::class, 'branch', 'id');
    }
}
