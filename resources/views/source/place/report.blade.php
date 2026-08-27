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

    // จำนวนวันของงาน — นับรวมทั้งวันเริ่มและวันจบ (1-6 ส.ค. = 6 วัน)
    // ไม่ระบุวันจบ = 1 วัน / ไม่ระบุวันเริ่มเลย = เว้นว่าง
    $dayCount = function ($p) {
        if (!$p->start_date) {
            return '';
        }
        return $p->end_date ? $p->start_date->diffInDays($p->end_date) + 1 : 1;
    };

    // สรุปยอดของชุดสถานที่ชุดหนึ่ง (ใช้ทั้งท้ายเซตรายสาขา และยอดรวมทั้งรายงาน)
    $totals = fn($list) => [
        'cost'    => $list->sum(fn($p) => (float) ($p->cost ?? 0) + (float) ($p->extra_cost ?? 0)),
        'extra'   => $list->sum(fn($p) => (float) ($p->extra_cost ?? 0)),
        'actual'  => $list->sum(fn($p) => $p->clears->sum('total')),
        'target'  => $list->sum('target'),
        'pp'      => $list->sum('pp_actual'),
        'booking' => $list->sum('booking_actual'),
        'days'    => $list->sum(fn($p) => $p->start_date ? ($p->end_date ? $p->start_date->diffInDays($p->end_date) + 1 : 1) : 0),
    ];
    $grand = $totals($places);
    // มีสาขาเดียว = ไม่ต้องขึ้นหัวข้อสาขา/ยอดรวมท้ายรายงาน (brand ที่ไม่ได้แยกสาขาจะเหมือนเดิมเป๊ะ)
    $multi = $groups->count() > 1;
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
        /* word-wrap = ตัดคำไทยยาว ๆ ที่ไม่มีช่องว่าง (ชื่อ อบต./เทศบาล) ให้ขึ้นบรรทัดใหม่ได้
           ห้ามใส่ table-layout:fixed — dompdf จะทิ้งความกว้างที่กำหนดแล้วหารทุกคอลัมน์เท่ากัน */
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 3px 4px; word-wrap: break-word; overflow-wrap: break-word; }
        th { background: #6af59d; text-align: center; }
        .num { text-align: right; }
        .center { text-align: center; }
        /* ห้ามตัดบรรทัดกลางเลขที่เอกสาร/วันที่ — dompdf ใช้ auto layout เสมอ (ไม่รองรับ table-layout:fixed)
           ถ้าไม่สั่ง nowrap มันจะหั่น RS-26-07-00408 เป็นสองบรรทัดเพื่อบีบคอลัมน์ */
        .nowrap { white-space: nowrap; }
        tfoot td { font-weight: bold; background: #eafff2; }
    </style>
</head>
<body>
    <h2>รายงานสรุปกิจกรรมการตลาด ประจำเดือน {{ $periodLabel }}</h2>


@foreach ($groups as $g)
    @if ($multi)
        {{-- สาขาถัดไปขึ้นหน้าใหม่เสมอ — ไม่งั้นหัวข้อสาขาจะค้างท้ายหน้าแล้วตารางไปโผล่หน้าถัดไป --}}
        <h3 style="margin:14px 0 6px; font-size:13px;{{ $loop->first ? '' : ' page-break-before: always;' }}">
            สาขา{{ $g['name'] }}
        </h3>
    @endif
    @php $sub = $totals($g['places']); @endphp
    <table>
        <thead>
            <tr>
                <th style="width:20px;">ลำดับ</th>
                <th style="width:92px;">LAS Number</th>
                <th style="width:56px;">วันเริ่มงาน</th>
                <th style="width:62px;">วันจบงาน</th>
                <th style="width:70px;">ระบุประเภทบูธ</th>
                <th style="width:270px;">ระบุสถานที่</th>
                <th class="nowrap" style="width:100px;">ประเภทค่าใช้จ่าย</th>
                <th style="width:72px;">ประมาณค่าใช้จ่าย</th>
                <th style="width:70px;">ค่าใช้จ่ายจริง</th>
                <th style="width:28px;">เป้า<br>PP</th>
                <th style="width:32px;">ยอด PP<br>จริง</th>
                <th style="width:34px;">ยอดจอง<br>(คัน)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($g['places'] as $i => $p)
                @include('source.place._report-row', ['p' => $p, 'i' => $i])
            @empty
                <tr><td colspan="12" class="center">ไม่มีข้อมูลของเดือนนี้</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                {{-- คลุม ลำดับ..วันเริ่มงาน แล้วโชว์ยอดรวมจำนวนวันในช่องวันจบงาน --}}
                <td colspan="3" class="center">รวม{{ $multi ? 'สาขา' . $g['name'] : '' }}</td>
                <td class="center">{{ number_format($sub['days'], 0) }} วัน</td>
                <td colspan="3"></td>
                <td class="num">
                    {{ number_format($sub['cost'], 2) }}
                    @if ($sub['extra'] > 0)
                        <br><span style="font-size:9px; color:#0a7a3d;">(รวมงบเพิ่ม +{{ number_format($sub['extra'], 2) }})</span>
                    @endif
                </td>
                <td class="num">{{ number_format($sub['actual'], 2) }}</td>
                <td class="num">{{ number_format($sub['target'], 0) }}</td>
                <td class="num">{{ number_format($sub['pp'], 0) }}</td>
                <td class="num">{{ number_format($sub['booking'], 0) }}</td>
            </tr>
        </tfoot>
    </table>
@endforeach

@if ($groups->isEmpty())
    <table>
        <thead>
            <tr>
                <th style="width:20px;">ลำดับ</th>
                <th style="width:92px;">LAS Number</th>
                <th style="width:56px;">วันเริ่มงาน</th>
                <th style="width:62px;">วันจบงาน</th>
                <th style="width:70px;">ระบุประเภทบูธ</th>
                <th style="width:270px;">ระบุสถานที่</th>
                <th class="nowrap" style="width:100px;">ประเภทค่าใช้จ่าย</th>
                <th style="width:72px;">ประมาณค่าใช้จ่าย</th>
                <th style="width:70px;">ค่าใช้จ่ายจริง</th>
                <th style="width:28px;">เป้า<br>PP</th>
                <th style="width:32px;">ยอด PP<br>จริง</th>
                <th style="width:34px;">ยอดจอง<br>(คัน)</th>
            </tr>
        </thead>
        <tbody>
            <tr><td colspan="12" class="center">ไม่มีข้อมูลของเดือนนี้</td></tr>
        </tbody>
    </table>
@endif

@if ($multi)
    {{-- ยอดรวมทุกสาขา ปิดท้ายรายงาน --}}
    <h3 style="margin:16px 0 6px; font-size:13px;">รวมทุกสาขา</h3>
    <table>
        <thead>
            <tr>
                <th>รายการ</th>
                <th style="width:78px;">ประมาณค่าใช้จ่าย</th>
                <th style="width:78px;">ค่าใช้จ่ายจริง</th>
                <th style="width:55px;">เป้า PP</th>
                <th style="width:55px;">ยอด PP จริง</th>
                <th style="width:50px;">ยอดจอง (คัน)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($groups as $g)
                @php $sub = $totals($g['places']); @endphp
                <tr>
                    <td>สาขา{{ $g['name'] }} ({{ $g['places']->count() }} รายการ)</td>
                    <td class="num">{{ number_format($sub['cost'], 2) }}</td>
                    <td class="num">{{ number_format($sub['actual'], 2) }}</td>
                    <td class="num">{{ number_format($sub['target'], 0) }}</td>
                    <td class="num">{{ number_format($sub['pp'], 0) }}</td>
                    <td class="num">{{ number_format($sub['booking'], 0) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td class="center">รวมทั้งหมด</td>
                <td class="num">
                    {{ number_format($grand['cost'], 2) }}
                    @if ($grand['extra'] > 0)
                        <br><span style="font-size:9px; color:#0a7a3d;">(รวมงบเพิ่ม +{{ number_format($grand['extra'], 2) }})</span>
                    @endif
                </td>
                <td class="num">{{ number_format($grand['actual'], 2) }}</td>
                <td class="num">{{ number_format($grand['target'], 0) }}</td>
                <td class="num">{{ number_format($grand['pp'], 0) }}</td>
                <td class="num">{{ number_format($grand['booking'], 0) }}</td>
            </tr>
        </tfoot>
    </table>
@endif
</body>
</html>
