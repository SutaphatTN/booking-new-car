<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Auth;

trait UserAccessScope
{
  protected static function bootUserAccessScope()
  {
    static::addGlobalScope('userAccess', function ($query) {

      if (Auth::check() && Auth::user()->userZone && Auth::user()->brand) {

        $user = Auth::user();

        $table = $query->getModel()->getTable();

        $query->where($table . '.userZone', $user->userZone)
          ->where($table . '.brand', $user->brand);
      }
    });
  }
}
