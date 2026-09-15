@php
    $disabled = empty($s->vehicleLicense?->withdrawal_date);
    // role ดูอย่างเดียว (insurance_reg) — เห็นแต่ปุ่มดูข้อมูล ไม่มีปุ่มแก้ไข
    $viewOnly = auth()->user()->isRegistrationViewOnly();
@endphp

<button class="btn btn-icon btn-info btnViewVehicle" data-id="{{ $s->id }}" title="ดูข้อมูล">
    <i class="bx bx-show"></i>
</button>
@unless ($viewOnly)
<button class="btn btn-icon btn-warning btnEditVehicle" data-id="{{ $s->id }}" title="แก้ไข" {{ $disabled ? 'disabled' : '' }}>
    <i class="bx bx-edit"></i>
</button>
@endunless
<!-- <button class="btn btn-icon btn-danger btnDeleteFN" data-id="{{ $s->id }}" title="ลบ">
    <i class="bx bx-trash"></i>
</button> -->

<style>
  .btn-icon i {
    color: white;
  }
</style>
