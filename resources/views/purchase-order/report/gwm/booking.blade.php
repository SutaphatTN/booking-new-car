<table>
  <thead>
    <tr>
      <th>รุ่นรถหลัก</th>
      <th>รุ่นย่อย</th>
      <th>สี</th>
      <th>สีภายใน</th>
      @foreach ($branches as $branch)
      <th>{{ $branch }}</th>
      @endforeach
      <th>รวม</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($pivot as $row)
      <tr>
        <td>{{ $row['mainModel'] }}</td>
        <td>{{ $row['subModel'] }}</td>
        <td>{{ $row['color'] }}</td>
        <td>{{ $row['interiorColor'] }}</td>
        @foreach ($branches as $branch)
        <td>{{ $row['branchCounts'][$branch] ?? 0 }}</td>
        @endforeach
        <td>{{ $row['total'] }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="{{ 5 + count($branches) }}" align="center">ไม่มีข้อมูล</td>
      </tr>
    @endforelse

    @if (count($pivot) > 0)
    @php
      $startRow = 2;
      $endRow   = count($pivot) + 1;
      $colOffset = 4;
    @endphp
    <tr>
      <td colspan="4" align="center">รวมทั้งหมด</td>
      @foreach ($branches as $i => $branch)
      @php $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colOffset + $i + 1); @endphp
      <td>=SUM({{ $col }}{{ $startRow }}:{{ $col }}{{ $endRow }})</td>
      @endforeach
      @php $totalCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colOffset + count($branches) + 1); @endphp
      <td>=SUM({{ $totalCol }}{{ $startRow }}:{{ $totalCol }}{{ $endRow }})</td>
    </tr>
    @endif
  </tbody>
</table>
