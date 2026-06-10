<table>
  <thead>
    <tr>
      <th>No.</th>
      <th>ประทับเวลา</th>
      <th>วันที่ส่งมอบ</th>
      <th>ชื่อ-นามสกุลลูกค้า</th>
      <th>ที่ปรึกษาการขาย</th>
      <th>รุ่น</th>
      <th>สถานที่ส่งมอบ</th>
      <th>จังหวัด</th>
      <th>เบอร์โทรลูกค้า</th>
      <th>เลขถัง</th>
      <th>วันที่โทรติดต่อได้</th>
      <th>ผลการติดต่อ</th>
      <th>สถานะการติดต่อ</th>
      <th>สถานการณ์ตรวจรถก่อนส่งมอบ</th>
      <th>SSI โดยรวม</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($rows as $r)
      <tr>
        <td>{{ $r['no'] }}</td>
        <td>{{ $r['timestamp'] }}</td>
        <td>{{ $r['delivery_date'] }}</td>
        <td>{{ $r['full_name'] }}</td>
        <td>{{ $r['sale_name'] }}</td>
        <td>{{ $r['model'] }}</td>
        <td>{{ $r['delivery_location'] }}</td>
        <td>{{ $r['delivery_province'] }}</td>
        <td>{{ $r['phone'] }}</td>
        <td>{{ $r['vin'] }}</td>
        <td>{{ $r['latest_contact_date'] }}</td>
        <td>{!! nl2br(e($r['contact_history'])) !!}</td>
        <td>{!! nl2br(e($r['contact_status'])) !!}</td>
        <td>{{ $r['pdi_status'] }}</td>
        <td>{{ $r['ssi_score'] }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="15" align="center">ไม่มีข้อมูล</td>
      </tr>
    @endforelse
  </tbody>
</table>
