<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchSwitcher
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            if (in_array($user->brand, [1, 2]) && session()->has('branch_switch')) {
                $user->branch = session('branch_switch');
            }
        }

        return $next($request);
    }
}
