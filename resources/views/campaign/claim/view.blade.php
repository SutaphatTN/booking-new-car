@extends('layouts/contentNavbarLayout')
@section('title', 'รายการใช้แคมเปญ')

@section('page-script')
@vite(['resources/assets/js/campaign-claim.js'])
@endsection

@section('content')
<div class="editClaimModal"></div>
<div class="row">
  <div class="col-12">
    <div class="card tbl-card">

      {{-- ── Card header ── --}}
      <div class="po-card-header d-flex align-items-center gap-3">
        <div class="po-hd-icon">
          <i class="bx bx-receipt fs-4 text-white"></i>
        </div>
        <div>
          <div class="text-white fw-bold mf-hd-title">รายการใช้แคมเปญ</div>
          <div class="text-white mf-hd-sub">Campaign Usage (On-Top)</div>
        </div>
      </div>

      <div class="card-body pt-3">

        {{-- ── Filter bar ── --}}
        <div class="po-filter-bar d-flex align-items-center justify-content-between gap-2 mb-3 flex-wrap">
          <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#claimReportModal">
            <i class="bx bx-spreadsheet me-1"></i> รายงาน
          </button>
          <div class="d-flex align-items-center gap-2">
            <label for="claimStatusFilter" class="mb-0 fw-semibold text-nowrap">
              <i class="bx bx-filter-alt me-1"></i>สถานะ :
            </label>
            <select id="claimStatusFilter" class="form-select form-select-sm" style="width:auto;min-width:260px;">
              <option value="">ยังไม่ตรวจสอบ (ค่าเริ่มต้น)</option>
              @foreach ($status as $s)
                {{-- ซ่อน "รับเงินเรียบร้อย" เพราะถือว่าจบงานแล้ว (ดูย้อนหลังได้จากรายงาน Excel) --}}
                @continue($s->name === 'รับเงินเรียบร้อย')
                <option value="{{ $s->id }}">{{ $s->name }}</option>
              @endforeach
            </select>
          </div>
        </div>

        @include('campaign.claim.report-modal')

        {{-- ── Table ── --}}
        <div class="table-responsive">
          <table class="table table-bordered tbl-table tbl-styled campaignClaimTable">
            <thead>
              <tr>
                <th class="tbl-th-no">No.</th>
                <th>ลูกค้า</th>
                <th>ฝ่ายขาย</th>
                <th>รุ่นรถ</th>
                <th>ประเภทแคมเปญ</th>
                <th>วันที่ส่งมอบ</th>
                {{-- <th>ยอดแคมเปญที่ใช้</th>
                <th>ยอดรับเคลม</th>
                <th>ยอด Diff</th>
                <th>วันที่รับเงิน</th>
                <th>สรุปผลการตรวจสอบ</th>
                <th>หมายเหตุ</th> --}}
                <th class="tbl-th-action" style="width:90px;">Action</th>
              </tr>
            </thead>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>

<div id="campaignClaimLoadingOverlay" style="display:flex;">
  <div class="ct-loading-box">
    <div class="spinner-border text-primary" role="status" style="width:1.4rem;height:1.4rem;"></div>
    <span>กำลังโหลด...</span>
  </div>
</div>
@endsection
