<?php

namespace App\Http\Controllers\finance;

use App\Exports\fn\FirmExport;
use App\Http\Controllers\Controller;
use App\Traits\ConvertsThaiDate;
use App\Models\Finance;
use App\Models\FinancesConfirm;
use App\Models\FinancesExtraCom;
use App\Models\Salecar;
use App\Models\TbCarmodel;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class FinanceController extends Controller
{
    use ConvertsThaiDate;
    public function index()
    {
        $fin = Finance::all();
        return view('finance.view', compact('fin'));
    }

    public function listFinance()
    {
        $fin = Finance::all();

        $data = $fin->map(function ($f, $index) {
            $taxAt = $f->tax . '%';
            $yearAt = $f->max_year . ' ปี';

            return [
                'No' => $index + 1,
                'name' => $f->FinanceCompany,
                'tax' => $taxAt,
                'year' => $yearAt,
                'update' => $f->format_updated,
                'Action' => view('finance.button', compact('f'))->render()
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function create()
    {
        $fin = Finance::all();
        return view('finance.input', compact('fin'));
    }

    function store(Request $request)
    {
        try {
            $data = [
                'FinanceCompany' => $request->FinanceCompany,
                'tax' => $request->tax,
                'max_year' => $request->max_year,
                'userZone' => Auth::user()->userZone ?? null,
                'brand' => Auth::user()->brand ?? null,
                'branch' => Auth::user()->branch ?? null,
            ];

            Finance::create($data);

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
        $fin = Finance::findOrFail($id);
        return view('finance.edit', compact('fin'));
    }

    public function update(Request $request, $id)
    {
        try {
            $fin = Finance::findOrFail($id);
            $data = $request->except(['_token', '_method']);

            $fin->update($data);

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

    function destroy($id)
    {
        try {
            $fin = Finance::findOrFail($id);
            $fin->delete();

            return response()->json([
                'success' => true,
                'message' => 'ลบข้อมูลเรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'
            ], 500);
        }
    }

    // Finance Extra Com
    public function viewExtraCom()
    {
        $finExtra = FinancesExtraCom::all();
        return view('finance.extra-com.view', compact('finExtra'));
    }

    public function listExtraCom()
    {
        $finExtra = FinancesExtraCom::all();

        $data = $finExtra->map(function ($f, $index) {
            $financeF = $f->financeAll ? $f->financeAll->FinanceCompany : '';
            $modelF = $f->model ? $f->model->Name_TH : '';

            return [
                'No' => $index + 1,
                'financeID' => $financeF,
                'model_id' => $modelF,
                'com' => $f->com !== null ? number_format($f->com, 2) : '-',
                'update' => $f->format_updated,
                'Action' => view('finance.extra-com.button', compact('f'))->render()
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function createExtraCom()
    {
        $finExtra = FinancesExtraCom::all();
        $financeAll = Finance::all();
        $model = TbCarmodel::all();
        return view('finance.extra-com.input', compact('finExtra', 'financeAll', 'model'));
    }

    function storeExtraCom(Request $request)
    {
        try {
            $data = [
                'financeID' => $request->financeID,
                'model_id' => $request->model_id,
                'com' => $request->filled('com')
                    ? str_replace(',', '', $request->com)
                    : null,
                'userZone' => Auth::user()->userZone ?? null,
                'brand' => Auth::user()->brand ?? null,
                'branch' => Auth::user()->branch ?? null,
            ];

            FinancesExtraCom::create($data);

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

    public function editExtraCom($id)
    {
        $finExtra = FinancesExtraCom::findOrFail($id);
        $financeAll = Finance::all();
        $model = TbCarmodel::all();
        return view('finance.extra-com.edit', compact('finExtra', 'financeAll', 'model'));
    }

    public function updateExtraCom(Request $request, $id)
    {
        try {
            $finExtra = FinancesExtraCom::findOrFail($id);
            $data = $request->except(['_token', '_method']);

            $data['com'] = $request->com
                ? str_replace(',', '', $request->com)
                : null;

            $finExtra->update($data);

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

    function destroyExtraCom($id)
    {
        try {
            $finExtra = FinancesExtraCom::findOrFail($id);
            $finExtra->delete();

            return response()->json([
                'success' => true,
                'message' => 'ลบข้อมูลเรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'
            ], 500);
        }
    }

    //confirm finance
    public function viewFN()
    {
        $saleCar = Salecar::all();
        return view('finance.confirm-finance.view', compact('saleCar'));
    }

    public function listFN(Request $request)
    {
        $status = $request->status ?? 'unpaid';

        $query = Salecar::with([
            'customer.prefix',
            'model',
            'subModel',
            'remainingPayment.financeInfo',
            'financeConfirm'
        ])
            ->where('payment_mode', 'finance')
            ->where('con_status', '5');

        if ($status === 'unpaid') {
            $query->where(function ($q) {
                $q->doesntHave('financeConfirm')
                    ->orWhereHas('financeConfirm', function ($qq) {
                        $qq->whereNull('date');
                    });
            });
        }

        if ($status === 'paid') {
            $query->whereHas('financeConfirm', function ($q) {
                $q->whereNotNull('date');
            });
        }

        // จำนวนทั้งหมดก่อนค้นหา
        $recordsTotal = (clone $query)->count();

        // ค้นหา (ชื่อ-นามสกุลลูกค้า / ชื่อไฟแนนซ์)
        $search = $request->input('search.value');
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('customer', function ($qq) use ($search) {
                    $qq->searchFullName($search);
                })
                    ->orWhereHas('remainingPayment.financeInfo', function ($qq) use ($search) {
                        $qq->where('FinanceCompany', 'like', "%{$search}%");
                    });
            });
        }

        // จำนวนหลังค้นหา
        $recordsFiltered = (clone $query)->count();

        // แบ่งหน้า (server-side)
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        $query->orderByDesc('id');

        if ($length != -1) {
            $query->skip($start)->take($length);
        }

        $saleCar = $query->get();

        $data = $saleCar->map(function ($s, $index) use ($start) {
            $c = $s->customer;
            $model = $s->model?->Name_TH ?? '-';
            $subModel = $s->subModel?->name ?? '-';
            $subDetail = $s->subModel ? $s->subModel->detail : '';
            $subModelFull = "{$subModel}<br>{$subDetail}";
            $number = $s->remainingPayment?->po_number ?? '-';
            $prefixText = $s->customer?->prefix?->Name_TH;

            $daysRemaining = '-';
            if ($s->BookingDate) {
                $bookingDate = Carbon::parse($s->BookingDate);
                $overdueDays = (int) Carbon::now()->diffInDays($bookingDate->copy()->addDays(5), false);

                if ($overdueDays < 0) {
                    $daysRemaining = 'เกินกำหนด ' . abs($overdueDays) . ' วัน';
                } else {
                    $daysRemaining = $overdueDays . ' วัน';
                }
            }

            $financeF = $s->remainingPayment?->financeInfo?->FinanceCompany ?? '-';

            // สถานะ lifecycle (เช็คจากขั้นสุดท้ายย้อนขึ้น): รับเงิน → เฟิร์ม → ส่งเอกสาร → ยังไม่ส่งเอกสาร
            $fc = $s->financeConfirm;
            if ($fc && $fc->date) {
                $statusBadge = '<span class="badge bg-success">รับเงินเรียบร้อย</span>';
            } elseif ($fc && $fc->firm_date) {
                $statusBadge = '<span class="badge bg-info">เฟิร์มแล้ว</span>';
            } elseif ($fc && $fc->document_date) {
                $statusBadge = '<span class="badge bg-warning text-dark">รอเฟิร์ม</span>';
            } else {
                $statusBadge = '<span class="badge bg-secondary">ยังไม่ส่งเอกสาร</span>';
            }

            return [
                'No' => $start + $index + 1,
                'FullName' => implode(' ', array_filter([
                    $prefixText ?? null,
                    $c->FirstName ?? null,
                    $c->LastName ?? null,
                ])),
                'finance_name' => $financeF,
                'delivery_date' => $s->format_delivery_date ?? '-',
                'document_date' => $s->financeConfirm->format_document_date ?? '-',
                'firm_date' => $s->financeConfirm->format_firm_date ?? '-',
                'date' => $s->financeConfirm->format_date ?? '-',
                'status' => $statusBadge,
                'Action' => view('finance.confirm-finance.button', compact('s'))->render()
            ];
        });

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function viewMoreFN($id)
    {
        $sale = Salecar::with([
            'customer.prefix',
            'model',
            'subModel',
            'remainingPayment.financeInfo',
            'carOrder'
        ])->findOrFail($id);

        // ค้นแบบไม่ผูก global scope เพราะ finances_confirm เป็น 1:1 ต่อ SaleID (กัน record ซ้ำ/หาไม่เจอ ข้ามสาขา)
        $fnCon = FinancesConfirm::withoutGlobalScopes()->where('SaleID', $id)->first();

        return view('finance.confirm-finance.view-more', compact('sale', 'fnCon'));
    }

    public function editFN($id)
    {
        $sale = Salecar::with([
            'customer.prefix',
            'model',
            'subModel',
            'remainingPayment.financeInfo',
            'carOrder'
        ])->findOrFail($id);

        $fnCon = FinancesConfirm::withoutGlobalScopes()->where('SaleID', $id)->first();
        $remaining = $sale->remainingPayment;

        if (!$fnCon) {
            $fnCon = new FinancesConfirm();
        }

        $fnCon->net_price ??= $sale->carOrder?->car_MSRP;
        $fnCon->down      ??= $sale->DownPayment;
        $fnCon->excellent ??= $sale->balanceFinance;
        $maxYear = $sale->remainingPayment?->financeInfo?->max_year ?? 0;

        $comExtra = FinancesExtraCom::where('model_id', $sale->model_id)
            ->where('financeID', $sale->remainingPayment?->financeInfo?->id)
            ->value('com') ?? 0;

        return view('finance.confirm-finance.edit', compact('sale', 'fnCon', 'comExtra', 'maxYear'));
    }

    public function updateFN(Request $request, $id)
    {
        try {
            $sale = Salecar::findOrFail($id);

            // ดึง record ถ้ามี ถ้าไม่มีก็สร้าง object ใหม่ แต่ยังไม่ save
            // ไม่ผูก global scope เพื่อให้เจอ record เดียวกับที่ upsert จากหน้าจอง (กัน record ซ้ำต่อ SaleID)
            $fnCon = FinancesConfirm::withoutGlobalScopes()->firstOrNew(['SaleID' => $id]);

            $data = [
                'SaleID' => $sale->id,
                'net_price' => $request->filled('net_price')
                    ? str_replace(',', '', $request->net_price)
                    : null,
                'down' => $request->filled('down')
                    ? str_replace(',', '', $request->down)
                    : null,
                'excellent' => $request->filled('excellent')
                    ? str_replace(',', '', $request->excellent)
                    : null,
                'excellent_accept' => $request->filled('excellent_accept')
                    ? str_replace(',', '', $request->excellent_accept)
                    : null,
                'excellent_diff' => $request->filled('excellent_diff')
                    ? str_replace(',', '', $request->excellent_diff)
                    : null,
                'com_fin' => $request->filled('com_fin')
                    ? str_replace(',', '', $request->com_fin)
                    : null,
                'com_fin_accept' => $request->filled('com_fin_accept')
                    ? str_replace(',', '', $request->com_fin_accept)
                    : null,
                'com_fin_diff' => $request->filled('com_fin_diff')
                    ? str_replace(',', '', $request->com_fin_diff)
                    : null,
                'com_extra' => $request->filled('com_extra')
                    ? str_replace(',', '', $request->com_extra)
                    : null,
                'com_extra_accept' => $request->filled('com_extra_accept')
                    ? str_replace(',', '', $request->com_extra_accept)
                    : null,
                'com_extra_diff' => $request->filled('com_extra_diff')
                    ? str_replace(',', '', $request->com_extra_diff)
                    : null,
                'com_kickback' => $request->filled('com_kickback')
                    ? str_replace(',', '', $request->com_kickback)
                    : null,
                'com_kickback_accept' => $request->filled('com_kickback_accept')
                    ? str_replace(',', '', $request->com_kickback_accept)
                    : null,
                'com_kickback_diff' => $request->filled('com_kickback_diff')
                    ? str_replace(',', '', $request->com_kickback_diff)
                    : null,
                'com_subsidy' => $request->filled('com_subsidy')
                    ? str_replace(',', '', $request->com_subsidy)
                    : null,
                'com_subsidy_accept' => $request->filled('com_subsidy_accept')
                    ? str_replace(',', '', $request->com_subsidy_accept)
                    : null,
                'com_subsidy_diff' => $request->filled('com_subsidy_diff')
                    ? str_replace(',', '', $request->com_subsidy_diff)
                    : null,
                'advance_installment' => $request->filled('advance_installment')
                    ? str_replace(',', '', $request->advance_installment)
                    : null,
                'advance_installment_accept' => $request->filled('advance_installment_accept')
                    ? str_replace(',', '', $request->advance_installment_accept)
                    : null,
                'advance_installment_diff' => $request->filled('advance_installment_diff')
                    ? str_replace(',', '', $request->advance_installment_diff)
                    : null,
                'special_money' => $request->filled('special_money')
                    ? str_replace(',', '', $request->special_money)
                    : null,
                'special_money_accept' => $request->filled('special_money_accept')
                    ? str_replace(',', '', $request->special_money_accept)
                    : null,
                'special_money_diff' => $request->filled('special_money_diff')
                    ? str_replace(',', '', $request->special_money_diff)
                    : null,
                'total' => $request->filled('total')
                    ? str_replace(',', '', $request->total)
                    : null,
                'actually_received' => $request->filled('actually_received')
                    ? str_replace(',', '', $request->actually_received)
                    : null,
                'diff' => $request->filled('diff')
                    ? str_replace(',', '', $request->diff)
                    : null,
                'firm_date' => $this->toGregorian($request->firm_date),
                'document_date' => $request->document_date,
                'date' => $request->date,
                'remark' => $request->remark,
                'userZone' => Auth::user()->userZone ?? null,
                'brand' => Auth::user()->brand ?? null,
                'branch' => Auth::user()->branch ?? null,
            ];

            // fill + save (รองรับทั้ง create และ update)
            $fnCon->fill($data);
            $fnCon->save();

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

    function destroyFN($id)
    {
        try {
            $fnCon = FinancesConfirm::findOrFail($id);
            $fnCon->delete();

            return response()->json([
                'success' => true,
                'message' => 'ลบข้อมูลเรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'
            ], 500);
        }
    }

    // report finance firm
    public function viewExportFirm()
    {
        return view('purchase-order.report.fn.view');
    }

    public function exportFirm(Request $request)
    {
        $fromDate = $request->from_date ?? now()->startOfMonth()->format('Y-m-d');
        $toDate   = $request->to_date   ?? now()->format('Y-m-d');

        return Excel::download(new FirmExport($fromDate, $toDate), 'firmFN-report.xlsx');
    }
}
