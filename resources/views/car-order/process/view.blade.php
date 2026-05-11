@extends('layouts/contentNavbarLayout')
@section('title', 'Car Order Process')

@section('page-script')
@vite(['resources/assets/js/car-order.js'])
@endsection

@section('content')
<div class="editProcessOrderModal"></div>
<div class="viewWaitingOrderModal"></div>
<div id="openIdHolder" data-open-id="{{ $openId ?? '' }}"></div>
<div class="row">
  <div class="col-12">
    <div class="card tbl-card">

      {{-- ── Card header ── --}}
      <div class="po-card-header d-flex align-items-center gap-3">
        <div class="po-hd-icon">
          <i class="bx bx-loader-circle fs-4 text-white"></i>
        </div>
        <div>
          <div class="text-white fw-bold mf-hd-title">รออนุมัติคำขอสั่งรถ</div>
          <div class="text-white mf-hd-sub">Car Order Process</div>
        </div>
      </div>

      <div class="card-body pt-3">

        {{-- ── Table ── --}}
        <div class="table-responsive">
          <table class="table table-bordered tbl-table tbl-styled processOrderTable">
            <thead>
              <tr>
                <th class="tbl-th-no">No.</th>
                <th>วันที่สั่งซื้อ</th>
                <th>ประเภทการสั่งรถ</th>
                <th>รุ่นรถหลัก</th>
                <th>รุ่นรถย่อย</th>
                <th>สี</th>
                <th>ราคาขาย</th>
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
