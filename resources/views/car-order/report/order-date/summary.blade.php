@php
  use Illuminate\Support\Carbon;

  $rangeText = 'ช่วงวันที่สั่ง : '
      . Carbon::parse($fromDate)->format('d-m-Y')
      . ' ถึง '
      . Carbon::parse($toDate)->format('d-m-Y');

  // จำนวนคอลัมน์ก่อนคอลัมน์ "จำนวน" ใช้ทำ colspan ของแถวรวม/แถวช่วงวันที่
  $labelSpan = 3 + ($showBranch ? 1 : 0);
@endphp
<table>
  <thead>
    <tr>
      <th>No</th>
      @if ($showBranch)
        <th>สาขา</th>
      @endif
      <th>รุ่นรถหลัก</th>
      <th>รุ่นย่อย</th>
      <th>จำนวน (คัน)</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($summary as $row)
    <tr>
      <td>{{ $loop->iteration }}</td>
      @if ($showBranch)
        <td>{{ $row['branch'] }}</td>
      @endif
      <td>{{ $row['model'] }}</td>
      <td>{{ $row['subModel'] }}</td>
      <td>{{ $row['count'] }}</td>
    </tr>
    @empty
    <tr>
      <td colspan="{{ $labelSpan + 1 }}" align="center">ไม่มีข้อมูล</td>
    </tr>
    @endforelse
    <tr>
      <td colspan="{{ $labelSpan }}" align="center"><b>รวมทั้งหมด</b></td>
      <td><b>{{ $total }}</b></td>
    </tr>
    <tr>
      <td colspan="{{ $labelSpan + 1 }}">{{ $rangeText }}</td>
    </tr>
  </tbody>
</table>
