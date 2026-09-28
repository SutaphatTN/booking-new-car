@php $colspan = 14; @endphp
<table>
  <thead>
    <tr>
      <th colspan="{{ $colspan }}">รายงานทดลองขับ (Test Drive) {{ $brandName }} ประจำเดือน {{ $monthLabel }}</th>
    </tr>
    <tr>
      <th>No.</th>
      <th>วันที่ทดลองขับ</th>
      <th>สาขา</th>
      <th>ชื่อ - นามสกุล</th>
      <th>เบอร์โทร</th>
      <th>ผู้ขาย</th>
      <th>รุ่นรถหลัก</th>
      <th>รุ่นรถย่อย</th>
      <th>แหล่งที่มา</th>
      <th>สถานะ</th>
      <th>หมายเหตุทดลองขับ</th>
      <th>ไฟล์หลักฐาน</th>
      <th>วันที่เพิ่มลูกค้า</th>
      <th>link</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($rows as $r)
      <tr>
        <td>{{ $r['no'] }}</td>
        <td>{{ $r['test_drive'] }}</td>
        <td>{{ $r['branch'] }}</td>
        <td>{{ $r['full_name'] }}</td>
        <td>{{ $r['phone'] }}</td>
        <td>{{ $r['sale'] }}</td>
        <td>{{ $r['model'] }}</td>
        <td>{{ $r['sub_model'] }}</td>
        <td>{{ $r['source'] }}</td>
        <td>{{ $r['status'] }}</td>
        <td>{{ $r['note'] }}</td>
        <td>{{ $r['files'] }}</td>
        <td>{{ $r['created_at'] }}</td>
        <td><a href="{{ $r['link'] }}">เปิดข้อมูลลูกค้า</a></td>
      </tr>
    @empty
      <tr>
        <td colspan="{{ $colspan }}" align="center">ไม่มีข้อมูลทดลองขับในเดือนนี้</td>
      </tr>
    @endforelse
  </tbody>
</table>
