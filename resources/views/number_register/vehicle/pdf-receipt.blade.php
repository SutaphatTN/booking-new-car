<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <title>Receipt Report</title>
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
      width: 100%;
      border-collapse: collapse;
    }

    th,
    td {
      border: 1px solid #000;
      padding: 5px;
      text-align: center;
    }

    .text-left {
      text-align: left;
    }
  </style>
</head>

<body>

  <h3 style="text-align:center;">เคลียร์ค่าจดทะเบียน</h3>

  <table>
    <thead>
      <thead>
        <tr>
          <th rowspan="2">ลำดับ</th>
          <th rowspan="2">ชื่อ-สกุล</th>
          <th rowspan="2">เลขตัวถัง</th>
          <th rowspan="2">จังหวัดขึ้นทะเบียน</th>
          <th colspan="4">ตั้งเบิก</th>
          <th colspan="5">เคลียร์</th>
        </tr>

        <tr>
          <th>ตรวจ</th>
          <th>ช่อง</th>
          <th>ใบเสร็จ</th>
          <th>รวมเบิก</th>

          <th>ตรวจ</th>
          <th>ช่อง</th>
          <th>ใบเสร็จ</th>
          <th>รวมเคลียร์</th>
          <th>คืน</th>
        </tr>
      </thead>
    </thead>
    <tbody>
      @php
        $sumCheck = 0;
        $sumChannel = 0;
        $sumBill = 0;
        $sumTotal = 0;
        $sumReCheck = 0;
        $sumReChannel = 0;
        $sumReBill = 0;
        $sumReTotal = 0;
        $sumRefund = 0;
      @endphp

      @foreach ($data as $i => $d)
        @php
          $sumCheck += $d->withdrawal_check ?? 0;
          $sumChannel += $d->withdrawal_channel ?? 0;
          $sumBill += $d->withdrawal_bill ?? 0;
          $sumTotal += $d->withdrawal_total ?? 0;
          $sumReCheck += $d->receipt_check ?? 0;
          $sumReChannel += $d->receipt_channel ?? 0;
          $sumReBill += $d->receipt_bill ?? 0;
          $sumReTotal += $d->receipt_total ?? 0;
          $sumRefund += $d->diff ?? 0;
        @endphp
        <tr>
          <td>{{ $i + 1 }}</td>
          <td class="text-left">
            {{ $d->saleCar->customer->prefix?->Name_TH ?? '' }} {{ $d->saleCar->customer->FirstName ?? '' }}
            {{ $d->saleCar->customer->LastName ?? '' }}
          </td>
          <td>{{ $d->saleCar->carOrder->vin_number ?? '' }}</td>
          <td>{{ $d->saleCar->provinces->name ?? '' }}</td>
          <td>{{ number_format($d->withdrawal_check, 2) ?? '' }}</td>
          <td>{{ number_format($d->withdrawal_channel, 2) ?? '' }}</td>
          <td>{{ number_format($d->withdrawal_bill, 2) ?? '' }}</td>
          <td>{{ number_format($d->withdrawal_total, 2) ?? '' }}</td>
          <td>{{ number_format($d->receipt_check, 2) ?? '' }}</td>
          <td>{{ number_format($d->receipt_channel, 2) ?? '' }}</td>
          <td>{{ number_format($d->receipt_bill, 2) ?? '' }}</td>
          <td>{{ number_format($d->receipt_total, 2) ?? '' }}</td>
          <td>{{ number_format($d->diff, 2) ?? '' }}</td>
        </tr>
      @endforeach

      <tr>
        <td colspan="4"><b>รวมทั้งหมด</b></td>
        <td><b>{{ number_format($sumCheck, 2) }}</b></td>
        <td><b>{{ number_format($sumChannel, 2) }}</b></td>
        <td><b>{{ number_format($sumBill, 2) }}</b></td>
        <td><b>{{ number_format($sumTotal, 2) }}</b></td>
        <td><b>{{ number_format($sumReCheck, 2) }}</b></td>
        <td><b>{{ number_format($sumReChannel, 2) }}</b></td>
        <td><b>{{ number_format($sumReBill, 2) }}</b></td>
        <td><b>{{ number_format($sumReTotal, 2) }}</b></td>
        <td><b>{{ number_format($sumRefund, 2) }}</b></td>
      </tr>
    </tbody>
  </table>

  <br><br>

  <table style="width:100%; margin-top:20px; border-collapse:collapse;">
    <tr>
      <td style="width:50%; text-align:center; border:none !important;">
        นางสาวอริสา ย่าสัน<br>
        ฝ่ายทะเบียน
      </td>

      <td style="width:50%; text-align:center; border:none !important;">
        พรวิมล ทองปิด<br>
        ฝ่ายการเงิน
      </td>
    </tr>
  </table>

</body>

</html>
