<button class="btn btn-icon btn-info btnViewCam" data-id="{{ $c->id }}" title="ดูข้อมูล">
    <i class="bx bx-show"></i>
</button>
<button class="btn btn-icon btn-warning btnEditCam" data-id="{{ $c->id }}" title="แก้ไข">
    <i class="bx bx-edit"></i>
</button>
<button class="btn btn-icon btn-danger btnDeleteCam" data-id="{{ $c->id }}" title="ลบ">
    <i class="bx bx-trash"></i>
</button>

<style>
  .btn-icon i {
    color: white;
  }
</style>