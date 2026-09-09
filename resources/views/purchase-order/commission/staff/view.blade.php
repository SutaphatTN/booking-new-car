@extends('layouts/contentNavbarLayout')
@section('title', 'Staff Commission')

@section('page-script')
  @vite(['resources/assets/js/staff-commission.js'])
@endsection

@section('content')
  <div class="staffCommissionDetailModel"></div>
  <div class="viewExportStaffComModel"></div>

  <div class="row">
    <div class="col-12">
      <div class="card tbl-card">

        <div class="po-card-header d-flex align-items-center gap-3">
          <div class="po-hd-icon">
            <i class="bx bx-briefcase-alt-2 fs-4 text-white"></i>
          </div>
          <div>
            <div class="text-white fw-bold mf-hd-title">ค่าคอมมิชชั่นฝ่ายสนับสนุน</div>
            <div class="text-white mf-hd-sub">ผู้จัดการ · แอดมิน · ทะเบียน · การตลาด</div>
          </div>
        </div>

        <div class="card-body pt-3">

          <div class="po-filter-bar d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
              <label for="staffCommissionMonth" class="mb-0 fw-semibold text-nowrap">
                <i class="bx bx-calendar me-1"></i> เลือกเดือน
              </label>
              <input type="month" id="staffCommissionMonth" class="form-control form-control-sm"
                style="max-width:170px;" value="{{ now()->format('Y-m') }}">
            </div>
            <button class="btn btn-warning btn-sm btnViewExportStaffCom">
              <i class="bx bx-file me-1"></i> รายงานค่าคอม
            </button>
          </div>

          <div class="text-muted small mb-2">
            <i class="bx bx-info-circle me-1"></i>
            ยอดคิดจาก "จำนวนคันที่ตัด CK ในเดือนนั้น" ของแบรนด์ที่แต่ละคนดูแล — ฐานเดียวกับค่าคอมฝ่ายขาย
          </div>

          <div class="table-responsive">
            <table class="table table-bordered tbl-table tbl-styled staffCommissionTable">
              <thead>
                <tr>
                  <th class="tbl-th-no">No.</th>
                  <th>ชื่อ / ฝ่าย</th>
                  <th>ฐานที่นับ</th>
                  <th class="text-end">คอมตามยอดขาย</th>
                  <th class="text-end">รายการเพิ่มเติม</th>
                  <th class="text-end">รวมสุทธิ</th>
                  <th class="text-center">จัดการ</th>
                </tr>
              </thead>
            </table>
          </div>

        </div>
      </div>
    </div>
  </div>

  <div id="staffCommissionLoadingOverlay" style="display:flex;">
    <div class="ct-loading-box">
      <div class="spinner-border text-primary" role="status" style="width:1.4rem;height:1.4rem;"></div>
      <span>กำลังโหลด...</span>
    </div>
  </div>
@endsection
