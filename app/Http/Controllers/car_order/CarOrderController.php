<?php

namespace App\Http\Controllers\car_order;

use App\Http\Controllers\Controller;
use App\Models\CarOrder;
use App\Models\CarOrderHistory;
use App\Models\TbCarmodel;
use App\Models\TbOrderStatus;
use App\Models\TbSubcarmodel;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CarOrderController extends Controller
{
    public function index()
    {
        $order = CarOrder::all();
        return view('car-order.view', compact('order'));
    }

    public function listCarOrder()
    {
        $order = CarOrder::with('model', 'subModel')->get();

        $data = $order->map(function ($c, $index) {
            $modelOrder = $c->model ? $c->model->Name_TH : '';
            $subModelOrder = $c->subModel ? $c->subModel->name : '';
            $status = $c->orderStatus ? $c->orderStatus->name : '';

            return [
                'No' => $index + 1,
                'order_code' => $c->order_code,
                'model_id' => $modelOrder,
                'subModel_id' => $subModelOrder,
                'vin_number' => $c->vin_number,
                'order_status' => $status,
                'car_status' => $c->car_status,
                'Action' => view('car-order.button', compact('c'))->render()
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function history()
    {
        $history = CarOrderHistory::all();
        return view('car-order.history', compact('history'));
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

            return [
                'No' => $index + 1,
                'full_name' => $prefix . ' ' . $name . ' ' . $last,
                'order_code' => $orderCode,
                'model' => $model,
                'subModel' => $subModel,
                'booking' => $h->format_booking_date,
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

    public function create()
    {
        $order = CarOrder::all();
        $model = TbCarmodel::all();
        $orderStatus = TbOrderStatus::all();

        return view('car-order.input', compact('order', 'model', 'orderStatus'));
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
                'vin_number' => $request->vin_number,
                'option' => $request->option,
                'engine_number' => $request->engine_number,
                'purchase_source' => $request->purchase_source,
                'order_code' => $order_code,
                'order_date' => $request->order_date,
                'color' => $request->color,
                'year' => $request->year,
                'purchase_type' => $request->purchase_type,
                'order_status' => $request->order_status,
                'order_invoice_date' => $request->order_invoice_date,
                'order_stock_date' => $request->order_stock_date,
                'cancel_date' => $request->cancel_date ?? null,
                'car_DNP' => $request->filled('car_DNP')
                    ? str_replace(',', '', $request->car_DNP)
                    : null,
                'car_MSRP' => $request->filled('car_MSRP')
                    ? str_replace(',', '', $request->car_MSRP)
                    : null,
                'estimated_stock_date' => $request->estimated_stock_date,
                'stock_id' => $request->stock_id,
                'car_status' => $request->car_status,
                'userZone' => $request->userZone  ?? null,

            ];

            CarOrder::create($data);

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

    public function getSubModelCarOrder($model_id)
    {
        $subModels = TbSubcarmodel::where('model_id', $model_id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($subModels);
    }

    public function edit($id)
    {
        $order = CarOrder::findOrFail($id);
        $model = TbCarmodel::all();
        $subModels = TbSubcarmodel::where('model_id', $order->model_id)->get();
        $orderStatus = TbOrderStatus::all();

        return view('car-order.edit', compact('order', 'model', 'subModels', 'orderStatus'));
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
            ->where('car_status', 'Null')
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
}
