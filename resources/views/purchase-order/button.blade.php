<button class="btn btn-icon btn-info btnViewSale" data-id="{{ $s->id }}" title="ดูข้อมูล">
  <i class="bx bx-show"></i></a>
</button>
<button class="btn btn-icon btn-warning" title="แก้ไข">
  <a class="nav-link" href="{{ route('purchase-order.edit', $s->id) }}"><i class="bx bx-edit"></i></a>
</button>
<button class="btn btn-icon btn-primary" title="สรุปค่าใช้จ่าย">
  <a class="nav-link" href="{{ route('purchase-order.summary', $s->id) }}" target="_blank"><i class="bx bx-printer"></i></a>
</button>
<button class="btn btn-icon btn-danger btnDeleteSale" data-id="{{ $s->id }}" title="ลบ">
  <i class="bx bx-trash"></i>
</button>