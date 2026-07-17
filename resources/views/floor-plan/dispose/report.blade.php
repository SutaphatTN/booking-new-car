@php
  // รายงานถูก brand-scope อยู่แล้ว (UserAccessScope) → อิง brand ของ user ที่ login
  //  - Option: เฉพาะ brand 1
  //  - สีภายใน: เฉพาะ brand 2
  $brand = auth()->user()->brand;

  // นับคอลัมน์ไว้ทำ colspan ของแถว "ไม่มีข้อมูล" (15 ฐาน + option + interior)
  $colCount = 15 + ($brand == 1 ? 1 : 0) + ($brand == 2 ? 1 : 0);
@endphp
<table>
  <thead>
    <tr>
      <th>No</th>
      <th>VIN Number</th>
      <th>เลขเครื่อง</th>
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
    @forelse ($rows as $s)
      @php
        $co = $s->carOrder;
        $modelName = $co->model->Name_TH ?? $s->model->Name_TH ?? '-';
        $subModel  = $co->subModel->name ?? $s->subModel->name ?? '-';
        $year      = $co->year ?? $s->Year ?? '-';
        $color     = $co ? $co->display_color : $s->display_color;
        $option    = $co->option ?? $s->option ?? '-';
        $interior  = $co->interiorColor->name ?? $s->interiorColor->name ?? '-';
        $cus       = $s->customer;
        $cusName   = $cus ? trim(collect([$cus->FirstName, $cus->MiddleName, $cus->LastName])->filter()->implode(' ')) : '';
      @endphp
      <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $co->vin_number ?? '-' }}</td>
        <td>{{ $co->engine_number ?? '-' }}</td>
        <td>{{ $modelName }}</td>
        <td>{{ $subModel }}</td>
        <td>{{ $year ?: '-' }}</td>
        <td>{{ $color ?: '-' }}</td>
        @if ($brand == 1)
          <td>{{ $option ?: '-' }}</td>
        @endif
        @if ($brand == 2)
          <td>{{ $interior ?: '-' }}</td>
        @endif
        <td>{{ $co->car_DNP ?? 0 }}</td>
        <td>{{ $cusName !== '' ? $cusName : '-' }}</td>
        <td>{{ $co->format_fp_close_date ?? '-' }}</td>
        <td>{{ $s->dispose_set ? ($disposeSets[$s->dispose_set] ?? '-') : '-' }}</td>
        <td>{{ $s->format_dispose_received_date ?? '-' }}</td>
        <td>{{ $s->format_dispose_reg_withdraw_date ?? '-' }}</td>
        <td>{{ $s->dispose_note ?: '-' }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="{{ $colCount }}" align="center">ไม่มีข้อมูล</td>
      </tr>
    @endforelse
  </tbody>
</table>
