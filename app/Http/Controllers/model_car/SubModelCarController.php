<?php

namespace App\Http\Controllers\model_car;

use App\Http\Controllers\Controller;
use App\Models\TbCarmodel;
use App\Models\TbCaroderType;
use App\Models\TbSubcarmodel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubModelCarController extends Controller
{
    public function index()
    {
        $sub = TbSubcarmodel::all();
        return view('car.sub-model-car.view', compact('sub'));
    }

    public function listSubCar()
    {
        $sub = TbSubcarmodel::with('model')->get();

        $data = $sub->map(function ($s, $index) {
            $modelS = $s->model ? $s->model->Name_TH : '';

            $statusSubCar = '
            <div class="d-flex justify-content-center align-items-center">
                <div class="form-check form-switch m-0">
                    <input class="form-check-input status-sub-car" 
                        type="checkbox"
                        id="status_sub_car_' . $s->id . '"
                        name="status_sub_car_' . $s->id . '"
                        data-id="' . $s->id . '"
                        ' . ($s->active === 'active' ? 'checked' : '') . '>
                </div>
            </div>
            ';

            return [
                'No' => $index + 1,
                'model_id' => $modelS,
                'name' => $s->name,
                'detail' => $s->detail,
                'active' => $statusSubCar,
                'Action' => view('car.sub-model-car.button', compact('s'))->render()
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function statusSubCar(Request $request)
    {
        $id = $request->id;
        $status = $request->status;

        $sub = TbSubcarmodel::find($id);
        if (!$sub) {
            return response()->json(['success' => false, 'message' => 'ไม่พบข้อมูล'], 404);
        }

        $sub->active = $status;
        $sub->save();

        return response()->json(['success' => true, 'message' => 'อัปเดตสถานะเรียบร้อยแล้ว']);
    }

    public function viewMore($id)
    {
        $sub = TbSubcarmodel::with(['model','typeCar'])->find($id);

        return view('car.sub-model-car.view-more', compact('sub'));
    }

    public function create()
    {
        $sub = TbSubcarmodel::all();
        $model = TbCarmodel::all();
        $typeCar = TbCaroderType::all();

        return view('car.sub-model-car.input', compact('sub', 'model', 'typeCar'));
    }

    function store(Request $request)
    {
        try {
            $active = 'active';

            $data = [
                'model_id' => $request->model_id,
                // 'code' => $request->code,
                'name' => $request->name,
                'detail' => $request->detail,
                'year' => $request->year,
                'active' => $active,
                'over_budget' => $request->filled('over_budget')
                    ? str_replace(',', '', $request->over_budget)
                    : null,
                'type_carOrder' => $request->type_carOrder,
                'userZone' => Auth::user()->userZone ?? null,
                'brand' => Auth::user()->brand ?? null,
            ];

            TbSubcarmodel::create($data);

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
        $sub = TbSubcarmodel::findOrFail($id);
        $model = TbCarmodel::all();
        $typeCar = TbCaroderType::all();
        return view('car.sub-model-car.edit', compact('sub', 'model', 'typeCar'));
    }

    public function update(Request $request, $id)
    {
        try {
            $sub = TbSubcarmodel::findOrFail($id);
            $data = $request->except(['_token', '_method']);

            $data['over_budget'] = $request->over_budget
                ? str_replace(',', '', $request->over_budget)
                : null;

            $sub->update($data);

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
            $sub = TbSubcarmodel::findOrFail($id);
            $sub->delete();

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
