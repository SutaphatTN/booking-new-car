<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  @php $approverLabel = $approverLabel ?? 'MD'; @endphp
  <title>อนุมัติคำขอ ({{ $approverLabel }})</title>
  <style>
    body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f1f5f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; }
    .card { background: #fff; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,.08); padding: 32px; max-width: 480px; width: 100%; }
    h1 { font-size: 1.15rem; color: #0f172a; margin: 0 0 4px; text-align: center; }
    .sub { color: #64748b; font-size: .85rem; text-align: center; margin-bottom: 20px; }
    .row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px dashed #e2e8f0; font-size: .92rem; }
    .row .lbl { color: #475569; }
    .row .val { font-weight: 600; color: #0f172a; }
    label { display: block; font-size: .88rem; color: #334155; margin: 18px 0 6px; font-weight: 600; }
    input[type=text], textarea { width: 100%; padding: 13px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1rem; box-sizing: border-box; }
    input[type=text] { font-size: 1.2rem; font-weight: 600; text-align: right; }
    textarea { min-height: 70px; resize: vertical; font-family: inherit; }
    input:focus, textarea:focus { outline: none; border-color: #6c5ffc; box-shadow: 0 0 0 3px rgba(108,95,252,.15); }
    .extra { background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 10px 14px; margin-top: 14px; display: flex; justify-content: space-between; }
    .extra .lbl { color: #047857; font-weight: 600; }
    .extra .val { color: #047857; font-weight: 700; }
    button { width: 100%; margin-top: 14px; padding: 12px; color: #fff; border: none; border-radius: 8px; font-size: 1rem; cursor: pointer; }
    .btn-approve { background: #ec4899; } .btn-approve:hover { background: #db2777; }
    .btn-return { background: #f59e0b; } .btn-return:hover { background: #d97706; }
    button:disabled { opacity: .7; cursor: not-allowed; }
    .err { color: #dc2626; font-size: .82rem; margin-top: 6px; }
    .spinner { display: inline-block; width: 14px; height: 14px; border: 2px solid rgba(255,255,255,.5); border-top-color: #fff; border-radius: 50%; animation: spin .7s linear infinite; vertical-align: middle; margin-right: 6px; }
    @keyframes spin { to { transform: rotate(360deg); } }
  </style>
</head>
<body>
  @php $allowRevise = $allowRevise ?? false; @endphp
  <div class="card">
    <h1>อนุมัติคำขอเกินงบ ({{ $approverLabel }})</h1>
    <div class="sub">ส่งต่อมาขั้น {{ $approverLabel }} — อนุมัติขั้นสุดท้าย</div>

    <div class="row"><span class="lbl">ใบจอง</span><span class="val">{{ $saleCar->order_code ?? $saleCar->id }}</span></div>
    <div class="row"><span class="lbl">รุ่นรถ</span><span class="val">{{ $saleCar->model->Name_TH ?? '-' }}</span></div>
    <div class="row"><span class="lbl">ลูกค้า</span><span class="val">{{ $saleCar->customer->FirstName ?? '' }} {{ $saleCar->customer->LastName ?? '' }}</span></div>
    <div class="row"><span class="lbl">ยอดที่เหลือ</span><span class="val">{{ number_format($saleCar->approval_remaining ?? 0, 2) }}</span></div>
    <div class="row"><span class="lbl">ยอดหักค่าคอม (ผู้จัดการกรอก)</span><span class="val">{{ number_format($saleCar->approval_commission_deduct ?? 0, 2) }}</span></div>

    @if ($errors->any())
      <div class="err">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('purchase-order.finalApprove', $token) }}">
      @csrf

      @if ($allowRevise)
        <label for="commission_deduct">ยอดหักค่าคอมฝ่ายขาย (แก้ได้ก่อนอนุมัติ)</label>
        <input type="text" inputmode="decimal" id="commission_deduct" name="commission_deduct"
          value="{{ old('commission_deduct', $saleCar->approval_commission_deduct) }}"
          oninput="formatComma(this); calcExtra()">

        <div class="extra">
          <span class="lbl">เก็บงบเพิ่มเติม (ยอดที่เหลือ − หักค่าคอม)</span>
          <span class="val" id="extraVal">-</span>
        </div>

        <label for="md_note">เหตุผล/โน้ตถึงผู้จัดการ (ไม่บังคับ)</label>
        <textarea id="md_note" name="md_note" placeholder="เช่น ยอดหักน้อยไป ขอให้ทบทวน...">{{ old('md_note') }}</textarea>

        {{-- decision เก็บใน hidden (กันค่าหายเมื่อปุ่มโดน disable ตอน submit) --}}
        <input type="hidden" name="decision" id="decisionField" value="approve">
        <button type="submit" data-decision="approve" class="btn-approve">อนุมัติ (ใช้ยอดนี้)</button>
        <button type="submit" data-decision="return" class="btn-return">ส่งกลับให้ผู้จัดการแก้</button>
      @else
        <input type="hidden" name="decision" value="approve">
        <button type="submit" class="btn-approve">ยืนยันอนุมัติ ({{ $approverLabel }})</button>
      @endif
    </form>
  </div>

  <script>
    const form = document.querySelector('form');
    const deductInput = document.getElementById('commission_deduct');

    @if ($allowRevise)
      const remaining = {{ (float) ($saleCar->approval_remaining ?? 0) }};
      function formatComma(el) {
        let v = el.value.replace(/[^\d.]/g, '');
        const dot = v.indexOf('.');
        if (dot !== -1) v = v.slice(0, dot + 1) + v.slice(dot + 1).replace(/\./g, '');
        let [intp, dec] = v.split('.');
        intp = (intp || '').replace(/^0+(?=\d)/, '').replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        el.value = dec !== undefined ? intp + '.' + dec.slice(0, 2) : intp;
      }
      function calcExtra() {
        const d = parseFloat(deductInput.value.replace(/,/g, '')) || 0;
        document.getElementById('extraVal').textContent =
          (remaining - d).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      }
      formatComma(deductInput);
      calcExtra();

      // ตั้งค่า decision จากปุ่มที่กด (เก็บใน hidden field กันค่าหายตอน disable ปุ่ม)
      document.querySelectorAll('button[type=submit][data-decision]').forEach(function (b) {
        b.addEventListener('click', function () {
          document.getElementById('decisionField').value = b.dataset.decision;
        });
      });
    @endif

    form.addEventListener('submit', function (e) {
      if (deductInput) deductInput.value = deductInput.value.replace(/,/g, '');
      const btn = e.submitter || this.querySelector('button[type=submit]');
      if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> กำลังดำเนินการ...';
      }
    });
  </script>
</body>
</html>
