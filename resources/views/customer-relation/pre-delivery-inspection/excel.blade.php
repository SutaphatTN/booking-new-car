<table>
  <thead>
    <tr>
      <th>No.</th>
      <th>ที่ปรึกษาการขาย</th>
      <th>ชื่อ-นามสกุลลูกค้า</th>
      <th>รุ่นรถ</th>
      <th>วันที่ส่งมอบ</th>
      <th>วันที่ตรวจรถ</th>
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
        <td>{{ $r['inspection_date'] }}</td>
        <td>{{ $r['status'] }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="7" align="center">ไม่มีข้อมูล</td>
      </tr>
    @endforelse
    <tr>
      <td colspan="6" align="right"></td>
      <td align="center"><strong>ผลงาน</strong></td>
    </tr>
    <tr>
      <td colspan="6" align="right"><strong>รวมส่งมอบทั้งหมด</strong></td>
      <td align="center"><strong>{{ $total }} คัน (100%)</strong></td>
    </tr>
    <tr>
      <td colspan="6" align="right">ตรวจรถทั้งหมด</td>
      <td align="center">{{ $total_inspected }} คัน ({{ $total > 0 ? round($total_inspected / $total * 100) : 0 }}%)</td>
    </tr>
    <tr>
      <td colspan="6" align="right">เรียบร้อย [จากรถที่ตรวจ]</td>
      <td align="center">{{ $total_ok }} คัน ({{ $total_inspected > 0 ? round($total_ok / $total_inspected * 100) : 0 }}%)</td>
    </tr>
    <tr>
      <td colspan="6" align="right">ไม่เรียบร้อย [จากรถที่ตรวจ]</td>
      <td align="center">{{ $total_not }} คัน ({{ $total_inspected > 0 ? round($total_not / $total_inspected * 100) : 0 }}%)</td>
    </tr>
  </tbody>
</table>
