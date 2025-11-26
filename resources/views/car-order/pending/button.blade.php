<button class="btn btn-icon btn-warning btnPendingOrder" data-id="{{ $p->id }}" title="แก้ไข">
    <i class="bx bx-edit"></i>
</button>
<button class="btn btn-icon btn-danger btnDeletePendingOrder" data-id="{{ $p->id }}" title="ลบ">
    <i class="bx bx-trash"></i>
</button>

<style>
  .btn-icon i {
    color: white;
  }
</style>