<?php

namespace App\Http\Controllers\model_car;

use App\Http\Controllers\Controller;
use App\Models\TbCarmodel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ModelCarController extends Controller
{
    public function index()
    {
        $car = TbCarmodel::all();
        return view('car.model-car.view', compact('car'));
    }

    public function listCar()
    {
        $car = TbCarmodel::all();

        $data = $car->map(function ($c, $index) {

            return [
                'No' => $index + 1,
                'Name_TH' => $c->Name_TH,
                'Name_EN' => $c->Name_EN,
                'initials' => $c->initials,
                'over_budget' => $c->over_budget !== null ? number_format($c->over_budget, 2) : '-',
                'money_min' => $c->money_min !== null ? number_format($c->money_min, 2) : '-',
                'Action' => view('car.model-car.button', compact('c'))->render()
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function create()
    {
        $car = TbCarmodel::all();
        return view('car.model-car.input', compact('car'));
    }

    function store(Request $request)
    {
        try {

            $data = [
                'Name_TH' => $request->Name_TH,
                'Name_EN' => $request->Name_EN,
                'initials' => $request->initials,
                'Active' => 'active',
                'over_budget' => $request->filled('over_budget')
                    ? str_replace(',', '', $request->over_budget)
                    : null,
                'money_min' => $request->filled('money_min')
                    ? str_replace(',', '', $request->money_min)
                    : null,
                'userZone' => Auth::user()->userZone ?? null,
                'brand' => Auth::user()->brand ?? null,
            ];

            TbCarmodel::create($data);

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
        $car = TbCarmodel::findOrFail($id);
        return view('car.model-car.edit', compact('car'));
    }

    public function update(Request $request, $id)
    {
        try {
            $car = TbCarmodel::findOrFail($id);
            $data = $request->except(['_token', '_method']);

            $data['over_budget'] = $request->over_budget
                ? str_replace(',', '', $request->over_budget)
                : null;

            $data['money_min'] = $request->money_min
                ? str_replace(',', '', $request->money_min)
                : null;

            $car->update($data);

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
            $car = TbCarmodel::findOrFail($id);
            $car->delete();

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
