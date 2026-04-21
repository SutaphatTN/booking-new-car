<table>
  <thead>
    <tr>
      <th>No.</th>
      <th>ชื่อ - นามสกุล</th>
      <th>รุ่นหลัก</th>
      <th>รุ่นย่อย</th>
      <th>สี</th>
      <th>ผู้ขาย</th>
      <th>แหล่งที่มา</th>
      <th>วันที่ติดต่อล่าสุด</th>
      <th>สถานะการติดต่อ</th>
      <th>การตัดสินใจ</th>
      <th>หมายเหตุ</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($rows as $r)
      <tr>
        <td>{{ $r['no'] }}</td>
        <td>{{ $r['full_name'] }}</td>
        <td>{{ $r['model'] }}</td>
        <td>{{ $r['sub_model'] }}</td>
        <td>{{ $r['color'] }}</td>
        <td>{{ $r['sale'] }}</td>
        <td>{{ $r['source'] }}</td>
        <td>{{ $r['contact_date'] }}</td>
        <td>{{ $r['contact_status'] }}</td>
        <td>{{ $r['decision'] }}</td>
        <td>{{ $r['comment'] }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="11" align="center">ไม่มีข้อมูล</td>
      </tr>
    @endforelse
  </tbody>
</table>
