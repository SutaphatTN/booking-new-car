@php
  // คอลัมน์รวม = รุ่น/ฝ่ายขาย + จำนวนสถานะ + ผลรวมทั้งหมด
  $colSpan = $statuses->count() + 2;

  // สไตล์เซลล์ (กรอบ + จัดกึ่งกลาง) — PhpSpreadsheet อ่าน inline style จาก HTML
  $b   = 'border:1px solid #000000;';
  $ctr = $b . 'text-align:center;';
  $lft = $b . 'text-align:left;';

  // โทนสีตาราง
  $blueHead   = 'background:#4472C4;color:#FFFFFF;font-weight:bold;';
  $blueTotal  = 'background:#BDD7EE;font-weight:bold;';
  $blueRow    = 'background:#DDEBF7;';
  $greenHead  = 'background:#70AD47;color:#FFFFFF;font-weight:bold;';
  $greenTotal = 'background:#A9D08E;font-weight:bold;';
  $greenRow   = 'background:#E2EFDA;';
@endphp

{{-- ===================== ตารางที่ 1 : แยกตามรุ่น ===================== --}}
<table style="border-collapse:collapse;">
  <thead>
    <tr>
      <th colspan="{{ $colSpan }}" style="{{ $ctr }}font-weight:bold;">
        ตารางสรุปยอดขายแยกตามรุ่น แยกตามสถานะสัญญา
      </th>
    </tr>
    <tr>
      <th style="{{ $ctr }}{{ $blueHead }}">รุ่นรถ</th>
      @foreach ($statuses as $status)
        <th style="{{ $ctr }}{{ $blueHead }}">{{ $status->name }}</th>
      @endforeach
      <th style="{{ $ctr }}{{ $blueHead }}">ผลรวมทั้งหมด</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($byModel as $modelName => $row)
      <tr>
        <td style="{{ $lft }}{{ $blueRow }}">{{ $modelName }}</td>
        @foreach ($statuses as $status)
          <td style="{{ $ctr }}{{ $blueRow }}">{{ $row['counts'][$status->id] }}</td>
        @endforeach
        <td style="{{ $ctr }}{{ $blueRow }}">{{ $row['total'] }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="{{ $colSpan }}" style="{{ $ctr }}">ไม่มีข้อมูล</td>
      </tr>
    @endforelse
    <tr>
      <td style="{{ $ctr }}{{ $blueTotal }}">ผลรวมทั้งหมด</td>
      @foreach ($statuses as $status)
        <td style="{{ $ctr }}{{ $blueTotal }}">{{ $colTotals[$status->id] }}</td>
      @endforeach
      <td style="{{ $ctr }}{{ $blueTotal }}">{{ $grandTotal }}</td>
    </tr>
  </tbody>
</table>

{{-- แถวเว้นว่างคั่นระหว่างสองตาราง --}}
<table>
  <tr><td>&nbsp;</td></tr>
</table>

{{-- ===================== ตารางที่ 2 : แยกตามฝ่ายขาย ===================== --}}
<table style="border-collapse:collapse;">
  <thead>
    <tr>
      <th colspan="{{ $colSpan }}" style="{{ $ctr }}font-weight:bold;">
        ตารางยอดขายแยกตามฝ่ายขาย แยกตามสถานะสัญญา
      </th>
    </tr>
    <tr>
      <th style="{{ $ctr }}{{ $greenHead }}">ฝ่ายขาย</th>
      @foreach ($statuses as $status)
        <th style="{{ $ctr }}{{ $greenHead }}">{{ $status->name }}</th>
      @endforeach
      <th style="{{ $ctr }}{{ $greenHead }}">ผลรวมทั้งหมด</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($bySale as $saleName => $row)
      <tr>
        <td style="{{ $lft }}{{ $greenRow }}">{{ $saleName }}</td>
        @foreach ($statuses as $status)
          <td style="{{ $ctr }}{{ $greenRow }}">{{ $row['counts'][$status->id] }}</td>
        @endforeach
        <td style="{{ $ctr }}{{ $greenRow }}">{{ $row['total'] }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="{{ $colSpan }}" style="{{ $ctr }}">ไม่มีข้อมูล</td>
      </tr>
    @endforelse
    <tr>
      <td style="{{ $ctr }}{{ $greenTotal }}">ผลรวมทั้งหมด</td>
      @foreach ($statuses as $status)
        <td style="{{ $ctr }}{{ $greenTotal }}">{{ $colTotals[$status->id] }}</td>
      @endforeach
      <td style="{{ $ctr }}{{ $greenTotal }}">{{ $grandTotal }}</td>
    </tr>
  </tbody>
</table>

{{--
|--------------------------------------------------------------------------
| รายงานแบบเดิม : สรุปซ้อน ฝ่ายขาย → รุ่นรถ (ตารางเดียว) — เก็บไว้เผื่อใช้อีก
|--------------------------------------------------------------------------
| ใช้คู่กับ view() เวอร์ชันเดิม ($summary / $saleTotals / $grandTotal[] / $colCount)
| ที่ comment ไว้ใน SaleCarEstimatedSummaryExport

<table>
  <thead>
    <tr>
      <th colspan="{{ $colCount }}">รายงานสรุปประมาณการณ์ที่จะตัดเดือนนี้</th>
    </tr>
    <tr>
      <th>ฝ่ายขาย / รุ่นรถ</th>
      @foreach ($statuses as $status)
        <th>{{ $status->name }}</th>
      @endforeach
      <th>ผลรวมทั้งหมด</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($summary as $saleName => $models)
      <!-- sale person row -->
      <tr>
        <td>{{ $saleName }}</td>
        @foreach ($statuses as $status)
          <td>{{ $saleTotals[$saleName][$status->id] ?? '' }}</td>
        @endforeach
        <td>{{ array_sum($saleTotals[$saleName] ?? []) }}</td>
      </tr>
      <!-- model sub-rows -->
      @foreach ($models as $modelName => $statusCounts)
        <tr>
          <td>    {{ $modelName }}</td>
          @foreach ($statuses as $status)
            <td>{{ $statusCounts[$status->id] ?? '' }}</td>
          @endforeach
          <td>{{ array_sum($statusCounts) }}</td>
        </tr>
      @endforeach
    @empty
      <tr>
        <td colspan="{{ $colCount }}" align="center">ไม่มีข้อมูล</td>
      </tr>
    @endforelse
    @if (!empty($summary))
      <!-- grand total row -->
      <tr>
        <td>ผลรวมทั้งหมด</td>
        @foreach ($statuses as $status)
          <td>{{ $grandTotal[$status->id] ?? '' }}</td>
        @endforeach
        <td>{{ array_sum($grandTotal) }}</td>
      </tr>
    @endif
  </tbody>
</table>
--}}
