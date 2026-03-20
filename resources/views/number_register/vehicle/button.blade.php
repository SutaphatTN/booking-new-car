@php
    $disabled = empty($s->vehicleLicense?->withdrawal_date);
@endphp

<button class="btn btn-icon btn-info btnViewVehicle" data-id="{{ $s->id }}" title="ดูข้อมูล" {{ $disabled ? 'disabled' : '' }}>
    <i class="bx bx-show"></i>
</button>
<button class="btn btn-icon btn-warning btnEditVehicle" data-id="{{ $s->id }}" title="แก้ไข" {{ $disabled ? 'disabled' : '' }}>
    <i class="bx bx-edit"></i>
</button>
<!-- <button class="btn btn-icon btn-danger btnDeleteFN" data-id="{{ $s->id }}" title="ลบ">
    <i class="bx bx-trash"></i>
</button> -->

<style>
  .btn-icon i {
    color: white;
  }
</style>