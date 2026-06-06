<table>
  <thead>
    <tr>
      <th colspan="11">รายงานเลยกำหนดติดตามลูกค้า (ผู้จัดการ) ณ วันที่ {{ $dateFormatted }}</th>
    </tr>
    <tr>
      <th>No.</th>
      <th>วันที่เพิ่ม</th>
      <th>จำนวนวัน</th>
      <th>ชื่อ - นามสกุล</th>
      <th>ผู้ขาย</th>
      <th>แหล่งที่มา</th>
      <th>ผู้กรอก</th>
      <th>ประเภท</th>
      <th>วันที่ติดต่อ</th>
      <th>สถานะการตัดสินใจ</th>
      <th>หมายเหตุ</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($rows as $r)
      <tr>
        <td>{{ $r['no'] }}</td>
        <td>{{ $r['created_at'] }}</td>
        <td>{{ $r['days'] }}</td>
        <td>{{ $r['full_name'] }}</td>
        <td>{{ $r['sale'] }}</td>
        <td>{{ $r['source'] }}</td>
        <td>{{ $r['inserted_by'] }}</td>
        <td>{{ $r['entry_type'] }}</td>
        <td>{{ $r['contact_date'] }}</td>
        <td>{{ $r['decision'] }}</td>
        <td>{{ $r['comment'] }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="11" align="center">ไม่มีข้อมูล</td>
      </tr>
    @endforelse
  </tbody>
</table>
