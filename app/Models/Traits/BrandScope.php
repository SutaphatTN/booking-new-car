<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Auth;

trait BrandScope
{
    protected static function bootBrandScope()
    {
        static::addGlobalScope('brandAccess', function ($query) {
            if (!Auth::check()) return;

            $user = Auth::user();
            $table = $query->getModel()->getTable();

            if ($user->brand) {
                $query->where($table . '.brand', $user->brand);
            }
        });
    }
}