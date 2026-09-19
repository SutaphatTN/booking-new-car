@php
    // ลูกค้าไปจดทะเบียนเอง = ไม่ต้องส่งเบิก กรอกป้ายขาวได้เลย (ปุ่มแก้ไขจึงเปิดให้ทั้งที่ยังไม่มีวันตั้งเบิก)
    $selfRegistered = $s->vehicleLicense?->isSelfRegistered() ?? false;
    $disabled = empty($s->vehicleLicense?->withdrawal_date) && !$selfRegistered;
    // role ดูอย่างเดียว (insurance_reg) — เห็นแต่ปุ่มดูข้อมูล ไม่มีปุ่มแก้ไข
    $viewOnly = auth()->user()->isRegistrationViewOnly();
    // ส่งเบิกไปแล้วเปลี่ยนเป็น "ลูกค้าจดเอง" ไม่ได้ — เงินออกไปแล้วแปลว่าบริษัทจดให้
    $canMarkSelf = !$viewOnly && !$selfRegistered && empty($s->vehicleLicense?->withdrawal_date);
    // กดผิดแล้วคืนค่าได้เฉพาะ admin / registration
    $canUnmarkSelf = $selfRegistered && in_array(auth()->user()->role, ['admin', 'registration']);
@endphp

<button class="btn btn-icon btn-info btnViewVehicle" data-id="{{ $s->id }}" title="ดูข้อมูล">
    <i class="bx bx-show"></i>
</button>
@unless ($viewOnly)
<button class="btn btn-icon btn-warning btnEditVehicle" data-id="{{ $s->id }}" title="แก้ไข" {{ $disabled ? 'disabled' : '' }}>
    <i class="bx bx-edit"></i>
</button>
@endunless
@if ($canMarkSelf)
<button class="btn btn-icon btn-secondary btnMarkSelfRegister" data-id="{{ $s->id }}" title="ลูกค้าจดทะเบียนเอง">
    <i class="bx bx-user-check"></i>
</button>
@endif
@if ($canUnmarkSelf)
<button class="btn btn-icon btn-danger btnUnmarkSelfRegister" data-id="{{ $s->id }}" title="ยกเลิก &quot;ลูกค้าจดทะเบียนเอง&quot;">
    <i class="bx bx-undo"></i>
</button>
@endif
<!-- <button class="btn btn-icon btn-danger btnDeleteFN" data-id="{{ $s->id }}" title="ลบ">
    <i class="bx bx-trash"></i>
</button> -->

<style>
  .btn-icon i {
    color: white;
  }
</style>
