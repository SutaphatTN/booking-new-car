<?php

namespace App\Http\Controllers\marketing;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * โมดูลการตลาด : แอด (คลิปที่ยิงแอด)
 *  - เห็น/จัดการเฉพาะ role admin, adminPage
 *  - แยกตาม brand + branch ของผู้ใช้ (GWM มี 2 สาขา)
 *  - "เก็บ" = is_active 0 → ไม่แสดงเป็นตัวเลือกในหน้าเพิ่มการติดตาม (ยังเก็บใน DB)
 */
class AdController extends Controller
{
    private const ROLES = ['admin', 'adminPage'];

    private function authorizeRole(): void
    {
        abort_unless(in_array(Auth::user()->role, self::ROLES, true), 403);
    }

    /** query แอดของ brand + branch ปัจจุบันเท่านั้น */
    private function scopedQuery()
    {
        $user = Auth::user();

        return Ad::where('brand', $user->brand)
            ->where('branch', $user->branch);
    }

    public function index()
    {
        $this->authorizeRole();

        return view('source.ad.index');
    }

    public function list(Request $request)
    {
        $this->authorizeRole();

        // ฟิลเตอร์สถานะ: active (ดีฟอลต์) | archived
        $isActive = $request->input('status', 'active') === 'archived' ? 0 : 1;

        $rows = $this->scopedQuery()
            ->where('is_active', $isActive)
            ->orderByDesc('id')
            ->get();

        $data = $rows->map(function ($ad, $i) {
            $status = $ad->is_active
                ? '<span class="badge bg-success">กำลังใช้งาน</span>'
                : '<span class="badge bg-secondary">เก็บแล้ว</span>';

            $edit = '<button class="btn btn-icon btn-warning text-white btnEditAd" data-id="' . $ad->id . '" title="แก้ไข"><i class="bx bx-edit"></i></button>';

            $toggle = $ad->is_active
                ? '<button class="btn btn-icon btn-secondary text-white btnArchiveAd" data-id="' . $ad->id . '" title="เก็บ (ซ่อนจากตัวเลือก)"><i class="bx bx-archive-in"></i></button>'
                : '<button class="btn btn-icon btn-success text-white btnRestoreAd" data-id="' . $ad->id . '" title="นำกลับมาแสดง"><i class="bx bx-archive-out"></i></button>';

            return [
                'No'     => $i + 1,
                'name'   => e($ad->name),
                'url'    => $ad->url
                    ? '<a href="' . e($ad->url) . '" target="_blank" class="text-break">' . e(\Illuminate\Support\Str::limit($ad->url, 80)) . '</a>'
                    : '<span class="text-muted">-</span>',
                'status' => $status,
                'Action' => '<div class="d-flex justify-content-center gap-1">' . $edit . $toggle . '</div>',
            ];
        });

        return response()->json(['data' => $data]);
    }

    /** ดึงข้อมูลแอดสำหรับ prefill modal แก้ไข */
    public function edit($id)
    {
        $this->authorizeRole();

        $ad = $this->scopedQuery()->findOrFail($id);

        return response()->json([
            'id'   => $ad->id,
            'name' => $ad->name,
            'url'  => $ad->url,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->authorizeRole();

        $request->validate(
            [
                'name' => 'required|string|max:255',
                'url'  => 'nullable|string|max:2000',
            ],
            ['name.required' => 'กรุณากรอกชื่อแอด']
        );

        $this->scopedQuery()->whereKey($id)->update([
            'name' => trim($request->name),
            'url'  => $request->filled('url') ? trim($request->url) : null,
        ]);

        return response()->json(['success' => true, 'message' => 'แก้ไขแอดเรียบร้อยแล้ว']);
    }

    public function store(Request $request)
    {
        $this->authorizeRole();

        $request->validate(
            [
                'name' => 'required|string|max:255',
                'url'  => 'nullable|string|max:2000',
            ],
            ['name.required' => 'กรุณากรอกชื่อแอด']
        );

        $user = Auth::user();

        Ad::create([
            'name'       => trim($request->name),
            'url'        => $request->filled('url') ? trim($request->url) : null,
            'is_active'  => 1,
            'brand'      => $user->brand,
            'branch'     => $user->branch,
            'created_by' => $user->id,
        ]);

        return response()->json(['success' => true, 'message' => 'เพิ่มแอดเรียบร้อยแล้ว']);
    }

    public function archive($id)
    {
        $this->authorizeRole();

        $this->scopedQuery()->whereKey($id)->update(['is_active' => 0]);

        return response()->json(['success' => true, 'message' => 'เก็บแอดเรียบร้อยแล้ว']);
    }

    public function restore($id)
    {
        $this->authorizeRole();

        $this->scopedQuery()->whereKey($id)->update(['is_active' => 1]);

        return response()->json(['success' => true, 'message' => 'นำแอดกลับมาแสดงแล้ว']);
    }
}
