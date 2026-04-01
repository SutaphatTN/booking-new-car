<table>
  <thead>
    <tr>
      <th>No</th>
      <th>รุ่นรถหลัก</th>
      <th>รุ่นย่อย</th>
      <th>สี</th>
      @if (auth()->user()->brand == 2)
        <th>สีภายใน</th>
      @endif
      <th>ปี</th>
      @if (!in_array(auth()->user()->brand, [2,3]))
        <th>Option</th>
      @endif
      <th>ราคาขาย</th>
      <th>ประเภทการซื้อรถ</th>
      <th>สถานะรถ</th>
      <th>วันที่สั่งซื้อในระบบ</th>
      <th>วันที่คาดว่ารถจะมาถึง</th>
      <th>วันที่ออกใบกำกับ</th>
      <th>วันที่ Stock</th>
      <th>Vin - Number</th>
      <th>J - Number</th>
      <th>Aging (Stock Date)</th>
      <th>ผู้จอง</th>
      <th>Sale</th>
      <th>วันจอง</th>
      <th>สถานะสัญญา</th>
      <th>ระยะเวลาการจอง</th>
      <th>PO Date</th>
      <th>สถานะรถจัดสรร</th>
      <th>วันที่จัดสรร</th>
      <th>ประดับยนต์ของรถ</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($saleCar as $s)
      <tr>
        <td>{{ $s['No'] }}</td>
        <td>{{ $s['model'] }}</td>
        <td>{{ $s['subModel'] }}</td>
        <td>{{ $s['color'] }}</td>
        @if (auth()->user()->brand == 2)
          <td>{{ $s['interior_color'] }}</td>
        @endif
        <td>{{ $s['year'] }}</td>
        @if (!in_array(auth()->user()->brand, [2,3]))
          <td>{{ $s['option'] }}</td>
        @endif
        <td>{{ $s['car_MSRP'] }}</td>
        <td>{{ $s['purchase_type'] }}</td>
        <td>{{ $s['order_status'] }}</td>
        <td>{{ $s['system_date'] }}</td>
        <td>{{ $s['estimated_stock_date'] }}</td>
        <td>{{ $s['order_invoice_date'] }}</td>
        <td>{{ $s['order_stock_date'] }}</td>
        <td>{{ $s['vin_number'] }}</td>
        <td>{{ $s['j_number'] }}</td>
        <td>{{ $s['aging_date'] }}</td>
        <td>{{ $s['customer'] }}</td>
        <td>{{ $s['sale'] }}</td>
        <td>{{ $s['bookingDate'] }}</td>
        <td>{{ $s['status'] }}</td>
        <td>{{ $s['daysBind'] }}</td>
        <td>{{ $s['po_date'] }}</td>
        <td>{{ $s['allocation_status'] }}</td>
        <td>{{ $s['allocation_date'] }}</td>
        <td>{{ $s['note_accessory'] }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="25" align="center">
          ไม่มีข้อมูล
        </td>
      </tr>
    @endforelse
  </tbody>
</table>
