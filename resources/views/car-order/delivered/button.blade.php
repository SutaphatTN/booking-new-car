{{-- ปุ่มของหน้า "รถที่ส่งมอบ" — ใช้ handler เดียวกับหน้ารายการรถ (car-order.js) --}}
<button class="btn btn-icon btn-info btnViewCarOrder" data-id="{{ $c->id }}" title="ดูข้อมูล">
  <i class="bx bx-show"></i>
</button>
@if ($canEdit)
  {{-- แก้ไขได้เฉพาะ admin (เผื่อต้องแก้สถานะรถย้อนหลัง) --}}
  <button class="btn btn-icon btn-warning btnEditCarOrder" data-id="{{ $c->id }}" title="แก้ไข">
    <i class="bx bx-edit"></i>
  </button>
@endif

<style>
  .btn-icon i {
    color: white;
  }
</style>
