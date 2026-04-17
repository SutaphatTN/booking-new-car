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
    }

    @font-face {
      font-family: 'THSarabunNew';
      src: url("{{ public_path('fonts/THSarabunNew-Bold.ttf') }}") format('truetype');
      font-weight: bold;
    }

    @page {
      size: A4 portrait;
      margin: 8mm 0;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    html,
    body {
      font-family: 'THSarabunNew', DejaVu Sans, sans-serif;
      font-size: 7.5pt;
      color: #1a1a2e;
      line-height: 1.2;
      padding: 0 8mm;
    }

    /* ══ TOP ACCENT BAR ══ */
    .page-bar {
      height: 5px;
      /* background: #000; */
      margin-bottom: 4px;
    }

    /* ══ HEADER ══ */
    .page-header {
      width: 100%;
      margin-bottom: 5px;
    }

    .page-header-top {
      display: table;
      width: 100%;
      border-bottom: 1.5px solid #000;
      padding-bottom: 3px;
      margin-bottom: 3px;
    }

    .page-header-top-cell {
      display: table-cell;
      vertical-align: middle;
      width: 33.33%;
    }

    .page-header-top-cell.mid {
      text-align: center;
    }

    .page-header-top-cell.right {
      text-align: right;
    }

    .h-subtitle {
      font-size: 6.5pt;
      color: #555;
      display: block;
      text-transform: uppercase;
      letter-spacing: .04em;
    }

    .h-value {
      font-size: 8.5pt;
      font-weight: bold;
      color: #000;
      display: block;
    }

    .h-main {
      font-size: 14pt;
      font-weight: bold;
      display: block;
      letter-spacing: .05em;
      color: #000;
    }

    .h-sub {
      font-size: 7.5pt;
      color: #444;
      display: block;
    }

    /* ══ BODY COLUMNS ══ */
    .two-col {
      display: table;
      width: 100%;
      table-layout: fixed;
    }

    .col-l {
      display: table-cell;
      vertical-align: top;
      width: 50%;
      padding-right: 3px;
    }

    .col-r {
      display: table-cell;
      vertical-align: top;
      width: 50%;
      padding-left: 3px;
    }

    /* ══ SECTION ══ */
    .sec {
      margin-bottom: 4px;
      border: 1.5px solid #999;
      border-top: 2.5px solid #444;
    }

    /* Light-grey card header */
    .sec-title {
      font-size: 7pt;
      font-weight: bold;
      padding: 2.5px 8px 2.5px 10px;
      background: #e0e0e0;
      color: #1a1a1a;
      letter-spacing: .07em;
      text-transform: uppercase;
      border-bottom: 1px solid #bbb;
    }

    .sec-body {
      padding: 2px 7px;
      background: #fff;
    }

    .sec-no-data {
      font-size: 7pt;
      text-align: center;
      padding: 4px 0;
      color: #777;
    }

    /* ══ FIELD ROWS ══ */
    .f {
      display: table;
      width: 100%;
      padding: 1.5px 0;
      border-bottom: 1px dotted #ccc;
    }

    .f:last-child {
      border-bottom: none;
    }

    /* Zebra shading */
    .f:nth-child(even) {
      background: #f5f5f5;
    }

    .fl {
      display: table-cell;
      width: 46%;
      font-weight: bold;
      font-size: 7pt;
      color: #222;
      padding-right: 4px;
      white-space: nowrap;
    }

    .fv {
      display: table-cell;
      font-size: 7pt;
      color: #000;
    }

    /* ══ DIVIDER ══ */
    .col-divider {
      width: 1px;
      background: #ccc;
      display: table-cell;
    }

    /* ══ TABLES ══ */
    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 6.5pt;
    }

    th {
      background: #d0d0d0;
      color: #1a1a1a;
      border: 1px solid #999;
      padding: 2px 3px;
      text-align: center;
      font-weight: bold;
    }

    td {
      border: 1px solid #bbb;
      padding: 1.5px 3px;
      vertical-align: middle;
    }

    tr:nth-child(even) td {
      background: #f5f5f5;
    }

    tr.total-row td {
      background: #ddd !important;
      font-weight: bold;
      border-top: 1.5px solid #000;
    }

    .no-data {
      text-align: center;
      font-size: 7pt;
      padding: 5px 0;
      color: #777;
      font-style: italic;
    }
  </style>
</head>

<body>

  {{-- ══ ACCENT BAR ══ --}}
  <div class="page-bar"></div>

  {{-- ══ PAGE HEADER ══ --}}
  <div class="page-header">
    <div class="page-header-top">
      <div class="page-header-top-cell">
        <span class="h-subtitle">วันที่จอง</span>
        <span class="h-value">{{ $saleCar->format_booking_date_sum ?? '-' }}</span>
      </div>
      <div class="page-header-top-cell mid">
        <span class="h-sub">สรุปรายละเอียดการขาย</span>
        <span class="h-main">Purchase Summary</span>
      </div>
      <div class="page-header-top-cell right">
        <span class="h-subtitle">วันที่ออกรถ / จังหวัด</span>
        <span class="h-value">{{ $saleCar->format_delivery_date_sum ?? '-' }}&ensp;|&ensp;{{ $saleCar->provinces->name ?? '-' }}</span>
      </div>
    </div>
  </div>

  {{-- ══ BODY ══ --}}
  <div class="two-col">

    {{-- ════ LEFT ════ --}}
    <div class="col-l">

      {{-- ลูกค้า --}}
      <div class="sec">
        <div class="sec-title">ข้อมูลลูกค้า</div>
        <div class="sec-body">
          <div class="f">
            <div class="fl">ชื่อ-นามสกุล</div>
            <div class="fv">{{ $saleCar->customer->prefix->Name_TH ?? '' }}
              {{ $saleCar->customer->FirstName ?? '' }} {{ $saleCar->customer->LastName ?? '' }}</div>
          </div>

          <div class="f">
            <div class="fl">ชื่อฝ่ายขาย</div>
            <div class="fv">{{ $saleCar->saleUser?->name ?? '-' }}</div>
          </div>
          
          <div class="f">
            <div class="fl">ที่อยู่ปัจจุบัน</div>
            <div class="fv">{{ $saleCar->customer->currentAddress->short_address ?? '-' }}</div>
          </div>
          <div class="f">
            <div class="fl">ที่อยู่ส่งเอกสาร</div>
            <div class="fv">{{ $saleCar->customer->documentAddress->short_address ?? '-' }}</div>
          </div>
          <div class="f">
            <div class="fl">เบอร์มือถือ</div>
            <div class="fv">{{ $saleCar->customer->formatted_mobile ?? '-' }}</div>
          </div>
        </div>
      </div>

      {{-- ข้อมูลการขาย --}}
      <div class="sec">
        <div class="sec-title">ข้อมูลการขาย</div>
        <div class="sec-body">
          <div class="f">
            <div class="fl">รุ่นรถ</div>
            <div class="fv">
              {{ $saleCar->subModel->name ?? '-' }}
            </div>
          </div>
          @if (auth()->user()->brand == 1)
            <div class="f">
              <div class="fl">Option</div>
              <div class="fv">{{ $saleCar->option ?? '-' }}</div>
            </div>
          @endif
          <div class="f">
            <div class="fl">Vin / เลขถัง</div>
            <div class="fv">{{ $saleCar->carOrder?->vin_number ?? '-' }} /
              {{ $saleCar->carOrder?->engine_number ?? '-' }}</div>
          </div>

          @if (auth()->user()->brand == 2)
            <div class="f">
              <div class="fl">สีรถ</div>
              <div class="fv">{{ $saleCar->gwmColor->name ?? '-' }}</div>
            </div>
            <div class="f">
              <div class="fl">สีภายในรถ</div>
              <div class="fv">{{ $saleCar->interiorColor->name ?? '-' }}</div>
            </div>
          @elseif (auth()->user()->brand == 3)
            <div class="f">
              <div class="fl">สีรถ</div>
              <div class="fv">{{ $saleCar->gwmColor->name ?? '-' }}</div>
            </div>
          @else
            <div class="f">
              <div class="fl">สีรถ</div>
              <div class="fv">{{ $saleCar->Color ?? '-' }}</div>
            </div>
          @endif

          @if ($saleCar->remainingPayment?->type === 'finance')
            <div class="f">
              <div class="fl">ราคาเงินสด (รวมบวกหัว)</div>
              <div class="fv">{{ number_format($saleCar->CarSalePriceFinal ?? 0, 2) }} บาท</div>
            </div>
            <div class="f">
              <div class="fl">เงินดาวน์ / เปอร์เซ็นต์</div>
              <div class="fv">
                {{ is_numeric($saleCar->DownPayment ?? null) ? number_format($saleCar->DownPayment, 2) . ' บาท' : '-' }}
                /
                {{ is_numeric($saleCar->DownPaymentPercentage ?? null) ? number_format($saleCar->DownPaymentPercentage, 2) . '%' : '-' }}
              </div>
            </div>
            <div class="f">
              <div class="fl">ส่วนลดเงินดาวน์</div>
              <div class="fv">
                {{ is_numeric($saleCar->DownPaymentDiscount ?? null) ? number_format($saleCar->DownPaymentDiscount, 2) . ' บาท' : '-' }}
              </div>
            </div>
          @else
            <div class="f">
              <div class="fl">ราคาเงินสด</div>
              <div class="fv">{{ number_format($saleCar->CarSalePriceFinal ?? 0, 2) }} บาท</div>
            </div>
            <div class="f">
              <div class="fl">ส่วนลด</div>
              <div class="fv">
                {{ is_numeric($saleCar->PaymentDiscount ?? null) ? number_format($saleCar->PaymentDiscount, 2) . ' บาท' : '-' }}
              </div>
            </div>
          @endif

          <div class="f">
            <div class="fl">เงินจอง</div>
            <div class="fv">
              {{ is_numeric($saleCar->reservationPayment->cost ?? null) ? number_format($saleCar->reservationPayment->cost, 2) . ' บาท' : '-' }}
            </div>
          </div>
          <div class="f">
            <div class="fl">หักราคารถเทิร์น</div>
            <div class="fv">
              {{ is_numeric($saleCar->turnCar->cost_turn ?? null) ? number_format($saleCar->turnCar->cost_turn, 2) . ' บาท' : '-' }}
            </div>
          </div>
          <div class="f">
            <div class="fl">ลูกค้าจ่ายเพิ่ม</div>
            <div class="fv">
              {{ is_numeric($saleCar->TotalAccessoryExtra ?? null) ? number_format($saleCar->TotalAccessoryExtra, 2) . ' บาท' : '-' }}
            </div>
          </div>

          @if ($saleCar->remainingPayment?->type === 'finance')
            <div class="f">
              <div class="fl">ส่วนลดราคารถ / Vat ซื้อเพิ่ม</div>
              <div class="fv">
                {{ is_numeric($saleCar->discount ?? null) ? number_format($saleCar->discount, 2) . ' บาท' : '-' }} /
                {{ is_numeric($saleCar->AccessoryExtraVat ?? null) ? number_format($saleCar->AccessoryExtraVat, 2) . ' บาท' : '-' }}
              </div>
            </div>
            <div class="f">
              <div class="fl">ค่าใช้จ่ายอื่นๆ</div>
              <div class="fv">
                {{ is_numeric($saleCar->other_cost_fi ?? null) ? number_format($saleCar->other_cost_fi, 2) . ' บาท' : '-' }}
              </div>
            </div>
            <div class="f">
              <div class="fl">หมายเหตุ ค่าใช้จ่ายอื่นๆ</div>
              <div class="fv">{{ $saleCar->reason_other_cost_fi ?? '-' }}</div>
            </div>
            <div class="f">
              <div class="fl">สรุปค่าใช้จ่ายวันออกรถ</div>
              <div class="fv">
                {{ is_numeric($saleCar->TotalPaymentatDelivery ?? null) ? number_format($saleCar->TotalPaymentatDelivery, 2) . ' บาท' : '-' }}
              </div>
            </div>
            <div class="f">
              <div class="fl">ไฟแนนซ์</div>
              <div class="fv">
                {{ $saleCar->remainingPayment && $saleCar->remainingPayment->financeInfo ? $saleCar->remainingPayment->financeInfo->FinanceCompany : '-' }}
              </div>
            </div>
            <div class="f">
              <div class="fl">ยอดจัด</div>
              <div class="fv">
                {{ is_numeric($saleCar->remainingPayment?->cost ?? null) ? number_format($saleCar->remainingPayment->cost, 2) . ' บาท' : '-' }}
              </div>
            </div>
            <div class="f">
              <div class="fl">ดอกเบี้ย / งวดผ่อน</div>
              <div class="fv">{{ $saleCar->remainingPayment?->interest ?? '-' }}% /
                {{ $saleCar->remainingPayment?->period ?? '-' }} เดือน</div>
            </div>
            <div class="f">
              <div class="fl">ค่างวด (ไม่มี ALP)</div>
              <div class="fv">
                {{ is_numeric($saleCar->remainingPayment?->alp ?? null) ? number_format($saleCar->remainingPayment->alp, 2) . ' บาท/เดือน' : '-' }}
              </div>
            </div>
            <div class="f">
              <div class="fl">ค่างวด (รวม ALP)</div>
              <div class="fv">
                {{ is_numeric($saleCar->remainingPayment?->including_alp ?? null) ? number_format($saleCar->remainingPayment->including_alp, 2) . ' บาท/เดือน' : '-' }}
              </div>
            </div>
            <div class="f">
              <div class="fl">ยอด ALP หักจากใบเสร็จดาวน์</div>
              <div class="fv">
                {{ is_numeric($saleCar->remainingPayment?->total_alp ?? null) ? number_format($saleCar->remainingPayment->total_alp, 2) . ' บาท' : '-' }}
              </div>
            </div>
            <div class="f">
              <div class="fl">ดอกเบี้ยคอม / ยอดเงินค่าคอม</div>
              <div class="fv">C{{ $saleCar->remainingPayment?->type_com ?? '-' }} /
                {{ is_numeric($saleCar->remainingPayment?->total_com ?? null) ? number_format($saleCar->remainingPayment->total_com, 2) . ' บาท' : '-' }}
              </div>
            </div>
          @else
            <div class="f">
              <div class="fl">ส่วนลด</div>
              <div class="fv">
                {{ is_numeric($saleCar->PaymentDiscount ?? null) ? number_format($saleCar->PaymentDiscount, 2) . ' บาท' : '-' }}
              </div>
            </div>
            <div class="f">
              <div class="fl">ค่าใช้จ่ายอื่นๆ</div>
              <div class="fv">
                {{ is_numeric($saleCar->other_cost ?? null) ? number_format($saleCar->other_cost, 2) . ' บาท' : '-' }}
              </div>
            </div>
            <div class="f">
              <div class="fl">หมายเหตุ ค่าใช้จ่ายอื่นๆ</div>
              <div class="fv">{{ $saleCar->reason_other_cost ?? '-' }}</div>
            </div>
            <div class="f">
              <div class="fl">สรุปค่าใช้จ่ายวันออกรถ</div>
              <div class="fv">
                {{ is_numeric($saleCar->remainingPayment?->cost ?? null) ? number_format($saleCar->remainingPayment->cost, 2) . ' บาท' : '-' }}
              </div>
            </div>
          @endif
        </div>
      </div>

      {{-- แนะนำ --}}
      <div class="sec">
        <div class="sec-title">แนะนำ</div>
        <div class="sec-body">
          <div class="f">
            <div class="fl">ผู้แนะนำ</div>
            <div class="fv">{{ $saleCar->customerReferrer->formatted_id_number ?? '-' }}</div>
          </div>
          <div class="f">
            <div class="fl">ยอดเงินค่าแนะนำ</div>
            <div class="fv">
              {{ is_numeric($saleCar->ReferrerAmount ?? null) ? number_format($saleCar->ReferrerAmount, 2) . ' บาท' : '-' }}
            </div>
          </div>
        </div>
      </div>

      {{-- แคมเปญ --}}
      <div class="sec">
        <div class="sec-title">แคมเปญ</div>
        <div class="sec-body">
          <div class="f">
            <div class="fl">แคมเปญ</div>
            <div class="fv">
              @if ($saleCar->campaigns && $saleCar->campaigns->count() > 0)
                @foreach ($saleCar->campaigns as $camp)
                  ({{ $camp->campaign->type->name ?? '-' }})
                  {{ $camp->campaign->appellation->name ?? '-' }} -
                  {{ number_format($camp->campaign->cashSupport_final, 2) }}@if (!$loop->last)
                    +
                  @endif
                @endforeach
              @else
                -
              @endif
            </div>
          </div>

          @if ($saleCar->remainingPayment?->type === 'finance')
            @php
              $totalSaleCampaign = $saleCar->TotalSaleCampaign ?? 0;
              $markup90 = $saleCar->Markup90 ?? 0;
              $kickback = $saleCar->kickback ?? 0;
              $campaignBalance = $totalSaleCampaign + $markup90 + $kickback;
              $DownPaymentDiscount = $saleCar->DownPaymentDiscount ?? 0;
              $disC = $saleCar->discount ?? 0;
              $TotalAccessoryGift = $saleCar->TotalAccessoryGift ?? 0;
              $refA = $saleCar->ReferrerAmount ?? 0;
              $vatGift = $saleCar->AccessoryGiftVat ?? 0;
              $discountBalance = $DownPaymentDiscount + $TotalAccessoryGift + $refA + $vatGift + $disC;
              $balanceCam2 = $saleCar->balanceCampaign * 2;
              $balanceCamHalf = $saleCar->balanceCampaign ?? null;
              $perBudgetHalf = $saleCar->model->per_budget ?? 0;
              $isNegativeHalf = is_numeric($balanceCamHalf) && $balanceCamHalf < 0;
              $displayBalanceCamHalf = $isNegativeHalf ? $balanceCamHalf * 2 * ($perBudgetHalf / 100) : $balanceCamHalf;
            @endphp
            <div class="f">
              <div class="fl">งบ</div>
              <div class="fv">
                {{ is_numeric($saleCar->TotalSaleCampaign ?? null) ? number_format($saleCar->TotalSaleCampaign, 2) . ' บาท' : '-' }}
              </div>
            </div>
            <div class="f">
              <div class="fl">บวกหัว 90% / Kick Back</div>
              <div class="fv">
                {{ is_numeric($saleCar->Markup90 ?? null) ? number_format($saleCar->Markup90, 2) . ' บาท' : '-' }} /
                {{ is_numeric($saleCar->kickback ?? null) ? number_format($saleCar->kickback, 2) . ' บาท' : '-' }}</div>
            </div>
            <div class="f">
              <div class="fl">รวม</div>
              <div class="fv">{{ is_numeric($campaignBalance) ? number_format($campaignBalance, 2) . ' บาท' : '-' }}
              </div>
            </div>
            <div class="f">
              <div class="fl">ส่วนลดดาวน์ / Vat ของแถม</div>
              <div class="fv">
                {{ is_numeric($saleCar->DownPaymentDiscount ?? null) ? number_format($saleCar->DownPaymentDiscount, 2) . ' บาท' : '-' }}
                /
                {{ is_numeric($saleCar->AccessoryGiftVat ?? null) ? number_format($saleCar->AccessoryGiftVat, 2) . ' บาท' : '-' }}
              </div>
            </div>
            <div class="f">
              <div class="fl">ส่วนลดราคารถ</div>
              <div class="fv">
                {{ is_numeric($saleCar->discount ?? null) ? number_format($saleCar->discount, 2) . ' บาท' : '-' }}</div>
            </div>
            <div class="f">
              <div class="fl">ส่วนต่างของแถม</div>
              <div class="fv">
                {{ is_numeric($saleCar->TotalAccessoryGift ?? null) ? number_format($saleCar->TotalAccessoryGift, 2) . ' บาท' : '-' }}
              </div>
            </div>
            <div class="f">
              <div class="fl">รายการที่ใช้ไป</div>
              <div class="fv">{{ is_numeric($discountBalance) ? number_format($discountBalance, 2) . ' บาท' : '-' }}
              </div>
            </div>
            <div class="f">
              <div class="fl">{{ is_numeric($balanceCam2) && $balanceCam2 < 0 ? 'เกินงบ' : 'เหลืองบ' }}</div>
              <div class="fv">{{ is_numeric($balanceCam2) ? number_format($balanceCam2, 2) . ' บาท' : '-' }}</div>
            </div>
            <div class="f">
              <div class="fl">{{ $isNegativeHalf ? 'หักคอม' : 'เหลืองบ (แบ่ง 2 ส่วน)' }}</div>
              <div class="fv">
                {{ is_numeric($displayBalanceCamHalf) ? number_format($displayBalanceCamHalf, 2) . ' บาท' : '-' }}</div>
            </div>
          @else
            @php
              $PaymentDiscount = $saleCar->PaymentDiscount ?? 0;
              $TotalAccessoryGift = $saleCar->TotalAccessoryGift ?? 0;
              $discountBalance = $PaymentDiscount + $TotalAccessoryGift;
              $balanceCash = $saleCar->balanceCampaign * 2;
              $balanceCamHalf2 = $saleCar->balanceCampaign ?? null;
              $perBudgetHalf2 = $saleCar->model->per_budget ?? 0;
              $isNegativeHalf2 = is_numeric($balanceCamHalf2) && $balanceCamHalf2 < 0;
              $displayBalanceCamHalf2 = $isNegativeHalf2
                  ? $balanceCamHalf2 * 2 * ($perBudgetHalf2 / 100)
                  : $balanceCamHalf2;
            @endphp
            <div class="f">
              <div class="fl">รวม</div>
              <div class="fv">
                {{ is_numeric($saleCar->TotalSaleCampaign ?? null) ? number_format($saleCar->TotalSaleCampaign, 2) . ' บาท' : '-' }}
              </div>
            </div>
            <div class="f">
              <div class="fl">ส่วนลด / ส่วนต่างของแถม</div>
              <div class="fv">
                {{ is_numeric($saleCar->PaymentDiscount ?? null) ? number_format($saleCar->PaymentDiscount, 2) . ' บาท' : '-' }}
                /
                {{ is_numeric($saleCar->TotalAccessoryGift ?? null) ? number_format($saleCar->TotalAccessoryGift, 2) . ' บาท' : '-' }}
              </div>
            </div>
            <div class="f">
              <div class="fl">รวมส่วนลด</div>
              <div class="fv">{{ is_numeric($discountBalance) ? number_format($discountBalance, 2) . ' บาท' : '-' }}
              </div>
            </div>
            <div class="f">
              <div class="fl">{{ is_numeric($balanceCash) && $balanceCash < 0 ? 'เกินงบ' : 'เหลืองบ' }}</div>
              <div class="fv">{{ is_numeric($balanceCash) ? number_format($balanceCash, 2) . ' บาท' : '-' }}</div>
            </div>
            <div class="f">
              <div class="fl">{{ $isNegativeHalf2 ? 'หักคอม' : 'เหลืองบ (แบ่ง 2 ส่วน)' }}</div>
              <div class="fv">
                {{ is_numeric($displayBalanceCamHalf2) ? number_format($displayBalanceCamHalf2, 2) . ' บาท' : '-' }}
              </div>
            </div>
          @endif
        </div>
      </div>

    </div>{{-- /col-l --}}

    {{-- ════ RIGHT ════ --}}
    <div class="col-r">

      {{-- ของแถม --}}
      <div class="sec">
        <div class="sec-title">อุปกรณ์ตกแต่ง (แถม)</div>
        @php $giftAccessories = $saleCar->accessories->where('pivot.type', 'gift'); @endphp
        @if ($giftAccessories->isNotEmpty())
          <div class="sec-body" style="padding:3px 5px;">
            <table>
              <thead>
                <tr>
                  <th style="width:18px">#</th>
                  <th style="width:42px">รหัส</th>
                  <th>รายละเอียด</th>
                  <th style="width:55px">ราคา</th>
                  <th style="width:42px">Com</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($giftAccessories as $giftAcc)
                  <tr>
                    <td style="text-align:center">{{ $loop->iteration }}</td>
                    <td style="text-align:center">{{ $giftAcc->accessory_id ?? '-' }}</td>
                    <td>{{ $giftAcc->detail ?? '-' }}</td>
                    <td style="text-align:right">{{ number_format($giftAcc->pivot->price, 2) }}</td>
                    <td style="text-align:right">{{ number_format($giftAcc->pivot->commission, 2) }}</td>
                  </tr>
                @endforeach
                <tr class="total-row">
                  <td colspan="3" style="text-align:center">รวมทั้งหมด</td>
                  <td style="text-align:right">{{ number_format($saleCar->TotalAccessoryGift ?? 0, 2) }}</td>
                  <td style="text-align:right">{{ number_format($saleCar->AccessoryGiftCom ?? 0, 2) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        @else
          <p class="sec-no-data">— ไม่มีรายการของแถม —</p>
        @endif
      </div>

      {{-- ซื้อเพิ่ม --}}
      <div class="sec">
        <div class="sec-title">รายการซื้อเพิ่ม</div>
        @php $extraAccessories = $saleCar->accessories->where('pivot.type', 'extra'); @endphp
        @if ($extraAccessories->isNotEmpty())
          <div class="sec-body" style="padding:3px 5px;">
            <table>
              <thead>
                <tr>
                  <th style="width:18px">#</th>
                  <th style="width:42px">รหัส</th>
                  <th>รายละเอียด</th>
                  <th style="width:55px">ราคา</th>
                  <th style="width:42px">Com</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($extraAccessories as $extraAcc)
                  <tr>
                    <td style="text-align:center">{{ $loop->iteration }}</td>
                    <td style="text-align:center">{{ $extraAcc->accessory_id ?? '-' }}</td>
                    <td>{{ $extraAcc->detail ?? '-' }}</td>
                    <td style="text-align:right">{{ number_format($extraAcc->pivot->price, 2) }}</td>
                    <td style="text-align:right">{{ number_format($extraAcc->pivot->commission, 2) }}</td>
                  </tr>
                @endforeach
                <tr class="total-row">
                  <td colspan="3" style="text-align:center">รวมทั้งหมด</td>
                  <td style="text-align:right">{{ number_format($saleCar->TotalAccessoryExtra ?? 0, 2) }}</td>
                  <td style="text-align:right">{{ number_format($saleCar->AccessoryExtraCom ?? 0, 2) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        @else
          <p class="sec-no-data">— ไม่มีรายการซื้อเพิ่ม —</p>
        @endif
      </div>

      {{-- วันส่งมอบ --}}
      <div class="sec">
        <div class="sec-title">ข้อมูลวันส่งมอบ</div>
        <div class="sec-body">
          <div class="f">
            <div class="fl">วันที่ส่งเอกสารสรุปการขาย</div>
            <div class="fv">{{ $saleCar->format_key_date ?? '-' }}</div>
          </div>
          <div class="f">
            <div class="fl">วันส่งมอบจริง (แจ้งประกัน)</div>
            <div class="fv">{{ $saleCar->format_delivery_date ?? '-' }}</div>
          </div>
          <div class="f">
            <div class="fl">วันที่ส่งมอบของบริษัท</div>
            <div class="fv">{{ $saleCar->format_dms_date ?? '-' }}</div>
          </div>
          <div class="f">
            <div class="fl">วันที่ส่งมอบของฝ่ายขาย</div>
            <div class="fv">{{ $saleCar->format_ck_date ?? '-' }}</div>
          </div>
        </div>
      </div>

    </div>{{-- /col-r --}}

  </div>{{-- /two-col --}}

</body>

</html>
