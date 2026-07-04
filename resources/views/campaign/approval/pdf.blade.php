<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>รายการแคมเปญ CK</title>
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
    @page { margin: 28px 30px 40px 30px; }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'THSarabunNew', DejaVu Sans, sans-serif; font-size: 12.5pt; color: #1e293b; }

    /* Header band */
    .banner { background: #4f46e5; color: #fff; padding: 11px 16px; border-radius: 6px; }
    .banner h1 { font-size: 18pt; font-weight: bold; margin: 0; }
    .banner .tag { font-size: 11pt; color: #c7d2fe; }

    /* Summary chips */
    .info { width: 100%; margin: 12px 0 14px; border-collapse: separate; border-spacing: 7px 0; }
    .info td { width: 25%; background: #eef2ff; border: 1px solid #e0e7ff; border-radius: 6px; padding: 6px 12px; }
    .info .lbl { color: #6366f1; font-size: 10.5pt; }
    .info .val { font-size: 14pt; font-weight: bold; color: #312e81; }
    .info .val.money { color: #047857; }

    /* Table */
    table.list { width: 100%; border-collapse: collapse; }
    table.list th, table.list td { border: 1px solid #d7dced; padding: 5px 9px; }
    table.list thead th { background: #4f46e5; color: #fff; font-weight: bold; font-size: 11.5pt; }
    table.list tbody tr.odd td { background: #f6f7fc; }
    table.list td.no, table.list th.no { text-align: center; width: 30px; }
    table.list td.amt, table.list th.amt { text-align: right; white-space: nowrap; width: 108px; }
    table.list tfoot td { background: #e0e7ff; font-weight: bold; color: #312e81; }
    table.list tfoot td.amt { color: #047857; font-size: 13.5pt; }
    thead { display: table-header-group; }

    .foot { margin-top: 10px; font-size: 10.5pt; color: #94a3b8; text-align: right; }
  </style>
</head>
<body>
  @php
    $total = $approvals->sum(fn($ap) => (float) ($ap->campaign->cashSupport_final ?? 0));
    // รุ่นย่อย: brand 1 → name, แบรนด์อื่น → detail (fallback ไปอีกฟิลด์ถ้าว่าง)
    $subOf = function ($c) {
        $sub = $c->subModel ?? null;
        if (!$sub) return '-';
        $text = ((int) $c->brand === 1) ? $sub->name : $sub->detail;
        return $text ?: ($sub->name ?: ($sub->detail ?: '-'));
    };
  @endphp

  <div class="banner">
    <h1>ขออนุมัติแคมเปญ CK</h1>
    <div class="tag">Campaign CK Approval — สำหรับผู้อนุมัติ (MD)</div>
  </div>

  <table class="info">
    <tr>
      <td><div class="lbl">แบรนด์</div><div class="val">{{ $brandName }}</div></td>
      <td><div class="lbl">เดือน</div><div class="val">{{ $period }}</div></td>
      <td><div class="lbl">จำนวนรายการ</div><div class="val">{{ number_format($approvals->count()) }}</div></td>
      <td><div class="lbl">ยอดรวม (สุทธิ)</div><div class="val money">{{ number_format($total, 2) }}</div></td>
    </tr>
  </table>

  <table class="list">
    <thead>
      <tr>
        <th class="no">#</th>
        <th>ชื่อแคมเปญ</th>
        <th>ประเภท</th>
        <th>รุ่นรถ</th>
        <th>รุ่นย่อย</th>
        <th class="amt">จำนวนเงิน (สุทธิ)</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($approvals as $i => $ap)
        <tr class="{{ $i % 2 ? 'odd' : '' }}">
          <td class="no">{{ $i + 1 }}</td>
          <td>{{ $ap->campaign->appellation->name ?? '-' }}</td>
          <td>{{ $ap->campaign->type->name ?? '-' }}</td>
          <td>{{ $ap->campaign->model->Name_TH ?? '-' }}</td>
          <td>{{ $subOf($ap->campaign) }}</td>
          <td class="amt">{{ number_format($ap->campaign->cashSupport_final ?? 0, 2) }}</td>
        </tr>
      @endforeach
    </tbody>
    <tfoot>
      <tr>
        <td colspan="5">รวมทั้งหมด {{ number_format($approvals->count()) }} รายการ</td>
        <td class="amt">{{ number_format($total, 2) }}</td>
      </tr>
    </tfoot>
  </table>

  <div class="foot">พิมพ์เมื่อ {{ now()->format('d/m/Y H:i') }} น.</div>
</body>
</html>
