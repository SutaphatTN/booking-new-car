<button class="btn btn-icon btn-info btnViewCust" data-id="{{ $c->id }}" title="ดูข้อมูล">
    <i class="bx bx-show"></i>
</button>
<button class="btn btn-icon btn-warning btnEditCust" data-id="{{ $c->id }}" title="แก้ไข">
    <i class="bx bx-edit"></i>
</button>
{{-- ลบลูกค้า: admin เท่านั้น (backend กันซ้ำใน CustomerController::destroy) --}}
@if (auth()->user()->role === 'admin')
    <button class="btn btn-icon btn-danger btnDeleteCust" data-id="{{ $c->id }}" title="ลบ">
        <i class="bx bx-trash"></i>
    </button>
@endif

<style>
  .btn-icon i {
    color: white;
  }
</style>