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

      // ── กรณีพิเศษ: manager/audit ของ Lepas(brand 4) สลับไปดู Wuling(brand 3) ──
      // ให้เห็นเฉพาะรายการที่ "เซลล์ผู้ทำ" เป็นคน brand 4 (ทีม Lepas ที่ไปขาย Wuling)
      // — ไม่ปนลูกค้าของ manager Wuling. ใช้กับ salecars (SaleID) + customer_trackings (sale_id)
      if (
        in_array($user->role, ['manager', 'audit'], true)
        && (int) $user->getOriginal('brand') === 4
        && (int) $user->brand === 3
      ) {
        $saleCol = ['salecars' => 'SaleID', 'customer_trackings' => 'sale_id'][$table] ?? null;
        if ($saleCol) {
          $query->whereIn("{$table}.{$saleCol}", function ($q) {
            $q->select('id')->from('users')
              ->where('brand', 4)
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
