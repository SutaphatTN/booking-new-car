@extends('layouts/contentNavbarLayout')
@section('title', 'Data Invoice')

@section('page-script')
  @vite(['resources/assets/js/invoice.js'])
@endsection

@section('content')
  <div class="row">
    <div class="col-12">
      <div class="card tbl-card">

        {{-- ── Card header ── --}}
        <div class="po-card-header d-flex align-items-center gap-3">
          <div class="po-hd-icon">
            <i class="bx bx-receipt fs-4 text-white"></i>
          </div>
          <div>
            <div class="text-white fw-bold mf-hd-title">รายการใบสั่งซื้อ</div>
            <div class="text-white mf-hd-sub">Invoice List</div>
          </div>
        </div>

        <div class="card-body pt-3">
          @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              {{ session('success') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          @endif

          {{-- ── Filter / action bar ── --}}
          <div class="po-filter-bar d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('invoice.create') }}" class="btn btn-primary btn-sm">
              <i class="bx bx-plus me-1"></i> สร้างใบสั่งซื้อ
            </a>
            <div class="ms-auto d-flex align-items-center gap-2">
              <i class="bx bx-filter-alt text-muted"></i>
              <label for="invoiceStatusFilter" class="mb-0 text-muted">สถานะ :</label>
              <select id="invoiceStatusFilter" class="form-select form-select-sm" style="width:160px;">
                <option value="pending" selected>วางบิล</option>
                <option value="paid">จ่ายเงิน</option>
                <option value="all">ทั้งหมด</option>
              </select>
            </div>
          </div>

          {{-- ── Table ── --}}
          <div class="table-responsive">
            <table class="table table-bordered tbl-table tbl-styled invoiceTable">
              <thead>
                <tr>
                  <th class="tbl-th-no">No.</th>
                  <th>ชื่อลูกค้า</th>
                  <th>ชื่อร้าน</th>
                  <th>รายละเอียด</th>
                  <th>ยอดเงิน</th>
                  <th>วันที่</th>
                  <th class="tbl-th-action" style="width:170px;">Action</th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Modal ยืนยันออกใบเสร็จ --}}
  <div class="modal fade" id="confirmReceiptModal" tabindex="-1" role="dialog" data-bs-backdrop="static">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content border-0 shadow mf-content mf-content--input">

        {{-- Header --}}
        <div class="modal-header mf-header mf-header--input px-4">
          <div class="d-flex align-items-center gap-3">
            <div class="mf-hd-icon">
              <i class="bx bx-receipt fs-5 text-white"></i>
            </div>
            <div>
              <h6 class="mb-0 fw-bold text-white mf-hd-title">ยืนยันออกใบเสร็จ</h6>
              <small class="text-white mf-hd-sub">Confirm Receipt</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body mf-body">
          <label for="receiptConfirmedDate" class="form-label"><i class="bx bx-calendar ci-indigo"></i> วันที่จ่ายเงิน <span class="text-danger">*</span></label>
          <input type="date" id="receiptConfirmedDate" class="form-control">

          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">ยกเลิก</button>
            <button type="button" id="btnSubmitConfirmReceipt" class="btn btn-primary text-white">ยืนยัน</button>
          </div>
        </div>

      </div>
    </div>
  </div>

  <div id="invoiceLoadingOverlay" style="display:flex;">
    <div class="ct-loading-box">
      <div class="spinner-border text-primary" role="status" style="width:1.4rem;height:1.4rem;"></div>
      <span>กำลังโหลด...</span>
    </div>
  </div>

@endsection
