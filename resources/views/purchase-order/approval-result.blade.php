<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ผลการอนุมัติ</title>
  <style>
    body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f1f5f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
    .card { background: #fff; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,.08); padding: 40px; max-width: 460px; width: 90%; text-align: center; }
    .icon { width: 72px; height: 72px; border-radius: 50%; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 40px; margin: 0 auto 20px; }
    h1 { font-size: 1.2rem; color: #0f172a; margin: 0 0 8px; }
    p { color: #475569; font-size: .95rem; margin: 6px 0; }
    .code { color: #64748b; font-size: .85rem; }
  </style>
</head>
<body>
  <div class="card">
    <div class="icon">&#10004;</div>
    <h1>{{ $msg }}</h1>
    <p class="code">ใบจอง: {{ $saleCar->order_code ?? $saleCar->id }}</p>
    <p>รุ่น: {{ $saleCar->model->Name_TH ?? '-' }}</p>
    <p>ลูกค้า: {{ $saleCar->customer->FirstName ?? '' }} {{ $saleCar->customer->LastName ?? '' }}</p>
    <p style="margin-top:18px; font-size:.85rem; color:#94a3b8;">วันที่อนุมัติ: {{ now()->format('d/m/Y H:i') }}</p>
  </div>
</body>
</html>
