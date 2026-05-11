@extends('layouts/contentNavbarLayout')
@section('title', 'Data Finance')

@section('page-script')
@vite(['resources/assets/js/finance.js'])
@endsection

@section('content')
<div class="inputFinModal"></div>
<div class="editFinModal"></div>
<div class="row">
  <div class="col-12">
    <div class="card tbl-card">

      {{-- ── Card header ── --}}
      <div class="po-card-header d-flex align-items-center gap-3">
        <div class="po-hd-icon">
          <i class="bx bx-building fs-4 text-white"></i>
        </div>
        <div>
          <div class="text-white fw-bold mf-hd-title">ข้อมูลไฟแนนซ์</div>
          <div class="text-white mf-hd-sub">Finance</div>
        </div>
      </div>

      <div class="card-body pt-3">

        {{-- ── Action bar ── --}}
        <div class="po-filter-bar d-flex align-items-center justify-content-end">
          <button class="btn btn-secondary btn-sm btnInputFin">
            <i class="bx bx-plus me-1"></i> เพิ่ม
          </button>
        </div>

        {{-- ── Table ── --}}
        <div class="table-responsive">
          <table class="table table-bordered tbl-table tbl-styled financeTable">
            <thead>
              <tr>
                <th class="tbl-th-no">No.</th>
                <th>ชื่อไฟแนนซ์</th>
                <th>ภาษีที่หัก ณ ที่จ่าย</th>
                <th>จำนวนปีสูงสุด</th>
                <th>Update ล่าสุด</th>
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
