<?php

namespace App\Http\Controllers\floor_plan;

use App\Http\Controllers\Controller;
use App\Models\CarOrder;
use App\Models\FpMorRate;
use App\Models\FpInterestRate;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class FloorPlanController extends Controller
{
    // เมนู Floor Plan เห็น/แก้ได้เฉพาะ admin, audit_lead
    private const ALLOWED_ROLES = ['admin', 'audit_lead'];

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

        $orders = CarOrder::with(['model', 'subModel', 'interiorColor', 'gwmColor'])
            ->where('payment_type', 'fp_tisco')
            ->orderByDesc('fp_date')
            ->get();

        $rows = $orders->map(function ($o) use ($brand) {
            $billing = $o->fp_date ? Carbon::parse($o->fp_date) : null;
            $close   = $o->fp_close_date ? Carbon::parse($o->fp_close_date) : null;
            $cost    = (float) ($o->car_DNP ?? 0);

            $isClosed  = $billing && $close && $close->gte($billing);
            $calc      = $isClosed ? $this->buildFpSegments($billing, $close, $brand, $cost) : null;

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
}
