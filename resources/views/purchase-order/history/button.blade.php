<button class="btn btn-icon btn-info btnViewHistory" data-id="{{ $s->id }}" title="preview">
  <i class="bx bx-show"></i></a>
</button>

<a href="{{ route('purchase-order.edit', [$s->id, 'from' => 'history']) }}"
  class="btn btn-icon btn-success"
  title="ดูข้อมูล">
  <i class="bx bx-folder-open"></i>
</a>
@php
$hasRemaining = !empty($s->remainingPayment);
@endphp

<a
  href="{{ $hasRemaining ? route('purchase-order.summary', $s->id) : 'javascript:void(0)' }}"
  target="{{ $hasRemaining ? '_blank' : '' }}"
  class="btn btn-icon btn-primary {{ !$hasRemaining ? 'disabled-link' : '' }}"
  title="{{ !$hasRemaining ? 'ยังไม่มีข้อมูลค่างวด' : 'สรุปค่าใช้จ่าย' }}">
  <i class="bx bx-printer"></i>
</a>

@if (auth()->user()->role === 'admin')
  <button class="btn btn-icon btn-warning btnChangeStatus" data-id="{{ $s->id }}"
    data-status="{{ $s->con_status }}" title="ดึงกลับ / เปลี่ยนสถานะ">
    <i class="bx bx-transfer"></i>
  </button>
@endif

<style>
  .btn-icon i {
    color: white;
  }

  .disabled-link {
    pointer-events: none;
    opacity: 0.5;
    cursor: not-allowed;
  }
</style>