@php $colspan = 18 + ($showInterior ? 1 : 0) + ($showOption ? 1 : 0); @endphp
<table>
  <thead>
    <tr>
      <th colspan="{{ $colspan }}">รายงานการกรอกข้อมูลการติดตามลูกค้า วันที่ {{ $dateFromFormatted }} ถึง {{ $dateToFormatted }}</th>
    </tr>
    <tr>
      <th>No.</th>
      <th>วันที่กรอก</th>
      <th>ชื่อ - นามสกุล</th>
      <th>เบอร์โทร</th>
      <th>ผู้ขาย</th>
      <th>แหล่งที่มา</th>
      <th>รุ่นรถหลัก</th>
      <th>รุ่นย่อย</th>
      <th>สี</th>
      <th>ปี</th>
      @if ($showInterior)
        <th>สีภายใน</th>
      @endif
      @if ($showOption)
        <th>Option</th>
      @endif
      <th>ผู้กรอก</th>
      <th>ประเภท</th>
      <th>วันที่ทดลองขับ</th>
      <th>หมายเหตุทดลองขับ</th>
      <th>วันที่ติดต่อ</th>
      <th>สถานะการติดต่อ</th>
      <th>การตัดสินใจ</th>
      <th>หมายเหตุ</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($rows as $r)
      <tr>
        <td>{{ $r['no'] }}</td>
        <td>{{ $r['created_at'] }}</td>
        <td>{{ $r['full_name'] }}</td>
        <td>{{ $r['phone'] }}</td>
        <td>{{ $r['sale'] }}</td>
        <td>{{ $r['source'] }}</td>
        <td>{{ $r['model'] }}</td>
        <td>{{ $r['sub_model'] }}</td>
        <td>{{ $r['color'] }}</td>
        <td>{{ $r['year'] }}</td>
        @if ($showInterior)
          <td>{{ $r['interior_color'] }}</td>
        @endif
        @if ($showOption)
          <td>{{ $r['option'] }}</td>
        @endif
        <td>{{ $r['inserted_by'] }}</td>
        <td>{{ $r['entry_type'] }}</td>
        <td>{{ $r['test_date'] }}</td>
        <td>{{ $r['test_note'] }}</td>
        <td>{{ $r['contact_date'] }}</td>
        <td>{{ $r['contact_status'] }}</td>
        <td>{{ $r['decision'] }}</td>
        <td>{{ $r['comment'] }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="{{ $colspan }}" align="center">ไม่มีข้อมูลในช่วงวันที่นี้</td>
      </tr>
    @endforelse
  </tbody>
</table>
