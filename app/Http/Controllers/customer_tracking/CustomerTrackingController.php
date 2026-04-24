<?php

namespace App\Http\Controllers\customer_tracking;

use App\Exports\customerTracking\CustomerTrackingExport;
use App\Http\Controllers\Controller;
use App\Models\CustomerTracking;
use App\Models\CustomerTrackingDetail;
use App\Models\Salecar;
use App\Models\TbCarmodel;
use App\Models\TbDecision;
use App\Models\TbInteriorColor;
use App\Models\TbSalecarType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class CustomerTrackingController extends Controller
{
    public function index()
    {
        $decisions = TbDecision::all();
        return view('customer-tracking.view', compact('decisions'));
    }

    public function list(Request $request)
    {
        $user = Auth::user();

        // customer_id ที่มีการจองแล้วใน brand เดียวกัน (ไม่ถูก soft-delete และยังไม่ถอนจอง ยังไม่ส่งมอบ)
        $bookedCustomerIds = Salecar::whereNull('deleted_at')
            ->whereNull('CancelDate')
            ->whereNull('DeliveryDate')
            ->where('brand', $user->brand)
            ->pluck('CusID')
            ->unique()
            ->toArray();

        $query = CustomerTracking::with(['customer.prefix', 'sale', 'source', 'model', 'subModel', 'latestDetail.decision', 'nextManagerDetail', 'latestManagerDetail', 'wuColor'])
            ->whereNotIn('customer_id', $bookedCustomerIds)
            ->whereNull('cancelled_at');

        if ($user->role === 'sale') {
            $query->where('sale_id', $user->id);
        }

        $trackings = $query->get()->sortBy(function ($t) {
            return $t->nextManagerDetail?->contact_date
                ?? $t->latestManagerDetail?->contact_date
                ?? $t->latestDetail?->contact_date
                ?? '9999-12-31';
        })->values();

        $no = 1;
        $data = $trackings->map(function ($t) use (&$no) {
            $customer = $t->customer;
            $fullName = $customer
                ? (($customer->prefix->Name_TH ?? '') . ' ' . $customer->FirstName . ' ' . $customer->LastName)
                : '-';

            $model = $t->model->Name_TH ?? '-';
            $subMo = $t->subModel->name ?? '-';
            $subDetail = $t->subModel ? $t->subModel->detail : '';
            $color = $t->wuColor->name ?? '-';
            $colorText = $t->color_text ?? '-';
            if ($t->brand == 2 || $t->brand == 3) {
                $car = "รุ่นหลัก : {$model}<br>รุ่นย่อย : {$subMo}<br>สี : {$color}";
            } else {
                $car = "รุ่นหลัก : {$model}<br>รุ่นย่อย : {$subMo}<br>{$subDetail}<br>สี : {$colorText}";
            }

            $latestDetail = $t->latestDetail;
            $source = $t->source->name ?? '-';

            if ($t->nextManagerDetail) {
                $dateLabel    = 'วันที่ติดต่อครั้งถัดไป';
                $dateValue    = $t->nextManagerDetail->format_contact_date;
                $activeDetail = $t->nextManagerDetail;
            } elseif ($t->latestManagerDetail) {
                $dateLabel    = 'วันที่ติดต่อล่าสุด';
                $dateValue    = $t->latestManagerDetail->format_contact_date;
                $activeDetail = $t->latestManagerDetail;
            } else {
                $dateLabel    = 'วันที่ติดต่อล่าสุด';
                $dateValue    = $latestDetail?->format_contact_date ?? '-';
                $activeDetail = $latestDetail;
            }

            $decision = $activeDetail?->decision?->name ?? '-';

            $detail = "ที่มา : {$source}<br>{$dateLabel} : {$dateValue}<br>การตัดสินใจ : {$decision}";

            return [
                'No'           => $no++,
                'id'           => $t->id,
                'FullName'     => trim($fullName),
                'model'        => $car,
                'sale'         => $t->sale->name ?? '-',
                'detail'       => $detail,
                'decision_id'  => $activeDetail?->decision_id ?? '',
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function create()
    {
        $authUser      = Auth::user();
        $model         = TbCarmodel::where('brand', $authUser->brand)->get();
        $sources       = TbSalecarType::all();
        $decisions     = TbDecision::all();
        $saleUser      = User::where('role', 'sale')->where('brand', $authUser->brand)->get();
        $interiorColor = $authUser->brand == 2 ? TbInteriorColor::all() : collect();

        return view('customer-tracking.input', compact('model', 'sources', 'decisions', 'saleUser', 'interiorColor'));
    }

    public function checkDuplicate(Request $request)
    {
        $exists = CustomerTracking::where('customer_id', $request->customer_id)
            ->where('brand', Auth::user()->brand)
            ->exists();

        return response()->json(['exists' => $exists]);
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $authUser = Auth::user();

            $alreadyTracked = CustomerTracking::where('customer_id', $request->customer_id)
                ->where('brand', $authUser->brand)
                ->exists();

            if ($alreadyTracked) {
                return response()->json([
                    'success' => false,
                    'message' => 'ลูกค้านี้มีข้อมูลการติดตามอยู่แล้วในระบบ'
                ], 422);
            }

            $brand = (int) $authUser->brand;

            $tracking = CustomerTracking::create([
                'sale_id'           => $request->sale_id,
                'customer_id'       => $request->customer_id,
                'source_id'         => $request->source_id,
                'model_id'          => $request->model_id ?: null,
                'sub_model_id'      => $request->sub_model_id ?: null,
                'year'              => $request->year ?: null,
                'pricelist_color'   => $brand === 1 ? ($request->pricelist_color ?: null) : null,
                'option'            => $request->option ?: null,
                'color_id'          => $brand === 1 ? null : ($request->color_id ?: null),
                'interior_color_id' => $brand === 2 ? ($request->interior_color_id ?: null) : null,
                'color_text'        => $brand === 1 ? ($request->color_text ?: null) : null,
                'userZone'          => $authUser->userZone,
                'brand'             => $authUser->brand,
                'branch'            => $authUser->branch,
                'UserInsert'        => $authUser->id,
            ]);

            $entryType  = $authUser->role === 'sale' ? 'sale' : 'manager';
            $decisionId = $request->decision_id ?: null;
            $baseDate   = Carbon::parse($request->contact_date);

            CustomerTrackingDetail::create([
                'tracking_id'    => $tracking->id,
                'contact_date'   => $request->contact_date,
                'comment_sale'   => $request->comment_sale,
                'decision_id'    => $decisionId,
                'contact_status' => $request->contact_status,
                'entry_type'     => $entryType,
                'UserInsert'     => $authUser->id,
            ]);

            // auto-generate follow-up entries สำหรับ role ที่ไม่ใช่ sale
            if ($authUser->role !== 'sale' && $decisionId) {
                $followUpDays = match ((int) $decisionId) {
                    2 => [3, 6],
                    1 => [30, 60],
                    default => [],
                };

                foreach ($followUpDays as $index => $days) {
                    $isLast = ($index === array_key_last($followUpDays));
                    CustomerTrackingDetail::create([
                        'tracking_id'    => $tracking->id,
                        'contact_date'   => $baseDate->copy()->addDays($days)->format('Y-m-d'),
                        'contact_status' => 1,
                        'decision_id'    => $decisionId,
                        'comment_sale'   => null,
                        'entry_type'     => 'manager',
                        'is_checkpoint'  => $isLast ? 1 : 0,
                        'UserInsert'     => $authUser->id,
                    ]);
                }
            }

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

        $user       = Auth::user();
        $entryType  = $user->role === 'sale' ? 'sale' : 'manager';
        $decisionId = $request->decision_id ?: null;

        DB::beginTransaction();
        try {
            CustomerTrackingDetail::create([
                'tracking_id'    => $id,
                'contact_date'   => $request->contact_date,
                'contact_status' => $request->contact_status,
                'decision_id'    => $decisionId,
                'comment_sale'   => $request->comment_sale,
                'entry_type'     => $entryType,
                'UserInsert'     => $user->id,
            ]);

            if ($user->role !== 'sale' && $decisionId) {
                $followUpDays = match ((int) $decisionId) {
                    2 => [3, 6],
                    1 => [30, 60],
                    default => [],
                };

                $baseDate = Carbon::parse($request->contact_date);

                foreach ($followUpDays as $index => $days) {
                    $isLast = ($index === array_key_last($followUpDays));
                    CustomerTrackingDetail::create([
                        'tracking_id'    => $id,
                        'contact_date'   => $baseDate->copy()->addDays($days)->format('Y-m-d'),
                        'contact_status' => 1,
                        'decision_id'    => $decisionId,
                        'comment_sale'   => null,
                        'entry_type'     => 'manager',
                        'is_checkpoint'  => $isLast ? 1 : 0,
                        'UserInsert'     => $user->id,
                    ]);
                }
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด'], 500);
        }
    }

    public function updateDetail(Request $request, $detailId)
    {
        $request->validate([
            'contact_status' => 'required|in:1,0',
        ]);

        $detail = CustomerTrackingDetail::findOrFail($detailId);
        $detail->update([
            'contact_status' => $request->contact_status,
            'comment_sale'   => $request->comment_sale,
            'UserUpdate'     => Auth::id(),
        ]);

        return response()->json(['success' => true]);
    }

    public function continueTracking(Request $request, $detailId)
    {
        $request->validate([
            'decision_id' => 'required|integer',
        ]);

        $detail = CustomerTrackingDetail::findOrFail($detailId);
        $user   = Auth::user();

        $isAutoDecision = in_array((int) $request->decision_id, [1, 2]);

        $followUpDays = match ((int) $request->decision_id) {
            2 => [3, 6, 9],
            1 => [30, 60, 90],
            default => [0],
        };

        DB::beginTransaction();
        try {
            $detail->update(['is_checkpoint' => 0]);

            $baseDate = $isAutoDecision
                ? Carbon::parse($detail->contact_date)
                : Carbon::parse($request->contact_date ?? $detail->contact_date);

            foreach ($followUpDays as $index => $days) {
                $isLast = ($index === array_key_last($followUpDays));
                CustomerTrackingDetail::create([
                    'tracking_id'    => $detail->tracking_id,
                    'contact_date'   => $baseDate->copy()->addDays($days)->format('Y-m-d'),
                    'contact_status' => 1,
                    'decision_id'    => $request->decision_id,
                    'comment_sale'   => null,
                    'entry_type'     => 'manager',
                    'is_checkpoint'  => $isLast ? 1 : 0,
                    'UserInsert'     => $user->id,
                ]);
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด'], 500);
        }
    }

    public function report()
    {
        return view('customer-tracking.report');
    }

    public function exportExcel()
    {
        return Excel::download(new CustomerTrackingExport(), 'รายงานการติดตามลูกค้า.xlsx');
    }

    public function cancelTracking($id)
    {
        $tracking = CustomerTracking::findOrFail($id);
        $tracking->update([
            'cancelled_at'  => now(),
            'CancelledBy'   => Auth::id(),
        ]);
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        CustomerTracking::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
