<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BrandSwitcher
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && session()->has('brand_switch')) {
            $user   = Auth::user();
            $target = (int) session('brand_switch');

            // สลับได้เฉพาะ brand ที่อยู่ในสิทธิ์ของ user (กันยิง request ตรงไป brand ที่ไม่อนุญาต)
            if (in_array($target, $user->switchableBrandIds(), true)) {
                $user->brand = $target;
            }
        }

        return $next($request);
    }
}
