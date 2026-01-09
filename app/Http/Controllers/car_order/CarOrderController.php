<?php

namespace App\Http\Controllers\car_order;

use App\Http\Controllers\Controller;
use App\Mail\ApproveCarOrderMail;
use App\Models\CarOrder;
use App\Models\CarOrderHistory;
use App\Models\TbCarmodel;
use App\Models\TbOrderStatus;
use App\Models\TbSubcarmodel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class CarOrderController extends Controller
{
    // view all
    public function index()
    {
        $order = CarOrder::all();
        return view('car-order.view', compact('order'));
    }

    public function listCarOrder()
    {
        $order = CarOrder::with('model', 'subModel')
            ->where('status', 'finished')
            ->where('car_status', '!=', 'Delivered')
            ->get();

        $data = $order->map(function ($c, $index) {
            $modelOrder = $c->model ? $c->model->Name_TH : '';
            $subModelOrder = $c->subModel ? $c->subModel->name : '';
            $status = $c->orderStatus ? $c->orderStatus->name : '';

            $car = "หลัก : {$modelOrder}<br>ย่อย : {$subModelOrder}<br>สี : {$c->color}<br>ราคาขาย : " . number_format($c->car_MSRP);
            $statusDisplay = "รถ : {$status}<br>การจอง : {$c->car_status}";

            return [
                'No' => $index + 1,
                'date' => $c->format_system_date,
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
        ])->find($id);

        return view('car-order.view-more', compact('order'));
    }

    public function edit($id)
    {
        $order = CarOrder::findOrFail($id);
        $model = TbCarmodel::all();
        $subModels = TbSubcarmodel::where('model_id', $order->model_id)->get();
        $orderStatus = TbOrderStatus::all();
        $approvers = User::where('role', 'audit')->get();

        return view('car-order.edit', compact('order', 'model', 'subModels', 'orderStatus', 'approvers'));
    }

    public function update(Request $request, $id)
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

    function destroy($id)
    {
        try {
            $order = CarOrder::findOrFail($id);
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
        $keyword = $request->input('keyword');

        $order = CarOrder::with(['model', 'subModel', 'orderStatus'])
            ->where('car_status', 'Available')
            ->whereIn('status', ['approved', 'finished'])
            ->where(function ($query) use ($keyword) {
                $query->where('vin_number', 'like', "%{$keyword}%")
                    ->orWhere('order_code', 'like', "%{$keyword}%")
                    ->orWhere('option', 'like', "%{$keyword}%")
                    ->orWhere('color', 'like', "%{$keyword}%")
                    ->orWhere('year', 'like', "%{$keyword}%")
                    ->orWhereHas('model', function ($q) use ($keyword) {
                        $q->where('Name_TH', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('subModel', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
            })
            ->orderByRaw("CASE WHEN order_status = 5 THEN 0 ELSE 1 END")
            ->orderBy('order_stock_date', 'asc')
            ->limit(10)
            ->get();

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
        $order = CarOrder::with('model', 'subModel')
            ->where('status', 'pending')
            ->get();

        $data = $order->map(function ($p, $index) {
            $modelOrder = $p->model ? $p->model->Name_TH : '';
            $subModelOrder = $p->subModel ? $p->subModel->name : '';

            $subModelDisplay = "{$subModelOrder}<br>สี : {$p->color}<br>ราคาขาย : " . number_format($p->car_MSRP);

            return [
                'No' => $index + 1,
                'order_code' => $p->order_code,
                'date' => $p->format_order_date,
                'type' => $p->type,
                'model_id' => $modelOrder,
                'subModel_id' => $subModelDisplay,
                'Action' => view('car-order.pending.button', compact('p'))->render()
            ];
        });

        return response()->json(['data' => $data]);
    }


    public function create()
    {
        $order = CarOrder::all();
        $model = TbCarmodel::all();
        $orderStatus = TbOrderStatus::all();
        $approvers = User::where('role', 'audit')->get();

        return view('car-order.pending.input', compact('order', 'model', 'orderStatus', 'approvers'));
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

            $model = TbCarmodel::findOrFail($request->model_id);

            $prefix = "{$newOrderId}-{$OrderYear}-{$OrderMonth}-{$model->id}-";

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
                'option' => $request->option,
                'purchase_source' => $request->purchase_source,
                'order_code' => $order_code,
                'type' => $request->type,
                'order_date' => $request->order_date,
                'color' => $request->color,
                'year' => $request->year,
                'purchase_type' => $request->purchase_type,
                'order_status' => 1,
                'car_DNP' => $request->filled('car_DNP')
                    ? str_replace(',', '', $request->car_DNP)
                    : null,
                'car_MSRP' => $request->filled('car_MSRP')
                    ? str_replace(',', '', $request->car_MSRP)
                    : null,
                'car_status' => 'Available',
                'approver' => $request->approver,
                'note' => $request->note,
                'userZone' => $request->userZone ?? null,
                'status' => CarOrder::STATUS_PENDING,
            ];

            $order = CarOrder::create($data);

            $approverUser = User::find($request->approver);

            if ($approverUser && $approverUser->email) {
                Mail::to($approverUser->email)->send(new ApproveCarOrderMail($order));
            }

            return response()->json([
                'success' => true,
                'message' => 'เพิ่มข้อมูลเรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
            // return response()->json([
            //     'success' => false,
            //     'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'
            // ], 500);
        }
    }

    public function getSubModelCarOrder($model_id)
    {
        $subModels = TbSubcarmodel::where('model_id', $model_id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($subModels);
    }

    public function editPending($id)
    {
        $order = CarOrder::findOrFail($id);
        $model = TbCarmodel::all();
        $subModels = TbSubcarmodel::where('model_id', $order->model_id)->get();
        $orderStatus = TbOrderStatus::all();
        $approvers = User::where('role', 'audit')->get();

        return view('car-order.pending.edit', compact('order', 'model', 'subModels', 'orderStatus', 'approvers'));
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
        $process = CarOrder::all();
        $openId = $request->query('open_id');

        return view('car-order.process.view', compact('process', 'openId'));
    }

    public function listProcess()
    {
        $order = CarOrder::with('model', 'subModel')
            ->where('status', 'pending')
            ->get();

        $data = $order->map(function ($p, $index) {
            $modelOrder = $p->model ? $p->model->Name_TH : '';
            $subModelOrder = $p->subModel ? $p->subModel->name : '';

            return [
                'No' => $index + 1,
                'date' => $p->format_order_date,
                'type' => $p->type,
                'model_id' => $modelOrder,
                'subModel_id' => $subModelOrder,
                'color' => $p->color,
                'cost' => number_format($p->car_MSRP, 2),
                'Action' => view('car-order.process.button', compact('p'))->render()
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function editProcess($id)
    {
        $order = CarOrder::findOrFail($id);
        $model = TbCarmodel::all();
        $subModels = TbSubcarmodel::where('model_id', $order->model_id)->get();
        $orderStatus = TbOrderStatus::all();
        $approvers = User::where('role', 'audit')->get();

        return view('car-order.process.edit', compact('order', 'model', 'subModels', 'orderStatus', 'approvers'));
    }

    public function updateProcess(Request $request, $id)
    {
        try {
            $order = CarOrder::findOrFail($id);

            if ($request->action_status === 'approve') {
                $order->status = CarOrder::STATUS_APPROVED;
            } elseif ($request->action_status === 'reject') {
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
        $order = CarOrder::with('model', 'subModel')
            ->whereIn('status', ['approved', 'rejected'])
            ->get();

        $data = $order->map(function ($a, $index) {
            $modelOrder = $a->model ? $a->model->Name_TH : '';
            $subModelOrder = $a->subModel ? $a->subModel->name : '';

            if ($a->status === 'approved') {
                $statusBadge = '<span class="badge bg-label-success">อนุมัติ</span>';
            } elseif ($a->status === 'rejected') {
                $statusBadge = '<span class="badge bg-label-danger">ไม่อนุมัติ</span>';
            } else {
                $statusBadge = '<span class="badge bg-label-secondary">' . $a->status . '</span>';
            }

            return [
                'No' => $index + 1,
                'date' => $a->format_approver_date,
                'type' => $a->type,
                'model_id' => $modelOrder,
                'subModel_id' => $subModelOrder,
                'color' => $a->color,
                'cost' => number_format($a->car_MSRP, 2),
                'status' => $statusBadge,
                'Action' => view('car-order.approve.button', compact('a'))->render()
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function editApprove($id)
    {
        $order = CarOrder::findOrFail($id);
        $model = TbCarmodel::all();
        $subModels = TbSubcarmodel::where('model_id', $order->model_id)->get();
        $orderStatus = TbOrderStatus::all();
        $approvers = User::where('role', 'audit')->get();

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
                'system_date' => $request->system_date,
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
}
