<button class="btn btn-icon btn-info btnViewAcc" data-id="{{ $a->id }}" title="ดูข้อมูล">
    <i class="bx bx-show"></i>
</button>
<button class="btn btn-icon btn-warning btnEditAcc" data-id="{{ $a->id }}" title="แก้ไข">
    <i class="bx bx-edit"></i>
</button>
<button class="btn btn-icon btn-danger btnDeleteAcc" data-id="{{ $a->id }}" title="ลบ">
    <i class="bx bx-trash"></i>
</button>

<style>
  .btn-icon i {
    color: white;
  }
</style>