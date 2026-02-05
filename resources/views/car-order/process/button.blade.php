<button class="btn btn-icon btn-info btnProcessCarOrder" data-id="{{ $p->id }}" title="ดูรายละเอียด">
  <i class="bx bx-show"></i>
</button>

<button class="btn btn-icon btn-success btnApproveProcess" data-id="{{ $p->id }}" title="อนุมัติ">
  <i class="bx bx-check-circle"></i>
</button>

<button class="btn btn-icon btn-danger btnRejectProcess" data-id="{{ $p->id }}"  data-has-salecar="{{ $p->salecar_id ? 1 : 0 }}" title="ไม่อนุมัติ">
  <i class="bx bx-x-circle"></i>
</button>

<style>
  .btn-icon i {
    color: white;
  }
</style>