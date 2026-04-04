<button class="btn btn-icon btn-info btnViewWaiting"
  data-id="{{ $w->id }}"
  title="ดูรายละเอียด">
  <i class="bx bx-show"></i>
</button>

<button class="btn btn-icon btn-success btnApproveWaiting"
  data-id="{{ $w->id }}"
  data-count-order="{{ $w->count_order }}"
  title="อนุมัติ">
  <i class="bx bx-check-circle"></i>
</button>

<button class="btn btn-icon btn-danger btnRejectWaiting"
  data-id="{{ $w->id }}"
  title="ไม่อนุมัติ">
  <i class="bx bx-x-circle"></i>
</button>

<style>
  .btn-icon i {
    color: white;
  }
</style>
