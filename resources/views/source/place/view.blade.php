@extends('layouts/contentNavbarLayout')
@section('title', 'สถานที่')

@section('page-script')
@vite(['resources/assets/js/source.js'])
@endsection

@section('content')
<div class="inputPlaceModal"></div>
<div class="editPlaceModal"></div>
<div class="clearPlaceModal"></div>

<div class="row">
  <div class="col-12">
    <div class="card tbl-card">

      {{-- ── Card header ── --}}
      <div class="po-card-header d-flex align-items-center gap-3">
        <div class="po-hd-icon">
          <i class="bx bx-store fs-4 text-white"></i>
        </div>
        <div>
          <div class="text-white fw-bold mf-hd-title">สถานที่ (Offline)</div>
          <div class="text-white mf-hd-sub">Place</div>
        </div>
      </div>

      <div class="card-body pt-3">

        <div class="po-filter-bar d-flex align-items-center justify-content-between gap-2 flex-wrap">
          <div class="d-flex align-items-center gap-2">
            <span class="small text-muted">รายงานเดือน</span>
            <input type="month" id="reportMonth" class="form-control form-control-sm" style="width:160px;"
              value="{{ now()->format('Y-m') }}">
            <button class="btn btn-outline-primary btn-sm btnPlaceReport">
              <i class="bx bx-file me-1"></i> ออก PDF
            </button>
          </div>
          <div class="d-flex align-items-center gap-2">
            <button class="btn btn-success btn-sm btnRequestApproval">
              <i class="bx bx-mail-send me-1"></i> ขออนุมัติที่เลือก
            </button>
            <button class="btn btn-secondary btn-sm btnInputPlace">
              <i class="bx bx-plus me-1"></i> เพิ่มสถานที่
            </button>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table table-bordered tbl-table tbl-styled placeTable">
            <thead>
              <tr>
                <th style="width:36px;"><input type="checkbox" class="form-check-input" id="placeChkAll"></th>
                <th class="tbl-th-no">No.</th>
                {{-- <th>แหล่งที่มาย่อย</th> --}}
                <th>สถานที่</th>
                <th>LAS Number</th>
                <th>ช่วงวันที่</th>
                {{-- <th>ประเภทค่าใช้จ่าย</th> --}}
                <th class="text-end">ประมาณค่าใช้จ่าย</th>
                <th class="text-end">เป้า PP</th>
                <th style="width:110px;">สถานะ</th>
                <th class="tbl-th-action" style="width:120px;">Action</th>
              </tr>
            </thead>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>

{{-- Modal เลือกผู้อนุมัติ (role = md) --}}
<div class="modal fade" id="approverModal" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--input">
      <div class="modal-header mf-header mf-header--input px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon"><i class="bx bx-mail-send fs-5 text-white"></i></div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">ขออนุมัติสถานที่</h6>
            <small class="text-white mf-hd-sub">เลือกผู้อนุมัติ (MD)</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body mf-body">
        <p class="mb-2">รายการที่เลือก: <strong><span id="approverCount">0</span></strong> รายการ</p>
        <label for="approver_period" class="mf-label form-label"><i class="bx bx-calendar"></i> ประจำเดือน <span class="text-danger">*</span></label>
        <input type="month" id="approver_period" class="form-control mb-3"
          value="{{ now()->addMonthNoOverflow()->format('Y-m') }}" required>
        <label for="approver_id" class="mf-label form-label"><i class="bx bx-user-check"></i> ผู้อนุมัติ <span class="text-danger">*</span></label>
        <select id="approver_id" class="form-select" required>
          <option value="">— เลือกผู้อนุมัติ —</option>
          @foreach ($approvers as $a)
            <option value="{{ $a->id }}">{{ $a->full_name ?: $a->name }}</option>
          @endforeach
        </select>
        <div class="d-flex justify-content-end gap-2 pt-3">
          <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal"><i class="bx bx-x me-1"></i>ยกเลิก</button>
          <button type="button" class="btn btn-primary px-5 btnSubmitApproval"><i class="bx bx-send me-1"></i>ส่งคำขอ</button>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
