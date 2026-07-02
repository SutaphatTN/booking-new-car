@php
    $fontReg  = str_replace('\\', '/', public_path('fonts/Sarabun-Regular.ttf'));
    $fontBold = str_replace('\\', '/', public_path('fonts/Sarabun-Bold.ttf'));

    $thMonths = [1=>'มกราคม',2=>'กุมภาพันธ์',3=>'มีนาคม',4=>'เมษายน',5=>'พฤษภาคม',6=>'มิถุนายน',
                 7=>'กรกฎาคม',8=>'สิงหาคม',9=>'กันยายน',10=>'ตุลาคม',11=>'พฤศจิกายน',12=>'ธันวาคม'];

    $periodLabel = '-';
    if ($req->period && preg_match('/^(\d{4})-(\d{2})$/', $req->period, $m)) {
        $periodLabel = ($thMonths[(int) $m[2]] ?? '') . ' ' . ((int) $m[1] + 543);
    }

    $fmtDate = function ($d) {
        if (!$d) return '';
        return $d->format('d/m/') . ($d->year + 543);
    };

    $total = $req->places->sum('cost');
    $brandName = config("brand.names.{$req->brand}") ?? ('Brand ' . ($req->brand ?? '-'));
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @font-face {
            font-family: 'Sarabun';
            font-weight: normal;
            src: url('{{ $fontReg }}') format('truetype');
        }
        @font-face {
            font-family: 'Sarabun';
            font-weight: bold;
            src: url('{{ $fontBold }}') format('truetype');
        }
        * { font-family: 'Sarabun', sans-serif; }
        body { font-size: 12px; color: #000; }
        h2 { text-align: center; margin: 0 0 12px; font-size: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 4px 6px; }
        th { background: #6af59d; text-align: center; }
        .num { text-align: right; }
        .center { text-align: center; }
        tfoot td { font-weight: bold; }
        .sign { margin-top: 40px; width: 100%; }
        .sign td { border: none; text-align: center; padding-top: 30px; }
    </style>
</head>
<body>
    <h2>ประมาณการค่าใช้จ่ายกิจกรรมการตลาด ประจำเดือน {{ $periodLabel }}</h2>
    <p style="text-align:center; margin:-6px 0 12px; font-size:13px; font-weight:bold;">แบรนด์: {{ $brandName }}</p>

    <table>
        <thead>
            <tr>
                <th style="width:35px;">ลำดับ</th>
                <th style="width:110px;">LAS Number</th>
                <th style="width:70px;">วันเริ่มงาน</th>
                <th style="width:70px;">วันจบงาน</th>
                <th>ระบุประเภทบูธ</th>
                <th>ระบุสถานที่</th>
                <th style="width:130px;">ประเภทค่าใช้จ่าย</th>
                <th style="width:85px;">ประมาณค่าใช้จ่าย</th>
                <th style="width:70px;">เป้า PP</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($req->places as $i => $p)
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td>{{ $p->las_number ?? '' }}</td>
                    <td class="center">{{ $fmtDate($p->start_date) }}</td>
                    <td class="center">{{ $fmtDate($p->end_date) }}</td>
                    <td>{{ $p->source->name ?? '' }}</td>
                    <td>{{ $p->location }}</td>
                    <td>{{ $p->expense_type ?? '' }}</td>
                    <td class="num">{{ $p->cost !== null ? number_format($p->cost, 2) : '' }}</td>
                    <td class="num">{{ $p->target !== null ? number_format($p->target, 0) : '' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="center">รวม</td>
                <td class="num">{{ number_format($total, 2) }}</td>
                <td class="num">{{ number_format($req->places->sum('target'), 0) }}</td>
            </tr>
        </tfoot>
    </table>

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
