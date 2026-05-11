@extends('layouts/contentNavbarLayout')
@section('title', 'Data Model Car')

@section('page-script')
@vite(['resources/assets/js/car.js'])
@endsection

@section('content')
<div class="inputCarModal"></div>
<div class="editCarModal"></div>
<div class="row">
  <div class="col-12">
    <div class="card tbl-card">

      {{-- ── Card header ── --}}
      <div class="po-card-header d-flex align-items-center gap-3">
        <div class="po-hd-icon">
          <i class="bx bx-car fs-4 text-white"></i>
        </div>
        <div>
          <div class="text-white fw-bold mf-hd-title">ข้อมูลรุ่นรถหลัก</div>
          <div class="text-white mf-hd-sub">Model Car</div>
        </div>
      </div>

      <div class="card-body pt-3">

        {{-- ── Action bar ── --}}
        <div class="po-filter-bar d-flex align-items-center justify-content-end">
          <button class="btn btn-secondary btn-sm btnInputCar">
            <i class="bx bx-plus me-1"></i> เพิ่ม
          </button>
        </div>

        {{-- ── Table ── --}}
        <div class="table-responsive">
          <table class="table table-bordered tbl-table tbl-styled carTable">
            <thead>
              <tr>
                <th class="tbl-th-no">No.</th>
                <th>ชื่อรุ่นรถภาษาไทย</th>
                <th>ชื่อรุ่นรถภาษาอังกฤษ</th>
                <th>ชื่อย่อ</th>
                <th>ยอดเงินเกินงบ</th>
                <th>เงินจองขั้นต่ำ</th>
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
