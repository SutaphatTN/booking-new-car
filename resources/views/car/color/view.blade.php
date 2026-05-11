@extends('layouts/contentNavbarLayout')
@section('title', 'Data Color sub-Model Car')

@section('page-script')
@vite(['resources/assets/js/color.js'])
@endsection

@section('content')
<div class="inputColorSubModal"></div>
<div class="editColorSubModal"></div>
<div class="row">
  <div class="col-12">
    <div class="card tbl-card">

      {{-- ── Card header ── --}}
      <div class="po-card-header d-flex align-items-center gap-3">
        <div class="po-hd-icon">
          <i class="bx bx-palette fs-4 text-white"></i>
        </div>
        <div>
          <div class="text-white fw-bold mf-hd-title">ข้อมูลสีรถของแต่ละรุ่นย่อย</div>
          <div class="text-white mf-hd-sub">Color Sub-Model</div>
        </div>
      </div>

      <div class="card-body pt-3">

        {{-- ── Action bar ── --}}
        <div class="po-filter-bar d-flex align-items-center justify-content-end">
          <button class="btn btn-secondary btn-sm btnInputColorSub">
            <i class="bx bx-plus me-1"></i> เพิ่ม
          </button>
        </div>

        {{-- ── Table ── --}}
        <div class="table-responsive">
          <table class="table table-bordered tbl-table tbl-styled ColorSubTable">
            <thead>
              <tr>
                <th class="tbl-th-no">No.</th>
                <th>รุ่นหลัก</th>
                <th>รุ่นย่อย</th>
                <th>สี</th>
                <th class="tbl-th-action" style="width:150px;">Action</th>
              </tr>
            </thead>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection
