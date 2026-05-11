@extends('layouts/contentNavbarLayout')
@section('title', 'Car Order Approve')

@section('page-script')
@vite(['resources/assets/js/car-order.js'])
@endsection

@section('content')
<div class="editApproveOrderModal"></div>
<div class="editApproveWaitingOrderModal"></div>
<div class="row">
  <div class="col-12">
    <div class="card tbl-card">

      {{-- ── Card header ── --}}
      <div class="po-card-header d-flex align-items-center gap-3">
        <div class="po-hd-icon">
          <i class="bx bx-check-circle fs-4 text-white"></i>
        </div>
        <div>
          <div class="text-white fw-bold mf-hd-title">ผลการอนุมัติคำสั่งรถ</div>
          <div class="text-white mf-hd-sub">Car Order Approve</div>
        </div>
      </div>

      <div class="card-body pt-3">

        {{-- ── Table ── --}}
        <div class="table-responsive">
          <table class="table table-bordered tbl-table tbl-styled approveOrderTable">
            <thead>
              <tr>
                <th class="tbl-th-no">No.</th>
                <th>วันที่อนุมัติ</th>
                <th>ประเภทการสั่งรถ</th>
                <th>รุ่นรถหลัก</th>
                <th>รุ่นรถย่อย</th>
                <th>สี</th>
                <th>ราคาขาย</th>
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
