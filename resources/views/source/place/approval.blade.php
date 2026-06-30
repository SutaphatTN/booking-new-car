<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>อนุมัติค่าใช้จ่ายกิจกรรมการตลาด</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --green: #10b981; --green-d: #059669;
            --red: #ef4444; --red-d: #dc2626;
            --ink: #0f172a; --muted: #64748b; --line: #e2e8f0; --bg: #eef2f7;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Sarabun', 'Segoe UI', Tahoma, sans-serif;
            background: linear-gradient(135deg, #eef2f7 0%, #e6efe9 100%);
            margin: 0; padding: 32px 16px; color: var(--ink); min-height: 100vh;
        }
        .card {
            max-width: 1040px; margin: 0 auto; background: #fff;
            border-radius: 18px; box-shadow: 0 12px 40px rgba(15,23,42,.12); overflow: hidden;
        }
        .hd {
            background: linear-gradient(120deg, #10b981, #059669);
            color: #fff; padding: 26px 30px; display: flex; align-items: center; gap: 16px;
        }
        .hd .icon {
            width: 52px; height: 52px; border-radius: 14px; flex: none;
            background: rgba(255,255,255,.2); display: flex; align-items: center; justify-content: center; font-size: 26px;
        }
        .hd h1 { margin: 0; font-size: 20px; font-weight: 700; }
        .hd p { margin: 2px 0 0; font-size: 13px; opacity: .9; font-weight: 300; }
        .bd { padding: 28px 30px 34px; }

        .alert { display: flex; align-items: center; gap: 12px; padding: 16px 18px; border-radius: 12px; font-size: 15px; margin-bottom: 22px; }
        .alert .ai { font-size: 22px; }
        .alert-ok { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-no { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-info { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }

        .meta { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; margin-bottom: 24px; }
        .chip { background: #f8fafc; border: 1px solid var(--line); border-radius: 12px; padding: 12px 14px; }
        .chip .k { font-size: 12px; color: var(--muted); margin-bottom: 3px; }
        .chip .v { font-size: 15px; font-weight: 600; }
        .chip.total { background: linear-gradient(120deg, #ecfdf5, #d1fae5); border-color: #a7f3d0; }
        .chip.total .v { color: var(--green-d); }

        .tbl-wrap { border: 1px solid var(--line); border-radius: 12px; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        table.t-place { min-width: 760px; }
        table.t-topup { min-width: 560px; }
        thead th { background: #f1f5f9; color: #334155; font-weight: 600; padding: 11px 12px; text-align: left; border-bottom: 1px solid var(--line); white-space: nowrap; }
        tbody td { padding: 10px 12px; border-bottom: 1px solid #f1f5f9; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:nth-child(even) { background: #fafbfc; }
        .num { text-align: right; font-variant-numeric: tabular-nums; }
        .center { text-align: center; }
        tfoot td { padding: 13px 12px; background: #f0fdf4; font-weight: 700; }

        .actions { margin-top: 28px; display: flex; gap: 20px; flex-wrap: wrap; align-items: flex-end; }
        .btn { border: none; border-radius: 10px; padding: 12px 30px; font-size: 15px; font-weight: 600; color: #fff; cursor: pointer; font-family: inherit; transition: transform .05s, box-shadow .2s; }
        .btn:active { transform: translateY(1px); }
        .btn-approve { background: linear-gradient(120deg, #10b981, #059669); box-shadow: 0 6px 16px rgba(16,185,129,.35); }
        .btn-reject { background: linear-gradient(120deg, #f59e0b, #d97706); box-shadow: 0 6px 16px rgba(245,158,11,.35); }
        .reject-box { flex: 1; min-width: 260px; }
        .reject-box label { display: block; font-size: 13px; color: var(--muted); margin-bottom: 6px; }
        textarea { width: 100%; border: 1px solid var(--line); border-radius: 10px; padding: 10px 12px; font-family: inherit; font-size: 14px; resize: vertical; }
        textarea:focus { outline: none; border-color: var(--green); box-shadow: 0 0 0 3px rgba(16,185,129,.15); }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 20px; color: #fff; font-size: 13px; font-weight: 600; }
        .foot-note { margin-top: 22px; font-size: 12px; color: var(--muted); text-align: center; }
        @media (max-width: 640px) {
            .bd { padding: 20px 16px 26px; } .hd { padding: 20px; }
            .actions { flex-direction: column; align-items: stretch; } .btn { width: 100%; }
        }
    </style>
</head>
<body>
@php
    $statuses = config('source.statuses', []);
    $st = $statuses[$req->status] ?? ['label' => $req->status, 'class' => ''];
    $isTopup = ($req->type ?? 'place') === 'topup';
    $lines = $isTopup ? $req->topupPlaces : $req->places;
    $total = $isTopup ? $lines->sum(fn($p) => (float) ($p->pending_extra ?? 0)) : $lines->sum('cost');
    $fmtDate = fn($d) => $d ? $d->format('d/m/') . ($d->year + 543) : '';
    $badgeColor = ['approved' => '#059669', 'rejected' => '#dc2626', 'pending' => '#d97706'][$req->status] ?? '#64748b';
@endphp
<div class="card">
    <div class="hd">
        <div class="icon">📋</div>
        <div>
            <h1>{{ $isTopup ? 'ขออนุมัติเพิ่มงบประมาณกิจกรรมการตลาด' : 'ขออนุมัติค่าใช้จ่ายกิจกรรมการตลาด' }}</h1>
            <p>{{ $isTopup ? 'เอกสารขออนุมัติเพิ่มงบประมาณ' : 'เอกสารขออนุมัติสถานที่' }} · ประจำเดือน {{ $req->period ?? '-' }}</p>
        </div>
    </div>
    <div class="bd">

        @if (!empty($justDecided))
            @if ($req->status === 'approved')
                <div class="alert alert-ok">
                    <span class="ai">✅</span>
                    <div>อนุมัติเรียบร้อยแล้ว</div>
                </div>
            @else
                <div class="alert alert-info">
                    <span class="ai">✎</span>
                    <div>ส่งกลับให้ผู้ขอแก้ไขเรียบร้อยแล้ว — ระบบได้แจ้งผู้ขอทางอีเมลแล้ว</div>
                </div>
            @endif
        @elseif (!empty($alreadyDone))
            <div class="alert alert-info">
                <span class="ai">ℹ️</span>
                <div>คำขอนี้ถูกดำเนินการไปแล้ว — สถานะปัจจุบัน: <strong>{{ $st['label'] }}</strong>
                    @if ($req->decided_at) (เมื่อ {{ $req->decided_at->format('d/m/Y H:i') }} น.) @endif
                </div>
            </div>
        @endif

        <div class="meta">
            <div class="chip"><div class="k">ผู้ขออนุมัติ</div><div class="v">{{ optional($req->requester)->full_name ?: (optional($req->requester)->name ?? '-') }}</div></div>
            <div class="chip"><div class="k">ผู้อนุมัติ</div><div class="v">{{ optional($req->approver)->full_name ?: (optional($req->approver)->name ?? '-') }}</div></div>
            <div class="chip"><div class="k">ประจำเดือน</div><div class="v">{{ $req->period ?? '-' }}</div></div>
            <div class="chip"><div class="k">จำนวนรายการ</div><div class="v">{{ $lines->count() }} รายการ</div></div>
            <div class="chip total"><div class="k">{{ $isTopup ? 'ยอดที่ขอเพิ่มทั้งหมด' : 'ยอดรวมทั้งหมด' }}</div><div class="v">{{ number_format($total, 2) }} ฿</div></div>
        </div>

        <div class="tbl-wrap">
            @if ($isTopup)
            <table class="t-topup">
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>สถานที่</th>
                        <th>ประเภทบูธ</th>
                        <th class="num" style="width:120px;">งบเดิม (฿)</th>
                        <th class="num" style="width:120px;">ขอเพิ่ม (฿)</th>
                        <th class="num" style="width:130px;">งบรวมใหม่ (฿)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lines as $i => $p)
                        @php
                            $base = (float) ($p->cost ?? 0) + (float) ($p->extra_cost ?? 0);
                            $add  = (float) ($p->pending_extra ?? 0);
                        @endphp
                        <tr>
                            <td class="center">{{ $i + 1 }}</td>
                            <td>{{ $p->location }}</td>
                            <td>{{ $p->source->name ?? '-' }}</td>
                            <td class="num">{{ number_format($base, 2) }}</td>
                            <td class="num">{{ number_format($add, 2) }}</td>
                            <td class="num">{{ number_format($base + $add, 2) }}</td>
                        </tr>
                        @if ($p->extra_reason)
                            <tr><td></td><td colspan="5" style="color:#92400e;">เหตุผล: {{ $p->extra_reason }}</td></tr>
                        @endif
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="num">รวมที่ขอเพิ่ม</td>
                        <td class="num">{{ number_format($total, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            @else
            <table class="t-place">
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>LAS Number</th>
                        <th>วันเริ่ม</th>
                        <th>วันจบ</th>
                        <th>ประเภทบูธ</th>
                        <th>สถานที่</th>
                        <th>ประเภทค่าใช้จ่าย</th>
                        <th class="num" style="width:100px;">ประมาณค่าใช้จ่าย (฿)</th>
                        <th class="num" style="width:110px;">เป้า PP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lines as $i => $p)
                        <tr>
                            <td class="center">{{ $i + 1 }}</td>
                            <td>{{ $p->las_number ?? '-' }}</td>
                            <td class="center">{{ $fmtDate($p->start_date) ?: '-' }}</td>
                            <td class="center">{{ $fmtDate($p->end_date) ?: '-' }}</td>
                            <td>{{ $p->source->name ?? '-' }}</td>
                            <td>{{ $p->location }}</td>
                            <td>{{ $p->expense_type ?? '-' }}</td>
                            <td class="num">{{ $p->cost !== null ? number_format($p->cost, 2) : '-' }}</td>
                            <td class="num">{{ $p->target !== null ? number_format($p->target, 0) : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="7" class="num">รวม</td>
                        <td class="num">{{ number_format($total, 2) }}</td>
                        <td class="num">{{ number_format($lines->sum('target'), 0) }}</td>
                    </tr>
                </tfoot>
            </table>
            @endif
        </div>

        @if ($req->status === 'pending' && empty($justDecided))
            <div class="actions">
                <form method="POST" action="{{ route('source.approval.approve', $req->token) }}">
                    <button type="button" class="btn btn-approve" data-confirm
                        data-title="ยืนยันอนุมัติทั้งหมด?" data-text="ระบบจะอนุมัติสถานที่ทั้งหมดในใบนี้"
                        data-icon="question" data-ok="อนุมัติ" data-color="#6c5ffc">✓ อนุมัติทั้งหมด</button>
                </form>
                <form method="POST" action="{{ route('source.approval.reject', $req->token) }}" class="reject-box">
                    <label>สิ่งที่ต้องแก้ไข (ระบุให้ผู้ขอทราบ)</label>
                    <textarea name="reject_reason" rows="2" placeholder="ระบุสิ่งที่ต้องแก้ไข..."></textarea>
                    <div style="margin-top:10px;">
                        <button type="button" class="btn btn-reject" data-confirm
                            data-title="ส่งกลับให้แก้ไข?" data-text="ระบบจะส่งคำขอกลับให้ผู้ขอแก้ไข และแจ้งทางอีเมล"
                            data-icon="warning" data-ok="ส่งกลับ" data-color="#6c5ffc">✎ ส่งกลับให้แก้ไข</button>
                    </div>
                </form>
            </div>
        @endif

        <div class="foot-note">เอกสารนี้สร้างจากระบบจองรถอัตโนมัติ · กรุณาตรวจสอบรายละเอียดก่อนอนุมัติ</div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.querySelectorAll('[data-confirm]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var form = btn.closest('form');
            Swal.fire({
                title: btn.dataset.title,
                text: btn.dataset.text,
                icon: btn.dataset.icon || 'question',
                showCancelButton: true,
                confirmButtonText: btn.dataset.ok || 'ยืนยัน',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: btn.dataset.color || '#6c5ffc',
                cancelButtonColor: '#d33',
                reverseButtons: true,
                buttonsStyling: true
            }).then(function (r) {
                if (r.isConfirmed) {
                    var isApprove = btn.classList.contains('btn-approve');
                    Swal.fire({
                        title: isApprove ? 'กำลังอนุมัติ...' : 'กำลังส่งกลับให้แก้ไข...',
                        text: 'กรุณารอสักครู่ ระบบกำลังดำเนินการและแจ้งอีเมล',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: function () {
                            Swal.showLoading();
                        }
                    });
                    form.submit();
                }
            });
        });
    });
</script>
</body>
</html>
