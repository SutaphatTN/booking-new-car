<table>
  <thead>
    <tr>
      <th>รุ่นรถหลัก</th>
      <th>รุ่นย่อย</th>
      <th>สี</th>
      <th>สีภายใน</th>
      <th>รถทั้งหมด (คัน)</th>
      <th>มีลูกค้าแล้ว (คัน)</th>
      <th>ว่าง (คัน)</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($stock as $s)
    @php
    $startRow = 2;
    $endRow = count($stock) + 1;
    @endphp
      <tr>
        <td>{{ $s['mainModel'] }}</td>
        <td>{{ $s['subModel'] }}</td>
        <td>{{ $s['color'] }}</td>
        <td>{{ $s['interiorColor'] }}</td>
        <td>{{ $s['total'] }}</td>
        <td>{{ $s['withCustomer'] }}</td>
        <td>{{ $s['available'] }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="7" align="center">
          ไม่มีข้อมูล
        </td>
      </tr>
    @endforelse

    @if(count($stock) > 0)
    <tr>
      <td colspan="4" align="center">รวมทั้งหมด</td>
      <td>=SUM(E{{ $startRow }}:E{{ $endRow }})</td>
      <td>=SUM(F{{ $startRow }}:F{{ $endRow }})</td>
      <td>=SUM(G{{ $startRow }}:G{{ $endRow }})</td>
    </tr>
    @endif
  </tbody>
</table>
