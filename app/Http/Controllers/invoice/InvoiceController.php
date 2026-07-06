<?php

namespace App\Http\Controllers\invoice;

use App\Exports\invoice\InvoiceExport;
use App\Http\Controllers\Controller;
use App\Models\AccessoryPartner;
use App\Models\InvoiceAccessory;
use App\Models\InvoiceCustomer;
use App\Models\TbBrand;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Traits\ConvertsThaiDate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class InvoiceController extends Controller
{
    use ConvertsThaiDate;

    public function index()
    {
        return view('invoice.view');
    }

    public function create()
    {
        $partners = AccessoryPartner::orderBy('name')->get();
        $today = now()->format('Y-m-d');
        $user = Auth::user();
        $approvers = User::whereIn('role', ['audit', 'audit_lead', 'gm', 'manager', 'md', 'bp', 'cs'])
            ->where(function ($q) use ($user) {
                $q->where('brand', $user->brand == 2 ? 2 : 1)
                  ->orWhereIn('id', [3, 45]);
            })
            ->where('branch', $user->branch)
            ->where('userZone', $user->userZone)
            ->orderBy('name')
            ->get();

        return view('invoice.input', compact('partners', 'today', 'approvers'));
    }

    public function store(Request $request)
    {
        try {
            $codeNumber = $this->generateCodeNumber();
            $user = Auth::user();

            $invoice = InvoiceCustomer::create([
                'customer_name'  => $request->customer_name,
                'customer_phone' => preg_replace('/\D/', '', $request->customer_phone),
                'date'           => $request->date,
                'license_plate'  => $request->license_plate,
                'engine_number'  => $request->engine_number,
                'vin_number'     => $request->vin_number,
                'code_number'    => $codeNumber,
                'request_date'   => now()->format('Y-m-d'),
                'Approved'       => $request->approved_by ?: null,
                'UserInsert'     => $user->id,
                'brand'          => $user->brand,
                'userZone'       => $user->userZone,
                'branch'         => $user->branch,
            ]);

            $totalPrice = 0;
            if ($request->has('accessories')) {
                foreach ($request->accessories as $item) {
                    if (empty($item['acc_partner']) && empty($item['detail'])) continue;

                    $salePrice = $item['sale_price'] ? (float) str_replace(',', '', $item['sale_price']) : null;
                    $totalPrice += $salePrice ?? 0;

                    InvoiceAccessory::create([
                        'inv_cust_id' => $invoice->id,
                        'acc_partner' => $item['acc_partner'] ?? null,
                        'detail'      => $item['detail'] ?? null,
                        'cost_price'  => $item['cost_price'] ? str_replace(',', '', $item['cost_price']) : null,
                        'sale_price'  => $salePrice,
                        'brand'       => $user->brand,
                        'userZone'    => $user->userZone,
                        'branch'      => $user->branch,
                    ]);
                }
            }
            $invoice->update(['total_price' => $totalPrice]);

            return response()->json([
                'success' => true,
                'message' => 'เพิ่มข้อมูลเรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'
            ], 500);
        }
    }

    public function edit($id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $invoice  = InvoiceCustomer::with('accessories')->findOrFail($id);
        $partners = AccessoryPartner::orderBy('name')->get();

        // ผู้อนุมัติอิงตาม brand/branch/zone ของ invoice เพื่อให้ค่าเดิมอยู่ในลิสต์
        $approvers = User::whereIn('role', ['audit', 'audit_lead', 'gm', 'manager', 'md', 'bp', 'cs'])
            ->where(function ($q) use ($invoice) {
                $q->where('brand', $invoice->brand == 2 ? 2 : 1)
                  ->orWhereIn('id', [3, 45]);
            })
            ->where('branch', $invoice->branch)
            ->where('userZone', $invoice->userZone)
            ->orderBy('name')
            ->get();

        return view('invoice.edit', compact('invoice', 'partners', 'approvers'));
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        try {
            $invoice = InvoiceCustomer::findOrFail($id);

            $invoice->update([
                'customer_name'  => $request->customer_name,
                'customer_phone' => preg_replace('/\D/', '', $request->customer_phone),
                'date'           => $request->date,
                'license_plate'  => $request->license_plate,
                'engine_number'  => $request->engine_number,
                'vin_number'     => $request->vin_number,
                'Approved'       => $request->approved_by ?: null,
            ]);

            $totalPrice = 0;
            $keepIds    = [];

            if ($request->has('accessories')) {
                foreach ($request->accessories as $item) {
                    if (empty($item['acc_partner']) && empty($item['detail'])) continue;

                    $salePrice = $item['sale_price'] ? (float) str_replace(',', '', $item['sale_price']) : null;
                    $totalPrice += $salePrice ?? 0;

                    $payload = [
                        'acc_partner' => $item['acc_partner'] ?? null,
                        'detail'      => $item['detail'] ?? null,
                        'cost_price'  => $item['cost_price'] ? str_replace(',', '', $item['cost_price']) : null,
                        'sale_price'  => $salePrice,
                    ];

                    // แถวเดิม → update ตาม id
                    if (!empty($item['id'])) {
                        $acc = InvoiceAccessory::where('inv_cust_id', $invoice->id)->find($item['id']);
                        if ($acc) {
                            $acc->update($payload);
                            $keepIds[] = $acc->id;
                            continue;
                        }
                    }

                    // แถวใหม่ → create
                    $new = InvoiceAccessory::create($payload + [
                        'inv_cust_id' => $invoice->id,
                        'brand'       => $invoice->brand,
                        'userZone'    => $invoice->userZone,
                        'branch'      => $invoice->branch,
                    ]);
                    $keepIds[] = $new->id;
                }
            }

            // แถวที่ถูกเอาออกจากฟอร์ม → soft delete
            InvoiceAccessory::where('inv_cust_id', $invoice->id)
                ->whereNotIn('id', $keepIds)
                ->delete();

            $invoice->update(['total_price' => $totalPrice]);

            return response()->json([
                'success' => true,
                'message' => 'แก้ไขข้อมูลเรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'
            ], 500);
        }
    }

    public function destroy($id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $invoice = InvoiceCustomer::findOrFail($id);
        $invoice->accessories()->delete(); // soft delete invoice_accessory ทั้งหมด
        $invoice->delete();                // soft delete invoice_customer

        return response()->json([
            'success' => true,
            'message' => 'ลบข้อมูลเรียบร้อยแล้ว'
        ]);
    }

    public function list(Request $request)
    {
        $user = Auth::user();
        $canApprove = in_array($user->role, ['admin', 'audit', 'audit_lead', 'gm', 'manager', 'md', 'bp', 'cs']);
        $canConfirmReceipt = in_array($user->role, ['admin', 'account']);
        $canManage = $user->role === 'admin';

        $draw   = (int) ($request->draw ?? 1);
        $start  = (int) ($request->start ?? 0);
        $length = (int) ($request->length ?? 10);
        $search = trim($request->input('search.value', ''));
        $filter = $request->query('filter', 'pending');

        $base = InvoiceCustomer::query();
        if ($filter === 'pending') {
            $base->whereNull('receipt_confirmed_at');
        } elseif ($filter === 'paid') {
            $base->whereNotNull('receipt_confirmed_at');
        }

        $recordsTotal = (clone $base)->count();

        if ($search) {
            $base->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('vin_number', 'like', "%{$search}%")
                    ->orWhere('engine_number', 'like', "%{$search}%")
                    ->orWhere('license_plate', 'like', "%{$search}%")
                    ->orWhere('code_number', 'like', "%{$search}%")
                    ->orWhereHas('accessories.partner', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        $recordsFiltered = (clone $base)->count();

        $rows = $base
            ->with('accessories.partner')
            ->orderByDesc('id')
            ->skip($start)
            ->take($length)
            ->get();

        $rowNum = $start + 1;
        $data = $rows->map(function ($item) use (&$rowNum, $canApprove, $canConfirmReceipt, $canManage) {
            if ($item->UserApproved) {
                $action = '<a href="' . route('invoice.pdf', $item->id) . '" target="_blank" class="btn btn-icon btn-danger text-white" title="PDF"><i class="bx bxs-file-pdf"></i></a>';
                if ($canConfirmReceipt && !$item->receipt_confirmed_at) {
                    $action .= ' <button class="btn btn-icon btn-warning btn-confirm-receipt text-white" data-id="' . $item->id . '" title="ยืนยันออกใบเสร็จ"><i class="bx bx-receipt"></i></button>';
                }
            } elseif ($canApprove) {
                $action = '<button class="btn btn-icon btn-success btn-approve text-white" data-id="' . $item->id . '" title="อนุมัติ"><i class="bx bx-check-circle"></i></button>';
            } else {
                $action = '<button class="btn btn-icon btn-success text-white" style="filter:blur(2px);pointer-events:none;" title="อนุมัติ" disabled><i class="bx bx-check-circle"></i></button>';
            }

            // admin: แก้ไข / ลบ (ได้ทุกสถานะ)
            if ($canManage) {
                $action .= ' <a href="' . route('invoice.edit', $item->id) . '" class="btn btn-icon btn-primary text-white" title="แก้ไข"><i class="bx bx-edit"></i></a>';
                $action .= ' <button class="btn btn-icon btn-secondary btn-delete-invoice text-white" data-id="' . $item->id . '" title="ลบ"><i class="bx bx-trash"></i></button>';
            }

            $vin = $item->vin_number ?? '-';
            $engine = $item->engine_number ?? '-';
            $license = $item->license_plate ?? '-';
            $detail_car = "vin : {$vin}<br>engine : {$engine}<br>ทะเบียน : {$license}";

            $date_inv = $item->format_date ?? '-';
            $date_receipt = $item->format_receipt_confirmed ?? '-';
            $date = "วางบิล : {$date_inv}<br>จ่ายเงิน : {$date_receipt}";

            $total = $item->total_price ? number_format($item->total_price, 2) : '-';

            return [
                'No'            => $rowNum++,
                'customer_name' => $item->customer_name,
                'partner_name'  => $item->accessories->first()?->partner?->name ?? '-',
                'detail'        => $detail_car,
                'total_price'   => $total,
                'date'          => $date,
                'Action'        => $action,
            ];
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data->values(),
        ]);
    }

    public function confirmReceipt(Request $request, $id)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'account'])) {
            return response()->json(['success' => false], 403);
        }

        $date = $request->input('receipt_date')
            ? Carbon::parse($this->toGregorian($request->input('receipt_date')))->startOfDay()
            : now();

        $invoice = InvoiceCustomer::findOrFail($id);
        $invoice->update(['receipt_confirmed_at' => $date]);
        return response()->json(['success' => true]);
    }

    public function approve($id)
    {
        $invoice = InvoiceCustomer::findOrFail($id);
        $invoice->update([
            'UserApproved'  => Auth::id(),
            'approved_date' => now()->format('Y-m-d'),
        ]);
        return response()->json(['success' => true]);
    }

    public function pdf($id)
    {
        $invoice = InvoiceCustomer::with(['accessories.partner', 'insertInvoice', 'approvedInvoice'])->findOrFail($id);
        $brandName = TbBrand::find($invoice->brand)?->name ?? '';
        $groupedAccessories = $invoice->accessories->groupBy('acc_partner');

        $pdf = Pdf::loadView('invoice.pdf', compact('invoice', 'brandName', 'groupedAccessories'))
            ->setPaper('A4', 'portrait');

        $filename = 'invoice_' . $invoice->code_number . '.pdf';
        return $pdf->stream($filename);
    }

    private function generateCodeNumber(): string
    {
        $prefix = 'INV-' . now()->format('Ymd') . '-';
        $last = InvoiceCustomer::withTrashed()
            ->where('code_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('code_number');

        $seq = $last ? (int) substr($last, -4) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function viewExportReport()
    {
        return view('invoice.report.view');
    }

    public function exportReport(Request $request)
    {
        $fromDate = $request->from_date ?: null;
        $toDate   = $request->to_date   ?: null;

        return Excel::download(new InvoiceExport($fromDate, $toDate), 'ข้อมูลใบสั่งซื้อ.xlsx');
    }
}
