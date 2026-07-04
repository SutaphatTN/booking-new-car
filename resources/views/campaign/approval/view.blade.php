@extends('layouts/contentNavbarLayout')
@section('title', 'อนุมัติแคมเปญ CK')

@section('page-script')
@vite(['resources/assets/js/campaign.js', 'resources/assets/js/campaign-approval.js'])
@endsection

@section('content')
{{-- modals ใช้ร่วมกับหน้าข้อมูลแคมเปญ (ดู/เพิ่ม/แก้ไข) --}}
<div class="viewMoreCamModal"></div>
<div class="inputCamModal"></div>
<div class="editCamModal"></div>

<div class="row">
  <div class="col-12">
    <div class="card tbl-card">

      {{-- ── Card header ── --}}
      <div class="po-card-header d-flex align-items-center gap-3">
        <div class="po-hd-icon">
          <i class="bx bx-check-shield fs-4 text-white"></i>
        </div>
        <div>
          <div class="text-white fw-bold mf-hd-title">อนุมัติแคมเปญ CK</div>
          <div class="text-white mf-hd-sub">Campaign CK Approval (รายเดือน)</div>
        </div>
      </div>

      <div class="card-body pt-3">

        {{-- ── Filter / action bar ── --}}
        <div class="po-filter-bar d-flex align-items-end gap-2 flex-wrap">
          <div>
            <label for="ckPeriod" class="mb-1 text-muted small">เดือน</label>
            <input type="month" id="ckPeriod" class="form-control form-control-sm"
              style="min-width:170px;" value="{{ $period }}">
          </div>

          <button id="btnOpenApproval" class="btn btn-primary btn-sm" type="button">
            <i class="bx bx-send me-1"></i> ขออนุมัติแคมเปญ
          </button>

          <button class="btn btn-secondary btn-sm btnInputCam ms-auto">
            <i class="bx bx-plus me-1"></i> เพิ่มแคมเปญ
          </button>
        </div>

        <div class="text-muted small mb-2">
          <i class="bx bx-info-circle"></i>
          แคมเปญ CK ต้องขออนุมัติทุกเดือน — เดือนใดยังไม่อนุมัติจะเลือกในใบจองไม่ได้ · ต่ออายุได้เลย
        </div>

        {{-- ── Table ── --}}
        <div class="table-responsive">
          <table class="table table-bordered tbl-table tbl-styled ckApprovalTable">
            <thead>
              <tr>
                <th class="tbl-th-no">No.</th>
                <th class="col-filter-th">
                  <div class="col-filter-wrap">
                    <span>รุ่นรถ</span>
                    <button class="col-filter-btn" id="modelFilterBtn" type="button" title="กรองรุ่นรถ">
                      <i class="bx bx-filter-alt"></i>
                      <span class="col-filter-dot"></span>
                    </button>
                  </div>
                </th>
                <th>ชื่อแคมเปญ</th>
                <th>ประเภท</th>
                <th>จำนวนเงิน</th>
                <th>สถานะ (เดือนที่เลือก)</th>
                <th class="tbl-th-action" style="width:170px;">Action</th>
              </tr>
            </thead>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>

{{-- ── ตัวกรองรุ่นรถ (วางนอกตารางกัน overflow, จัดตำแหน่งด้วย JS) ── --}}
<div class="col-filter-dropdown" id="modelFilterDropdown">
  <div class="col-filter-search">
    <input type="text" id="modelFilterSearch" placeholder="ค้นหา...">
  </div>
  <div class="col-filter-list" id="modelFilterList"></div>
  <div class="col-filter-actions">
    <button class="btn btn-sm btn-light" id="modelFilterClear">ล้าง</button>
    <button class="btn btn-sm btn-primary" id="modelFilterApply">ตกลง</button>
  </div>
</div>

{{-- ── Modal เลือกแคมเปญที่จะขออนุมัติ ── --}}
<div class="modal fade" id="approvalModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bx bx-send me-1"></i> ขออนุมัติแคมเปญ CK — เดือน <span id="approvalModalPeriod"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
          <div class="input-group input-group-sm" style="max-width:260px;">
            <span class="input-group-text"><i class="bx bx-search"></i></span>
            <input type="text" id="approvalSearch" class="form-control" placeholder="ค้นหา รุ่น / ชื่อ / ประเภท...">
          </div>
          <div class="form-check ms-2 m-0">
            <input class="form-check-input" type="checkbox" id="approvalSelectAll" checked>
            <label class="form-check-label" for="approvalSelectAll">เลือกทั้งหมด</label>
          </div>
        </div>

        <div class="table-responsive" style="max-height:52vh;">
          <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light" style="position:sticky;top:0;z-index:1;">
              <tr>
                <th style="width:40px;"></th>
                <th>รุ่นรถ</th>
                <th>ชื่อแคมเปญ</th>
                <th class="text-end">จำนวนเงิน</th>
                <th>สถานะ</th>
              </tr>
            </thead>
            <tbody id="approvalModalBody">
              {{-- rendered by JS --}}
            </tbody>
          </table>
        </div>

        <div id="approvalEmpty" class="text-center text-muted py-4 d-none">
          <i class="bx bx-check-circle fs-3 d-block mb-1 text-success"></i>
          ไม่มีแคมเปญ CK ที่ต้องขออนุมัติสำหรับเดือนนี้
        </div>
      </div>

      <div class="modal-footer d-flex justify-content-between">
        <div class="small text-muted">
          เลือก <strong id="approvalSelCount">0</strong> รายการ ·
          รวม <strong id="approvalSelTotal">0.00</strong> บาท
        </div>
        <div>
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="button" class="btn btn-primary btn-sm" id="btnSubmitApproval" disabled>
            <i class="bx bx-send me-1"></i> ส่งขออนุมัติ (<span id="approvalSubmitCount">0</span>)
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<div id="ckApprovalLoadingOverlay" style="display:flex;">
  <div class="ct-loading-box">
    <div class="spinner-border text-primary" role="status" style="width:1.4rem;height:1.4rem;"></div>
    <span>กำลังโหลด...</span>
  </div>
</div>
@endsection
