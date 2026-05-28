<table>
  <thead>
    <tr>
      <th colspan="{{ $colCount }}">รายงานสรุปประมาณการณ์ที่จะตัดเดือนนี้</th>
    </tr>
    <tr>
      <th>ฝ่ายขาย / รุ่นรถ</th>
      @foreach ($statuses as $status)
        <th>{{ $status->name }}</th>
      @endforeach
      <th>ผลรวมทั้งหมด</th>
    </tr>
  </thead>
  <tbody>
    @forelse ($summary as $saleName => $models)
      {{-- sale person row --}}
      <tr>
        <td>{{ $saleName }}</td>
        @foreach ($statuses as $status)
          <td>{{ $saleTotals[$saleName][$status->id] ?? '' }}</td>
        @endforeach
        <td>{{ array_sum($saleTotals[$saleName] ?? []) }}</td>
      </tr>
      {{-- model sub-rows --}}
      @foreach ($models as $modelName => $statusCounts)
        <tr>
          <td>    {{ $modelName }}</td>
          @foreach ($statuses as $status)
            <td>{{ $statusCounts[$status->id] ?? '' }}</td>
          @endforeach
          <td>{{ array_sum($statusCounts) }}</td>
        </tr>
      @endforeach
    @empty
      <tr>
        <td colspan="{{ $colCount }}" align="center">ไม่มีข้อมูล</td>
      </tr>
    @endforelse
    @if (!empty($summary))
      {{-- grand total row --}}
      <tr>
        <td>ผลรวมทั้งหมด</td>
        @foreach ($statuses as $status)
          <td>{{ $grandTotal[$status->id] ?? '' }}</td>
        @endforeach
        <td>{{ array_sum($grandTotal) }}</td>
      </tr>
    @endif
  </tbody>
</table>
