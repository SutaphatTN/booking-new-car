<?php

namespace App\Http\Controllers\service_check_tracking;

use App\Http\Controllers\Controller;
use App\Models\ServiceCheckTracking;
use App\Models\ServiceCheckTrackingDetail;
use App\Models\Salecar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceCheckTrackingController extends Controller
{
    public function index()
    {
        return view('service-check-tracking.list');
    }

    public function list(Request $request)
    {
        $trackings = ServiceCheckTracking::with([
            'customer.prefix',
            'salecar.model',
            'salecar.gwmColor',
            'carOrder',
            'latestDetail',
        ])->get();

        $no = 1;
        $data = $trackings->map(function ($t) use (&$no) {
            $c = $t->customer;
            $fullName = $c
                ? (($c->prefix->Name_TH ?? '') . ' ' . $c->FirstName . ' ' . $c->LastName)
                : '-';

            $salecar = $t->salecar;
            $model   = $salecar?->model?->Name_TH ?? '-';
            $color   = $salecar?->gwmColor?->name ?? $salecar?->Color ?? '-';
            $vin     = $t->carOrder?->vin_number ?? '-';
            $delivery = $salecar?->getFormatDeliveryDateAttribute() ?? '-';

            $latestDetail = $t->latestDetail;
            $lastCheck    = $latestDetail?->format_check_date ?? '-';
            $lastMileage  = $latestDetail ? number_format($latestDetail->mileage) . ' กม.' : '-';

            return [
                'No'         => $no++,
                'id'         => $t->id,
                'FullName'   => trim($fullName),
                'model'      => "{$model}<br><small class=\"text-muted\">{$color}</small>",
                'vin'        => $vin,
                'delivery'   => $delivery,
                'last_check' => "{$lastCheck}<br><small class=\"text-muted\">{$lastMileage}</small>",
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function create()
    {
        return view('service-check-tracking.create');
    }

    public function searchSalecar(Request $request)
    {
        $keyword = trim($request->input('keyword', ''));

        if ($keyword === '') {
            return response()->json([]);
        }

        $existingIds = ServiceCheckTracking::whereNull('deleted_at')->pluck('salecar_id')->toArray();

        $salecars = Salecar::with(['customer.prefix', 'carOrder', 'model', 'gwmColor'])
            ->where('con_status', 5)
            ->where(function ($q) use ($keyword) {
                $q->whereHas('customer', function ($q2) use ($keyword) {
                    $q2->where('FirstName', 'like', "%{$keyword}%")
                        ->orWhere('LastName', 'like', "%{$keyword}%")
                        ->orWhere('Mobilephone1', 'like', "%{$keyword}%");
                })->orWhereHas('carOrder', function ($q2) use ($keyword) {
                    $q2->where('vin_number', 'like', "%{$keyword}%");
                });
            })
            ->get();

        $data = $salecars->map(function ($s) use ($existingIds) {
            $c        = $s->customer;
            $fullName = $c
                ? (($c->prefix->Name_TH ?? '') . ' ' . $c->FirstName . ' ' . $c->LastName)
                : '-';

            return [
                'salecar_id'   => $s->id,
                'customer_id'  => $s->CusID,
                'car_order_id' => $s->CarOrderID,
                'FullName'     => trim($fullName),
                'mobile'       => $c?->formatted_mobile ?? '-',
                'model'        => $s->model?->Name_TH ?? '-',
                'color'        => $s->gwmColor?->name ?? $s->Color ?? '-',
                'vin_number'   => $s->carOrder?->vin_number ?? '-',
                'delivery'     => $s->getFormatDeliveryDateAttribute() ?? '-',
                'already_added' => in_array($s->id, $existingIds),
            ];
        });

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'salecar_id'   => 'required|integer',
            'customer_id'  => 'required|integer',
            'car_order_id' => 'nullable|integer',
        ]);

        $exists = ServiceCheckTracking::whereNull('deleted_at')
            ->where('salecar_id', $request->salecar_id)
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'ลูกค้าคนนี้อยู่ในรายการติดตามแล้ว'], 422);
        }

        try {
            ServiceCheckTracking::create([
                'salecar_id'   => $request->salecar_id,
                'customer_id'  => $request->customer_id,
                'car_order_id' => $request->car_order_id ?: null,
                'UserInsert'   => Auth::id(),
            ]);

            return response()->json(['success' => true, 'message' => 'เพิ่มการติดตามเรียบร้อยแล้ว']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาติดต่อแอดมิน'], 500);
        }
    }

    public function show($id)
    {
        $tracking = ServiceCheckTracking::with([
            'customer.prefix',
            'salecar.model',
            'salecar.subModel',
            'salecar.gwmColor',
            'carOrder',
            'details',
        ])->findOrFail($id);

        return view('service-check-tracking.view-more', compact('tracking'));
    }

    public function addDetail(Request $request, $id)
    {
        $request->validate([
            'check_date' => 'required|date',
            'mileage'    => 'required|integer|min:0',
        ]);

        ServiceCheckTrackingDetail::create([
            'tracking_id' => $id,
            'check_date'  => $request->check_date,
            'mileage'     => $request->mileage,
            'note'        => $request->note,
            'UserInsert'  => Auth::id(),
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        ServiceCheckTracking::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
