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

      // ตัวกรอง "ทีมขาย" ย้ายไปอยู่ scope แยกชื่อ 'saleTeam' แล้ว (App\Models\Traits\SaleTeamScope)
      // เพราะรายงานที่ปลด userAccess เพื่อข้ามสาขา/แบรนด์ เคยทำให้ตัวกรองทีมหลุดไปด้วย

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
