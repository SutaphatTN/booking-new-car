<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>อนุมัติคำขอ (GM)</title>
  <style>
    body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f1f5f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; }
    .card { background: #fff; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,.08); padding: 32px; max-width: 480px; width: 100%; }
    h1 { font-size: 1.15rem; color: #0f172a; margin: 0 0 4px; text-align: center; }
    .sub { color: #64748b; font-size: .85rem; text-align: center; margin-bottom: 20px; }
    .row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px dashed #e2e8f0; font-size: .92rem; }
    .row .lbl { color: #475569; } .row .val { font-weight: 600; color: #0f172a; }
    .choice { display: block; margin: 14px 0 4px; font-size: .95rem; color: #334155; cursor: pointer; }
    label.field { display: block; font-size: .88rem; color: #334155; margin: 14px 0 6px; font-weight: 600; }
    input[type=number], input[type=text] { width: 100%; padding: 13px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1.2rem; font-weight: 600; text-align: right; box-sizing: border-box; }
    input:focus { outline: none; border-color: #ec4899; box-shadow: 0 0 0 3px rgba(236,72,153,.15); }
    .extra { background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 10px 14px; margin-top: 14px; display: flex; justify-content: space-between; }
    .extra .lbl { color: #047857; font-weight: 600; } .extra .val { color: #047857; font-weight: 700; }
    button { width: 100%; margin-top: 22px; padding: 12px; background: #ec4899; color: #fff; border: none; border-radius: 8px; font-size: 1rem; cursor: pointer; }
    button:hover { background: #db2777; }
    .err { color: #dc2626; font-size: .82rem; margin-top: 6px; }
    button:disabled { opacity: .7; cursor: not-allowed; }
    .spinner { display: inline-block; width: 14px; height: 14px; border: 2px solid rgba(255,255,255,.5); border-top-color: #fff; border-radius: 50%; animation: spin .7s linear infinite; vertical-align: middle; margin-right: 6px; }
    @keyframes spin { to { transform: rotate(360deg); } }
  </style>
</head>
<body>
  <div class="card">
    <h1>อนุมัติคำขอเกินงบ (GM)</h1>
    <div class="sub">เลือก: หักเงิน (จบที่ GM) หรือ ส่งต่อ md</div>

    <div class="row"><span class="lbl">ใบจอง</span><span class="val">{{ $saleCar->order_code ?? $saleCar->id }}</span></div>
    <div class="row"><span class="lbl">รุ่นรถ</span><span class="val">{{ $saleCar->model->Name_TH ?? '-' }}</span></div>
    <div class="row"><span class="lbl">ลูกค้า</span><span class="val">{{ $saleCar->customer->FirstName ?? '' }} {{ $saleCar->customer->LastName ?? '' }}</span></div>
    <div class="row"><span class="lbl">ยอดที่เหลือ</span><span class="val">{{ number_format($saleCar->approval_remaining ?? 0, 2) }}</span></div>

    @if ($errors->any())
      <div class="err">{{ $errors->first() }}</div>
    @endif

    @php
      // ยอดหักคอมแนะนำ = สูตรเดียวกับ displayBalanceCamHalf ในหน้า summary
      // (balanceCampaign * 2 * per_budget%) — ใช้ค่าสัมบูรณ์เพราะช่องนี้รับค่าบวก
      $balCam = $saleCar->balanceCampaign ?? null;
      $perBudget = $saleCar->model->per_budget ?? 0;
      $suggestDeduct = (is_numeric($balCam) && $balCam < 0)
          ? abs($balCam * 2 * ($perBudget / 100))
          : 0;
      // ยังไม่เคยตัดสินใจ → เติมยอดแนะนำให้แก้ไขได้; ถ้าเคยกรอกไว้แล้วใช้ค่าเดิม
      $defaultDeduct = $saleCar->approval_commission_deduct
          ?? ($suggestDeduct > 0 ? number_format($suggestDeduct, 2) : '');
    @endphp

    <form method="POST" action="{{ route('purchase-order.gmDecide', $token) }}">
      @csrf
      <label class="choice"><input type="radio" name="decision" value="deduct" checked onchange="toggle()"> หักเงิน (จบที่ GM)</label>
      <label class="choice"><input type="radio" name="decision" value="forward" onchange="toggle()"> ไม่หักเงิน VIP → ส่งต่อ MD</label>

      <div id="deductBox">
        <label class="field" for="commission_deduct">ยอดหักค่าคอมฝ่ายขาย (บาท)</label>
        <input type="text" inputmode="decimal" id="commission_deduct" name="commission_deduct"
          value="{{ old('commission_deduct', $defaultDeduct) }}" oninput="formatComma(this); calcExtra()">
        <div class="extra">
          <span class="lbl">เก็บงบเพิ่มเติม (ยอดที่เหลือ − หักค่าคอม)</span>
          <span class="val" id="extraVal">-</span>
        </div>
      </div>

      <button type="submit" id="btnSubmit">ยืนยัน — หักเงิน (จบที่ GM)</button>
    </form>
  </div>

  <script>
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
      const d = parseFloat(document.getElementById('commission_deduct').value.replace(/,/g, '')) || 0;
      document.getElementById('extraVal').textContent =
        (remaining - d).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    function toggle() {
      const isDeduct = document.querySelector('input[name=decision]:checked').value === 'deduct';
      document.getElementById('deductBox').style.display = isDeduct ? 'block' : 'none';
      document.getElementById('commission_deduct').required = isDeduct;
      document.getElementById('btnSubmit').textContent = isDeduct ? 'ยืนยัน — หักเงิน (จบที่ GM)' : 'ยืนยัน — ส่งต่อ MD';
    }
    toggle(); calcExtra();

    // กดแล้วโชว์ loading (กันกดซ้ำ + ระหว่างส่งเมล/สร้าง PDF) + ตัดลูกน้ำก่อนส่ง
    document.querySelector('form').addEventListener('submit', function () {
      const inp = document.getElementById('commission_deduct');
      if (inp) inp.value = inp.value.replace(/,/g, '');
      const btn = document.getElementById('btnSubmit');
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner"></span> กำลังดำเนินการ...';
    });
  </script>
</body>
</html>
