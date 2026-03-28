<button class="btn btn-icon btn-info btnViewCancellation" data-id="{{ $s->id }}" title="ดูข้อมูล">
  <i class="bx bx-show"></i>
</button>

<button class="btn btn-icon btn-warning btnEditCancellation" data-id="{{ $s->id }}" title="แก้ไขข้อมูล">
  <i class="bx bx-edit"></i>
</button>

<a href="{{ route('purchase-order.edit', $s->id) }}" class="btn btn-icon btn-success" title="ดูข้อมูล">
  <i class="bx bx-folder-open"></i>
</a>

@if (!empty($s->withdraw_attachment_url) && in_array(Auth::user()->role, ['admin', 'account']))
  <button class="btn btn-icon btn-success btnConfirmWithdraw" data-id="{{ $s->id }}" title="ยืนยันการคืนเงิน">
    <i class="bx bx-check-circle"></i>
  </button>
@endif

<style>
  .btn-icon i {
    color: white;
  }
</style>
