<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>อนุมัติแคมเปญ CK (MD)</title>
  <style>
    * { box-sizing: border-box; }
    body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f1f5f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 16px; }
    .card { background: #fff; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,.08); padding: 28px; max-width: 620px; width: 100%; }
    h1 { font-size: 1.15rem; color: #0f172a; margin: 0 0 4px; text-align: center; }
    .sub { color: #64748b; font-size: .85rem; text-align: center; margin-bottom: 18px; }
    .table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    table { width: 100%; border-collapse: collapse; font-size: .9rem; min-width: 460px; }
    th, td { padding: 8px 8px; border-bottom: 1px solid #e2e8f0; text-align: left; }
    th { color: #475569; font-weight: 600; background: #f8fafc; white-space: nowrap; }
    td.amt, th.amt { text-align: right; white-space: nowrap; }
    tfoot td { font-weight: 700; color: #0f172a; border-top: 2px solid #cbd5e1; }
    label.field { display: block; font-size: .88rem; color: #334155; margin: 16px 0 6px; font-weight: 600; }
    textarea { width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: .95rem; box-sizing: border-box; resize: vertical; }
    .btns { display: flex; gap: 10px; margin-top: 22px; flex-wrap: wrap; }
    button { flex: 1 1 160px; padding: 12px; border: none; border-radius: 8px; font-size: 1rem; cursor: pointer; color: #fff; }
    .approve { background: #059669; } .approve:hover { background: #047857; }
    .sendback { background: #f59e0b; } .sendback:hover { background: #d97706; }
    button:disabled { opacity: .7; cursor: not-allowed; }
    .spinner { display: inline-block; width: 14px; height: 14px; border: 2px solid rgba(255,255,255,.5); border-top-color: #fff; border-radius: 50%; animation: spin .7s linear infinite; vertical-align: middle; margin-right: 6px; }
    @keyframes spin { to { transform: rotate(360deg); } }
    #rejectBox { display: none; }

    @media (max-width: 480px) {
      body { padding: 0; align-items: flex-start; }
      .card { border-radius: 0; padding: 20px 14px; min-height: 100vh; box-shadow: none; }
      table { font-size: .82rem; }
      th, td { padding: 7px 6px; }
      button { flex: 1 1 100%; }
    }
  </style>
</head>
<body>
  @php
    $pending = $approvals->where('status', 'pending');
    $totalAmount = $pending->sum(fn($ap) => (float) ($ap->campaign->cashSupport_final ?? 0));
    // รุ่นย่อย: brand 1 → name, แบรนด์อื่น → detail (fallback ไปอีกฟิลด์ถ้าว่าง)
    $subOf = function ($c) {
        $sub = $c->subModel ?? null;
        if (!$sub) return '-';
        $text = ((int) $c->brand === 1) ? $sub->name : $sub->detail;
        return $text ?: ($sub->name ?: ($sub->detail ?: '-'));
    };
  @endphp
  <div class="card">
    <h1>อนุมัติแคมเปญ CK (MD)</h1>
    <div class="sub">อนุมัติแคมเปญ CK สำหรับเดือน {{ $period }} — {{ $pending->count() }} รายการ</div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>แคมเปญ</th>
            <th>รุ่นรถ</th>
            <th>รุ่นย่อย</th>
            <th class="amt">จำนวนเงิน (สุทธิ)</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($pending as $ap)
            <tr>
              <td>{{ $ap->campaign->appellation->name ?? '-' }} ({{ $ap->campaign->type->name ?? '-' }})</td>
              <td>{{ $ap->campaign->model->Name_TH ?? '-' }}</td>
              <td>{{ $subOf($ap->campaign) }}</td>
              <td class="amt">{{ number_format($ap->campaign->cashSupport_final ?? 0, 2) }}</td>
            </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr>
            <td colspan="3">รวม</td>
            <td class="amt">{{ number_format($totalAmount, 2) }}</td>
          </tr>
        </tfoot>
      </table>
    </div>

    <div class="btns">
      <form method="POST" action="{{ route('campaign.ckApproval.approve', $token) }}" style="flex:1 1 160px;">
        @csrf
        <button type="submit" class="approve">อนุมัติทั้งหมด ({{ $pending->count() }})</button>
      </form>
      <button type="button" class="sendback" id="btnShowReject">ส่งกลับไปแก้ไข</button>
    </div>

    <form method="POST" action="{{ route('campaign.ckApproval.reject', $token) }}" id="rejectBox">
      @csrf
      <label class="field" for="note">เหตุผล / สิ่งที่ต้องแก้ไข (แจ้งผู้ขอ)</label>
      <textarea id="note" name="note" rows="3" placeholder="เช่น ยอดไม่ถูกต้อง กรุณาแก้ไขแล้วส่งใหม่..."></textarea>
      <div class="btns">
        <button type="submit" class="sendback">ยืนยันส่งกลับให้ผู้ขอแก้ไข</button>
      </div>
    </form>
  </div>

  <script>
    document.getElementById('btnShowReject').addEventListener('click', function () {
      document.getElementById('rejectBox').style.display = 'block';
      this.style.display = 'none';
    });
    document.querySelectorAll('form').forEach(function (f) {
      f.addEventListener('submit', function () {
        const btn = f.querySelector('button[type=submit]');
        if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner"></span> กำลังดำเนินการ...'; }
      });
    });
  </script>
</body>
</html>
