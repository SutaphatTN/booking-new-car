@if ($inc)
  <button type="button" class="btn btn-icon btn-warning btnEditGwmIncentive"
    data-id="{{ $inc->id }}" title="แก้ไข">
    <i class="bx bx-edit" style="color:white"></i>
  </button>
  <button type="button" class="btn btn-icon btn-danger btnDeleteGwmIncentive"
    data-id="{{ $inc->id }}" title="ลบ">
    <i class="bx bx-trash" style="color:white"></i>
  </button>
@else
  <button type="button" class="btn btn-sm btn-outline-primary btnAddGwmIncentive"
    data-sub-id="{{ $sub->id }}"
    data-month="{{ $month }}"
    data-year="{{ $year }}"
    title="เพิ่มข้อมูล">
    <i class="bx bx-plus me-1"></i>เพิ่ม
  </button>
@endif
