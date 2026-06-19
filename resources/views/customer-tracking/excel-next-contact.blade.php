<table>
  <thead>
    <tr>
      <th colspan="10">รายงานวันที่ต้องติดต่อครั้งถัดไป (ผู้จัดการ) ประจำเดือน {{ $dateFormatted }}</th>
    </tr>
    <tr>
      <th>No.</th>
      {{-- <th>วันที่เพิ่ม</th> --}}
      <th>ชื่อ - นามสกุล</th>
      <th>เบอร์โทร</th>
      <th>ผู้ขาย</th>
      <th>แหล่งที่มา</th>
      <th>วันที่ต้องติดต่อครั้งถัดไป</th>
      <th>สถานะการตัดสินใจ</th>
      <th>สถานะการติดต่อ</th>
      <th>หมายเหตุ</th>
      <th>link</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($rows as $r)
      <tr>
        <td>{{ $r['no'] }}</td>
        {{-- <td>{{ $r['created_at'] }}</td> --}}
        <td>{{ $r['full_name'] }}</td>
        <td>{{ $r['phone'] }}</td>
        <td>{{ $r['sale'] }}</td>
        <td>{{ $r['source'] }}</td>
        <td>{{ $r['next_contact_date'] }}</td>
        <td>{{ $r['decision'] }}</td>
        <td>{{ $r['contact_status'] }}</td>
        <td>{{ $r['comment'] }}</td>
        <td>@if (!empty($r['link']))<a href="{{ $r['link'] }}">เปิดข้อมูลลูกค้า</a>@else - @endif</td>
      </tr>
    @empty
      <tr>
        <td colspan="10" align="center">ไม่มีข้อมูล</td>
      </tr>
    @endforelse
  </tbody>
</table>
