<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Models\SaleTeam;
use App\Models\TbBranch;
use App\Models\TbBrand;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    public function index()
    {
        $branch = TbBranch::all();
        $brand = TbBrand::all();
        $teams = SaleTeam::selectable();

        return view('auth.register', compact('branch', 'brand', 'teams'));
    }

    public function store(Request $request)
    {
        // branch/brand/userZone ต้อง validate ฝั่ง server ด้วย — ถ้าปล่อยให้ว่างได้
        // user จะถูกสร้างแบบไม่มีสังกัด แล้วไปโผล่ผิดที่ (หรือหายไปเลย) ในทุกหน้าที่ scope ตาม brand
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'cardID' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
            // รายการ role มาจาก User::ROLE_LABELS ที่เดียว — เพิ่มที่ model แล้วที่นี่รับตามเอง
            'role' => ['required', 'string', Rule::in(array_keys(User::ROLE_LABELS))],
            'branch' => ['required', 'integer', 'exists:tb_branch,id'],
            'brand' => ['required', 'integer', 'exists:tb_brand,id'],
            'userZone' => ['required', 'in:10,40'],
            'phone' => ['nullable', 'string', 'max:20'],
            'sale_team_id' => ['nullable', 'integer', 'exists:sale_teams,id'],
            // สาขาที่ทำยอด (BI อ่านคอลัมน์นี้) — เว้นว่างได้ ระบบคิดให้จากทีมขาย/สาขา
            'branch_make' => ['nullable', 'integer', 'exists:tb_branch,id'],
        ]);

        // ทีมขายที่จะบันทึกจริง — ใช้ซ้ำตอนคิด branch_make ด้วย
        // ไม่ได้เลือกเอง → ตั้งทีมเริ่มต้นของ brand ให้เฉพาะ role สายทีม (SaleTeam::TEAM_ROLES)
        // role อื่นปล่อย NULL ไม่มีใครอ่านค่านั้นอยู่ดี (เลือกเองได้ถ้าตั้งใจ)
        $saleTeamId = $request->filled('sale_team_id')
            ? (int) $request->sale_team_id
            : SaleTeam::defaultIdForRole($request->role, $request->brand);

        try {
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->username,
                'cardID' => preg_replace('/\D/', '', $request->cardID),
                'role' => $request->role,
                'branch' => $request->branch,
                'brand' => $request->brand,
                'password' => Hash::make($request->password),
                'password_plain' => $request->password,
                'userZone' => $request->userZone,
                'phone' => preg_replace('/\D/', '', $request->phone),
                // ทีมขาย: ถ้าไม่ได้เลือก ให้ตั้งอัตโนมัติจาก brand (sale_teams.default_for_brand)
                'sale_team_id' => $saleTeamId,
                // สาขาที่ทำยอด: เลือกเองได้ ไม่เลือก = คิดจากทีมขาย (fallback = สาขาสังกัด)
                // ห้ามปล่อย NULL — BI อ่านคอลัมน์นี้ตรง ๆ ไม่มีค่าแล้วยอดคนนั้นหลุดการแบ่งสาขา
                'branch_make' => $request->filled('branch_make')
                    ? (int) $request->branch_make
                    : User::resolveBranchMake($request->branch, $saleTeamId),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'สร้างบัญชีเรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }
}
