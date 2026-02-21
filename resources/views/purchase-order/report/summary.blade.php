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
      font-size: 15pt;
      margin: 0px;
      line-height: 0.9;
    }

    .center-text {
      text-align: center;
      margin-bottom: 8px;
      font-size: 20px;
      font-weight: bold;
    }

    .two-column {
      width: 100%;
      display: table;
      margin-top: 2px;
    }

    .col-left,
    .col-right {
      display: table-cell;
      vertical-align: top;
      width: 50%;
      padding: 3px;
    }

    .label {
      display: block;
      margin-bottom: 2px;
      font-weight: bold;
    }

    .section-title {
      font-weight: bold;
      margin-top: 0px;
      margin-bottom: 0px;
      font-size: 14pt;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    td {
      border: 1px solid #ccc;
      vertical-align: top;
      border: 1px solid #000;
    }

    th {
      border: 1px solid #000;
      background-color: #f2f2f2;
      text-align: center;
    }
  </style>
</head>

<body>
  <div class="center-text">
    รายละเอียดวันที่จอง : {{ $saleCar->format_booking_date ?? '-' }}
    &nbsp;&nbsp;&nbsp;
    จังหวัดที่ขึ้นทะเบียน : {{ $saleCar->provinces->name ?? '-' }}
  </div>

  <div class="two-column">

    <!-- LEFT -->
    <div class="col-left">
      <span class="label">
        จองชื่อ :
        <span style="font-weight: normal;">
          {{ $saleCar->customer->prefix->Name_TH ?? '' }}
          {{ $saleCar->customer->FirstName ?? '' }}
          {{ $saleCar->customer->LastName ?? '' }}
        </span>
      </span>

      <span class="label">
        ที่อยู่ปัจจุบัน :
        <span style="font-weight: normal;">
          {{ $saleCar->customer->currentAddress->full_address ?? '-' }}
        </span>
      </span>

      <span class="label">
        ที่อยู่สำหรับส่งเอกสาร :
        <span style="font-weight: normal;">
          {{ $saleCar->customer->documentAddress->full_address ?? '-' }}
        </span>
      </span>

      <span class="label">
        เบอร์โทรศัพท์ :
        <span style="font-weight: normal;">
          {{ $saleCar->customer->formatted_mobile ?? '-' }}
        </span>
      </span>

      <span class="label">
        รุ่นรถ :
        <span style="font-weight: normal;">
          {{ $saleCar->carOrder->model->Name_TH ?? '-' }}
        </span> &nbsp;&nbsp;
        แบบ :
        <span style="font-weight: normal;">
          {{ $saleCar->carOrder->option ?? '-' }}
        </span>
      </span>

      <span class="label">
        รุ่นรถย่อย :
        <span style="font-weight: normal;">
          {{ $saleCar->carOrder->subModel->detail ?? '-' }} - {{ $saleCar->carOrder->subModel->name ?? '-' }}
        </span>
      </span>

      @if(auth()->user()->brand == 2)
      <span class="label">
        สีรถ :
        <span style="font-weight: normal;">
          {{ $saleCar->carOrder->gwmColor->name ?? '-' }}
        </span>
      </span>

      <span class="label">
        สีภายในรถ :
        <span style="font-weight: normal;">
          {{ $saleCar->carOrder->interiorColor->name ?? '-' }}
        </span>
      </span>
      @else
      <span class="label">
        สีรถ :
        <span style="font-weight: normal;">
          {{ $saleCar->carOrder->color ?? '-' }}
        </span>
      </span>
      @endif

      @if($saleCar->remainingPayment?->type === 'finance')
      <span class="label">
        ราคาเงินสด (รวมบวกหัว) :
        <span style="font-weight: normal;">
          {{ number_format($saleCar->CarSalePriceFinal ?? 0, 2) }}
        </span>
      </span>
      @else
      <span class="label">
        ราคาเงินสด :
        <span style="font-weight: normal;">
          {{ number_format($saleCar->CarSalePriceFinal ?? 0, 2) }}
        </span>
      </span>
      @endif

      <span class="label">
        เงินจอง :
        <span style="font-weight: normal;">
          {{ is_numeric($saleCar->reservationPayment->cost ?? null) 
            ? number_format($saleCar->reservationPayment->cost, 2) 
            : '-' }}
        </span>
      </span>

      <span class="label">
        หักราคารถเทิร์น :
        <span style="font-weight: normal;">
          {{ is_numeric($saleCar->turnCar->cost_turn ?? null) 
            ? number_format($saleCar->turnCar->cost_turn, 2) 
            : '-' }}
        </span>
      </span>

      <span class="label">
        ลูกค้าจ่ายเพิ่ม :
        <span style="font-weight: normal;">
          {{ is_numeric($saleCar->TotalAccessoryExtra ?? null) 
            ? number_format($saleCar->TotalAccessoryExtra, 2) 
            : '-' }}
        </span>
      </span>

      @if($saleCar->remainingPayment?->type === 'finance')
      <span class="label">
        เงินดาวน์ :
        <span style="font-weight: normal;">
          {{ is_numeric($saleCar->DownPayment ?? null) 
            ? number_format($saleCar->DownPayment, 2) 
            : '-' }}
        </span>
      </span>

      <span class="label">
        ส่วนลดเงินดาวน์ :
        <span style="font-weight: normal;">
          {{ is_numeric($saleCar->DownPaymentDiscount ?? null) 
            ? number_format($saleCar->DownPaymentDiscount, 2) 
            : '-' }}
        </span>
      </span>

      <span class="label">
        สรุปค่าใช้จ่ายวันออกรถ :
        <span style="font-weight: normal;">
          {{ is_numeric($saleCar->deliveryPayment->cost ?? null) 
            ? number_format($saleCar->deliveryPayment->cost, 2) 
            : '-' }}
        </span>
      </span>

      <span class="label">
        ไฟแนนซ์ :
        <span style="font-weight: normal;">
          {{ $saleCar->remainingPayment && $saleCar->remainingPayment->financeInfo
            ? $saleCar->remainingPayment->financeInfo->FinanceCompany
            : '-' }}
        </span>
      </span>

      <span class="label">
        ยอดจัด :
        <span style="font-weight: normal;">
          {{ is_numeric($saleCar->remainingPayment?->cost ?? null) 
            ? number_format($saleCar->remainingPayment->cost, 2) 
            : '-' }}
        </span>
      </span>

      <span class="label">
        ดอกเบี้ย :
        <span style="font-weight: normal;">
          {{ $saleCar->remainingPayment?->interest ?? '-' }}
        </span> &nbsp;&nbsp;
        งวดผ่อน :
        <span style="font-weight: normal;">
          {{ $saleCar->remainingPayment?->period ?? '-' }} งวด
        </span>
      </span>

      <span class="label">
        ค่างวด (กรณีไม่มี ALP) :
        <span style="font-weight: normal;">
          {{ is_numeric($saleCar->remainingPayment?->alp ?? null) 
        ? number_format($saleCar->remainingPayment->alp, 2) 
        : '-' }}
        </span>
      </span>

      <span class="label">
        ค่างวด (รวม ALP) :
        <span style="font-weight: normal;">
          {{ is_numeric($saleCar->remainingPayment?->including_alp ?? null) 
            ? number_format($saleCar->remainingPayment->including_alp, 2) 
            : '-' }}
        </span>
      </span>

      <span class="label">
        ยอดเงิน ALP ที่หักจากใบเสร็จดาวน์ :
        <span style="font-weight: normal;">
          {{ is_numeric($saleCar->remainingPayment?->total_alp ?? null) 
            ? number_format($saleCar->remainingPayment->total_alp, 2) 
            : '-' }}
        </span>
      </span>

      <span class="label">
        ดอกเบี้ยคอม :
        <span style="font-weight: normal;">
          {{ 'C' . $saleCar->remainingPayment?->type_com ?? '-' }}
        </span> &nbsp;&nbsp;
        ยอดเงินค่าคอม :
        <span style="font-weight: normal;">
          {{ is_numeric($saleCar->remainingPayment?->total_com ?? null) 
            ? number_format($saleCar->remainingPayment->total_com, 2) 
            : '-' }}
        </span>
      </span>

      @else
      <span class="label">
        ส่วนลด :
        <span style="font-weight: normal;">
          {{ is_numeric($saleCar->PaymentDiscount ?? null) 
            ? number_format($saleCar->PaymentDiscount, 2) 
            : '-' }}
        </span>
      </span>

      <span class="label">
        ยอดที่ต้องจ่ายวันออกรถ :
        <span style="font-weight: normal;">
          {{ is_numeric($saleCar->remainingPayment?->cost ?? null) 
            ? number_format($saleCar->remainingPayment->cost, 2) 
            : '-' }}
        </span>
      </span>
      @endif

      <span class="label">
        ผู้แนะนำ :
        <span style="font-weight: normal;">
          {{ $saleCar->customerReferrer->formatted_id_number ?? '-' }}
        </span>
      </span>

      <span class="label">
        ยอดเงินค่าแนะนำ :
        <span style="font-weight: normal;">
          {{ is_numeric($saleCar->ReferrerAmount ?? null) 
            ? number_format($saleCar->ReferrerAmount, 2) 
            : '-' }}
        </span>
      </span>

      <span class="label">
        แคมเปญ :
        <span style="font-weight: normal;">
          @if($saleCar->campaigns && $saleCar->campaigns->count() > 0)
          @foreach($saleCar->campaigns as $index => $camp)
          ({{ $camp->campaign->type->name ?? '-' }})
          {{ $camp->campaign->appellation->name ?? '-' }} - {{ number_format($camp->campaign->cashSupport_final, 2) }}
          @if(!$loop->last)
          +
          @endif
          @endforeach
          @else
          -
          @endif
        </span>
      </span>

      @if($saleCar->remainingPayment?->type === 'finance')
      @php
      $totalSaleCampaign = $saleCar->TotalSaleCampaign ?? 0;
      $markup90 = $saleCar->Markup90 ?? 0;
      $campaignBalance = $totalSaleCampaign + $markup90;
      @endphp

      <span class="label">
        งบ :
        <span style="font-weight: normal;">
          {{ is_numeric($saleCar->TotalSaleCampaign ?? null) 
            ? number_format($saleCar->TotalSaleCampaign, 2) 
            : '-' }}
        </span> &nbsp;&nbsp;
        บวกหัว 90% :
        <span style="font-weight: normal;">
          {{ is_numeric($saleCar->Markup90 ?? null) 
            ? number_format($saleCar->Markup90, 2) 
            : '-' }}
        </span>
      </span>

      <span class="label">
        รวม :
        <span style="font-weight: normal;">
          {{ is_numeric($campaignBalance ?? null) 
            ? number_format($campaignBalance, 2) 
            : '-' }}
        </span>
      </span>

      @php
      $DownPaymentDiscount = $saleCar->DownPaymentDiscount ?? 0;
      $TotalAccessoryGift = $TotalAccessoryGift->Markup90 ?? 0;
      $discountBalance = $DownPaymentDiscount + $TotalAccessoryGift;
      @endphp

      <span class="label">
        ส่วนลด :
        <span style="font-weight: normal;">
          {{ is_numeric($saleCar->DownPaymentDiscount ?? null) 
            ? number_format($saleCar->DownPaymentDiscount, 2) 
            : '-' }}
        </span> &nbsp;&nbsp;
        ส่วนต่างของแถม :
        <span style="font-weight: normal;">
          {{ is_numeric($saleCar->TotalAccessoryGift ?? null) 
            ? number_format($saleCar->TotalAccessoryGift, 2) 
            : '-' }}
        </span>
      </span>

      <span class="label">
        รวมส่วนลด :
        <span style="font-weight: normal;">
          {{ is_numeric($discountBalance ?? null) 
            ? number_format($discountBalance, 2) 
            : '-' }}
        </span>
      </span>

      <span class="label">
        เหลืองบ :
        <span style="font-weight: normal;">
          {{ is_numeric($saleCar->balanceCampaign ?? null) 
            ? number_format($saleCar->balanceCampaign, 2) 
            : '-' }}
        </span>
      </span>

      @else
      <span class="label">
        รวม :
        <span style="font-weight: normal;">
          {{ is_numeric($saleCar->TotalSaleCampaign ?? null) 
            ? number_format($saleCar->TotalSaleCampaign, 2) 
            : '-' }}
        </span>
      </span>

      @php
      $PaymentDiscount = $saleCar->PaymentDiscount ?? 0;
      $TotalAccessoryGift = $TotalAccessoryGift->Markup90 ?? 0;
      $discountBalance = $PaymentDiscount + $TotalAccessoryGift;
      @endphp

      <span class="label">
        ส่วนลด :
        <span style="font-weight: normal;">
          {{ is_numeric($saleCar->PaymentDiscount ?? null) 
            ? number_format($saleCar->PaymentDiscount, 2) 
            : '-' }}
        </span> &nbsp;&nbsp;
        ส่วนต่างของแถม :
        <span style="font-weight: normal;">
          {{ is_numeric($saleCar->TotalAccessoryGift ?? null) 
            ? number_format($saleCar->TotalAccessoryGift, 2) 
            : '-' }}
        </span>
      </span>

      <span class="label">
        รวมส่วนลด :
        <span style="font-weight: normal;">
          {{ is_numeric($discountBalance ?? null) 
            ? number_format($discountBalance, 2) 
            : '-' }}
        </span>
      </span>

      <span class="label">
        เหลืองบ :
        <span style="font-weight: normal;">
          {{ is_numeric($saleCar->balanceCampaign ?? null) 
            ? number_format($saleCar->balanceCampaign, 2) 
            : '-' }}
        </span>
      </span>

      @endif

    </div>

    <!-- RIGHT -->
    <div class="col-right">
      <div class="section-title" style="text-align: center;">ข้อมูลวันส่งมอบ</div>

      <span class="label">
        วันที่ส่งเอกสารสรุปการขาย :
        <span style="font-weight: normal;">
          {{ $saleCar->format_key_date ?? '-' }}
        </span>
      </span>

      <span class="label">
        วันส่งมอบจริง (วันที่แจ้งประกัน) :
        <span style="font-weight: normal;">
          {{ $saleCar->format_delivery_date ?? '-' }}
        </span>
      </span>

      <span class="label">
        วันที่ส่งมอบในระบบ DMS :
        <span style="font-weight: normal;">
          {{ $saleCar->format_dms_date ?? '-' }}
        </span>
      </span>

      <span class="label">
        วันที่ส่งมอบตามยอดชูเกียรติ :
        <span style="font-weight: normal;">
          {{ $saleCar->format_ck_date ?? '-' }}
        </span>
      </span>

      <div class="section-title" style="text-align: center; margin-top: 10px;">ผู้อนุมัติ</div>
      <span class="label">
        ผู้เช็ครายการ (แอดมินขาย) :
        <span style="font-weight: normal;">
          @if(($saleCar->AdminSignature ?? null) == 1)
          เช็ครายการเรียบร้อยแล้ว
          @else
          ยังไม่ได้เช็ค
          @endif
        </span>
      </span>

      <span class="label">
        วันที่แอดมินเช็ครายการ :
        <span style="font-weight: normal;">
          {{ $saleCar->format_admin_check_date ?? '-' }}
        </span>
      </span>

      <span class="label">
        ผู้ตรวจสอบรายการ (IA) :
        <span style="font-weight: normal;">
          @if(($saleCar->CheckerID ?? null) == 1)
          เช็ครายการเรียบร้อยแล้ว
          @else
          ยังไม่ได้เช็ค
          @endif
        </span>
      </span>

      <span class="label">
        วันที่ฝ่ายตรวจสอบเช็ครายการ :
        <span style="font-weight: normal;">
          {{ $saleCar->format_checker_date ?? '-' }}
        </span>
      </span>

      <span class="label">
        ผู้อนุมัติรายการ (ผู้จัดการขาย) :
        <span style="font-weight: normal;">
          @if(($saleCar->SMSignature ?? null) == 1)
          เช็ครายการเรียบร้อยแล้ว
          @else
          ยังไม่ได้เช็ค
          @endif
        </span>
      </span>

      <span class="label">
        วันที่ผู้จัดการขายอนุมัติ :
        <span style="font-weight: normal;">
          {{ $saleCar->format_sm_date ?? '-' }}
        </span>
      </span>

      <span class="label">
        ผู้อนุมัติการขายกรณีเกินจากงบ :
        <span style="font-weight: normal;">
          @if(($saleCar->ApprovalSignature ?? null) == 1)
          เช็ครายการเรียบร้อยแล้ว
          @else
          ยังไม่ได้เช็ค
          @endif
        </span>
      </span>

      <span class="label">
        วันที่ผู้จัดการอนุมัติการขาย :
        <span style="font-weight: normal;">
          {{ $saleCar->format_approval_date ?? '-' }}
        </span>
      </span>

      <span class="label">
        GM อนุมัติกรณีงบเกิน (N) :
        <span style="font-weight: normal;">
          @if(($saleCar->GMApprovalSignature ?? null) == 1)
          เช็ครายการเรียบร้อยแล้ว
          @else
          ยังไม่ได้เช็ค
          @endif
        </span>
      </span>

      <span class="label">
        วันที่ GM อนุมัติกรณีงบเกิน :
        <span style="font-weight: normal;">
          {{ $saleCar->format_gm_date ?? '-' }}
        </span>
      </span>

    </div>
  </div>

  <div style="page-break-before: always;"></div>
  <div class="section-title" style="text-align: center;">รายละเอียดอุปกรณ์ตกแต่ง (แถม)</div>
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
        <th>ค่าคอม</th>
      </tr>
    </thead>
    <tbody>
      @foreach($giftAccessories as $giftAcc)
      <tr>
        <td style="text-align: center;">{{ $loop->iteration }}</td>
        <td style="text-align: center;">{{ $giftAcc->accessory_id ?? '-' }}</td>
        <td style="text-align: center;">{{ $giftAcc->detail ?? '-' }}</td>
        <td style="text-align: center;">{{ $giftAcc->pivot->price_type ?? '-' }}</td>
        <td style="text-align: right;">{{ number_format($giftAcc->pivot->price, 2) }}</td>
        <td style="text-align: right;">{{ number_format($giftAcc->pivot->commission, 2) }}</td>
      </tr>
      @endforeach
      <tr>
        <td colspan="4" style="text-align: center; font-weight: bold;">รวมทั้งหมด</td>
        <td style="text-align: right; font-weight: bold;">{{ number_format($saleCar->TotalAccessoryGift ?? 0, 2) }}</td>
        <td style="text-align: right; font-weight: bold;">{{ number_format($saleCar->AccessoryGiftCom ?? 0, 2) }}</td>
      </tr>
    </tbody>
  </table>
  @else
  <p style="text-align: center;">- ไม่มีข้อมูลรายการของแถม -</p>
  @endif

  <div class="section-title" style="text-align: center; margin-top: 1em;">รายการซื้อเพิ่ม</div>
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
        <th>ค่าคอม</th>
      </tr>
    </thead>
    <tbody>
      @foreach($extraAccessories as $extraAcc)
      <tr>
        <td style="text-align: center;">{{ $loop->iteration }}</td>
        <td style="text-align: center;">{{ $extraAcc->accessory_id ?? '-' }}</td>
        <td style="text-align: center;">{{ $extraAcc->detail ?? '-' }}</td>
        <td style="text-align: center;">{{ $extraAcc->pivot->price_type ?? '-' }}</td>
        <td style="text-align: right;">{{ number_format($extraAcc->pivot->price, 2) }}</td>
        <td style="text-align: right;">{{ number_format($extraAcc->pivot->commission, 2) }}</td>
      </tr>
      @endforeach
      <tr>
        <td colspan="4" style="text-align: center; font-weight: bold;">รวมทั้งหมด</td>
        <td style="text-align: right; font-weight: bold;">{{ number_format($saleCar->TotalAccessoryExtra ?? 0, 2) }}</td>
        <td style="text-align: right; font-weight: bold;">{{ number_format($saleCar->AccessoryExtraCom ?? 0, 2) }}</td>
      </tr>
    </tbody>
  </table>
  @else
  <p style="text-align: center;">- ไม่มีข้อมูลรายการของแถม -</p>
  @endif


</body>

</html>