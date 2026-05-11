@extends('layouts/contentNavbarLayout')
@section('title', 'Car Order Pending')

@section('page-script')
@vite(['resources/assets/js/car-order.js'])
@endsection

@section('content')
<div class="inputCarOrderModal"></div>
<div class="editPendingOrderModal"></div>
<div class="editWaitingOrderModal"></div>
<div class="row">
  <div class="col-12">
    <div class="card tbl-card">

      {{-- ── Card header ── --}}
      <div class="po-card-header d-flex align-items-center gap-3">
        <div class="po-hd-icon">
          <i class="bx bx-time-five fs-4 text-white"></i>
        </div>
        <div>
          <div class="text-white fw-bold mf-hd-title">รายการคำขอสั่งรถ (รอดำเนินการ)</div>
          <div class="text-white mf-hd-sub">Car Order Pending</div>
        </div>
      </div>

      <div class="card-body pt-3">

        {{-- ── Action bar ── --}}
        <div class="po-filter-bar d-flex align-items-center justify-content-end">
          <button class="btn btn-secondary btn-sm btnInputCarOrder">
            <i class="bx bx-plus me-1"></i> เพิ่ม
          </button>
        </div>

        {{-- ── Table ── --}}
        <div class="table-responsive">
          <table class="table table-bordered tbl-table tbl-styled pendingOrderTable">
            <thead>
              <tr>
                <th class="tbl-th-no">No.</th>
                <th>รหัส Car Order</th>
                <th>วันที่สั่งซื้อ</th>
                <th>ประเภทการสั่งรถ</th>
                <th>รุ่นรถ</th>
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
