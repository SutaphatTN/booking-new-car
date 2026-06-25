<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <title>ใบจอง</title>
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

    @page {
      size: A4 portrait;
      margin: 0;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html,
    body {
      font-family: 'THSarabunNew', DejaVu Sans, sans-serif;
      font-size: 14pt;
      color: #1a1a2e;
      line-height: 1.15;
    }

    /* ใช้ padding ที่ body แทน @page margin (dompdf respect ได้ชัวร์กว่า) */
    body {
      padding: 18mm 20mm;
    }

    /* ══ COMPANY HEADER ══ */
    .company-header {
      display: table;
      width: 100%;
      border-bottom: 2px solid #000;
      padding-bottom: 5px;
      margin-bottom: 6px;
    }

    .company-logo-cell {
      display: table-cell;
      width: 1%;
      white-space: nowrap;
      vertical-align: middle;
      padding-right: 14px;
    }

    /* กำหนดสูงอย่างเดียว ปล่อยกว้าง auto — dompdf ไม่รองรับ object-fit
       ห้ามใส่ max-width เพราะจะหนีบความกว้างจนภาพถูกบีบ (โดยเฉพาะโลโก้แนวนอน) */
    .company-logo {
      height: 54px;
      width: auto;
    }

    .company-info-cell {
      display: table-cell;
      vertical-align: middle;
      padding-left: 8px;
    }

    .company-name {
      font-size: 17pt;
      font-weight: bold;
    }

    .company-line {
      font-size: 13pt;
      color: #222;
    }

    /* ══ TITLE ══ */
    .doc-title {
      text-align: center;
      margin: 4px 0 8px;
    }

    .doc-title .th {
      font-size: 18pt;
      font-weight: bold;
      letter-spacing: .05em;
    }

    .doc-title .en {
      font-size: 12pt;
      color: #555;
      text-transform: uppercase;
      letter-spacing: .08em;
    }

    .booking-date {
      text-align: right;
      font-size: 13pt;
      font-weight: bold;
      margin-bottom: 4px;
    }

    /* ══ SECTION ══ */
    .sec {
      border: 1.2px solid #888;
      border-top: 2px solid #444;
      margin-bottom: 6px;
    }

    .sec-title {
      font-size: 13pt;
      font-weight: bold;
      padding: 2px 8px;
      background: #e2e2e2;
      border-bottom: 1px solid #bbb;
      letter-spacing: .05em;
    }

    /* ══ FIELD GRID (two columns) ══ */
    .grid {
      display: table;
      width: 100%;
      table-layout: fixed;
    }

    .f {
      display: table;
      width: 100%;
      border-bottom: 1px dotted #ccc;
      padding: 2px 0;
    }

    .f.last {
      border-bottom: none;
    }

    .fl {
      display: table-cell;
      width: 38%;
      font-weight: bold;
      color: #222;
      padding: 0 4px 0 8px;
      white-space: nowrap;
    }

    .fv {
      display: table-cell;
      color: #000;
      padding-right: 8px;
    }

    .col2 {
      display: table-cell;
      width: 50%;
      vertical-align: top;
    }

    .col2.left {
      border-right: 1px solid #ddd;
    }

    /* ══ SIGNATURES ══ */
    .sig {
      display: table;
      width: 100%;
      table-layout: fixed;
      margin-top: 28px;
    }

    .sig-block {
      display: table-cell;
      width: 33.33%;
      text-align: center;
      padding: 0 10px;
      vertical-align: top;
    }

    .sig-line {
      border-bottom: 1px dotted #000;
      height: 26px;
      margin: 0 6px 4px;
    }

    .sig-label {
      font-size: 13pt;
      font-weight: bold;
    }

    .sig-name {
      font-size: 13pt;
      margin-top: 2px;
    }

    /* ══ FOOTER ══ (อยู่ใต้คอลัมน์ผู้สั่งจอง) */
    .print-footer {
      margin-top: 14px;
      text-align: center;
      font-size: 12pt;
      color: #444;
    }
  </style>
</head>

@php
  $c = $saleCar->customer;
  $prefix = $c?->prefix?->Name_TH ?? '';
  $fullName = trim($prefix . ' ' . ($c?->FirstName ?? '') . ' ' . ($c?->LastName ?? ''));
  $isFinance = ($saleCar->payment_mode ?? null) === 'finance';
  $modelName = $saleCar->subModel?->name ?? ($saleCar->model?->Name_TH ?? '');
  $saleName = $saleCar->saleUser?->name ?? '';
@endphp

<body>

  {{-- ══ COMPANY HEADER ══ --}}
  <div class="company-header">
    <div class="company-logo-cell">
      <img src="{{ public_path($company['logo'] ?? 'assets/img/Wuling_logo.png') }}" class="company-logo" alt="logo">
    </div>
    <div class="company-info-cell">
      <div class="company-name">{{ $company['name'] ?? '' }}</div>
      <div class="company-line">{{ $company['address'] ?? '' }}</div>
      <div class="company-line">โทร. {{ $company['phone'] ?? '' }}</div>
    </div>
  </div>

  {{-- ══ TITLE ══ --}}
  <div class="doc-title">
    <div class="th">ใบจองรถ</div>
    <div class="en">Vehicle Booking Form</div>
  </div>

  <div class="booking-date">วันที่จอง : {{ $saleCar->format_booking_date ?? '' }}</div>

  {{-- ══ ข้อมูลผู้จอง ══ --}}
  <div class="sec">
    <div class="sec-title">ข้อมูลผู้จอง</div>
    <div class="grid">
      <div class="col2 left">
        <div class="f">
          <div class="fl">ชื่อผู้จอง</div>
          <div class="fv">{{ $fullName }}</div>
        </div>
        <div class="f">
          <div class="fl">เพศ</div>
          <div class="fv">{{ $c?->gender_th && $c->gender_th !== '-' ? $c->gender_th : '' }}</div>
        </div>
        <div class="f">
          <div class="fl">วันเกิด</div>
          <div class="fv">{{ optional($c?->Birthday)->format('d/m/Y') ?? '' }}</div>
        </div>
        <div class="f last">
          <div class="fl">อาชีพ</div>
          <div class="fv">{{ $c?->career ?? '' }}</div>
        </div>
      </div>
      <div class="col2">
        <div class="f">
          <div class="fl">เบอร์โทรศัพท์</div>
          <div class="fv">{{ $c?->formatted_mobile ?? '' }}</div>
        </div>
        <div class="f">
          <div class="fl">เลขบัตรประชาชน</div>
          <div class="fv">{{ $c?->formatted_id_number ?? '' }}</div>
        </div>
        <div class="f last">
          <div class="fl">ชื่อพนักงานขาย</div>
          <div class="fv">{{ $saleName }}</div>
        </div>
      </div>
    </div>
  </div>

  {{-- ══ ที่อยู่ ══ --}}
  <div class="sec">
    <div class="sec-title">ที่อยู่</div>
    <div class="f">
      <div class="fl">ที่อยู่ตามทะเบียนบ้าน</div>
      <div class="fv">{{ $c?->currentAddress?->short_address ?? '' }}</div>
    </div>
    <div class="f last">
      <div class="fl">ที่อยู่ส่งเอกสาร</div>
      <div class="fv">{{ $c?->documentAddress?->short_address ?? '' }}</div>
    </div>
  </div>

  {{-- ══ ข้อมูลรถ & การชำระเงิน ══ --}}
  <div class="sec">
    <div class="sec-title">ข้อมูลรถและการชำระเงิน</div>
    <div class="grid">
      <div class="col2 left">
        <div class="f">
          <div class="fl">เงื่อนไขการจ่าย</div>
          <div class="fv">{{ $isFinance ? 'ผ่อนชำระ (ไฟแนนซ์)' : 'เงินสด' }}</div>
        </div>
        <div class="f">
          <div class="fl">รุ่นรถหลัก</div>
          <div class="fv">{{ $saleCar->model?->Name_TH ?? '' }}</div>
        </div>
        <div class="f">
          <div class="fl">รุ่นย่อย</div>
          <div class="fv">{{ $saleCar->subModel?->detail ?? '' }}</div>
        </div>
        <div class="f last">
          <div class="fl">สีรถ</div>
          <div class="fv">{{ $saleCar->displayColor ?? ($saleCar->Color ?? '') }}</div>
        </div>
      </div>
      <div class="col2">
        @if (auth()->user()->brand == 1)
          <div class="f">
            <div class="fl">Option</div>
            <div class="fv">{{ $saleCar->option ?? '' }}</div>
          </div>
        @elseif (auth()->user()->brand == 2)
          <div class="f">
            <div class="fl">สีภายใน</div>
            <div class="fv">{{ $saleCar->interiorColor?->name ?? '' }}</div>
          </div>
        @endif
        <div class="f">
          <div class="fl">ราคารถ</div>
          <div class="fv">{{ is_numeric($saleCar->price_sub ?? null) ? number_format($saleCar->price_sub, 2) . ' บาท' : '' }}</div>
        </div>
        <div class="f last">
          <div class="fl">เงินจอง</div>
          <div class="fv">{{ is_numeric($saleCar->reservationPayment?->cost ?? null) ? number_format($saleCar->reservationPayment->cost, 2) . ' บาท' : '' }}</div>
        </div>
      </div>
    </div>
  </div>

  {{-- ══ SIGNATURES ══ --}}
  @php $printNow = now(); @endphp
  <div class="sig">
    <div class="sig-block">
      <div class="sig-line"></div>
      <div class="sig-label">พนักงานขาย</div>
      <div class="sig-name">( {{ $saleName }} )</div>
    </div>
    <div class="sig-block">
      <div class="sig-line"></div>
      <div class="sig-label">ผู้จัดการแผนกขาย</div>
    </div>
    <div class="sig-block">
      <div class="sig-line"></div>
      <div class="sig-label">ผู้สั่งจอง / ผู้เช่าซื้อ</div>
      {{-- วันที่พิมพ์ อยู่ใต้คอลัมน์ขวา --}}
      <div class="print-footer">
        วันที่พิมพ์ : {{ $printNow->format('d/m/Y') }} {{ $printNow->format('H.i') }}
      </div>
    </div>
  </div>

</body>

</html>
