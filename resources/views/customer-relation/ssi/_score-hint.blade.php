@if ($ssiIsLow)
  <div class="alert {{ $hasResDate ? 'alert-success' : 'alert-warning' }} d-flex align-items-start gap-2 py-2 mb-3"
    style="font-size:.85rem;">
    <i class="bx {{ $hasResDate ? 'bx-check-circle' : 'bx-error-circle' }} fs-5"></i>
    <div>
      คะแนน SSI ต่ำกว่า 90% ({{ $ssiScore }}%)
      @if ($hasResDate)
        — มีวันที่แก้ไขปัญหาแล้ว สามารถกด "ตรวจสอบเสร็จแล้ว" ได้
      @else
        — กรุณาระบุ <strong>วันที่แก้ไขปัญหา</strong> (การ์ดการจัดการร้องเรียนด้านล่าง) ก่อน จึงจะกด "ตรวจสอบเสร็จแล้ว" ได้
      @endif
    </div>
  </div>
@endif
