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
    @page { margin: 24px 26px 34px 26px; }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'THSarabunNew', DejaVu Sans, sans-serif; font-size: 12.5pt; color: #1e293b; }

    /* Header band */
    .banner { background: #4f46e5; color: #fff; padding: 10px 16px; border-radius: 6px; }
    .banner h1 { font-size: 18pt; font-weight: bold; margin: 0; }
    .banner .tag { font-size: 11pt; color: #c7d2fe; }

    /* Summary chips */
    .info { width: 100%; margin: 10px 0 14px; border-collapse: separate; border-spacing: 7px 0; }
    .info td { width: 25%; background: #eef2ff; border: 1px solid #e0e7ff; border-radius: 6px; padding: 5px 12px; }
    .info .lbl { color: #6366f1; font-size: 10.5pt; }
    .info .val { font-size: 14pt; font-weight: bold; color: #312e81; }
    .info .val.money { color: #047857; }

    /* แสดงต่อเนื่อง — ไม่บังคับขึ้นหน้าใหม่ต่อรุ่น แต่กันไม่ให้รุ่นเดียวถูกตัดกลางหน้า (เซลล์รวมไม่เพี้ยน) */
    .grp-section { margin-bottom: 12px; page-break-inside: avoid; }

    /* Table (แนวนอน — รวมเซลล์ รุ่นหลัก/รุ่นย่อย ด้านซ้าย) */
    table.list { width: 100%; border-collapse: collapse; font-size: 12pt; }
    table.list th, table.list td { border: 1px solid #c9cfe6; padding: 3px 9px; line-height: 1.15; vertical-align: middle; }
    table.list thead th { background: #4f46e5; color: #fff; font-weight: bold; font-size: 11.5pt; }
    table.list td.amt, table.list th.amt { text-align: right; white-space: nowrap; width: 120px; }
    table.list td.model-cell { background: #eef2ff; color: #312e81; font-weight: bold; font-size: 13.5pt; text-align: center; }
    table.list td.sub-cell, table.list td.year-cell { background: #f7f8fd; text-align: center; }
    table.list td.year-cell { white-space: nowrap; }
    table.list tfoot td { background: #e0e7ff; font-weight: bold; color: #312e81; }
    table.list tfoot td.amt { color: #047857; }
    thead { display: table-header-group; }

    .foot { margin-top: 8px; font-size: 10.5pt; color: #94a3b8; text-align: right; }
  </style>
</head>
<body>
  @php
    $grandTotal = $approvals->sum(fn($ap) => (float) ($ap->campaign->cashSupport_final ?? 0));
    // รุ่นย่อย: brand 1 → detail (ชื่ออ่านง่าย), แบรนด์อื่น → name
    $subOf = function ($c) {
        $sub = $c->subModel ?? null;
        if (!$sub) return '-';
        $text = ((int) $c->brand === 1) ? $sub->detail : $sub->name;
        return $text ?: ($sub->name ?: ($sub->detail ?: '-'));
    };
    // ปี: ปีเดียวกัน → ปีเดียว, คนละปี → 2024-2026, มีช่องเดียว → ช่องนั้น
    $yearOf = function ($c) {
        $s = trim((string) ($c->startYear ?? ''));
        $e = trim((string) ($c->endYear ?? ''));
        if ($s === '' && $e === '') return '-';
        if ($s === '') return $e;
        if ($e === '' || $s === $e) return $s;
        return $s . '-' . $e;
    };
    // แปลง + จัดเรียง แล้วจัดกลุ่มตาม รุ่นหลัก (แต่ละกลุ่มจัดกลุ่มย่อยตามรุ่นย่อยอีกที)
    $groups = $approvals->map(fn($ap) => [
        'model'  => $ap->campaign->model->Name_TH ?? '-',
        'sub'    => $subOf($ap->campaign),
        'year'   => $yearOf($ap->campaign),
        'cam'    => $ap->campaign->appellation->name ?? '-',
        'amount' => (float) ($ap->campaign->cashSupport_final ?? 0),
    ])
    ->sortBy(fn($r) => $r['model'] . '|' . $r['sub'] . '|' . $r['cam'])
    ->groupBy('model');
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
      <td><div class="lbl">ยอดรวม (สุทธิ)</div><div class="val money">{{ number_format($grandTotal, 2) }}</div></td>
    </tr>
  </table>

  @foreach ($groups as $model => $modelItems)
    @php
      $bySub      = $modelItems->groupBy('sub');
      $modelRows  = $modelItems->count();
      $modelTotal = $modelItems->sum('amount');
    @endphp
    <div class="grp-section">
      <table class="list">
        <thead>
          <tr>
            <th style="width:15%;">รุ่นหลัก</th>
            <th style="width:28%;">รุ่นย่อย</th>
            <th style="width:90px;">ปี</th>
            <th>ชื่อแคมเปญ</th>
            <th class="amt">จำนวนเงิน (สุทธิ)</th>
          </tr>
        </thead>
        <tbody>
          @php $firstModel = true; @endphp
          @foreach ($bySub as $sub => $subItems)
            @php $subRows = $subItems->count(); $subYear = $subItems->first()['year']; $firstSub = true; @endphp
            @foreach ($subItems as $r)
              <tr>
                @if ($firstModel)
                  <td rowspan="{{ $modelRows }}" class="model-cell">{{ $model }}</td>
                  @php $firstModel = false; @endphp
                @endif
                @if ($firstSub)
                  <td rowspan="{{ $subRows }}" class="sub-cell">{{ $sub }}</td>
                  <td rowspan="{{ $subRows }}" class="year-cell">{{ $subYear }}</td>
                  @php $firstSub = false; @endphp
                @endif
                <td>{{ $r['cam'] }}</td>
                <td class="amt">{{ number_format($r['amount'], 2) }}</td>
              </tr>
            @endforeach
          @endforeach
        </tbody>
        <tfoot>
          <tr>
            <td colspan="4">รวม {{ $model }} ({{ number_format($modelRows) }} รายการ)</td>
            <td class="amt">{{ number_format($modelTotal, 2) }}</td>
          </tr>
        </tfoot>
      </table>
    </div>
  @endforeach

  <div class="foot">พิมพ์เมื่อ {{ now()->format('d/m/Y H:i') }} น.</div>
</body>
</html>
