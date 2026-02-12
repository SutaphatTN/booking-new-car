<table>
  <thead>
    <tr>
      <th>รุ่นย่อย</th>
      <th>สี</th>
      <th>ปี</th>
      <th>Option</th>
      <th>ราคาขาย</th>
      <th>สถานะรถ</th>
      <th>Vin - Number</th>
      <th>J - Number</th>
      <th>วันที่ Stock</th>
      <th>Aging (Stock Date)</th>
      <th>ชื่อผู้จอง</th>
      <th>สถานะสัญญา</th>
      <th>Sale</th>
      <th>วันที่จอง</th>
      <th>สถานะสัญญา</th>
      <th>ระยะเวลาการจอง</th>
      <th>สถานะรถจัดสรร</th>
      <th>วันที่จัดสรร</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($rows as $r)
    <tr>
      <td>{{ $r['subModel'] }}</td>
      <td>{{ $r['color'] }}</td>
      <td>{{ $r['year'] }}</td>
      <td>{{ $r['option'] }}</td>
      <td>{{ $r['car_MSRP'] }}</td>
      <td>{{ $r['order_status'] }}</td>
      <td>{{ $r['vin_number'] }}</td>
      <td>{{ $r['j_number'] }}</td>
      <td>{{ $r['order_stock_date'] }}</td>
      <td>{{ $r['aging_date'] }}</td>
      <td>{{ $r['customer'] }}</td>
      <td>{{ $r['con_status'] }}</td>
      <td>{{ $r['sale'] }}</td>
      <td>{{ $r['bookingDate'] }}</td>
      <td>{{ $r['status'] }}</td>
      <td>{{ $r['daysBind'] }}</td>
      <td>{{ $r['allocation_status'] }}</td>
      <td>{{ $r['allocation_date'] }}</td>
    </tr>
    @empty
    <tr>
      <td colspan="12" align="center">ไม่มีข้อมูล</td>
    </tr>
    @endforelse
  </tbody>
</table>