@php
  // brand-scope อยู่แล้ว (UserAccessScope) → Option เฉพาะ brand 1, สีภายใน เฉพาะ brand 2
  $showOption   = $brand == 1;
  $showInterior = $brand == 2;
  $colCount     = 15 + ($showOption ? 1 : 0) + ($showInterior ? 1 : 0);
@endphp
<table>
  <thead>
    <tr>
      <th>No</th>
      <th>VIN Number</th>
      <th>เลขเครื่อง</th>
      <th>J Number</th>
      <th>รุ่นหลัก</th>
      <th>รุ่นย่อย</th>
      <th>ปี</th>
      @if ($showOption)
        <th>Option</th>
      @endif
      <th>สี</th>
      @if ($showInterior)
        <th>สีภายใน</th>
      @endif
      <th>ราคาทุน</th>
      <th>Billing date</th>
      <th>วันที่ปิด FP</th>
      <th>สถานะ</th>
      <th>จำนวนวัน</th>
      <th>รวมดอกเบี้ย</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($rows as $i => $r)
      <tr>
        <td>{{ $i + 1 }}</td>
        <td>{{ $r['vin'] }}</td>
        <td>{{ $r['engine'] }}</td>
        <td>{{ $r['jNumber'] }}</td>
        <td>{{ $r['modelName'] }}</td>
        <td>{{ $r['subModelName'] }}</td>
        <td>{{ $r['year'] }}</td>
        @if ($showOption)
          <td>{{ $r['option'] }}</td>
        @endif
        <td>{{ $r['color'] }}</td>
        @if ($showInterior)
          <td>{{ $r['interior'] }}</td>
        @endif
        <td>{{ $r['cost'] }}</td>
        <td>{{ $r['billingText'] }}</td>
        <td>{{ $r['closeText'] }}</td>
        <td>{{ $r['isClosed'] ? 'ปิดแล้ว' : 'รอปิด FP' }}</td>
        <td>{{ $r['totalDays'] !== null ? $r['totalDays'] : '-' }}</td>
        <td>{{ $r['totalInterest'] !== null ? $r['totalInterest'] : '-' }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="{{ $colCount }}" align="center">ไม่มีข้อมูล</td>
      </tr>
    @endforelse
  </tbody>
</table>
