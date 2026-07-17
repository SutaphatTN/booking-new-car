<?php

namespace App\Http\Controllers\car_order;

use App\Exports\carOrder\CarOrderStockExport;
use App\Http\Controllers\Controller;
use App\Traits\ConvertsThaiDate;
use App\Mail\ApproveCarOrderMail;
use App\Mail\BatchApproveCarOrderMail;
use Illuminate\Validation\Rule;
use App\Models\CarOrder;
use App\Models\CarOrderHistory;
use App\Models\CarOrderWaiting;
use App\Models\Salecar;
use App\Models\TbCarmodel;
use App\Models\TbBranch;
use App\Models\TbInteriorColor;
use App\Models\TbOrderStatus;
use App\Models\TbPurchaseType;
use App\Models\TbPricelistCar;
use App\Models\TbSubcarmodel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class CarOrderController extends Controller
{
    use ConvertsThaiDate;

    // view all
    public function index()
    {
        $order = CarOrder::all();
        $model = TbCarmodel::all();
        return view('car-order.view', compact('order', 'model'));
    }

    public function listCarOrder(Request $request)
    {
        $query = CarOrder::with('model', 'subModel')
            ->where('status', 'finished')
            ->where('car_status', '!=', 'Delivered');

        if ($request->model_id) {
            $query->where('model_id', $request->model_id);
        }

        if ($request->sub_model_id) {
            $query->where('subModel_id', $request->sub_model_id);
        }

        $order = $query->get();

        $data = $order->map(function ($c, $index) {
            $modelOrder = $c->model ? $c->model->Name_TH : '';
            $subModelOrder = $c->subModel ? $c->subModel->name : '';
            $subDetail = $c->subModel ? $c->subModel->detail : '';
            $status = $c->orderStatus ? $c->orderStatus->name : '';

            $row = fn($icon, $class, $tip, $text) =>
                "<div class=\"text-start\"><i class=\"bx {$icon} {$class} me-1\" data-bs-toggle=\"tooltip\" title=\"{$tip}\"></i>:&nbsp;{$text}</div>";

            if (in_array($c->brand, [2, 3, 4])) {
                $car = $row('bxs-car',         'text-primary', 'รุ่นหลัก', $modelOrder)
                     . $row('bx-git-branch',   'text-info',    'รุ่นย่อย', $subModelOrder)
                     . $row('bx-palette',      'text-danger',  'สี',       $c->display_color)
                     . $row('bx-purchase-tag', 'text-success', 'ราคาขาย',  number_format($c->car_MSRP));
            } else {
                $car = $row('bxs-car',         'text-primary', 'รุ่นหลัก',    $modelOrder)
                     . $row('bx-git-branch',   'text-info',    'รุ่นย่อย',    $subModelOrder)
                     . ($subDetail ? $row('bx-info-circle', 'text-warning', 'รายละเอียด', $subDetail) : '')
                     . $row('bx-palette',      'text-danger',  'สี',          $c->display_color)
                     . $row('bx-purchase-tag', 'text-success', 'ราคาขาย',     number_format($c->car_MSRP));
            }

            $statusDisplay = "รถ : {$status}<br>การจอง : {$c->car_status}";

            $sysDate = $c->format_system_date ?? '-';
            $esDate = $c->format_estimated_stock_date ?? '-';
            $invDate = $c->format_order_invoice_date ?? '-';
            $stDate = $c->format_order_stock_date ?? '-';

            // $allDate = "สั่งในระบบ : {$sysDate}<br>คาดว่าสินค้ามาถึง : {$esDate}<br>ออกใบกำกับ : {$invDate}<br>สต็อก : {$stDate}";
            $allDate = '
            <div class="text-start">
            <div>
            <i class="bx bx-calendar text-primary me-1"
                data-bs-toggle="tooltip"
                title="วันที่สั่งในระบบ"></i>
                :&nbsp;' . $sysDate . '
            </div>
            <div>
                <i class="bx bx-time text-warning me-1"
                data-bs-toggle="tooltip"
                title="วันที่คาดว่าสินค้ามาถึง"></i>
                :&nbsp;' . $esDate . '
            </div>
            <div>
                <i class="bx bx-receipt text-info me-1"
                data-bs-toggle="tooltip"
                title="วันที่ออกใบกำกับ"></i>
                :&nbsp;' . $invDate . '
            </div>
            <div>
                <i class="bx bx-package text-success me-1"
                data-bs-toggle="tooltip"
                title="วันที่ Stock"></i>
                :&nbsp;' . $stDate . '
            </div>
            </div>
            ';

            return [
                'No' => $index + 1,
                'date' => $allDate,
                'car' => $car,
                'vin_number' => $c->vin_number ?? '-',
                'j_number' => $c->j_number ?? '-',
                'status' => $statusDisplay,
                'Action' => view('car-order.button', compact('c'))->render()
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function viewMore($id)
    {
        $order = CarOrder::with([
            'model',
            'subModel',
            'gwmColor',
            'interiorColor',
            'approvers'
        ])->find($id);

        return view('car-order.view-more', compact('order'));
    }

    public function edit($id)
    {
        $authUser = Auth::user();

        $order = CarOrder::findOrFail($id);
        $model = TbCarmodel::all();
        $subModels = TbSubcarmodel::where('model_id', $order->model_id)->get();
        $orderStatus = TbOrderStatus::all();
        $approvers = User::whereIn('role', ['audit', 'audit_lead', 'audit_dp', 'gm', 'md'])
            ->where('brand', $authUser->brand == 2 ? 2 : 1)
            ->get();
        $purchaseType = TbPurchaseType::all();
        $interiorColor = TbInteriorColor::all();
        $branches = TbBranch::all();

        return view('car-order.edit', compact('order', 'model', 'subModels', 'orderStatus', 'approvers', 'purchaseType', 'interiorColor', 'branches'));
    }

    public function update(Request $request, $id)
    {
        try {
            $order = CarOrder::findOrFail($id);

            // FP Tisco + สถานะ Invoice(3)/Stock(4) ต้องกรอกวันที่จ่าย FP
            if ($request->payment_type === 'fp_tisco'
                && in_array((int) $request->order_status, [3, 4], true)
                && !$request->filled('fp_date')) {
                return response()->json([
                    'success' => false,
                    'message' => 'กรุณากรอกวันที่จ่าย FP ก่อนเปลี่ยนสถานะเป็น Stock หรือ Invoice',
                ], 422);
            }

            $data = $request->except(['_token', '_method']);

            $data['car_DNP'] = $request->car_DNP
                ? str_replace(',', '', $request->car_DNP)
                : null;

            $data['car_MSRP'] = $request->car_MSRP
                ? str_replace(',', '', $request->car_MSRP)
                : null;

            $data['RI'] = $request->RI
                ? str_replace(',', '', $request->RI)
                : null;

            $data['WS'] = $request->WS
                ? str_replace(',', '', $request->WS)
                : null;

            $order->update($data);

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

    //ยกเลิกการผูกรถ
    private function unbindCarOrder(CarOrder $order)
    {
        if (!$order->salecar_id) {
            return;
        }

        $saleCar = SaleCar::find($order->salecar_id);

        if ($saleCar) {
            $saleCar->update([
                'CarOrderID' => null
            ]);
        }

        $order->update([
            'car_status' => 'Available'
        ]);
    }

    function destroy($id)
    {
        try {
            $order = CarOrder::findOrFail($id);
            $this->unbindCarOrder($order);
            $order->delete();

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

    public function search(Request $request)
    {
        $keyword = trim($request->input('keyword', ''));

        $query = CarOrder::with(['model', 'subModel', 'orderStatus', 'gwmColor', 'interiorColor'])
            ->where('car_status', 'Available')
            ->whereIn('status', ['approved', 'finished']);

        if ($keyword !== '') {
            // ค้นหาแบบพิมพ์หา
            $query->where(function ($q) use ($keyword) {
                $q->where('vin_number', 'like', "%{$keyword}%")
                    ->orWhere('j_number', 'like', "%{$keyword}%")
                    ->orWhere('order_code', 'like', "%{$keyword}%")
                    ->orWhere('option', 'like', "%{$keyword}%")
                    ->orWhere('color', 'like', "%{$keyword}%")
                    ->orWhere('year', 'like', "%{$keyword}%")
                    ->orWhereHas('model', function ($q2) use ($keyword) {
                        $q2->where('Name_TH', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('subModel', function ($q2) use ($keyword) {
                        $q2->where('name', 'like', "%{$keyword}%")
                            ->orWhere('detail', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('gwmColor', function ($q2) use ($keyword) {
                        $q2->where('name', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('interiorColor', function ($q2) use ($keyword) {
                        $q2->where('name', 'like', "%{$keyword}%");
                    });
            });
        } else {
            // ค้นหาแบบไม่ต้องพิมพ์
            if ($request->filled('model_id')) {
                $query->where('model_id', $request->model_id);
            }
            if ($request->filled('sub_model_id')) {
                $query->where('subModel_id', $request->sub_model_id);
            }
            if ($request->filled('option')) {
                $query->where('option', 'like', '%' . $request->option . '%');
            }
            if ($request->filled('color_id')) {
                $query->where('gwm_color', $request->color_id);
            }
            if ($request->filled('interior_color_id')) {
                $query->where('interior_color', $request->interior_color_id);
            }
            if ($request->filled('color_text')) {
                $query->where('color', 'like', '%' . $request->color_text . '%');
            }
            if ($request->filled('year')) {
                $query->where('year', $request->year);
            }
        }

        $order = $query->orderByRaw("CASE WHEN order_status = 4 THEN 0 ELSE 1 END")
            ->orderBy('order_stock_date', 'asc')
            ->limit(20)
            ->get();

        $order = $order->map(function ($item) {

            if ($item->brand == 2) {
                $item->display_color = $item->gwmColor->name ?? '-';
                $item->display_interior_color = $item->interiorColor->name ?? '-';
            } elseif (in_array($item->brand, [3, 4])) {
                $item->display_color = $item->gwmColor->name ?? '-';
                $item->display_interior_color = null;
            } else {
                $item->display_color = $item->color ?? '-';
                $item->display_interior_color = null;
            }

            $item->format_order_stock_date = $item->format_order_stock_date;

            return $item;
        });

        return response()->json($order);
    }

    // history
    public function history()
    {
        $history = CarOrderHistory::all();
        return view('car-order.history.history', compact('history'));
    }

    public function listHistory()
    {
        $history = CarOrderHistory::with([
            'saleCar.customer.prefix',
            'carOrder.model',
            'carOrder.subModel'
        ])->get();

        $data = $history->map(function ($h, $index) {

            $prefix = $h->saleCar?->customer?->prefix?->Name_TH ?? '';
            $name   = $h->saleCar?->customer?->FirstName ?? '-';
            $last   = $h->saleCar?->customer?->LastName ?? '';

            $orderCode = $h->carOrder?->order_code ?? '-';

            $model = $h->carOrder?->model?->Name_TH ?? '-';
            $subModel = $h->carOrder?->subModel?->name ?? '-';

            $status   = $h->saleCar?->conStatus?->name ?? '';

            return [
                'No' => $index + 1,
                'full_name' => $prefix . ' ' . $name . ' ' . $last,
                'order_code' => $orderCode,
                'model' => $model,
                'subModel' => $subModel,
                'booking' => $h->format_booking_date,
                'status' => $status,
            ];
        });

        return response()->json(['data' => $data]);
    }

    //pending
    public function pending()
    {
        $pending = CarOrder::all();
        return view('car-order.pending.view', compact('pending'));
    }

    public function listPending()
    {
        $orders = CarOrder::with('model', 'subModel', 'gwmColor')
            ->where('status', 'pending')
            ->get();

        $waitings = CarOrderWaiting::with('model', 'subModel', 'gwmColor')
            ->where('status', 'pending')
            ->get();

        $data = collect();

        $row = fn($icon, $class, $tip, $text) =>
            "<div class=\"text-start\"><i class=\"bx {$icon} {$class} me-1\" data-bs-toggle=\"tooltip\" title=\"{$tip}\"></i>:&nbsp;{$text}</div>";

        foreach ($orders as $p) {
            $modelOrder    = $p->model ? $p->model->Name_TH : '';
            $subModelOrder = $p->subModel ? $p->subModel->name : '';
            $subDetail     = $p->subModel ? $p->subModel->detail : '';

            if (in_array($p->brand, [2, 3, 4])) {
                $modelDisplay = $row('bxs-car',         'text-primary', 'รุ่นหลัก', $modelOrder)
                              . $row('bx-git-branch',   'text-info',    'รุ่นย่อย', $subModelOrder)
                              . $row('bx-palette',      'text-danger',  'สี',       $p->display_color)
                              . $row('bx-purchase-tag', 'text-success', 'ราคาขาย',  number_format($p->car_MSRP));
            } else {
                $modelDisplay = $row('bxs-car',         'text-primary', 'รุ่นหลัก',    $modelOrder)
                              . $row('bx-git-branch',   'text-info',    'รุ่นย่อย',    $subModelOrder)
                              . ($subDetail ? $row('bx-info-circle', 'text-warning', 'รายละเอียด', $subDetail) : '')
                              . $row('bx-palette',      'text-danger',  'สี',          $p->display_color)
                              . $row('bx-purchase-tag', 'text-success', 'ราคาขาย',     number_format($p->car_MSRP));
            }

            $data->push([
                'No'         => 0,
                'order_code' => $p->order_code,
                'date'       => $p->format_order_date,
                'type'       => $p->type,
                'model'      => $modelDisplay,
                'Action'     => view('car-order.pending.button', compact('p'))->render(),
            ]);
        }

        foreach ($waitings as $w) {
            $modelOrder    = $w->model ? $w->model->Name_TH : '';
            $subModelOrder = $w->subModel ? $w->subModel->name : '';
            $subDetail     = $w->subModel ? $w->subModel->detail : '';
            $colorDisplay  = $w->display_color;
            $msrp          = $w->car_MSRP ? number_format($w->car_MSRP) : '-';

            if (in_array($w->brand, [2, 3, 4])) {
                $modelDisplay = $row('bxs-car',         'text-primary', 'รุ่นหลัก', $modelOrder)
                              . $row('bx-git-branch',   'text-info',    'รุ่นย่อย', $subModelOrder)
                              . $row('bx-palette',      'text-danger',  'สี',       $colorDisplay)
                              . $row('bx-purchase-tag', 'text-success', 'ราคาขาย',  $msrp)
                              . "<span class='badge bg-label-warning'>รอ : {$w->count_order} คัน</span>";
            } else {
                $modelDisplay = $row('bxs-car',         'text-primary', 'รุ่นหลัก',    $modelOrder)
                              . $row('bx-git-branch',   'text-info',    'รุ่นย่อย',    $subModelOrder)
                              . ($subDetail ? $row('bx-info-circle', 'text-warning', 'รายละเอียด', $subDetail) : '')
                              . $row('bx-palette',      'text-danger',  'สี',          $colorDisplay)
                              . $row('bx-purchase-tag', 'text-success', 'ราคาขาย',     $msrp)
                              . "<span class='badge bg-label-warning'>รอ : {$w->count_order} คัน</span>";
            }

            $data->push([
                'No'         => 0,
                'order_code' => $w->order_code . '<br> <span class="badge bg-label-warning mt-1">Waiting</span>',
                'date'       => $w->format_order_date,
                'type'       => $w->type,
                'model'      => $modelDisplay,
                'Action'     => view('car-order.pending.button-waiting', compact('w'))->render(),
            ]);
        }

        $data = $data->values()->map(function ($item, $index) {
            $item['No'] = $index + 1;
            return $item;
        });

        return response()->json(['data' => $data]);
    }

    public function create()
    {
        $authUser = Auth::user();

        $order = CarOrder::all();
        $model = TbCarmodel::all();
        $orderStatus = TbOrderStatus::all();
        $approvers = User::where('role', 'md')
            ->where('brand', $authUser->brand == 2 ? 2 : 1)
            ->get();
        $purchaseType = TbPurchaseType::all();
        $interiorColor = TbInteriorColor::all();

        return view('car-order.pending.input', compact('order', 'model', 'orderStatus', 'approvers', 'purchaseType', 'interiorColor'));
    }

    function store(Request $request)
    {
        try {

            if (!$request->filled('order_date')) {
                $request->merge(['order_date' => now()]);
            }

            $request->validate([
                'order_date' => 'required|date',
                'model_id' => 'required|integer|exists:tb_carmodels,id',
            ]);

            $lastOrder = CarOrder::latest('id')->first();
            $newOrderId = $lastOrder ? $lastOrder->id + 1 : 1;

            $OrderYear = Carbon::parse($request->order_date)->format('Y');
            $OrderMonth = Carbon::parse($request->order_date)->format('m');
            $OrderDate = Carbon::parse($request->order_date)->format('d');

            $model = TbCarmodel::findOrFail($request->model_id);

            $prefix = "{$OrderYear}-{$OrderMonth}-{$OrderDate}-{$model->id}-";

            $lastCode = CarOrder::where('order_code', 'like', $prefix . '%')
                ->orderBy('order_code', 'desc')
                ->first();

            if ($lastCode) {
                $lastNumber = intval(substr($lastCode->order_code, -4));
                $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '0001';
            }

            $order_code = $prefix . $newNumber;

            $exists = CarOrder::where('order_code', $order_code)->exists();
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'รหัส Order Code ถูกสร้างซ้ำ กรุณาบันทึกใหม่'
                ], 400);
            }

            $data = [
                'model_id' => $request->model_id,
                'subModel_id' => $request->subModel_id,
                'salecar_id' => $request->salecar_id,
                'option' => $request->option,
                'purchase_source' => $request->purchase_source,
                'order_code' => $order_code,
                'type' => $request->type,
                'order_date' => $this->toGregorian($request->order_date),
                'color' => $request->color ?? null,
                'type_color' => $request->type_color ?? null,
                'year' => $request->year,
                'purchase_type' => $request->purchase_type,
                'payment_type' => $request->payment_type,
                'order_status' => 1,
                'car_DNP' => $request->filled('car_DNP')
                    ? str_replace(',', '', $request->car_DNP)
                    : null,
                'car_MSRP' => $request->filled('car_MSRP')
                    ? str_replace(',', '', $request->car_MSRP)
                    : null,
                'RI' => $request->filled('RI')
                    ? str_replace(',', '', $request->RI)
                    : null,
                'WS' => $request->filled('WS')
                    ? str_replace(',', '', $request->WS)
                    : null,
                'car_status' => 'Available',
                'approver' => $request->approver,
                'note' => $request->note,
                'status' => CarOrder::STATUS_PENDING,
                'userZone' => Auth::user()->userZone ?? null,
                'brand' => Auth::user()->brand ?? null,
                'UserInsert' => Auth::id(),
                'branch' => Auth::user()->branch ?? null,
            ];

            $brand = Auth::user()->brand;

            if ($brand == 2) {
                $data['gwm_color'] = $request->gwm_color;
                $data['interior_color'] = $request->interior_color;
            }

            if (in_array($brand, [3, 4])) {
                $data['gwm_color'] = $request->gwm_color;
            }

            $order = CarOrder::create($data);

            DB::transaction(function () use ($request, $order) {

                if ($request->filled('salecar_id')) {

                    $saleCar = SaleCar::findOrFail($request->salecar_id);

                    $oldCarOrderID = $saleCar->CarOrderID;
                    $newCarOrderID = $order->id;

                    // update Salecar
                    $saleCar->update([
                        'CarOrderID' => $newCarOrderID,
                    ]);

                    // history
                    CarOrderHistory::create([
                        'SaleID'      => $saleCar->id,
                        'CarOrderID'  => $newCarOrderID,
                        'BookingDate' => $this->toGregorian($request->order_date),
                        'changed_at' => now(),
                        'userZone' => Auth::user()->userZone ?? null,
                        'brand' => Auth::user()->brand ?? null,
                        'branch' => Auth::user()->branch ?? null,
                    ]);

                    // คืนสถานะ order เก่า (ถ้ามี)
                    if ($oldCarOrderID) {
                        CarOrder::where('id', $oldCarOrderID)
                            ->update(['car_status' => 'Available']);
                    }

                    // ตั้งสถานะ order ใหม่
                    $order->update([
                        'car_status' => 'Booked'
                    ]);
                }
            });

            // หมายเหตุ: ไม่ส่งเมลตอนสร้าง order แล้ว — ใช้ปุ่ม "ขออนุมัติที่เลือก" ในหน้า process ส่งเมลรวมครั้งเดียวแทน

            return response()->json([
                'success' => true,
                'message' => 'เพิ่มข้อมูลเรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {
            // return response()->json([
            //     'success' => false,
            //     'message' => $e->getMessage(),
            //     'trace' => $e->getTraceAsString(),
            // ], 500);
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'
            ], 500);
        }
    }

    public function storeWaiting(Request $request)
    {
        try {
            if (!$request->filled('order_date')) {
                $request->merge(['order_date' => now()]);
            }

            $request->validate([
                'order_date' => 'required|date',
                'model_id'   => 'required|integer|exists:tb_carmodels,id',
            ]);

            $OrderYear  = Carbon::parse($request->order_date)->format('Y');
            $OrderMonth = Carbon::parse($request->order_date)->format('m');
            $OrderDate  = Carbon::parse($request->order_date)->format('d');

            $model  = TbCarmodel::findOrFail($request->model_id);
            $prefix = "{$OrderYear}-{$OrderMonth}-{$OrderDate}-{$model->id}-";

            $lastCode = CarOrderWaiting::where('order_code', 'like', $prefix . '%')
                ->orderBy('order_code', 'desc')
                ->first();

            $newNumber  = $lastCode
                ? str_pad(intval(substr($lastCode->order_code, -4)) + 1, 4, '0', STR_PAD_LEFT)
                : '0001';

            $order_code = $prefix . $newNumber;

            $data = [
                'model_id'       => $request->model_id,
                'subModel_id'    => $request->subModel_id,
                'option'         => $request->option,
                'purchase_source' => $request->purchase_source,
                'order_code'     => $order_code,
                'type'           => $request->type,
                'order_date'     => $this->toGregorian($request->order_date),
                'color'          => $request->color ?? null,
                'type_color'     => $request->type_color ?? null,
                'year'           => $request->year,
                'purchase_type'  => $request->purchase_type,
                'payment_type'   => $request->payment_type,
                'car_DNP'        => $request->filled('car_DNP')  ? str_replace(',', '', $request->car_DNP)  : null,
                'car_MSRP'       => $request->filled('car_MSRP') ? str_replace(',', '', $request->car_MSRP) : null,
                'RI'             => $request->filled('RI')  ? str_replace(',', '', $request->RI)  : null,
                'WS'             => $request->filled('WS')  ? str_replace(',', '', $request->WS)  : null,
                'count_order'    => $request->count_order ?? 1,
                'approver'       => $request->approver,
                'note'           => $request->note,
                'status'         => CarOrderWaiting::STATUS_PENDING,
                'userZone'       => Auth::user()->userZone ?? null,
                'brand'          => Auth::user()->brand ?? null,
                'UserInsert'     => Auth::id(),
                'branch'         => Auth::user()->branch ?? null,
            ];

            $brand = Auth::user()->brand;
            if ($brand == 2) {
                $data['gwm_color']     = $request->gwm_color;
                $data['interior_color'] = $request->interior_color;
            }
            if (in_array($brand, [3, 4])) {
                $data['gwm_color'] = $request->gwm_color;
            }

            CarOrderWaiting::create($data);

            // หมายเหตุ: ไม่ส่งเมลตอนสร้าง — ใช้ปุ่ม "ขออนุมัติที่เลือก" ในหน้า process ส่งเมลรวมครั้งเดียวแทน

            return response()->json([
                'success' => true,
                'message' => 'เพิ่มข้อมูลรออนุมัติเรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {
            // return response()->json([
            //     'success' => false,
            //     'message' => $e->getMessage(),
            //     'trace' => $e->getTraceAsString(),
            // ], 500);
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'
            ], 500);
        }
    }

    public function approveWaiting(Request $request, $id)
    {
        try {
            $request->validate([
                'received_order' => 'required|integer|min:0',
            ]);

            $waiting = CarOrderWaiting::findOrFail($id);
            $received = (int) $request->received_order;

            DB::transaction(function () use ($waiting, $received) {
                $this->createOrdersFromWaiting($waiting, $received);
            });

            return response()->json([
                'success' => true,
                'message' => "อนุมัติเรียบร้อย สร้าง รายการรถ จำนวน {$received} คัน"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'
            ], 500);
        }
    }

    public function rejectWaiting(Request $request, $id)
    {
        try {
            $waiting = CarOrderWaiting::findOrFail($id);
            $waiting->update([
                'status'      => CarOrderWaiting::STATUS_REJECTED,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'reason'        => $request->reason,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'บันทึกการไม่อนุมัติเรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'
            ], 500);
        }
    }

    //get sub model from model
    public function getSubModelCarOrder(Request $request)
    {
        $modelId = $request->model_id;

        $subModels = TbSubcarmodel::where('model_id', $modelId)
            ->select('id', 'name', 'detail')
            ->orderBy('name')
            ->get();

        return response()->json($subModels);
    }

    //get color from sub model
    public function getColorBySubModel(Request $request)
    {
        $subModelId = $request->sub_model_id;

        $colors = TbSubcarmodel::with('colors')
            ->find($subModelId)
            ?->colors
            ->select('id', 'name');

        return response()->json($colors);
    }

    // get interior color by model_id
    public function getInteriorColorByModel(Request $request)
    {
        $modelId = $request->model_id;

        $colors = TbInteriorColor::whereHas('models', fn($q) => $q->where('tb_carmodels.id', $modelId))
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($colors);
    }

    //get price list car option
    public function getPricelistOptions(Request $request)
    {
        $subModelId = $request->sub_model_id;
        $brand = Auth::user()->brand;

        if (!$subModelId) {
            return response()->json([]);
        }

        $query = TbPricelistCar::where('subModel_id', $subModelId);

        if ($brand == 1) {
            $rows = $query->select('color', 'year')->distinct()->orderBy('year')->orderBy('color')->get();
            return response()->json(['type' => 'color_year', 'data' => $rows]);
        } else {
            $rows = $query->select('year')->distinct()->orderBy('year')->get();
            return response()->json(['type' => 'year_only', 'data' => $rows]);
        }
    }

    //get price list data
    public function getPricelistData(Request $request)
    {
        $subModelId = $request->sub_model_id;
        $year = $request->year;
        $brand = Auth::user()->brand;

        if (!$subModelId || !$year) {
            return response()->json(null);
        }

        $query = TbPricelistCar::where('subModel_id', $subModelId)->where('year', $year);

        if ($brand == 1 && $request->color) {
            $query->where('color', $request->color);
        }

        $row = $query->first();

        if (!$row) {
            return response()->json(null);
        }

        return response()->json([
            'option' => $row->option,
            'dnp'    => $row->dnp,
            'msrp'   => $row->msrp,
            'ri'     => $row->ri,
            'ws'     => $row->ws,
        ]);
    }

    public function editPending($id)
    {
        $authUser = Auth::user();

        $order = CarOrder::with(['saleCus'])->findOrFail($id);
        $model = TbCarmodel::all();
        $subModels = TbSubcarmodel::where('model_id', $order->model_id)->get();
        $orderStatus = TbOrderStatus::all();
        $approvers = User::whereIn('role', ['audit', 'audit_lead', 'audit_dp', 'gm', 'md'])
            ->where('brand', $authUser->brand == 2 ? 2 : 1)
            ->get();
        $purchaseType = TbPurchaseType::all();
        $gwmColor = $order->subModel
            ? $order->subModel->colors
            : collect();
        $interiorColor = TbInteriorColor::all();

        return view('car-order.pending.edit', compact('order', 'model', 'subModels', 'orderStatus', 'approvers', 'purchaseType', 'gwmColor', 'interiorColor'));
    }

    public function updatePending(Request $request, $id)
    {
        try {
            $order = CarOrder::findOrFail($id);
            $data = $request->except(['_token', '_method']);

            $data['car_DNP'] = $request->car_DNP
                ? str_replace(',', '', $request->car_DNP)
                : null;

            $data['car_MSRP'] = $request->car_MSRP
                ? str_replace(',', '', $request->car_MSRP)
                : null;

            $data['RI'] = $request->RI
                ? str_replace(',', '', $request->RI)
                : null;

            $data['WS'] = $request->WS
                ? str_replace(',', '', $request->WS)
                : null;

            $order->update($data);

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

    function destroyPending($id)
    {
        try {
            $order = CarOrder::findOrFail($id);
            $this->unbindCarOrder($order);
            $order->delete();

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

    // process
    public function process(Request $request)
    {
        $authUser = Auth::user();
        $process = CarOrder::all();
        $openId = $request->query('open_id');

        // ผู้อนุมัติ (role md) สำหรับ modal "ขออนุมัติที่เลือก"
        $approvers = User::where('role', 'md')
            ->where('brand', $authUser->brand == 2 ? 2 : 1)
            ->get();

        return view('car-order.process.view', compact('process', 'openId', 'approvers'));
    }

    public function listProcess()
    {
        $orders = CarOrder::with('model', 'subModel')
            ->where('status', 'pending')
            ->get();

        $waitings = CarOrderWaiting::with('model', 'subModel')
            ->where('status', 'pending')
            ->get();

        // จำนวนใน stock — นับ CarOrder ที่ status = approved/finished จัดกลุ่มตาม รุ่น + ปี + สี (auto brand-scope)
        // ไม่นับคันที่ car_status = Delivered (ส่งมอบ = ขายไปแล้ว ไม่ใช่สต็อก)
        // สี: brand 2/3 ใช้ gwm_color (id), อื่นๆ ใช้ฟิลด์ color
        $stockKey = fn($o) => $o->model_id . '|' . ($o->year ?? '-')
            . '|' . (in_array($o->brand, [2, 3, 4]) ? 'g:' . $o->gwm_color : 'c:' . $o->color);
        $stockMap = CarOrder::whereIn('status', [CarOrder::STATUS_APPROVED, CarOrder::STATUS_FINISHED])
            ->where(function ($q) {
                $q->where('car_status', '!=', 'Delivered')->orWhereNull('car_status');
            })
            ->get()
            ->groupBy($stockKey)
            ->map->count();

        $data = collect();

        foreach ($orders as $p) {
            $modelOrder = $p->model ? $p->model->Name_TH : '';
            $subModelOrder = $p->subModel ? $p->subModel->name : '';
            $subDetail = $p->subModel ? $p->subModel->detail : null;
            $subModelFull = $subDetail ? "{$subDetail} - {$subModelOrder}" : $subModelOrder;

            $data->push([
                'id'        => $p->id,
                'row_type'  => 'order',
                'No'        => 0,
                'date'      => $p->format_order_date,
                'type'      => $p->type,
                'model_id'  => $modelOrder,
                'subModel_id' => $subModelFull,
                'color'     => $p->display_color,
                'stock'     => $stockMap[$stockKey($p)] ?? 0,
                'order_qty' => 1,
                'Action'    => view('car-order.process.button', compact('p'))->render(),
            ]);
        }

        foreach ($waitings as $w) {
            $modelOrder = $w->model ? $w->model->Name_TH : '';
            $subModelOrder = $w->subModel ? $w->subModel->name : '';
            $subDetail = $w->subModel ? $w->subModel->detail : null;
            $subModelFull = $subDetail ? "{$subDetail} - {$subModelOrder}" : $subModelOrder;

            $data->push([
                'id'        => $w->id,
                'row_type'  => 'waiting',
                'No'        => 0,
                'date'      => $w->format_order_date,
                'type'      => $w->type,
                'model_id'  => $modelOrder,
                'subModel_id' => $subModelFull,
                'color'     => $w->display_color,
                'stock'     => $stockMap[$stockKey($w)] ?? 0,
                'order_qty' => $w->count_order ?? 1,
                'Action'    => view('car-order.process.button-waiting', compact('w'))->render(),
            ]);
        }

        $data = $data->values()->map(function ($item, $index) {
            $item['No'] = $index + 1;
            return $item;
        });

        return response()->json(['data' => $data]);
    }

    // ขออนุมัติที่เลือก — ส่งเมลรวมครั้งเดียวให้ผู้อนุมัติ (md) พร้อมรายการทั้งหมด + ลิงก์กลับหน้า process
    public function requestApproval(Request $request)
    {
        $request->validate([
            'approver_id' => ['required', Rule::exists('users', 'id')->where('role', 'md')],
            'order_ids'   => ['array'],
            'waiting_ids' => ['array'],
        ], [
            'approver_id.required' => 'กรุณาเลือกผู้อนุมัติ',
            'approver_id.exists'   => 'ผู้อนุมัติไม่ถูกต้อง',
        ]);

        try {
            $approver = User::where('role', 'md')->findOrFail($request->approver_id);
            if (!$approver->email) {
                return response()->json(['success' => false, 'message' => 'ผู้อนุมัติยังไม่มีอีเมลในระบบ'], 422);
            }

            $orders = CarOrder::with(['model', 'subModel', 'gwmColor'])
                ->whereIn('id', $request->input('order_ids', []))
                ->where('status', CarOrder::STATUS_PENDING)
                ->get();

            $waitings = CarOrderWaiting::with(['model', 'subModel', 'gwmColor'])
                ->whereIn('id', $request->input('waiting_ids', []))
                ->where('status', 'pending')
                ->get();

            if ($orders->isEmpty() && $waitings->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'ไม่พบรายการที่ขออนุมัติได้'], 422);
            }

            // อัปเดตผู้อนุมัติของรายการที่เลือกให้ตรงกับคนที่เลือกตอนขออนุมัติ
            CarOrder::whereIn('id', $orders->pluck('id'))->update(['approver' => $approver->id]);
            CarOrderWaiting::whereIn('id', $waitings->pluck('id'))->update(['approver' => $approver->id]);

            // ประกอบรายการสำหรับเมล
            $mapItem = function ($r) {
                $subDetail = $r->subModel->detail ?? null;
                $subName   = $r->subModel->name ?? '-';
                return [
                    'order_code' => $r->order_code,
                    'type'       => $r->type,
                    'model'      => $r->model->Name_TH ?? '-',
                    'subModel'   => $subDetail ? "{$subDetail} - {$subName}" : $subName,
                    'color'      => $r->display_color,
                    'year'       => $r->year ?? '-',
                    'qty'        => $r->count_order ?? 1,
                ];
            };
            $items = $orders->map($mapItem)->merge($waitings->map($mapItem))->values()->all();

            Mail::to($approver->email)->send(new BatchApproveCarOrderMail($items, $approver->name, Auth::user()->brand));

            return response()->json([
                'success' => true,
                'message' => 'ส่งคำขออนุมัติเรียบร้อยแล้ว (' . count($items) . ' รายการ)'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }

    // สร้างรายการรถ (CarOrder) จากคำขอ waiting ตามจำนวนที่รับจริง + อัปเดตสถานะ waiting
    // หมายเหตุ: ต้องเรียกภายใน DB::transaction
    private function createOrdersFromWaiting(CarOrderWaiting $waiting, int $received)
    {
        for ($i = 0; $i < $received; $i++) {
            $OrderYear  = Carbon::parse($waiting->order_date ?? now())->format('Y');
            $OrderMonth = Carbon::parse($waiting->order_date ?? now())->format('m');
            $OrderDate  = Carbon::parse($waiting->order_date ?? now())->format('d');

            $prefix = "{$OrderYear}-{$OrderMonth}-{$OrderDate}-{$waiting->model_id}-";

            $lastCode = CarOrder::where('order_code', 'like', $prefix . '%')
                ->orderBy('order_code', 'desc')
                ->lockForUpdate()
                ->first();

            $newNumber  = $lastCode
                ? str_pad(intval(substr($lastCode->order_code, -4)) + 1, 4, '0', STR_PAD_LEFT)
                : '0001';

            $order_code = $prefix . $newNumber;

            CarOrder::create([
                'model_id'       => $waiting->model_id,
                'subModel_id'    => $waiting->subModel_id,
                'option'         => $waiting->option,
                'purchase_source' => $waiting->purchase_source,
                'order_code'     => $order_code,
                'type'           => $waiting->type,
                'order_date'     => $waiting->order_date ?? now(),
                'color'          => $waiting->color,
                'type_color'     => $waiting->type_color,
                'gwm_color'      => $waiting->gwm_color,
                'interior_color' => $waiting->interior_color,
                'year'           => $waiting->year,
                'purchase_type'  => $waiting->purchase_type,
                'payment_type'   => $waiting->payment_type,
                'order_status'   => 1,
                'car_DNP'        => $waiting->car_DNP,
                'car_MSRP'       => $waiting->car_MSRP,
                'RI'             => $waiting->RI,
                'WS'             => $waiting->WS,
                'car_status'     => 'Available',
                'approver'       => $waiting->approver,
                'note'           => $waiting->note,
                'status'         => CarOrder::STATUS_APPROVED,
                'approved_by'    => Auth::id(),
                'approver_date'  => now(),
                'userZone'       => $waiting->userZone,
                'brand'          => $waiting->brand,
                'UserInsert'     => $waiting->UserInsert,
                'branch'         => $waiting->branch,
                'waiting_id'     => $waiting->id,
            ]);
        }

        $waiting->update([
            'received_order' => $received,
            'status'         => CarOrderWaiting::STATUS_APPROVED,
            'approved_by'    => Auth::id(),
            'approved_at'    => now(),
        ]);
    }

    // อนุมัติที่เลือก — bulk approve ครั้งเดียว
    // order ลูกค้า: อนุมัติตรงๆ | waiting: อนุมัติ "ตามจำนวนที่สั่ง" (received = count_order)
    public function bulkApprove(Request $request)
    {
        // อนุมัติที่เลือก : เฉพาะ role md, admin (กันยิง endpoint ตรง)
        if (!in_array(Auth::user()->role, ['md', 'admin'])) {
            return response()->json(['success' => false, 'message' => 'คุณไม่มีสิทธิ์อนุมัติรายการ'], 403);
        }

        $request->validate([
            'order_ids'   => ['array'],
            'waiting_ids' => ['array'],
        ]);

        $orderIds   = $request->input('order_ids', []);
        $waitingIds = $request->input('waiting_ids', []);

        if (empty($orderIds) && empty($waitingIds)) {
            return response()->json(['success' => false, 'message' => 'กรุณาเลือกรายการที่จะอนุมัติ'], 422);
        }

        try {
            $count = DB::transaction(function () use ($orderIds, $waitingIds) {
                $approved = 0;

                // order ลูกค้า
                if (!empty($orderIds)) {
                    $approved += CarOrder::whereIn('id', $orderIds)
                        ->where('status', CarOrder::STATUS_PENDING)
                        ->update([
                            'status'        => CarOrder::STATUS_APPROVED,
                            'approved_by'   => Auth::id(),
                            'approver_date' => now(),
                        ]);
                }

                // waiting — อนุมัติตามจำนวนที่สั่ง (count_order)
                if (!empty($waitingIds)) {
                    $waitings = CarOrderWaiting::whereIn('id', $waitingIds)
                        ->where('status', 'pending')
                        ->get();

                    foreach ($waitings as $w) {
                        $this->createOrdersFromWaiting($w, (int) ($w->count_order ?? 0));
                        $approved++;
                    }
                }

                return $approved;
            });

            if ($count === 0) {
                return response()->json(['success' => false, 'message' => 'ไม่พบรายการที่อนุมัติได้'], 422);
            }

            return response()->json(['success' => true, 'message' => "อนุมัติเรียบร้อย {$count} รายการ"]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }

    public function editProcess($id)
    {
        $authUser = Auth::user();

        $order = CarOrder::findOrFail($id);
        $model = TbCarmodel::all();
        $subModels = TbSubcarmodel::where('model_id', $order->model_id)->get();
        $orderStatus = TbOrderStatus::all();
        $approvers = User::whereIn('role', ['audit', 'audit_lead', 'audit_dp', 'gm', 'md'])
            ->where('brand', $authUser->brand == 2 ? 2 : 1)
            ->get();

        return view('car-order.process.edit', compact('order', 'model', 'subModels', 'orderStatus', 'approvers'));
    }

    public function updateProcess(Request $request, $id)
    {
        try {
            $order = CarOrder::findOrFail($id);

            if ($request->action_status === 'approve') {
                $order->status = CarOrder::STATUS_APPROVED;
            } elseif ($request->action_status === 'reject') {

                if ($order->salecar_id) {
                    $this->unbindCarOrder($order);
                }

                $order->status = CarOrder::STATUS_REJECTED;
                $order->reason = $request->reason;
            }

            $order->approved_by = Auth::id();
            $order->approver_date = now();

            $order->save();

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

    // approve
    public function approve()
    {
        $approve = CarOrder::all();
        return view('car-order.approve.view', compact('approve'));
    }

    public function listApprove()
    {
        $orders = CarOrder::with('model', 'subModel')
            ->whereIn('status', ['approved', 'rejected'])
            ->where(function ($q) {
                $q->whereNull('waiting_id')
                    ->orWhere('status', 'rejected');
            })
            ->get();

        $waitings = CarOrderWaiting::with('model', 'subModel')
            ->whereIn('status', ['approved', 'rejected'])
            ->where(function ($q) {
                $q->whereNull('system_date')
                    ->orWhere('status', 'rejected');
            })
            ->get();

        $data = collect();

        foreach ($orders as $a) {
            $modelOrder    = $a->model ? $a->model->Name_TH : '';
            $subModelOrder = $a->subModel ? $a->subModel->name : '';
            $subDetail     = $a->subModel ? $a->subModel->detail : null;
            $subModelFull  = $subDetail ? "{$subDetail} - {$subModelOrder}" : $subModelOrder;

            if ($a->status === 'approved') {
                $statusBadge = '<span class="badge bg-label-success">อนุมัติ</span>';
            } else {
                $statusBadge = '<span class="badge bg-label-danger">ไม่อนุมัติ</span>';
            }

            $data->push([
                'No'         => 0,
                'date'       => $a->format_approver_date,
                'type'       => $a->type,
                'model_id'   => $modelOrder,
                'subModel_id' => $subModelFull,
                'color'      => $a->display_color,
                'cost'       => number_format($a->car_MSRP, 2),
                'status'     => $statusBadge,
                'Action'     => view('car-order.approve.button', compact('a'))->render(),
                '_sort_date' => $a->approver_date,
            ]);
        }

        foreach ($waitings as $w) {
            $modelOrder    = $w->model ? $w->model->Name_TH : '';
            $subModelOrder = $w->subModel ? $w->subModel->name : '';
            $subDetail     = $w->subModel ? $w->subModel->detail : null;
            $subModelFull  = $subDetail ? "{$subDetail} - {$subModelOrder}" : $subModelOrder;
            $colorDisplay  = $w->color ?? ($w->gwmColor ? $w->gwmColor->name : '-');

            if ($w->status === 'approved') {
                $statusBadgeWait = '<span class="badge bg-label-success">อนุมัติ</span><br><span class="badge bg-label-info mt-2">' . $w->received_order . ' คัน</span>';
                // 'status'     => '<span class="badge bg-label-success">อนุมัติ</span><br><span class="badge bg-label-info">' . $w->received_order . '/' . $w->count_order . ' คัน</span>';
            } else {
                $statusBadgeWait = '<span class="badge bg-label-danger">ไม่อนุมัติ</span>';
            }

            $data->push([
                'No'         => 0,
                'date'       => $w->format_approved_at,
                'type'       => $w->type,
                'model_id'   => $modelOrder,
                'subModel_id' => $subModelFull,
                'color'      => $colorDisplay,
                'cost'       => $w->car_MSRP ? number_format($w->car_MSRP, 2) : '-',
                'status'     => $statusBadgeWait,
                'Action'     => view('car-order.approve.button-waiting', compact('w'))->render(),
                '_sort_date' => $w->approved_at,
            ]);
        }

        $data = $data->sortByDesc('_sort_date')->values()->map(function ($item, $index) {
            unset($item['_sort_date']);
            $item['No'] = $index + 1;
            return $item;
        });

        return response()->json(['data' => $data]);
    }

    public function editApprove($id)
    {
        $authUser = Auth::user();

        $order = CarOrder::findOrFail($id);
        $model = TbCarmodel::all();
        $subModels = TbSubcarmodel::where('model_id', $order->model_id)->get();
        $orderStatus = TbOrderStatus::all();
        $approvers = User::whereIn('role', ['audit', 'audit_lead', 'audit_dp', 'gm', 'md'])
            ->where('brand', $authUser->brand == 2 ? 2 : 1)
            ->get();

        return view('car-order.approve.edit', compact('order', 'model', 'subModels', 'orderStatus', 'approvers'));
    }

    public function updateApprove(Request $request, $id)
    {
        $request->validate([
            'system_date' => 'required|date',
        ], [
            'system_date.required' => 'กรุณากรอกวันที่',
            'system_date.date' => 'รูปแบบวันที่ไม่ถูกต้อง',
        ]);

        try {
            $order = CarOrder::findOrFail($id);

            $order->update([
                'system_date' => $this->toGregorian($request->system_date),
                'order_status' => 2,
                'status' => CarOrder::STATUS_FINISHED
            ]);

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

    // กำหนดเงือนไขเลือกรถรุ่นหลัก
    public function getModelsByCustomer(Request $request)
    {
        $saleCar = Salecar::with([
            'reservationPayment',
            'remainingPayment'
        ])->findOrFail($request->salecar_id);

        // ใบจองเก่าที่ยังไม่ได้ระบุประเภทการซื้อ (ผ่อนชำระ/เงินสด) → แจ้งให้ไปแก้ไขใบจองก่อน
        if (!in_array($saleCar->payment_mode, ['finance', 'non-finance'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'ใบจองนี้ยังไม่ได้ระบุประเภทการซื้อ (ผ่อนชำระ/เงินสด) กรุณาแก้ไขใบจองก่อน'
            ]);
        }

        $cash = $saleCar->CashDeposit;
        $query = TbCarmodel::query();

        if ($saleCar->payment_mode === 'finance') {

            if (!$saleCar->reservationPayment) {
                return response()->json([
                    'success' => false,
                    'message' => 'ลูกค้าไม่มีข้อมูลเงินจอง'
                ]);
            }

            if (
                !$saleCar->remainingPayment ||
                empty($saleCar->remainingPayment->po_number)
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'ลูกค้าไม่มีข้อมูล PO Number'
                ]);
            }
        }

        // กรองรุ่นตามเงินจองขั้นต่ำ — รุ่นที่ไม่ได้ตั้ง money_min ถือว่าผ่านเงื่อนไข
        $query->where(function ($q) use ($cash) {
            $q->whereNull('money_min')
                ->orWhere('money_min', '<=', (float) $cash);
        });

        $models = $query->select('id', 'Name_TH')->get();

        if ($models->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'เงินจองไม่ถึงเงื่อนไขที่กำหนด'
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $models
        ]);
    }

    // waiting
    public function viewWaiting($id)
    {
        $waiting = CarOrderWaiting::with(['model', 'subModel', 'approvers', 'purchaseType', 'gwmColor', 'interiorColor'])->findOrFail($id);

        // สรุปสต็อกคงเหลือของรุ่นหลักเดียวกัน — แจกแจงตามรุ่นย่อย → สี (auto brand-scope)
        // ไม่นับสถานะ rejected และไม่นับคันที่ car_status = Delivered (ส่งมอบ = ขายไปแล้ว ไม่ใช่สต็อก)
        $modelOrders = CarOrder::with(['subModel', 'gwmColor'])
            ->where('model_id', $waiting->model_id)
            ->where('status', '!=', CarOrder::STATUS_REJECTED)
            ->where(function ($q) {
                $q->where('car_status', '!=', 'Delivered')->orWhereNull('car_status');
            })
            ->get();

        $subLabel = function ($o) {
            $sm = $o->subModel;
            if (!$sm) {
                return '-';
            }
            return $sm->detail ? ($sm->detail . ' - ' . $sm->name) : $sm->name;
        };

        $stockSummary = $modelOrders
            ->groupBy($subLabel)
            ->map(fn($group) => [
                'count' => $group->count(),
                'items' => $group
                    ->groupBy(fn($o) => ($o->year ?? '-') . '|' . $o->display_color)
                    ->map(fn($g) => [
                        'year'  => $g->first()->year ?? '-',
                        'color' => $g->first()->display_color,
                        'count' => $g->count(),
                    ])
                    ->sortBy([
                        ['year', 'desc'],
                        ['color', 'asc'],
                    ])
                    ->values(),
            ])
            ->sortKeys();
        $colorTotal = $modelOrders->count();

        return view('car-order.process.view-waiting', compact('waiting', 'stockSummary', 'colorTotal'));
    }

    public function editWaiting($id)
    {
        $authUser = Auth::user();

        $waiting = CarOrderWaiting::findOrFail($id);
        $purchaseType = TbPurchaseType::all();
        $approvers = User::whereIn('role', ['audit', 'audit_lead', 'audit_dp', 'gm', 'md'])
            ->where('brand', $authUser->brand == 2 ? 2 : 1)
            ->get();
        $gwmColor = $waiting->subModel
            ? $waiting->subModel->colors
            : collect();
        $interiorColor = TbInteriorColor::all();

        return view('car-order.pending.edit-waiting', compact('waiting', 'purchaseType', 'approvers', 'gwmColor', 'interiorColor'));
    }

    public function updateWaiting(Request $request, $id)
    {
        try {
            $waiting = CarOrderWaiting::findOrFail($id);

            $data = $request->except(['_token', '_method']);

            $data['car_DNP']  = $request->filled('car_DNP')  ? str_replace(',', '', $request->car_DNP)  : null;
            $data['car_MSRP'] = $request->filled('car_MSRP') ? str_replace(',', '', $request->car_MSRP) : null;
            $data['RI']       = $request->filled('RI')  ? str_replace(',', '', $request->RI)  : null;
            $data['WS']       = $request->filled('WS')  ? str_replace(',', '', $request->WS)  : null;

            $waiting->update($data);

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

    public function destroyWaiting($id)
    {
        try {
            CarOrderWaiting::findOrFail($id)->delete();

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

    public function editApproveWaiting($id)
    {
        $waiting = CarOrderWaiting::with(['model', 'subModel', 'approvers', 'purchaseType', 'gwmColor', 'interiorColor'])->findOrFail($id);

        return view('car-order.approve.edit-waiting', compact('waiting'));
    }

    public function updateApproveWaiting(Request $request, $id)
    {
        $request->validate([
            'system_date' => 'required|date',
        ], [
            'system_date.required' => 'กรุณากรอกวันที่',
            'system_date.date'     => 'รูปแบบวันที่ไม่ถูกต้อง',
        ]);

        try {
            $waiting = CarOrderWaiting::findOrFail($id);

            CarOrder::where('waiting_id', $id)->update([
                'system_date'  => $this->toGregorian($request->system_date),
                'order_status' => 2,
                'status'       => CarOrder::STATUS_FINISHED,
            ]);

            $waiting->update(['system_date' => $this->toGregorian($request->system_date)]);

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

    // รับทราบรายการที่ไม่อนุมัติ (rejected) — soft delete เพื่อเอาออกจากหน้าผลการอนุมัติ
    // เฉพาะ role: admin, audit, manager, md
    private const ACK_REJECT_ROLES = ['admin', 'audit', 'audit_lead', 'audit_dp', 'gm', 'manager', 'md'];

    public function acknowledgeReject($id)
    {
        if (!in_array(Auth::user()->role, self::ACK_REJECT_ROLES)) {
            return response()->json(['success' => false, 'message' => 'ไม่มีสิทธิ์ดำเนินการ'], 403);
        }

        try {
            $order = CarOrder::findOrFail($id);

            if ($order->status !== CarOrder::STATUS_REJECTED) {
                return response()->json(['success' => false, 'message' => 'รับทราบได้เฉพาะรายการที่ไม่อนุมัติ'], 422);
            }

            $order->delete();

            return response()->json(['success' => true, 'message' => 'รับทราบรายการเรียบร้อยแล้ว']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }

    public function acknowledgeRejectWaiting($id)
    {
        if (!in_array(Auth::user()->role, self::ACK_REJECT_ROLES)) {
            return response()->json(['success' => false, 'message' => 'ไม่มีสิทธิ์ดำเนินการ'], 403);
        }

        try {
            $waiting = CarOrderWaiting::findOrFail($id);

            if ($waiting->status !== 'rejected') {
                return response()->json(['success' => false, 'message' => 'รับทราบได้เฉพาะรายการที่ไม่อนุมัติ'], 422);
            }

            $waiting->delete();

            return response()->json(['success' => true, 'message' => 'รับทราบรายการเรียบร้อยแล้ว']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }

    public function viewExportStock()
    {
        return view('car-order.report.view');
    }

    public function exportStock(Request $request)
    {
        $fromDate = $request->from_date ?: now()->startOfMonth()->format('Y-m-d');
        $toDate   = $request->to_date   ?: now()->format('Y-m-d');

        return Excel::download(new CarOrderStockExport($fromDate, $toDate), 'ข้อมูลรับรถเข้า Stock.xlsx');
    }
}
