<table>
  <thead>
    <tr>
      <th>No</th>
      <th>รุ่นรถหลัก</th>
      <th>รุ่นย่อย</th>
      <th>สี</th>
      @if(auth()->user()->brand == 2)
      <th>สีภายใน</th>
      @endif
      <th>ปี</th>
      <th>Option</th>
      <th>ราคาขาย</th>
      <th>ประเภทการซื้อรถ</th>
      <th>สถานะรถ</th>
      <th>วันที่สั่งซื้อในระบบ</th>
      <th>วันที่คาดว่ารถจะมาถึง</th>
      <th>Vin - Number</th>
      <th>J - Number</th>
      <th>วันที่ Stock</th>
      <th>Aging (Stock Date)</th>
      <th>ผู้จอง</th>
      <th>Sale</th>
      <th>วันจอง</th>
      <th>สถานะสัญญา</th>
      <th>ระยะเวลาการจอง</th>
      <th>PO Date</th>
      <th>สถานะรถจัดสรร</th>
      <th>วันที่จัดสรร</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($saleCar as $s)
    <tr>
      <td>{{ $s['No'] }}</td>
      <td>{{ $s['model'] }}</td>
      <td>{{ $s['subModel'] }}</td>
      <td>{{ $s['color'] }}</td>
      @if(auth()->user()->brand == 2)
      <td>{{ $s['interior_color'] }}</td>
      @endif
      <td>{{ $s['year'] }}</td>
      <td>{{ $s['option'] }}</td>
      <td>{{ $s['car_MSRP'] }}</td>
      <td>{{ $s['purchase_type'] }}</td>
      <td>{{ $s['order_status'] }}</td>
      <td>{{ $s['system_date'] }}</td>
      <td>{{ $s['estimated_stock_date'] }}</td>
      <td>{{ $s['vin_number'] }}</td>
      <td>{{ $s['j_number'] }}</td>
      <td>{{ $s['order_stock_date'] }}</td>
      <td>{{ $s['aging_date'] }}</td>
      <td>{{ $s['customer'] }}</td>
      <td>{{ $s['sale'] }}</td>
      <td>{{ $s['bookingDate'] }}</td>
      <td>{{ $s['status'] }}</td>
      <td>{{ $s['daysBind'] }}</td>
      <td>{{ $s['po_date'] }}</td>
      <td>{{ $s['allocation_status'] }}</td>
      <td>{{ $s['allocation_date'] }}</td>
    </tr>
    @empty
    <tr>
      <td colspan="{{ auth()->user()->brand == 2 ? 24 : 23 }}" align="center">
        ไม่มีข้อมูล
      </td>
    </tr>
    @endforelse
  </tbody>
</table>