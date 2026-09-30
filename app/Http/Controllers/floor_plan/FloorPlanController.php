<?php

namespace App\Http\Controllers\floor_plan;

use App\Http\Controllers\Controller;
use App\Exports\dispose\DisposeReportExport;
use App\Exports\fp\FpReportExport;
use App\Models\CarOrder;
use App\Models\CarOrderFpClosing;
use App\Models\FpMorRate;
use App\Models\FpInterestRate;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use App\Support\ExportFilename;
use App\Support\BrandFeature;
use Illuminate\Support\Facades\DB;

class FloorPlanController extends Controller
{
    // เข้าเมนู Floor Plan ได้ (ดูทุกหน้า + โหลด Excel)
    // 2026-08-31 เพิ่ม account — ดูได้ทุกหน้าและแก้ "แจ้งจำหน่าย" ได้ (มีชุด "ส่งบัญชี" เป็นงานของฝ่ายนี้)
    // แต่ห้ามแตะอัตราดอกเบี้ยกับวันปิด FP → คุมด้วย 2 const ด้านล่าง
    private const ALLOWED_ROLES = ['admin', 'audit_internal', 'md', 'account'];

    // แก้ข้อมูล FP ในโมดัล (Billing date / วันที่ปิด FP / Net Amount)
    private const FP_EDIT_ROLES = ['admin', 'audit_internal', 'md'];

    // แก้อัตราดอกเบี้ยวงเงิน (MOR / spread) — ค่ากลางที่กระทบดอกเบี้ยทุกคัน
    private const RATE_EDIT_ROLES = ['admin', 'audit_internal', 'md'];

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

    private function canEditFp(): bool
    {
        return in_array(Auth::user()->role, self::FP_EDIT_ROLES, true);
    }

    private function canEditRate(): bool
    {
        return in_array(Auth::user()->role, self::RATE_EDIT_ROLES, true);
    }

    /**
     * งวด (period) เก็บด้วย "เดือนที่เริ่ม" (YYYY-MM) — 1 งวด = วันที่ 16 ของเดือนนั้น ถึงวันที่ 15 ของเดือนถัดไป
     * เช่น period '2026-06' = 16/06/2026 – 15/07/2026
     */

    // งวดที่วันที่หนึ่ง ๆ ตกอยู่: ตั้งแต่วันที่ 16 = งวดของเดือนนั้น / ก่อนวันที่ 16 = งวดเดือนก่อน
    // เช่น 16/07 และ 15/08 อยู่งวดเดียวกัน = '2026-07' (16/07/2026 – 15/08/2026)
    private function periodOf(Carbon $date): string
    {
        return $date->day >= 16
            ? $date->format('Y-m')
            : $date->copy()->subMonthNoOverflow()->format('Y-m');
    }

    // งวดปัจจุบันตามวันนี้
    private function currentPeriod(): string
    {
        return $this->periodOf(now());
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

        $canEditRate = $this->canEditRate();

        return view('floor-plan.interest-rate.view', compact(
            'month',
            'brand',
            'brandName',
            'mor',
            'spreads',
            'buckets',
            'periodLabel',
            'morIsThisMonth',
            'spreadIsThisMonth',
            'canEditRate'
        ));
    }

    /**
     * บันทึกอัตราดอกเบี้ยวงเงินของ "เดือนที่เลือก"
     * - upsert เฉพาะเดือนนั้น ไม่แตะเดือนอื่น (เก็บประวัติแต่ละเดือน)
     */
    public function updateInterestRate(Request $request)
    {
        $this->authorizeAccess();

        // account เข้าหน้านี้ได้แต่แก้ไม่ได้ (กันยิง endpoint ตรง)
        abort_unless($this->canEditRate(), 403);

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
     * - ปิด FP วันเดียวกับ Billing date → นับเป็น 1 วัน (ไม่ใช่ 0)
     *
     * @param float $netAmount ยอดที่ใช้คิดดอกเบี้ย (fp_net_amount ถ้ากรอกไว้ ไม่งั้นใช้ car_DNP)
     */
    private function buildFpSegments(Carbon $billing, Carbon $close, int $brand, float $netAmount): array
    {
        // ปิดวันเดียวกับ billing → คิด 1 วัน (diffInDays จะได้ 0 ซึ่งทำให้ดอกเบี้ยเป็น 0)
        $sameDay   = $billing->isSameDay($close);
        $totalDays = $sameDay ? 1 : (int) $billing->diffInDays($close);   // นับส่วนต่าง (exclusive)
        $bucketCol = $this->spreadBucketColumn($totalDays);

        $segments      = [];
        $totalInterest = 0.0;

        $segStart = $billing->copy();
        // เดือนตั้งต้นของ segment = เดือนของ "งวด" ที่ billing date ตกอยู่ (16→15) ไม่ใช่ calendar month
        // เช่น billing 14/08 อยู่งวด 2026-07 → ขอบงวดถัดไปคือ 16/08
        // (ถ้าใช้ startOfMonth ขอบจะเพี้ยนไปเป็น 16/09 ทำให้คันที่ billing ก่อนวันที่ 16 ไม่ถูกแตกงวด
        //  และป้ายชื่องวดไม่ตรงกับ periodOf() ที่ใช้กรองรายงาน)
        $periodMonth = Carbon::createFromFormat('Y-m-d', $this->periodOf($billing) . '-01')->startOfDay();

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
            $days    = (int) ($isLast ? $segStart->diffInDays($close) : $segStart->diffInDays($nextBoundary));

            // ปิดวันเดียวกับ billing → มี segment เดียวเสมอ นับ 1 วัน
            if ($sameDay) {
                $days = 1;
            }

            $interest = $netAmount * ($rate / 100) * ($days / 365);
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
     *
     * @param Carbon|null $estimateTo วันตัด "ประมาณการ" สำหรับคันที่ยังไม่ปิด FP (ใช้เฉพาะรายงาน Excel)
     *        ถ้าส่งมา คันที่ยังไม่มีวันที่ปิดจะถูกคิดดอกเบี้ยถึงวันนี้แทน แล้วติดธง isEstimated
     *        หน้า list ไม่ส่ง → พฤติกรรมเดิมทุกอย่าง (รอปิด FP = ไม่มีดอกเบี้ย)
     */
    private function fpRows(int $brand, ?Carbon $estimateTo = null)
    {
        $orders = CarOrder::with([
                'model', 'subModel', 'interiorColor', 'gwmColor',
                // ใบจองผูกผ่าน salecars.CarOrderID (car_order.salecar_id ไม่ถูกใช้)
                'salecars' => fn ($q) => $q->with(['remainingPayment.financeInfo', 'customer.prefix']),
                'fpClosings',
            ])
            ->where('payment_type', 'fp_tisco')
            ->orderByDesc('fp_date')
            ->get();

        return $orders->map(function ($o) use ($brand, $estimateTo) {
            $billing = $o->fp_date ? Carbon::parse($o->fp_date) : null;
            $close   = $o->fp_close_date ? Carbon::parse($o->fp_close_date) : null;
            $cost    = (float) ($o->car_DNP ?? 0);

            // ยอดที่ใช้คิดดอกเบี้ย — ตั้งต้นจากราคาทุน แต่แก้ทับได้ (ค่าประดับยนต์ทำให้บางคันไม่ตรง)
            $netAmount = $o->fp_net_amount !== null ? (float) $o->fp_net_amount : $cost;

            // ข้อมูลการเงินจากใบจอง (ถ้ามี)
            $sale = $o->salecars->first();

            // ── ยอดที่คิดดอกเบี้ย ──
            // ปกติ = ยอดเดียว (Net Amount × Billing → วันปิด FP)
            // แบ่งปิด (brand ที่เปิดไว้ + มีแถวใน car_order_fp_closings) = หลายยอด ใช้ Billing เดียวกัน
            // แต่ละยอดคิดแยก (จำนวนวัน/ช่วง aging ของ MLR เป็นของยอดนั้นเอง) แล้วรวมกัน
            $isSplit  = BrandFeature::hasFpSplitClose($brand) && $o->fpClosings->isNotEmpty();
            $tranches = $isSplit
                ? $o->fpClosings->map(fn ($c) => [
                    'seq'    => (int) $c->seq,
                    'amount' => (float) $c->amount,
                    'close'  => $c->close_date ? Carbon::parse($c->close_date) : null,
                ])->all()
                : [['seq' => null, 'amount' => $netAmount, 'close' => $close]];

            $trancheRows = [];
            foreach ($tranches as $t) {
                $tClosed = $billing && $t['close'] && $t['close']->gte($billing);
                $tCalc   = $tClosed ? $this->buildFpSegments($billing, $t['close'], $brand, $t['amount']) : null;

                // ยังไม่ปิด + มีวันตัดประมาณการ → คิดดอกเบี้ยถึงวันตัดนั้นแทน (billing ต้องไม่เลยวันตัดไปแล้ว)
                $tEstimated = !$tClosed && $estimateTo && $billing && $billing->lte($estimateTo);
                if ($tEstimated) {
                    $tCalc = $this->buildFpSegments($billing, $estimateTo->copy(), $brand, $t['amount']);
                }

                // Rate = อัตราเฉลี่ยถ่วงน้ำหนักด้วยจำนวนวันของทุก segment
                // (คร่อมหลายงวดมี MOR/MLR คนละค่า) — segment เดียวจะเท่ากับ rate ของงวดนั้นพอดี
                $tDays = $tCalc['totalDays'] ?? 0;

                $trancheRows[] = [
                    'seq'           => $t['seq'],
                    'amount'        => $t['amount'],
                    'closeDate'     => $t['close'] ? $t['close']->format('Y-m-d') : null,
                    // ประมาณการ → แสดงวันตัด (วันที่ 15 สิ้นงวด) แทนช่องว่าง / d-m-Y ให้ตรงกับ accessor
                    'closeText'     => $tEstimated
                        ? $estimateTo->format('d-m-Y')
                        : ($t['close'] ? $t['close']->format('d-m-Y') : '-'),
                    'isClosed'      => $tClosed,
                    'isEstimated'   => $tEstimated,
                    // ติดเลขยอดไว้ทุก segment — หน้า list ใช้แยกจำนวนวันรายยอด
                    'segments'      => array_map(fn ($s) => $s + ['seq' => $t['seq']], $tCalc['segments'] ?? []),
                    'totalDays'     => $tCalc['totalDays'] ?? null,
                    'totalInterest' => $tCalc['totalInterest'] ?? null,
                    'rate'          => $tCalc && $t['amount'] > 0 && $tDays > 0
                        ? $tCalc['totalInterest'] / ($t['amount'] * $tDays / 365) * 100
                        : null,
                ];
            }

            $tr          = collect($trancheRows);
            $isClosed    = $tr->every(fn ($t) => $t['isClosed']);
            $isEstimated = $tr->contains(fn ($t) => $t['isEstimated']);
            $hasCalc     = $tr->contains(fn ($t) => $t['totalInterest'] !== null);
            $calc = $hasCalc ? [
                'segments'      => $tr->pluck('segments')->flatten(1)->all(),
                'totalDays'     => $tr->max('totalDays'),
                'totalInterest' => $tr->sum(fn ($t) => $t['totalInterest'] ?? 0),
            ] : null;

            // Rate รวมทั้งคัน = ดอกรวม ÷ (Σ ยอด × วันของยอด ÷ 365) — ยอดเดียวได้ค่าเท่าสูตรเดิม
            $weight = $tr->sum(fn ($t) => $t['totalDays'] ? $t['amount'] * $t['totalDays'] / 365 : 0);
            $rate   = $calc && $weight > 0 ? $calc['totalInterest'] / $weight * 100 : null;

            return [
                'id'            => $o->id,
                'modelName'     => $o->model->Name_TH ?? '-',
                'subModelName'  => $o->subModel->name ?? '-',
                'vin'           => $o->vin_number ?: '-',
                'billingText'   => $o->format_fp_date ?? '-',
                'billingDate'   => $billing ? $billing->format('Y-m-d') : null,   // Y-m-d สำหรับ input
                // งวดของ Billing date = งวด 16–15 ที่วันนั้นตกอยู่ (ไม่ใช่ calendar month)
                // เช่น billing 03/08/2026 → งวด '2026-07' (16/07 – 15/08) ตรงกับ statement ของ Tisco
                'billingPeriod' => $billing ? $this->periodOf($billing) : null,
                'year'          => $o->year ?: '-',
                'option'        => $o->option ?: '-',
                'color'         => $o->display_color ?? '-',
                'interior'      => $o->interiorColor->name ?? '-',
                'engine'        => $o->engine_number ?: '-',
                'jNumber'       => $o->j_number ?: '-',
                'cost'          => $cost,
                'netAmount'     => $netAmount,
                // กรอกยอดเองไว้หรือยัง (ใช้บอกในหน้าจอว่าค่านี้มาจากราคาทุนหรือคนกรอก)
                'netIsCustom'   => $o->fp_net_amount !== null,
                // ── ข้อมูลการเงินจากใบจอง (salecars ผูกด้วย CarOrderID) ──
                'customerName'   => $sale && $sale->customer
                    ? (trim(($sale->customer->prefix->Name_TH ?? '') . ' ' . ($sale->customer->FirstName ?? '') . ' ' . ($sale->customer->LastName ?? '')) ?: '-')
                    : '-',
                'downPayment'    => $sale && $sale->DownPayment !== null ? (float) $sale->DownPayment : null,
                'balanceFinance' => $sale && $sale->balanceFinance !== null ? (float) $sale->balanceFinance : null,
                'financeName'    => $sale->remainingPayment->financeInfo->FinanceCompany ?? '-',
                // วันส่งมอบจากใบจอง — คันที่ยังไม่มีใบจอง/ยังไม่ส่งมอบจะเป็น '-'
                'deliveryDate'   => $sale->DeliveryDate ?? null,            // Y-m-d
                'deliveryText'   => $sale->format_delivery_date ?? '-',
                'closeDate'     => $o->fp_close_date,          // Y-m-d สำหรับ input
                // แบ่งปิด → วันปิดทุกยอดต่อกัน "27-09-2026 / 29-09-2026"
                // ปกติ: ประมาณการ → แสดงวันตัด (วันที่ 15 สิ้นงวด) แทนช่องว่าง (d-m-Y ตรงกับ accessor)
                'closeText'     => $isSplit
                    ? $tr->pluck('closeText')->implode(' / ')
                    : $trancheRows[0]['closeText'],
                'isClosed'      => $isClosed,
                'isEstimated'   => $isEstimated,
                'isSplit'       => $isSplit,
                // แบ่งปิดแล้วบางยอด (ยอดอื่นยังไม่มีวันปิด)
                'isPartial'     => $isSplit && !$isClosed && $tr->contains(fn ($t) => $t['isClosed']),
                'tranches'      => $trancheRows,
                'segments'      => $calc['segments'] ?? [],
                'totalDays'     => $calc['totalDays'] ?? null,
                'rate'          => $rate,
                'totalInterest' => $calc['totalInterest'] ?? null,
            ];
        });
    }

    /**
     * คีย์เรียงลำดับของรายการ FP — ใช้ร่วมกันทั้งหน้า list และรายงาน Excel (ลำดับจะได้ไม่ต่างกัน)
     *  วันส่งมอบ เก่า → ใหม่ / คันที่ยังไม่ส่งมอบยกไปกองท้าย เรียงกันเองตาม Billing date เก่า → ใหม่
     * เทียบเป็น string ตรง ๆ ได้เพราะทุกวันเป็น Y-m-d ความยาวคงที่ ('9999-99-99' = ไม่มีวัน → ท้ายสุด)
     */
    private function fpSortKey(array $r): string
    {
        return ($r['deliveryDate'] ? '0_' . $r['deliveryDate'] : '1_')
            . ($r['billingDate'] ?: '9999-99-99');
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

        // กรอง: รอปิด FP แสดงเสมอ (ยกเว้นเลือกสถานะ "ปิดแล้ว")
        // ปิดแล้ว = แสดงในทุกงวดที่คันนั้น "กินวัน" อยู่ ไม่ใช่เฉพาะงวดของ Billing date
        // (คันที่คร่อมงวด เช่น billing 14/08 ปิด 17/08 จะเห็นทั้งงวด 2026-07 และ 2026-08)
        $rows = $rows->filter(function ($r) use ($month, $status) {
            $isPending = !$r['isClosed'];
            $inPeriod  = collect($r['segments'])->contains(fn ($s) => $s['period'] === $month);

            if ($status === 'pending') return $isPending;
            if ($status === 'closed')  return !$isPending && $inPeriod;

            // all
            return $isPending || $inPeriod;
        })->values();

        // ตัวเลขเฉพาะ "งวดที่เลือก" (คันคร่อมงวดจะเห็นวัน/ดอกเบี้ยของงวดนั้นเท่านั้น)
        // คันที่ยังไม่ปิด FP ไม่มี segment → null (หน้า list ไม่ประมาณการ ต่างจากรายงาน Excel)
        $rows = $rows->map(function ($r) use ($month) {
            $segs = collect($r['segments'])->where('period', $month);

            // แบ่งปิด → วันของแต่ละยอดนับแยก ("9 / 11") บวกรวมกันไม่ได้เพราะเป็นช่วงวันซ้อนกัน
            $r['periodDays']     = $segs->isEmpty() ? null
                : ($r['isSplit']
                    ? $segs->groupBy('seq')->map(fn ($g) => (int) $g->sum('days'))->implode(' / ')
                    : (int) $segs->sum('days'));
            $r['periodInterest'] = $segs->isNotEmpty() ? (float) $segs->sum('interest') : null;

            return $r;
        });

        // ลำดับ : วันส่งมอบ เก่า → ใหม่ (ดู fpSortKey) — รายงาน Excel ใช้คีย์เดียวกันภายในแต่ละงวด
        $rows = $rows->sortBy(fn ($r) => $this->fpSortKey($r))->values();

        $canEditFp = $this->canEditFp();

        return view('floor-plan.fp.view', compact(
            'rows',
            'brand',
            'brandName',
            'month',
            'status',
            'periodLabel',
            'canEditFp'
        ));
    }

    /**
     * บันทึกข้อมูล FP ที่แก้ในโมดัล ลง car_order — admin / audit_internal / md
     *  - fp_date        Billing date (บางคันยังไม่มี ต้องกรอกเอง)
     *  - fp_close_date  วันที่ปิด FP (เว้นว่างได้ = กลับเป็น "รอปิด FP")
     *  - fp_net_amount  ยอดที่ใช้คิดดอกเบี้ย — บันทึกทุกครั้งที่ส่งมา ไม่ว่าจะแก้หรือใช้ยอดเดิม
     *                   (ก่อนหน้านี้คิดจาก car_DNP ตรง ๆ ซึ่งไม่ตรงเมื่อมีค่าประดับยนต์)
     */
    public function updateFpCloseDate(Request $request, $id)
    {
        $this->authorizeAccess();

        if (!$this->canEditFp()) {
            abort(403);
        }

        $order = CarOrder::where('payment_type', 'fp_tisco')->findOrFail($id);

        // ช่องกรอกใส่ลูกน้ำให้อ่านง่าย (905,509.84) — ตัดออกก่อน validate
        foreach (['fp_net_amount', 'split_amount1'] as $moneyField) {
            if ($request->has($moneyField)) {
                $request->merge([
                    $moneyField => str_replace(',', '', (string) $request->input($moneyField)),
                ]);
            }
        }

        $validated = $request->validate([
            'fp_close_date' => 'nullable|date',
            'fp_date'       => 'nullable|date',
            'fp_net_amount' => 'nullable|numeric|min:0',
            // แบ่งปิด 2 ยอด (เฉพาะ brand ที่เปิดไว้) — ยอดที่ 2 = Net − ยอดที่ 1 คิดที่ server เสมอ
            'split_amount1' => 'nullable|numeric|min:0',
            'split_close1'  => 'nullable|date',
            'split_close2'  => 'nullable|date',
        ]);

        // ส่งมาจากโมดัลของ brand ที่แบ่งปิดได้ + กรอกยอดที่ 1 = โหมดแบ่งปิด
        $canSplit  = BrandFeature::hasFpSplitClose($order->brand) && $request->has('split_amount1');
        $isSplit   = $canSplit && ($validated['split_amount1'] ?? '') !== '' && $validated['split_amount1'] !== null;

        // Billing date ที่จะใช้เทียบ = ค่าที่ส่งมา (ถ้าฟอร์มส่งมา) มิฉะนั้นใช้ของเดิม
        $editBilling = $request->has('fp_date');
        $billing     = $editBilling ? ($validated['fp_date'] ?: null) : $order->fp_date;

        // วันปิดทุกช่องที่ใช้จริงต้องไม่ก่อน Billing date
        $closeFields = $isSplit ? ['split_close1', 'split_close2'] : ['fp_close_date'];
        foreach ($closeFields as $f) {
            if (!empty($validated[$f]) && $billing
                && Carbon::parse($validated[$f])->lt(Carbon::parse($billing))) {
                throw ValidationException::withMessages([
                    $f => 'วันที่ปิด FP ต้องไม่ก่อน Billing date',
                ]);
            }
        }

        if ($editBilling) {
            $order->fp_date = $billing;
        }
        if ($request->has('fp_net_amount')) {
            // เว้นว่าง = กลับไปใช้ราคาทุน (car_DNP)
            $order->fp_net_amount = $validated['fp_net_amount'] !== null && $validated['fp_net_amount'] !== ''
                ? $validated['fp_net_amount']
                : null;
        }

        // Net Amount ที่ใช้แบ่ง = ค่าที่เพิ่งบันทึก หรือราคาทุนถ้าเว้นว่าง
        $net = round((float) ($order->fp_net_amount ?? $order->car_DNP ?? 0), 2);

        $splitRows = [];
        if ($isSplit) {
            $amount1 = round((float) $validated['split_amount1'], 2);
            if ($amount1 <= 0 || $amount1 >= $net) {
                throw ValidationException::withMessages([
                    'split_amount1' => 'ยอดที่ 1 ต้องมากกว่า 0 และน้อยกว่า Net Amount (' . number_format($net, 2) . ')',
                ]);
            }
            $splitRows = [
                1 => ['amount' => $amount1,                'close_date' => $validated['split_close1'] ?: null],
                2 => ['amount' => round($net - $amount1, 2), 'close_date' => $validated['split_close2'] ?: null],
            ];
            // fp_close_date = วันปิดยอดสุดท้าย เมื่อปิดครบทุกยอด (รายงานแจ้งจำหน่ายยังอ่านช่องนี้)
            $dates = array_filter(array_column($splitRows, 'close_date'));
            $order->fp_close_date = count($dates) === count($splitRows) ? max($dates) : null;
        } else {
            $order->fp_close_date = $validated['fp_close_date'] ?: null;
        }

        DB::transaction(function () use ($order, $canSplit, $isSplit, $splitRows) {
            $order->save();

            if (!$canSplit) {
                return;
            }

            if (!$isSplit) {
                // ล้างยอดที่ 1 = กลับไปปิดครั้งเดียว
                CarOrderFpClosing::where('car_order_id', $order->id)->delete();
                return;
            }

            foreach ($splitRows as $seq => $data) {
                $row = CarOrderFpClosing::firstOrNew(['car_order_id' => $order->id, 'seq' => $seq]);
                if (!$row->exists) {
                    $row->UserInsert = Auth::id();
                }
                $row->fill($data);
                $row->UserUpdate = Auth::id();
                $row->save();
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'บันทึกข้อมูล FP เรียบร้อยแล้ว',
        ]);
    }

    /**
     * ออกรายงาน FP (Excel) — ยึดตาม "งวด" (16 → 15 ของเดือนถัดไป) ไม่ใช่รายคัน
     * 1 แถว = 1 คัน × 1 งวด : คันที่คร่อมงวดจะแยกแถวตามงวด แต่ละแถวคิดจำนวนวัน/ดอกเบี้ย
     * เฉพาะช่วงที่ตกในงวดนั้น (ใช้ MOR/MLR ของงวดนั้น) รวมทุกงวดแล้วเท่ากับดอกเบี้ยทั้งคัน
     * เลือกได้เป็น "ช่วงเดือน" (month_from – month_to) เพื่อดูหลายงวดรวมกัน
     * ไม่ระบุเลย = ทุกงวด (ทุกคัน fp_tisco)
     *
     * คันที่ยังไม่ปิด FP จะถูกประมาณการดอกเบี้ยถึง "วันที่ 15 สิ้นงวดของเดือนสุดท้ายที่เลือก"
     * แล้วทำสีเหลือง + ใส่ * ในรายงาน (ดู FpReportExport)
     */
    public function exportFp(Request $request)
    {
        $this->authorizeAccess();

        $brand = (int) Auth::user()->brand;

        $from = $request->input('month_from');
        $to   = $request->input('month_to');

        // ลิงก์เดิมส่ง month มาตัวเดียว — ยังรองรับไว้
        if (!$from && !$to) {
            $from = $to = $request->input('month');
        }
        // เลือกมาข้างเดียว = เดือนเดียว
        $from = $from ?: $to;
        $to   = $to   ?: $from;

        // กรอกกลับด้าน → สลับให้ (YYYY-MM เทียบเป็น string ได้ตรงตามลำดับเวลา)
        if ($from && $to && $to < $from) {
            [$from, $to] = [$to, $from];
        }

        // วันตัดประมาณการ = วันสิ้นงวดของเดือนสุดท้ายที่เลือก (วันที่ 15 ของเดือนถัดไป)
        $estimateTo = $to ? $this->periodRange($to)[1] : null;

        $rows = $this->fpRows($brand, $estimateTo);

        // ── แตกเป็น "รายงวด" : 1 แถว = 1 คัน × 1 งวด ──
        // คันที่คร่อมงวด (เช่น billing 14/08 ปิด 17/08) จะได้ 2 แถว — งวด 2026-07 ช่วง 14–15/08
        // และงวด 2026-08 ช่วง 16–17/08 โดยจำนวนวัน/ดอกเบี้ยของแต่ละแถวคิดเฉพาะช่วงในงวดนั้น
        // (รวมทุกงวดแล้วเท่ากับดอกเบี้ยทั้งคันเท่าเดิม)
        // คันที่แบ่งปิด → แยกแถวรายยอดด้วย (1 แถว = 1 คัน × 1 ยอด × 1 งวด) Net Amount = ยอดนั้น
        $reportRows = [];
        foreach ($rows as $car) {
            foreach ($car['tranches'] as $t) {
                $r = array_merge($car, [
                    'netAmount'   => $t['amount'],
                    'closeText'   => $t['closeText'],
                    'isClosed'    => $t['isClosed'],
                    'isEstimated' => $t['isEstimated'],
                    'trancheSeq'  => $t['seq'] ?? 0,
                    'trancheText' => $car['isSplit'] ? "ยอด {$t['seq']}/" . count($car['tranches']) : null,
                ]);
                $segments = $t['segments'] ?: [];

                // ไม่มี segment = ยังไม่มี Billing date → ไม่มีงวดให้ลง เก็บไว้เฉพาะตอนไม่กรองงวด
                if (!$segments) {
                    if (!$from) {
                        $reportRows[] = array_merge($r, [
                            'period'      => null,
                            'periodText'  => '-',
                            'periodFrom'  => $r['billingText'],
                            'periodTo'    => $r['closeText'],
                            'segDays'     => null,
                            'segRate'     => null,
                            'segInterest' => null,
                            'isEstimateCut' => false,
                        ]);
                    }
                    continue;
                }

                foreach ($segments as $seg) {
                    if ($from && ($seg['period'] < $from || $seg['period'] > $to)) {
                        continue;
                    }
                    $reportRows[] = array_merge($r, [
                        'period'      => $seg['period'],
                        'periodText'  => Carbon::createFromFormat('Y-m-d', $seg['period'] . '-01')->format('m/Y'),
                        'periodFrom'  => $seg['startText'],
                        'periodTo'    => $seg['endText'],
                        'segDays'     => $seg['days'],
                        'segRate'     => $seg['rate'],
                        'segInterest' => $seg['interest'],
                        // ใส่ * เฉพาะงวดสุดท้ายที่จบด้วย "วันตัดประมาณการ" จริง ๆ
                        // (งวดกลาง ๆ จบที่วันที่ 15 ตามปกติ ไม่ใช่วันประมาณการ)
                        'isEstimateCut' => $r['isEstimated']
                            && $estimateTo
                            && $seg['endText'] === $estimateTo->format('d/m/Y'),
                    ]);
                }
            }
        }

        // เรียงตามงวดก่อน (รายงานเลือกได้หลายงวด ต้องจัดกลุ่มให้ตรงกับ statement ของ Tisco)
        // แล้วภายในงวดเดียวกันเรียงแบบเดียวกับหน้า list — วันส่งมอบ เก่า → ใหม่ (ดู fpSortKey)
        // คันที่คร่อมงวดจึงอยู่ตำแหน่งเดียวกันในทุกงวดที่มันโผล่ / คันแบ่งปิดเรียงยอด 1 → 2 ติดกัน
        usort($reportRows, fn ($a, $b) => [$a['period'] ?? '', $this->fpSortKey($a), $a['id'], $a['trancheSeq']]
            <=> [$b['period'] ?? '', $this->fpSortKey($b), $b['id'], $b['trancheSeq']]);

        $rangeLabel = $from
            ? ($from === $to ? " {$from}" : " {$from} ถึง {$to}")
            : '';
        $filename = ExportFilename::withBrand('รายงาน FP' . $rangeLabel . '.xlsx');

        return Excel::download(new FpReportExport($reportRows, $brand, $estimateTo), $filename);
    }

    /**
     * หน้า "แจ้งจำหน่าย" — ยึดจาก car_order ทุกคัน (auto brand-scoped)
     * เอกสารแจ้งจำหน่ายผูกกับ "คัน" ไม่ใช่ใบจอง เพราะใบจองสลับรถได้
     * (ของเดิมยิงจาก salecars ทำให้รถที่ยังไม่มีใบจอง/ถูกสลับออกหายไปจากหน้านี้)
     * - ฟิลด์แก้ไขได้: ชุดแจ้งจำหน่าย / วันที่รับ / วันที่ ทบ.เบิก / หมายเหตุ (เก็บบน car_order)
     * - ชื่อลูกค้าดึงจากใบจองที่ยังไม่ถอน (con_status 7,8,9 = ถอน) ถ้าไม่มีใบจองก็เว้นไว้
     * - ฟิลเตอร์: สถานะ (ยังไม่เบิก = ยังไม่มีวันที่ ทบ.เบิก / เบิกแล้ว) + เดือน (ตามวันที่รับ)
     */
    public function disposeList(Request $request)
    {
        $this->authorizeAccess();

        $brand     = (int) Auth::user()->brand;
        $brandName = config('brand.names')[$brand] ?? ('Brand ' . $brand);

        $status = $request->input('status', 'pending');   // pending (ยังไม่เบิก) | withdrawn (เบิกแล้ว)
        $month  = $request->input('month');                // YYYY-MM ของ "วันที่รับ" (ว่าง = ทุกเดือน)

        // acrossBranches() = เห็นรถทุกสาขาใน brand (เหมือน admin) — หน้านี้ใช้กันไม่กี่คน
        // และเป็นงานภาพรวมทั้ง brand สลับสาขาไปมาเสียเวลากว่า
        $query = CarOrder::acrossBranches()
            ->with(['model', 'subModel', 'interiorColor', 'gwmColor', 'purchaseType', 'dealerProvince']);

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

        $orders = $query->orderByDesc('dispose_received_date')
            ->orderByDesc('order_date')
            ->orderByDesc('id')
            ->get();

        $rows = $orders->map(function ($co) {
            return [
                'id'           => $co->id,
                'vin'          => $co->vin_number ?: '-',
                'engine'       => $co->engine_number ?: '-',
                'jNumber'      => $co->j_number ?: '-',
                'modelName'    => $co->model->Name_TH ?? '-',
                'subModelName' => $co->subModel->name ?? '-',
                'year'         => $co->year ?: '-',
                'color'        => $co->display_color ?: '-',
                'option'       => $co->option ?: '-',
                'interior'     => $co->interiorColor->name ?? '-',
                'cost'         => (float) ($co->car_DNP ?? 0),
                // ข้อมูลการซื้อ — ใช้ดูประกอบตอนแจ้งจำหน่าย (โดยเฉพาะรถที่ซื้อมาจากดีลเลอร์อื่น)
                'source'         => $co->purchase_source ?: '-',
                'purchaseType'   => $co->purchaseType->name ?? '-',
                'paymentType'    => $co->payment_type_label ?: '-',
                'dealerProvince' => $co->purchase_source === CarOrder::SOURCE_DEALER
                    ? ($co->dealerProvince->name ?? '')
                    : '',
                'dealerName'     => $co->purchase_source === CarOrder::SOURCE_DEALER
                    ? ($co->dealer_name ?: '')
                    : '',
                'fpCloseText'  => $co->format_fp_close_date ?? '-',
                'disposeSet'   => $co->dispose_set,
                'received'     => $co->dispose_received_date,          // Y-m-d สำหรับ input
                'receivedText' => $co->format_dispose_received_date ?? '-',
                'withdraw'     => $co->dispose_reg_withdraw_date,      // Y-m-d สำหรับ input
                'withdrawText' => $co->format_dispose_reg_withdraw_date ?? '-',
                'note'         => $co->dispose_note,
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
     * บันทึกข้อมูลแจ้งจำหน่ายของรถ 1 คัน (ชุดแจ้งจำหน่าย / วันที่รับ / วันที่ ทบ.เบิก / หมายเหตุ)
     * $id = car_order.id (ของเดิมเป็น salecars.id)
     */
    public function updateDispose(Request $request, $id)
    {
        $this->authorizeAccess();

        // ต้องใช้ scope เดียวกับหน้าลิสต์ ไม่งั้นรถของสาขาอื่นที่โชว์อยู่จะกดแก้ไม่ได้ (404)
        $order = CarOrder::acrossBranches()->findOrFail($id);

        $validated = $request->validate([
            'dispose_set'               => ['nullable', Rule::in(array_keys(self::DISPOSE_SETS))],
            'dispose_received_date'     => 'nullable|date',
            'dispose_reg_withdraw_date' => 'nullable|date',
            'dispose_note'              => 'nullable|string|max:1000',
        ]);

        $order->dispose_set               = $validated['dispose_set'] ?: null;
        $order->dispose_received_date     = $validated['dispose_received_date'] ?: null;
        $order->dispose_reg_withdraw_date = $validated['dispose_reg_withdraw_date'] ?: null;
        $order->dispose_note              = $validated['dispose_note'] ?: null;
        $order->save();

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

        // รายงานยึด "เดือนของวันที่รับ" + สถานะเบิกตามตัวกรองในหน้า
        //  pending = ยังไม่เบิก | withdrawn = เบิกแล้ว | อื่น ๆ/ว่าง = ทุกสถานะ (ลิงก์เก่าที่ไม่ส่ง status)
        $month  = $request->input('month');
        $status = $request->input('status');

        $statusLabel = ['pending' => ' (ยังไม่เบิก)', 'withdrawn' => ' (เบิกแล้ว)'][$status] ?? '';

        $suffix   = $statusLabel . ($month ? (' ' . $month) : '');
        $filename = ExportFilename::withBrand('รายงานแจ้งจำหน่าย' . $suffix . '.xlsx');

        return Excel::download(new DisposeReportExport($month, $status), $filename);
    }
}
