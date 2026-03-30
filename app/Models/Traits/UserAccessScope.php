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

      // ไม่จำกัด zone — filter แค่ brand (ที่อาจถูก override โดย BrandSwitcher middleware)
      if (in_array($user->role, ['account', 'admin', 'audit'])) {
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
