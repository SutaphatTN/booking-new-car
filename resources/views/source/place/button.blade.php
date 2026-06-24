@if ($p->status === \App\Models\SourcePlace::STATUS_APPROVED)
    <button class="btn btn-icon btn-success btnClearPlace" data-id="{{ $p->id }}" title="เคลียร์ค่าใช้จ่าย">
        <i class="bx bx-receipt"></i>
    </button>
@endif
<button class="btn btn-icon btn-warning btnEditPlace" data-id="{{ $p->id }}" title="แก้ไข">
    <i class="bx bx-edit"></i>
</button>
<button class="btn btn-icon btn-danger btnDeletePlace" data-id="{{ $p->id }}" title="ลบ">
    <i class="bx bx-trash"></i>
</button>

<style>
    .btn-icon i {
        color: white;
    }
</style>
