<!-- <button class="btn btn-icon btn-info btnViewSale" data-id="{{ $s->id }}" title="ดูข้อมูล">
  <i class="bx bx-show"></i></a>
</button> -->
<a href="{{ route('purchase-order.edit', $s->id) }}"
  class="btn btn-icon btn-warning"
  title="แก้ไข">
  <i class="bx bx-edit"></i>
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

<button class="btn btn-icon btn-danger btnDeleteSale" data-id="{{ $s->id }}" title="ลบ">
  <i class="bx bx-trash"></i>
</button>

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