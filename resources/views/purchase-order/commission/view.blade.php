@extends('layouts/contentNavbarLayout')
@section('title', 'Data Commission Sales')

@section('page-script')
@vite(['resources/assets/js/commission.js'])
@endsection

@section('content')
<div class="viewExportComModel"></div>
<div class="commissionDetailModel"></div>
<div class="row">
  <div class="col-12">
    <div class="card tbl-card">

      {{-- ── Card header ── --}}
      <div class="po-card-header d-flex align-items-center gap-3">
        <div class="po-hd-icon">
          <i class="bx bx-coin-stack fs-4 text-white"></i>
        </div>
        <div>
          <div class="text-white fw-bold mf-hd-title">ข้อมูลค่าคอมมิชชั่นฝ่ายขาย</div>
          <div class="text-white mf-hd-sub">Commission Report</div>
        </div>
      </div>

      <div class="card-body pt-3">

        {{-- ── Action bar ── --}}
        <div class="po-filter-bar d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="d-flex align-items-center gap-2">
              <label for="commissionMonth" class="mb-0 fw-semibold text-nowrap">
                <i class="bx bx-calendar me-1"></i> เลือกเดือน
              </label>
              <input type="month" id="commissionMonth" class="form-control form-control-sm" style="max-width:170px;"
                value="{{ now()->format('Y-m') }}">
            </div>
            @if (auth()->user()->brand != 3)
              {{-- brand 3 ไม่มีเป้า (คอมตัวรถคิดตามรุ่น) จึงไม่ต้องมีช่องเป้า --}}
              <div class="d-flex align-items-center gap-2">
                <label for="monthlyTarget" class="mb-0 fw-semibold text-nowrap">
                  <i class="bx bx-target-lock me-1"></i> เป้าเดือนนี้ (คัน)
                </label>
                <input type="number" min="0" id="monthlyTarget" class="form-control form-control-sm text-end"
                  style="max-width:90px;" placeholder="0">
                <button class="btn btn-outline-primary btn-sm" id="btnSaveTarget">
                  <i class="bx bx-save me-1"></i> บันทึกเป้า
                </button>
                <span id="targetStatus" class="small text-muted"></span>
              </div>
            @endif
          </div>
          <button class="btn btn-warning btn-sm btnViewExportCom">
            <i class="bx bx-file me-1"></i> รายงานค่าคอม
          </button>
        </div>
        <div class="text-muted small mb-2">
          <i class="bx bx-info-circle me-1"></i> กดปุ่ม <span class="fw-semibold text-primary">“รายละเอียด / กรอกค่าคอม”</span> เพื่อดูรายชื่อลูกค้าและกรอกค่าคอมเพิ่มเติมของเดือนนั้น
        </div>

        {{-- ── Table ── --}}
        <div class="table-responsive">
          <table class="table table-bordered tbl-table tbl-styled commissionTable">
            <thead>
              <tr>
                <th class="tbl-th-no">No.</th>
                <th>ฝ่ายขาย</th>
                <th>จำนวนคัน</th>
                <th>ยอดค่าคอมมิชชั่น</th>
                <th class="text-center">จัดการ</th>
              </tr>
            </thead>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>

<div id="commissionLoadingOverlay" style="display:flex;">
  <div class="ct-loading-box">
    <div class="spinner-border text-primary" role="status" style="width:1.4rem;height:1.4rem;"></div>
    <span>กำลังโหลด...</span>
  </div>
</div>
@endsection
