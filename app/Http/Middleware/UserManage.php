<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class UserManage
{
    /**
     * จัดการสิทธิ์หน้า "รายชื่อผู้ใช้งาน"
     *  - read  : ดูได้ (admin + audit_lead)
     *  - write : แก้ไข/ลบ (admin เท่านั้น) — audit_lead ดูได้อย่างเดียว
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $level = 'read'): Response
    {
        $role = Auth::user()->role ?? null;

        $allowed = $level === 'write' ? ['admin'] : ['admin', 'audit_lead'];

        if (!in_array($role, $allowed, true)) {
            abort(403);
        }

        return $next($request);
    }
}
