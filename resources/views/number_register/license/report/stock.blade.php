<table>
  <thead>
    <tr>
      <th>No</th>
      <th>ชื่อ - นามสกุล ลูกค้า</th>
      <th>เบอร์ลูกค้า</th>
      <th>ฝ่ายขาย</th>
      <th>ป้ายแดง</th>
      <th>ใช้ข้ามแบรนด์</th>
      <th>วันที่ส่งมอบ</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($stockLic as $s)
    <tr>
      <td>{{ $loop->iteration }}</td>
      <td>{{ $s['customer'] }}</td>
      <td>{{ $s['phone'] }}</td>
      <td>{{ $s['sale_lic'] }}</td>
      <td>{{ $s['red_license'] }}</td>
      <td>{{ $s['bound_brand'] }}</td>
      <td>{{ $s['delivery_date'] }}</td>
    </tr>
    @empty
    <tr>
      <td colspan="7" align="center">
        ไม่มีข้อมูล
      </td>
    </tr>
    @endforelse
  </tbody>
</table>