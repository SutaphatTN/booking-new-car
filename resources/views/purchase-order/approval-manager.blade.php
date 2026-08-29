<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>อนุมัติคำขอ (ผู้จัดการ)</title>
  <style>
    body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f1f5f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; }
    .card { background: #fff; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,.08); padding: 32px; max-width: 480px; width: 100%; }
    h1 { font-size: 1.15rem; color: #0f172a; margin: 0 0 4px; text-align: center; }
    .sub { color: #64748b; font-size: .85rem; text-align: center; margin-bottom: 20px; }
    .row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px dashed #e2e8f0; font-size: .92rem; }
    .row .lbl { color: #475569; }
    .row .val { font-weight: 600; color: #0f172a; }
    label { display: block; font-size: .88rem; color: #334155; margin: 18px 0 6px; font-weight: 600; }
    label.choice { display: block; margin: 14px 0 4px; font-size: .95rem; color: #334155; font-weight: 500; cursor: pointer; }
    input[type=number], input[type=text] { width: 100%; padding: 13px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1.2rem; font-weight: 600; text-align: right; box-sizing: border-box; }
    input:focus { outline: none; border-color: #6c5ffc; box-shadow: 0 0 0 3px rgba(108,95,252,.15); }
    .extra { background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 10px 14px; margin-top: 14px; display: flex; justify-content: space-between; }
    .extra .lbl { color: #047857; font-weight: 600; }
    .extra .val { color: #047857; font-weight: 700; }
    button { width: 100%; margin-top: 22px; padding: 12px; background: #6c5ffc; color: #fff; border: none; border-radius: 8px; font-size: 1rem; cursor: pointer; }
    button:hover { background: #5a4fd6; }
    .err { color: #dc2626; font-size: .82rem; margin-top: 6px; }
    .hint { font-size: .82rem; color: #64748b; margin-top: 6px; line-height: 1.35; }
    .hint strong { color: #334155; }
    button:disabled { opacity: .7; cursor: not-allowed; }
    .spinner { display: inline-block; width: 14px; height: 14px; border: 2px solid rgba(255,255,255,.5); border-top-color: #fff; border-radius: 50%; animation: spin .7s linear infinite; vertical-align: middle; margin-right: 6px; }
    @keyframes spin { to { transform: rotate(360deg); } }
  </style>
</head>
<body>
  @php
    // กติกาใหม่ : ทุกแบรนด์กรอกเป็น "ยอดที่ต้องหัก" (ดู Salecar::usesDeductAmount / suggestedCommissionDeduct)
    $deductLabel   = 'ยอดที่ต้องหัก';                             // กติกาใหม่ : ทุกแบรนด์กรอกเป็นยอดหัก
    $showVip       = $showDeduct && $saleCar->allowsVipChoice(); // เฉพาะ brand 2
    $showExtra     = $showDeduct && $saleCar->usesExtraBudget(); // เฉพาะ brand 1/3
    $finalLabel    = strtoupper($saleCar->finalApproverRole());
    $decisionValue = old('decision', $saleCar->approval_is_vip ? 'vip' : 'deduct');

    // เกินงบยอดเต็ม + ยอดหักแนะนำ (เกินงบ × 10%) — เติมให้เป็นค่าตั้งต้น ผู้จัดการแก้เพิ่มได้
    $overFull    = abs((float) ($saleCar->balanceCampaign ?? 0)) * 2;
    $suggest     = $saleCar->suggestedCommissionDeduct();
    $deductValue = old('commission_deduct', $saleCar->approval_commission_deduct ?? ($suggest > 0 ? number_format($suggest, 2, '.', '') : ''));
  @endphp
  <div class="card">
    <h1>อนุมัติคำขอสั่งจอง</h1>
    <div class="sub">
      @if ($showDeduct) ผู้จัดการ — กรอก{{ $deductLabel }} ก่อนส่งต่อผู้อนุมัติขั้นสุดท้าย @else ผู้จัดการ — ยืนยันการอนุมัติ @endif
    </div>

    <div class="row"><span class="lbl">ใบจอง</span><span class="val">{{ $saleCar->order_code ?? $saleCar->id }}</span></div>
    <div class="row"><span class="lbl">รุ่นรถ</span><span class="val">{{ $saleCar->model->Name_TH ?? '-' }}</span></div>
    <div class="row"><span class="lbl">ลูกค้า</span><span class="val">{{ $saleCar->customer->FirstName ?? '' }} {{ $saleCar->customer->LastName ?? '' }}</span></div>
    <div class="row"><span class="lbl">ยอดที่เหลือ (จากใบขออนุมัติ)</span><span class="val" style="color: {{ ($saleCar->approval_remaining ?? 0) < 0 ? '#dc2626' : '#059669' }}">{{ number_format($saleCar->approval_remaining ?? 0, 2) }}</span></div>

    @if ($showDeduct && !empty($saleCar->approval_md_note))
      <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:12px 14px;margin-top:14px;font-size:.9rem;color:#92400e;">
        🔁 <strong>ผู้อนุมัติตีกลับ</strong> — ขอให้ทบทวน{{ $deductLabel }}<br>
        <span style="color:#78350f;">โน้ตจากผู้อนุมัติ : {{ $saleCar->approval_md_note }}</span>
      </div>
    @endif

    @if ($errors->any())
      <div class="err">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('purchase-order.managerApprove', $token) }}">
      @csrf
      @if ($showDeduct)
        @if ($showVip)
          {{-- brand 2 : เลือกหักเงิน (ส่งต่อ GM) หรือ ไม่หักเงิน VIP (ส่งต่อ MD สำเนาถึง GM) --}}
          <label class="choice">
            <input type="radio" name="decision" value="deduct" {{ $decisionValue === 'deduct' ? 'checked' : '' }} onchange="toggleVip()">
            หักเงิน — กรอก{{ $deductLabel }} (ส่งต่อ GM)
          </label>
          <label class="choice">
            <input type="radio" name="decision" value="vip" {{ $decisionValue === 'vip' ? 'checked' : '' }} onchange="toggleVip()">
            ไม่หักเงิน VIP — ส่งต่อ MD (สำเนาถึง GM)
          </label>
        @endif

        <div id="deductBox">
          <label for="commission_deduct">{{ $deductLabel }} (บาท)</label>
          <input type="text" inputmode="decimal" id="commission_deduct" name="commission_deduct"
            value="{{ $deductValue }}" required
            oninput="formatComma(this)">
          @if ($suggest > 0)
            <div class="hint">
              ยอดตั้งต้นจาก เกินงบ {{ number_format($overFull, 2) }} ×
              {{ \App\Models\Salecar::OVER_BUDGET_DEDUCT_PERCENT }}% =
              <strong>{{ number_format($suggest, 2) }}</strong> — แก้ได้ถ้าต้องการหักเพิ่ม
            </div>
          @endif

          @if ($showExtra)
            <label for="extra_budget">เก็บงบเพิ่มเติม (บาท)</label>
            <input type="text" inputmode="decimal" id="extra_budget" name="extra_budget"
              value="{{ old('extra_budget', $saleCar->approval_extra_budget) }}"
              oninput="formatComma(this)">
          @endif
        </div>

        <button type="submit" id="btnSubmit">อนุมัติ และส่งต่อ {{ $decisionValue === 'vip' ? 'MD' : $finalLabel }}</button>
      @else
        <button type="submit">ยืนยันอนุมัติ</button>
      @endif
    </form>

    {{-- ตีกลับ — แจ้ง audit (config) + ฝ่ายขาย ให้แก้ไขใบจอง (ลายเซ็นทั้งหมดจะถูกรีเซ็ต) --}}
    <hr style="margin:22px 0 14px;border:none;border-top:1px solid #e5e7eb;">
    <form method="POST" action="{{ route('purchase-order.returnApproval', $token) }}">
      @csrf
      <label for="return_reason">เหตุผลที่ตีกลับ <span style="color:#6b7280;font-weight:400;">(ไม่บังคับ)</span></label>
      <textarea id="return_reason" name="return_reason" rows="2"
        placeholder="เช่น ยอดหักไม่ถูกต้อง / ข้อมูลใบจองผิด..."
        style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:8px;font-family:inherit;font-size:.95rem;"></textarea>
      <button type="submit" style="background:#dc2626;">ตีกลับให้แก้ไขใบจอง</button>
    </form>
  </div>

  <script>
    // กดแล้วโชว์ loading (กันกดซ้ำ + ระหว่างส่งเมล/สร้าง PDF) + ตัดลูกน้ำก่อนส่ง
    document.querySelectorAll('form').forEach(function (f) {
      f.addEventListener('submit', function () {
        this.querySelectorAll('input[inputmode=decimal]').forEach(inp => inp.value = inp.value.replace(/,/g, ''));
        const btn = this.querySelector('button[type=submit]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> กำลังดำเนินการ...';
      });
    });
  </script>

  @if ($showDeduct)
    <script>
      function formatComma(el) {
        let v = el.value.replace(/[^\d.]/g, '');
        const dot = v.indexOf('.');
        if (dot !== -1) v = v.slice(0, dot + 1) + v.slice(dot + 1).replace(/\./g, '');
        let [intp, dec] = v.split('.');
        intp = (intp || '').replace(/^0+(?=\d)/, '').replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        el.value = dec !== undefined ? intp + '.' + dec.slice(0, 2) : intp;
      }
      document.querySelectorAll('input[inputmode=decimal]').forEach(formatComma);
    </script>
  @endif

  @if ($showVip)
    <script>
      // VIP = ไม่หักเงิน → ซ่อนช่องยอด (ระบบบันทึกเป็น 0 ให้เอง) แล้วส่งต่อ MD
      function toggleVip() {
        const isVip = document.querySelector('input[name=decision]:checked').value === 'vip';
        document.getElementById('deductBox').style.display = isVip ? 'none' : 'block';
        document.getElementById('commission_deduct').required = !isVip;
        document.getElementById('btnSubmit').textContent = isVip ? 'อนุมัติ และส่งต่อ MD (VIP)' : 'อนุมัติ และส่งต่อ GM';
      }
      toggleVip();
    </script>
  @endif
</body>
</html>
