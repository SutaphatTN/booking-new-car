<table>
  <thead>
    <tr>
      <th>สาขา</th>
      <th>รุ่นรถหลัก</th>
      <th>รุ่นย่อย</th>
      <th>สี</th>
      <th>สีภายใน</th>
      <th>รถทั้งหมด (คัน)</th>
      {{-- <th>มีลูกค้าแล้ว (คัน)</th>
      <th>ว่าง (คัน)</th> --}}
    </tr>
  </thead>
  <tbody>
    @forelse ($book as $b)
    @php
    $startRow = 2;
    $endRow = count($book) + 1;
    @endphp
      <tr>
        <td>{{ $b['branch'] }}</td>
        <td>{{ $b['mainModel'] }}</td>
        <td>{{ $b['subModel'] }}</td>
        <td>{{ $b['color'] }}</td>
        <td>{{ $b['interiorColor'] }}</td>
        <td>{{ $b['total'] }}</td>
        {{-- <td>{{ $b['withCustomer'] }}</td>
        <td>{{ $b['available'] }}</td> --}}
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
      <td colspan="5" align="center">รวมทั้งหมด</td>
      <td>=SUM(F{{ $startRow }}:F{{ $endRow }})</td>
      {{-- <td>=SUM(G{{ $startRow }}:G{{ $endRow }})</td>
      <td>=SUM(H{{ $startRow }}:H{{ $endRow }})</td> --}}
    </tr>
    @endif
  </tbody>
</table>
