<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Auth;

trait UserAccessScope
{
  protected static function bootUserAccessScope()
  {
    static::addGlobalScope('userAccess', function ($query) {

      if (!Auth::check()) return;

      $user = Auth::user();
      $table = $query->getModel()->getTable();

      // ── ทีมที่ตั้งค่าเป็น isolated: manager/audit เห็นเฉพาะงานของเซลล์ในทีมตัวเอง ──
      // ใช้ตอนไปทำงานใต้ "แบรนด์ที่ใช้ร่วมกับทีมอื่น" (เช่นทีม Lepas สลับไปขาย Wuling
      // ที่ทีม Mitsu ก็ขายอยู่) — จะได้ไม่ปนลูกค้าของอีกทีม
      //
      // เงื่อนไข brand ที่ทำงานอยู่ ≠ home brand สำคัญ ห้ามตัดทิ้ง:
      // ตอนดูแบรนด์บ้านตัวเองต้องเห็นครบ รวมใบที่เซลล์ทีมอื่นเป็นคนขายให้ด้วย
      //
      // ใช้กับ salecars (SaleID) + customer_trackings (sale_id)
      $teamId = $user->sale_team_id ?? null;
      if (
        $teamId
        && in_array($user->role, ['manager', 'audit'], true)
        && (int) $user->getOriginal('brand') !== (int) $user->brand
        && optional($user->saleTeam)->isIsolated()
      ) {
        $saleCol = ['salecars' => 'SaleID', 'customer_trackings' => 'sale_id'][$table] ?? null;
        if ($saleCol) {
          $query->whereIn("{$table}.{$saleCol}", function ($q) use ($teamId) {
            $q->select('id')->from('users')
              ->where('sale_team_id', $teamId)
              ->whereIn('role', ['sale', 'lead_sale']);
          });
        }
      }

      if ($user->role === 'admin') {
        if ($user->brand) {
          $query->where($table . '.brand', $user->brand);
        }
        return;
      }

      // ไม่จำกัด zone — filter แค่ brand และ branch
      if (in_array($user->role, ['account', 'audit', 'audit_lead', 'audit_dp', 'gm', 'registration'])) {
        if ($user->brand) {
          $query->where($table . '.brand', $user->brand);
        }

        if ($user->branch) {
          $query->where($table . '.branch', $user->branch);
        }

        return;
      }

      //filter zone + brand + branch
      if ($user->userZone && $user->brand) {
        $query->where($table . '.userZone', $user->userZone)
          ->where($table . '.brand', $user->brand);

        if ($user->branch) {
          $query->where($table . '.branch', $user->branch);
        }
      }
    });
  }
}
