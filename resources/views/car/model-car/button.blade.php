<button class="btn btn-icon btn-warning btnEditCar" data-id="{{ $c->id }}" title="แก้ไข">
  <i class="bx bx-edit"></i>
</button>
<button class="btn btn-icon btn-danger btnDeleteCar" data-id="{{ $c->id }}" title="ลบ">
  <i class="bx bx-trash"></i>
</button>

<style>
  .btn-icon i {
    color: white;
  }
</style>