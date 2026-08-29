<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <title>Sale Pocket</title>
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

    * { box-sizing: border-box; margin: 0; padding: 0; }

    html, body {
      font-family: 'THSarabunNew', DejaVu Sans, sans-serif;
      font-size: 13pt;
      color: #1a1a2e;
      line-height: 1.15;
    }

    /* ขอบกระดาษต้องประกาศที่ body ไม่ใช่ @page — dompdf ตัวนี้ไม่สนใจ margin ใน @page
       (ทดสอบแล้ว: @page margin ไม่มีผลเลย ส่วน body margin ถูกใช้ซ้ำ "ทุกหน้า" ตามต้องการ)
       เดิมเว้นขอบด้วย padding ของกล่อง หน้า 2 เป็นต้นไปจึงชิดหัวกระดาษ */
    body {
      margin: 12mm 9mm;
    }

    .page-header { margin: 0; }

    .page-header-top {
      display: table;
      width: 100%;
      border-bottom: 1.5px solid #000;
      padding-bottom: 3px;
      margin-bottom: 4px;
    }

    .page-header-top-cell { display: table-cell; vertical-align: middle; width: 33.33%; }
    .page-header-top-cell.mid { text-align: center; }
    .page-header-top-cell.right { text-align: right; }

    .h-subtitle { display: block; font-size: 10pt; color: #666; }
    .h-value { display: block; font-weight: bold; }
    .h-sub { display: block; font-size: 11pt; color: #444; }
    .h-main { display: block; font-size: 15pt; font-weight: bold; letter-spacing: .04em; }

    .page-content { padding: 0; }

    .sec { margin-bottom: 6px; border: 1.5px solid #999; border-top: 2.5px solid #444; }
    .sec-title {
      font-size: 12pt; font-weight: bold; padding: 3px 10px;
      background: #e0e0e0; color: #1a1a1a; letter-spacing: .06em;
      border-bottom: 1px solid #bbb;
    }
    .sec-body { padding: 3px 8px; background: #fff; }

    .f { display: table; width: 100%; padding: 2px 0; border-bottom: 1px dotted #ccc; }
    .f:last-child { border-bottom: none; }
    .fl { display: table-cell; width: 62%; color: #333; }
    .fv { display: table-cell; width: 38%; text-align: right; font-weight: bold; }

    .f.total { border-top: 1.5px solid #444; border-bottom: none; padding-top: 4px; margin-top: 2px; }
    .f.total .fl, .f.total .fv { font-weight: bold; font-size: 14pt; }

    /* ตารางของแถม — แยกบล็อก ไม่มีกรอบนอกครอบ เพื่อให้ตัดหน้ากลางตารางแล้วยังดูจบสวย
       หัวตาราง (thead) dompdf จะพิมพ์ซ้ำให้เองทุกหน้า และไม่ตัดกลางแถว */
    .gift-block { margin-top: 6px; }

    .gift-head {
      font-size: 12pt; font-weight: bold; padding: 3px 10px;
      background: #e0e0e0; color: #1a1a1a; letter-spacing: .06em;
      border: 1.5px solid #999; border-top: 2.5px solid #444; border-bottom: none;
    }

    .gift-table { width: 100%; border-collapse: collapse; }
    .gift-table thead { display: table-header-group; }
    .gift-table tr { page-break-inside: avoid; }
    .gift-table th, .gift-table td {
      border: 1px solid #999; padding: 2.5px 6px; font-size: 11.5pt; vertical-align: top;
    }
    .gift-table th { background: #f2f2f2; font-weight: bold; text-align: center; }
    .gift-table td.num { text-align: right; white-space: nowrap; }
    .gift-table tr.sum td { background: #fafafa; font-weight: bold; }
    .gift-note { display: block; font-size: 10pt; color: #6b7280; }

    /* กล่องสรุปสั้นพอที่จะอยู่หน้าเดียว — ห้ามตัดกลางกล่อง (ที่ทำให้กรอบค้างเปิด) */
    .sec, .result { page-break-inside: avoid; }

    /* แถบ "รวมรายการที่ใช้ไป" ต่อท้ายตารางของแถม — ไม่มีหัวกล่อง */
    .sum-bar { margin-top: 4px; }
    .sum-bar .sec-body { padding: 2px 8px; }
    .sum-bar .f.total { border-top: none; margin-top: 0; }

    .result { border: 2.5px solid #444; padding: 6px 10px; display: table; width: 100%; }
    .result .lbl { display: table-cell; font-size: 15pt; font-weight: bold; }
    .result .val { display: table-cell; text-align: right; font-size: 17pt; font-weight: bold; }

    .note { font-size: 10.5pt; color: #666; margin-top: 5px; line-height: 1.3; }
  </style>
</head>

<body>
  @php
    $fmt = fn($v) => number_format((float) $v, 2);
    $isNeg = $pocket['remaining'] < 0;
  @endphp
  <div class="page">
    <div class="page-header">
      <div class="page-header-top">
        <div class="page-header-top-cell">
          <span class="h-subtitle">วันที่จอง</span>
          <span class="h-value">{{ $saleCar->format_booking_date_sum ?? '-' }}</span>
        </div>
        <div class="page-header-top-cell mid">
          <span class="h-sub">รายละเอียดกระเป๋าของเซลล์</span>
          <span class="h-main">Sale Pocket</span>
        </div>
        <div class="page-header-top-cell right">
          <span class="h-subtitle">เลขที่ใบจอง</span>
          <span class="h-value">{{ $saleCar->order_code ?? $saleCar->id }}</span>
        </div>
      </div>
    </div>

    <div class="page-content">

      {{-- ── ข้อมูลใบจอง ── --}}
      <div class="sec">
        <div class="sec-title">ข้อมูลใบจอง</div>
        <div class="sec-body">
          <div class="f">
            <div class="fl">ลูกค้า</div>
            <div class="fv">{{ $saleCar->customer->prefix->Name_TH ?? '' }}{{ $saleCar->customer->FirstName ?? '' }}
              {{ $saleCar->customer->LastName ?? '' }}</div>
          </div>
          <div class="f">
            <div class="fl">ฝ่ายขาย</div>
            <div class="fv">{{ $saleCar->saleUser?->name ?? '-' }}</div>
          </div>
          <div class="f">
            <div class="fl">รุ่นรถ / รุ่นย่อย</div>
            <div class="fv">{{ $saleCar->model->Name_TH ?? '-' }} / {{ $saleCar->subModel->name ?? '-' }}</div>
          </div>
          <div class="f">
            <div class="fl">ประเภทการชำระ</div>
            <div class="fv">{{ $pocket['is_finance'] ? 'จัดไฟแนนซ์' : 'เงินสด' }}</div>
          </div>
        </div>
      </div>

      {{-- ── งบที่ได้ ── --}}
      <div class="sec">
        <div class="sec-title">งบที่ได้</div>
        <div class="sec-body">
          <div class="f">
            <div class="fl">ยอดแคมเปญทั้งหมด</div>
            <div class="fv">{{ $fmt($pocket['campaign_total']) }}</div>
          </div>
          <div class="f">
            <div class="fl">บวกหัว 90%</div>
            <div class="fv">{{ $fmt($pocket['markup90']) }}</div>
          </div>
          <div class="f">
            <div class="fl">Kick Back</div>
            <div class="fv">{{ $fmt($pocket['kickback']) }}</div>
          </div>
          <div class="f total">
            <div class="fl">รวมงบที่ได้</div>
            <div class="fv">{{ $fmt($pocket['budget_total']) }}</div>
          </div>
        </div>
      </div>


      {{-- ── รายการที่ใช้ไป : ตัวเลข → รายละเอียดของแถม → รวม ── --}}
      <div class="sec">
        <div class="sec-title">รายการที่ใช้ไป</div>
        <div class="sec-body">
          <div class="f">
            <div class="fl">ส่วนลดราคารถ</div>
            <div class="fv">{{ $fmt($pocket['car_discount']) }}</div>
          </div>
          <div class="f">
            <div class="fl">ส่วนลดเงินดาวน์</div>
            <div class="fv">{{ $fmt($pocket['down_payment_discount']) }}</div>
          </div>
          <div class="f">
            <div class="fl">เงินจอง</div>
            <div class="fv">{{ $fmt($pocket['cash_deposit']) }}</div>
          </div>
          <div class="f">
            <div class="fl">Vat ของแถม</div>
            <div class="fv">{{ $fmt($pocket['accessory_gift_vat']) }}</div>
          </div>
          <div class="f">
            <div class="fl">ของแถม (ราคาทุนอะไหล่) — {{ count($pocket['gift_details']) }} รายการ</div>
            <div class="fv">{{ $fmt($pocket['gift_total']) }}</div>
          </div>
        </div>
      </div>

      {{-- รายละเอียดของแถม — แยกบล็อกไม่มีกรอบนอกครอบ ตัดหน้าได้ หัวตารางซ้ำเองทุกหน้า --}}
      @if (count($pocket['gift_details']))
        <div class="gift-block">
          <div class="gift-head">รายละเอียดของแถม (ราคาทุนอะไหล่)</div>
          <table class="gift-table">
            <thead>
              <tr>
                <th style="width:9%;">ลำดับ</th>
                <th>รายการ</th>
                <th style="width:22%;">ราคาทุนอะไหล่</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($pocket['gift_details'] as $i => $g)
                <tr>
                  <td class="num">{{ $i + 1 }}</td>
                  <td>
                    {{ $g['detail'] }}
                    @if (filled($g['note'] ?? null))
                      <span class="gift-note">หมายเหตุ: {{ $g['note'] }}</span>
                    @endif
                  </td>
                  <td class="num">{{ $fmt($g['amount']) }}</td>
                </tr>
              @endforeach
              <tr class="sum">
                <td colspan="2">รวมของแถม ({{ count($pocket['gift_details']) }} รายการ)</td>
                <td class="num">{{ $fmt($pocket['gift_total']) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      @endif

      {{-- ── รวมรายการที่ใช้ไป + คงเหลือ ── --}}
      <div class="sec sum-bar">
        <div class="sec-body">
          <div class="f total">
            <div class="fl">รวมรายการที่ใช้ไป</div>
            <div class="fv">{{ $fmt($pocket['used_total']) }}</div>
          </div>
        </div>
      </div>

      <div class="result">
        <span class="lbl">{{ $isNeg ? 'เกินงบ' : 'คงเหลือในกระเป๋า' }}</span>
        <span class="val">{{ $fmt($pocket['remaining']) }} บาท</span>
      </div>

      <div class="note">
        คำนวณจาก : (ยอดแคมเปญทั้งหมด + บวกหัว 90% + Kick Back) − (ส่วนลดราคารถ + ส่วนลดเงินดาวน์ + เงินจอง +
        Vat ของแถม + ของแถมตามราคาทุนอะไหล่)<br>
        ของแถมคิดที่ "ราคาทุนอะไหล่" ชุดเดียวกับที่แสดงในอีเมลขออนุมัติ ซึ่งเป็นคนละฐานกับยอดของแถมที่ใช้คิด
        เหลืองบ/เกินงบ ในไฟล์สรุปการขาย ตัวเลขคงเหลือสองไฟล์จึงไม่จำเป็นต้องเท่ากัน
      </div>

    </div>
  </div>
</body>

</html>
