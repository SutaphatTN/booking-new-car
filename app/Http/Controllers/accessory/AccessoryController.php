<?php

namespace App\Http\Controllers\accessory;

use App\Http\Controllers\Controller;
use App\Models\AccessoryPartner;
use App\Models\AccessoryPrice;
use App\Models\AccessoryType;
use App\Models\TbCarmodel;
use App\Models\TbSubcarmodel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AccessoryController extends Controller
{
    public function index()
    {
        $acc = AccessoryPrice::all();
        return view('accessory.view', compact('acc'));
    }

    public function listAccessory()
    {
        $acc = AccessoryPrice::with('partner')->get();

        $data = $acc->map(function ($a, $index) {
            $partnerA = $a->partner ? $a->partner->name : '';

            $statusSwitch = '
            <div class="d-flex justify-content-center align-items-center">
                <div class="form-check form-switch m-0">
                    <input class="form-check-input status-acc" type="checkbox"
                        data-id="' . $a->id . '"
                        ' . ($a->active === 'active' ? 'checked' : '') . '>
                </div>
            </div>
            ';

            return [
                'No' => $index + 1,
                'accessoryPartner_id' => $partnerA,
                'accessory_id' => $a->accessory_id,
                'cost' => $a->cost,
                'sale' => $a->sale,
                'promo' => $a->promo,
                'active' => $statusSwitch,
                'Action' => view('accessory.button', compact('a'))->render()
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function statusAcc(Request $request)
    {
        $id = $request->id;
        $status = $request->status;

        $acc = AccessoryPrice::find($id);
        if (!$acc) {
            return response()->json(['success' => false, 'message' => 'ไม่พบข้อมูล'], 404);
        }

        $acc->active = $status;
        $acc->save();

        return response()->json(['success' => true, 'message' => 'อัปเดตสถานะเรียบร้อยแล้ว']);
    }

    public function create()
    {
        $acc = AccessoryPrice::all();
        $model = TbCarmodel::all();
        $partner = AccessoryPartner::all();
        $type = AccessoryType::all();
        return view('accessory.input', compact('acc', 'model', 'partner', 'type'));
    }

    public function getSubModelAcc($model_id)
    {
        $subModels = TbSubcarmodel::where('model_id', $model_id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($subModels);
    }

    function store(Request $request)
    {
        try {
            $active = 'active';

            $data = [
                'model_id' => $request->model_id,
                'subModel_id' => $request->subModel_id,
                'accessory_id' => $request->accessory_id,
                'detail' => $request->detail,
                'accessoryType_id' => $request->accessoryType_id,
                'accessoryPartner_id' => $request->accessoryPartner_id,
                'cost' => str_replace(',', '', $request->cost ?: 0),
                'sale' => str_replace(',', '', $request->sale ?: 0),
                'comSale' => str_replace(',', '', $request->comSale ?: 0),
                'promo' => str_replace(',', '', $request->promo ?: 0),
                'userZone' => $request->userZone  ?? null,
                'startDate' => $request->startDate,
                'endDate' => $request->endDate,
                'active' => $active,
            ];

            AccessoryPrice::create($data);

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

    public function viewMore($id)
    {
        $acc = AccessoryPrice::with([
            'model',
            'subModel',
            'type',
            'partner',
        ])->find($id);

        return view('accessory.view-more', compact('acc'));
    }

    public function edit($id)
    {
        $acc = AccessoryPrice::findOrFail($id);
        $model = TbCarmodel::all();
        $subModels = TbSubcarmodel::where('model_id', $acc->model_id)->get();
        $partner = AccessoryPartner::all();
        $type = AccessoryType::all();
        return view('accessory.edit', compact('acc', 'model', 'subModels', 'partner', 'type'));
    }

    public function update(Request $request, $id)
    {
        try {
            $acc = AccessoryPrice::findOrFail($id);
            $data = $request->except(['_token', '_method']);

            if ($request->cost) {
                $data['cost'] = str_replace(',', '', $request->cost);
            }

            if ($request->sale) {
                $data['sale'] = str_replace(',', '', $request->sale);
            }

            if ($request->comSale) {
                $data['comSale'] = str_replace(',', '', $request->comSale);
            }

            if ($request->promo) {
                $data['promo'] = str_replace(',', '', $request->promo);
            }
            $acc->update($data);

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
            $acc = AccessoryPrice::findOrFail($id);
            $acc->delete();

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

    //partner
    public function viewPartner()
    {
        $partner = AccessoryPartner::all();
        return view('accessory.partner.view', compact('partner'));
    }

    public function listPartner()
    {
        $partner = AccessoryPartner::all();

        $data = $partner->map(function ($p, $index) {

            return [
                'No' => $index + 1,
                'name' => $p->name,
                'Action' => view('accessory.partner.button', compact('p'))->render()
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function createPartner()
    {
        $partner = AccessoryPartner::all();
        return view('accessory.partner.input', compact('partner'));
    }

    function storePartner(Request $request)
    {
        try {

            $data = [
                'name' => $request->name,
            ];

            AccessoryPartner::create($data);

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

    public function editPartner($id)
    {
        $partner = AccessoryPartner::findOrFail($id);
        return view('accessory.partner.edit', compact('partner'));
    }

    public function updatePartner(Request $request, $id)
    {
        try {
            $partner = AccessoryPartner::findOrFail($id);
            $data = $request->except(['_token', '_method']);
            $partner->update($data);

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

    function destroyPartner($id)
    {
        try {
            $partner = AccessoryPartner::findOrFail($id);
            $partner->delete();

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
