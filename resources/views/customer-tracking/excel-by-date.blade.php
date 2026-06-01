<table>
  <thead>
    <tr>
      <th colspan="13">รายงานการกรอกข้อมูลการติดตามลูกค้า วันที่ {{ $dateFromFormatted }} ถึง {{ $dateToFormatted }}</th>
    </tr>
    <tr>
      <th>No.</th>
      <th>วันที่กรอก</th>
      <th>ชื่อ - นามสกุล</th>
      <th>ผู้ขาย</th>
      <th>แหล่งที่มา</th>
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
        <td>{{ $r['sale'] }}</td>
        <td>{{ $r['source'] }}</td>
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
        <td colspan="13" align="center">ไม่มีข้อมูลในช่วงวันที่นี้</td>
      </tr>
    @endforelse
  </tbody>
</table>
