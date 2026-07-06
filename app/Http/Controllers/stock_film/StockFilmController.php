<?php

namespace App\Http\Controllers\stock_film;

use App\Http\Controllers\Controller;
use App\Traits\ConvertsThaiDate;
use App\Models\FilmBrand;
use App\Models\FilmStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockFilmController extends Controller
{
    use ConvertsThaiDate;
    private function userBrandGroup(): string
    {
        return FilmStock::brandGroupFromUserBrand(Auth::user()->brand ?? null);
    }

    public function index()
    {
        return view('stock-film.stock.view');
    }

    public function listStock()
    {
        $stocks = FilmStock::with('filmBrand')
            ->whereNull('audit_completed_at') // ตรวจสอบเสร็จสิ้นแล้ว = ซ่อนจากลิสต์
            ->orderBy('withdrawal_date', 'desc')
            ->orderBy('brand_group')
            ->orderBy('film_brand_id')
            ->orderBy('shade')
            ->get();

        $data = $stocks->map(function ($s, $index) {
            $remaining = $s->remaining_qty;
            $diff      = $s->inspection_diff;

            $statusBadge = $remaining <= 0
                ? '<span class="badge bg-secondary">หมด</span>'
                : ($remaining < 100
                    ? '<span class="badge bg-warning text-dark">เหลือน้อย</span>'
                    : '<span class="badge bg-success">ใช้งาน</span>');

            $inspectionResult = match ($s->inspection_result) {
                'pass'  => '<span class="badge bg-success">ถูกต้อง</span>',
                'fail'  => '<span class="badge bg-danger">ไม่ถูกต้อง</span>',
                default => '-',
            };

            $diffText = $diff !== null
                ? ($diff == 0
                    ? '<span class="text-success">0</span>'
                    : '<span class="text-danger">' . number_format($diff, 2) . '</span>')
                : '-';

            return [
                'No'                => $index + 1,
                'stock_no'          => $s->stock_no,
                'part_no'           => $s->part_no ?? '-',
                'brand_group'       => FilmStock::BRAND_GROUPS[$s->brand_group] ?? $s->brand_group,
                'film_brand'        => $s->filmBrand?->name ?? '-',
                'shade'             => $s->shade,
                'withdrawal_date'   => $s->withdrawal_date?->format('d/m/Y') ?? '-',
                'initial_qty'       => number_format($s->initial_qty, 2),
                'used_qty'          => number_format($s->used_qty, 2),
                'remaining_qty'     => number_format($remaining, 2),
                'status'            => $statusBadge,
                'inspection_date'   => $s->inspection_date?->format('d/m/Y') ?? '-',
                'inspection_qty'    => $s->inspection_qty !== null ? number_format($s->inspection_qty, 2) : '-',
                'inspection_diff'   => $diffText,
                'inspection_result' => $inspectionResult,
                'Action'            => view('stock-film.stock.button', compact('s'))->render(),
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function create()
    {
        $filmBrands  = FilmBrand::orderBy('id')->get();
        $brandGroup  = $this->userBrandGroup();
        return view('stock-film.stock.input', compact('filmBrands', 'brandGroup'));
    }

    public function store(Request $request)
    {
        try {
            $brandGroup = $this->userBrandGroup();
            $filmBrand  = FilmBrand::findOrFail($request->film_brand_id);

            $stockNo = FilmStock::generateStockNo(
                $brandGroup,
                $filmBrand->code,
                $request->shade,
                $request->withdrawal_date
            );

            // นับรวมแถวที่ถูกลบ (soft delete) ด้วย เพราะ UNIQUE index บน stock_no ใน DB
            // ยังนับแถวที่ถูกลบอยู่ — ถ้าเช็คแค่แถว active จะหลุดไป INSERT แล้วชน unique
            $existing = FilmStock::withTrashed()->where('stock_no', $stockNo)->first();

            if ($existing && !$existing->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => "Stock No. {$stockNo} มีอยู่แล้วในระบบ",
                ], 422);
            }

            $user = Auth::user();

            $payload = [
                'stock_no'        => $stockNo,
                'part_no'         => $request->part_no ?: null,
                'brand_group'     => $brandGroup,
                'brand'           => $user->brand ?? null,
                'branch'          => $user->branch ?? null,
                'userZone'        => $user->userZone ?? null,
                'userInsert'      => $user->id ?? null,
                'film_brand_id'   => $filmBrand->id,
                'shade'           => $request->shade,
                'withdrawal_date' => $this->toGregorian($request->withdrawal_date),
                'initial_qty'     => $request->initial_qty,
                'used_qty'        => 0,
            ];

            if ($existing) {
                // stock_no เดิมเคยถูกลบไป — กู้คืนแล้วอัปเดตเป็นข้อมูลใหม่ (เริ่มต้นใหม่หมด)
                $existing->restore();
                $existing->update($payload + [
                    'inspection_date'    => null,
                    'inspection_qty'     => null,
                    'inspection_result'  => null,
                    'inspection_by'      => null,
                    'audit_completed_at' => null,
                    'audit_completed_by' => null,
                ]);
            } else {
                FilmStock::create($payload);
            }

            return response()->json(['success' => true, 'message' => 'เพิ่มข้อมูลเรียบร้อยแล้ว', 'stock_no' => $stockNo]);
        } catch (\Exception) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }

    public function viewMore(int $id)
    {
        $stock = FilmStock::with('filmBrand')->findOrFail($id);
        return view('stock-film.stock.view-more', compact('stock'));
    }

    public function edit(int $id)
    {
        $stock      = FilmStock::with('filmBrand')->findOrFail($id);
        $filmBrands = FilmBrand::orderBy('id')->get();
        return view('stock-film.stock.edit', compact('stock', 'filmBrands'));
    }

    public function update(Request $request, int $id)
    {
        try {
            $stock = FilmStock::findOrFail($id);

            // เก็บ id ผู้ตรวจสอบเมื่อมีการบันทึกผลตรวจนับ
            $hasInspection = $request->filled('inspection_date')
                || $request->filled('inspection_qty')
                || $request->filled('inspection_result');

            $stock->update([
                'part_no'           => $request->part_no ?: null,
                'initial_qty'       => $request->initial_qty,
                'inspection_date'   => $this->toGregorian($request->inspection_date ?: null),
                'inspection_qty'    => $request->filled('inspection_qty') ? $request->inspection_qty : null,
                'inspection_result' => $request->inspection_result ?: null,
                'inspection_by'     => $hasInspection ? Auth::id() : null,
            ]);

            return response()->json(['success' => true, 'message' => 'แก้ไขข้อมูลเรียบร้อยแล้ว']);
        } catch (\Exception) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            FilmStock::findOrFail($id)->delete();
            return response()->json(['success' => true, 'message' => 'ลบข้อมูลเรียบร้อยแล้ว']);
        } catch (\Exception) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }

    // ── ตรวจสอบเสร็จสิ้น (เฉพาะ admin / audit) → ซ่อนออกจากลิสต์ ──
    public function auditComplete(int $id)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'audit', 'audit_lead', 'gm'])) {
            return response()->json(['success' => false, 'message' => 'ไม่มีสิทธิ์ดำเนินการ'], 403);
        }

        try {
            $stock = FilmStock::findOrFail($id);
            $stock->update([
                'audit_completed_at' => now(),
                'audit_completed_by' => $user->id,
            ]);

            return response()->json(['success' => true, 'message' => 'ทำเครื่องหมายตรวจสอบเสร็จสิ้นแล้ว']);
        } catch (\Exception) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }

    public function previewStockNo(Request $request)
    {
        if (!$request->film_brand_id || !$request->shade || !$request->withdrawal_date) {
            return response()->json(['stock_no' => '']);
        }

        $brandGroup = $this->userBrandGroup();
        $filmBrand  = FilmBrand::find($request->film_brand_id);

        if (!$filmBrand) {
            return response()->json(['stock_no' => '']);
        }

        $stockNo = FilmStock::generateStockNo(
            $brandGroup,
            $filmBrand->code,
            $request->shade,
            $request->withdrawal_date
        );

        return response()->json([
            'stock_no' => $stockNo,
            'exists'   => FilmStock::where('stock_no', $stockNo)->exists(),
        ]);
    }
}
