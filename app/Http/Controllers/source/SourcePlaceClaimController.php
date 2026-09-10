<?php

namespace App\Http\Controllers\source;

use App\Exports\source\SourcePlaceClaimExport;
use App\Http\Controllers\Controller;
use App\Models\SourcePlace;
use App\Models\SourcePlaceClaim;
use App\Models\TbBranch;
use App\Support\ExportFilename;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

/**
 * เงินเคลม (Form B) — ติดตามยอดที่ผู้ผลิตจ่ายคืนของแต่ละสถานที่
 *
 * รายการยิงจาก tb_source_place (สถานที่) ตรง ๆ แล้วพ่วงใบเคลมมาแสดง
 * ผู้ใช้แก้ได้เฉพาะฝั่งเคลม — ข้อมูลสถานที่ในหน้านี้อ่านอย่างเดียว (แก้ที่เมนู "สถานที่")
 */
class SourcePlaceClaimController extends Controller
{
    /** role ที่เข้าหน้านี้ได้ (ซ้ำกับ middleware ที่ route — กันเรียก endpoint ตรง ๆ) */
    private function guard(): void
    {
        abort_unless(in_array(Auth::user()->role ?? '', config('source.claim_roles', []), true), 403);
    }

    public function index()
    {
        $this->guard();

        return view('source.claim.view', [
            'statuses'      => config('source.claim_statuses', []),
            'checkStatuses' => config('source.claim_check_statuses', []),
        ]);
    }

    /**
     * ตารางเงินเคลม — กรองตามเดือนแบบเดียวกับหน้าสถานที่ คือ "เดือนที่ขออนุมัติ"
     * (period ของใบขออนุมัติ) ไม่ใช่วันที่จัดงาน
     * state = month (ตามเดือน) | all (ทั้งหมด)
     */
    public function list(Request $request)
    {
        $this->guard();

        $places = $this->placesQuery(
            $request->input('state', 'month'),
            $request->input('month')
        )->with('claim')->get();

        $data = $places->map(function ($p, $index) {
            $claim = $p->claim;

            return [
                'No'         => $index + 1,
                'location'   => $p->location,
                'las_number' => $p->las_number ?: '-',
                'date_range' => $this->dateRange($p),
                'form_b'     => $claim && $claim->form_b !== null ? number_format($claim->form_b, 2) : '-',
                'Action'     => view('source.claim.button', ['p' => $p])->render(),
            ];
        });

        return response()->json(['data' => $data]);
    }

    /** query กลางของหน้านี้ — ใช้ร่วมกันทั้งตารางบนจอและรายงาน Excel ให้ได้ชุดข้อมูลเดียวกัน */
    private function placesQuery(?string $state, ?string $month)
    {
        return SourcePlace::query()
            ->orderBy('id', 'desc')
            ->when(($state ?? 'month') === 'month' && $month && preg_match('/^\d{4}-\d{2}$/', $month),
                fn($q) => $q->whereHas('request', fn($r) => $r->where('period', $month)));
    }

    private function dateRange(SourcePlace $p): string
    {
        $start = $p->start_date ? $p->start_date->format('d/m/Y') : null;
        $end   = $p->end_date ? $p->end_date->format('d/m/Y') : null;

        if ($start && $end) {
            return "{$start} - {$end}";
        }
        return $start ?? $end ?? '-';
    }

    /** รายงานเงินเคลม (Excel) — ตามตัวกรองที่ค้างอยู่บนหน้าจอ */
    public function export(Request $request)
    {
        $this->guard();

        $state = $request->input('state', 'month');
        $month = $request->input('month');

        // ดึงข้อมูลที่นี่ทีเดียวแล้วส่งเข้า Export (ไม่ให้ Export ยิง query เอง จะได้ไม่หลุดจากที่เห็นบนจอ)
        $places = $this->placesQuery($state, $month)
            ->with(['claim.updater', 'claim.creator'])
            ->get();

        $branchNames = TbBranch::whereIn('id', $places->pluck('branch')->filter()->unique())
            ->pluck('name', 'id');

        $suffix   = ($state ?? 'month') === 'month' && $month ? ' ' . $month : ' ทั้งหมด';
        $filename = ExportFilename::withBrand('รายงานเงินเคลม' . $suffix . '.xlsx');

        return Excel::download(new SourcePlaceClaimExport($places, $branchNames), $filename);
    }

    /** ฟอร์มแก้ไข — {id} คือ id ของ "สถานที่" เพราะใบเคลมอาจยังไม่ถูกสร้าง */
    public function edit($id)
    {
        $this->guard();

        $place = SourcePlace::with('claim')->findOrFail($id);
        $claim = $place->claim;

        // ประวัติการกรอกไม่ได้อยู่ใน modal นี้ — ดูรวมกับส่วนอื่นที่เมนู "ประวัติการแก้ไข" (admin)
        // ตัวเขียน log ยังทำงานอยู่ผ่าน LogsActivity บน SourcePlaceClaim
        return view('source.claim.edit', [
            'place'         => $place,
            'claim'         => $claim,
            'branchName'    => $place->branch ? optional(TbBranch::find($place->branch))->name : null,
            'statuses'      => config('source.claim_statuses', []),
            'checkStatuses' => config('source.claim_check_statuses', []),
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->guard();

        try {
            $place = SourcePlace::with('claim')->findOrFail($id);

            // ช่องเงินส่งมาพร้อม comma คั่นหลักพัน — ถอดก่อน validate
            foreach (['form_b', 'actual_amount'] as $f) {
                $request->merge([
                    $f => $request->filled($f) ? str_replace(',', '', $request->input($f)) : null,
                ]);
            }

            $validated = $request->validate([
                'form_b'        => 'nullable|numeric|min:0',
                'actual_amount' => 'nullable|numeric|min:0',
                'received_date' => 'nullable|date',
                'note'          => 'nullable|string|max:500',
                'status'        => ['nullable', Rule::in(array_keys(config('source.claim_statuses', [])))],
                'check_status'  => ['nullable', Rule::in(array_keys(config('source.claim_check_statuses', [])))],
            ], [
                'form_b.numeric'        => 'Form B ต้องเป็นตัวเลข',
                'actual_amount.numeric' => 'ยอดเงินจริงในบัญชีต้องเป็นตัวเลข',
            ]);

            $user  = Auth::user();
            $claim = $place->claim;

            // 50% คำนวณฝั่ง server เสมอ — ค่าที่โชว์ในฟอร์มเป็นแค่ตัวช่วยดู ไม่ได้ส่งมาบันทึก
            $values = $validated + [
                'form_b_half' => SourcePlaceClaim::shareOf($validated['form_b'] ?? null),
                'UserUpdate'  => $user->id ?? null,
            ];

            if ($claim) {
                $claim->update($values);
            } else {
                // ใบเคลมเกาะ brand/สาขาของสถานที่ ไม่ใช่ของคนกรอก — คนกรอกเป็นส่วนกลางที่สลับ brand ได้
                // (สถานที่ข้อมูลเก่าที่ brand ว่าง ให้ตกมาที่ brand ของคนกรอก ไม่งั้นใบเคลมจะหลุด BrandScope
                //  แล้วกรอกซ้ำรอบหน้าจะชน unique place_id)
                SourcePlaceClaim::create($values + [
                    'place_id'   => $place->id,
                    'brand'      => $place->brand ?? $user->brand ?? null,
                    'userZone'   => $place->userZone,
                    'branch'     => $place->branch,
                    'UserInsert' => $user->id ?? null,
                ]);
            }

            return response()->json(['success' => true, 'message' => 'บันทึกข้อมูลเรียบร้อยแล้ว']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->validator->errors()->first()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }
}
