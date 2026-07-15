@php
  // role manager เห็นรายงานนี้ได้ แต่ไม่ให้เห็นคอลัมน์ราคาทุน
  $showCost = auth()->user()->role !== 'manager';

  // รายงานถูก brand-scope อยู่แล้ว (UserAccessScope) → อิง brand ของ user ที่ login
  //  - Option: เฉพาะ brand 1
  //  - สีภายใน: เฉพาะ brand 2
  $brand = auth()->user()->brand;

  // นับคอลัมน์ไว้ทำ colspan ของแถว "ไม่มีข้อมูล"
  $colCount = 16
      + ($brand == 1 ? 1 : 0)   // Option
      + ($brand == 2 ? 1 : 0)   // สีภายใน
      + ($showCost ? 1 : 0);    // ราคาทุน
@endphp
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
      @if ($brand == 1)
        <th>Option</th>
      @endif
      <th>สี</th>
      @if ($brand == 2)
        <th>สีภายใน</th>
      @endif
      <th>ปี</th>
      @if ($showCost)
        <th>ราคาทุน</th>
      @endif
      <th>ราคาขาย</th>
      <th>ประเภทการจัดซื้อ</th>
      <th>แหล่งที่มา</th>
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
      @if ($brand == 1)
        <td>{{ $row->option ?? '-' }}</td>
      @endif
      <td>{{ $row->display_color }}</td>
      @if ($brand == 2)
        <td>{{ $row->interiorColor->name ?? '-' }}</td>
      @endif
      <td>{{ $row->year ?? '-' }}</td>
      @if ($showCost)
        <td>{{ $row->car_DNP ?? '-' }}</td>
      @endif
      <td>{{ $row->car_MSRP ?? '-' }}</td>
      <td>{{ $row->purchaseType->name ?? '-' }}</td>
      <td>{{ $row->purchase_source ?? '-' }}</td>
      <td>{{ $row->orderStatus->name ?? '-' }}</td>
      <td>{{ $row->car_status ?? '-' }}</td>
    </tr>
    @empty
    <tr>
      <td colspan="{{ $colCount }}" align="center">ไม่มีข้อมูล</td>
    </tr>
    @endforelse
  </tbody>
</table>
