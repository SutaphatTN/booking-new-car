<table>
  <thead>
    <tr>
      <th>No</th>
      <th>ชื่อ - นามสกุล ลูกค้า</th>
      <th>เลขตัวถัง</th>
      <th>เลขเครื่อง</th>
      <th>วันที่จดทะเบียน</th>
      <th>เลขทะเบียน</th>
      <th>จังหวัด</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($rows as $r)
    <tr>
      <td>{{ $loop->iteration }}</td>
      <td>{{ $r['customer'] }}</td>
      <td>{{ $r['vin'] }}</td>
      <td>{{ $r['engine_number'] }}</td>
      <td>{{ $r['backup_clear_date'] }}</td>
      <td>{{ $r['license_plate'] }}</td>
      <td>{{ $r['license_province'] }}</td>
    </tr>
    @empty
    <tr>
      <td colspan="7" align="center">ไม่มีข้อมูล</td>
    </tr>
    @endforelse
  </tbody>
</table>
