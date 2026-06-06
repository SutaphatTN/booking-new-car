@extends('layouts/contentNavbarLayout')
@section('title', 'Purchase Order History')

@section('page-script')
@vite(['resources/assets/js/purchase-order.js'])
@endsection

@section('content')
<div class="viewMoreHistory"></div>
<div class="row">
  <div class="col-12">
    <div class="card tbl-card">

      {{-- ── Card header ── --}}
      <div class="po-card-header d-flex align-items-center gap-3">
        <div class="po-hd-icon">
          <i class="bx bx-history fs-4 text-white"></i>
        </div>
        <div>
          <div class="text-white fw-bold mf-hd-title">ประวัติคำสั่งซื้อที่ส่งมอบแล้ว</div>
          <div class="text-white mf-hd-sub">Purchase Order History</div>
        </div>
      </div>

      <div class="card-body pt-3">
        <div class="table-responsive">
          <table class="table table-bordered tbl-table tbl-styled historyFinalTable">
            <thead>
              <tr>
                <th class="tbl-th-no">No.</th>
                <th>ชื่อ - นามสกุล ลูกค้า</th>
                <th>รหัส Car Order</th>
                <th class="tbl-th-action" style="width:150px;">Action</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>

    </div>
  </div>
</div>

<div id="historyLoadingOverlay" style="display:flex;">
  <div class="ct-loading-box">
    <div class="spinner-border text-primary" role="status" style="width:1.4rem;height:1.4rem;"></div>
    <span>กำลังโหลด...</span>
  </div>
</div>

@endsection
