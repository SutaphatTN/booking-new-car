 <table>
  <thead>
    <tr>
      <th>No</th>
      <th>Model</th>
      <th>Exterior Color</th>
      <th>Interior Color</th>
      <th>Month</th>
      <th>Units</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($book as $b)
    @php
    $startRow = 2;
    $endRow = count($book) + 1;
    @endphp
      <tr>
        <td>{{ $loop->iteration }}</td>
        <td>{{ $b['model'] }}</td>
        <td>{{ $b['color'] }}</td>
        <td>{{ $b['interiorColor'] }}</td>
        <td>{{ $b['date'] }}</td>
        <td>{{ $b['units'] }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="6" align="center">
          ไม่มีข้อมูล
        </td>
      </tr>
    @endforelse

    @if(count($book) > 0)
    <tr>
      <td>Total</td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>

      <td>=SUM(F{{ $startRow }}:F{{ $endRow }})</td>
    </tr>
    @endif
  </tbody>
</table>
