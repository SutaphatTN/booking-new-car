<table>
  <thead>
    <tr>
      @foreach ($headers as $h)
        <th>{{ $h }}</th>
      @endforeach
    </tr>
  </thead>
  <tbody>
    @forelse ($rows as $r)
      <tr>
        @foreach ($r as $cell)
          <td>{{ $cell === null ? '' : $cell }}</td>
        @endforeach
      </tr>
    @empty
      <tr>
        <td colspan="{{ count($headers) }}" align="center">ไม่มีข้อมูล</td>
      </tr>
    @endforelse

    @if (!empty($totalRow))
      <tr>
        @foreach ($totalRow as $cell)
          <td>{{ $cell === null ? '' : $cell }}</td>
        @endforeach
      </tr>
    @endif
  </tbody>
</table>
