<table>
  <thead>
    <tr>
      <th>No</th>
      <th>เลขป้ายแดง</th>
      <th>ยืมโดยแบรนด์</th>
      <th>วันที่ยืม</th>
      <th>วันที่คืน</th>
      <th>สถานะ</th>
      <th>ผู้บันทึกยืม</th>
      <th>ผู้บันทึกคืน</th>
      <th>หมายเหตุ</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($loans as $l)
    <tr>
      <td>{{ $loop->iteration }}</td>
      <td>{{ $l['number'] }}</td>
      <td>{{ $l['borrower'] }}</td>
      <td>{{ $l['borrow_date'] }}</td>
      <td>{{ $l['return_date'] }}</td>
      <td>{{ $l['status'] }}</td>
      <td>{{ $l['borrowed_by'] }}</td>
      <td>{{ $l['returned_by'] }}</td>
      <td>{{ $l['note'] }}</td>
    </tr>
    @empty
    <tr>
      <td colspan="9" align="center">
        ไม่มีข้อมูล
      </td>
    </tr>
    @endforelse
  </tbody>
</table>
