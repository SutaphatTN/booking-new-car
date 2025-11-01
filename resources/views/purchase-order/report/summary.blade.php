<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <title>Summary Purchase</title>
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
      font-size: 16pt;
      margin: 0px 50px 30px 50px;
    }

    h2 {
      text-align: center;
      margin-bottom: 5px;
    }

    .print-date {
      text-align: right;
      font-size: 14pt;
      margin-bottom: 20px;
    }

    .section-title {
      font-weight: bold;
      margin-top: 0px;
      margin-bottom: 0px;
      font-size: 17pt;
    }

    .info-block {
      margin-left: 10px;
      line-height: 1.6;
    }

    .info-block p {
      margin: 0;
    }

    .info-row {
      display: flex;
      flex-wrap: wrap;
      line-height: 1;
      margin-left: 15px;
      margin-top: 0px;
      margin-bottom: 0px;
    }

    .info-row span {
      white-space: nowrap;
    }

    .info-row strong {
      font-weight: bold;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    td {
      width: 50%;
      border: 1px solid #ccc;
      vertical-align: top;
      border: 1px solid #000;
    }

    th {
      border: 1px solid #000;
      background-color: #f2f2f2;
      text-align: center;
    }

    p {
      margin-top: 0px;
      margin-bottom: 0px;
    }

    .label-top {
      display: table;
      width: 100%;
    }

    .label-top img {
      display: table-cell;
      vertical-align: top;
      width: 140px;
      height: 140px;
    }

    .label-text {
      display: table-cell;
      vertical-align: top;
      padding-left: 8px;
      font-size: 16pt;
      text-align: left;
    }

    .label-text p {
      margin: 0;
      line-height: 1.2;
    }
  </style>
</head>

<body>
  <h2>สรุปค่าใช้จ่ายใบจองรถ</h2>
  <div class="print-date">วันที่พิมพ์: {{ now()->format('d/m/Y H:i') }}</div>

  <div class="section-title">ข้อมูลลูกค้า</div>
  <div class="info-row">
    <span><strong>ชื่อ - นามสกุล :</strong> {{ $saleCar->customer->prefix->Name_TH ?? '' }} {{ $saleCar->customer->FirstName }} {{ $saleCar->customer->LastName }}</span>
    &nbsp;&nbsp;
    <span><strong>เบอร์โทรศัพท์ :</strong> {{ $saleCar->customer->Mobilephone1 ?? '' }}</span>
  </div>

  <div class="section-title">รายละเอียดรถ</div>
  <div class="info-row">
    <span><strong>รุ่นรถ : </strong> {{ $saleCar->carModel->Name_TH ?? '-' }}</span>
    &nbsp;&nbsp;
    <span><strong>สี : </strong> {{ $saleCar->Color ?? '-' }}</span>
  </div>

  <div class="section-title">รายละเอียดอุปกรณ์ตกแต่ง (แถม)</div>
  @php
  $giftAccessories = $saleCar->accessories->where('pivot.type', 'gift');
  @endphp
  @if($giftAccessories->isNotEmpty())
  <table style="width:100%; border-collapse: collapse;">
    <thead>
      <tr>
        <th>ลำดับ</th>
        <th>รหัส</th>
        <th>รายละเอียด</th>
        <th>ประเภทราคา</th>
        <th>ราคา</th>
      </tr>
    </thead>
    <tbody>
      @foreach($giftAccessories as $index => $giftAccessories)
      <tr>
        <td style="text-align: center;">{{ $index + 1 }}</td>
        <td style="text-align: center;">{{ $giftAccessories->AccessorySource ?? '-' }}</td>
        <td style="text-align: center;">{{ $giftAccessories->AccessoryDetail ?? '-' }}</td>
        <td style="text-align: center;">{{ $giftAccessories->pivot->price_type ?? '-' }}</td>
        <td style="text-align: right;">{{ number_format($giftAccessories->pivot->price, 2) }}</td>
      </tr>
      @endforeach
      <tr>
        <td colspan="4" style="text-align: center; font-weight: bold;">รวมทั้งหมด</td>
        <td style="text-align: right; font-weight: bold;">{{ number_format($saleCar->TotalAccessoryGift ?? 0, 2) }}</td>
      </tr>
    </tbody>
  </table>
  @else
  <p>- ไม่มีข้อมูลรายการของแถม -</p>
  @endif

  <div class="section-title">รายการซื้อเพิ่ม</div>
  @php
  $extraAccessories = $saleCar->accessories->where('pivot.type', 'extra');
  @endphp
  @if($extraAccessories->isNotEmpty())
  <table style="width:100%; border-collapse: collapse;">
    <thead>
      <tr>
        <th>ลำดับ</th>
        <th>รหัส</th>
        <th>รายละเอียด</th>
        <th>ประเภทราคา</th>
        <th>ราคา</th>
      </tr>
    </thead>
    <tbody>
      @foreach($extraAccessories as $index => $extraAccessories)
      <tr>
        <td style="text-align: center;">{{ $index + 1 }}</td>
        <td style="text-align: center;">{{ $extraAccessories->AccessorySource ?? '-' }}</td>
        <td style="text-align: center;">{{ $extraAccessories->AccessoryDetail ?? '-' }}</td>
        <td style="text-align: center;">{{ $extraAccessories->pivot->price_type ?? '-' }}</td>
        <td style="text-align: right;">{{ number_format($extraAccessories->pivot->price, 2) }}</td>
      </tr>
      @endforeach
      <tr>
        <td colspan="4" style="text-align: center; font-weight: bold;">รวมทั้งหมด</td>
        <td style="text-align: right; font-weight: bold;">{{ number_format($saleCar->TotalAccessoryExtra ?? 0, 2) }}</td>
      </tr>
    </tbody>
  </table>
  @else
  <p>- ไม่มีข้อมูลรายการซื้อเพิ่ม -</p>
  @endif

  <div class="section-title">ข้อมูลแคมเปญ</div>
  @if($saleCar->campaigns->isNotEmpty())
  <table style="width:100%; border-collapse: collapse;">
    <thead>
      <tr>
        <th>ลำดับ</th>
        <th>ชื่อแคมเปญ</th>
        <th>ยอด (บาท)</th>
      </tr>
    </thead>
    <tbody>
      @foreach($saleCar->campaigns as $index => $saleCampaign)
      <tr>
        <td style="text-align: center;">{{ $index + 1 }}</td>
        <td style="text-align: center;">{{ $saleCampaign->campaign->campaignType->Name_TH ?? '-' }}</td>
        <td style="text-align: right;">{{ number_format($saleCampaign->CashSupport, 2) }}</td>

        <!-- <td style="border: 1px solid #000; padding: 5px;">{{ $saleCampaign->campaign->SubCampaignType ?? '-' }}</td>
        <td style="border: 1px solid #000; padding: 5px; text-align: right;">{{ number_format($saleCampaign->CashSupportDeduct, 2) }}</td>
        <td style="border: 1px solid #000; padding: 5px; text-align: right;">{{ number_format($saleCampaign->CashSupportFinal, 2) }}</td> -->
      </tr>
      @endforeach

      <tr>
        <td colspan="2" style="text-align: center; font-weight: bold;">รวมทั้งหมด</td>
        <td style="text-align: right; font-weight: bold;">{{ number_format($saleCar->TotalSaleCampaign ?? 0, 2) }}</td>
      </tr>
    </tbody>
  </table>

  <div class="section-title">ยอดคงเหลือแคมเปญ</div>
  @php
  $totalSaleCampaign = $saleCar->TotalSaleCampaign ?? 0;
  $markup90 = $saleCar->Markup90 ?? 0;
  $downPaymentDiscount = $saleCar->DownPaymentDiscount ?? 0;
  $totalAccessoryGift = $saleCar->TotalAccessoryGift ?? 0;

  $campaignBalance = ($totalSaleCampaign + $markup90) - ($downPaymentDiscount + $totalAccessoryGift);

  $campaignMessage = number_format($campaignBalance, 2);

  $campaignT = $totalSaleCampaign + $markup90;

   $campaignTB = number_format($campaignT, 2);
  @endphp
  <!-- if ($campaignBalance > 0) {
  $campaignMessage = 'ได้กำไร : ' . number_format($campaignBalance, 2) . ' บาท';
  } elseif ($campaignBalance < 0) {
    $campaignMessage='ขาดทุน : ' . number_format(abs($campaignBalance), 2) . ' บาท' ;
    } else {
    $campaignMessage='เสมอตัว' ;
    }-->


  <table style="width:100%; border-collapse: collapse;">
    <tbody>
      <tr>
        <td style="text-align: center; font-weight: bold;">ยอดรวมแคมเปญ</td>
        <td style="text-align: right; font-weight: bold;">{{ number_format($saleCar->TotalSaleCampaign ?? 0, 2) }}</td>
      </tr>
      <tr>
        <td style="text-align: center; font-weight: bold;">บวกหัว 90%</td>
        <td style="text-align: right; font-weight: bold;">{{ number_format($saleCar->Markup90 ?? 0, 2) }}</td>
      </tr>
      <tr>
        <td style="text-align: center; font-weight: bold;">ยอดรวมแคมเปญ รวม บวกหัว 90%</td>
        <td style="text-align: right; font-weight: bold;">{{ $campaignTB }}</td>
      </tr>
      <!-- <tr>
        <td style="text-align: center; font-weight: bold;">ยอดรวมของแถม</td>
        <td style="text-align: right; font-weight: bold;">{{ number_format($saleCar->TotalAccessoryGift ?? 0, 2) }}</td>
      </tr> -->
      <tr>
        <td style="text-align: center; font-weight: bold;">คงเหลือ</td>
        <td style="text-align: right; font-weight: bold;">{{ $campaignMessage }}</td>
      </tr>
    </tbody>
  </table>
  @else
  <p>- ไม่มีข้อมูลแคมเปญ -</p>
  @endif

  <div class="section-title">สรุปยอด</div>
  <table style="width:100%; border-collapse: collapse;">
    <tbody>
      <tr>
        <td style="text-align: center; font-weight: bold;">ราคารถ (รวมบวกหัว)</td>
        <td style="text-align: right; font-weight: bold;">{{ number_format($saleCar->CarSalePriceFinal ?? 0, 2) }}</td>
      </tr>
      <tr>
        <td style="text-align: center; font-weight: bold;">ยอดดาวน์</td>
        <td style="text-align: right; font-weight: bold;">{{ number_format($saleCar->DownPayment ?? 0, 2) }}</td>
      </tr>
      <tr>
        <td style="text-align: center; font-weight: bold;">ค่าใช้จ่ายสำหรับวันที่ออกรถ</td>
        <td style="text-align: right; font-weight: bold;">{{ number_format($saleCar->TotalPaymentatDelivery ?? 0, 2) }}</td>
      </tr>
      <tr>
        <td style="text-align: center; font-weight: bold;">ยอดที่เหลือ</td>
        <td style="text-align: right; font-weight: bold;">{{ number_format($saleCar->TotalCashSupportUsed ?? 0, 2) }}</td>
      </tr>
    </tbody>
  </table>


</body>

</html>