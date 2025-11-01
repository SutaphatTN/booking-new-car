<?php

namespace App\Http\Controllers\car_order;

use App\Http\Controllers\Controller;
use App\Models\CarOrder;
use App\Models\TbCarmodel;
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

            return [
                'No' => $index + 1,
                'model_id' => $modelOrder,
                'subModel_id' => $subModelOrder,
                'vinNo' => $c->vinNo,
                'order_status' => $c->order_status,
                'car_status' => $c->car_status,
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

    public function create()
    {
        $order = CarOrder::all();
        $model = TbCarmodel::all();
        return view('car-order.input', compact('order', 'model'));
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
                'vinNo' => $request->vinNo,
                'purchase_source' => $request->purchase_source,
                'order_code' => $order_code,
                'order_date' => $request->order_date,
                'color' => $request->color,
                'year' => $request->year,
                'purchase_type' => $request->purchase_type,
                'order_status' => $request->order_status,
                'order_invoice_date' => $request->order_invoice_date,
                'order_stock_date' => $request->viorder_stock_datenNo,
                'cancel_date' => $request->cancel_date ?? null,
                'car_DNP' => str_replace(',', '', $request->car_DNP ?: 0),
                'car_MSRP' => str_replace(',', '', $request->car_MSRP ?: 0),
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
        return view('car-order.edit', compact('order', 'model', 'subModels'));
    }

    public function update(Request $request, $id)
    {
        try {
            $order = CarOrder::findOrFail($id);
            $data = $request->except(['_token', '_method']);

            if ($request->car_DNP) {
                $data['car_DNP'] = str_replace(',', '', $request->car_DNP);
            }

            if ($request->car_MSRP) {
                $data['car_MSRP'] = str_replace(',', '', $request->car_MSRP);
            }

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
}
