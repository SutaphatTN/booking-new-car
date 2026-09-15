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
     * ตอนนี้เหลือ admin อย่างเดียวทั้ง read และ write — พารามิเตอร์ level ยังคงไว้เผื่อแยกสิทธิ์อีก
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $level = 'read'): Response
    {
        $role = Auth::user()->role ?? null;

        // 2026-09-15 : เหลือ admin อย่างเดียวทั้งดูและแก้ (เดิม audit_lead/audit_dp ดูรายชื่อได้)
        $allowed = ['admin'];

        if (!in_array($role, $allowed, true)) {
            abort(403);
        }

        return $next($request);
    }
}
