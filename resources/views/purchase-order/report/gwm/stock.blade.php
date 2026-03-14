<table>
  <thead>
    <tr>
      <th>No</th>
      <th>Model</th>
      <th>Exterior Color</th>
      <th>Interior Color</th>
      <th>Month</th>
      <th>Stock</th>
      <th>จอง / ส่งมอบ</th>
      <th>คงเหลือที่ขายได้</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($stock as $s)
    @php
    $startRow = 2;
    $endRow = count($stock) + 1;
    @endphp
      <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $s['model'] }}</td>
        <td>{{ $s['color'] }}</td>
        <td>{{ $s['interiorColor'] }}</td>
        <td>{{ $s['date'] }}</td>
        <td>{{ $s['stock'] }}</td>
        <td>{{ $s['total_booking'] }}</td>
        <td>{{ $s['total'] }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="8" align="center">
          ไม่มีข้อมูล
        </td>
      </tr>
    @endforelse

    @if(count($stock) > 0)
    <tr>
      <td>Total</td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>

      <td>=SUM(F{{ $startRow }}:F{{ $endRow }})</td>
      <td>=SUM(G{{ $startRow }}:G{{ $endRow }})</td>
      <td>=SUM(H{{ $startRow }}:H{{ $endRow }})</td>
    </tr>
    @endif
  </tbody>
</table>
