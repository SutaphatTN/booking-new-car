<button class="btn btn-icon btn-success btnViewHistory" data-id="{{ $s->id }}" title="preview">
  <i class="bx bx-note"></i></a>
</button>

<a href="{{ route('purchase-order.edit', $s->id) }}"
  class="btn btn-icon btn-info"
  title="ดูข้อมูล">
  <i class="bx bx-show"></i>
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