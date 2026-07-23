@extends('layouts/contentNavbarLayout')
@section('title', 'Car Order Process')

@section('page-script')
@vite(['resources/assets/js/car-order.js'])
@endsection

@section('content')
<div class="editProcessOrderModal"></div>
<div class="viewWaitingOrderModal"></div>
<div id="openIdHolder" data-open-id="{{ $openId ?? '' }}"></div>
<div class="row">
  <div class="col-12">
    <div class="card tbl-card">

      {{-- ── Card header ── --}}
      <div class="po-card-header d-flex align-items-center gap-3">
        <div class="po-hd-icon">
          <i class="bx bx-loader-circle fs-4 text-white"></i>
        </div>
        <div>
          <div class="text-white fw-bold mf-hd-title">รออนุมัติคำขอสั่งรถ</div>
          <div class="text-white mf-hd-sub">Car Order Process</div>
        </div>
      </div>

      <div class="card-body pt-3">

        {{-- ── Action bar ── --}}
        <div class="d-flex flex-wrap justify-content-end gap-2 mb-3">
          <button type="button" class="btn btn-success btn-sm btnRequestApproval">
            <i class="bx bx-mail-send me-1"></i> ขออนุมัติที่เลือก
          </button>
          {{-- อนุมัติที่เลือก : เฉพาะ role md, admin --}}
          @if (in_array(auth()->user()->role, ['md', 'admin']))
            <button type="button" class="btn btn-primary btn-sm btnBulkApprove">
              <i class="bx bx-check-double me-1"></i> อนุมัติที่เลือก
            </button>
          @endif
        </div>

        {{-- ── Table ── --}}
        <div class="table-responsive">
          <table class="table table-bordered tbl-table tbl-styled processOrderTable">
            <thead>
              <tr>
                <th style="width:38px;" class="text-center">
                  <input type="checkbox" class="form-check-input" id="processChkAll">
                </th>
                <th class="tbl-th-no">No.</th>
                <th>วันที่สั่งซื้อ</th>
                <th>ประเภทการสั่งรถ</th>
                <th>รุ่นรถหลัก</th>
                <th>รุ่นรถย่อย</th>
                <th>สี</th>
                <th class="text-center">จำนวนใน stock</th>
                <th class="text-center">จำนวนที่สั่ง</th>
                <th class="tbl-th-action" style="width:150px;">Action</th>
              </tr>
            </thead>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>

{{-- Modal : เลือกผู้อนุมัติ สำหรับขออนุมัติที่เลือก --}}
<div class="modal fade" id="processApproverModal" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog" role="document">
    <div class="modal-content border-0 shadow">
      <div class="modal-header mf-header mf-header--input px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-mail-send fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">ขออนุมัติคำสั่งซื้อรถ</h6>
            <small class="text-white mf-hd-sub">ส่งอีเมลแจ้งผู้อนุมัติ</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <p class="mb-3">
          รายการที่เลือก: <strong><span id="processApproverCount">0</span></strong> รายการ
          <small class="text-warning" id="processApproverSkip"></small>
        </p>
        <label for="process_approver_id" class="mf-label form-label">
          <i class="bx bx-user-check"></i> ผู้อนุมัติ <span class="text-danger">*</span>
        </label>
        <select id="process_approver_id" class="form-select" required>
          <option value="">— เลือกผู้อนุมัติ —</option>
          @foreach ($approvers as $a)
            <option value="{{ $a->id }}">{{ $a->full_name ?: $a->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="d-flex justify-content-end gap-2 px-4 pb-4">
        <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
          <i class="bx bx-x me-1"></i>ยกเลิก
        </button>
        <button type="button" class="btn btn-success px-4 btnConfirmRequestApproval">
          <i class="bx bx-send me-1"></i>ส่งคำขออนุมัติ
        </button>
      </div>
    </div>
  </div>
</div>
@endsection
