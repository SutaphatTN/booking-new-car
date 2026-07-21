@php
    $fontReg  = str_replace('\\', '/', public_path('fonts/Sarabun-Regular.ttf'));
    $fontBold = str_replace('\\', '/', public_path('fonts/Sarabun-Bold.ttf'));

    $thMonths = [1=>'มกราคม',2=>'กุมภาพันธ์',3=>'มีนาคม',4=>'เมษายน',5=>'พฤษภาคม',6=>'มิถุนายน',
                 7=>'กรกฎาคม',8=>'สิงหาคม',9=>'กันยายน',10=>'ตุลาคม',11=>'พฤศจิกายน',12=>'ธันวาคม'];

    $periodLabel = '-';
    if ($period && preg_match('/^(\d{4})-(\d{2})$/', $period, $m)) {
        $periodLabel = ($thMonths[(int) $m[2]] ?? '') . ' ' . ((int) $m[1] + 543);
    }

    $fmtDate = fn($d) => $d ? $d->format('d/m/') . ($d->year + 543) : '';

    $sumCost   = $places->sum(fn($p) => (float) ($p->cost ?? 0) + (float) ($p->extra_cost ?? 0));
    $sumExtra  = $places->sum(fn($p) => (float) ($p->extra_cost ?? 0));
    $sumActual = $places->sum(fn($p) => $p->clears->sum('total'));
    $sumTarget = $places->sum('target');
    $sumPp     = $places->sum('pp_actual');
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @font-face { font-family: 'Sarabun'; font-weight: normal; src: url('{{ $fontReg }}') format('truetype'); }
        @font-face { font-family: 'Sarabun'; font-weight: bold; src: url('{{ $fontBold }}') format('truetype'); }
        * { font-family: 'Sarabun', sans-serif; }
        body { font-size: 11px; color: #000; }
        h2 { text-align: center; margin: 0 0 12px; font-size: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 4px 5px; }
        th { background: #6af59d; text-align: center; }
        .num { text-align: right; }
        .center { text-align: center; }
        tfoot td { font-weight: bold; background: #eafff2; }
    </style>
</head>
<body>
    <h2>รายงานสรุปกิจกรรมการตลาด ประจำเดือน {{ $periodLabel }}</h2>

    <table>
        <thead>
            <tr>
                <th style="width:30px;">ลำดับ</th>
                <th style="width:95px;">LAS Number</th>
                <th style="width:62px;">วันเริ่มงาน</th>
                <th style="width:62px;">วันจบงาน</th>
                <th>ระบุประเภทบูธ</th>
                <th>ระบุสถานที่</th>
                <th style="width:110px;">ประเภทค่าใช้จ่าย</th>
                <th style="width:78px;">ประมาณค่าใช้จ่าย</th>
                <th style="width:78px;">ค่าใช้จ่ายจริง</th>
                <th style="width:55px;">เป้า PP</th>
                <th style="width:55px;">ยอด PP จริง</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($places as $i => $p)
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td>{{ $p->las_number ?? '' }}</td>
                    <td class="center">{{ $fmtDate($p->start_date) }}</td>
                    <td class="center">{{ $fmtDate($p->end_date) }}</td>
                    <td>{{ $p->source->name ?? '' }}</td>
                    <td>{{ $p->location }}</td>
                    @php
                        $eff       = (float) ($p->cost ?? 0) + (float) ($p->extra_cost ?? 0);
                        $actualSum = $p->clears->sum('total');
                        // ข้อมูลเก่าที่ยังไม่ได้แจกแจง → แสดงบรรทัดเดียวแบบเดิม (ประเภทรวมของเดิมไม่ตรงกับประเภทตอนเคลียร์ แจกแจงแล้วจะอ่านยาก)
                        $hasBreakdown = $p->budgetItems->isNotEmpty();
                        $rows = collect();
                        if ($hasBreakdown) {
                            $rows = $p->expenseComparison();
                            if ($p->extra_cost) {
                                $rows->push(['type' => 'งบเพิ่ม', 'estimate' => (float) $p->extra_cost, 'actual' => null, 'extra' => true]);
                            }
                        }
                        // บรรทัด "รวม" ขึ้นเมื่อมีมากกว่า 1 บรรทัด (บรรทัดเดียวยอดก็คือยอดรวมอยู่แล้ว)
                        $showSum = $rows->count() > 1;
                        $sumLine = 'border-top:1px solid #999; font-weight:bold;';
                    @endphp
                    @if (!$hasBreakdown)
                        <td>{{ $p->expense_type ?? '' }}</td>
                        <td class="num">
                            {{ ($p->cost !== null || $p->extra_cost !== null) ? number_format($eff, 2) : '' }}
                            @if ($p->extra_cost)
                                <br><span style="font-size:9px; color:#0a7a3d;">(งบเพิ่ม +{{ number_format($p->extra_cost, 2) }})</span>
                            @endif
                        </td>
                        <td class="num">{{ $p->clears->count() ? number_format($actualSum, 2) : '-' }}</td>
                    @else
                        <td>
                            @foreach ($rows as $r)
                                <div @if (!empty($r['extra'])) style="color:#0a7a3d;" @endif>{{ $r['type'] }}</div>
                            @endforeach
                            @if ($showSum)
                                <div style="{{ $sumLine }}">รวม</div>
                            @endif
                        </td>
                        <td class="num">
                            @foreach ($rows as $r)
                                <div @if (!empty($r['extra'])) style="color:#0a7a3d;" @endif>
                                    {{ $r['estimate'] === null ? '-' : (!empty($r['extra']) ? '+' : '') . number_format($r['estimate'], 2) }}
                                </div>
                            @endforeach
                            @if ($showSum)
                                <div style="{{ $sumLine }}">{{ number_format($eff, 2) }}</div>
                            @endif
                        </td>
                        <td class="num">
                            @foreach ($rows as $r)
                                <div>{{ $r['actual'] === null ? '-' : number_format($r['actual'], 2) }}</div>
                            @endforeach
                            @if ($showSum)
                                <div style="{{ $sumLine }}">{{ $p->clears->count() ? number_format($actualSum, 2) : '-' }}</div>
                            @endif
                        </td>
                    @endif
                    <td class="num">{{ $p->target !== null ? number_format($p->target, 0) : '' }}</td>
                    <td class="num">{{ number_format($p->pp_actual, 0) }}</td>
                </tr>
            @empty
                <tr><td colspan="11" class="center">ไม่มีข้อมูลของเดือนนี้</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="center">รวม</td>
                <td class="num">
                    {{ number_format($sumCost, 2) }}
                    @if ($sumExtra > 0)
                        <br><span style="font-size:9px; color:#0a7a3d;">(รวมงบเพิ่ม +{{ number_format($sumExtra, 2) }})</span>
                    @endif
                </td>
                <td class="num">{{ number_format($sumActual, 2) }}</td>
                <td class="num">{{ number_format($sumTarget, 0) }}</td>
                <td class="num">{{ number_format($sumPp, 0) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
