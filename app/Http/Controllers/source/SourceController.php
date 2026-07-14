<?php

namespace App\Http\Controllers\source;

use App\Http\Controllers\Controller;
use App\Mail\SourcePlaceApprovalMail;
use App\Mail\SourcePlaceApprovedMail;
use App\Mail\SourcePlaceRevisionMail;
use App\Models\CustomerTracking;
use App\Models\SourcePlace;
use App\Models\SourcePlaceClear;
use App\Models\SourcePlaceRequest;
use App\Models\TbSalecarType;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SourceController extends Controller
{
    /** แหล่งที่มาหลักที่มี "สถานที่" */
    private function placeMain(): string
    {
        return config('source.place_main', 'offline');
    }

    public function index()
    {
        return redirect()->route('source.sub.index');
    }

    public function subIndex()
    {
        $mains = config('source.main', []);
        return view('source.sub.view', compact('mains'));
    }

    public function placeIndex()
    {
        $approvers = User::where('role', 'md')->orderBy('name')->get(['id', 'name', 'full_name']);
        return view('source.place.view', compact('approvers'));
    }

    /* ===================== แหล่งที่มาย่อย (sub-source) ===================== */

    public function listSub()
    {
        $mains = config('source.main', []);

        $data = TbSalecarType::orderBy('id')->get()->map(function ($s, $index) use ($mains) {
            return [
                'No'          => $index + 1,
                'name'        => $s->name,
                'main_source' => $mains[$s->main_source] ?? '-',
                'Action'      => view('source.sub.button', ['s' => $s])->render(),
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function createSub()
    {
        $mains = config('source.main', []);
        return view('source.sub.input', compact('mains'));
    }

    public function storeSub(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'        => 'required|string|max:255',
                'main_source' => ['required', Rule::in(array_keys(config('source.main', [])))],
            ]);

            TbSalecarType::create($validated);

            return response()->json(['success' => true, 'message' => 'เพิ่มข้อมูลเรียบร้อยแล้ว']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->validator->errors()->first()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }

    public function editSub($id)
    {
        $source = TbSalecarType::findOrFail($id);
        $mains  = config('source.main', []);
        return view('source.sub.edit', compact('source', 'mains'));
    }

    public function updateSub(Request $request, $id)
    {
        try {
            $source    = TbSalecarType::findOrFail($id);
            $validated = $request->validate([
                'name'        => 'required|string|max:255',
                'main_source' => ['required', Rule::in(array_keys(config('source.main', [])))],
            ]);

            $source->update($validated);

            return response()->json(['success' => true, 'message' => 'แก้ไขข้อมูลเรียบร้อยแล้ว']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->validator->errors()->first()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }

    public function destroySub($id)
    {
        // เฉพาะ admin เท่านั้น (กันเรียก endpoint ตรง ๆ แม้ปุ่มจะซ่อนแล้ว)
        abort_unless(Auth::user()->role === 'admin', 403);

        try {
            // soft delete — เก็บแถวไว้เพื่อให้ PO/การติดตามเดิมที่อ้างอิงยังแสดงชื่อได้
            // (relation ใช้ withTrashed) แต่จะหายจากลิสต์และ dropdown เลือกใหม่
            TbSalecarType::findOrFail($id)->delete();

            return response()->json(['success' => true, 'message' => 'ลบข้อมูลเรียบร้อยแล้ว']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }

    /* ===================== สถานที่ (place) ===================== */

    public function listPlace(Request $request)
    {
        $statuses = config('source.statuses', []);
        $state    = $request->input('state', 'active'); // active | settled | all
        $month    = $request->input('month');           // Y-m (ใช้เฉพาะ settled/all)

        $data = SourcePlace::with(['source', 'clears'])
            ->orderBy('id', 'desc')
            // สถานะ: กำลังใช้งาน = ยังไม่ปิดยอด / ปิดยอดแล้ว = ปิดยอด / ทั้งหมด = ไม่กรอง
            ->when($state === 'active', fn($q) => $q->whereNull('settled_at'))
            ->when($state === 'settled', fn($q) => $q->whereNotNull('settled_at'))
            // กรองเดือน (อิง period ของใบขออนุมัติ) เฉพาะตอนดูปิดยอด/ทั้งหมด — กันข้อมูลย้อนหลังเยอะเกิน
            ->when($state !== 'active' && $month && preg_match('/^\d{4}-\d{2}$/', $month), function ($q) use ($month) {
                $q->whereHas('request', fn($r) => $r->where('period', $month));
            })
            ->get()
            ->map(function ($p, $index) use ($statuses) {
            $st = $statuses[$p->status] ?? ['label' => $p->status, 'class' => 'bg-secondary'];
            // เลือกขออนุมัติได้เฉพาะ draft / rejected
            $selectable = in_array($p->status, [SourcePlace::STATUS_DRAFT, SourcePlace::STATUS_REJECTED]);

            return [
                'checkbox'     => $selectable
                    ? '<input type="checkbox" class="form-check-input place-chk" value="' . $p->id . '">'
                    : '',
                'No'           => $index + 1,
                'source'       => $p->source->name ?? '-',
                'location'     => $p->location,
                'las_number'   => $p->las_number ?? '-',
                'date_range'   => $this->dateRange($p),
                'expense_type' => $p->expense_type ?? '-',
                'cost'         => $this->costCell($p),
                'target'       => $p->target !== null ? number_format($p->target, 0) : '-',
                'status'       => '<div class="d-inline-flex flex-column align-items-center gap-1">'
                    . '<span class="badge rounded-pill ' . $st['class'] . '">' . $st['label'] . '</span>'
                    . ($p->isSettled()
                        ? '<span class="badge rounded-pill" style="background:#eef0f2;color:#5a6675;border:1px solid #d9dee3;font-weight:600;" title="บัญชีปิดยอดแล้ว"><i class="bx bx-lock-alt me-1"></i>ปิดยอดแล้ว</span>'
                        : '')
                    . '</div>',
                'Action'       => view('source.place.button', ['p' => $p])->render(),
            ];
        });

        return response()->json(['data' => $data]);
    }

    /** เซลล์ "ประมาณค่าใช้จ่าย" ในตาราง: แสดงงบรวม + ป้ายงบเพิ่มที่อนุมัติ/รออนุมัติ */
    private function costCell(SourcePlace $p): string
    {
        $budget = $p->effectiveBudget();
        $html   = $budget !== null ? number_format($budget, 2) : '-';

        if ($p->extra_cost) {
            $html .= ' <span class="badge bg-success" title="รวมงบเพิ่มที่อนุมัติแล้ว">+' . number_format($p->extra_cost, 2) . '</span>';
        }
        if ($p->pending_extra !== null) {
            $html .= ' <span class="badge bg-warning text-dark" title="งบเพิ่มที่รออนุมัติ">รออนุมัติ +' . number_format($p->pending_extra, 2) . '</span>';
        }

        return $html;
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

    public function createPlace()
    {
        $offlineSources = TbSalecarType::where('main_source', $this->placeMain())
            ->orderBy('name')->get();
        return view('source.place.input', compact('offlineSources'));
    }

    public function storePlace(Request $request)
    {
        try {
            $validated = $this->validatePlace($request);
            $user      = Auth::user();

            SourcePlace::create($validated + [
                'brand'      => $user->brand ?? null,
                'userZone'   => $user->userZone ?? null,
                'branch'     => $user->branch ?? null,
                'UserInsert' => $user->id ?? null,
            ]);

            return response()->json(['success' => true, 'message' => 'เพิ่มข้อมูลเรียบร้อยแล้ว']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->validator->errors()->first()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }

    public function editPlace($id)
    {
        $place          = SourcePlace::with('request')->findOrFail($id);
        $offlineSources = TbSalecarType::where('main_source', $this->placeMain())
            ->orderBy('name')->get();
        $approvers      = User::where('role', 'md')->orderBy('name')->get(['id', 'name', 'full_name']);
        return view('source.place.edit', compact('place', 'offlineSources', 'approvers'));
    }

    public function updatePlace(Request $request, $id)
    {
        try {
            $place     = SourcePlace::findOrFail($id);

            // ปิดยอดแล้ว = ดูอย่างเดียว ต้องเปิดใหม่ก่อนจึงแก้ได้
            if ($place->isSettled()) {
                return response()->json(['success' => false, 'message' => 'ปิดยอดแล้ว — ต้องเปิดใหม่ก่อนจึงแก้ไขได้'], 422);
            }

            $validated = $this->validatePlace($request);

            // ขออนุมัติแล้ว (รออนุมัติ/อนุมัติแล้ว) ห้ามแก้ ประมาณค่าใช้จ่าย/เป้า PP — กันแก้ฝั่ง client
            if (in_array($place->status, [SourcePlace::STATUS_PENDING, SourcePlace::STATUS_APPROVED])) {
                unset($validated['cost'], $validated['target']);
            }

            $place->update($validated);

            return response()->json(['success' => true, 'message' => 'แก้ไขข้อมูลเรียบร้อยแล้ว']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->validator->errors()->first()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }

    public function destroyPlace($id)
    {
        try {
            $place = SourcePlace::findOrFail($id);

            // ส่งขออนุมัติแล้ว (รออนุมัติ/อนุมัติแล้ว) ห้ามลบ — กันยิงลบตรงข้าม UI
            if (in_array($place->status, [SourcePlace::STATUS_PENDING, SourcePlace::STATUS_APPROVED])) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่สามารถลบได้ เนื่องจากสถานที่นี้ส่งขออนุมัติแล้ว',
                ], 422);
            }

            // กันลบ ถ้ามีการติดตามลูกค้าอ้างอิงสถานที่นี้อยู่ (ตรวจข้ามทุก scope)
            $used = CustomerTracking::withoutGlobalScopes()->where('place_id', $place->id)->exists();
            if ($used) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่สามารถลบได้ เนื่องจากมีการติดตามลูกค้าใช้สถานที่นี้อยู่',
                ], 422);
            }

            $place->delete();

            return response()->json(['success' => true, 'message' => 'ลบข้อมูลเรียบร้อยแล้ว']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }

    /* ===================== ขออนุมัติ (batch) ===================== */

    public function storeRequest(Request $request)
    {
        try {
            $validated = $request->validate([
                'place_ids'   => 'required|array|min:1',
                'place_ids.*' => 'integer',
                'approver_id' => ['required', Rule::exists('users', 'id')->where('role', 'md')],
                'period'      => 'required|date_format:Y-m',
            ], [
                'period.required'    => 'กรุณาเลือกประจำเดือน',
                'period.date_format' => 'รูปแบบเดือนไม่ถูกต้อง',
            ]);

            $user = Auth::user();

            // เลือกได้เฉพาะสถานที่ที่เป็น draft/rejected เท่านั้น
            $places = SourcePlace::whereIn('id', $validated['place_ids'])
                ->whereIn('status', [SourcePlace::STATUS_DRAFT, SourcePlace::STATUS_REJECTED])
                ->get();

            if ($places->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'ไม่พบสถานที่ที่ขออนุมัติได้'], 422);
            }

            $approver = User::where('role', 'md')->findOrFail($validated['approver_id']);
            if (!$approver->email) {
                return response()->json(['success' => false, 'message' => 'ผู้อนุมัติยังไม่มีอีเมลในระบบ'], 422);
            }

            $period = $validated['period']; // ประจำเดือนที่ผู้ขอเลือกเอง

            $req = DB::transaction(function () use ($places, $user, $approver, $period) {
                $req = SourcePlaceRequest::create([
                    'requester_id'  => $user->id,
                    'approver_id'   => $approver->id,
                    'status'        => SourcePlaceRequest::STATUS_PENDING,
                    'token'         => Str::random(48),
                    'period'        => $period,

                    'brand'         => $user->brand ?? null,
                    'userZone'      => $user->userZone ?? null,
                    'branch'        => $user->branch ?? null,
                ]);

                SourcePlace::whereIn('id', $places->pluck('id'))->update([
                    'request_id' => $req->id,
                    'status'     => SourcePlace::STATUS_PENDING,
                ]);

                return $req;
            });

            // ส่งเมลหาผู้อนุมัติ พร้อม PDF แนบ + ลิงก์อนุมัติ
            $pdf = $this->buildRequestPdf($req->fresh('places'));
            Mail::to($approver->email)->send(new SourcePlaceApprovalMail($req->fresh(['places', 'requester', 'approver']), $pdf->output()));

            return response()->json(['success' => true, 'message' => 'ส่งคำขออนุมัติเรียบร้อยแล้ว']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->validator->errors()->first()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()], 500);
        }
    }

    /* ===================== ขออนุมัติเพิ่ม (topup งบประมาณ) ===================== */

    public function storeTopupRequest(Request $request, $id)
    {
        try {
            $place = SourcePlace::with('request')->findOrFail($id);

            // ของบเพิ่มได้เฉพาะสถานที่ที่อนุมัติแล้ว และยังไม่มีคำขอเพิ่มที่ค้างอยู่
            if ($place->status !== SourcePlace::STATUS_APPROVED) {
                return response()->json(['success' => false, 'message' => 'ขออนุมัติเพิ่มได้เฉพาะสถานที่ที่อนุมัติแล้วเท่านั้น'], 422);
            }
            if ($place->pending_extra !== null) {
                return response()->json(['success' => false, 'message' => 'มีคำขออนุมัติเพิ่มที่รอผลอยู่แล้ว กรุณารอผลก่อน'], 422);
            }

            $request->merge([
                'extra_amount' => $request->filled('extra_amount') ? str_replace(',', '', $request->extra_amount) : null,
            ]);

            $validated = $request->validate([
                'extra_amount' => 'required|numeric|gt:0',
                'extra_reason' => 'required|string|max:500',
                'approver_id'  => ['required', Rule::exists('users', 'id')->where('role', 'md')],
                'period'       => 'required|date_format:Y-m',
            ], [
                'extra_amount.required' => 'กรุณากรอกจำนวนเงินที่ขอเพิ่ม',
                'extra_amount.gt'       => 'จำนวนเงินที่ขอเพิ่มต้องมากกว่า 0',
                'extra_reason.required' => 'กรุณาระบุเหตุผลในการขอเพิ่ม',
                'approver_id.required'  => 'กรุณาเลือกผู้อนุมัติ',
                'period.required'       => 'กรุณาเลือกประจำเดือน',
                'period.date_format'    => 'รูปแบบเดือนไม่ถูกต้อง',
            ]);

            $user     = Auth::user();
            $approver = User::where('role', 'md')->findOrFail($validated['approver_id']);
            if (!$approver->email) {
                return response()->json(['success' => false, 'message' => 'ผู้อนุมัติยังไม่มีอีเมลในระบบ'], 422);
            }

            $req = DB::transaction(function () use ($place, $user, $approver, $validated) {
                $req = SourcePlaceRequest::create([
                    'requester_id' => $user->id,
                    'approver_id'  => $approver->id,
                    'status'       => SourcePlaceRequest::STATUS_PENDING,
                    'type'         => SourcePlaceRequest::TYPE_TOPUP,
                    'token'        => Str::random(48),
                    'period'       => $validated['period'],
                    'brand'        => $user->brand ?? null,
                    'userZone'     => $user->userZone ?? null,
                    'branch'       => $user->branch ?? null,
                ]);

                // ผูกงบเพิ่มที่รออนุมัติเข้ากับสถานที่ (status เดิมคงเป็น approved — tracking ไม่กระทบ)
                $place->update([
                    'pending_extra'    => $validated['extra_amount'],
                    'extra_request_id' => $req->id,
                    'extra_reason'     => $validated['extra_reason'],
                ]);

                return $req;
            });

            // ส่งเมลหาผู้อนุมัติ พร้อม PDF แนบ + ลิงก์อนุมัติ
            $pdf = $this->buildApprovalPdf($req->fresh(['topupPlaces.source', 'requester', 'approver']));
            Mail::to($approver->email)->send(new SourcePlaceApprovalMail($req->fresh(['topupPlaces.source', 'requester', 'approver']), $pdf->output()));

            return response()->json(['success' => true, 'message' => 'ส่งคำขออนุมัติเพิ่มเรียบร้อยแล้ว']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->validator->errors()->first()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()], 500);
        }
    }

    /** สร้าง PDF ใบขออนุมัติ (dompdf + ฟอนต์ไทย Sarabun) */
    private function buildRequestPdf(SourcePlaceRequest $req)
    {
        $req->loadMissing(['places.source', 'requester', 'approver']);

        return Pdf::loadView('source.place.pdf', ['req' => $req])
            ->setPaper('a4', 'landscape')
            ->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true]);
    }

    /** เลือก PDF ตามชนิดคำขอ: topup ใช้ใบของบเพิ่ม, ปกติใช้ใบขออนุมัติสถานที่ */
    private function buildApprovalPdf(SourcePlaceRequest $req)
    {
        if ($req->is_topup) {
            $req->loadMissing(['topupPlaces.source', 'requester', 'approver']);

            return Pdf::loadView('source.place.topup-pdf', ['req' => $req])
                ->setPaper('a4', 'portrait')
                ->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true]);
        }

        return $this->buildRequestPdf($req);
    }

    /* ===================== รายงานสรุปตามเดือน (PDF) ===================== */

    public function reportMonthly(Request $request)
    {
        $period = $request->query('period');
        if (!$period || !preg_match('/^\d{4}-\d{2}$/', $period)) {
            abort(400, 'กรุณาระบุเดือนให้ถูกต้อง');
        }

        // สถานที่ทั้งหมดของเดือนนั้น (อ้างอิงจาก period ของใบขออนุมัติ)
        $places = SourcePlace::with(['source', 'clears'])
            ->whereHas('request', fn($q) => $q->where('period', $period))
            ->orderBy('start_date')          // เรียงตามวันเริ่มงานเป็นหลัก
            ->orderBy('salecar_type_id')      // วันเดียวกัน → เรียงตามประเภทเป็นตัวรอง
            ->get();

        // ยอด PP จริง = จำนวนการติดตามลูกค้าที่เลือกสถานที่นี้ (นับทุก scope, ไม่รวมที่ลบ)
        $places->each(function ($p) {
            $p->pp_actual = CustomerTracking::withoutGlobalScopes()
                ->whereNull('deleted_at')
                ->where('place_id', $p->id)
                ->count();
        });

        $pdf = Pdf::loadView('source.place.report', ['places' => $places, 'period' => $period])
            ->setPaper('a4', 'landscape')
            ->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true]);

        return $pdf->stream('source-report-' . $period . '.pdf');
    }

    // PDF สรุปการเคลียร์ค่าใช้จ่ายของสถานที่ (แนบรายการทำจ่าย — เห็นที่มาของค่าใช้จ่าย)
    public function clearPdf($id)
    {
        $place = SourcePlace::with(['source', 'request', 'clears.items', 'clears.payApprover', 'settledBy'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('source.place.clear-pdf', ['place' => $place])
            ->setPaper('a4', 'portrait')
            ->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true]);

        return $pdf->stream('clear-' . $place->id . '.pdf');
    }

    /* ===================== เคลียร์ค่าใช้จ่าย ===================== */

    private function accountingRoles(): array
    {
        return config('source.accounting_roles', ['account', 'admin', 'md']);
    }

    public function clearForm($id)
    {
        $place      = SourcePlace::with(['source', 'clears.items', 'clears.payApprover', 'settledBy'])->findOrFail($id);
        $clearTypes = config('source.clear_types', []);
        $canAccount = in_array(Auth::user()->role, $this->accountingRoles());

        return view('source.place.clear', compact('place', 'clearTypes', 'canAccount'));
    }

    public function storeClear(Request $request, $id)
    {
        try {
            $place = SourcePlace::findOrFail($id);

            // ปิดยอดแล้ว = ดูอย่างเดียว
            if ($place->isSettled()) {
                return response()->json(['success' => false, 'message' => 'ปิดยอดแล้ว — ต้องเปิดใหม่ก่อนจึงแก้ไขได้'], 422);
            }

            // ทำความสะอาดรายการ (ตัด comma, ตัดแถวว่าง)
            $items = collect($request->input('items', []))
                ->map(fn($it) => [
                    'type'   => $it['type'] ?? null,
                    'amount' => isset($it['amount']) && $it['amount'] !== '' ? str_replace(',', '', $it['amount']) : null,
                ])
                ->filter(fn($it) => !empty($it['type']) || $it['amount'] !== null)
                ->values();

            $validator = Validator::make(
                ['clear_date' => $request->clear_date, 'items' => $items->toArray()],
                [
                    'clear_date'     => 'nullable|date',
                    'items'          => 'required|array|min:1',
                    'items.*.type'   => ['required', Rule::in(config('source.clear_types', []))],
                    'items.*.amount' => 'required|numeric|min:0',
                ],
                [
                    'items.required'      => 'กรุณาเพิ่มรายการค่าใช้จ่ายอย่างน้อย 1 รายการ',
                    'items.*.type.required'   => 'กรุณาเลือกประเภทค่าใช้จ่ายให้ครบ',
                    'items.*.type.in'         => 'ประเภทค่าใช้จ่ายไม่ถูกต้อง',
                    'items.*.amount.required' => 'กรุณากรอกจำนวนเงินให้ครบ',
                    'items.*.amount.numeric'  => 'จำนวนเงินต้องเป็นตัวเลข',
                ]
            );

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }

            $total = $items->sum(fn($it) => (float) $it['amount']);

            // ใบเคลียร์ที่กำลังแก้ไข (ถ้ามี clear_id) — ต้องเป็นของสถานที่นี้
            $editing = null;
            if ($request->filled('clear_id')) {
                $editing = SourcePlaceClear::where('place_id', $place->id)->find($request->clear_id);
                if (!$editing) {
                    return response()->json(['success' => false, 'message' => 'ไม่พบใบเคลียร์ที่ต้องการแก้ไข'], 422);
                }
            }

            // ค่าใช้จ่ายจริงรวมทุกใบต้องไม่เกินงบประมาณ (ประมาณค่าใช้จ่าย + งบเพิ่มที่อนุมัติแล้ว)
            // งบคงเหลือสำหรับใบนี้ = งบรวม − ยอดใบอื่น (ไม่รวมใบที่กำลังแก้ไข)
            $budget = $place->effectiveBudget();
            if ($budget !== null) {
                $otherTotal = SourcePlaceClear::where('place_id', $place->id)
                    ->when($editing, fn($q) => $q->where('id', '!=', $editing->id))
                    ->sum('total');
                if ($total + (float) $otherTotal > $budget + 0.01) {
                    $remain = $budget - (float) $otherTotal;
                    return response()->json([
                        'success' => false,
                        'message' => 'ยอดใบนี้ (' . number_format($total, 2) . ' บาท) เกินงบคงเหลือ (' . number_format($remain, 2) . ' บาท) — เคลียร์ไปแล้ว ' . number_format((float) $otherTotal, 2) . ' จากงบ ' . number_format($budget, 2),
                    ], 422);
                }
            }

            $user  = Auth::user();

            DB::transaction(function () use ($place, $request, $items, $total, $user, $editing) {
                $clear = $editing ?: new SourcePlaceClear(['place_id' => $place->id]);
                $clear->clear_date = $request->clear_date ?: null;
                $clear->total      = $total;
                if (!$clear->exists) {
                    $clear->place_id   = $place->id;
                    $clear->brand      = $user->brand ?? null;
                    $clear->userZone   = $user->userZone ?? null;
                    $clear->branch     = $user->branch ?? null;
                    $clear->UserInsert = $user->id ?? null;
                }
                $clear->save();

                $clear->items()->delete();
                foreach ($items as $it) {
                    $clear->items()->create(['type' => $it['type'], 'amount' => $it['amount']]);
                }
            });

            return response()->json(['success' => true, 'message' => $editing ? 'อัปเดตใบเคลียร์เรียบร้อยแล้ว' : 'บันทึกใบเคลียร์เรียบร้อยแล้ว']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }

    public function approveClearPay(Request $request, $id)
    {
        // เฉพาะบัญชี/แอดมิน/MD
        abort_unless(in_array(Auth::user()->role, $this->accountingRoles()), 403);

        try {
            $place = SourcePlace::findOrFail($id);

            if ($place->isSettled()) {
                return response()->json(['success' => false, 'message' => 'ปิดยอดแล้ว — ต้องเปิดใหม่ก่อนจึงแก้ไขได้'], 422);
            }

            $request->validate(
                ['pay_date' => 'required|date', 'clear_id' => 'required'],
                [
                    'pay_date.required' => 'กรุณาระบุวันที่จ่ายก่อนอนุมัติ',
                    'clear_id.required' => 'ไม่พบใบเคลียร์ที่ต้องการอนุมัติ',
                ]
            );

            // อนุมัติ "ต่อใบเคลียร์" — ต้องเป็นใบของสถานที่นี้
            $clear = SourcePlaceClear::where('place_id', $place->id)->find($request->clear_id);
            if (!$clear) {
                return response()->json(['success' => false, 'message' => 'ไม่พบใบเคลียร์ที่ต้องการอนุมัติ'], 422);
            }

            $clear->update([
                'pay_date'        => $request->pay_date ?: null,
                'pay_approved'    => 1,
                'pay_approved_by' => Auth::id(),
                'pay_approved_at' => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'อนุมัติการจ่ายเรียบร้อยแล้ว']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->validator->errors()->first()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }

    // ลบใบเคลียร์ (งวด) หนึ่งใบ — ลบรายการย่อยด้วย
    public function destroyClear($id, $clearId)
    {
        try {
            $place = SourcePlace::findOrFail($id);

            if ($place->isSettled()) {
                return response()->json(['success' => false, 'message' => 'ปิดยอดแล้ว — ต้องเปิดใหม่ก่อนจึงลบได้'], 422);
            }

            $clear = SourcePlaceClear::where('place_id', $place->id)->find($clearId);

            if (!$clear) {
                return response()->json(['success' => false, 'message' => 'ไม่พบใบเคลียร์'], 422);
            }

            // อนุมัติจ่ายแล้ว ลบได้เฉพาะบัญชี/แอดมิน/MD
            if ($clear->pay_approved && !in_array(Auth::user()->role, $this->accountingRoles())) {
                return response()->json(['success' => false, 'message' => 'ใบเคลียร์นี้อนุมัติจ่ายแล้ว ไม่สามารถลบได้'], 403);
            }

            DB::transaction(function () use ($clear) {
                $clear->items()->delete();
                $clear->delete();
            });

            return response()->json(['success' => true, 'message' => 'ลบใบเคลียร์เรียบร้อยแล้ว']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }

    // ปิดยอด/จบงาน (บัญชี) — ต้องเคลียร์และจ่ายครบทุกงวดก่อน แล้วจึงซ่อนออกจากรายการ
    public function settlePlace($id)
    {
        abort_unless(in_array(Auth::user()->role, $this->accountingRoles()), 403);

        try {
            $place = SourcePlace::with('clears')->findOrFail($id);

            if (!$place->canSettle()) {
                return response()->json([
                    'success' => false,
                    'message' => 'ต้องมีใบเคลียร์และอนุมัติจ่ายครบทุกงวดก่อนจึงจะปิดยอดได้',
                ], 422);
            }

            $place->update(['settled_at' => now(), 'settled_by' => Auth::id()]);

            return response()->json(['success' => true, 'message' => 'ปิดยอดเรียบร้อยแล้ว']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }

    // เปิดใหม่ (ยกเลิกปิดยอด) — บัญชี + admin
    public function reopenPlace($id)
    {
        abort_unless(in_array(Auth::user()->role, $this->accountingRoles()) || Auth::user()->role === 'admin', 403);

        try {
            $place = SourcePlace::findOrFail($id);
            $place->update(['settled_at' => null, 'settled_by' => null]);

            return response()->json(['success' => true, 'message' => 'เปิดรายการใหม่เรียบร้อยแล้ว']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }

    /* ===================== อนุมัติผ่านลิงก์ (ไม่ต้อง login) ===================== */

    /**
     * หาใบคำขอจาก token (ลิงก์ในอีเมล) — ข้าม global scope brand ทั้งใบคำขอและ relation สถานที่
     * เพราะผู้อนุมัติอาจล็อกอินอยู่คนละ brand กับคำขอ (ไม่งั้นหาไม่เจอ → 404)
     */
    private function findRequestByToken($token): SourcePlaceRequest
    {
        $unscope = fn($q) => $q->withoutGlobalScopes();

        return SourcePlaceRequest::withoutGlobalScopes()
            ->with([
                'places'      => fn($q) => $q->withoutGlobalScopes()->with(['source' => $unscope]),
                'topupPlaces' => fn($q) => $q->withoutGlobalScopes()->with(['source' => $unscope]),
                'requester',
                'approver',
            ])
            ->where('token', $token)
            ->firstOrFail();
    }

    public function showApproval($token)
    {
        $req = $this->findRequestByToken($token);

        return view('source.place.approval', ['req' => $req]);
    }

    public function approve($token)
    {
        return $this->decide($token, SourcePlaceRequest::STATUS_APPROVED);
    }

    public function reject(Request $request, $token)
    {
        return $this->decide($token, SourcePlaceRequest::STATUS_REJECTED, $request->input('reject_reason'));
    }

    private function decide($token, $decision, $reason = null)
    {
        $req = $this->findRequestByToken($token);

        // กดซ้ำ / ดำเนินการไปแล้ว
        if ($req->status !== SourcePlaceRequest::STATUS_PENDING) {
            return view('source.place.approval', ['req' => $req, 'alreadyDone' => true]);
        }

        $isTopup     = $req->is_topup;
        $placeStatus = $decision === SourcePlaceRequest::STATUS_APPROVED
            ? SourcePlace::STATUS_APPROVED
            : SourcePlace::STATUS_REJECTED;

        DB::transaction(function () use ($req, $decision, $reason, $placeStatus, $isTopup) {
            $req->update([
                'status'        => $decision,
                'reject_reason' => $decision === SourcePlaceRequest::STATUS_REJECTED ? $reason : null,
                'decided_at'    => now(),
            ]);

            if ($isTopup) {
                // ของบเพิ่ม: ไม่แตะ status ของสถานที่ (ยังคงเป็น approved → tracking ไม่กระทบ)
                foreach (SourcePlace::withoutGlobalScopes()->where('extra_request_id', $req->id)->get() as $p) {
                    if ($decision === SourcePlaceRequest::STATUS_APPROVED) {
                        // อนุมัติ → รวมงบเพิ่มเข้ากับ extra_cost สะสม
                        $p->extra_cost = (float) ($p->extra_cost ?? 0) + (float) ($p->pending_extra ?? 0);
                    }
                    // ไม่ว่าจะอนุมัติหรือส่งกลับ → ล้างสถานะที่รออนุมัติ
                    $p->pending_extra    = null;
                    $p->extra_request_id = null;
                    $p->extra_reason     = null;
                    $p->save();
                }
            } else {
                SourcePlace::withoutGlobalScopes()->where('request_id', $req->id)->update(['status' => $placeStatus]);
            }
        });

        // topup: ใช้ $req ใน memory (relation topupPlaces ยังมีค่า pending_extra ก่อนถูกล้างใน transaction)
        // place: reload ใหม่ (unscoped) เพื่อให้ได้ status ล่าสุดของสถานที่
        $mailReq = $isTopup ? $req : $this->findRequestByToken($token);

        // ส่งกลับให้แก้ไข → แจ้งผู้ขอทางอีเมลให้แก้แล้วส่งใหม่
        if ($decision === SourcePlaceRequest::STATUS_REJECTED && optional($req->requester)->email) {
            try {
                Mail::to($req->requester->email)->send(new SourcePlaceRevisionMail($mailReq, $reason));
            } catch (\Exception $e) {
                // ไม่ให้เมลล้มเหลวมาขวางผลการตัดสิน
            }
        }

        // อนุมัติแล้ว → ส่งเมลให้บัญชี (acc.mitsuchookiatkrabi@gmail.com) cc บัญชีเดิม (acct@chookiat.org) + ผู้ขอ
        if ($decision === SourcePlaceRequest::STATUS_APPROVED) {
            try {
                $pdf = $this->buildApprovalPdf($mailReq);

                // cc() set ทับ ไม่ append → รวมเป็น array เดียว
                $cc = ['acct@chookiat.org'];
                if (optional($req->requester)->email) {
                    $cc[] = $req->requester->email;
                }

                Mail::to('acc.mitsuchookiatkrabi@gmail.com')
                    ->cc($cc)
                    ->send(new SourcePlaceApprovedMail($mailReq, $pdf->output()));
            } catch (\Exception $e) {
                // ไม่ให้เมลล้มเหลวมาขวางผลการตัดสิน
            }
        }

        // topup ใช้ $mailReq (in-memory) ที่ relation topupPlaces ยังมีค่าให้แสดงบนหน้ายืนยันผล
        return view('source.place.approval', [
            'req'         => $mailReq,
            'justDecided' => true,
        ]);
    }

    /** validate + เตรียมค่าของสถานที่ (แปลงเงิน) */
    private function validatePlace(Request $request): array
    {
        $request->merge([
            'cost'   => $request->filled('cost') ? str_replace(',', '', $request->cost) : null,
            'target' => $request->filled('target') ? str_replace(',', '', $request->target) : null,
        ]);

        return $request->validate([
            'salecar_type_id' => ['required', Rule::exists('tb_salecar_type', 'id')->where('main_source', $this->placeMain())],
            'location'        => 'required|string|max:255',
            'las_number'      => 'nullable|string|max:255',
            'start_date'      => 'nullable|date',
            'end_date'        => 'nullable|date|after_or_equal:start_date',
            'expense_type'    => ['nullable', Rule::in(config('source.expense_types', []))],
            'cost'            => 'nullable|numeric|min:0',
            'target'          => 'nullable|integer|min:0',
        ]);
    }

    /* ===================== API cascade ===================== */

    /** คืนสถานที่ของ sub-source (offline) สำหรับ dropdown ในหน้าเพิ่ม customer-tracking — brand-aware */
    public function apiPlaces($sourceId)
    {
        $places = SourcePlace::where('salecar_type_id', $sourceId)
            ->where('status', SourcePlace::STATUS_APPROVED)
            // แสดงถึงวันจบงาน +1 วัน (เผื่อเซลล์กรอกข้อมูลย้อนหลัง) — ยังไม่ระบุวันจบ = แสดงไว้
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', now()->subDay()->toDateString());
            })
            ->orderBy('location')
            ->get()
            ->map(function ($p) {
                $range = $this->dateRange($p);
                $label = $range !== '-' ? "{$p->location} ({$range})" : $p->location;
                return ['id' => $p->id, 'label' => $label];
            });

        return response()->json($places);
    }
}
