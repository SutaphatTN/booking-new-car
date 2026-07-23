@php
  // รายงานถูก brand-scope อยู่แล้ว (UserAccessScope) → อิง brand ของ user ที่ login
  //  - Option: เฉพาะ brand 1
  //  - สีภายใน: เฉพาะ brand 2
  $brand = auth()->user()->brand;

  // นับคอลัมน์ไว้ทำ colspan ของแถว "ไม่มีข้อมูล" (16 ฐาน + option + interior)
  $colCount = 16 + ($brand == 1 ? 1 : 0) + ($brand == 2 ? 1 : 0);
@endphp
<table>
  <thead>
    <tr>
      <th>No</th>
      <th>VIN Number</th>
      <th>เลขเครื่อง</th>
      <th>J Number</th>
      <th>รุ่นรถหลัก</th>
      <th>รุ่นย่อย</th>
      <th>ปี</th>
      <th>สี</th>
      @if ($brand == 1)
        <th>Option</th>
      @endif
      @if ($brand == 2)
        <th>สีภายใน</th>
      @endif
      <th>ราคาทุน</th>
      <th>ชื่อลูกค้า</th>
      <th>วันที่ปิด FP</th>
      <th>ชุดแจ้งจำหน่าย</th>
      <th>วันที่รับ</th>
      <th>วันที่ ทบ.เบิก</th>
      <th>หมายเหตุ</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($rows as $co)
      @php
        // ใบจองที่ยังไม่ถอน (ถ้ามี) — ใช้แค่ชื่อลูกค้า ข้อมูลรถยึดจาก car_order ทั้งหมด
        $sale    = $co->salecars->last();
        $cus     = $sale?->customer;
        $cusName = $cus ? trim(collect([$cus->FirstName, $cus->MiddleName, $cus->LastName])->filter()->implode(' ')) : '';
      @endphp
      <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $co->vin_number ?: '-' }}</td>
        <td>{{ $co->engine_number ?: '-' }}</td>
        <td>{{ $co->j_number ?: '-' }}</td>
        <td>{{ $co->model->Name_TH ?? '-' }}</td>
        <td>{{ $co->subModel->name ?? '-' }}</td>
        <td>{{ $co->year ?: '-' }}</td>
        <td>{{ $co->display_color ?: '-' }}</td>
        @if ($brand == 1)
          <td>{{ $co->option ?: '-' }}</td>
        @endif
        @if ($brand == 2)
          <td>{{ $co->interiorColor->name ?? '-' }}</td>
        @endif
        <td>{{ $co->car_DNP ?? 0 }}</td>
        <td>{{ $cusName !== '' ? $cusName : '-' }}</td>
        <td>{{ $co->format_fp_close_date ?? '-' }}</td>
        <td>{{ $co->dispose_set ? ($disposeSets[$co->dispose_set] ?? '-') : '-' }}</td>
        <td>{{ $co->format_dispose_received_date ?? '-' }}</td>
        <td>{{ $co->format_dispose_reg_withdraw_date ?? '-' }}</td>
        <td>{{ $co->dispose_note ?: '-' }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="{{ $colCount }}" align="center">ไม่มีข้อมูล</td>
      </tr>
    @endforelse
  </tbody>
</table>
