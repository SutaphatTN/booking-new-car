<table>
  <thead>
    <tr>
      <th>รุ่นรถหลัก</th>
      <th>1-90 วัน</th>
      <th>91-180 วัน</th>
      <th>181-270 วัน</th>
      <th>271-365 วัน</th>
      <th>366 วันขึ้นไป</th>
      <th>รวม</th>
    </tr>
  </thead>

  <tbody>
    @forelse($rows as $r)
    <tr>
      <td>{{ $r['model'] }}</td>
      <td>{{ $r['b1'] }}</td>
      <td>{{ $r['b2'] }}</td>
      <td>{{ $r['b3'] }}</td>
      <td>{{ $r['b4'] }}</td>
      <td>{{ $r['b5'] }}</td>
      <td>{{ $r['total'] }}</td>
    </tr>
     @empty
    <tr>
      <td colspan="7" align="center">ไม่มีข้อมูล</td>
    </tr>
    @endforelse
  </tbody>
</table>