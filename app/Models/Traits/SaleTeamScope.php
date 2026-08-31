<?php

namespace App\Models\Traits;

use App\Models\SaleTeam;
use Illuminate\Support\Facades\Auth;

/**
 * ตัวกรอง "ทีมขาย" — แยกออกมาจาก UserAccessScope โดยตั้งใจ
 *
 * เหตุผลที่ต้องเป็น global scope คนละตัว:
 * รายงานหลายตัวสั่ง withoutGlobalScope('userAccess') เพื่อข้ามขอบเขตสาขา/แบรนด์
 * (ดู BookingReportQuery, OverBudgetPerBrandSheet, LeadOnlinePerBrandSheet ฯลฯ)
 * ถ้าตัวกรองทีมอยู่ใน scope เดียวกัน มันจะหลุดไปพร้อมกันทุกครั้ง — ซึ่งเคยเป็น
 * ช่องโหว่จริงที่ทำให้ manager ทีมนึงโหลดรายงานแล้วเห็นใบจองของอีกทีม
 *
 * ผลของการแยก: ตัวกรองทีมเป็น "ปิดไว้ก่อน ต้องตั้งใจเปิด" — รายงานที่เขียนเพิ่ม
 * ทีหลังจะกรองทีมให้เองอัตโนมัติ ถ้าพลาดคือเห็นน้อยไป (มีคนบ่น) ไม่ใช่ข้อมูลรั่ว
 *
 * จุดที่ต้องเห็นทุกทีมจริง ๆ ให้สั่ง ->withoutGlobalScope('saleTeam') เอง
 * (ด่านกันจอง/ติดตามซ้ำ, การคิดงบ/คอมรายเดือน, ลิงก์อนุมัติทางอีเมล, รายงานสต๊อกรถ)
 *
 * กติกา: role manager/audit + ทีมตัวเองเป็น isolated + กำลังทำงานใต้แบรนด์ที่ไม่ใช่
 * home brand → เห็นเฉพาะใบของ "กลุ่มทีม" ตัวเอง
 * เงื่อนไข "≠ home brand" ห้ามตัดทิ้ง — ตอนดูแบรนด์บ้านตัวเองต้องเห็นครบ
 *
 * ขอบเขตเป็น "กลุ่ม" (sale_teams.group_code) ไม่ใช่ทีมเดี่ยว เพราะค่ายนึงมีได้หลายทีม
 * เช่นฝั่ง Mitsu มีทั้งทีมสำนักงานใหญ่และทีมอ่าวลึก ผู้จัดการต้องเห็นทั้งคู่
 * ทีมที่ยังไม่ตั้ง group_code จะล็อกที่ทีมตัวเองทีมเดียว (พฤติกรรมเดิม)
 *
 * อ่านจากคอลัมน์ snapshot (sale_team_id) ไม่ใช่ join กลับไป users
 * ไม่งั้นเซลล์ย้ายทีมเมื่อไหร่ ยอดย้อนหลังไหลตามไปทีมใหม่ทันที
 */
trait SaleTeamScope
{
    /** จำผลต่อ request — scope ถูกเรียกทุก query ห้ามยิง DB ซ้ำ (DB อยู่ remote ~50ms/query) */
    private static $saleTeamIdsMemo = false;

    protected static function bootSaleTeamScope()
    {
        static::addGlobalScope('saleTeam', function ($query) {

            $teamIds = static::visibleSaleTeamIds();

            if ($teamIds === null) {
                return;
            }

            $query->whereIn($query->getModel()->getTable() . '.sale_team_id', $teamIds);
        });
    }

    /**
     * ทีมที่ผู้ใช้ปัจจุบันเห็นได้ — null = ไม่ต้องกรอง (เห็นทุกทีมตามปกติ)
     * array = รายชื่อ team id ในกลุ่มเดียวกับตัวเอง
     */
    public static function visibleSaleTeamIds(): ?array
    {
        if (self::$saleTeamIdsMemo !== false) {
            return self::$saleTeamIdsMemo;
        }

        return self::$saleTeamIdsMemo = self::resolveVisibleSaleTeamIds();
    }

    private static function resolveVisibleSaleTeamIds(): ?array
    {
        if (!Auth::check()) {
            return null;
        }

        $user   = Auth::user();
        $teamId = $user->sale_team_id ?? null;

        if (!$teamId || !in_array($user->role, ['manager', 'audit'], true)) {
            return null;
        }

        // ทำงานอยู่ใต้แบรนด์บ้านตัวเอง = เห็นครบ รวมใบที่เซลล์ทีมอื่นขายให้
        if ((int) $user->getOriginal('brand') === (int) $user->brand) {
            return null;
        }

        $team = $user->saleTeam;

        if (!$team || !$team->isIsolated()) {
            return null;
        }

        return $team->groupTeamIds();
    }

    /** ใบใบนี้อยู่นอกกลุ่มทีมของคนดูไหม (คนดูไม่ถูกจำกัด = false เสมอ) */
    public static function outsideViewerTeams($saleTeamId): bool
    {
        $ids = static::visibleSaleTeamIds();

        return $ids !== null && !in_array((int) $saleTeamId, $ids, true);
    }

    /** ล้าง memo — ใช้ในเทส/คำสั่ง CLI ที่สลับ user กลางคัน */
    public static function forgetSaleTeamIds(): void
    {
        self::$saleTeamIdsMemo = false;
    }
}
