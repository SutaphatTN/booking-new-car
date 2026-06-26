@php
    $fontReg  = str_replace('\\', '/', public_path('fonts/Sarabun-Regular.ttf'));
    $fontBold = str_replace('\\', '/', public_path('fonts/Sarabun-Bold.ttf'));

    $thMonths = [1=>'มกราคม',2=>'กุมภาพันธ์',3=>'มีนาคม',4=>'เมษายน',5=>'พฤษภาคม',6=>'มิถุนายน',
                 7=>'กรกฎาคม',8=>'สิงหาคม',9=>'กันยายน',10=>'ตุลาคม',11=>'พฤศจิกายน',12=>'ธันวาคม'];

    $periodLabel = '-';
    if ($req->period && preg_match('/^(\d{4})-(\d{2})$/', $req->period, $m)) {
        $periodLabel = ($thMonths[(int) $m[2]] ?? '') . ' ' . ((int) $m[1] + 543);
    }

    $lines = $req->topupPlaces;
    $sumExtra = $lines->sum(fn($p) => (float) ($p->pending_extra ?? 0));
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @font-face { font-family: 'Sarabun'; font-weight: normal; src: url('{{ $fontReg }}') format('truetype'); }
        @font-face { font-family: 'Sarabun'; font-weight: bold; src: url('{{ $fontBold }}') format('truetype'); }
        * { font-family: 'Sarabun', sans-serif; }
        body { font-size: 13px; color: #000; }
        h2 { text-align: center; margin: 0 0 4px; font-size: 17px; }
        .sub { text-align: center; margin: 0 0 16px; font-size: 13px; color: #444; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 6px 8px; }
        th { background: #fde68a; text-align: center; }
        .num { text-align: right; }
        .center { text-align: center; }
        tfoot td { font-weight: bold; background: #fef3c7; }
        .reason { margin-top: 14px; border: 1px solid #000; padding: 8px 10px; }
        .reason .k { font-weight: bold; }
        .sign { margin-top: 46px; width: 100%; }
        .sign td { border: none; text-align: center; padding-top: 30px; }
    </style>
</head>
<body>
    <h2>ขออนุมัติเพิ่มงบประมาณกิจกรรมการตลาด</h2>
    <div class="sub">ประจำเดือน {{ $periodLabel }}</div>

    <table>
        <thead>
            <tr>
                <th style="width:35px;">ลำดับ</th>
                <th>สถานที่</th>
                <th style="width:120px;">ประเภทบูธ</th>
                <th style="width:110px;" class="num">งบเดิม</th>
                <th style="width:110px;" class="num">ขอเพิ่ม</th>
                <th style="width:110px;" class="num">งบรวมใหม่</th>
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
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="center">รวมที่ขอเพิ่ม</td>
                <td class="num">{{ number_format($sumExtra, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    @foreach ($lines as $p)
        @if ($p->extra_reason)
            <div class="reason"><span class="k">เหตุผล ({{ $p->location }}):</span> {{ $p->extra_reason }}</div>
        @endif
    @endforeach

    <table class="sign">
        <tr>
            <td>
                ............................................<br>
                ({{ $req->requester->full_name ?: ($req->requester->name ?? '') }})<br>
                ผู้ขออนุมัติ
            </td>
            <td>
                ............................................<br>
                ({{ $req->approver->full_name ?: ($req->approver->name ?? '') }})<br>
                ผู้อนุมัติ
            </td>
        </tr>
    </table>
</body>
</html>
