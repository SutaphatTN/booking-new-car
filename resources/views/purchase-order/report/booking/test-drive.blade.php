<table>
  <thead>
    <tr>
      <th>รุ่นหลัก</th>
      <th>รุ่นย่อย</th>
      <th>สี</th>
      <th>ปี</th>
      <th>Option</th>
      <th>ราคาขาย</th>
      <th>แคมเปญทดลองขับ</th>
       <th>เลขไมล์</th>
      <th>สถานะรถ</th>
      <th>Vin - Number</th>
      <th>ชื่อผู้จอง</th>
      <th>สถานะสัญญา</th>
      <th>Sale</th>
      <th>วันที่จอง</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($testD as $t)
    <tr>
      <td>{{ $t['model'] }}</td>
      <td>{{ $t['subModel'] }}</td>
      <td>{{ $t['color'] }}</td>
      <td>{{ $t['year'] }}</td>
      <td>{{ $t['option'] }}</td>
      <td>{{ $t['car_MSRP'] }}</td>
      <td>{{ $t['cam_testdrive'] }}</td>
      <td>{{ $t['mileage_test'] }}</td>
      <td>{{ $t['order_status'] }}</td>
      <td>{{ $t['vin_number'] }}</td>
      <td>{{ $t['customer'] }}</td>
      <td>{{ $t['status'] }}</td>
      <td>{{ $t['sale'] }}</td>
      <td>{{ $t['bookingDate'] }}</td>
    </tr>
    @empty
    <tr>
      <td colspan="14" align="center">ไม่มีข้อมูล</td>
    </tr>
    @endforelse
  </tbody>
</table>