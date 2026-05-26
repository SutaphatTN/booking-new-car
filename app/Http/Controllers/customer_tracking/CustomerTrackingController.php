<?php

namespace App\Http\Controllers\customer_tracking;

use App\Exports\customerTracking\CustomerTrackingExport;
use App\Exports\customerTracking\CustomerTrackingByDateExport;
use App\Exports\customerTracking\CustomerTrackingDailyExport;
use App\Http\Controllers\Controller;
use App\Models\CustomerTracking;
use App\Models\CustomerTrackingDetail;
use App\Models\Salecar;
use App\Models\Customer;
use App\Models\TbCarmodel;
use App\Models\TbDecision;
use App\Models\TbInteriorColor;
use App\Models\TbPrefixname;
use App\Models\TbSalecarType;
use App\Models\Traits\UserAccessScope;
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
        $draw           = (int) ($request->draw ?? 1);
        $start          = (int) ($request->start ?? 0);
        $length         = (int) ($request->length ?? 10);
        $search         = trim($request->input('search.value', ''));
        $decisionId     = $request->decision_id;
        $saleFilter     = $request->sale_filter     ? json_decode($request->sale_filter, true)      : null;
        $sourceFilter   = $request->source_filter   ? json_decode($request->source_filter, true)    : null;
        $statusFilter   = $request->status_filter   ? json_decode($request->status_filter, true)    : null;
        $lastDateFilter = $request->last_date_filter ? json_decode($request->last_date_filter, true) : null;
        $nextDateFilter = $request->next_date_filter ? json_decode($request->next_date_filter, true) : null;
        $user           = Auth::user();
        $today          = now()->toDateString();

        // ซ่อน tracking เฉพาะใบจอง active (con_status 1-4,6)
        // ถอนจอง (7,8,9) → tracking กลับมาแสดง | ส่งมอบ (5) → tracking ถูกปิดด้วย cancelled_at แล้ว
        $bookedSubquery = Salecar::select('CusID')
            ->whereNull('deleted_at')
            ->whereIn('con_status', [1, 2, 3, 4, 6])
            ->where('brand', $user->brand);

        $base = CustomerTracking::query()
            ->whereNotIn('customer_id', $bookedSubquery)
            ->whereNull('cancelled_at');

        if ($user->role === 'sale') {
            $visibleSaleIds = [$user->id];
            if ($user->id === 7) {
                $visibleSaleIds = array_merge($visibleSaleIds, [9, 10, 11]);
            }
            $base->whereIn('sale_id', $visibleSaleIds);
        }

        // filterDecision dropdown — ค้นหา decision_id ของ "active detail" ของแต่ละ tracking
        if ($decisionId) {
            $base->whereRaw('(
                SELECT decision_id FROM customer_tracking_details
                WHERE tracking_id = customer_trackings.id AND deleted_at IS NULL
                ORDER BY
                    CASE WHEN entry_type = "manager" AND contact_date > ? THEN 0
                         WHEN entry_type = "manager" THEN 1
                         ELSE 2 END ASC,
                    CASE WHEN entry_type = "manager" AND contact_date > ? THEN contact_date END ASC,
                    created_at DESC
                LIMIT 1
            ) = ?', [$today, $today, $decisionId]);
        }

        // Sale column filter
        if ($saleFilter && count($saleFilter) > 0) {
            $saleIds = User::whereIn('name', $saleFilter)->pluck('id');
            $base->whereIn('sale_id', $saleIds);
        }

        // Source column filter
        if ($sourceFilter && count($sourceFilter) > 0) {
            $sourceIds = TbSalecarType::whereIn('name', $sourceFilter)->pluck('id');
            $base->whereIn('source_id', $sourceIds);
        }

        // Status column filter (by decision name → id)
        if ($statusFilter && count($statusFilter) > 0) {
            $decisionIds = TbDecision::whereIn('name', $statusFilter)->pluck('id');
            $base->whereRaw('(
                SELECT decision_id FROM customer_tracking_details
                WHERE tracking_id = customer_trackings.id AND deleted_at IS NULL
                ORDER BY
                    CASE WHEN entry_type = "manager" AND contact_date > ? THEN 0
                         WHEN entry_type = "manager" THEN 1
                         ELSE 2 END ASC,
                    CASE WHEN entry_type = "manager" AND contact_date > ? THEN contact_date END ASC,
                    created_at DESC
                LIMIT 1
            ) IN (' . implode(',', array_fill(0, count($decisionIds), '?')) . ')',
                array_merge([$today, $today], $decisionIds->toArray())
            );
        }

        // Next date column filter (YYYY-MM-DD)
        if ($nextDateFilter && count($nextDateFilter) > 0) {
            $base->whereHas('details', fn($q) =>
                $q->where('entry_type', 'manager')
                  ->where('contact_date', '>', $today)
                  ->whereIn('contact_date', $nextDateFilter)
            );
        }

        // Last date column filter (YYYY-MM-DD)
        if ($lastDateFilter && count($lastDateFilter) > 0) {
            $placeholders = implode(',', array_fill(0, count($lastDateFilter), '?'));
            $base->whereRaw("(
                SELECT MAX(contact_date) FROM customer_tracking_details
                WHERE tracking_id = customer_trackings.id
                AND contact_date <= ? AND deleted_at IS NULL
            ) IN ({$placeholders})", array_merge([$today], $lastDateFilter));
        }

        // Global search
        if ($search) {
            $searchDigits = preg_replace('/\D/', '', $search);
            $base->where(function ($q) use ($search, $searchDigits) {
                $q->whereHas('customer', function ($q) use ($search, $searchDigits) {
                    $q->where('FirstName', 'like', "%{$search}%")
                      ->orWhere('LastName', 'like', "%{$search}%");
                    if ($searchDigits !== '') {
                        $q->orWhereRaw("REPLACE(Mobilephone1, '-', '') LIKE ?", ["%{$searchDigits}%"]);
                    }
                })
                ->orWhereHas('sale', fn($q) =>
                    $q->where('name', 'like', "%{$search}%")
                );
            });
        }

        $recordsTotal    = (clone $base)->count();
        $recordsFiltered = $recordsTotal; // ตัวเลขเดียวกันเพราะ filter ทำก่อน count

        // Sort by next contact date (DB-level)
        $base->orderByRaw('(
            COALESCE(
                (SELECT MIN(contact_date) FROM customer_tracking_details
                 WHERE tracking_id = customer_trackings.id
                 AND entry_type = "manager" AND contact_date > ? AND deleted_at IS NULL),
                "9999-12-31"
            )
        ) ASC', [$today]);

        $trackings = $base
            ->with(['customer.prefix', 'sale', 'source', 'model', 'subModel',
                    'latestDetail.decision', 'nextManagerDetail', 'latestManagerDetail',
                    'latestPastDetail', 'wuColor'])
            ->skip($start)
            ->take($length)
            ->get();

        $rowNum = $start + 1;
        $data = $trackings->map(function ($t) use (&$rowNum) {
            $customer = $t->customer;
            $fullName = $customer
                ? (($customer->prefix->Name_TH ?? '') . ' ' . $customer->FirstName . ' ' . $customer->LastName)
                : '-';

            $model        = $t->model ? $t->model->Name_TH : '';
            $subModelSale = $t->subModel ? $t->subModel->name : '';
            $subDetail    = $t->subModel ? $t->subModel->detail : '';

            $row = fn($icon, $class, $tip, $text) =>
                "<div class=\"text-start\"><i class=\"bx {$icon} {$class} me-1\" data-bs-toggle=\"tooltip\" title=\"{$tip}\"></i>:&nbsp;{$text}</div>";

            if ($t->brand == 2 || $t->brand == 3) {
                $car = $row('bxs-car',       'text-primary', 'รุ่นหลัก', $model)
                     . $row('bx-git-branch', 'text-info',    'รุ่นย่อย', $subModelSale);
            } else {
                $car = $row('bxs-car',       'text-primary', 'รุ่นหลัก', $model)
                     . $row('bx-git-branch', 'text-info',    'รุ่นย่อย', $subModelSale)
                     . ($subDetail ? $row('bx-info-circle', 'text-warning', 'รายละเอียด', $subDetail) : '');
            }

            $latestDetail = $t->latestDetail;
            $nextDate     = $t->nextManagerDetail?->format_contact_date ?? '-';
            $nextDateRaw  = $t->nextManagerDetail?->contact_date ?? '9999-12-31';
            $lastDate     = $t->latestPastDetail?->format_contact_date ?? '-';

            if ($t->nextManagerDetail) {
                $activeDetail = $t->nextManagerDetail;
            } elseif ($t->latestManagerDetail) {
                $activeDetail = $t->latestManagerDetail;
            } else {
                $activeDetail = $latestDetail;
            }

            $decision = $activeDetail?->decision?->name ?? '-';

            $phone    = $customer?->formatted_mobile ?? null;
            $lineId   = $customer?->LineID ?? null;
            $facebook = $customer?->FacebookName ?? null;
            $contactParts = [];
            if ($phone)    $contactParts[] = "<div class=\"text-nowrap\"><i class=\"bx bx-phone text-danger me-1\"></i>: {$phone}</div>";
            if ($lineId)   $contactParts[] = "<div class=\"text-nowrap\"><i class=\"bx bxl-whatsapp text-success me-1\"></i>: {$lineId}</div>";
            if ($facebook) $contactParts[] = "<div class=\"text-nowrap\"><i class=\"bx bxl-facebook-circle text-primary me-1\"></i>: {$facebook}</div>";
            $contactInfo = $contactParts ? implode('', $contactParts) : '<span class="text-muted">—</span>';

            return [
                'No'             => $rowNum++,
                'id'             => $t->id,
                'FullName'       => trim($fullName),
                'contact_info'   => $contactInfo,
                'model'          => $car,
                'sale'           => $t->sale->name ?? '-',
                'source'         => $t->source->name ?? '-',
                'last_date'      => $lastDate,
                'next_date'      => $nextDate,
                'next_date_sort' => $nextDateRaw,
                'status'         => $decision,
                'decision_id'    => $activeDetail?->decision_id ?? '',
            ];
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data->values(),
        ]);
    }

    public function filterOptions(Request $request)
    {
        $user  = Auth::user();
        $today = now()->toDateString();

        $decisionId     = $request->decision_id;
        $saleFilter     = $request->sale_filter     ? json_decode($request->sale_filter, true)      : null;
        $sourceFilter   = $request->source_filter   ? json_decode($request->source_filter, true)    : null;
        $statusFilter   = $request->status_filter   ? json_decode($request->status_filter, true)    : null;
        $lastDateFilter = $request->last_date_filter ? json_decode($request->last_date_filter, true) : null;
        $nextDateFilter = $request->next_date_filter ? json_decode($request->next_date_filter, true) : null;

        $bookedSubquery = Salecar::select('CusID')
            ->whereNull('deleted_at')
            ->whereIn('con_status', [1, 2, 3, 4, 6])
            ->where('brand', $user->brand);

        $base = CustomerTracking::whereNotIn('customer_id', $bookedSubquery)
            ->whereNull('cancelled_at')
            ->when($user->role === 'sale', fn($q) => $q->where('sale_id', $user->id));

        if ($decisionId) {
            $base->whereRaw('(
                SELECT decision_id FROM customer_tracking_details
                WHERE tracking_id = customer_trackings.id AND deleted_at IS NULL
                ORDER BY
                    CASE WHEN entry_type = "manager" AND contact_date > ? THEN 0
                         WHEN entry_type = "manager" THEN 1
                         ELSE 2 END ASC,
                    CASE WHEN entry_type = "manager" AND contact_date > ? THEN contact_date END ASC,
                    created_at DESC
                LIMIT 1
            ) = ?', [$today, $today, $decisionId]);
        }

        if ($saleFilter && count($saleFilter) > 0) {
            $saleIds = User::whereIn('name', $saleFilter)->pluck('id');
            $base->whereIn('sale_id', $saleIds);
        }

        if ($sourceFilter && count($sourceFilter) > 0) {
            $sourceIds = TbSalecarType::whereIn('name', $sourceFilter)->pluck('id');
            $base->whereIn('source_id', $sourceIds);
        }

        if ($statusFilter && count($statusFilter) > 0) {
            $decisionIds = TbDecision::whereIn('name', $statusFilter)->pluck('id');
            $base->whereRaw('(
                SELECT decision_id FROM customer_tracking_details
                WHERE tracking_id = customer_trackings.id AND deleted_at IS NULL
                ORDER BY
                    CASE WHEN entry_type = "manager" AND contact_date > ? THEN 0
                         WHEN entry_type = "manager" THEN 1
                         ELSE 2 END ASC,
                    CASE WHEN entry_type = "manager" AND contact_date > ? THEN contact_date END ASC,
                    created_at DESC
                LIMIT 1
            ) IN (' . implode(',', array_fill(0, count($decisionIds), '?')) . ')',
                array_merge([$today, $today], $decisionIds->toArray())
            );
        }

        if ($nextDateFilter && count($nextDateFilter) > 0) {
            $base->whereHas('details', fn($q) =>
                $q->where('entry_type', 'manager')
                  ->where('contact_date', '>', $today)
                  ->whereIn('contact_date', $nextDateFilter)
            );
        }

        if ($lastDateFilter && count($lastDateFilter) > 0) {
            $placeholders = implode(',', array_fill(0, count($lastDateFilter), '?'));
            $base->whereRaw("(
                SELECT MAX(contact_date) FROM customer_tracking_details
                WHERE tracking_id = customer_trackings.id
                AND contact_date <= ? AND deleted_at IS NULL
            ) IN ({$placeholders})", array_merge([$today], $lastDateFilter));
        }

        $trackingIds = $base->pluck('id');

        // Distinct sale names
        $sales = User::whereIn('id',
            CustomerTracking::whereIn('id', $trackingIds)->pluck('sale_id')->unique()
        )->orderBy('name')->pluck('name');

        // Distinct source names
        $sources = TbSalecarType::whereIn('id',
            CustomerTracking::whereIn('id', $trackingIds)->whereNotNull('source_id')->pluck('source_id')->unique()
        )->orderBy('name')->pluck('name');

        // Distinct decision names (from details of active trackings)
        $usedDecisionIds = CustomerTrackingDetail::whereIn('tracking_id', $trackingIds)
            ->whereNotNull('decision_id')
            ->pluck('decision_id')
            ->unique();
        $decisions = TbDecision::whereIn('id', $usedDecisionIds)->orderBy('name')->pluck('name');

        // Distinct last dates (max past contact per tracking)
        $lastDates = CustomerTrackingDetail::whereIn('tracking_id', $trackingIds)
            ->whereDate('contact_date', '<=', $today)
            ->selectRaw('MAX(contact_date) as last_date')
            ->groupBy('tracking_id')
            ->pluck('last_date')
            ->unique()
            ->sort()
            ->values();

        // Distinct next dates (min future manager contact per tracking)
        $nextDates = CustomerTrackingDetail::whereIn('tracking_id', $trackingIds)
            ->where('entry_type', 'manager')
            ->whereDate('contact_date', '>', $today)
            ->selectRaw('MIN(contact_date) as next_date')
            ->groupBy('tracking_id')
            ->pluck('next_date')
            ->unique()
            ->sort()
            ->values();

        return response()->json([
            'sales'     => $sales->values(),
            'sources'   => $sources->values(),
            'decisions' => $decisions->values(),
            'lastDates' => $lastDates,
            'nextDates' => $nextDates,
        ]);
    }

    public function create()
    {
        $authUser      = Auth::user();
        $model         = TbCarmodel::where('brand', $authUser->brand)->get();
        $sources       = TbSalecarType::all();
        $decisions     = TbDecision::all();
        $brandForSale  = $authUser->brand == 3 ? 1 : $authUser->brand;
        $saleUser      = User::where('role', 'sale')->where('brand', $brandForSale)->get();
        $interiorColor = $authUser->brand == 2 ? TbInteriorColor::all() : collect();
        $prefixes      = TbPrefixname::all();

        return view('customer-tracking.input', compact('model', 'sources', 'decisions', 'saleUser', 'interiorColor', 'prefixes'));
    }

    public function checkDuplicate(Request $request)
    {
        $exists = CustomerTracking::where('customer_id', $request->customer_id)
            ->where('brand', Auth::user()->brand)
            ->whereNull('cancelled_at')
            ->exists();

        return response()->json(['exists' => $exists]);
    }

    public function checkPhone(Request $request)
    {
        $brand    = Auth::user()->brand;
        $field    = $request->field ?? 'phone';

        if ($field === 'line_id') {
            $customer = Customer::withTrashed()->where('LineID', $request->value)->first();
        } elseif ($field === 'facebook') {
            $customer = Customer::withTrashed()->where('FacebookName', $request->value)->first();
        } else {
            $phone    = preg_replace('/\D/', '', $request->phone);
            $customer = Customer::withTrashed()->where('Mobilephone1', $phone)->first();
        }

        if (!$customer) {
            return response()->json(['found' => false, 'has_tracking' => false, 'has_booking' => false]);
        }

        // เช็คการจอง active ใน brand เดียวกัน (con_status ไม่ใช่ 5,7,8,9 = จบแล้ว)
        $hasBooking = Salecar::withoutGlobalScope(UserAccessScope::class)
            ->where('CusID', $customer->id)
            ->where('brand', $brand)
            ->whereNotIn('con_status', [5, 7, 8, 9])
            ->exists();

        $tracking = CustomerTracking::where('customer_id', $customer->id)
            ->where('brand', $brand)
            ->whereNull('cancelled_at')
            ->first();

        $prefix = $customer->prefix?->Name_TH ?? '';
        $name   = trim("{$prefix} {$customer->FirstName} {$customer->LastName}");

        return response()->json([
            'found'       => true,
            'customer_id' => $customer->id,
            'name'        => $name,
            'has_booking' => $hasBooking,
            'has_tracking'=> $tracking !== null,
            'tracking_id' => $tracking?->id,
        ]);
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $authUser = Auth::user();

            $hasBooking = Salecar::withoutGlobalScope(UserAccessScope::class)
                ->where('CusID', $request->customer_id)
                ->where('brand', $authUser->brand)
                ->whereNotIn('con_status', [5, 7, 8, 9])
                ->exists();

            if ($hasBooking) {
                return response()->json([
                    'success' => false,
                    'message' => 'ลูกค้านี้มีข้อมูลการจองอยู่แล้ว ไม่สามารถเพิ่มการติดตามได้'
                ], 422);
            }

            $alreadyTracked = CustomerTracking::where('customer_id', $request->customer_id)
                ->where('brand', $authUser->brand)
                ->whereNull('cancelled_at')
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
                'customer_date'     => $request->contact_date ?: null,
                'model_id'          => $request->model_id ?: null,
                'sub_model_id'      => $request->sub_model_id ?: null,
                'year'              => $request->year ?: null,
                'pricelist_color'   => $brand === 1 ? ($request->pricelist_color ?: null) : null,
                'option'            => $request->option ?: null,
                'color_id'          => $brand === 1 ? null : ($request->color_id ?: null),
                'interior_color_id' => $brand === 2 ? ($request->interior_color_id ?: null) : null,
                'color_text'        => $brand === 1 ? ($request->color_text ?: null) : null,
                'clip_add'          => $request->clip_add ?: null,
                'userZone'          => $authUser->userZone,
                'brand'             => $authUser->brand,
                'branch'            => $authUser->branch,
                'UserInsert'        => $authUser->id,
            ]);

            $isSaleRole = in_array($authUser->role, ['sale', 'adminPage', 'audit']);
            $entryType  = $isSaleRole ? 'sale' : 'manager';
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
            if (!$isSaleRole && $decisionId) {
                $followUpDays = match ((int) $decisionId) {
                    1 => [3, 6],
                    2 => [15, 30],
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
        $isSaleRole = in_array($user->role, ['sale', 'adminPage', 'audit']);
        $entryType  = $isSaleRole ? 'sale' : 'manager';
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

            if (!$isSaleRole && $decisionId) {
                $followUpDays = match ((int) $decisionId) {
                    1 => [3, 6],
                    2 => [15, 60],
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
            1 => [3, 6, 9],
            2 => [15, 30, 45],
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

    public function exportExcelByDate(Request $request)
    {
        $dateFrom = $request->date_from ?? now()->toDateString();
        $dateTo   = $request->date_to   ?? now()->toDateString();
        $filename = 'รายงานการกรอกข้อมูล_' . $dateFrom . '_ถึง_' . $dateTo . '.xlsx';

        return Excel::download(new CustomerTrackingByDateExport($dateFrom, $dateTo), $filename);
    }

    public function exportDailyReport(Request $request)
    {
        $date     = $request->date ?? now()->toDateString();
        $filename = 'รายงานประจำวัน_' . $date . '.xlsx';

        return Excel::download(new CustomerTrackingDailyExport($date), $filename);
    }

    public function saveTestDrive(Request $request, $id)
    {
        $tracking = CustomerTracking::findOrFail($id);
        $tracking->update([
            'test_drive_date' => $request->test_drive_date ?: null,
            'test_drive_note' => $request->test_drive_note ?: null,
        ]);
        return response()->json(['success' => true]);
    }

    public function saveGrade(Request $request, $id)
    {
        $tracking = CustomerTracking::findOrFail($id);

        $tracking->update([
            'delivery_timeline_scoring' => $request->delivery_timeline_scoring ?: null,
            'test_drive_scoring'        => $request->test_drive_scoring ?: null,
            'occupation_scoring'        => $request->occupation_scoring ?: null,
            'revenue_scoring'           => $request->revenue_scoring ?: null,
            'model_interest_scoring'    => $request->model_interest_scoring ?: null,
            'purchase_type_scoring'     => $request->purchase_type_scoring ?: null,
            'engagement_scoring'        => $request->engagement_scoring ?: null,
        ]);

        return response()->json(['success' => true]);
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

    public function quickStoreCustomer(Request $request)
    {
        $request->validate([
            'PrefixName'   => 'nullable|integer|exists:tb_prefixname,id',
            'FirstName'    => 'required|string|max:100',
            'LastName'     => 'nullable|string|max:100',
            'Mobilephone1' => 'nullable|string|max:20',
            'IDNumber'     => 'nullable|string|max:17',
            'LineID'       => 'nullable|string|max:100',
            'FacebookName' => 'nullable|string|max:100',
        ]);

        $authUser = Auth::user();
        $idNumber = $request->IDNumber ? preg_replace('/\D/', '', $request->IDNumber) : null;
        $mobile   = $request->Mobilephone1 ? preg_replace('/\D/', '', $request->Mobilephone1) : null;

        if ($idNumber) {
            $idExists = Customer::where('IDNumber', $idNumber)->exists();
            if ($idExists) {
                return response()->json(['success' => false, 'message' => 'เลขบัตรประชาชนนี้มีอยู่ในระบบแล้ว'], 422);
            }
        }

        if ($mobile) {
            $phoneExists = Customer::withTrashed()->where('Mobilephone1', $mobile)->exists();
            if ($phoneExists) {
                return response()->json(['success' => false, 'message' => 'เบอร์โทรศัพท์นี้มีอยู่ในระบบแล้ว'], 422);
            }
        }

        if ($request->LineID) {
            $lineExists = Customer::withTrashed()->where('LineID', $request->LineID)->exists();
            if ($lineExists) {
                return response()->json(['success' => false, 'message' => 'Line ID นี้มีอยู่ในระบบแล้ว'], 422);
            }
        }

        if ($request->FacebookName) {
            $fbExists = Customer::withTrashed()->where('FacebookName', $request->FacebookName)->exists();
            if ($fbExists) {
                return response()->json(['success' => false, 'message' => 'Facebook นี้มีอยู่ในระบบแล้ว'], 422);
            }
        }

        $prefixName   = $request->PrefixName ? TbPrefixname::find($request->PrefixName)?->Name_TH : null;
        $originalName = trim(implode(' ', array_filter([
            $prefixName,
            $request->FirstName,
            $request->LastName ?: null,
        ]))) ?: null;

        $customer = Customer::create([
            'PrefixName'   => $request->PrefixName ?: null,
            'FirstName'    => $request->FirstName,
            'LastName'     => $request->LastName ?: null,
            'OriginalName' => $originalName,
            'Mobilephone1' => $mobile,
            'IDNumber'     => $idNumber,
            'LineID'       => $request->LineID,
            'FacebookName' => $request->FacebookName,
            'userZone'     => $authUser->userZone,
            'brand'        => $authUser->brand,
            'branch'       => $authUser->branch,
            'UserInsert'   => $authUser->id,
        ]);

        $customer->load('prefix');
        $prefixName = $customer->prefix?->Name_TH ?? '';

        return response()->json([
            'success'   => true,
            'id'        => $customer->id,
            'name'      => trim("{$prefixName} {$customer->FirstName} {$customer->LastName}"),
            'mobile'    => $customer->formatted_mobile,
            'id_number' => $idNumber ? $customer->formatted_id_number : '-',
        ]);
    }
}
