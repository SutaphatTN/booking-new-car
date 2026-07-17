<?php

namespace App\Http\Controllers\floor_plan;

use App\Http\Controllers\Controller;
use App\Exports\dispose\DisposeReportExport;
use App\Exports\fp\FpReportExport;
use App\Models\CarOrder;
use App\Models\Salecar;
use App\Models\FpMorRate;
use App\Models\FpInterestRate;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class FloorPlanController extends Controller
{
    // เมนู Floor Plan เห็น/แก้ได้เฉพาะ admin, audit_internal, md
    private const ALLOWED_ROLES = ['admin', 'audit_internal', 'md'];

    // ชุดแจ้งจำหน่าย (key => label) — key เก็บลง salecars.dispose_set
    public const DISPOSE_SETS = [
        'tracking'     => 'ระหว่างติดตาม',
        'sent_reg'     => 'ส่งฝ่าย ทบ.',
        'before_doc'   => 'ก่อนคุมเอกสาร',
        'registered'   => 'จด ทบ. แล้ว',
        'with_ia'      => 'อยู่กับ IA',
        'return_mmth'  => 'คืน MMTH ลักษณะผิด',
        'sent_account' => 'ส่งบัญชี',
    ];

    private function authorizeAccess(): void
    {
        abort_unless(in_array(Auth::user()->role, self::ALLOWED_ROLES, true), 403);
    }

    /**
     * งวด (period) เก็บด้วย "เดือนที่เริ่ม" (YYYY-MM) — 1 งวด = วันที่ 16 ของเดือนนั้น ถึงวันที่ 15 ของเดือนถัดไป
     * เช่น period '2026-06' = 16/06/2026 – 15/07/2026
     */

    // งวดปัจจุบันตามวันนี้: ตั้งแต่วันที่ 16 = งวดของเดือนนี้ / ก่อนวันที่ 16 = งวดเดือนก่อน
    private function currentPeriod(): string
    {
        $today = now();
        return $today->day >= 16
            ? $today->format('Y-m')
            : $today->copy()->subMonthNoOverflow()->format('Y-m');
    }

    // ช่วงวันที่ของงวด [เริ่ม, สิ้นสุด] จาก period (YYYY-MM ของเดือนที่เริ่ม)
    private function periodRange(string $period): array
    {
        $start = Carbon::createFromFormat('Y-m-d', $period . '-16')->startOfDay();
        $end   = $start->copy()->addMonthNoOverflow()->day(15);
        return [$start, $end];
    }

    /**
     * หน้า "อัตราดอกเบี้ยวงเงิน"
     * - MOR = ค่ากลางทุก brand (รายเดือน)
     * - spread = แยกตาม brand ที่ user กำลังทำงานอยู่ (effective brand) รายเดือน
     * - เดือนที่ยังไม่ตั้งค่า จะ fallback ใช้ค่าเดือนก่อนหน้า
     */
    public function interestRate(Request $request)
    {
        $this->authorizeAccess();

        // period = "เดือนที่เริ่มงวด" (YYYY-MM) — default = งวดปัจจุบันตามกฎวันที่ 16
        $month = $request->input('month') ?: $this->currentPeriod();
        $brand = (int) Auth::user()->brand;

        $mor     = FpMorRate::effectiveForMonth($month);
        $spreads = FpInterestRate::effectiveForMonth($brand, $month);
        $buckets = FpInterestRate::BUCKETS;

        $brandName = config('brand.names')[$brand] ?? ('Brand ' . $brand);

        // ช่วงวันที่ของงวดที่เลือก (16 ของเดือนนี้ – 15 ของเดือนถัดไป)
        [$periodStart, $periodEnd] = $this->periodRange($month);
        $periodLabel = $periodStart->format('d/m/Y') . ' – ' . $periodEnd->format('d/m/Y');

        // มีข้อมูลของ "งวดนี้" จริงหรือไม่ (เพื่อบอก user ว่าค่านี้สืบทอดมาจากงวดก่อน)
        $morIsThisMonth   = FpMorRate::where('period', $month)->exists();
        $spreadIsThisMonth = FpInterestRate::where('brand', $brand)->where('period', $month)->exists();

        return view('floor-plan.interest-rate.view', compact(
            'month',
            'brand',
            'brandName',
            'mor',
            'spreads',
            'buckets',
            'periodLabel',
            'morIsThisMonth',
            'spreadIsThisMonth'
        ));
    }

    /**
     * บันทึกอัตราดอกเบี้ยวงเงินของ "เดือนที่เลือก"
     * - upsert เฉพาะเดือนนั้น ไม่แตะเดือนอื่น (เก็บประวัติแต่ละเดือน)
     */
    public function updateInterestRate(Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'month'          => 'required|date_format:Y-m',
            'mor'            => 'required|numeric|min:0',
            'spread_1_60'    => 'required|numeric|min:0',
            'spread_61_120'  => 'required|numeric|min:0',
            'spread_121_180' => 'required|numeric|min:0',
            'spread_181_up'  => 'required|numeric|min:0',
        ]);

        $month = $validated['month'];
        $brand = (int) Auth::user()->brand;
        $uid   = Auth::id();

        // MOR = ค่ากลางทุก brand ต่อเดือน — upsert เฉพาะเดือนนี้
        $mor = FpMorRate::firstOrNew(['period' => $month]);
        if (!$mor->exists) {
            $mor->UserInsert = $uid;
        }
        $mor->mor = $validated['mor'];
        $mor->save();

        // spread แยกตาม brand ต่อเดือน — upsert เฉพาะ brand + เดือนนี้
        $rate = FpInterestRate::firstOrNew(['brand' => $brand, 'period' => $month]);
        if (!$rate->exists) {
            $rate->UserInsert = $uid;
        }
        $rate->fill([
            'spread_1_60'    => $validated['spread_1_60'],
            'spread_61_120'  => $validated['spread_61_120'],
            'spread_121_180' => $validated['spread_121_180'],
            'spread_181_up'  => $validated['spread_181_up'],
        ]);
        $rate->save();

        return response()->json([
            'success' => true,
            'message' => 'บันทึกอัตราดอกเบี้ยวงเงินเรียบร้อยแล้ว',
        ]);
    }

    // ── FP interest helpers ──────────────────────────────────────────────

    // ช่วง aging เลือกจาก "จำนวนวันรวม" (billing → ปิด) — 1 ช่วงต่อ 1 คัน
    private function spreadBucketColumn(int $totalDays): string
    {
        if ($totalDays <= 60)  return 'spread_1_60';
        if ($totalDays <= 120) return 'spread_61_120';
        if ($totalDays <= 180) return 'spread_121_180';
        return 'spread_181_up';
    }

    /**
     * แตกดอกเบี้ย FP เป็น segment ตามงวด (16→15) เพราะ MOR/Rate แต่ละเดือนต่างกัน
     * - งวดแรกเริ่มที่ billing date (แม้ก่อนวันที่ 16) นับถึง 15 ของเดือนถัดไป
     * - งวดถัดไป 16 → 15 ของเดือนถัดไป งวดสุดท้ายตัดที่วันปิด FP
     * - spread (MLR) ใช้ช่วง aging เดียวจากจำนวนวันรวม แต่ค่า MOR/MLR อ่านตามเดือนของ segment
     */
    private function buildFpSegments(Carbon $billing, Carbon $close, int $brand, float $cost): array
    {
        $totalDays = $billing->diffInDays($close);           // นับส่วนต่าง (exclusive)
        $bucketCol = $this->spreadBucketColumn($totalDays);

        $segments      = [];
        $totalInterest = 0.0;

        $segStart    = $billing->copy();
        $periodMonth = $billing->copy()->startOfMonth();

        // กันลูปหลุด (สูงสุด ~10 ปี)
        for ($guard = 0; $guard < 130; $guard++) {
            $nextBoundary = $periodMonth->copy()->addMonthNoOverflow()->day(16)->startOfDay();
            $period  = $periodMonth->format('Y-m');
            $mor     = FpMorRate::effectiveForMonth($period);
            $spreads = FpInterestRate::effectiveForMonth($brand, $period);
            $mlr     = (float) $spreads[$bucketCol];
            $rate    = $mor - $mlr;

            $isLast  = $close->lt($nextBoundary);
            $segEnd  = $isLast ? $close->copy() : $nextBoundary->copy()->subDay(); // งวดสุดท้าย = วันปิด / อื่น ๆ = วันที่ 15
            $days    = $isLast ? $segStart->diffInDays($close) : $segStart->diffInDays($nextBoundary);

            $interest = $cost * ($rate / 100) * ($days / 365);
            $totalInterest += $interest;

            $segments[] = [
                'period'    => $period,
                'startText' => $segStart->format('d/m/Y'),
                'endText'   => $segEnd->format('d/m/Y'),
                'days'      => $days,
                'mor'       => $mor,
                'mlr'       => $mlr,
                'rate'      => $rate,
                'interest'  => $interest,
            ];

            if ($isLast) break;

            $segStart    = $nextBoundary->copy();
            $periodMonth = $periodMonth->copy()->addMonthNoOverflow();
        }

        return [
            'segments'      => $segments,
            'totalDays'     => $totalDays,
            'totalInterest' => $totalInterest,
        ];
    }

    /**
     * สร้างแถวข้อมูล FP (car_order ที่ payment_type = fp_tisco, auto brand-scoped)
     * ใช้ร่วมกันทั้งหน้า list และรายงาน Excel
     */
    private function fpRows(int $brand)
    {
        $orders = CarOrder::with(['model', 'subModel', 'interiorColor', 'gwmColor'])
            ->where('payment_type', 'fp_tisco')
            ->orderByDesc('fp_date')
            ->get();

        return $orders->map(function ($o) use ($brand) {
            $billing = $o->fp_date ? Carbon::parse($o->fp_date) : null;
            $close   = $o->fp_close_date ? Carbon::parse($o->fp_close_date) : null;
            $cost    = (float) ($o->car_DNP ?? 0);

            $isClosed = $billing && $close && $close->gte($billing);
            $calc     = $isClosed ? $this->buildFpSegments($billing, $close, $brand, $cost) : null;

            return [
                'id'            => $o->id,
                'modelName'     => $o->model->Name_TH ?? '-',
                'subModelName'  => $o->subModel->name ?? '-',
                'vin'           => $o->vin_number ?: '-',
                'billingText'   => $o->format_fp_date ?? '-',
                // งวดของ Billing date = เดือนของ segment แรก (calendar month ของ billing)
                'billingPeriod' => $billing ? $billing->format('Y-m') : null,
                'year'          => $o->year ?: '-',
                'option'        => $o->option ?: '-',
                'color'         => $o->display_color ?? '-',
                'interior'      => $o->interiorColor->name ?? '-',
                'engine'        => $o->engine_number ?: '-',
                'jNumber'       => $o->j_number ?: '-',
                'cost'          => $cost,
                'closeDate'     => $o->fp_close_date,          // Y-m-d สำหรับ input
                'closeText'     => $o->format_fp_close_date ?? '-',
                'isClosed'      => $isClosed,
                'segments'      => $calc['segments'] ?? [],
                'totalDays'     => $calc['totalDays'] ?? null,
                'totalInterest' => $calc['totalInterest'] ?? null,
            ];
        });
    }

    /**
     * หน้า "รายการ FP" — car_order ที่ประเภทการจ่าย = fp_tisco (auto brand-scoped)
     */
    public function fpList(Request $request)
    {
        $this->authorizeAccess();

        $brand     = (int) Auth::user()->brand;
        $brandName = config('brand.names')[$brand] ?? ('Brand ' . $brand);

        // ── ฟิลเตอร์ ──
        $month  = $request->input('month') ?: $this->currentPeriod();     // งวดของ Billing date (YYYY-MM)
        $status = $request->input('status', 'all');                        // all | closed | pending

        [$periodStart, $periodEnd] = $this->periodRange($month);
        $periodLabel = $periodStart->format('d/m/Y') . ' – ' . $periodEnd->format('d/m/Y');

        $rows = $this->fpRows($brand);

        // กรอง: รอปิด FP แสดงเสมอ (ยกเว้นเลือกสถานะ "ปิดแล้ว") / ปิดแล้ว กรองตามงวด billing
        $rows = $rows->filter(function ($r) use ($month, $status) {
            $isPending = !$r['isClosed'];

            if ($status === 'pending') return $isPending;
            if ($status === 'closed')  return !$isPending && $r['billingPeriod'] === $month;

            // all
            return $isPending || $r['billingPeriod'] === $month;
        })->values();

        return view('floor-plan.fp.view', compact(
            'rows',
            'brand',
            'brandName',
            'month',
            'status',
            'periodLabel'
        ));
    }

    /**
     * บันทึก "วันที่ปิด FP" (กรอกเอง) ลง car_order — เว้นว่างได้ (กลับเป็น รอปิด FP)
     */
    public function updateFpCloseDate(Request $request, $id)
    {
        $this->authorizeAccess();

        $order = CarOrder::where('payment_type', 'fp_tisco')->findOrFail($id);

        $validated = $request->validate([
            'fp_close_date' => 'nullable|date|after_or_equal:' . ($order->fp_date ?? '1900-01-01'),
        ], [
            'fp_close_date.after_or_equal' => 'วันที่ปิด FP ต้องไม่ก่อน Billing date',
        ]);

        $order->fp_close_date = $validated['fp_close_date'] ?: null;
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'บันทึกวันที่ปิด FP เรียบร้อยแล้ว',
        ]);
    }

    /**
     * ออกรายงาน FP (Excel) — ยึดตามงวด Billing date (calendar month ของ fp_date)
     * ถ้าไม่ระบุเดือน = ทุกงวด (ทุกคัน fp_tisco)
     */
    public function exportFp(Request $request)
    {
        $this->authorizeAccess();

        $brand = (int) Auth::user()->brand;
        $month = $request->input('month');   // YYYY-MM หรือว่าง

        $rows = $this->fpRows($brand);
        if ($month) {
            $rows = $rows->filter(fn ($r) => $r['billingPeriod'] === $month);
        }
        $rows = $rows->values();

        $filename = 'รายงาน FP' . ($month ? " {$month}" : '') . '.xlsx';

        return Excel::download(new FpReportExport($rows->all(), $brand), $filename);
    }

    /**
     * หน้า "แจ้งจำหน่าย" — รถจาก salecars (auto brand-scoped) ยกเว้น con_status 7,8,9
     * - ข้อมูลรถ/ราคาทุน/วันที่ปิด FP ดึงจาก carOrder ที่ผูก (CarOrderID)
     * - ฟิลด์แก้ไขได้: ชุดแจ้งจำหน่าย / วันที่รับ / วันที่ ทบ.เบิก / หมายเหตุ (เก็บบน salecars)
     * - ฟิลเตอร์: สถานะ (ยังไม่เบิก = ยังไม่มีวันที่ ทบ.เบิก / เบิกแล้ว) + เดือน (ตามวันที่รับ)
     */
    public function disposeList(Request $request)
    {
        $this->authorizeAccess();

        $brand     = (int) Auth::user()->brand;
        $brandName = config('brand.names')[$brand] ?? ('Brand ' . $brand);

        $status = $request->input('status', 'pending');   // pending (ยังไม่เบิก) | withdrawn (เบิกแล้ว)
        $month  = $request->input('month');                // YYYY-MM ของ "วันที่รับ" (ว่าง = ทุกเดือน)

        $query = Salecar::with([
                'carOrder' => fn ($q) => $q->with(['model', 'subModel', 'interiorColor', 'gwmColor']),
                'customer', 'model', 'subModel', 'interiorColor', 'gwmColor',
            ])
            ->whereNotIn('con_status', [7, 8, 9]);

        // สถานะ: ยังไม่เบิก = ยังไม่มีวันที่ ทบ.เบิก / เบิกแล้ว = มีแล้ว
        if ($status === 'withdrawn') {
            $query->whereNotNull('dispose_reg_withdraw_date');
        } else {
            $query->whereNull('dispose_reg_withdraw_date');
        }

        // เดือนตาม "วันที่รับ"
        if ($month) {
            [$y, $m] = array_pad(explode('-', $month), 2, null);
            if ($y && $m) {
                $query->whereYear('dispose_received_date', (int) $y)
                    ->whereMonth('dispose_received_date', (int) $m);
            }
        }

        $sales = $query->orderByDesc('dispose_received_date')
            ->orderByDesc('BookingDate')
            ->get();

        $rows = $sales->map(function ($s) {
            $co = $s->carOrder;

            // ข้อมูลรถดึงจาก carOrder ที่ผูก (fallback = salecar ถ้าไม่มี carOrder)
            $modelName = $co->model->Name_TH ?? $s->model->Name_TH ?? '-';
            $subModel  = $co->subModel->name ?? $s->subModel->name ?? '-';
            $year      = $co->year ?? $s->Year ?? '-';
            $color     = $co ? $co->display_color : $s->display_color;
            $option    = $co->option ?? $s->option ?? '-';
            $interior  = $co->interiorColor->name ?? $s->interiorColor->name ?? '-';

            $cus = $s->customer;
            $cusName = $cus
                ? trim(collect([$cus->FirstName, $cus->MiddleName, $cus->LastName])->filter()->implode(' '))
                : '';

            return [
                'id'           => $s->id,
                'vin'          => $co->vin_number ?? '-',
                'engine'       => $co->engine_number ?? '-',
                'modelName'    => $modelName,
                'subModelName' => $subModel,
                'year'         => $year ?: '-',
                'color'        => $color ?: '-',
                'option'       => $option ?: '-',
                'interior'     => $interior ?: '-',
                'cost'         => (float) ($co->car_DNP ?? 0),
                'customer'     => $cusName !== '' ? $cusName : '-',
                'fpCloseText'  => $co->format_fp_close_date ?? '-',
                'disposeSet'   => $s->dispose_set,
                'received'     => $s->dispose_received_date,          // Y-m-d สำหรับ input
                'receivedText' => $s->format_dispose_received_date ?? '-',
                'withdraw'     => $s->dispose_reg_withdraw_date,      // Y-m-d สำหรับ input
                'withdrawText' => $s->format_dispose_reg_withdraw_date ?? '-',
                'note'         => $s->dispose_note,
            ];
        });

        return view('floor-plan.dispose.view', [
            'rows'        => $rows,
            'brand'       => $brand,
            'brandName'   => $brandName,
            'status'      => $status,
            'month'       => $month,
            'disposeSets' => self::DISPOSE_SETS,
        ]);
    }

    /**
     * บันทึกข้อมูลแจ้งจำหน่ายของ salecar (ชุดแจ้งจำหน่าย / วันที่รับ / วันที่ ทบ.เบิก / หมายเหตุ)
     */
    public function updateDispose(Request $request, $id)
    {
        $this->authorizeAccess();

        $sale = Salecar::whereNotIn('con_status', [7, 8, 9])->findOrFail($id);

        $validated = $request->validate([
            'dispose_set'               => ['nullable', Rule::in(array_keys(self::DISPOSE_SETS))],
            'dispose_received_date'     => 'nullable|date',
            'dispose_reg_withdraw_date' => 'nullable|date',
            'dispose_note'              => 'nullable|string|max:1000',
        ]);

        $sale->dispose_set               = $validated['dispose_set'] ?: null;
        $sale->dispose_received_date     = $validated['dispose_received_date'] ?: null;
        $sale->dispose_reg_withdraw_date = $validated['dispose_reg_withdraw_date'] ?: null;
        $sale->dispose_note              = $validated['dispose_note'] ?: null;
        $sale->save();

        return response()->json([
            'success' => true,
            'message' => 'บันทึกข้อมูลแจ้งจำหน่ายเรียบร้อยแล้ว',
        ]);
    }

    /**
     * ออกรายงานแจ้งจำหน่าย (Excel) ตามฟิลเตอร์ปัจจุบัน (สถานะ + เดือนวันที่รับ)
     */
    public function exportDispose(Request $request)
    {
        $this->authorizeAccess();

        // รายงานยึด "เดือนของวันที่รับ" เท่านั้น (ไม่ยึดสถานะเบิก/ยังไม่เบิก)
        $month = $request->input('month');

        $suffix   = $month ? (' ' . $month) : '';
        $filename = 'รายงานแจ้งจำหน่าย' . $suffix . '.xlsx';

        return Excel::download(new DisposeReportExport($month), $filename);
    }
}
