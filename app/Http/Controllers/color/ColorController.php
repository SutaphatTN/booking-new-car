<?php

namespace App\Http\Controllers\color;

use App\Http\Controllers\Controller;
use App\Models\ColorSubmodel;
use App\Models\TbCarmodel;
use App\Models\TbColor;
use App\Models\TbSubcarmodel;
use Illuminate\Http\Request;

class ColorController extends Controller
{
    public function index()
    {
        return view('car.color.view');
    }

    public function listColor()
    {
        $colorSub = ColorSubmodel::with([
            'subModel.model',
            'gwmColor'
        ])->get();

        $data = $colorSub->map(function ($c, $index) {
            $modelC = $c->subModel?->model?->Name_TH ?? '-';
            $subModelSale = $c->subModel ? $c->subModel->name : '';
            $subDetail = $c->subModel ? $c->subModel->detail : '';
            $subModelData = "{$subModelSale}<br>{$subDetail}";

            return [
                'No' => $index + 1,
                'model' => $modelC,
                'submodel' => $subModelData ?? '-',
                'color' => $c->gwmColor?->name ?? '-',
                'Action' => view('car.color.button', compact('c'))->render()
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function create()
    {
        $colorSub = ColorSubmodel::all();
        $model = TbCarmodel::all();
        $subModels = TbSubcarmodel::all();
        $gwmColor = TbColor::all();
        return view('car.color.input', compact('colorSub', 'model', 'subModels', 'gwmColor'));
    }

    public function store(Request $request)
    {
        try {

            $subModel = TbSubcarmodel::findOrFail(
                $request->input('subcarmodel_id')
            );

            $subModel->colors()
                ->syncWithoutDetaching(
                    $request->input('color_id')
                );

            return response()->json([
                'success' => true,
                'message' => 'เพิ่มข้อมูลเรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'
            ], 500);
            // return response()->json([
            //     'success' => false,
            //     'message' => $e->getMessage()
            // ], 500);
        }
    }

    // public function edit($id)
    // {
    //     $colorSub = ColorSubmodel::findOrFail($id);
    //     return view('car.color.edit', compact('colorSub'));
    // }

    // public function update(Request $request, $id)
    // {
    //     try {
    //         $colorSub = ColorSubmodel::findOrFail($id);
    //         $data = $request->except(['_token', '_method']);

    //         $colorSub->update($data);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'แก้ไขข้อมูลเรียบร้อยแล้ว'
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'
    //         ], 500);
    //     }
    // }

    function destroy($id)
    {
        try {
            $colorSub = ColorSubmodel::findOrFail($id);
            $colorSub->delete();

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

    public function getSubModelColorSub($model_id)
    {
        $subModels = TbSubcarmodel::where('model_id', $model_id)
            ->select('id', 'name', 'detail')
            ->orderBy('name')
            ->get();

        return response()->json($subModels);
    }
}
