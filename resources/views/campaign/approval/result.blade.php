<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ผลการอนุมัติแคมเปญ</title>
  <style>
    body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f1f5f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; }
    .card { background: #fff; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,.08); padding: 40px; max-width: 520px; width: 100%; text-align: center; }
    .icon { width: 72px; height: 72px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 40px; margin: 0 auto 20px; }
    .ok { background: #ecfdf5; color: #059669; }
    .back { background: #fffbeb; color: #d97706; }
    h1 { font-size: 1.2rem; color: #0f172a; margin: 0 0 8px; }
    p { color: #475569; font-size: .95rem; margin: 6px 0; }
    ul { text-align: left; margin: 14px 0 0; padding: 0 0 0 18px; color: #475569; font-size: .9rem; }
    li { margin: 4px 0; }
    @media (max-width: 480px) {
      body { padding: 0; align-items: flex-start; }
      .card { border-radius: 0; padding: 28px 16px; min-height: 100vh; box-shadow: none; }
    }
  </style>
</head>
<body>
  @php $sentBack = $approvals->where('status', 'approved')->isEmpty() && $approvals->where('status', 'rejected')->isNotEmpty(); @endphp
  <div class="card">
    <div class="icon {{ $sentBack ? 'back' : 'ok' }}">{!! $sentBack ? '&#8617;' : '&#10004;' !!}</div>
    <h1>{{ $msg }}</h1>
    <p>เดือน: {{ $period }}</p>
    <ul>
      @foreach ($approvals as $ap)
        <li>
          {{ $ap->campaign->appellation->name ?? '-' }} ({{ $ap->campaign->type->name ?? '-' }})
          — {{ number_format($ap->campaign->cashSupport_final ?? 0, 2) }} บาท
          @if ($ap->status === 'approved') ✅
          @elseif ($ap->status === 'rejected') ↩️ ส่งกลับแก้ไข
          @endif
        </li>
      @endforeach
    </ul>
    <p style="margin-top:18px; font-size:.85rem; color:#94a3b8;">วันที่ดำเนินการ: {{ now()->format('d/m/Y H:i') }}</p>
  </div>
</body>
</html>
