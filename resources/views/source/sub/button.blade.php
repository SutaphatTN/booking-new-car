<button class="btn btn-icon btn-warning btnEditSub" data-id="{{ $s->id }}" title="แก้ไข">
    <i class="bx bx-edit"></i>
</button>
@if (Auth::user()->role === 'admin')
    <button class="btn btn-icon btn-danger btnDeleteSub" data-id="{{ $s->id }}" title="ลบ">
        <i class="bx bx-trash"></i>
    </button>
@endif

<style>
    .btn-icon i {
        color: white;
    }
</style>
