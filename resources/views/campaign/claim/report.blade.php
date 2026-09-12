@php
  // คอลัมน์เอกสารแนบต่อท้าย — 1 ไฟล์ = 1 คอลัมน์ เพราะ 1 เซลล์ใส่ hyperlink ได้อันเดียว
  $maxFiles = $maxFiles ?? 0;
@endphp
<table>
  <thead>
    <tr>
      <th>วันที่ตั้งเคลมค้าง</th>
      <th>YEAR</th>
      <th>เดือน</th>
      <th>Cliam Type</th>
      <th>ชื่อลูกค้า</th>
      <th>CHASSIS</th>
      <th>ยอดเคลมค้างรับ</th>
      <th>ยอดรับเคลมในเดือน</th>
      <th>ยอดคงเหลือ</th>
      <th>วันที่รับเงิน</th>
      <th>สรุปผลการตรวจสอบ</th>
      <th>Comment from Internal Audit</th>
      @for ($i = 1; $i <= $maxFiles; $i++)
        <th>เอกสาร {{ $i }}</th>
      @endfor
    </tr>
  </thead>
  <tbody>
    @forelse ($rows as $r)
      <tr>
        <td>{{ $r['delivery_date'] }}</td>
        <td>{{ $r['year'] }}</td>
        <td>{{ $r['month'] }}</td>
        <td>{{ $r['claim_type'] }}</td>
        <td>{{ $r['customer'] }}</td>
        <td>{{ $r['chassis'] }}</td>
        <td>{{ $r['used'] }}</td>
        <td>{{ $r['claim_amount'] }}</td>
        <td>{{ $r['diff'] }}</td>
        <td>{{ $r['received_date'] }}</td>
        <td>{{ $r['status'] }}</td>
        <td>{{ $r['note'] }}</td>
        @for ($i = 0; $i < $maxFiles; $i++)
          @php $f = $r['files'][$i] ?? null; @endphp
          <td>
            @if ($f)
              <a href="{{ $f['url'] }}">{{ $f['name'] }}</a>
            @endif
          </td>
        @endfor
      </tr>
    @empty
      <tr>
        <td colspan="{{ 12 + $maxFiles }}" align="center">ไม่มีข้อมูล</td>
      </tr>
    @endforelse
  </tbody>
</table>
