<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <title>Accessory Partner Report</title>
  <style>
    @font-face {
      font-family: 'THSarabunNew';
      src: url("{{ public_path('fonts/THSarabunNew.ttf') }}") format('truetype');
      font-weight: normal;
      font-style: normal;
    }

    @font-face {
      font-family: 'THSarabunNew';
      src: url("{{ public_path('fonts/THSarabunNew-Bold.ttf') }}") format('truetype');
      font-weight: bold;
      font-style: normal;
    }

    body {
      font-family: 'THSarabunNew', DejaVu Sans, sans-serif;
      font-size: 14pt;
      margin: 0px;
      line-height: 0.9;
    }

    table {
      page-break-inside: auto;
    }

    tr {
      page-break-inside: avoid;
      page-break-after: auto;
    }

    td {
      word-wrap: break-word;
      word-break: break-word;
      text-align: center;
    }
  </style>
</head>

<body>
  @foreach ($groups as $partnerId => $rows)
    <h3 style="text-align: center;">
      ใบเบิกค่าอุปกรณ์ ({{ $rows->first()['partner_name'] }})
    </h3>

    <p>วันที่: {{ $from }} ถึง {{ $to }}</p>

    <table width="100%" border="1" cellspacing="0" cellpadding="5">
      <thead>
        <tr>
          <th width="10">ลำดับ</th>
          <th>วันที่ส่งมอบ</th>
          <th>ลูกค้า</th>
          <th>vin-number</th>
          <th>Accessory</th>
          <th>ต้นทุน</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($rows as $row)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $row['delivery_date'] }}</td>
            <td>{{ $row['customer'] }}</td>
            <td>{{ $row['vin'] }}</td>
            <td>{!! $row['accessory_name'] !!}</td>
            <td style="text-align:right">
              {{ number_format($row['cost'], 2) }}
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <p style="text-align:right; margin-top:10px;">
      <strong>
        รวม: {{ number_format($rows->sum('cost'), 2) }} บาท
      </strong>
    </p>

    {{-- แยกหน้า --}}
    @if (!$loop->last)
      <div style="page-break-after: always;"></div>
    @endif
  @endforeach
</body>

</html>
