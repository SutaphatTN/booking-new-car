<?php

namespace App\Http\Controllers\campaign;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignApproval;
use App\Mail\CampaignApprovalMail;
use App\Mail\CampaignApprovedMail;
use App\Mail\CampaignRevisionMail;
use App\Support\ScopeBypass;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CampaignApprovalController extends Controller
{
    // ── หน้าอนุมัติแคมเปญ CK (type = 4) ──
    public function index()
    {
        $period = $this->normalizePeriod(request('period'));
        return view('campaign.approval.view', compact('period'));
    }

    // DataTables serverSide — แคมเปญ type = 4 พร้อมสถานะอนุมัติของเดือนที่เลือก
    public function list(Request $request)
    {
        $draw   = (int) ($request->draw ?? 1);
        $start  = (int) ($request->start ?? 0);
        $length = (int) ($request->length ?? 10);
        $search = trim($request->input('search.value', ''));
        $period = $this->normalizePeriod($request->input('period'));

        // เช็ค type = 4 (CK) จาก tb_campaign_type แทนการฟิค id
        $base = Campaign::query()
            ->where('archived', 0)
            ->whereHas('type', fn($q) => $q->where('type', 4));

        $recordsTotal = (clone $base)->count();

        // ── ตัวกรองคอลัมน์ รุ่นรถ ──
        if ($request->filled('model_filter')) {
            $ids = json_decode($request->model_filter, true);
            if (is_array($ids) && count($ids)) {
                $base->whereIn('model_id', $ids);
            }
        }

        $this->applyCkSearch($base, $search);

        // ── ตัวกรองคอลัมน์ สถานะ (ตามเดือนที่เลือก) ──
        //   none = ยังไม่ขอ (ไม่มี approval ของเดือนนั้น) · pending/approved/rejected = ตาม status
        if ($request->filled('status_filter')) {
            $statuses = json_decode($request->status_filter, true);
            if (is_array($statuses) && count($statuses) && count($statuses) < 4) {
                $base->where(function ($q) use ($statuses, $period) {
                    foreach ($statuses as $st) {
                        if ($st === 'none') {
                            $q->orWhereDoesntHave('approvals', fn($a) => $a->where('period_ym', $period));
                        } elseif (in_array($st, ['pending', 'approved', 'rejected'], true)) {
                            $q->orWhereHas('approvals', fn($a) => $a->where('period_ym', $period)->where('status', $st));
                        }
                    }
                });
            }
        }

        $recordsFiltered = (clone $base)->count();

        $cam = $base
            ->with([
                'model',
                'subModel',
                'type',
                'appellation',
                'approvals' => fn($q) => $q->where('period_ym', $period),
            ])
            ->orderBy('id')
            ->skip($start)
            ->take($length)
            ->get();

        $rowNum = $start + 1;
        $data = $cam->map(function ($c) use (&$rowNum) {
            $ap = $c->approvals->first();

            $modelC   = $c->model->Name_TH ?? '';
            $subModel = $c->subModel?->name ?? '-';
            $name     = $c->appellation?->name ?? '';

            return [
                'No'            => $rowNum++,
                'model'         => trim($modelC . ' / ' . $subModel),
                'name'          => $name,
                'type'          => $c->type?->name ?? '-',
                'amount'        => $c->cashSupport_final !== null ? number_format($c->cashSupport_final, 2) : '-',
                'status'        => $this->statusBadge($ap),
                'Action'        => view('campaign.button', ['c' => $c])->render(),
            ];
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data->values(),
        ]);
    }

    // ── รายการแคมเปญ CK ที่ "ยังไม่อนุมัติ" ของเดือนนั้น (ใช้ใน modal เลือกขออนุมัติ) ──
    public function pendingList(Request $request)
    {
        $period = $this->normalizePeriod($request->input('period'));

        $cams = Campaign::query()
            ->where('archived', 0)
            ->whereHas('type', fn($t) => $t->where('type', 4))
            ->with([
                'model',
                'subModel',
                'type',
                'appellation',
                'approvals' => fn($q) => $q->where('period_ym', $period),
            ])
            ->orderBy('id')
            ->get();

        $data = $cams
            ->filter(function ($c) {
                $ap = $c->approvals->first();
                // เหลือเฉพาะที่ยัง "ต้องขอ" = ยังไม่ขอ หรือ ส่งกลับแก้ไข
                // ตัด approved (อนุมัติแล้ว) และ pending (รออนุมัติ — กันขอซ้ำทับ token เดิม) ออก
                return !$ap || !in_array($ap->status, ['approved', 'pending'], true);
            })
            ->map(function ($c) {
                $ap = $c->approvals->first();
                $statusText = 'ยังไม่ขอ';
                if ($ap) {
                    $statusText = $ap->status === 'pending' ? 'รออนุมัติ' : ($ap->status === 'rejected' ? 'ส่งกลับแก้ไข' : 'ยังไม่ขอ');
                }

                return [
                    'id'         => $c->id,
                    'model_id'   => $c->model->id ?? 0,
                    'model_main' => $c->model->Name_TH ?? '-',           // รุ่นหลัก (ใช้จัดกลุ่ม/เลือกทั้งรุ่น)
                    'sub'        => $c->subModel?->name ?? '-',           // รุ่นย่อย
                    'model'      => trim(($c->model->Name_TH ?? '') . ' / ' . ($c->subModel?->name ?? '-')),
                    'name'       => $c->appellation?->name ?? '-',
                    'type'       => $c->type?->name ?? '-',
                    'amount'     => (float) ($c->cashSupport_final ?? 0),
                    'status'     => $statusText,
                ];
            })
            // จัดเรียงตามรุ่นหลัก เพื่อให้จัดกลุ่มในโมดัลได้ต่อเนื่อง
            ->sortBy('model_main')
            ->values();

        return response()->json(['period' => $period, 'data' => $data]);
    }

    // ── ตัวเลือกรุ่นรถ (สำหรับ dropdown ฟิลเตอร์คอลัมน์ รุ่นรถ) ──
    public function modelOptions()
    {
        $models = Campaign::query()
            ->where('archived', 0)
            ->whereHas('type', fn($t) => $t->where('type', 4))
            ->with('model:id,Name_TH')
            ->get()
            ->pluck('model')
            ->filter()
            ->unique('id')
            ->sortBy('Name_TH')
            ->map(fn($m) => ['id' => $m->id, 'name' => $m->Name_TH])
            ->values();

        return response()->json($models);
    }

    // ── ขออนุมัติเป็นชุด (เลือกหลายแคมเปญ หรือทั้งหมดทุกหน้า) — ส่งอีเมลหา MD ครั้งเดียว ──
    public function requestApproval(Request $request)
    {
        $request->validate([
            'period_ym'      => 'required|date_format:Y-m',
            'select_all'     => 'nullable|boolean',
            'campaign_ids'   => 'required_without:select_all|array',
            'campaign_ids.*' => 'integer',
        ]);

        try {
            $period = $this->normalizePeriod($request->period_ym);
            [$startDate, $endDate] = $this->periodRange($period);
            $token = Str::random(48);

            // แคมเปญ CK (type = 4) ที่ยังไม่ archived
            $q = Campaign::with('type')
                ->where('archived', 0)
                ->whereHas('type', fn($t) => $t->where('type', 4));

            if ($request->boolean('select_all')) {
                // ทั้งหมดทุกหน้า (เคารพคำค้นหาที่กรองอยู่ ถ้ามี)
                $this->applyCkSearch($q, trim((string) $request->input('search', '')));
            } else {
                $q->whereIn('id', $request->campaign_ids);
            }

            $campaigns = $q->get();

            $created         = collect();
            $skippedApproved = 0;
            $skippedPending  = 0;
            $skippedNotCk    = 0;

            foreach ($campaigns as $campaign) {
                // ข้ามตัวที่ไม่ใช่ CK
                if ((int) ($campaign->type->type ?? 0) !== 4) {
                    $skippedNotCk++;
                    continue;
                }

                // ข้ามตัวที่อนุมัติแล้ว / รออนุมัติอยู่ ในเดือนนั้น
                //  - approved: กันรีเซ็ตของที่ใช้งานอยู่
                //  - pending : กันขอซ้ำทับ token เดิม (ลิงก์อนุมัติในเมลรอบก่อนจะใช้ไม่ได้)
                $existing = CampaignApproval::where('campaign_id', $campaign->id)
                    ->where('period_ym', $period)
                    ->first();
                if ($existing && $existing->status === 'approved') {
                    $skippedApproved++;
                    continue;
                }
                if ($existing && $existing->status === 'pending') {
                    $skippedPending++;
                    continue;
                }

                $ap = CampaignApproval::updateOrCreate(
                    ['campaign_id' => $campaign->id, 'period_ym' => $period],
                    [
                        'start_date'     => $startDate,
                        'end_date'       => $endDate,
                        'status'         => 'pending',
                        'approval_token' => $token,
                        'note'           => null,
                        'requested_by'   => Auth::id(),
                        'requested_at'   => now(),
                        'approved_by'    => null,
                        'approved_at'    => null,
                        'brand'          => $campaign->brand,
                        'branch'         => $campaign->branch,
                        'userZone'       => $campaign->userZone,
                    ]
                );
                $created->push($ap);
            }

            if ($created->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => ($skippedApproved > 0 || $skippedPending > 0)
                        ? 'แคมเปญที่เลือกถูกขอ/อนุมัติสำหรับเดือนนี้แล้วทั้งหมด'
                        : 'ไม่มีแคมเปญ CK ที่ต้องขออนุมัติ',
                ], 422);
            }

            $brand = $created->first()->brand;
            $this->sendBatchMail($period, $token, $brand);

            $msg = 'ส่งคำขออนุมัติ ' . $created->count() . ' รายการ ไปยัง MD เรียบร้อยแล้ว (เดือน ' . $period . ')';
            $skips = [];
            if ($skippedApproved > 0) $skips[] = "{$skippedApproved} รายการที่อนุมัติแล้ว";
            if ($skippedPending > 0)  $skips[] = "{$skippedPending} รายการที่รออนุมัติอยู่";
            if ($skips) {
                $msg .= ' — ข้าม ' . implode(' · ', $skips);
            }

            return response()->json(['success' => true, 'message' => $msg, 'period' => $period]);
        } catch (\Exception $e) {
            Log::error('CK approval request failed: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน',
            ], 500);
        }
    }

    // ── หน้าอนุมัติจากลิงก์ในเมล (ไม่ต้อง login — ใช้ token) — แสดงทั้งชุด ──
    public function emailApprove($token)
    {
        // เปิดผ่านลิงก์ในเมล — ผู้กดอาจล็อกอินคนละ brand → ปิด BrandScope ทั้ง request
        ScopeBypass::$brand = true;

        $approvals = CampaignApproval::withoutGlobalScopes()
            ->with(['campaign.model', 'campaign.subModel', 'campaign.appellation', 'campaign.type'])
            ->where('approval_token', $token)
            ->orderBy('id')
            ->get();

        if ($approvals->isEmpty()) {
            return response('ลิงก์ไม่ถูกต้องหรือหมดอายุ', 404);
        }

        // ถ้าไม่มีตัวที่ยัง pending แล้ว → แสดงผลว่าดำเนินการไปแล้ว
        if ($approvals->where('status', 'pending')->isEmpty()) {
            return view('campaign.approval.result', [
                'approvals' => $approvals,
                'period'    => $approvals->first()->period_ym,
                'msg'       => 'คำขอชุดนี้ถูกดำเนินการไปแล้ว',
            ]);
        }

        return view('campaign.approval.approve', [
            'approvals' => $approvals,
            'period'    => $approvals->first()->period_ym,
            'token'     => $token,
        ]);
    }

    // MD กดอนุมัติทั้งชุด
    public function approve($token)
    {
        ScopeBypass::$brand = true; // ผู้อนุมัติอาจล็อกอินคนละ brand → ปิด BrandScope ทั้ง request

        $approvals = CampaignApproval::withoutGlobalScopes()
            ->with(['campaign.model', 'campaign.subModel', 'campaign.appellation', 'campaign.type', 'requester'])
            ->where('approval_token', $token)
            ->get();

        abort_if($approvals->isEmpty(), 404);

        $approved = collect();
        foreach ($approvals->where('status', 'pending') as $ap) {
            $ap->update(['status' => 'approved', 'approved_at' => now()]);
            $approved->push($ap);
        }

        // แจ้งผู้ขอทางอีเมลว่าแคมเปญได้รับการอนุมัติแล้ว (เมลล้มเหลวไม่ให้ขวางผลการตัดสิน)
        $this->notifyApproved($approved);

        return view('campaign.approval.result', [
            'approvals' => $approvals,
            'period'    => $approvals->first()->period_ym,
            'msg'       => 'อนุมัติแคมเปญเรียบร้อยแล้ว ' . $approved->count() . ' รายการ (MD)',
        ]);
    }

    // MD ส่งกลับให้ผู้ขอแก้ไข (พร้อมเหตุผล) → แจ้งผู้ขอทางอีเมล
    public function reject(Request $request, $token)
    {
        ScopeBypass::$brand = true; // ผู้อนุมัติอาจล็อกอินคนละ brand → ปิด BrandScope ทั้ง request

        $approvals = CampaignApproval::withoutGlobalScopes()
            ->with(['campaign.model', 'campaign.subModel', 'campaign.appellation', 'campaign.type', 'requester'])
            ->where('approval_token', $token)
            ->get();

        abort_if($approvals->isEmpty(), 404);

        $note = trim((string) $request->input('note')) ?: null;

        $sentBack = collect();
        foreach ($approvals->where('status', 'pending') as $ap) {
            $ap->update(['status' => 'rejected', 'note' => $note, 'approved_at' => now()]);
            $sentBack->push($ap);
        }

        // แจ้งผู้ขอทางอีเมลให้แก้ไขแล้วส่งใหม่ (เมลล้มเหลวไม่ให้ขวางผลการตัดสิน)
        $this->notifyRequesters($sentBack, $note);

        return view('campaign.approval.result', [
            'approvals' => $approvals,
            'period'    => $approvals->first()->period_ym,
            'msg'       => 'ส่งกลับให้ผู้ขอแก้ไขแล้ว ' . $sentBack->count() . ' รายการ',
        ]);
    }

    // ── helpers ──

    // ตัวกรองค้นหา (ใช้ร่วมกันระหว่างตาราง list กับโหมด select_all)
    private function applyCkSearch($query, string $search): void
    {
        if ($search === '') {
            return;
        }
        $query->where(function ($q) use ($search) {
            $q->whereHas('appellation', fn($q) => $q->where('name', 'like', "%{$search}%"))
                ->orWhereHas('model', fn($q) => $q->where('Name_TH', 'like', "%{$search}%"))
                ->orWhereHas('subModel', fn($q) => $q->where('name', 'like', "%{$search}%"))
                ->orWhere('startYear', 'like', "%{$search}%")
                ->orWhere('endYear', 'like', "%{$search}%");
        });
    }

    private function statusBadge(?CampaignApproval $ap): string
    {
        if (!$ap) {
            return "<span class='badge bg-secondary'>ยังไม่ขอ</span>";
        }

        $map = [
            'pending'  => ['warning', 'รออนุมัติ'],
            'approved' => ['success', 'อนุมัติแล้ว'],
            'rejected' => ['danger', 'ส่งกลับแก้ไข'],
        ];
        [$cls, $txt] = $map[$ap->status] ?? ['secondary', $ap->status];

        return "<span class='badge bg-{$cls}'>{$txt}</span>";
    }

    // แปลง input เดือนให้เป็น 'Y-m' เสมอ (default = เดือนปัจจุบัน)
    private function normalizePeriod($value): string
    {
        try {
            if ($value && preg_match('/^\d{4}-\d{2}$/', $value)) {
                return Carbon::createFromFormat('Y-m', $value)->format('Y-m');
            }
        } catch (\Exception $e) {
            // ignore
        }
        return now()->format('Y-m');
    }

    // ช่วงวันที่ต้น-ปลายเดือนของ period
    private function periodRange(string $period): array
    {
        $d = Carbon::createFromFormat('Y-m', $period)->startOfMonth();
        return [$d->copy()->startOfMonth()->toDateString(), $d->copy()->endOfMonth()->toDateString()];
    }

    // อีเมล MD ตาม brand (alias brand 3 → 1 ผ่าน config/approval.php)
    private function mdEmails($brand): array
    {
        $alias = config("approval.$brand");
        $resolved = is_int($alias) ? $alias : (int) $brand;
        $emails = array_values(array_filter((array) (config("approval.$resolved.md") ?? [])));

        if (empty($emails)) {
            $emails = (int) $brand === 2 ? ['danut@chookiat.org'] : ['ketsudap@chookiat.org'];
        }
        return $emails;
    }

    // ส่งเมลชุดเดียวรวมหลายแคมเปญ (ลิงก์ token เดียว)
    private function sendBatchMail(string $period, string $token, $brand): void
    {
        $to = $this->mdEmails($brand);
        if (empty($to)) {
            return;
        }

        // ดึงใหม่เป็น Eloquent collection พร้อม eager load (ตอนขอสร้างเป็น base collection)
        $approvals = CampaignApproval::with(['campaign.model', 'campaign.subModel', 'campaign.appellation', 'campaign.type'])
            ->where('approval_token', $token)
            ->get();

        if ($approvals->isEmpty()) {
            return;
        }

        Mail::to($to)->send(new CampaignApprovalMail($approvals, $period, $token));
    }

    // แจ้งผู้ขอ (แยกตามคนขอ) ว่าแคมเปญถูกส่งกลับให้แก้ไข
    private function notifyRequesters($approvals, ?string $reason): void
    {
        if ($approvals->isEmpty()) {
            return;
        }

        foreach ($approvals->groupBy('requested_by') as $items) {
            $email = optional($items->first()->requester)->email;
            if (!$email) {
                continue;
            }
            try {
                Mail::to($email)->send(new CampaignRevisionMail($items->values(), $items->first()->period_ym, $reason));
            } catch (\Exception $e) {
                Log::error('CK revision mail failed: ' . $e->getMessage());
            }
        }
    }

    // แจ้งผู้ขอ (แยกตามคนขอ) ว่าแคมเปญได้รับการอนุมัติแล้ว
    private function notifyApproved($approvals): void
    {
        if ($approvals->isEmpty()) {
            return;
        }

        foreach ($approvals->groupBy('requested_by') as $items) {
            $email = optional($items->first()->requester)->email;
            if (!$email) {
                continue;
            }
            try {
                Mail::to($email)->send(new CampaignApprovedMail($items->values(), $items->first()->period_ym));
            } catch (\Exception $e) {
                Log::error('CK approved mail failed: ' . $e->getMessage());
            }
        }
    }
}
