@php $colspan = 7; @endphp
<table>
  <thead>
    <tr>
      <th colspan="{{ $colspan }}">สรุปทดลองขับ (Test Drive) ประจำเดือน {{ $monthLabel }}</th>
    </tr>
    <tr>
      <th>No.</th>
      <th>แบรนด์</th>
      <th>สาขา</th>
      <th>ผู้ขาย</th>
      <th>จำนวนทดลองขับ</th>
      <th>จองแล้ว</th>
      <th>% ปิดการขาย</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($rows as $r)
      <tr>
        <td>{{ $r['no'] }}</td>
        <td>{{ $r['brand'] }}</td>
        <td>{{ $r['branch'] }}</td>
        <td>{{ $r['sale'] }}</td>
        <td>{{ $r['total'] }}</td>
        <td>{{ $r['booked'] }}</td>
        <td>{{ $r['booked_rate'] }}</td>
      </tr>
    @endforeach
    <tr>
      <td colspan="4" align="center">รวม</td>
      <td>{{ $sumTotal }}</td>
      <td>{{ $sumBooked }}</td>
      <td>{{ $sumRate }}</td>
    </tr>
  </tbody>
</table>
