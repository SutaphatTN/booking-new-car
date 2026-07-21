@php
    $fontReg  = str_replace('\\', '/', public_path('fonts/Sarabun-Regular.ttf'));
    $fontBold = str_replace('\\', '/', public_path('fonts/Sarabun-Bold.ttf'));

    $thMonths = [1=>'มกราคม',2=>'กุมภาพันธ์',3=>'มีนาคม',4=>'เมษายน',5=>'พฤษภาคม',6=>'มิถุนายน',
                 7=>'กรกฎาคม',8=>'สิงหาคม',9=>'กันยายน',10=>'ตุลาคม',11=>'พฤศจิกายน',12=>'ธันวาคม'];

    $fmtDate = fn($d) => $d ? $d->format('d/m/') . ($d->year + 543) : '-';

    $period = optional($place->request)->period;
    $periodLabel = '-';
    if ($period && preg_match('/^(\d{4})-(\d{2})$/', $period, $m)) {
        $periodLabel = ($thMonths[(int) $m[2]] ?? '') . ' ' . ((int) $m[1] + 543);
    }

    $budget    = $place->effectiveBudget();
    $cleared   = $place->clearedTotal();
    $remaining = $place->remainingBudget();
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @font-face { font-family: 'Sarabun'; font-weight: normal; src: url('{{ $fontReg }}') format('truetype'); }
        @font-face { font-family: 'Sarabun'; font-weight: bold; src: url('{{ $fontBold }}') format('truetype'); }
        * { font-family: 'Sarabun', sans-serif; }
        body { font-size: 12px; color: #000; line-height: 1.5; }
        h2 { text-align: center; margin: 0 0 4px; font-size: 17px; }
        .sub { text-align: center; margin: 0 0 12px; font-size: 12px; color: #333; }
        table { width: 100%; border-collapse: collapse; }
        .info td { border: none; padding: 2px 4px; vertical-align: top; }
        .info .lbl { color: #555; width: 90px; }
        .data th, .data td { border: 1px solid #000; padding: 4px 6px; }
        .data th { background: #6af59d; text-align: center; }
        .num { text-align: right; }
        .center { text-align: center; }
        .period-row td { background: #eef7f0; font-weight: bold; }
        tfoot td { font-weight: bold; background: #eafff2; }
        .paid { color: #0a7a3d; }
        .unpaid { color: #b45309; }
        .settled { margin: 10px 0; padding: 9px 12px; border: 1px solid #999; background: #f2f2f2; font-weight: bold; line-height: 1.7; }
        .sign { margin-top: 36px; width: 100%; }
        .sign td { border: none; text-align: center; padding-top: 28px; }
        .summary td { border: 1px solid #000; padding: 5px 8px; }
        /* ตารางแจกแจงงบ — เต็มความกว้างให้ขอบตรงกับตารางสรุปงบ/ตารางเคลียร์ด้านล่าง */
        .budget th:first-child { text-align: left; }
    </style>
</head>
<body>
    <h2>ใบสรุปการเคลียร์ค่าใช้จ่าย</h2>
    <div class="sub">กิจกรรมการตลาด — ประจำเดือน {{ $periodLabel }}</div>

    {{-- ── ข้อมูลสถานที่ ── --}}
    <table class="info">
        <tr>
            <td class="lbl">สถานที่</td><td><strong>{{ $place->location }}</strong></td>
            <td class="lbl">LAS Number</td><td>{{ $place->las_number ?? '-' }}</td>
        </tr>
        <tr>
            <td class="lbl">แหล่งที่มา</td><td>{{ $place->source->name ?? '-' }}</td>
            <td class="lbl">ช่วงวันที่</td>
            <td>{{ $fmtDate($place->start_date) }} - {{ $fmtDate($place->end_date) }}</td>
        </tr>
        <tr>
            <td class="lbl">เป้า PP</td><td>{{ $place->target !== null ? number_format($place->target, 0) : '-' }}</td>
            <td class="lbl"></td><td></td>
        </tr>
    </table>

    {{-- ── ประมาณการค่าใช้จ่ายที่ตั้งไว้ (แจกแจงตามประเภท) — ตั้งคู่กับตารางเคลียร์จริงด้านล่าง ── --}}
    @php $budgetLines = $place->budgetLines(); @endphp
    <table class="data budget" style="margin-top:12px;">
        <thead>
            <tr>
                <th>ประมาณการค่าใช้จ่ายที่ตั้งไว้ (แจกแจงตามประเภท)</th>
                <th style="width:150px;">จำนวนเงิน</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($budgetLines as $line)
                <tr>
                    <td>{{ $line['type'] }}</td>
                    <td class="num">{{ number_format($line['amount'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="2" class="center">ไม่ได้ตั้งงบไว้</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td class="num">รวมประมาณการ</td>
                <td class="num">{{ number_format($budgetLines->sum('amount'), 2) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- ── สรุปงบ ── --}}
    <table class="summary" style="margin-top:10px;">
        <tr>
            <td class="center" style="width:33%;">งบประมาณ<br><strong>{{ $budget !== null ? number_format($budget, 2) : '-' }} บาท</strong></td>
            <td class="center" style="width:33%;">เคลียร์ไปแล้ว<br><strong>{{ number_format($cleared, 2) }} บาท</strong></td>
            <td class="center">คงเหลือ<br><strong>{{ $remaining !== null ? number_format($remaining, 2) : '-' }} บาท</strong></td>
        </tr>
    </table>

    @if ($place->isSettled())
        <div class="settled">
            ● ปิดยอดแล้ว
            @if ($place->settled_at) — เมื่อ {{ $fmtDate($place->settled_at) }} {{ $place->settled_at->format('H:i') }} @endif
            @if ($place->settledBy) โดย {{ $place->settledBy->full_name ?: $place->settledBy->name }} @endif
        </div>
    @endif

    {{-- ── รายการเคลียร์แต่ละงวด ── --}}
    <table class="data" style="margin-top:10px;">
        <thead>
            <tr>
                <th style="width:45px;">งวดที่</th>
                <th style="width:80px;">วันที่เคลียร์</th>
                <th>ประเภทค่าใช้จ่าย</th>
                <th style="width:95px;">จำนวนเงิน</th>
                <th style="width:80px;">วันที่จ่าย</th>
                <th style="width:130px;">สถานะ / ผู้อนุมัติจ่าย</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($place->clears as $idx => $c)
                @php $rows = $c->items->count() ? $c->items : collect([null]); @endphp
                @foreach ($rows as $j => $it)
                    <tr>
                        @if ($j === 0)
                            <td class="center" rowspan="{{ $rows->count() }}">{{ $idx + 1 }}</td>
                            <td class="center" rowspan="{{ $rows->count() }}">{{ $fmtDate($c->clear_date) }}</td>
                        @endif
                        <td>{{ $it->type ?? '-' }}</td>
                        <td class="num">{{ $it && $it->amount !== null ? number_format($it->amount, 2) : '-' }}</td>
                        @if ($j === 0)
                            <td class="center" rowspan="{{ $rows->count() }}">
                                {{ $c->pay_approved ? $fmtDate($c->pay_date) : '-' }}
                            </td>
                            <td class="center" rowspan="{{ $rows->count() }}">
                                @if ($c->pay_approved)
                                    <span class="paid">จ่ายแล้ว</span>
                                    @if ($c->payApprover)<br><span style="font-size:10px;">{{ $c->payApprover->full_name ?: $c->payApprover->name }}</span>@endif
                                @else
                                    <span class="unpaid">ยังไม่จ่าย</span>
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            @empty
                <tr><td colspan="6" class="center">ยังไม่มีข้อมูลการเคลียร์</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="center">รวมทั้งสิ้น</td>
                <td class="num">{{ number_format($cleared, 2) }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <table class="sign">
        <tr>
            <td style="width:50%;">
                ............................................<br>
                ผู้จัดทำเอกสาร
            </td>
            <td style="width:50%;">
                ............................................<br>
                ผู้อนุมัติจ่าย
            </td>
        </tr>
    </table>
</body>
</html>
