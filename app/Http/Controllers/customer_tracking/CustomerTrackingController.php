<?php

namespace App\Http\Controllers\customer_tracking;

use App\Exports\customerTracking\CustomerTrackingExport;
use App\Http\Controllers\Controller;
use App\Models\CustomerTracking;
use App\Models\CustomerTrackingDetail;
use App\Models\Salecar;
use App\Models\TbCarmodel;
use App\Models\TbDecision;
use App\Models\TbSalecarType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class CustomerTrackingController extends Controller
{
    public function index()
    {
        return view('customer-tracking.view');
    }

    public function list(Request $request)
    {
        $user = Auth::user();

        // customer_id ที่มีการจองแล้ว (ไม่ถูก soft-delete และยังไม่ถอนจอง ยังไม่ส่งมอบ)
        $bookedCustomerIds = Salecar::whereNull('deleted_at')
            ->whereNull('CancelDate')
            ->whereNull('DeliveryDate')
            ->pluck('CusID')
            ->unique()
            ->toArray();

        $query = CustomerTracking::with(['customer.prefix', 'sale', 'source', 'model', 'subModel', 'latestDetail.decision', 'wuColor'])
            ->whereNotIn('customer_id', $bookedCustomerIds);

        if ($user->role === 'sale') {
            $query->where('sale_id', $user->id);
        }

        $trackings = $query->get();

        $no = 1;
        $data = $trackings->map(function ($t) use (&$no) {
            $customer = $t->customer;
            $fullName = $customer
                ? (($customer->prefix->Name_TH ?? '') . ' ' . $customer->FirstName . ' ' . $customer->LastName)
                : '-';

            $model = $t->model->Name_TH ?? '-';
            $subMo = $t->subModel->name ?? '-';
            $color = $t->wuColor->name ?? '-';
            $car = "หลัก : {$model}<br>ย่อย : {$subMo}<br>สี : {$color}";

            $latestDetail = $t->latestDetail;
            $source = $t->source->name ?? '-';
            $contact_date = $latestDetail?->contact_date ?? '-';
            $decision = $latestDetail?->decision?->name ?? '-';
            $detail = "ที่มา : {$source}<br>วันที่ติดต่อล่าสุด : {$contact_date}<br>การตัดสินใจ : {$decision}";

            return [
                'No'           => $no++,
                'id'           => $t->id,
                'FullName'     => trim($fullName),
                'model'        => $car,
                'sale'         => $t->sale->name ?? '-',
                'detail'       => $detail,
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function create()
    {
        $authUser = Auth::user();
        $model    = TbCarmodel::where('brand', $authUser->brand)->get();
        $sources  = TbSalecarType::all();
        $decisions = TbDecision::all();
        $saleUser = User::where('role', 'sale')->where('brand', $authUser->brand)->get();

        return view('customer-tracking.input', compact('model', 'sources', 'decisions', 'saleUser'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'    => 'required|integer',
            'sale_id'        => 'required|integer',
            'source_id'      => 'required|integer',
            'contact_date'   => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            $authUser = Auth::user();

            $tracking = CustomerTracking::create([
                'sale_id'     => $request->sale_id,
                'customer_id' => $request->customer_id,
                'source_id'   => $request->source_id,
                'model_id'    => $request->model_id ?: null,
                'sub_model_id' => $request->sub_model_id ?: null,
                'year'        => $request->year ?: null,
                'color_id'    => $request->color_id ?: null,
                'userZone'   => $authUser->userZone,
                'brand'       => $authUser->brand,
                'branch'      => $authUser->branch,
                'UserInsert' => $authUser->id,
            ]);

            CustomerTrackingDetail::create([
                'tracking_id'    => $tracking->id,
                'contact_date'   => $request->contact_date,
                'comment_sale'   => $request->comment_sale,
                'decision_id'    => $request->decision_id,
                'contact_status' => $request->contact_status,
                'UserInsert'    => $authUser->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'เพิ่มข้อมูลเรียบร้อยแล้ว'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

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

    public function show($id)
    {
        $tracking = CustomerTracking::with([
            'customer.prefix',
            'sale',
            'source',
            'model',
            'subModel',
            'details.decision',
        ])->findOrFail($id);

        $decisions = TbDecision::all();

        return view('customer-tracking.view-more', compact('tracking', 'decisions'));
    }

    public function addDetail(Request $request, $id)
    {
        $request->validate([
            'contact_date'   => 'required|date',
            'contact_status' => 'required|in:1,0',
        ]);

        CustomerTrackingDetail::create([
            'tracking_id'    => $id,
            'contact_date'   => $request->contact_date,
            'contact_status' => $request->contact_status,
            'decision_id'    => $request->decision_id ?: null,
            'comment_sale'   => $request->comment_sale,
            'UserInsert'    => Auth::id(),
        ]);

        return response()->json(['success' => true]);
    }

    public function report()
    {
        return view('customer-tracking.report');
    }

    public function exportExcel()
    {
        return Excel::download(new CustomerTrackingExport(), 'รายงานการติดตามลูกค้า.xlsx');
    }

    public function destroy($id)
    {
        CustomerTracking::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
