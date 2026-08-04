<?php

namespace App\Http\Controllers\campaign;

use App\Http\Controllers\Controller;
use App\Traits\ConvertsThaiDate;
use App\Models\Salecampaign;
use App\Models\CampaignClaim;
use App\Models\TbCampaignClaimStatus;
use App\Exports\campaign\CampaignClaimExport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Support\ExportFilename;

class CampaignClaimController extends Controller
{
    use ConvertsThaiDate;

    // เฉพาะแคมเปญ On-Top (tb_campaign_type id 10-12, 23-25)
    private const ONTOP_TYPE_IDS = [10, 11, 12, 23, 24, 25];

    public function index()
    {
        $status = TbCampaignClaimStatus::orderBy('id')->get();
        return view('campaign.claim.view', compact('status'));
    }

    public function listClaim(Request $request)
    {
        $draw   = (int) ($request->draw ?? 1);
        $start  = (int) ($request->start ?? 0);
        $length = (int) ($request->length ?? 10);
        $search = trim($request->input('search.value', ''));
        $statusFilter = $request->input('status_filter', '');

        $base = Salecampaign::query()
            ->whereIn('CampaignType', self::ONTOP_TYPE_IDS)
            // เฉพาะรถที่ส่งมอบแล้ว (con_status = 5)
            ->whereHas('saleCar', fn($q) => $q->where('con_status', 5));

        // ฟิลเตอร์เดือนส่งมอบ (YYYY-MM) — เว้นว่าง = ทุกเดือน
        // คนละแกนกับสถานะ จึงเป็นเงื่อนไข AND เพิ่มอีกชั้น
        //
        // แถวที่ con_status = 5 แต่ DeliveryDate เป็น NULL แสดงเสมอไม่ว่าเลือกเดือนไหน
        // (ข้อมูลหลุดมาจากการย้ายระบบ ~17% ของใบที่ส่งมอบ) ถ้ากรองออกจะไม่มีใครเห็นและไม่มีใครแก้
        $month = (string) $request->input('delivery_month', '');
        if (preg_match('/^\d{4}-\d{2}$/', $month)) {
            $monthStart = Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfMonth();
            $monthEnd   = $monthStart->copy()->endOfMonth();
            $base->whereHas('saleCar', fn($q) => $q->where(
                fn($w) => $w
                    ->whereBetween('DeliveryDate', [$monthStart->toDateString(), $monthEnd->toDateString()])
                    ->orWhereNull('DeliveryDate')
            ));
        }

        // ฟิลเตอร์สถานะ (สรุปผลการตรวจสอบ)
        //  ''    = ยังไม่ตรวจสอบ (ค่าเริ่มต้น) → เฉพาะรายการที่ยังไม่มีสถานะ
        //  'all' = ทั้งหมด → ไม่กรองเลย รวม "รับเงินเรียบร้อย" ที่ไม่มีในตัวเลือกรายสถานะ
        //  ตัวเลข = กรองตามสถานะนั้น
        if ($statusFilter === 'all') {
            // ฟิลเตอร์คอลัมน์ "สรุปผลการตรวจสอบ" (เลือกได้หลายสถานะ) — ใช้ได้เฉพาะตอน "ทั้งหมด"
            // 'none' = รายการที่ยังไม่มี status_id
            $statusIds = json_decode((string) $request->input('status_ids', ''), true);
            if (is_array($statusIds) && count($statusIds)) {
                $wantNone = in_array('none', $statusIds, true);
                $ids = array_values(array_filter($statusIds, fn($v) => $v !== 'none'));

                $base->where(function ($q) use ($ids, $wantNone) {
                    if ($ids) {
                        $q->whereHas('claim', fn($c) => $c->whereIn('status_id', $ids));
                    }
                    if ($wantNone) {
                        // orWhere... ตัวแรกของ closure ทำงานเหมือน where ปกติ จึงใช้ได้แม้ $ids ว่าง
                        $q->orWhereDoesntHave('claim', fn($c) => $c->whereNotNull('status_id'));
                    }
                });
            }
        } elseif ($statusFilter !== '' && $statusFilter !== null) {
            $base->whereHas('claim', fn($q) => $q->where('status_id', $statusFilter));
        } else {
            $base->whereDoesntHave('claim', fn($q) => $q->whereNotNull('status_id'));
        }

        $recordsTotal = (clone $base)->count();

        if ($search) {
            $base->where(function ($q) use ($search) {
                $q->whereHas('campaignType', fn($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('saleCar.customer', function ($q) use ($search) {
                        $q->where('FirstName', 'like', "%{$search}%")
                            ->orWhere('LastName', 'like', "%{$search}%");
                    })
                    ->orWhereHas('saleCar.saleUser', fn($q) => $q->where('name', 'like', "%{$search}%"))
                    // ->orWhereHas('saleCar.model', fn($q) => $q->where('Name_TH', 'like', "%{$search}%"));
                    ->orWhereHas('saleCar.carOrder', fn($q) => $q->where('vin_number', 'like', "%{$search}%"));
            });
        }

        $recordsFiltered = (clone $base)->count();

        // จำนวนแถวที่ยังไม่ได้กรอกวันส่งมอบ (ในชุดที่กำลังแสดง) — เอาไปขึ้นป้ายเตือนบนแถบฟิลเตอร์
        $nullDeliveryCount = (clone $base)
            ->whereHas('saleCar', fn($q) => $q->whereNull('DeliveryDate'))
            ->count();

        $rows = $base
            ->with([
                'campaignType',
                'saleCar.customer',
                'saleCar.saleUser',
                'saleCar.model',
                'saleCar.carOrder',
                'claim.status',
            ])
            ->orderByDesc('id')
            ->skip($start)
            ->take($length)
            ->get();

        $rowNum = $start + 1;
        $data = $rows->map(function ($sc) use (&$rowNum) {
            $no = $rowNum++;

            $cus = $sc->saleCar?->customer;
            $customer = $cus ? trim(($cus->FirstName ?? '') . ' ' . ($cus->LastName ?? '')) : '-';
            $customer = $customer !== '' ? $customer : '-';

            $saleName = $sc->saleCar?->saleUser?->name ?? '-';
            $model = $sc->saleCar?->model?->Name_TH ?? '-';
            $vinNumber = $sc->saleCar?->carOrder?->vin_number ?? '-';
            $typeName = $sc->campaignType?->name ?? '-';

            $used = (float) ($sc->CashSupportFinal ?? 0);
            $claim = $sc->claim;
            $claimAmount = $claim && $claim->claim_amount !== null ? (float) $claim->claim_amount : null;
            $diff = $claimAmount !== null ? $used - $claimAmount : null;

            $statusName = $claim?->status?->name;
            $statusBadge = $statusName
                ? '<span class="badge bg-label-primary">' . e($statusName) . '</span>'
                : '<span class="badge bg-label-secondary">-</span>';

            return [
                'No' => $no,
                'customer' => $customer,
                'saleName' => $saleName,
                // 'model' => $model,
                'vin_number' => $vinNumber,
                'campaignType' => $typeName,
                // ส่งมอบแล้วแต่ไม่มีวันที่ = ข้อมูลไม่ครบ ต้องเห็นชัดว่าเป็นแถวที่ต้องตามแก้
                'delivery_date' => $sc->saleCar?->DeliveryDate
                    ? ($sc->saleCar->format_delivery_date ?? '-')
                    : '<span class="badge bg-label-warning" title="ใบจองสถานะส่งมอบแล้ว แต่ยังไม่ได้กรอกวันที่ส่งมอบ">'
                        . '<i class="bx bx-error-circle me-1"></i>ไม่มีวันส่งมอบ</span>',
                'used' => number_format($used, 2),
                'claim_amount' => $claimAmount !== null ? number_format($claimAmount, 2) : '-',
                'diff' => $diff !== null ? number_format($diff, 2) : '-',
                'received_date' => $claim?->format_received_date ?? '-',
                'status' => $statusBadge,
                'note' => $claim?->note ? e($claim->note) : '-',
                'Action' => view('campaign.claim.button', ['sc' => $sc])->render(),
            ];
        });

        return response()->json([
            'draw'              => $draw,
            'recordsTotal'      => $recordsTotal,
            'recordsFiltered'   => $recordsFiltered,
            'nullDeliveryCount' => $nullDeliveryCount,
            'data'              => $data->values(),
        ]);
    }

    public function exportReport(Request $request)
    {
        $fromDate = $this->toGregorian($request->from_date);
        $toDate   = $this->toGregorian($request->to_date);

        return Excel::download(
            new CampaignClaimExport($fromDate, $toDate),
            ExportFilename::withBrand('campaign-claim-report.xlsx')
        );
    }

    public function editClaim($id)
    {
        $sc = Salecampaign::with(['campaignType', 'saleCar.customer', 'saleCar.carOrder', 'claim'])
            ->whereIn('CampaignType', self::ONTOP_TYPE_IDS)
            ->findOrFail($id);

        $status = TbCampaignClaimStatus::orderBy('id')->get();

        return view('campaign.claim.edit', compact('sc', 'status'));
    }

    public function updateClaim(Request $request, $id)
    {
        try {
            $sc = Salecampaign::whereIn('CampaignType', self::ONTOP_TYPE_IDS)->findOrFail($id);

            $claimAmount = $request->filled('claim_amount')
                ? str_replace(',', '', $request->claim_amount)
                : null;

            CampaignClaim::updateOrCreate(
                ['salecampaign_id' => $sc->id],
                [
                    'claim_amount'  => $claimAmount,
                    'received_date' => $this->toGregorian($request->received_date),
                    'status_id'     => $request->status_id ?: null,
                    'note'          => $request->note,
                    'userZone'      => Auth::user()->userZone ?? null,
                    'brand'         => Auth::user()->brand ?? null,
                    'branch'        => Auth::user()->branch ?? null,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'บันทึกข้อมูลเรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'
            ], 500);
        }
    }
}
