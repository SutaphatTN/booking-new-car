@if ($p->status === \App\Models\SourcePlace::STATUS_APPROVED)
    <button class="btn btn-icon btn-success btnClearPlace" data-id="{{ $p->id }}" title="เคลียร์ค่าใช้จ่าย">
        <i class="bx bx-receipt"></i>
    </button>
@endif
{{-- PDF ข้อมูลเคลียร์ (แนบรายการทำจ่าย) — แสดงเมื่ออนุมัติแล้ว หรือมีข้อมูลเคลียร์แล้ว --}}
@if ($p->status === \App\Models\SourcePlace::STATUS_APPROVED || $p->clears->count())
    <a class="btn btn-icon btn-danger" href="{{ route('source.place.clear.pdf', $p->id) }}" target="_blank" title="PDF ข้อมูลเคลียร์">
        <i class="bx bxs-file-pdf"></i>
    </a>
@endif
<button class="btn btn-icon btn-warning btnEditPlace" data-id="{{ $p->id }}" title="แก้ไข">
    <i class="bx bx-edit"></i>
</button>
{{-- ส่งขออนุมัติแล้ว (รออนุมัติ/อนุมัติแล้ว) ห้ามลบ — แสดงปุ่มลบเฉพาะฉบับร่าง/ถูกส่งกลับ --}}
@if (!in_array($p->status, [\App\Models\SourcePlace::STATUS_PENDING, \App\Models\SourcePlace::STATUS_APPROVED]))
    <button class="btn btn-icon btn-danger btnDeletePlace" data-id="{{ $p->id }}" title="ลบ">
        <i class="bx bx-trash"></i>
    </button>
@endif

<style>
    .btn-icon i {
        color: white;
    }
</style>
