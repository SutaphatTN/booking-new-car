<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BrandSwitcher
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $role = $user->role;
            $allowed = $user->brand != 2
                && in_array($role, ['admin', 'account', 'audit', 'manager', 'md', 'sale']);

            if ($allowed && session()->has('brand_switch')) {
                $user->brand = session('brand_switch');
            }
        }

        return $next($request);
    }
}
