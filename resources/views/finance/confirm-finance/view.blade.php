@extends('layouts/contentNavbarLayout')
@section('title', 'Data Booking Finance')

@section('page-script')
@vite(['resources/assets/js/finance.js'])
@endsection

@section('content')
<div class="editFinConfirmModal"></div>
<div class="viewMoreFinConfirmModal"></div>
<div class="viewExportFirmModel"></div>
<div class="row">
  <div class="col-12">
    <div class="card tbl-card">

      {{-- ── Card header ── --}}
      <div class="po-card-header d-flex align-items-center gap-3">
        <div class="po-hd-icon">
          <i class="bx bx-wallet fs-4 text-white"></i>
        </div>
        <div>
          <div class="text-white fw-bold mf-hd-title">ยอดเฟิร์มเงิน FN</div>
          <div class="text-white mf-hd-sub">Confirm Finance</div>
        </div>
      </div>

      <div class="card-body pt-3">

        {{-- ── Filter / action bar ── --}}
        <div class="po-filter-bar d-flex align-items-center gap-2 flex-wrap">
          <button class="btn btn-warning btn-sm btnViewExportFirm">
            <i class="bx bx-file me-1"></i> รายงาน Firm FN
          </button>
          <div class="ms-auto d-flex align-items-center gap-2">
            <i class="bx bx-filter-alt text-muted"></i>
            <label for="fnStatusFilter" class="mb-0 text-muted">สถานะ</label>
            <select id="fnStatusFilter" class="form-select form-select-sm" style="min-width:160px;">
              <option value="unpaid" selected>ยังไม่ได้รับเงิน</option>
              <option value="paid">รับเงินแล้ว</option>
              <option value="all">ทั้งหมด</option>
            </select>
          </div>
        </div>

        {{-- ── Table ── --}}
        <div class="table-responsive">
          <table class="table table-bordered tbl-table tbl-styled confirmFNTable">
            <thead>
              <tr>
                <th class="tbl-th-no">No.</th>
                <th>ชื่อ - นามสกุล</th>
                <th>ชื่อไฟแนนซ์</th>
                <th>วันที่ส่งมอบ</th>
                <th>วันที่เฟิร์มเคส</th>
                <th>วันที่ได้รับเงิน</th>
                <th class="tbl-th-action" style="width:130px;">Action</th>
              </tr>
            </thead>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection
