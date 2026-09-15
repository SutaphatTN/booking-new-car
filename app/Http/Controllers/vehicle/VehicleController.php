<?php

namespace App\Http\Controllers\vehicle;

use App\Exports\vehicle\VehicleExport;
use App\Exports\vehicle\VehicleLicensePlateExport;
use App\Http\Controllers\Controller;
use App\Models\Salecar;
use App\Models\TbBranch;
use App\Models\TbProvinces;
use App\Models\VehicleLicense;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Support\ExportFilename;

class VehicleController extends Controller
{
    public function index()
    {
        return view('number_register.vehicle.view');
    }

    /**
     * วันตัด go-live ของเมนูป้ายทะเบียน — ซ่อนรถที่ส่งมอบก่อนวันนั้นออกจากหน้ารายการ
     * (ข้อมูลยังอยู่ใน DB ครบ แค่ไม่เอามารกหน้าจอ)
     *
     * ใบเก่าชุดที่ import ตอนเปิดระบบ (ก.พ. 2026) หลายใบสถานะเป็น "ส่งมอบ" แต่ไม่มี DeliveryDate
     * ถ้าเทียบเฉพาะ DeliveryDate จะกรองไม่ออก จึงไล่หาวันสำรองตามลำดับ :
     *   DeliveryDate → DeliveryInDMSDate → DeliveryInCKDate → BookingDate
     * ใบที่ไม่มีวันไหนเลยสักช่อง = ใบใหม่ที่ยังกรอกไม่ครบ ปล่อยให้เห็นไว้ ไม่ซ่อน
     */
    private function applyRegistrationCutoff($query): void
    {
        $startDate = config('vehicle.registration_start_date');

        if (empty($startDate)) {
            return;
        }

        $effectiveDate = 'COALESCE(DeliveryDate, DeliveryInDMSDate, DeliveryInCKDate, BookingDate)';

        $query->where(function ($q) use ($effectiveDate, $startDate) {
            $q->whereRaw("{$effectiveDate} >= ?", [$startDate])
                ->orWhereRaw("{$effectiveDate} IS NULL");
        });
    }

    public function listVehicle(Request $request)
    {
        $status = $request->status ?? 'unWithdrawal';

        $query = Salecar::with([
            // customer.prefix ต้อง eager load ด้วย — ตารางเอาชื่อลูกค้ามาโชว์ทุกแถว
            'customer.prefix',
            'carOrder',
            'provinces',
            'vehicleLicense',
            'licensePlateRed',
            'financeConfirm'
        ])
            ->whereNotNull('CarOrderID')
            ->where('con_status', 5);

        // ซ่อนรถที่ส่งมอบก่อนวัน go-live ของเมนูนี้ (ตั้งค่าใน config/vehicle.php → .env)
        $this->applyRegistrationCutoff($query);

        if ($status === 'unWithdrawal') {
            $query->where(function ($q) {
                $q->doesntHave('vehicleLicense')
                    ->orWhereHas('vehicleLicense', function ($qq) {
                        $qq->whereNull('withdrawal_date');
                    });
            });
        }

        if ($status === 'withdrawal') {
            $query->whereHas('vehicleLicense', function ($q) {
                $q->whereNotNull('withdrawal_date')
                    ->whereNull('backup_clear_date');
            });
        }

        if ($status === 'cleared') {
            $query->whereHas('vehicleLicense', function ($q) {
                $q->whereNotNull('withdrawal_date')
                    ->whereNotNull('backup_clear_date');
            });
        }

        if ($status === 'all') {
            $query->orderByDesc('id');
        }

        $saleCar = $query->get();

        $data = $saleCar->map(function ($s, $index) {
            $prefix = $s->customer?->prefix?->Name_TH;
            $first  = $s->customer?->FirstName;
            $last   = $s->customer?->LastName;

            $vin_num = $s->carOrder?->vin_number ?? '-';
            $eng_num = $s->carOrder?->engine_number ?? '-';
            $vin = "Vin : {$vin_num}<br>Engine : {$eng_num}";

            // คอลัมน์ "ป้าย" — ป้ายแดงบรรทัดบน ป้ายขาวบรรทัดล่าง ใช้ไอคอนแทนคำว่าป้ายแดง/ป้ายขาว
            // ชี้ที่บรรทัดไหนก็ได้ (title ครอบทั้งบรรทัด ไม่ใช่แค่ตัวไอคอน) จะได้รู้ว่าอันไหนคืออะไร
            $redPlate = $s->licensePlateRed?->number;
            $whitePlate = trim(($s->vehicleLicense?->license_name ?? '') . ' ' . ($s->vehicleLicense?->license_number ?? ''));

            $plateLine = fn(string $icon, string $color, string $label, ?string $value) =>
                '<div class="d-flex align-items-center gap-1" style="font-size:.8rem;" title="' . $label . '">'
                . '<i class="bx ' . $icon . '" style="color:' . $color . ';"></i>'
                . '<span class="' . ($value ? '' : 'text-muted') . '">' . ($value ? e($value) : '-') . '</span>'
                . '</div>';

            $plates = $plateLine('bx-purchase-tag', '#ef4444', 'ป้ายแดง', $redPlate)
                . $plateLine('bx-id-card', '#334155', 'ป้ายขาว', $whitePlate ?: null);

            return [
                'No' => $index + 1,
                'FullName' => implode(' ', array_filter([
                    $prefix ?? null,
                    $first ?? null,
                    $last ?? null,
                ])),
                'vin' => $vin,
                'plates' => $plates,
                'province' => $s->provinces?->name,
                'withdrawn_cost' => $s->vehicleLicense?->withdrawal_total !== null ? number_format($s->vehicleLicense?->withdrawal_total, 2) : '-',
                'receipt_total' => $s->vehicleLicense?->receipt_total !== null ? number_format($s->vehicleLicense?->receipt_total, 2) : '-',
                // 'withdrawn_cost' => view('number_register.vehicle.input-withdrawn', [
                //     'vl' => $s->vehicleLicense,
                //     'SaleID' => $s->id
                // ])->render(),

                // 'receipt_total' => view('number_register.vehicle.input-receipt', [
                //     'vl' => $s->vehicleLicense,
                //     'SaleID' => $s->id
                // ])->render(),
                'Action' => view('number_register.vehicle.button', compact('s'))->render()
            ];
        });

        return response()->json(['data' => $data]);
    }

    // public function updateVehicle(Request $request)
    // {
    //     $userZone = Auth::user()->userZone ?? null;
    //     $brand = Auth::user()->brand ?? null;

    //     $vl = VehicleLicense::firstOrCreate(
    //         [
    //             'SaleID' => $request->SaleID,
    //             'userZone' => $userZone,
    //             'brand' => $brand,
    //         ],
    //         [
    //             'userZone' => $userZone,
    //             'brand' => $brand,
    //         ]
    //     );

    //     if ($request->has('withdrawal_total')) {
    //         $vl->withdrawal_total = $request->filled('withdrawal_total')
    //             ? str_replace(',', '', $request->withdrawal_total)
    //             : null;
    //     }

    //     if ($request->has('receipt_total')) {
    //         $vl->receipt_total = $request->filled('receipt_total')
    //             ? str_replace(',', '', $request->receipt_total)
    //             : null;
    //     }

    //     $vl->save();

    //     return response()->json(['success' => true]);
    // }

    public function viewMore($id)
    {
        $veh = Salecar::with([
            'carOrder',
            'provinces',
            'vehicleLicense',
            'vehicleLicense.provincesV',
            'licensePlateRed',
            'financeConfirm',
            'accessories'
        ])->findOrFail($id);

        return view('number_register.vehicle.view-more', compact('veh'));
    }

    public function edit($id)
    {
        $veh = Salecar::with([
            'carOrder',
            'provinces',
            'vehicleLicense',
            'vehicleLicense.provincesV',
            'licensePlateRed',
            'financeConfirm',
            'accessories'
        ])->findOrFail($id);

        $provincesV = TbProvinces::all();

        return view('number_register.vehicle.edit', compact('veh', 'provincesV'));
    }

    public function update(Request $request, $id)
    {
        try {
            $userZone = Auth::user()->userZone ?? null;
            $brand = Auth::user()->brand ?? null;
            $branch = Auth::user()->branch ?? null;

            $data = $request->except(['_token', '_method']);

            // ล้าง comma ช่องเงินทั้งหมด (breakdown + ยอดรวม)
            $moneyFields = [
                'withdrawal_check', 'withdrawal_channel', 'withdrawal_bill', 'withdrawal_other', 'withdrawal_total',
                'receipt_check', 'receipt_channel', 'receipt_bill', 'receipt_other', 'receipt_total',
            ];
            foreach ($moneyFields as $f) {
                if (array_key_exists($f, $data)) {
                    $data[$f] = ($data[$f] !== null && $data[$f] !== '')
                        ? str_replace(',', '', $data[$f])
                        : null;
                }
            }

            // "อื่นๆ" มียอดต้องมีหมายเหตุ — กติกาเดียวกับตอนส่งเบิก/ส่งเคลียร์
            foreach ([['withdrawal_other', 'withdrawal_other_note'], ['receipt_other', 'receipt_other_note']] as [$amountKey, $noteKey]) {
                if ((float) ($data[$amountKey] ?? 0) > 0 && trim((string) ($data[$noteKey] ?? '')) === '') {
                    return response()->json([
                        'success' => false,
                        'message' => 'มียอด "อื่นๆ" แต่ยังไม่ได้ระบุหมายเหตุ',
                    ], 422);
                }
            }

            // คิดยอดรวมใหม่จาก breakdown ให้ตรงกับ PDF เสมอ (ตรวจ + ช่อง + ใบเสร็จ + อื่นๆ)
            if (array_key_exists('withdrawal_check', $data) || array_key_exists('withdrawal_channel', $data) || array_key_exists('withdrawal_bill', $data) || array_key_exists('withdrawal_other', $data)) {
                $data['withdrawal_total'] = (float) ($data['withdrawal_check'] ?? 0)
                    + (float) ($data['withdrawal_channel'] ?? 0)
                    + (float) ($data['withdrawal_bill'] ?? 0)
                    + (float) ($data['withdrawal_other'] ?? 0);
            }
            if (array_key_exists('receipt_check', $data) || array_key_exists('receipt_channel', $data) || array_key_exists('receipt_bill', $data) || array_key_exists('receipt_other', $data)) {
                $data['receipt_total'] = (float) ($data['receipt_check'] ?? 0)
                    + (float) ($data['receipt_channel'] ?? 0)
                    + (float) ($data['receipt_bill'] ?? 0)
                    + (float) ($data['receipt_other'] ?? 0);
            }

            // key ด้วย SaleID อย่างเดียว (กันสร้างแถวซ้ำเวลาคนแก้อยู่คนละ zone/brand/branch)
            $vl = VehicleLicense::firstOrNew(['SaleID' => $id]);
            $vl->fill($data);

            // เติม scope เฉพาะแถวที่สร้างใหม่ — ไม่ทับ scope เดิมของแถวที่มีอยู่
            if (!$vl->exists) {
                $vl->userZone = $userZone;
                $vl->brand = $brand;
                $vl->branch = $branch;
            }

            // ส่วนต่าง = เบิก − เคลียร์ (ให้ตรงกับ confirmClear) — อัปเดตเฉพาะรายการที่เคลียร์แล้ว
            if ($vl->backup_clear_date) {
                $vl->diff = (float) ($vl->withdrawal_total ?? 0) - (float) ($vl->receipt_total ?? 0);
            }

            $vl->save();

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

    public function withdrawalPending()
    {
        // ฝั่ง "ส่งเบิก" — ใช้วันตัด go-live ชุดเดียวกับหน้ารายการ ไม่งั้นโมดัลจะมีของเก่าก่อนเปิดระบบปนมาเยอะ
        $withdrawalQuery = Salecar::with(['carOrder', 'vehicleLicense', 'customer'])
            ->whereNotNull('CarOrderID')
            ->where('con_status', 5)
            ->where(function ($q) {
                $q->doesntHave('vehicleLicense')
                    ->orWhereHas('vehicleLicense', function ($qq) {
                        $qq->whereNull('withdrawal_date');
                    });
            });

        $this->applyRegistrationCutoff($withdrawalQuery);

        $withdrawalData = $withdrawalQuery->get();

        // ฝั่ง "เคลียร์" — ไม่ใส่วันตัด : รายการที่นี่คือของที่ "ส่งเบิกไปแล้ว" ทั้งหมด
        // ถ้าซ่อนตามวันส่งมอบ ใบเก่าที่เพิ่งส่งเบิกหลังเปิดระบบจะเคลียร์ไม่ได้เลย (ค้างถาวร)
        $clearData = Salecar::with(['carOrder', 'vehicleLicense', 'customer'])
            ->whereNotNull('CarOrderID')
            ->where('con_status', 5)
            ->whereHas('vehicleLicense', function ($q) {
                $q->whereNotNull('withdrawal_date')
                    ->whereNull('backup_clear_date');
            })
            ->get();

        return view('number_register.vehicle.withdrawal', compact('withdrawalData', 'clearData'));
    }

    public function confirmWithdrawal(Request $request)
    {
        $userZone = Auth::user()->userZone ?? null;
        $brand = Auth::user()->brand ?? null;
        $branch = Auth::user()->branch ?? null;

        // เลขชุดเบิก 1 ค่า ประทับทุกรายการในการกดครั้งเดียว + เวลาเดียว (ใช้ re-export ทั้งชุด)
        $now = now();
        $batch = (int) (VehicleLicense::withoutGlobalScopes()->max('withdrawal_batch') ?? 0) + 1;

        foreach ($request->items as $item) {

            // "อื่นๆ" มียอดต้องมีหมายเหตุ — ดักซ้ำฝั่ง server (JS กันไว้ชั้นแรกแล้ว)
            $other = isset($item['other']) && $item['other'] !== ''
                ? (float) str_replace(',', '', $item['other'])
                : 0;
            $otherNote = trim($item['other_note'] ?? '');

            if ($other > 0 && $otherNote === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'มียอด "อื่นๆ" แต่ยังไม่ได้ระบุหมายเหตุ',
                ], 422);
            }

            // ยอดรวมคิดใหม่ฝั่ง server เสมอ (ตรวจ + ช่อง + ใบเสร็จ + อื่นๆ) ไม่เชื่อค่าที่ส่งมาจากหน้าจอ
            $total = (float) str_replace(',', '', $item['check'] ?? 0)
                + (float) str_replace(',', '', $item['channel'] ?? 0)
                + (float) str_replace(',', '', $item['receipt'] ?? 0)
                + $other;

            VehicleLicense::updateOrCreate(
                [
                    'SaleID' => $item['id'],
                ],
                [
                    'withdrawal_date' => $now,
                    'withdrawal_batch' => $batch,
                    'withdrawal_check' => $item['check'] ? str_replace(',', '', $item['check']) : null,
                    'withdrawal_channel' => $item['channel'] ? str_replace(',', '', $item['channel']) : null,
                    'withdrawal_bill' => $item['receipt'] ? str_replace(',', '', $item['receipt']) : null,
                    'withdrawal_other' => $other ?: null,
                    'withdrawal_other_note' => $otherNote ?: null,
                    'withdrawal_total' => $total,
                    'userZone' => $userZone,
                    'brand' => $brand,
                    'branch' => $branch,
                ]
            );
        }

        return response()->json(['success' => true]);
    }

    public function exportPdf(Request $request)
    {
        $query = VehicleLicense::with(['saleCar.customer', 'saleCar.carOrder', 'saleCar.provinces']);

        // re-export ทั้งชุด (batch) — ดึงตามข้อมูลปัจจุบันใน DB ; ถ้าไม่มี batch ใช้ ids (ตอนกดยืนยันครั้งแรก)
        if ($request->filled('batch')) {
            $query->where('withdrawal_batch', $request->batch);
        } else {
            $query->whereIn('SaleID', explode(',', $request->ids));
        }

        $data = $query->get();
        [$brandName, $branchName] = $this->pdfHeaderContext();

        $pdf = Pdf::loadView('number_register.vehicle.pdf-withdrawal', compact('data', 'brandName', 'branchName'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream('withdrawal.pdf');
    }

    //clear
    public function confirmClear(Request $request)
    {
        // เลขชุดเคลียร์ 1 ค่า ประทับทุกรายการในการกดครั้งเดียว + เวลาเดียว (ใช้ re-export ทั้งชุด)
        $now = now();
        $batch = (int) (VehicleLicense::withoutGlobalScopes()->max('clear_batch') ?? 0) + 1;

        foreach ($request->items as $item) {

            $vehicle = VehicleLicense::where('SaleID', $item['id'])->first();

            if (!$vehicle) continue;

            // "อื่นๆ" มียอดต้องมีหมายเหตุ — กติกาเดียวกับฝั่งส่งเบิก
            $other = isset($item['other']) && $item['other'] !== ''
                ? (float) str_replace(',', '', $item['other'])
                : 0;
            $otherNote = trim($item['other_note'] ?? '');

            if ($other > 0 && $otherNote === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'มียอด "อื่นๆ" แต่ยังไม่ได้ระบุหมายเหตุ',
                ], 422);
            }

            // ยอดรวมคิดใหม่ฝั่ง server เสมอ (ตรวจ + ช่อง + ใบเสร็จ + อื่นๆ)
            $receiptTotal = (float) str_replace(',', '', $item['check'] ?? 0)
                + (float) str_replace(',', '', $item['channel'] ?? 0)
                + (float) str_replace(',', '', $item['receipt'] ?? 0)
                + $other;

            $withdrawalTotal = $vehicle->withdrawal_total ?? 0;

            $diff = ($withdrawalTotal ?? 0) - ($receiptTotal ?? 0);

            $vehicle->update([
                'backup_clear_date' => $now,
                'clear_batch'     => $batch,
                'receipt_check'   => str_replace(',', '', $item['check']) ?? null,
                'receipt_channel' => str_replace(',', '', $item['channel']) ?? null,
                'receipt_bill'    => str_replace(',', '', $item['receipt']) ?? null,
                'receipt_other'   => $other ?: null,
                'receipt_other_note' => $otherNote ?: null,
                'receipt_total'   => $receiptTotal,
                'diff'            => $diff,
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function exportClearPdf(Request $request)
    {
        $query = VehicleLicense::with(['saleCar.customer', 'saleCar.carOrder', 'saleCar.provinces']);

        // re-export ทั้งชุด (batch) — ดึงตามข้อมูลปัจจุบันใน DB ; ถ้าไม่มี batch ใช้ ids (ตอนกดยืนยันครั้งแรก)
        if ($request->filled('batch')) {
            $query->where('clear_batch', $request->batch);
        } else {
            $query->whereIn('SaleID', explode(',', $request->ids));
        }

        $data = $query->get();
        [$brandName, $branchName] = $this->pdfHeaderContext();

        $pdf = Pdf::loadView('number_register.vehicle.pdf-receipt', compact('data', 'brandName', 'branchName'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream('clear.pdf');
    }

    /**
     * หัวเอกสาร PDF ส่งเบิก/เคลียร์ — [ชื่อแบรนด์, ชื่อสาขา]
     * ยึด brand/branch ที่ user กำลังทำงานอยู่ ไม่ใช่ค่าที่ประทับไว้บนแถว
     * เพราะแถวเก่าถูกเขียนทับ brand/branch ตอนแก้ข้อมูลทีหลังได้ (บาง batch เลยมีค่าปนกัน)
     * สาขา: เฉพาะ brand 2 ที่แยกสาขาจริง (BranchSwitcher ก็สลับสาขาได้เฉพาะ brand นี้)
     */
    private function pdfHeaderContext(): array
    {
        $user = Auth::user();
        $brand = (int) ($user->brand ?? 0);

        $brandName = config("brand.names.{$brand}") ?? '';
        $branchName = $brand === 2
            ? (TbBranch::find($user->branch)->name ?? '')
            : '';

        return [$brandName, $branchName];
    }

    // ประวัติส่งเบิก / เคลียร์ (รายชุด) — re-export PDF ทั้งชุดได้ (เฉพาะ admin, registration)
    public function history(Request $request)
    {
        if (!in_array(Auth::user()->role, ['admin', 'registration'])) {
            abort(403);
        }

        // กรองรายเดือน (default = เดือนปัจจุบัน) — DataTables แบ่งหน้า 10 รายการฝั่ง client
        $wMonth = $request->input('w_month') ?: now()->format('Y-m');
        $cMonth = $request->input('c_month') ?: now()->format('Y-m');
        [$wYear, $wMon] = array_pad(explode('-', $wMonth), 2, null);
        [$cYear, $cMon] = array_pad(explode('-', $cMonth), 2, null);

        $withdrawalBatches = VehicleLicense::query()
            ->whereNotNull('withdrawal_batch')
            ->when($wYear && $wMon, fn($q) => $q->whereYear('withdrawal_date', (int) $wYear)->whereMonth('withdrawal_date', (int) $wMon))
            ->selectRaw('withdrawal_batch, COUNT(*) as cnt, SUM(withdrawal_total) as total, MIN(withdrawal_date) as batch_date')
            ->groupBy('withdrawal_batch')
            ->orderByDesc('withdrawal_batch')
            ->get();

        $clearBatches = VehicleLicense::query()
            ->whereNotNull('clear_batch')
            ->when($cYear && $cMon, fn($q) => $q->whereYear('backup_clear_date', (int) $cYear)->whereMonth('backup_clear_date', (int) $cMon))
            ->selectRaw('clear_batch, COUNT(*) as cnt, SUM(receipt_total) as total, MIN(backup_clear_date) as batch_date')
            ->groupBy('clear_batch')
            ->orderByDesc('clear_batch')
            ->get();

        return view('number_register.vehicle.history', compact('withdrawalBatches', 'clearBatches', 'wMonth', 'cMonth'));
    }

    public function viewExportVehicle()
    {
        return view('number_register.vehicle.report.view');
    }

    public function exportVehicle(Request $request)
    {
        $fromDate = $request->from_date ?? now()->startOfMonth()->format('Y-m-d');
        $toDate   = $request->to_date   ?? now()->format('Y-m-d');

        return Excel::download(new VehicleExport($fromDate, $toDate), ExportFilename::withBrand('รายงานการส่งเบิก-เคลียร์.xlsx'));
    }

    public function exportLicensePlate()
    {
        return Excel::download(new VehicleLicensePlateExport(), ExportFilename::withBrand('รายงานป้ายทะเบียน.xlsx'));
    }
}
