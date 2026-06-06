<table>
  <thead>
    <tr>
      <th>No.</th>
      <th>ที่ปรึกษาการขาย</th>
      <th>ชื่อ-นามสกุลลูกค้า</th>
      <th>รุ่นรถ</th>
      <th>วันที่ส่งมอบ</th>
      <th>สรุปสถานะการตรวจ</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($rows as $r)
      <tr>
        <td>{{ $r['no'] }}</td>
        <td>{{ $r['sale_name'] }}</td>
        <td>{{ $r['full_name'] }}</td>
        <td>{{ $r['model'] }}</td>
        <td>{{ $r['delivery_date'] }}</td>
        <td>{{ $r['status'] }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="6" align="center">ไม่มีข้อมูล</td>
      </tr>
    @endforelse
    <tr>
      <td colspan="5" align="right"><strong>รวมทั้งหมด</strong></td>
      <td align="center"><strong>{{ $total }} คัน</strong></td>
    </tr>
    <tr>
      <td colspan="5" align="right">เรียบร้อย</td>
      <td align="center">{{ $total_ok }} คัน</td>
    </tr>
    <tr>
      <td colspan="5" align="right">ไม่เรียบร้อย</td>
      <td align="center">{{ $total_not }} คัน</td>
    </tr>
  </tbody>
</table>
