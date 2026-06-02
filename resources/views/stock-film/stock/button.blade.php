<button class="btn btn-icon btn-info btnViewFilm" data-id="{{ $s->id }}" title="ดูข้อมูล">
    <i class="bx bx-show"></i>
</button>
<button class="btn btn-icon btn-warning btnEditFilm" data-id="{{ $s->id }}" title="แก้ไข">
    <i class="bx bx-edit"></i>
</button>
<button class="btn btn-icon btn-danger btnDeleteFilm" data-id="{{ $s->id }}" title="ลบ">
    <i class="bx bx-trash"></i>
</button>

<style>
  .btn-icon i { color: white; }
</style>
