<?php

namespace App\Http\Controllers\vehicle;

use App\Exports\vehicle\VehicleExport;
use App\Exports\vehicle\VehicleLicensePlateExport;
use App\Http\Controllers\Controller;
use App\Models\Salecar;
use App\Models\TbProvinces;
use App\Models\VehicleLicense;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class VehicleController extends Controller
{
    public function index()
    {
        return view('number_register.vehicle.view');
    }

    public function listVehicle(Request $request)
    {
        $status = $request->status ?? 'unWithdrawal';

        $query = Salecar::with([
            'carOrder',
            'provinces',
            'vehicleLicense',
            'financeConfirm'
        ])
            ->whereNotNull('CarOrderID')
            // ->whereNotNull('DeliveryDate')
            ->where('con_status', 5);

        // ซ่อนรถที่ส่งมอบก่อนวัน go-live ของเมนูนี้ (ตั้งค่าใน config/vehicle.php → .env)
        // รถที่ยังไม่มีวันส่งมอบ (DeliveryDate = NULL) ยังคงแสดงอยู่
        $registrationStartDate = config('vehicle.registration_start_date');
        if (!empty($registrationStartDate)) {
            $query->where(function ($q) use ($registrationStartDate) {
                $q->whereNull('DeliveryDate')
                    ->orWhereDate('DeliveryDate', '>=', $registrationStartDate);
            });
        }
            // ->where(function ($q) {
            //     $q->where('payment_mode', 'non-finance')
            //         ->orWhere(function ($q2) {
            //             $q2->where('payment_mode', 'finance')
            //                 ->whereHas('financeConfirm', function ($q3) {
            //                     $q3->whereNotNull('firm_date');
            //                 });
            //         });
            // });

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

            return [
                'No' => $index + 1,
                'FullName' => implode(' ', array_filter([
                    $prefix ?? null,
                    $first ?? null,
                    $last ?? null,
                ])),
                'vin' => $vin,
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
                'withdrawal_check', 'withdrawal_channel', 'withdrawal_bill', 'withdrawal_total',
                'receipt_check', 'receipt_channel', 'receipt_bill', 'receipt_total',
            ];
            foreach ($moneyFields as $f) {
                if (array_key_exists($f, $data)) {
                    $data[$f] = ($data[$f] !== null && $data[$f] !== '')
                        ? str_replace(',', '', $data[$f])
                        : null;
                }
            }

            // คิดยอดรวมใหม่จาก breakdown ให้ตรงกับ PDF เสมอ (ตรวจ + ช่อง + ใบเสร็จ)
            if (array_key_exists('withdrawal_check', $data) || array_key_exists('withdrawal_channel', $data) || array_key_exists('withdrawal_bill', $data)) {
                $data['withdrawal_total'] = (float) ($data['withdrawal_check'] ?? 0)
                    + (float) ($data['withdrawal_channel'] ?? 0)
                    + (float) ($data['withdrawal_bill'] ?? 0);
            }
            if (array_key_exists('receipt_check', $data) || array_key_exists('receipt_channel', $data) || array_key_exists('receipt_bill', $data)) {
                $data['receipt_total'] = (float) ($data['receipt_check'] ?? 0)
                    + (float) ($data['receipt_channel'] ?? 0)
                    + (float) ($data['receipt_bill'] ?? 0);
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
        $withdrawalData = Salecar::with(['carOrder', 'vehicleLicense', 'customer'])
            ->whereNotNull('CarOrderID')
            ->where('con_status', 5)
            // ->whereNotNull('DeliveryDate')
            // ->where(function ($q) {
            //     $q->where('payment_mode', 'non-finance')
            //         ->orWhere(function ($q2) {
            //             $q2->where('payment_mode', 'finance')
            //                 ->whereHas('financeConfirm', function ($q3) {
            //                     $q3->whereNotNull('firm_date');
            //                 });
            //         });
            // })
            ->where(function ($q) {
                $q->doesntHave('vehicleLicense')
                    ->orWhereHas('vehicleLicense', function ($qq) {
                        $qq->whereNull('withdrawal_date');
                    });
            })->get();

        $clearData = Salecar::with(['carOrder', 'vehicleLicense', 'customer'])
            ->whereNotNull('CarOrderID')
            ->where('con_status', 5)
            // ->whereNotNull('DeliveryDate')
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
                    'withdrawal_total' => $item['total'] ? str_replace(',', '', $item['total']) : null,
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

        $pdf = Pdf::loadView('number_register.vehicle.pdf-withdrawal', compact('data'))
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

            $receiptTotal = isset($item['total'])
                ? str_replace(',', '', $item['total'])
                : null;

            $withdrawalTotal = $vehicle->withdrawal_total ?? 0;

            $diff = ($withdrawalTotal ?? 0) - ($receiptTotal ?? 0);
            // $diff = abs(($withdrawalTotal ?? 0) - ($receiptTotal ?? 0));

            $vehicle->update([
                'backup_clear_date' => $now,
                'clear_batch'     => $batch,
                'receipt_check'   => str_replace(',', '', $item['check']) ?? null,
                'receipt_channel' => str_replace(',', '', $item['channel']) ?? null,
                'receipt_bill'    => str_replace(',', '', $item['receipt']) ?? null,
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

        $pdf = Pdf::loadView('number_register.vehicle.pdf-receipt', compact('data'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream('clear.pdf');
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

        return Excel::download(new VehicleExport($fromDate, $toDate), 'รายงานการส่งเบิก-เคลียร์.xlsx');
    }

    public function exportLicensePlate()
    {
        return Excel::download(new VehicleLicensePlateExport(), 'รายงานป้ายทะเบียน.xlsx');
    }
}
