<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Models\SaleTeam;
use App\Models\TbBranch;
use App\Models\TbBrand;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
            'role' => ['required', 'string', 'in:sale,audit,account,manager,md,bp,cs,registration'],
            'branch' => ['required', 'integer', 'exists:tb_branch,id'],
            'brand' => ['required', 'integer', 'exists:tb_brand,id'],
            'userZone' => ['required', 'in:10,40'],
            'phone' => ['nullable', 'string', 'max:20'],
            'sale_team_id' => ['nullable', 'integer', 'exists:sale_teams,id'],
        ]);

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
                'sale_team_id' => $request->filled('sale_team_id')
                    ? (int) $request->sale_team_id
                    : SaleTeam::defaultIdForBrand($request->brand),
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
