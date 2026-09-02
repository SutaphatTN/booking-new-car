<?php

namespace App\Http\Controllers\customer_tracking;

use App\Exports\customerTracking\CustomerTrackingExport;
use App\Exports\customerTracking\CustomerTrackingByDateExport;
use App\Exports\customerTracking\CustomerTrackingDailyExport;
use App\Exports\customerTracking\CustomerTrackingOverdueExport;
use App\Exports\customerTracking\CustomerTrackingOverdueReport;
use App\Exports\customerTracking\CustomerTrackingOfflinePlaceReport;
use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Traits\ConvertsThaiDate;
use App\Models\CustomerTracking;
use App\Models\CustomerTrackingDetail;
use App\Models\Salecar;
use App\Models\Customer;
use App\Models\TbCarmodel;
use App\Models\TbDecision;
use App\Models\TbInteriorColor;
use App\Models\TbPrefixname;
use App\Models\TbSalecarType;
use App\Models\SourcePlace;
use App\Models\User;
use App\Services\OneDriveService;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use App\Support\ExportFilename;
use App\Support\BrandFeature;
use App\Support\SourceScope;

class CustomerTrackingController extends Controller
{
    use ConvertsThaiDate;

    public function index()
    {
        $decisions = TbDecision::all();
        $bookedMode = false;
        return view('customer-tracking.view', compact('decisions', 'bookedMode'));
    }

    /**
     * รายการติดตาม "ที่ปิดไปแล้ว" — หน้าย้อนหลังของ admin
     *
     * หน้ารายการติดตามปกติซ่อนใบที่ลูกค้าจองแล้ว (customer_id อยู่ในใบจอง active) กับใบที่ถูกปิด
     * ด้วย cancelled_at ทิ้ง พอปิดไปแล้วก็ตามดู/แก้ย้อนหลังไม่ได้เลย หน้านี้กลับด้านตัวกรอง
     * ให้เหลือเฉพาะใบพวกนั้น — สองหน้ารวมกันได้ใบติดตามครบทุกใบพอดี ไม่ซ้ำกัน
     * ใช้ view + DataTable ตัวเดียวกับหน้ารายการติดตาม (ปุ่มจึงเหมือนกันทุกปุ่ม)
     */
    public function booked()
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $decisions = TbDecision::all();
        $bookedMode = true;
        return view('customer-tracking.view', compact('decisions', 'bookedMode'));
    }

    /**
     * ใบติดตามโหมดไหน — ปกติ (ยังติดตามอยู่) หรือย้อนหลัง (จอง/ปิดไปแล้ว)
     * โหมดย้อนหลังเปิดให้ admin เท่านั้น role อื่นยิง ?booked=1 มาก็ได้ลิสต์ปกติ
     */
    private function applyBookedScope($query, $bookedSubquery, bool $booked)
    {
        if ($booked && Auth::user()->role === 'admin') {
            return $query->where(
                fn($q) => $q->whereIn('customer_id', $bookedSubquery)->orWhereNotNull('cancelled_at')
            );
        }

        return $query->whereNotIn('customer_id', $bookedSubquery)->whereNull('cancelled_at');
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

        $base = $this->applyBookedScope(
            CustomerTracking::query(),
            $bookedSubquery,
            $request->boolean('booked')
        );

        if (in_array($user->role, ['sale', 'lead_sale'])) {
            $visibleSaleIds = [$user->id];
            if ($user->role === 'lead_sale') {
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
            $base->whereRaw(
                '(
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
            $base->whereHas(
                'details',
                fn($q) =>
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
                    $q->searchFullName($search);
                    if ($searchDigits !== '') {
                        $q->orWhereRaw("REPLACE(Mobilephone1, '-', '') LIKE ?", ["%{$searchDigits}%"]);
                    }
                })
                    ->orWhereHas(
                        'sale',
                        fn($q) =>
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
            ->with([
                'customer.prefix',
                'sale',
                'source',
                'model',
                'subModel',
                'latestDetail.decision',
                'nextManagerDetail',
                'latestManagerDetail',
                'latestPastDetail',
                'wuColor'
            ])
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

            if (in_array($t->brand, [2, 3, 4])) {
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
            if ($phone)    $contactParts[] = "<div class=\"text-start\"><i class=\"bx bx-phone text-danger me-1\"></i>: {$phone}</div>";
            if ($lineId)   $contactParts[] = "<div class=\"text-start\"><i class=\"bx bxl-whatsapp text-success me-1\"></i>: {$lineId}</div>";
            if ($facebook) $contactParts[] = "<div class=\"text-start\"><i class=\"bx bxl-facebook-circle text-primary me-1\"></i>: {$facebook}</div>";
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
                // จองแล้ว / จบการติดตาม / ยกเลิกการติดตาม — โชว์เฉพาะหน้าย้อนหลัง (ดู CustomerTracking::statusLabel)
                'outcome'        => $t->statusLabel(),
                'cancel_reason'  => trim(($t->cancel_reason ?? '') . ' ' . ($t->cancel_reason_note ?? '')),
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

        $visibleSaleIds = [];
        if (in_array($user->role, ['sale', 'lead_sale'])) {
            $visibleSaleIds = [$user->id];
            if ($user->role === 'lead_sale') {
                $visibleSaleIds = array_merge($visibleSaleIds, [9, 10, 11]);
            }
        }

        $base = $this->applyBookedScope(
            CustomerTracking::query(),
            $bookedSubquery,
            $request->boolean('booked')
        )->when(
            in_array($user->role, ['sale', 'lead_sale']),
            fn($q) => $q->whereIn('sale_id', $visibleSaleIds)
        );

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
            $base->whereRaw(
                '(
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
            $base->whereHas(
                'details',
                fn($q) =>
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
        $sales = User::whereIn(
            'id',
            CustomerTracking::whereIn('id', $trackingIds)->pluck('sale_id')->unique()
        )->orderBy('name')->pluck('name');

        // Distinct source names
        $sources = TbSalecarType::whereIn(
            'id',
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
        // แหล่งที่มาหลักที่คีย์ได้ — ตัดกลุ่มเฉพาะตอนจอง ("ลูกค้าเก่า") และกลุ่มที่เซลล์คีย์เองไม่ได้
        // ("Online บริษัท" / "ดีลเลอร์") ออกตั้งแต่ต้นทาง เพื่อให้ sub-source ล้อกับกลุ่มที่เหลือเสมอ
        $sourceMains   = SourceScope::allowedMains();
        $sources       = TbSalecarType::whereIn('main_source', array_keys($sourceMains))->get();
        $decisions     = TbDecision::all();
        $saleBrands = config("brand.sale_pool.{$authUser->brand}", [$authUser->brand]);
        $extraSaleIds = User::extraSaleUserIdsForBrand((int) $authUser->brand);
        $saleUser = User::whereIn('role', ['sale', 'lead_sale'])
            ->where(function ($q) use ($saleBrands, $extraSaleIds) {
                $q->whereIn('brand', $saleBrands)
                    ->orWhereIn('id', $extraSaleIds);
            })
            ->when($authUser->brand == 2, function ($q) use ($authUser) {
                $q->where('branch', $authUser->branch);
            })
            ->get();
        $interiorColor = BrandFeature::hasInteriorColor($authUser->brand) ? TbInteriorColor::all() : collect();
        $prefixes      = TbPrefixname::all();
        $placeMain     = config('source.place_main', 'offline');

        // ตัวเลือก "คลิปที่ยิงแอด" — เฉพาะแอดที่ยังแสดง (is_active=1) ของ brand+branch นี้
        $ads = Ad::where('is_active', 1)
            ->where('brand', $authUser->brand)
            ->where('branch', $authUser->branch)
            ->orderBy('name')
            ->get();

        return view('customer-tracking.input', compact('model', 'sources', 'decisions', 'saleUser', 'interiorColor', 'prefixes', 'sourceMains', 'placeMain', 'ads'));
    }

    public function checkDuplicate(Request $request)
    {
        $authUser = Auth::user();
        $exists   = self::activeTrackingQuery($request->customer_id, $authUser->brand, $authUser->branch)->exists();

        return response()->json(['exists' => $exists]);
    }

    /**
     * ด่านกันติดตามซ้ำ (มติ GM 2026-08-07)
     *
     * ขอบเขตขึ้นกับ brand — ดู config/brand.php > tracking_split_by_branch
     *  - brand ที่แยกสาขา (GWM)  : กันซ้ำเฉพาะในสาขาเดียวกัน ข้ามสาขาตามคนเดียวกันได้
     *                              เพราะแต่ละสาขาจัดโปรไม่เหมือนกัน
     *  - brand ที่ไม่แยก (1/3/4) : กันซ้ำทั้ง brand เพราะอยู่ในชื่อบริษัทเดียวกัน
     *  - admin                    : เห็นภาพรวมทั้ง brand เสมอ ไม่ติดเงื่อนไขสาขา
     *
     * เขียนเงื่อนไขเองแทนการปล่อยให้ userAccess scope ทำ เพราะ scope นั้นมีไว้คุมสิทธิ์การ
     * มองเห็นข้อมูล ไม่ใช่กติกาธุรกิจ ถ้าวันหลังมีคนไปแก้ scope ด้วยเหตุผลอื่น ด่านนี้จะเปลี่ยน
     * ตามไปเงียบๆ (ผลลัพธ์ตอนนี้เท่ากับของเดิมทุก role — user ทุกคนอยู่ zone เดียวกันหมด)
     */
    private static function activeTrackingQuery($customerId, $brand, $branch)
    {
        $query = CustomerTracking::withoutGlobalScopes(['userAccess', 'saleTeam'])
            ->where('customer_id', $customerId)
            ->where('brand', $brand)
            ->whereNull('cancelled_at');

        $splitByBranch = in_array((int) $brand, config('brand.tracking_split_by_branch', []), true);

        if ($splitByBranch && Auth::user()?->role !== 'admin') {
            $query->where('branch', $branch);
        }

        return $query;
    }

    public function checkPhone(Request $request)
    {
        $authUser = Auth::user();
        $brand    = $authUser->brand;
        $field    = $request->field ?? 'phone';

        if ($field === 'line_id') {
            $column = 'LineID';
            $value  = Customer::normalizeContactValue($request->value) ?? '';
        } elseif ($field === 'facebook') {
            $column = 'FacebookName';
            $value  = Customer::normalizeContactValue($request->value) ?? '';
        } else {
            $column = 'Mobilephone1';
            $value  = preg_replace('/\D/', '', (string) $request->phone);
        }

        // ค่าค้นหาว่างห้ามยิง query — where(col, '') จะไปแมตช์แถวที่เก็บค่าว่างไว้
        // แล้วเด้งชื่อลูกค้าคนเดิมกลับมาทุกครั้งไม่ว่ากรอกอะไรมา
        // (normalizeContactValue ตัด '-' / '.' ทิ้งด้วย — ไม่งั้นมันกลายเป็นค่าที่ชนกันได้)
        // orderBy('id') กันผลแกว่งเวลาค่าซ้ำ (LineID/FacebookName ไม่มี unique index)
        $customer = $value === ''
            ? null
            : Customer::withTrashed()->where($column, $value)->orderBy('id')->first();

        if (!$customer) {
            return response()->json(['found' => false, 'has_tracking' => false, 'has_booking' => false]);
        }

        // เช็คการจอง active ใน brand เดียวกัน (con_status ไม่ใช่ 5,7,8,9 = จบแล้ว)
        $hasBooking = Salecar::withoutGlobalScopes(['userAccess', 'saleTeam'])
            ->where('CusID', $customer->id)
            ->where('brand', $brand)
            ->whereNotIn('con_status', [5, 7, 8, 9])
            ->exists();

        $tracking = self::activeTrackingQuery($customer->id, $brand, $authUser->branch)
            ->with('sale:id,name')
            ->orderByDesc('id')
            ->first();

        $prefix = $customer->prefix?->Name_TH ?? '';
        $name   = trim("{$prefix} {$customer->FirstName} {$customer->LastName}");

        return response()->json([
            'found'       => true,
            'customer_id' => $customer->id,
            'name'        => $name,
            'has_booking' => $hasBooking,
            'has_tracking' => $tracking !== null,
            'tracking_id' => $tracking?->id,
            // บริบทให้เซลแยกออกว่า "คนละคน = พิมพ์เบอร์ผิด" หรือ "ลูกค้าเก่าจริง"
            'tracking_sale' => $tracking?->sale?->name,
            'is_deleted'    => $customer->trashed(),
            'created_at'    => $customer->created_at?->toIso8601String(),
            'added_by'      => $customer->userInsert?->name,
        ]);
    }

    public function store(Request $request)
    {
        try {
            // แหล่งที่มา: ตรวจ source_id + place_id (place จำเป็นเสมอเมื่อ main=offline,
            // รับเฉพาะสถานที่ที่อนุมัติแล้ว — กันกรณีเลือก offline แต่สถานที่ยังรออนุมัติ)
            $source    = TbSalecarType::find($request->source_id);
            $placeMain = config('source.place_main', 'offline');
            $isOffline = $source && $source->main_source === $placeMain;

            // กลุ่มที่ผู้ใช้คนนี้คีย์ได้ — ต้องตรวจซ้ำฝั่งเซิร์ฟเวอร์ ไม่งั้นยิง request ตรงข้ามดรอปดาวน์ได้
            $allowedMains = array_keys(SourceScope::allowedMains());

            $validator = Validator::make($request->all(), [
                'source_main' => ['required', Rule::in($allowedMains)],
                'source_id'   => [
                    'required',
                    Rule::exists('tb_salecar_type', 'id')->whereIn('main_source', $allowedMains),
                ],
                'place_id'    => [
                    $isOffline ? 'required' : 'nullable',
                    Rule::exists('tb_source_place', 'id')
                        ->where('salecar_type_id', $request->source_id)
                        ->where('status', SourcePlace::STATUS_APPROVED)
                        ->whereNull('deleted_at'),
                ],
                'decision_id' => 'required|exists:tb_decision,id',
            ], [
                'source_main.required' => 'กรุณาเลือกแหล่งที่มาหลัก',
                'source_main.in'       => 'แหล่งที่มาหลักไม่ถูกต้อง',
                'source_id.required'   => 'กรุณาเลือกแหล่งที่มาย่อย',
                'source_id.exists'     => 'แหล่งที่มาย่อยไม่ถูกต้อง',
                'place_id.required'    => 'กรุณาเลือกสถานที่',
                'place_id.exists'      => 'สถานที่ไม่ถูกต้อง',
                'decision_id.required' => 'กรุณาเลือกสถานะการตัดสินใจ',
                'decision_id.exists'   => 'สถานะการตัดสินใจไม่ถูกต้อง',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }

            // เก็บ place_id เฉพาะเมื่อ main=offline เท่านั้น (กันค่าหลงมาจากกรณีอื่น)
            $placeId = ($source && $source->main_source === $placeMain) ? ($request->place_id ?: null) : null;

            DB::beginTransaction();

            $authUser = Auth::user();

            // ถ้าลูกค้าถูก soft-delete ไว้ → restore กลับมาก่อน เพราะมีการติดตามใหม่
            // ดึง model มา restore ทีละตัวแทน restore() บน query builder เพราะตัวหลังเป็น
            // mass update ที่ไม่ยิง model event → TracksUserActions ไม่ได้ stamp UserUpdate
            // แล้วจะสืบไม่ได้เลยว่าใครเป็นคนนำลูกค้าที่ถูกลบกลับมา
            if ($request->customer_id) {
                $trashedCustomer = Customer::withTrashed()->find($request->customer_id);
                if ($trashedCustomer?->trashed()) {
                    $trashedCustomer->restore();
                }
            }

            $hasBooking = Salecar::withoutGlobalScopes(['userAccess', 'saleTeam'])
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

            $alreadyTracked = self::activeTrackingQuery($request->customer_id, $authUser->brand, $authUser->branch)->exists();

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
                'place_id'          => $placeId,
                'customer_date'     => $this->toGregorian($request->contact_date ?: null),
                'model_id'          => $request->model_id ?: null,
                'sub_model_id'      => $request->sub_model_id ?: null,
                'year'              => $request->year ?: null,
                'pricelist_color'   => $brand === 1 ? ($request->pricelist_color ?: null) : null,
                'option'            => $request->option ?: null,
                'color_id'          => $brand === 1 ? null : ($request->color_id ?: null),
                'interior_color_id' => BrandFeature::hasInteriorColor($brand) ? ($request->interior_color_id ?: null) : null,
                'color_text'        => $brand === 1 ? ($request->color_text ?: null) : null,
                'clip_add'          => $request->clip_add ?: null,
                'userZone'          => $authUser->userZone,
                'brand'             => $authUser->brand,
                'branch'            => $authUser->branch,
                'UserInsert'        => $authUser->id,
            ]);

            $isSaleRole = in_array($authUser->role, ['sale', 'lead_sale', 'adminPage', 'audit', 'audit_lead', 'audit_dp', 'gm']);
            $entryType  = $isSaleRole ? 'sale' : 'manager';
            $decisionId = $request->decision_id ?: null;
            $baseDate   = Carbon::parse($request->contact_date);

            CustomerTrackingDetail::create([
                'tracking_id'    => $tracking->id,
                'contact_date'   => $this->toGregorian($request->contact_date),
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
                    3 => [120, 240],
                    4 => [180, 360],
                    default => [],
                };

                foreach ($followUpDays as $index => $days) {
                    $isLast = ($index === array_key_last($followUpDays));
                    CustomerTrackingDetail::create([
                        'tracking_id'    => $tracking->id,
                        'contact_date'   => $baseDate->copy()->addDays($days)->format('Y-m-d'),
                        'contact_status' => null, // checkpoint อัตโนมัติ → เว้นว่างไว้จนกว่าจะติดต่อจริง
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
            'place',
            'ad',
            'model',
            'subModel',
            'details.decision',
            'details.insertedBy', // ชื่อผู้บันทึกบนการ์ดแต่ละใบ
        ])->findOrFail($id);

        $decisions = TbDecision::all();

        $authUser = Auth::user();

        // ย้ายลูกค้าไปให้เซลล์คนอื่นได้ไหม — โหลดรายชื่อเฉพาะ role ที่มีสิทธิ์ ไม่งั้นเปลือง query
        $canReassignSale = $authUser->canReassignSale();
        $saleUser = $canReassignSale
            ? User::salePoolForBrand((int) ($tracking->brand ?: Auth::user()->brand), (int) $tracking->sale_id)
            : collect();

        // แก้แหล่งที่มา/สถานที่/คลิปแอด ย้อนหลัง — โหลดตัวเลือกเฉพาะคนที่มีสิทธิ์
        $canEditSource = $this->canEditTrackingSourceOf($tracking);
        $canEditAd     = $canEditSource && $authUser->canEditTrackingAd();

        $placeMain   = config('source.place_main', 'offline');
        // กติกาเดียวกับหน้าเพิ่มการติดตาม: เซลล์ไม่เห็นกลุ่ม "Online บริษัท" / "ดีลเลอร์"
        $sourceMains = SourceScope::allowedMains();

        // แหล่งที่มาปัจจุบันอาจเป็นตัวที่ถูกซ่อน/ลบไปแล้ว — ต้องคงไว้ในลิสต์ ไม่งั้นเปิดหน้ามาค่าจะหาย
        $sources = $canEditSource
            ? SourceScope::allowedSubs($tracking->source_id)
            : collect();

        if ($canEditSource && $tracking->source && !array_key_exists($tracking->source->main_source, $sourceMains)) {
            $sourceMains[$tracking->source->main_source] = config("source.main.{$tracking->source->main_source}", $tracking->source->main_source);
        }

        $ads = $canEditAd
            ? Ad::where('brand', $tracking->brand ?: $authUser->brand)
                ->where('branch', $tracking->branch ?: $authUser->branch)
                ->where(fn ($q) => $q->where('is_active', 1)->orWhere('id', $tracking->clip_add))
                ->orderBy('name')
                ->get()
            : collect();

        return view('customer-tracking.view-more', compact(
            'tracking',
            'decisions',
            'canReassignSale',
            'saleUser',
            'canEditSource',
            'canEditAd',
            'sources',
            'sourceMains',
            'placeMain',
            'ads'
        ));
    }

    /** ย้ายผู้ขายของการติดตามนี้ไปให้เซลล์อีกคน */
    public function updateSale(Request $request, $id)
    {
        abort_unless(Auth::user()->canReassignSale(), 403);

        $tracking = CustomerTracking::findOrFail($id);

        $validated = $request->validate([
            'sale_id' => 'required|exists:users,id',
        ], [
            'sale_id.required' => 'กรุณาเลือกผู้ขาย',
            'sale_id.exists'   => 'ไม่พบผู้ขายที่เลือก',
        ]);

        // เลือกได้เฉพาะเซลล์ที่อยู่ใน pool ของ brand นี้ (กันยิง id มั่วผ่าน devtools)
        $allowedIds = User::salePoolForBrand((int) ($tracking->brand ?: Auth::user()->brand), (int) $tracking->sale_id)
            ->pluck('id')
            ->all();

        if (!in_array((int) $validated['sale_id'], array_map('intval', $allowedIds), true)) {
            return response()->json(['success' => false, 'message' => 'ผู้ขายที่เลือกไม่อยู่ในแบรนด์นี้'], 422);
        }

        $tracking->update(['sale_id' => (int) $validated['sale_id']]);

        return response()->json([
            'success'   => true,
            'sale_name' => $tracking->fresh('sale')->sale->name ?? '-',
        ]);
    }

    /**
     * แก้แหล่งที่มาของ "ใบนี้" ได้ไหม
     * เซลล์แก้ได้เฉพาะการติดตามที่ตัวเองถืออยู่ (เห็นทั้งสาขาแต่แก้ของเพื่อนไม่ได้)
     * role อื่นที่อยู่ในลิสต์แก้ได้ทุกใบที่มองเห็น
     */
    private function canEditTrackingSourceOf(CustomerTracking $tracking): bool
    {
        $user = Auth::user();

        if (!$user->canEditTrackingSource()) {
            return false;
        }

        return !$user->editsTrackingSourceOwnOnly()
            || (int) $tracking->sale_id === (int) $user->id;
    }

    /**
     * แก้แหล่งที่มา / สถานที่ / คลิปที่ยิงแอด ของการติดตามย้อนหลัง
     * กติกาเดียวกับตอนสร้าง (store): place บังคับเมื่อแหล่งที่มาหลัก = offline และต้องเป็นสถานที่ที่อนุมัติแล้ว
     * แหล่งที่มาที่ไม่ใช่ offline จะล้าง place_id ทิ้ง กันค่าค้างจากของเดิม
     */
    public function updateSource(Request $request, $id)
    {
        $authUser = Auth::user();
        $tracking = CustomerTracking::findOrFail($id);

        abort_unless($this->canEditTrackingSourceOf($tracking), 403);

        $source    = TbSalecarType::withTrashed()->find($request->source_id);
        $placeMain = config('source.place_main', 'offline');
        $isOffline = $source && $source->main_source === $placeMain;

        // แหล่งที่มาที่ไม่ใช่ offline ไม่ต้องตรวจ place เลย — ค่าที่ค้างมาจากของเดิมจะถูกล้างทิ้งอยู่แล้ว
        $placeRules = $isOffline
            ? [
                'required',
                Rule::exists('tb_source_place', 'id')
                    ->where('salecar_type_id', $request->source_id)
                    ->where('status', SourcePlace::STATUS_APPROVED)
                    ->whereNull('deleted_at'),
            ]
            : ['nullable'];

        // ย้ายได้เฉพาะกลุ่มที่ตัวเองคีย์ได้ + ค่าเดิมของใบนี้ (ค่าเดิมอาจอยู่ในกลุ่มที่ถูกซ่อน
        // เช่นเซลล์เปิดใบที่แอดมินลงเป็น "Online บริษัท" ไว้ — ต้องกดบันทึกช่องอื่นได้โดยไม่ติด validate)
        $allowedMains = array_keys(SourceScope::allowedMains());

        $validator = Validator::make($request->all(), [
            'source_id' => [
                'required',
                Rule::exists('tb_salecar_type', 'id')
                    ->where(fn ($q) => $q->whereIn('main_source', $allowedMains)
                        ->orWhere('id', $tracking->source_id)),
            ],
            'place_id'  => $placeRules,
            'clip_add'  => 'nullable|exists:tb_ad,id',
        ], [
            'source_id.required' => 'กรุณาเลือกแหล่งที่มาย่อย',
            'source_id.exists'   => 'แหล่งที่มาย่อยไม่ถูกต้อง',
            'place_id.required'  => 'กรุณาเลือกสถานที่',
            'place_id.exists'    => 'สถานที่ไม่ถูกต้อง',
            'clip_add.exists'    => 'ไม่พบคลิปที่เลือก',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $data = [
            'source_id' => (int) $request->source_id,
            'place_id'  => $isOffline ? ($request->place_id ?: null) : null,
        ];

        // คลิปแอดแก้ได้เฉพาะ admin/adminPage — role อื่นส่งมาก็ไม่รับ ค่าเดิมคงไว้
        if ($authUser->canEditTrackingAd()) {
            $data['clip_add'] = $request->clip_add ?: null;
        }

        $tracking->update($data);
        $tracking->refresh()->load(['source', 'place', 'ad']);

        $mainKey = $tracking->source->main_source ?? null;

        return response()->json([
            'success'     => true,
            'source_main' => $mainKey ? config("source.main.$mainKey", $mainKey) : '-',
            'source_name' => $tracking->source->name ?? '-',
            'place_name'  => $tracking->place->name ?? '-',
            'is_offline'  => $mainKey === $placeMain,
            'ad_text'     => $tracking->ad
                ? trim($tracking->ad->name . ($tracking->ad->url ? "\n" . $tracking->ad->url : ''))
                : '-',
        ]);
    }

    public function addDetail(Request $request, $id)
    {
        $request->validate([
            'contact_date'   => 'required|date',
            'contact_status' => 'required|in:1,0',
            'decision_id'    => 'required|exists:tb_decision,id',
        ], [
            'decision_id.required' => 'กรุณาเลือกสถานะการตัดสินใจ',
            'decision_id.exists'   => 'สถานะการตัดสินใจไม่ถูกต้อง',
        ]);

        $user       = Auth::user();
        $isSaleRole = in_array($user->role, ['sale', 'adminPage', 'audit', 'audit_lead', 'audit_dp', 'gm']);
        $entryType  = $isSaleRole ? 'sale' : 'manager';
        $decisionId = $request->decision_id ?: null;

        DB::beginTransaction();
        try {
            CustomerTrackingDetail::create([
                'tracking_id'    => $id,
                'contact_date'   => $this->toGregorian($request->contact_date),
                'contact_status' => $request->contact_status,
                'decision_id'    => $decisionId,
                'comment_sale'   => $request->comment_sale,
                'entry_type'     => $entryType,
                'UserInsert'     => $user->id,
            ]);

            if (!$isSaleRole && $decisionId) {
                $followUpDays = match ((int) $decisionId) {
                    1 => [3, 6],
                    2 => [15, 30],
                    3 => [120, 240],
                    4 => [180, 360],
                    default => [],
                };

                $baseDate = Carbon::parse($request->contact_date);

                foreach ($followUpDays as $index => $days) {
                    $isLast = ($index === array_key_last($followUpDays));
                    CustomerTrackingDetail::create([
                        'tracking_id'    => $id,
                        'contact_date'   => $baseDate->copy()->addDays($days)->format('Y-m-d'),
                        'contact_status' => null, // checkpoint อัตโนมัติ → เว้นว่างไว้จนกว่าจะติดต่อจริง
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

        $isAutoDecision = in_array((int) $request->decision_id, [1, 2, 3, 4]);

        $followUpDays = match ((int) $request->decision_id) {
            1 => [3, 6, 9],
            2 => [15, 30, 45],
            3 => [120, 240, 360],
            4 => [180, 360, 540],
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
                    'contact_status' => null, // checkpoint อัตโนมัติ → เว้นว่างไว้จนกว่าจะติดต่อจริง
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
        return Excel::download(new CustomerTrackingExport(), ExportFilename::withBrand('รายงานการติดตามลูกค้า.xlsx'));
    }

    public function exportExcelByDate(Request $request)
    {
        if (Auth::user()->role === 'sale') {
            abort(403);
        }

        $dateFrom = $request->date_from ?? now()->toDateString();
        $dateTo   = $request->date_to   ?? now()->toDateString();
        $filename = ExportFilename::withBrand('รายงานการกรอกข้อมูล_' . $dateFrom . '_ถึง_' . $dateTo . '.xlsx');

        return Excel::download(new CustomerTrackingByDateExport($dateFrom, $dateTo), $filename);
    }

    public function exportDailyReport(Request $request)
    {
        $date     = $request->date ?? now()->toDateString();
        $filename = ExportFilename::withBrand('รายงานประจำวัน_' . $date . '.xlsx');

        return Excel::download(new CustomerTrackingDailyExport($date), $filename);
    }

    public function exportOverdueReport(Request $request)
    {
        if (Auth::user()->role === 'sale') {
            abort(403);
        }

        $month    = $request->month ?? now()->format('Y-m');
        $filename = ExportFilename::withBrand('รายงานเลยกำหนดติดตามลูกค้า_' . $month . '.xlsx');

        return Excel::download(new CustomerTrackingOverdueReport($month), $filename);
    }

    // รายงานเลยกำหนดติดตาม (เซลล์) — เห็นได้ทุก role ; role = sale เห็นเฉพาะของตัวเอง (คนอื่นเห็นทั้งหมด)
    public function exportOverdueSaleReport(Request $request)
    {
        $user     = Auth::user();
        $month    = $request->month ?? now()->format('Y-m');
        $saleId   = $user->role === 'sale' ? $user->id : null;
        $filename = ExportFilename::withBrand('รายงานเลยกำหนดติดตามลูกค้า(เซลล์)_' . $month . '.xlsx');

        return Excel::download(new CustomerTrackingOverdueReport($month, $saleId), $filename);
    }

    // รายงานลูกค้าจากงาน Offline แยกตามสถานที่ (1 สถานที่ = 1 sheet) — ซ่อนจาก role sale เหมือนรายงานอื่นในแถวนี้
    public function exportOfflinePlaceReport(Request $request)
    {
        if (Auth::user()->role === 'sale') {
            abort(403);
        }

        $month    = $request->month ?: now()->format('Y-m');
        $filename = ExportFilename::withBrand('รายงานลูกค้างาน Offline แยกสถานที่_' . $month . '.xlsx');

        return Excel::download(new CustomerTrackingOfflinePlaceReport($month), $filename);
    }

    public function saveTestDrive(Request $request, $id)
    {
        $tracking = CustomerTracking::findOrFail($id);

        $request->validate([
            'attachments'   => ['array'],
            'attachments.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $data = [
            'test_drive_date' => $this->toGregorian($request->test_drive_date ?: null),
            'test_drive_note' => $request->test_drive_note ?: null,
        ];

        if ($request->hasFile('attachments')) {
            try {
                $data['test_drive_attachments'] = $this->uploadTestDriveFiles($tracking, $request->file('attachments'));
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'อัปโหลดไฟล์ไม่สำเร็จ กรุณาลองใหม่',
                ], 500);
            }
        }

        $tracking->update($data);

        // ส่งรายการไฟล์ล่าสุดกลับไป ให้หน้าจอวาดใหม่ได้โดยไม่ต้อง reload
        return response()->json([
            'success'     => true,
            'attachments' => $this->testDriveAttachmentPayload($tracking->fresh()),
        ]);
    }

    /**
     * อัปโหลดหลักฐานทดลองขับขึ้น OneDrive แล้วต่อท้ายรายการเดิม
     * โฟลเดอร์: New Car/{แบรนด์}/หลักฐานทดลองขับ/{id-ชื่อลูกค้า} — วางคู่กับ "หลักฐานการจอง" ที่ใช้อยู่เดิม
     */
    private function uploadTestDriveFiles(CustomerTracking $tracking, array $files): array
    {
        $customer = $tracking->customer;
        $customerFolder = $tracking->customer_id . '-' . ($customer->FirstName ?? 'unknown');
        $brandName = $tracking->brandInfo->name ?? (Auth::user()->brandInfo->name ?? 'Other');
        $folder = "New Car/{$brandName}/หลักฐานทดลองขับ/{$customerFolder}";

        $oneDrive = new OneDriveService();
        $existing = is_array($tracking->test_drive_attachments) ? $tracking->test_drive_attachments : [];

        foreach ($files as $index => $file) {
            $fileName = 'testdrive_' . $tracking->id . '_' . ($index + 1) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $existing[] = [
                'url'  => $oneDrive->upload($file->getRealPath(), $fileName, $folder),
                'name' => $file->getClientOriginalName(),
            ];
        }

        return $existing;
    }

    /** รูปแบบข้อมูลไฟล์ที่หน้าจอใช้วาดรายการ (url ผ่าน proxy เพื่อไม่หลุด share link ตรง ๆ) */
    private function testDriveAttachmentPayload(CustomerTracking $tracking): array
    {
        $items = is_array($tracking->test_drive_attachments) ? $tracking->test_drive_attachments : [];

        return collect($items)->values()->map(function ($item, $i) use ($tracking) {
            $url  = is_array($item) ? $item['url'] ?? '' : $item;
            $name = is_array($item) ? $item['name'] ?? null : null;
            $base = route('customer-tracking.test-drive.proxy', $tracking->id);

            return [
                'index' => $i,
                'name'  => $name,
                'ext'   => $name ? strtolower(pathinfo($name, PATHINFO_EXTENSION)) : null,
                'url'   => $name
                    ? $base . '/' . rawurlencode($name) . '?url=' . urlencode($url)
                    : $base . '?url=' . urlencode($url),
            ];
        })->all();
    }

    /** ส่งไฟล์หลักฐานทดลองขับผ่าน server — กันคนเดา share url ของ OneDrive ตรง ๆ */
    public function proxyTestDriveAttachment(Request $request, $id, $filename = null)
    {
        $tracking = CustomerTracking::findOrFail($id);
        $shareUrl = $request->input('url');

        $allowed = collect($tracking->test_drive_attachments ?? [])->contains(
            fn($item) => (is_array($item) ? ($item['url'] ?? '') : $item) === $shareUrl
        );

        if (!$allowed) {
            abort(403);
        }

        try {
            $oneDrive = new OneDriveService();
            ['url' => $downloadUrl, 'name' => $filename] = $oneDrive->getDownloadInfo($shareUrl);

            $response = (new Client(['allow_redirects' => true]))->get($downloadUrl);

            return response($response->getBody()->getContents(), 200, [
                'Content-Type'        => $response->getHeader('Content-Type')[0] ?? 'application/octet-stream',
                'Content-Disposition' => "inline; filename=\"{$filename}\"",
                'Cache-Control'       => 'private, max-age=3600',
            ]);
        } catch (\Exception $e) {
            abort(404);
        }
    }

    /** ลบหลักฐานทดลองขับออกจากรายการ (ไฟล์บน OneDrive ยังอยู่ เหมือนหลักฐานการจอง) */
    public function deleteTestDriveAttachment(Request $request, $id)
    {
        $tracking = CustomerTracking::findOrFail($id);

        $index = (int) $request->input('index');
        $items = is_array($tracking->test_drive_attachments) ? $tracking->test_drive_attachments : [];

        if (!isset($items[$index])) {
            return response()->json(['success' => false, 'message' => 'ไม่พบไฟล์'], 404);
        }

        array_splice($items, $index, 1);
        $tracking->update(['test_drive_attachments' => $items]);

        return response()->json([
            'success'     => true,
            'attachments' => $this->testDriveAttachmentPayload($tracking->fresh()),
        ]);
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

    public function cancelTracking(Request $request, $id)
    {
        // finished = "จบการติดตาม" (ไม่ต้องเลือกเหตุผล) | cancelled = "ยกเลิกการติดตาม" (บังคับเหตุผล)
        $endType = $request->input('end_type') === 'finished' ? 'finished' : 'cancelled';

        if ($endType === 'cancelled') {
            $request->validate([
                'reason'      => 'required|in:ไม่ผ่านอนุมัติไฟแนนซ์,ออกรถแบรนด์อื่น,เคสชนกัน,อื่นๆ',
                'reason_note' => 'required_if:reason,อื่นๆ|nullable|string|max:1000',
            ], [
                'reason.required'         => 'กรุณาเลือกเหตุผล',
                'reason.in'               => 'เหตุผลไม่ถูกต้อง',
                'reason_note.required_if' => 'กรุณากรอกเหตุผล',
            ]);
        }

        $tracking = CustomerTracking::findOrFail($id);
        $tracking->update([
            'cancelled_at'       => now(),
            'CancelledBy'        => Auth::id(),
            'end_type'           => $endType,
            'cancel_reason'      => $endType === 'cancelled' ? $request->reason : null,
            'cancel_reason_note' => ($endType === 'cancelled' && $request->reason === 'อื่นๆ') ? trim($request->reason_note) : null,
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
        $idNumber = Customer::normalizeIdNumber($request->IDNumber);
        $mobile   = $request->Mobilephone1 ? preg_replace('/\D/', '', $request->Mobilephone1) : null;
        // เก็บค่าว่างเป็น null ไม่ใช่ '' — ไม่งั้นค่าว่างจะกองรวมกันในคอลัมน์แล้วชนกันเอง
        // ตอนเช็คซ้ำรอบหน้า (เคสเดียวกับที่ทำให้เด้งชื่อลูกค้าคนเดิมตลอด)
        $mobile   = $mobile ?: null;
        // '-' / '.' ที่เซลใส่แทนช่องว่าง ต้องเก็บเป็น null ไม่งั้นมันจะไป "จอง" ค่านั้น
        // แล้วบล็อกลูกค้าคนถัดไปที่พิมพ์เหมือนกัน
        $lineId   = Customer::normalizeContactValue($request->LineID);
        $facebook = Customer::normalizeContactValue($request->FacebookName);

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

        if ($lineId) {
            $lineExists = Customer::withTrashed()->where('LineID', $lineId)->exists();
            if ($lineExists) {
                return response()->json(['success' => false, 'message' => 'Line ID นี้มีอยู่ในระบบแล้ว'], 422);
            }
        }

        // FacebookName ไม่บล็อกซ้ำ — มันคือชื่อที่ตั้งเอง คนละคนซ้ำกันได้เป็นเรื่องปกติ
        // ฝั่งหน้าเว็บจะเตือนให้ดูก่อนแล้วให้เลือกเองว่า "คนเดียวกัน" หรือ "สร้างใหม่"
        // ถ้าบล็อกตรงนี้ด้วย ปุ่มสร้างใหม่จะกดไม่ผ่าน

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
            'LineID'       => $lineId,
            'FacebookName' => $facebook,
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
