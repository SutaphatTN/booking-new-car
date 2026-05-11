@extends('layouts/contentNavbarLayout')
@section('title', 'Data Accessory')

@section('page-script')
@vite(['resources/assets/js/accessory.js'])
@endsection

@section('content')
<div class="viewMoreAccModal"></div>
<div class="inputAccModal"></div>
<div class="editAccModal"></div>
<div class="row">
  <div class="col-12">
    <div class="card tbl-card">

      {{-- ── Card header ── --}}
      <div class="po-card-header d-flex align-items-center gap-3">
        <div class="po-hd-icon">
          <i class="bx bx-wrench fs-4 text-white"></i>
        </div>
        <div>
          <div class="text-white fw-bold mf-hd-title">ข้อมูลประดับยนต์</div>
          <div class="text-white mf-hd-sub">Accessory</div>
        </div>
      </div>

      <div class="card-body pt-3">

        {{-- ── Action bar ── --}}
        <div class="po-filter-bar d-flex align-items-center justify-content-end">
          <button class="btn btn-secondary btn-sm btnInputAcc">
            <i class="bx bx-plus me-1"></i> เพิ่ม
          </button>
        </div>

        {{-- ── Table ── --}}
        <div class="table-responsive">
          <table class="table table-bordered tbl-table tbl-styled accessoryTable">
            <thead>
              <tr>
                <th class="tbl-th-no">No.</th>
                <th>ชื่อร้าน</th>
                <th>รายละเอียด</th>
                <th>รุ่นรถ</th>
                <th>ราคา</th>
                <th>สถานะ</th>
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
