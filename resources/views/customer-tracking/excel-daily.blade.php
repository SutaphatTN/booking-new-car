<table>
  <thead>
    <tr>
      <th colspan="9">รายงานประจำวัน วันที่ {{ $dateFormatted }} ผู้บันทึก: {{ $userName }}</th>
    </tr>
    <tr>
      <th>No.</th>
      <th>ชื่อ - นามสกุล</th>
      <th>ข้อมูลรุ่นรถ</th>
      <th>วันที่ทดลองขับ</th>
      <th>หมายเหตุทดลองขับ</th>
      <th>วันที่ติดต่อ</th>
      <th>สถานะการตัดสินใจ</th>
      <th>สถานะการติดต่อ</th>
      <th>หมายเหตุ</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($rows as $r)
      <tr>
        <td>{{ $r['no'] }}</td>
        <td>{{ $r['full_name'] }}</td>
        <td>{{ $r['car_info'] }}</td>
        <td>{{ $r['test_date'] }}</td>
        <td>{{ $r['test_note'] }}</td>
        <td>{{ $r['contact_date'] }}</td>
        <td>{{ $r['decision'] }}</td>
        <td>{{ $r['contact_status'] }}</td>
        <td>{{ $r['comment'] }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="9" align="center">ไม่มีข้อมูลในวันที่นี้</td>
      </tr>
    @endforelse
  </tbody>
</table>
