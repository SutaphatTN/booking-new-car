<button class="btn btn-icon btn-info btnViewUser" data-id="{{ $u->id }}" title="ดูข้อมูล">
    <i class="bx bx-show"></i>
</button>
<button class="btn btn-icon btn-warning btnEditUser" data-id="{{ $u->id }}" title="แก้ไข">
    <i class="bx bx-edit"></i>
</button>
<button class="btn btn-icon btn-danger btnDeleteUser" data-id="{{ $u->id }}" title="ลบ">
    <i class="bx bx-trash"></i>
</button>

<style>
  .btn-icon i {
    color: white;
  }
</style>