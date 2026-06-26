@php
  // ค่าเริ่มต้น: แสดงความคืบหน้า (กรอกแล้ว x/y ข้อ)
  $bClass = 'bg-secondary';
  $bText  = 'กรอกแล้ว ' . $ssiInfo['answered'] . '/' . $ssiInfo['total'] . ' ข้อ';
  $bShow  = $ssiInfo['total'] > 0 && $ssiInfo['answered'] > 0;

  // ถ้ากรอกครบ → แสดงคะแนนรวม % (สีแดงถ้า < 90%)
  if ($ssiInfo['complete'] && $ssiScore !== null) {
      $bClass = $ssiScore < 90 ? 'bg-danger' : 'bg-success';
      $bText  = 'คะแนนรวม ' . $ssiScore . '%';
  }
@endphp
<div class="po-section-header-end">
  <span id="ssiScoreBadge" class="badge {{ $bClass }}" style="font-size:.8rem;{{ $bShow ? '' : 'display:none;' }}">{{ $bText }}</span>
</div>
