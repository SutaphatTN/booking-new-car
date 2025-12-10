<table>
  <thead>
    <tr>
      <th>No</th>
      <th>รุ่นรถหลัก</th>
      <th>รุ่นย่อย</th>
      <th>Vin - Number</th>
      <th>J - Number</th>
      <th>Option</th>
      <th>ปี</th>
      <th>สี</th>
      <th>จำนวนคัน</th>
      <th>ผู้จอง</th>
      <th>Sale</th>
      <th>วันจอง</th>
      <th>สถานะ</th>
      <th>สถานะการผูกรถ</th>
      <th>จำนวนวันที่ผูกรถ</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($saleCar as $s)
    <tr>
      <td>{{ $s['No'] }}</td>
      <td>{{ $s['model'] }}</td>
      <td>{{ $s['subModel'] }}</td>
      <td>{{ $s['vin_number'] }}</td>
      <td>{{ $s['j_number'] }}</td>
      <td>{{ $s['option'] }}</td>
      <td>{{ $s['year'] }}</td>
      <td>{{ $s['color'] }}</td>
      <td>{{ $s['count'] }}</td>
      <td>{{ $s['customer'] }}</td>
      <td>{{ $s['sale'] }}</td>
      <td>{{ $s['bookingDate'] }}</td>
      <td>{{ $s['status'] }}</td>
      <td>{{ $s['statusCar'] }}</td>
      <td>{{ $s['daysBind'] }}</td>
    </tr>
    @endforeach
  </tbody>
</table>