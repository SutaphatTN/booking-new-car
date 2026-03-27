<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <title>Invoice {{ $invoice->code_number }}</title>
  <style>
    @font-face {
      font-family: 'THSarabunNew';
      src: url("{{ public_path('fonts/THSarabunNew.ttf') }}") format('truetype');
      font-weight: normal;
    }

    @font-face {
      font-family: 'THSarabunNew';
      src: url("{{ public_path('fonts/THSarabunNew-Bold.ttf') }}") format('truetype');
      font-weight: bold;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'THSarabunNew', DejaVu Sans, sans-serif;
      font-size: 13pt;
      color: #1a1a1a;
    }

    .page {
      padding: 28px 36px;
      page-break-after: always;
    }

    .page:last-child {
      page-break-after: avoid;
    }

    .header {
      width: 100%;
      display: table;
      margin-bottom: 18px;
      border-bottom: 3px solid #1a3c6e;
      padding-bottom: 12px;
    }

    .header-left {
      display: table-cell;
      vertical-align: middle;
      width: 60%;
    }

    .header-right {
      display: table-cell;
      vertical-align: middle;
      width: 40%;
      text-align: right;
    }

    .brand-name {
      font-size: 22pt;
      font-weight: bold;
      color: #1a3c6e;
      line-height: 1.1;
    }

    .brand-sub {
      font-size: 11pt;
      color: #555;
      margin-top: 2px;
    }

    .doc-title {
      font-size: 20pt;
      font-weight: bold;
      color: #1a3c6e;
      letter-spacing: 1px;
    }

    .doc-meta {
      font-size: 11pt;
      color: #333;
      margin-top: 4px;
      line-height: 1.5;
    }

    .partner-badge {
      display: inline-block;
      background: #1a3c6e;
      color: #fff;
      font-size: 11pt;
      font-weight: bold;
      padding: 3px 12px;
      border-radius: 3px;
      margin-bottom: 14px;
    }

    .info-section {
      width: 100%;
      display: table;
      margin-bottom: 16px;
    }

    .info-box {
      display: table-cell;
      width: 50%;
      vertical-align: top;
      padding: 10px 12px;
      background: #f5f7fa;
      border: 1px solid #dce3ed;
    }

    .info-box:first-child {
      padding-right: 8px;
    }

    .info-box:last-child {
      padding-left: 8px;
    }

    .info-title {
      font-size: 10pt;
      font-weight: bold;
      color: #1a3c6e;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      border-bottom: 1px solid #c5d0e0;
      padding-bottom: 4px;
      margin-bottom: 6px;
    }

    .info-row {
      display: table;
      width: 100%;
      margin-bottom: 2px;
    }

    .info-label {
      display: table-cell;
      width: 42%;
      color: #555;
      font-size: 11.5pt;
    }

    .info-value {
      display: table-cell;
      font-weight: bold;
      font-size: 11.5pt;
    }

    .section-title {
      font-size: 13pt;
      font-weight: bold;
      color: #1a3c6e;
      margin-bottom: 6px;
      border-left: 4px solid #1a3c6e;
      padding-left: 8px;
    }

    table.items {
      width: 100%;
      border-collapse: collapse;
      font-size: 12pt;
    }

    table.items thead tr {
      background-color: #1a3c6e;
      color: #ffffff;
    }

    table.items thead th {
      padding: 7px 10px;
      text-align: center;
      font-weight: bold;
    }

    table.items tbody tr:nth-child(even) {
      background: #f0f4f9;
    }

    table.items tbody td {
      padding: 6px 10px;
      border-bottom: 1px solid #dce3ed;
      vertical-align: middle;
    }

    table.items tbody td.num {
      text-align: center;
    }

    table.items tbody td.money {
      text-align: right;
    }

    table.items tfoot td {
      padding: 6px 10px;
      font-weight: bold;
      font-size: 13pt;
    }

    table.items tfoot .total-label {
      text-align: right;
      color: #1a3c6e;
    }

    table.items tfoot .total-value {
      text-align: right;
      color: #1a3c6e;
      border-top: 2px solid #1a3c6e;
    }

    .sign-section {
      width: 100%;
      display: table;
      margin-top: 36px;
    }

    .sign-box {
      display: table-cell;
      width: 33.33%;
      text-align: center;
      padding: 0 10px;
    }

    .sign-line {
      border-top: 1px dashed #999;
      margin: 0 20px 4px;
    }

    .sign-label {
      font-size: 11pt;
      color: #333;
    }

    .sign-date {
      font-size: 10.5pt;
      color: #777;
      margin-top: 2px;
    }

    .page-note {
      text-align: center;
      font-size: 9pt;
      color: #aaa;
      margin-top: 24px;
      border-top: 1px solid #eee;
      padding-top: 8px;
    }
  </style>
</head>

<body>

  @php
    $totalGroups = count($groupedAccessories);
    $pageNum = 0;
  @endphp

  @forelse ($groupedAccessories as $partnerId => $items)
    @php
      $pageNum++;
      $partnerName = $items->first()?->partner?->name ?? 'ไม่ระบุร้านค้า';
      $totalCost = $items->sum('cost_price');
      $totalSale = $items->sum('sale_price');
    @endphp

    <div class="page">

      <div class="header">
        <div class="header-left">
          <div class="brand-name">{{ $brandName }}</div>
          <div class="brand-sub">ใบสั่งซื้ออุปกรณ์ตกแต่ง / Accessory Purchase Order</div>
        </div>
        <div class="header-right">
          <div class="doc-title">INVOICE</div>
          <div class="doc-meta">
            เลขที่ : <strong>{{ $invoice->code_number }}-{{ str_pad($pageNum, 2, '0', STR_PAD_LEFT) }}</strong><br>
            วันที่ : <strong>{{ $invoice->formatted_date }}</strong><br>
            {{-- หน้า {{ $pageNum }} / {{ $totalGroups }} --}}
          </div>
        </div>
      </div>

      <div class="partner-badge">
        <span>ร้านค้า : {{ $partnerName }}</span>
      </div>

      <div class="info-section">
        <div class="info-box">
          <div class="info-title">ข้อมูลลูกค้า</div>
          <div class="info-row">
            <span class="info-label">ชื่อ-นามสกุล</span>
            <span class="info-value">{{ $invoice->customer_name }}</span>
          </div>
          <div class="info-row">
            <span class="info-label">เบอร์โทรศัพท์</span>
            <span class="info-value">{{ $invoice->formatted_phone }}</span>
          </div>
        </div>
        <div class="info-box">
          <div class="info-title">ข้อมูลรถ</div>
          <div class="info-row">
            <span class="info-label">ป้ายทะเบียน</span>
            <span class="info-value">{{ $invoice->license_plate ?: '-' }}</span>
          </div>
          <div class="info-row">
            <span class="info-label">เลขถัง (VIN)</span>
            <span class="info-value">{{ $invoice->vin_number ?: '-' }}</span>
          </div>
          <div class="info-row">
            <span class="info-label">เลขเครื่อง</span>
            <span class="info-value">{{ $invoice->engine_number ?: '-' }}</span>
          </div>
        </div>
      </div>

      <div class="section-title">รายการอุปกรณ์</div>
      <table class="items">
        <thead>
          <tr>
            <th style="width:10%">ที่</th>
            <th style="width:65%">รายละเอียด</th>
            <th style="width:25%">ราคาทุน</th>
            {{-- <th style="width:20%">ราคาขาย</th> --}}
          </tr>
        </thead>
        <tbody>
          @foreach ($items as $i => $acc)
            <tr>
              <td class="num">{{ $i + 1 }}</td>
              <td style="text-align: center;">
                {{ $acc->detail ?? '-' }}
              </td>
              <td class="money">
                {{ $acc->cost_price !== null ? number_format($acc->cost_price, 2) : '-' }}
              </td>
              {{-- <td class="money">
            {{ $acc->sale_price !== null ? number_format($acc->sale_price, 2) : '-' }}
          </td> --}}
            </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr>
            <td colspan="2" class="total-label">รวมทั้งสิ้น</td>
            <td class="total-value">{{ number_format($totalCost, 2) }}</td>
            {{-- <td class="total-value">{{ number_format($totalSale, 2) }}</td> --}}
          </tr>
        </tfoot>
      </table>

      <div class="sign-section">
        <div class="sign-box">
          <div class="sign-line"></div>
          <div class="sign-label">{{ $invoice->insertInvoice?->name ?: '-' }}</div>
          <div class="sign-date">{{ $invoice->formatted_request_date }}</div>
        </div>
        {{-- <div class="sign-box">
        <div class="sign-line"></div>
        <div class="sign-label">ผู้รับสินค้า ({{ $partnerName }})</div>
        <div class="sign-date">วันที่ ............/............/............</div>
      </div> --}}
        <div class="sign-box">
          <div class="sign-line"></div>
          <div class="sign-label">{{ $invoice->approvedInvoice?->name ?: '-' }}</div>
          <div class="sign-date">{{ $invoice->formatted_approved_date }}</div>
        </div>
      </div>

      <div class="page-note">
        {{ $brandName }} &nbsp;|&nbsp; {{ $invoice->code_number }}-{{ str_pad($pageNum, 2, '0', STR_PAD_LEFT) }}
        &nbsp;|&nbsp; ร้านค้า: {{ $partnerName }}
      </div>

    </div>

  @empty
    <div class="page">
      <p style="text-align:center; color:#999; margin-top:40px;">ไม่มีรายการอุปกรณ์</p>
    </div>
  @endforelse

</body>

</html>
