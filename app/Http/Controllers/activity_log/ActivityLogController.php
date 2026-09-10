<?php

namespace App\Http\Controllers\activity_log;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * ประวัติการแก้ไข — หน้ารวมของ activity_logs (admin เท่านั้น)
 *
 * ทุก model ที่ use Traits\LogsActivity จะโผล่ที่นี่อัตโนมัติ ไม่ต้องทำหน้าจอแยกรายส่วน
 * (ตั้งชื่อไทย/ชื่อรายการของแต่ละประเภทได้ที่ config/activity_log.php)
 *
 * ไม่กรอง brand — เป็นเครื่องมือตรวจสอบของ admin ที่ต้องเห็นข้ามแบรนด์
 * (log บางประเภท เช่น MOR ของ Floor Plan ก็ไม่มี brand อยู่แล้ว) มีตัวกรองแบรนด์ให้เลือกเอง
 */
class ActivityLogController extends Controller
{
    private function guard(): void
    {
        abort_unless(Auth::user()->role === 'admin', 403);
    }

    /** ประเภทที่เลือกไว้ตอนเปิดหน้า — ถ้า config ตั้งค่าผิด ให้ตกมาที่ประเภทแรกใน subjects */
    private function defaultSubject(): string
    {
        $subjects = config('activity_log.subjects', []);
        $default  = config('activity_log.default_subject');

        return isset($subjects[$default]) ? $default : (string) array_key_first($subjects);
    }

    public function index()
    {
        $this->guard();

        // ผู้ใช้ที่เคยมี log จริงเท่านั้น (ไม่ต้องไล่ทั้งตาราง users)
        $userIds = ActivityLog::distinct()->pluck('user_id')->filter();
        $users   = User::withTrashed()->whereIn('id', $userIds)
            ->orderBy('name')->get(['id', 'name', 'full_name']);

        return view('activity-log.view', [
            'subjects'       => config('activity_log.subjects', []),
            'events'         => config('activity_log.events', []),
            'users'          => $users,
            'brands'         => config('brand.names', []),
            'defaultSubject' => $this->defaultSubject(),
        ]);
    }

    /** ตาราง (serverSide) — activity_logs โตเรื่อย ๆ จึงต้องแบ่งหน้าที่ฝั่ง server */
    public function list(Request $request)
    {
        $this->guard();

        $draw   = (int) ($request->draw ?? 1);
        $start  = (int) ($request->start ?? 0);
        $length = (int) ($request->length ?? 25);
        $search = trim($request->input('search.value', ''));

        // ต้องมีประเภทเสมอ — ไม่ส่งมา (หรือส่งค่าที่ไม่รู้จัก) ให้ตกมาที่ค่าตั้งต้น
        // กันเปิด endpoint ตรง ๆ แล้วลากทุกประเภทออกมาพร้อมกันหลายพันแถว
        $subject = $request->filter_subject;
        if (!$subject || !array_key_exists($subject, config('activity_log.subjects', []))) {
            $subject = $this->defaultSubject();
        }

        $base = ActivityLog::query()
            ->where('subject_type', $subject)
            ->when($request->filled('filter_event'), fn($q) => $q->where('event', $request->filter_event))
            ->when($request->filled('filter_user'), fn($q) => $q->where('user_id', $request->filter_user))
            ->when($request->filled('filter_brand'), fn($q) => $q->where('brand', $request->filter_brand))
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('created_at', '<=', $request->date_to));

        $recordsTotal = (clone $base)->count();

        if ($search) {
            // ค้นได้ทั้ง id ของรายการ และเนื้อหาที่เปลี่ยน (changes เก็บเป็น JSON ข้อความ)
            $base->where(function ($q) use ($search) {
                $q->where('subject_id', $search)
                    ->orWhere('changes', 'like', "%{$search}%")
                    ->orWhere('url', 'like', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $base)->count();

        $logs = $base->with('user')
            ->orderByDesc('id')
            ->skip($start)->take($length)
            ->get();

        $subjectNames = $this->resolveSubjectNames($logs);
        $subjects     = config('activity_log.subjects', []);
        $events       = config('activity_log.events', []);
        $previewLines = (int) config('activity_log.preview_lines', 3);
        $brandNames   = config('brand.names', []);

        $data = $logs->map(function ($log) use ($subjectNames, $subjects, $events, $previewLines, $brandNames) {
            $sub = $subjects[$log->subject_type] ?? ['label' => $log->subject_type, 'class' => 'bg-secondary'];
            $ev  = $events[$log->event] ?? ['label' => $log->event, 'class' => 'bg-secondary'];

            $lines = $this->describeChanges($log);
            $shown = array_slice($lines, 0, $previewLines);

            $changesHtml = $shown
                ? implode('', array_map(fn($l) => '<div class="small">' . e($l) . '</div>', $shown))
                : '<span class="text-muted">—</span>';

            if (count($lines) > $previewLines) {
                $changesHtml .= '<button type="button" class="btn btn-link btn-sm p-0 btnLogDetail" data-id="' . $log->id . '">'
                    . 'ดูทั้งหมด (' . count($lines) . ')</button>';
            }

            $name = $subjectNames[$log->subject_type][$log->subject_id] ?? null;

            return [
                'at'      => optional($log->created_at)->format('d/m/Y H:i:s') ?: '-',
                'by'      => optional($log->user)->full_name ?: (optional($log->user)->name ?? '<span class="text-muted">ระบบ</span>'),
                'subject' => '<span class="badge rounded-pill ' . $sub['class'] . '">' . e($sub['label']) . '</span>',
                'item'    => '<div>#' . $log->subject_id . '</div>'
                    . ($name ? '<div class="small text-muted">' . e($name) . '</div>' : ''),
                'event'   => '<span class="badge rounded-pill ' . $ev['class'] . '">' . e($ev['label']) . '</span>',
                'changes' => $changesHtml,
                'brand'   => $log->brand ? e($brandNames[$log->brand] ?? $log->brand) : '-',
            ];
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data->values(),
        ]);
    }

    /** รายละเอียดเต็มของ log แถวเดียว (ตอนที่ตารางย่อไว้) */
    public function show($id)
    {
        $this->guard();

        $log      = ActivityLog::with('user')->findOrFail($id);
        $subjects = config('activity_log.subjects', []);
        $events   = config('activity_log.events', []);

        return view('activity-log.detail', [
            'log'     => $log,
            'lines'   => $this->describeChanges($log),
            'subject' => $subjects[$log->subject_type] ?? ['label' => $log->subject_type, 'class' => 'bg-secondary'],
            'event'   => $events[$log->event] ?? ['label' => $log->event, 'class' => 'bg-secondary'],
            'brands'  => config('brand.names', []),
        ]);
    }

    /**
     * หา "ชื่อรายการ" ของ log ในหน้านี้ — ยิงทีละประเภทแบบ whereIn (ไม่ใช่รายแถว)
     * DB อยู่ remote จำนวน query สำคัญกว่าจำนวนข้อมูล : หน้านึงได้ไม่เกิน 1 query ต่อประเภทที่โผล่
     *
     * ใช้ DB::table ตรง ๆ เพื่อข้าม global scope ทั้งหมด — admin ต้องเห็นชื่อรายการข้ามแบรนด์/สาขา
     * และแถวที่ถูกลบไปแล้วก็ยังต้องอ่านออก
     */
    private function resolveSubjectNames($logs): array
    {
        $subjects = config('activity_log.subjects', []);
        $out      = [];

        foreach ($logs->groupBy('subject_type') as $type => $group) {
            $conf = $subjects[$type]['subject'] ?? null;
            if (!$conf) {
                continue;
            }

            [$table, $column] = $conf;
            $ids = $group->pluck('subject_id')->unique()->all();

            $rows = DB::table($table)->whereIn('id', $ids)->pluck($column, 'id');

            // บางประเภทเก็บแค่ id ของอีกตาราง — ต่ออีกทอดให้เป็นชื่อที่คนอ่านออก
            if ($type === 'Salecar') {
                $names = DB::table('customers')->whereIn('id', $rows->filter()->unique())
                    ->get(['id', 'FirstName', 'LastName'])
                    ->keyBy('id');

                $rows = $rows->map(fn($cusId) => $cusId && isset($names[$cusId])
                    ? trim($names[$cusId]->FirstName . ' ' . $names[$cusId]->LastName)
                    : null);
            } elseif ($type === 'SourcePlaceClaim') {
                $places = DB::table('tb_source_place')->whereIn('id', $rows->filter()->unique())
                    ->pluck('location', 'id');

                $rows = $rows->map(fn($placeId) => $places[$placeId] ?? null);
            }

            $out[$type] = $rows->filter()->all();
        }

        return $out;
    }

    /** changes (JSON) → ['ชื่อฟิลด์: เก่า → ใหม่', ...] ; created/deleted ที่ไม่มี diff คืนอาร์เรย์ว่าง */
    private function describeChanges(ActivityLog $log): array
    {
        $changes = $log->changes ?? [];
        if (!is_array($changes)) {
            return [];
        }

        $labels = array_merge(
            config('activity_log.fields.*', []),
            config('activity_log.fields.' . $log->subject_type, [])
        );

        $fmt = function ($v) {
            if ($v === null || $v === '') {
                return '—';
            }
            if (!is_scalar($v)) {
                return json_encode($v, JSON_UNESCAPED_UNICODE);
            }
            // ค่าวันที่เขียนลง log หลายรูปแบบ (2026-08-01 00:00:00 / ISO 8601) — จัดให้อ่านง่ายรูปแบบเดียว
            if (($ts = $this->asTimestamp($v)) !== null) {
                $d = date('d/m/Y H:i', $ts);
                return str_ends_with($d, ' 00:00') ? date('d/m/Y', $ts) : $d;
            }
            return (string) $v;
        };

        $out = [];
        foreach ($changes as $field => $c) {
            if ($field === '_meta' || !is_array($c) || !array_key_exists('new', $c)) {
                continue;
            }

            $old = $c['old'] ?? null;
            $new = $c['new'];

            // ข้ามความเปลี่ยนแปลงหลอก — ค่าจริงเท่าเดิมแต่รูปแบบข้อความต่างกัน
            //   decimal : "1.00" → "1"
            //   วันที่   : "2026-08-01 00:00:00" → "2026-07-31T17:00:00.000000Z" (เวลาเดียวกัน คนละ timezone)
            // ตัดทิ้งแค่ตอนแสดงผล ข้อมูลใน activity_logs ยังอยู่ครบ
            if (is_numeric($old) && is_numeric($new) && (float) $old === (float) $new) {
                continue;
            }
            $oldTs = $this->asTimestamp($old);
            if ($oldTs !== null && $oldTs === $this->asTimestamp($new)) {
                continue;
            }

            // คอลัมน์ที่เก็บเป็น "รหัส" → แปลงเป็นชื่อจริงก่อนแสดง
            // สถานะใบจองอยู่ใน DB (tb_constatus) ที่เหลืออยู่ใน config (ดู value_maps)
            $map = $log->subject_type === 'Salecar' && $field === 'con_status'
                ? $this->conStatusNames()
                : $this->valueMap($log->subject_type, $field);

            if ($map) {
                $old = $map[$old] ?? $old;
                $new = $map[$new] ?? $new;
            }

            $out[] = ($labels[$field] ?? $field) . ': ' . $fmt($old) . ' → ' . $fmt($new);
        }

        return $out;
    }

    /**
     * ตารางแปลงรหัส→ชื่อ ของคอลัมน์นี้ (จาก config activity_log.value_maps) — ไม่มีก็คืน []
     * รองรับ config ทั้งแบบ [key => 'ชื่อ'] และ [key => ['label' => 'ชื่อ']]
     */
    private function valueMap(string $subjectType, string $field): array
    {
        $path = config("activity_log.value_maps.{$subjectType}.{$field}");
        if (!$path) {
            return [];
        }

        return collect(config($path, []))
            ->map(fn($v) => is_array($v) ? ($v['label'] ?? null) : $v)
            ->filter()
            ->all();
    }

    /** ชื่อสถานะใบจอง (tb_constatus) — โหลดครั้งเดียวต่อ request แล้วใช้ซ้ำทุกแถวในหน้า */
    private ?array $conStatusNames = null;

    private function conStatusNames(): array
    {
        return $this->conStatusNames ??= DB::table('tb_constatus')->pluck('name', 'id')->all();
    }

    /**
     * ค่านี้เป็น "วันที่" ไหม → คืน timestamp ถ้าใช่ / null ถ้าไม่ใช่
     * จับเฉพาะที่ขึ้นต้นด้วย YYYY-MM-DD เพื่อไม่ให้ข้อความอื่น (เลขตัวถัง, ทะเบียน) โดนตีความเป็นวันที่
     */
    private function asTimestamp($v): ?int
    {
        if (!is_string($v) || !preg_match('/^\d{4}-\d{2}-\d{2}([ T]|$)/', $v)) {
            return null;
        }
        $ts = strtotime($v);
        return $ts === false ? null : $ts;
    }
}
