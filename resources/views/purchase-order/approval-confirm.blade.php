<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>อนุมัติคำขอ (GM)</title>
  <style>
    body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f1f5f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; }
    .card { background: #fff; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,.08); padding: 32px; max-width: 460px; width: 100%; }
    h1 { font-size: 1.15rem; color: #0f172a; margin: 0 0 4px; text-align: center; }
    .sub { color: #64748b; font-size: .85rem; text-align: center; margin-bottom: 20px; }
    .row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px dashed #e2e8f0; font-size: .92rem; }
    .row .lbl { color: #475569; }
    .row .val { font-weight: 600; color: #0f172a; }
    button { width: 100%; margin-top: 22px; padding: 12px; background: #ec4899; color: #fff; border: none; border-radius: 8px; font-size: 1rem; cursor: pointer; }
    button:hover { background: #db2777; }
    button:disabled { opacity: .7; cursor: not-allowed; }
    .spinner { display: inline-block; width: 14px; height: 14px; border: 2px solid rgba(255,255,255,.5); border-top-color: #fff; border-radius: 50%; animation: spin .7s linear infinite; vertical-align: middle; margin-right: 6px; }
    @keyframes spin { to { transform: rotate(360deg); } }
  </style>
</head>
<body>
  <div class="card">
    <h1>อนุมัติคำขอเกินงบ (MD)</h1>
    <div class="sub">ส่งต่อมาขั้น MD — อนุมัติขั้นสุดท้าย</div>

    <div class="row"><span class="lbl">ใบจอง</span><span class="val">{{ $saleCar->order_code ?? $saleCar->id }}</span></div>
    <div class="row"><span class="lbl">รุ่นรถ</span><span class="val">{{ $saleCar->model->Name_TH ?? '-' }}</span></div>
    <div class="row"><span class="lbl">ลูกค้า</span><span class="val">{{ $saleCar->customer->FirstName ?? '' }} {{ $saleCar->customer->LastName ?? '' }}</span></div>
    <div class="row"><span class="lbl">ยอดที่เหลือ</span><span class="val">{{ number_format($saleCar->approval_remaining ?? 0, 2) }}</span></div>
    <div class="row"><span class="lbl">หักค่าคอมฝ่ายขาย</span><span class="val">{{ number_format($saleCar->approval_commission_deduct ?? 0, 2) }}</span></div>
    <div class="row"><span class="lbl">เก็บงบเพิ่มเติม</span><span class="val">{{ number_format(($saleCar->approval_remaining ?? 0) - ($saleCar->approval_commission_deduct ?? 0), 2) }}</span></div>

    <form method="POST" action="{{ route('purchase-order.finalApprove', $token) }}">
      @csrf
      <button type="submit">ยืนยันอนุมัติ (MD)</button>
    </form>
  </div>

  <script>
    document.querySelector('form').addEventListener('submit', function () {
      const btn = this.querySelector('button[type=submit]');
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner"></span> กำลังดำเนินการ...';
    });
  </script>
</body>
</html>
