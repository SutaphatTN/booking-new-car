<?php

namespace App\Http\Controllers\purchase_order;

use App\Exports\commission\StaffCommissionExport;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\StaffCommissionMonthly;
use App\Services\StaffCommissionQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

/**
 * ค่าคอมฝ่ายสนับสนุน — ผู้จัดการ / แอดมิน / ทะเบียน / การตลาด
 *
 * สิทธิ์ (ตามที่ตกลง):
 *  - admin / md / gm  : เห็นทุกคน + แก้ช่องที่กรอกเองได้
 *  - คนที่อยู่ใน config/staff_commission.php : เห็นเฉพาะของตัวเอง (อ่านอย่างเดียว)
 *  - role อื่น : เข้าไม่ได้
 * ไม่ผูกกับ brand — ยอดคิดจากแบรนด์ที่แต่ละคนดูแลตาม config ไม่ใช่แบรนด์ที่กำลังเปิดดู
 */
class StaffCommissionController extends Controller
{
    /** role ที่เห็นของทุกคนและแก้ได้ */
    public const MANAGE_ROLES = ['admin', 'md', 'gm'];

    private function canManage(): bool
    {
        return in_array(Auth::user()->role, self::MANAGE_ROLES, true);
    }

    /** เข้าหน้านี้ได้ไหม (ผู้ดูแล หรือเป็นคนที่มีสิทธิ์รับคอมเอง) */
    private function canAccess(): bool
    {
        return $this->canManage() || StaffCommissionQuery::isStaff((int) Auth::id());
    }

    /** แปลง "YYYY-MM" เป็น [year, month] ; ไม่ส่งมาใช้เดือนปัจจุบัน */
    private function resolveMonth($input): array
    {
        if ($input && preg_match('/^(\d{4})-(\d{2})$/', $input, $m)) {
            return [(int) $m[1], (int) $m[2]];
        }

        return [(int) Carbon::now()->year, (int) Carbon::now()->month];
    }

    public function index()
    {
        abort_unless($this->canAccess(), 403);

        return view('purchase-order.commission.staff.view');
    }

    /** ตารางรายชื่อ (DataTables) */
    public function list(Request $request)
    {
        abort_unless($this->canAccess(), 403);

        [$year, $month] = $this->resolveMonth($request->input('month'));

        // ไม่ใช่ผู้ดูแล → เห็นแถวของตัวเองแถวเดียว
        $onlyIds = $this->canManage() ? null : [(int) Auth::id()];
        $rows = StaffCommissionQuery::forMonth($year, $month, $onlyIds);

        $users = User::withTrashed()->whereIn('id', array_keys($rows))->get()->keyBy('id');

        $data = collect($rows)
            ->map(function ($d, $uid) use ($users) {
                $u = $users->get($uid);

                return [
                    'uid'      => (int) $uid,
                    'name'     => $u->name ?? ('(ไม่พบผู้ใช้ #' . $uid . ')'),
                    'dept'     => $d['label'],
                    'detail'   => collect($d['buckets'])
                        ->map(fn($b) => $b['name'] . ' ' . $b['count'] . ' คัน')
                        ->implode('<br>') ?: '-',
                    'carTotal' => number_format($d['car_total'], 2),
                    'extra'    => number_format($d['extra_total'], 2),
                    'total'    => number_format($d['total'], 2),
                    'active'   => $d['active'],
                ];
            })
            ->sortByDesc(fn($r) => (float) str_replace(',', '', $r['total']))
            ->values()
            ->map(function ($r, $i) {
                return [
                    'No'         => $i + 1,
                    'name'       => $r['name'] . '<br><span class="text-muted small">' . $r['dept'] . '</span>',
                    'detail'     => $r['detail'],
                    'carTotal'   => $r['carTotal'],
                    'extra'      => $r['extra'],
                    'total'      => $r['total'],
                    'DT_RowData' => ['uid' => $r['uid']],
                ];
            });

        return response()->json(['data' => $data]);
    }

    /** modal เลือกเดือนก่อนโหลดรายงาน */
    public function reportView()
    {
        abort_unless($this->canAccess(), 403);

        return view('purchase-order.commission.staff.report');
    }

    /** รายงาน Excel ของเดือนที่เลือก — ยอดชุดเดียวกับตารางในหน้าจอ */
    public function export(Request $request)
    {
        abort_unless($this->canAccess(), 403);

        [$year, $month] = $this->resolveMonth($request->input('month'));

        // ไม่ใช่ผู้ดูแล → ได้เฉพาะของตัวเอง (กติกาเดียวกับตาราง)
        $onlyIds = $this->canManage() ? null : [(int) Auth::id()];

        return Excel::download(
            new StaffCommissionExport($year, $month, $onlyIds),
            sprintf('staff-commission-%04d-%02d.xlsx', $year, $month)
        );
    }

    /** รายละเอียดรายคน + ฟอร์มกรอกช่องที่กรอกเอง */
    public function detail(Request $request, $userId)
    {
        abort_unless($this->canAccess(), 403);
        abort_unless($this->canManage() || (int) $userId === (int) Auth::id(), 403);
        abort_unless(StaffCommissionQuery::isStaff((int) $userId), 404);

        [$year, $month] = $this->resolveMonth($request->input('month'));

        $months = [1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน', 5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม', 9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'];

        return view('purchase-order.commission.staff.detail', [
            'staffUser'  => User::withTrashed()->find($userId),
            'data'       => StaffCommissionQuery::forStaff((int) $userId, $year, $month),
            'year'       => $year,
            'month'      => $month,
            'monthLabel' => ($months[$month] ?? $month) . ' ' . ($year + 543),
            'canEdit'    => $this->canManage(),
        ]);
    }

    /** บันทึกช่องที่กรอกเอง (LAS / PDS / Lepas ฯลฯ) */
    public function save(Request $request)
    {
        abort_unless($this->canManage(), 403);

        $data = $request->validate([
            'user_id' => 'required|integer',
            'year'    => 'required|integer',
            'month'   => 'required|integer|min:1|max:12',
            'note'    => 'nullable|string|max:255',
            'extras'  => 'nullable|array',
        ]);

        abort_unless(StaffCommissionQuery::isStaff((int) $data['user_id']), 404);

        // รับเฉพาะ key ที่ประกาศไว้ใน config ของคนนั้น — กันยิงค่าอื่นเข้ามา
        $conf = (array) config('staff_commission.staff.' . $data['user_id'] . '.extras', []);
        $clean = [];
        foreach ($conf as $e) {
            $raw = $data['extras'][$e['key']] ?? null;
            $clean[$e['key']] = ($e['type'] ?? 'money') === 'bool'
                ? (bool) $raw
                : (float) str_replace(',', '', (string) ($raw ?? 0));
        }

        StaffCommissionMonthly::updateOrCreate(
            [
                'user_id' => $data['user_id'],
                'year'    => $data['year'],
                'month'   => $data['month'],
            ],
            [
                'extras' => $clean,
                'note'   => trim($data['note'] ?? '') ?: null,
            ]
        );

        return response()->json(['status' => 'success']);
    }
}
