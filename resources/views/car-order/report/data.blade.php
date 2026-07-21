@php
  // role manager เห็นรายงานนี้ได้ แต่ไม่ให้เห็นคอลัมน์ราคาทุน (ยึดตาม convention รายงาน Stock เดิม)
  $showCost = auth()->user()->role !== 'manager';

  // รายงานถูก brand-scope อยู่แล้ว (UserAccessScope) → อิง brand ของ user ที่ login
  //  - Option + ราคาขาย RI/WS: เฉพาะ brand 1
  //  - สีภายใน: เฉพาะ brand 2
  $brand = auth()->user()->brand;

  // นับคอลัมน์ไว้ทำ colspan ของแถว "ไม่มีข้อมูล"
  $colCount = 13
      + ($brand == 1 ? 3 : 0)   // Option + RI + WS
      + ($brand == 2 ? 1 : 0)   // สีภายใน
      + ($showCost ? 1 : 0);    // ราคาทุน
@endphp
<table>
  <thead>
    <tr>
      <th>No</th>
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
      @if ($brand == 1)
        <th>RI</th>
        <th>WS</th>
      @endif
      <th>แหล่งที่มา</th>
      <th>ประเภทการซื้อรถ</th>
      <th>ประเภทการจ่าย</th>
      <th>สถานะออเดอร์</th>
      <th>สถานะรถ</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($rows as $row)
    <tr>
      <td>{{ $loop->iteration }}</td>
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
      @if ($brand == 1)
        <td>{{ $row->RI ?? '-' }}</td>
        <td>{{ $row->WS ?? '-' }}</td>
      @endif
      <td>{{ $row->purchase_source ?? '-' }}</td>
      <td>{{ $row->purchaseType->name ?? '-' }}</td>
      <td>{{ $row->payment_type_label }}</td>
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
