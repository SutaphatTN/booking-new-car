<?php

namespace App\Http\Controllers\pricelist_car;

use App\Http\Controllers\Controller;
use App\Models\TbCarmodel;
use App\Models\TbPricelistCar;
use App\Models\TbSubcarmodel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PricelistCarController extends Controller
{
    public function index()
    {
        return view('car.pricelist-car.view');
    }

    public function listPricelist()
    {
        $prices = TbPricelistCar::all();
        $userBrand = Auth::user()->brand;

        $modelIds    = $prices->pluck('model_id')->unique()->filter();
        $subModelIds = $prices->pluck('subModel_id')->unique()->filter();

        $models    = TbCarmodel::whereIn('id', $modelIds)->pluck('Name_TH', 'id');
        $subModels = TbSubcarmodel::whereIn('id', $subModelIds)->pluck('name', 'id');

        $data = $prices->map(function ($p, $index) use ($models, $subModels, $userBrand) {
            $hide = in_array($userBrand, [2, 3]);

            $model_id = $models[$p->model_id] ?? '-';
            $subModel_id = $subModels[$p->subModel_id] ?? '-';
            $car = "รหลัก : {$model_id}<br>ย่อย : {$subModel_id}";

            return [
                'No'         => $index + 1,
                'car'   => $car,
                'option' => $hide ? '-' : ($p->option ?? '-'),
                'year'       => $p->year ?? '-',
                'color'  => $hide ? '-' : ($p->color ?? '-'),
                'dnp'        => $p->dnp !== null ? number_format($p->dnp, 2) : '-',
                'msrp'       => $p->msrp !== null ? number_format($p->msrp, 2) : '-',
                'dm' => $hide ? '-' : ($p->dm !== null ? number_format($p->dm, 2) : '-'),
                'ri' => $hide ? '-' : ($p->ri !== null ? number_format($p->ri, 2) : '-'),
                'ws' => $hide ? '-' : ($p->ws !== null ? number_format($p->ws, 2) : '-'),
                'Action'     => view('car.pricelist-car.button', compact('p'))->render(),
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function create()
    {
        $models = TbCarmodel::all();
        $brand  = Auth::user()->brand ?? null;

        return view('car.pricelist-car.input', compact('models', 'brand'));
    }

    public function getSubModel($model_id)
    {
        $subModels = TbSubcarmodel::where('model_id', $model_id)
            ->select('id', 'name', 'detail')
            ->orderBy('name')
            ->get();

        return response()->json($subModels);
    }

    public function store(Request $request)
    {
        try {
            $user  = Auth::user();
            $brand = $user->brand ?? null;

            $data = [
                'model_id'    => $request->model_id,
                'subModel_id' => $request->subModel_id,
                'year'        => $request->year,
                'dnp'         => $request->filled('dnp') ? str_replace(',', '', $request->dnp) : null,
                'msrp'        => $request->filled('msrp') ? str_replace(',', '', $request->msrp) : null,
                'brand'       => $brand,
                'userZone'    => $user->userZone ?? null,
                'userInsert'  => $user->id ?? null,
                'branch'      => $user->branch ?? null,
            ];

            if ($brand == 1) {
                $data['option'] = $request->option;
                $data['color']  = $request->color;
                $data['dm']     = $request->filled('dm') ? str_replace(',', '', $request->dm) : null;
                $data['ri']     = $request->filled('ri') ? str_replace(',', '', $request->ri) : null;
                $data['ws']     = $request->filled('ws') ? str_replace(',', '', $request->ws) : null;
            }

            TbPricelistCar::create($data);

            return response()->json([
                'success' => true,
                'message' => 'เพิ่มข้อมูลเรียบร้อยแล้ว',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน',
            ], 500);
        }
    }

    public function edit($id)
    {
        $price    = TbPricelistCar::findOrFail($id);
        $models   = TbCarmodel::all();
        $subModels = TbSubcarmodel::where('model_id', $price->model_id)
            ->select('id', 'name', 'detail')
            ->orderBy('name')
            ->get();
        $brand = Auth::user()->brand ?? null;

        return view('car.pricelist-car.edit', compact('price', 'models', 'subModels', 'brand'));
    }

    public function update(Request $request, $id)
    {
        try {
            $price = TbPricelistCar::findOrFail($id);
            $brand = Auth::user()->brand ?? null;

            $data = [
                'model_id'    => $request->model_id,
                'subModel_id' => $request->subModel_id,
                'year'        => $request->year,
                'dnp'         => $request->filled('dnp') ? str_replace(',', '', $request->dnp) : null,
                'msrp'        => $request->filled('msrp') ? str_replace(',', '', $request->msrp) : null,
            ];

            if ($brand == 1) {
                $data['option'] = $request->option;
                $data['color']  = $request->color;
                $data['dm']     = $request->filled('dm') ? str_replace(',', '', $request->dm) : null;
                $data['ri']     = $request->filled('ri') ? str_replace(',', '', $request->ri) : null;
                $data['ws']     = $request->filled('ws') ? str_replace(',', '', $request->ws) : null;
            }

            $price->update($data);

            return response()->json([
                'success' => true,
                'message' => 'แก้ไขข้อมูลเรียบร้อยแล้ว',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน',
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $price = TbPricelistCar::findOrFail($id);
            $price->delete();

            return response()->json([
                'success' => true,
                'message' => 'ลบข้อมูลเรียบร้อยแล้ว',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน',
            ], 500);
        }
    }
}
