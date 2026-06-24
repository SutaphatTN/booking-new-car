@extends('layouts/contentNavbarLayout')
@section('title', 'Data Price List Car')

@section('page-script')
@vite(['resources/assets/js/car.js'])
@endsection

@section('content')
<div class="inputPricelistCarModal"></div>
<div class="editPricelistCarModal"></div>
<input type="hidden" id="userBrand" value="{{ auth()->user()->brand }}">
<div class="row">
  <div class="col-12">
    <div class="card tbl-card">

      {{-- ── Card header ── --}}
      <div class="po-card-header d-flex align-items-center gap-3">
        <div class="po-hd-icon">
          <i class="bx bx-purchase-tag fs-4 text-white"></i>
        </div>
        <div>
          <div class="text-white fw-bold mf-hd-title">ข้อมูลราคารถ</div>
          <div class="text-white mf-hd-sub">Price List Car</div>
        </div>
      </div>

      <div class="card-body pt-3">

        {{-- ── Action bar ── --}}
        <div class="po-filter-bar d-flex align-items-center justify-content-end">
          <button class="btn btn-secondary btn-sm btnInputPricelistCar">
            <i class="bx bx-plus me-1"></i> เพิ่ม
          </button>
        </div>

        {{-- ── Table ── --}}
        <div class="table-responsive">
          <table class="table table-bordered tbl-table tbl-styled pricelistCarTable">
            <thead>
              <tr>
                <th class="tbl-th-no">No.</th>
                <th>รุ่นรถ</th>
                <th>Option</th>
                <th>ปี</th>
                <th>ประเภทสี</th>
                <th>ราคาทุน (DNP)</th>
                <th>ราคาขาย (MSRP)</th>
                <th>DM</th>
                <th>RI</th>
                <th class="tbl-th-action" style="width:100px;">Action</th>
              </tr>
            </thead>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection
