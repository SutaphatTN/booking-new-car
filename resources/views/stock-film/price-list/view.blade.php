@extends('layouts/contentNavbarLayout')
@section('title', 'PriceList Film')

@section('page-script')
@vite(['resources/assets/js/film-price-list.js'])
@endsection

@section('content')
<div class="inputFilmPriceModal"></div>
<div class="editFilmPriceModal"></div>

<div class="row">
  <div class="col-12">
    <div class="card tbl-card">

      <div class="po-card-header d-flex align-items-center gap-3">
        <div class="po-hd-icon">
          <i class="bx bx-purchase-tag fs-4 text-white"></i>
        </div>
        <div>
          <div class="text-white fw-bold mf-hd-title">ราคาฟิล์ม</div>
          <div class="text-white mf-hd-sub">Film Price List</div>
        </div>
      </div>

      <div class="card-body pt-3">

        <div class="po-filter-bar d-flex align-items-center justify-content-end">
          <button class="btn btn-secondary btn-sm btnInputFilmPrice">
            <i class="bx bx-plus me-1"></i> เพิ่มข้อมูล
          </button>
        </div>

        <div class="table-responsive mt-2">
          <table class="table table-bordered tbl-table tbl-styled filmPriceTable">
            <thead>
              <tr>
                <th class="tbl-th-no">No.</th>
                <th>รุ่นรถ</th>
                <th>ยี่ห้อฟิล์ม</th>
                <th>ตำแหน่ง</th>
                <th>ความเข้ม</th>
                <th class="text-end">ตร.ฟุต</th>
                <th class="text-end">ราคาขายรวมภาษี</th>
                <th class="text-end">ค่าคอม (SCom)</th>
                <th class="tbl-th-action">Action</th>
              </tr>
            </thead>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection
