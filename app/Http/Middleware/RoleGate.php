<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleGate
{
    /**
     * จำกัดการเข้าถึงตาม role ที่กำหนด เช่น 'role:admin,gm'
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check() || !in_array(Auth::user()->role, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
