@php $colspan = 12; @endphp
<table>
  <thead>
    <tr>
      <th colspan="{{ $colspan }}">สรุปลูกค้าจากงาน Offline แยกตามสถานที่ ประจำเดือน {{ $monthLabel }}</th>
    </tr>
    <tr>
      <th>No.</th>
      <th>สถานที่</th>
      <th>ประเภทแหล่งที่มา</th>
      <th>ช่วงวันที่จัดงาน</th>
      <th>เลขที่เอกสาร</th>
      <th>ชื่อ Sheet</th>
      <th>จำนวนลูกค้า</th>
      <th>จองแล้ว</th>
      <th>% ปิดการขาย</th>
      <th>กำลังติดตาม</th>
      <th>จบการติดตาม</th>
      <th>ยกเลิกการติดตาม</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($rows as $r)
      <tr>
        <td>{{ $r['no'] }}</td>
        <td>{{ $r['place'] }}</td>
        <td>{{ $r['type'] }}</td>
        <td>{{ $r['range'] }}</td>
        <td>{{ $r['las_number'] }}</td>
        <td>{{ $r['sheet'] }}</td>
        <td>{{ $r['total'] }}</td>
        <td>{{ $r['booked'] }}</td>
        <td>{{ $r['booked_rate'] }}</td>
        <td>{{ $r['ongoing'] }}</td>
        <td>{{ $r['ended'] }}</td>
        <td>{{ $r['cancel'] }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="{{ $colspan }}" align="center">ไม่มีลูกค้าจากงาน Offline ที่ระบุสถานที่ในเดือนนี้</td>
      </tr>
    @endforelse

    @if ($rows->isNotEmpty())
      <tr>
        <td colspan="6" align="center">รวมทั้งหมด</td>
        <td>{{ $sumTotal }}</td>
        <td>{{ $sumBooked }}</td>
        <td>{{ $sumTotal > 0 ? number_format($sumBooked * 100 / $sumTotal, 1) . '%' : '-' }}</td>
        <td>{{ $sumOngoing }}</td>
        <td>{{ $sumEnded }}</td>
        <td>{{ $sumCancel }}</td>
      </tr>
    @endif
  </tbody>
</table>
