@extends('layouts/contentNavbarLayout')
@section('title', 'แหล่งที่มาย่อย')

@section('page-script')
@vite(['resources/assets/js/source.js'])
@endsection

@section('content')
<div class="inputSubModal"></div>
<div class="editSubModal"></div>

<div class="row">
  <div class="col-12">
    <div class="card tbl-card">

      {{-- ── Card header ── --}}
      <div class="po-card-header d-flex align-items-center gap-3">
        <div class="po-hd-icon">
          <i class="bx bx-sitemap fs-4 text-white"></i>
        </div>
        <div>
          <div class="text-white fw-bold mf-hd-title">แหล่งที่มาย่อย</div>
          <div class="text-white mf-hd-sub">Sub-source</div>
        </div>
      </div>

      <div class="card-body pt-3">

        <div class="po-filter-bar d-flex align-items-center justify-content-end">
          <button class="btn btn-secondary btn-sm btnInputSub">
            <i class="bx bx-plus me-1"></i> เพิ่มแหล่งที่มาย่อย
          </button>
        </div>

        <div class="table-responsive">
          <table class="table table-bordered tbl-table tbl-styled subSourceTable">
            <thead>
              <tr>
                <th class="tbl-th-no">No.</th>
                <th>ชื่อแหล่งที่มาย่อย</th>
                <th style="width:160px;">แหล่งที่มาหลัก</th>
                <th class="tbl-th-action" style="width:120px;">Action</th>
              </tr>
            </thead>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection
