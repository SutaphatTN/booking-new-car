<table>
  <thead>
    <tr>
      <th>No</th>
      <th>วันที่สั่งรถในระบบ Motor</th>
      <th>วันที่สั่งรถในระบบ New Car</th>
      <th>วันที่ Stock</th>
      <th>รุ่นรถหลัก</th>
      <th>รุ่นย่อย</th>
      <th>VIN Number</th>
      <th>J Number</th>
      <th>Engine Number</th>
      <th>Option</th>
      <th>สี</th>
      <th>ปี</th>
      <th>ราคาทุน</th>
      <th>ราคาขาย</th>
      <th>ประเภทการจัดซื้อ</th>
      <th>สถานะออเดอร์</th>
      <th>สถานะรถ</th>
      
    </tr>
  </thead>
  <tbody>
    @forelse ($rows as $row)
    <tr>
      <td>{{ $loop->iteration }}</td>
      <td>{{ $row->format_system_date ?? '-' }}</td>
      <td>{{ $row->format_order_date ?? '-' }}</td>
      <td>{{ $row->format_order_stock_date ?? '-' }}</td>
      <td>{{ $row->model->Name_TH ?? '-' }}</td>
      <td>{{ $row->subModel->name ?? '-' }}</td>
      <td>{{ $row->vin_number ?? '-' }}</td>
      <td>{{ $row->j_number ?? '-' }}</td>
      <td>{{ $row->engine_number ?? '-' }}</td>
      <td>{{ $row->option ?? '-' }}</td>
      <td>{{ $row->display_color }}</td>
      <td>{{ $row->year ?? '-' }}</td>
      <td>{{ $row->car_DNP ?? '-' }}</td>
      <td>{{ $row->car_MSRP ?? '-' }}</td>
      <td>{{ $row->purchaseType->name ?? '-' }}</td>
      <td>{{ $row->orderStatus->name ?? '-' }}</td>
      <td>{{ $row->car_status ?? '-' }}</td>
    </tr>
    @empty
    <tr>
      <td colspan="16" align="center">ไม่มีข้อมูล</td>
    </tr>
    @endforelse
  </tbody>
</table>
