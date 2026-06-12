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
      </tr>
    @empty
      <tr>
        <td colspan="12" align="center">ไม่มีข้อมูล</td>
      </tr>
    @endforelse
  </tbody>
</table>
