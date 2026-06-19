<table>
  <thead>
    <tr>
      <th colspan="12">รายงานเลยกำหนดติดตามลูกค้า (ผู้จัดการ) ณ วันที่ {{ $dateFormatted }}</th>
    </tr>
    <tr>
      <th>No.</th>
      <th>วันที่เพิ่ม</th>
      <th>จำนวนวัน</th>
      <th>ชื่อ - นามสกุล</th>
      <th>เบอร์โทร</th>
      <th>ผู้ขาย</th>
      <th>แหล่งที่มา</th>
      <th>ผู้กรอก</th>
      {{-- <th>ประเภท</th> --}}
      <th>วันที่ติดต่อ</th>
      <th>สถานะการตัดสินใจ</th>
      <th>หมายเหตุ</th>
      <th>link</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($rows as $r)
      <tr>
        <td>{{ $r['no'] }}</td>
        <td>{{ $r['created_at'] }}</td>
        <td>{{ $r['days'] }}</td>
        <td>{{ $r['full_name'] }}</td>
        <td>{{ $r['phone'] }}</td>
        <td>{{ $r['sale'] }}</td>
        <td>{{ $r['source'] }}</td>
        <td>{{ $r['inserted_by'] }}</td>
        {{-- <td>{{ $r['entry_type'] }}</td> --}}
        <td>{{ $r['contact_date'] }}</td>
        <td>{{ $r['decision'] }}</td>
        <td>{{ $r['comment'] }}</td>
        <td>@if (!empty($r['link']))<a href="{{ $r['link'] }}">เปิดข้อมูลลูกค้า</a>@else - @endif</td>
      </tr>
    @empty
      <tr>
        <td colspan="12" align="center">ไม่มีข้อมูล</td>
      </tr>
    @endforelse
  </tbody>
</table>
