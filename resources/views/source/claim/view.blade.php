@extends('layouts/contentNavbarLayout')
@section('title', 'เงินเคลม')

@section('page-script')
  @vite(['resources/assets/js/source-claim.js'])
@endsection

@section('content')
  <div class="editClaimModal"></div>

  <div id="claimLoadingOverlay">
    <div class="ct-loading-box">
      <div class="spinner-border text-primary" role="status" style="width:1.4rem;height:1.4rem;"></div>
      <span>กำลังโหลด...</span>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card tbl-card">

        {{-- ── Card header ── --}}
        <div class="po-card-header d-flex align-items-center gap-3">
          <div class="po-hd-icon">
            <i class="bx bx-wallet fs-4 text-white"></i>
          </div>
          <div>
            <div class="text-white fw-bold mf-hd-title">เงินเคลม</div>
            <div class="text-white mf-hd-sub">Claim (Form B)</div>
          </div>
        </div>

        <div class="card-body pt-3">

          <div class="po-filter-bar d-flex align-items-center justify-content-end gap-2 flex-wrap">
            {{-- ลำดับ: โหมด → เดือน → ปุ่ม (โหมดเป็นตัวคุมว่าช่องเดือนมีผลไหม เลือก "ทั้งหมด" แล้วช่องเดือนจะซ่อน) --}}
            <select id="claimStateFilter" class="form-select form-select-sm" style="width:140px;" title="กรองตามเดือน">
              <option value="month" selected>ตามเดือน</option>
              <option value="all">ทั้งหมด</option>
            </select>
            <input type="month" id="claimFilterMonth" class="form-control form-control-sm" style="width:150px;"
              title="เดือนที่ขออนุมัติ (period)" value="{{ now()->format('Y-m') }}">
            <button class="btn btn-success btn-sm btnClaimExport">
              <i class="bx bx-download me-1"></i> ออก Excel
            </button>
          </div>

          <div class="table-responsive">
            <table class="table table-bordered tbl-table tbl-styled claimTable">
              <thead>
                <tr>
                  <th class="tbl-th-no">No.</th>
                  <th>สถานที่</th>
                  <th>LAS Number</th>
                  <th>ช่วงวันที่</th>
                  <th class="text-end">Form B</th>
                  <th class="tbl-th-action" style="width:90px;">Action</th>
                </tr>
              </thead>
            </table>
          </div>

        </div>
      </div>
    </div>
  </div>
@endsection
