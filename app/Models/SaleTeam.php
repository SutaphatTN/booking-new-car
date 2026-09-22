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

    /**
     * role ที่ "ทีมของตัวเอง" มีผลต่อการมองเห็นข้อมูล
     * (App\Models\Traits\SaleTeamScope อ่านค่านี้ — แก้ที่นี่ที่เดียว ห้าม hardcode ซ้ำ)
     */
    public const SCOPED_ROLES = ['manager', 'audit'];

    /**
     * role ที่ควรสังกัดทีมเสมอ = สายขาย + role ที่ scope ใช้ทีมตัวเองตัดสินการมองเห็น
     *
     * role นอกลิสต์นี้ (account / cs / registration / insurance_reg ฯลฯ) ไม่ต้องมีทีม —
     * ค่าที่ติดมาไม่เคยถูกอ่าน เพราะ snapshot บนใบจอง/ใบติดตามดึงจากทีมของ "ผู้ขาย"
     * ไม่ใช่ทีมของคนที่กดสร้าง (ดู Salecar::booted / CustomerTracking::booted)
     */
    public const TEAM_ROLES = ['sale', 'lead_sale', 'manager', 'audit'];

    protected $fillable = [
        'name',
        'code',
        'group_code',       // ค่าย/กลุ่มทีม — ทีมในกลุ่มเดียวกันมองเห็นข้อมูลกันได้
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

    /**
     * team id ทั้งหมดใน "กลุ่ม" เดียวกับทีมนี้ (ค่ายเดียวกัน)
     *
     * ค่ายนึงมีได้หลายทีม — ฝั่ง Mitsu มีทั้งทีมสำนักงานใหญ่และทีมอ่าวลึก ซึ่งผู้จัดการ
     * ต้องเห็นทั้งคู่ ส่วนทีม Lepas เป็นคนละค่าย จึงต้องกรองด้วยกลุ่มไม่ใช่ทีมเดี่ยว
     *
     * ทีมที่ยังไม่ตั้ง group_code = อยู่คนเดียว (พฤติกรรมเดิมก่อนมีคอลัมน์นี้)
     * อ่านผ่าน $this->group_code เฉย ๆ เพื่อให้โค้ดยังทำงานได้ถ้ายังไม่ได้รัน ALTER TABLE
     */
    public function groupTeamIds(): array
    {
        $group = $this->group_code ?? null;

        if (!$group) {
            return [(int) $this->id];
        }

        return static::where('group_code', $group)
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->all();
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

    /**
     * ทีมเริ่มต้นตอนสร้าง user ใหม่ที่ยังไม่ได้เลือกทีม — ตั้งให้เฉพาะ role ในสายทีม
     *
     * เดิมตั้งให้ทุก role ทำให้ account/cs/insurance_reg ติดทีมมาโดยไม่มีใครใช้ค่านั้น
     * (แอดมินยังเลือกทีมให้ role นอกลิสต์เองได้อยู่ ถ้าตั้งใจ — ตรงนี้คุมแค่ค่าอัตโนมัติ)
     */
    public static function defaultIdForRole(?string $role, $brand): ?int
    {
        if (!in_array($role, self::TEAM_ROLES, true)) {
            return null;
        }

        return static::defaultIdForBrand($brand);
    }

    public function branchInfo()
    {
        return $this->belongsTo(TbBranch::class, 'branch', 'id');
    }
}
