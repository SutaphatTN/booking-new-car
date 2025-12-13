<?php

namespace App\Http\Controllers\campaign;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\TbCampaignType;
use App\Models\TbCarmodel;
use App\Models\TbSubcarmodel;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index()
    {
        $cam = Campaign::all();
        return view('campaign.view', compact('cam'));
    }

    public function listCampaign()
    {
        $cam = Campaign::with('model', 'type')->get();

        $data = $cam->map(function ($c, $index) {
            $modelC = $c->model ? $c->model->Name_TH : '';
            $typeC = $c->type ? $c->type->name : '';

            $statusSwitch = '
                <div class="d-flex justify-content-center align-items-center">
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input status-cam"
                            type="checkbox"
                            id="status_cam_' . $c->id . '"
                            name="status_cam_' . $c->id . '"
                            data-id="' . $c->id . '"
                            ' . ($c->active === 'active' ? 'checked' : '') . '>
                    </div>
                </div>
            ';

            return [
                'No' => $index + 1,
                'model_id' => $modelC,
                'name' => $c->name,
                'campaign_type' => $typeC,
                'cashSupport_final' => $c->cashSupport_final !== null ? number_format($c->cashSupport_final, 2) : '-',
                'active' => $statusSwitch,
                'Action' => view('campaign.button', compact('c'))->render()
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function viewMore($id)
    {
        $cam = Campaign::with([
            'model',
            'subModel',
            'type',
        ])->find($id);

        return view('campaign.view-more', compact('cam'));
    }

    public function statusCam(Request $request)
    {
        $id = $request->id;
        $status = $request->status;

        $cam = Campaign::find($id);
        if (!$cam) {
            return response()->json(['success' => false, 'message' => 'ไม่พบข้อมูล'], 404);
        }

        $cam->active = $status;
        $cam->save();

        return response()->json(['success' => true, 'message' => 'อัปเดตสถานะเรียบร้อยแล้ว']);
    }

    public function create()
    {
        $cam = Campaign::all();
        $model = TbCarmodel::all();
        $type = TbCampaignType::all();
        return view('campaign.input', compact('cam', 'model', 'type'));
    }

    function store(Request $request)
    {
        try {
            $active = 'active';

            $data = [
                'model_id' => $request->model_id,
                'subModel_id' => $request->subModel_id,
                'name' => $request->name,
                'campaign_type' => $request->campaign_type,
                'cashSupport' => $request->filled('cashSupport')
                    ? str_replace(',', '', $request->cashSupport)
                    : null,
                'cashSupport_deduct' => $request->filled('cashSupport_deduct')
                    ? str_replace(',', '', $request->cashSupport_deduct)
                    : null,
                'cashSupport_final' => $request->filled('cashSupport_final')
                    ? str_replace(',', '', $request->cashSupport_final)
                    : null,
                'userZone' => $request->userZone  ?? null,
                'startDate' => $request->startDate,
                'endDate' => $request->endDate,
                'active' => $active,
            ];

            Campaign::create($data);

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

    public function getSubModelCam($model_id)
    {
        $subModels = TbSubcarmodel::where('model_id', $model_id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($subModels);
    }

    public function edit($id)
    {
        $cam = Campaign::findOrFail($id);
        $model = TbCarmodel::all();
        $subModels = TbSubcarmodel::where('model_id', $cam->model_id)->get();
        $type = TbCampaignType::all();
        return view('campaign.edit', compact('cam', 'model', 'subModels', 'type'));
    }

    public function update(Request $request, $id)
    {
        try {
            $cam = Campaign::findOrFail($id);
            $data = $request->except(['_token', '_method']);

            $data['cashSupport'] = $request->cashSupport
                ? str_replace(',', '', $request->cashSupport)
                : null;

            $data['cashSupport_deduct'] = $request->cashSupport_deduct
                ? str_replace(',', '', $request->cashSupport_deduct)
                : null;

            $data['cashSupport_final'] = $request->cashSupport_final
                ? str_replace(',', '', $request->cashSupport_final)
                : null;

            $cam->update($data);

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
            $cam = Campaign::findOrFail($id);
            $cam->delete();

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
