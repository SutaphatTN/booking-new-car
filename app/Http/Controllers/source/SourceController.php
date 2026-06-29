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

    public function listPlace()
    {
        $statuses = config('source.statuses', []);

        $data = SourcePlace::with('source')
            // ซ่อนรายการที่เคลียร์เสร็จแล้ว (บัญชีอนุมัติการจ่ายแล้ว)
            ->whereDoesntHave('clear', fn($q) => $q->where('pay_approved', 1))
            ->orderBy('id', 'desc')->get()->map(function ($p, $index) use ($statuses) {
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
                'status'       => '<span class="badge ' . $st['class'] . '">' . $st['label'] . '</span>',
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
            $validated = $this->validatePlace($request);

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
        $places = SourcePlace::with(['source', 'clear'])
            ->whereHas('request', fn($q) => $q->where('period', $period))
            ->orderBy('salecar_type_id')
            ->orderBy('start_date')
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

    /* ===================== เคลียร์ค่าใช้จ่าย ===================== */

    private function accountingRoles(): array
    {
        return config('source.accounting_roles', ['account', 'admin', 'md']);
    }

    public function clearForm($id)
    {
        $place      = SourcePlace::with(['source', 'clear.items', 'clear.payApprover'])->findOrFail($id);
        $clearTypes = config('source.clear_types', []);
        $canAccount = in_array(Auth::user()->role, $this->accountingRoles());

        return view('source.place.clear', compact('place', 'clearTypes', 'canAccount'));
    }

    public function storeClear(Request $request, $id)
    {
        try {
            $place = SourcePlace::findOrFail($id);

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

            // ค่าใช้จ่ายจริงต้องไม่เกินงบประมาณรวม (ประมาณค่าใช้จ่าย + งบเพิ่มที่อนุมัติแล้ว)
            $budget = $place->effectiveBudget();
            if ($budget !== null && $total > $budget) {
                return response()->json([
                    'success' => false,
                    'message' => 'ยอดค่าใช้จ่ายจริง (' . number_format($total, 2) . ' บาท) เกินงบประมาณ (' . number_format($budget, 2) . ' บาท)',
                ], 422);
            }

            $user  = Auth::user();

            DB::transaction(function () use ($place, $request, $items, $total, $user) {
                $clear = SourcePlaceClear::firstOrNew(['place_id' => $place->id]);
                $clear->clear_date = $request->clear_date ?: null;
                $clear->total      = $total;
                if (!$clear->exists) {
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

            return response()->json(['success' => true, 'message' => 'บันทึกการเคลียร์เรียบร้อยแล้ว']);
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
            $clear = SourcePlaceClear::where('place_id', $place->id)->first();

            if (!$clear) {
                return response()->json(['success' => false, 'message' => 'ยังไม่มีข้อมูลการเคลียร์'], 422);
            }

            $request->validate(
                ['pay_date' => 'required|date'],
                ['pay_date.required' => 'กรุณาระบุวันที่จ่ายก่อนอนุมัติ']
            );

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

    /* ===================== อนุมัติผ่านลิงก์ (ไม่ต้อง login) ===================== */

    public function showApproval($token)
    {
        $req = SourcePlaceRequest::with(['places.source', 'topupPlaces.source', 'requester', 'approver'])
            ->where('token', $token)->firstOrFail();

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
        $req = SourcePlaceRequest::with(['places.source', 'topupPlaces.source', 'requester', 'approver'])
            ->where('token', $token)->firstOrFail();

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
                foreach (SourcePlace::where('extra_request_id', $req->id)->get() as $p) {
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
                SourcePlace::where('request_id', $req->id)->update(['status' => $placeStatus]);
            }
        });

        // topup: ใช้ $req ใน memory (relation topupPlaces ยังมีค่า pending_extra ก่อนถูกล้างใน transaction)
        // place: reload ใหม่เพื่อให้ได้ status ล่าสุดของสถานที่
        $mailReq = $isTopup ? $req : $req->fresh(['places.source', 'requester', 'approver']);

        // ส่งกลับให้แก้ไข → แจ้งผู้ขอทางอีเมลให้แก้แล้วส่งใหม่
        if ($decision === SourcePlaceRequest::STATUS_REJECTED && optional($req->requester)->email) {
            try {
                Mail::to($req->requester->email)->send(new SourcePlaceRevisionMail($mailReq, $reason));
            } catch (\Exception $e) {
                // ไม่ให้เมลล้มเหลวมาขวางผลการตัดสิน
            }
        }

        // อนุมัติแล้ว → แจ้งผู้ขออนุมัติ และส่งสำเนาให้บัญชี (acc@chookiat.org)
        if ($decision === SourcePlaceRequest::STATUS_APPROVED) {
            try {
                $pdf = $this->buildApprovalPdf($mailReq);

                // $mail = Mail::to('acc@chookiat.org');
                $mail = Mail::to('acct@chookiat.org');
                if (optional($req->requester)->email) {
                    $mail->cc($req->requester->email);
                }
                $mail->send(new SourcePlaceApprovedMail($mailReq, $pdf->output()));
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
