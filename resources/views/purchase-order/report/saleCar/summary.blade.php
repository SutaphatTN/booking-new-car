<table>
  <thead>
    <tr>
      <th>No</th>
      <th>ชื่อ - นามสกุล ลูกค้า</th>
      <th>รุ่นรถหลัก</th>
      <th>รุ่นรถย่อย</th>
      <th>Option</th>
      <th>สี</th>
      @if(auth()->user()->brand == 2)
      <th>สีภายใน</th>
      @endif
      <th>ปี</th>
      <th>วันที่จอง</th>
      <th>ไฟแนนซ์</th>
      <th>สถานะรถ</th>
      <th>วันที่เซ็นสัญญา</th>
      <th>ประมาณการส่งมอบ</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($sale as $s)
    <tr>
      <td>{{ $loop->iteration }}</td>
      <td>{{ $s['customer'] }}</td>
      <td>{{ $s['model'] }}</td>
      <td>{{ $s['subModel'] }}</td>
      <td>{{ $s['option'] }}</td>
      <td>{{ $s['color'] }}</td>
      @if(auth()->user()->brand == 2)
      <td>{{ $s['interior_color'] }}</td>
      @endif
      <td>{{ $s['year'] }}</td> 
      <td>{{ $s['bookingDate'] }}</td>
      <td>{{ $s['name_fi'] }}</td>
      <td>{{ $s['order_status'] }}</td>
      <td>{{ $s['contract_date'] }}</td>
      <td>{{ $s['DeliveryEstimateDate'] }}</td>
    </tr>
    @empty
    <tr>
      <td colspan="{{ auth()->user()->brand == 2 ? 13 : 12 }}" align="center">
        ไม่มีข้อมูล
      </td>
    </tr>
    @endforelse
  </tbody>
</table>