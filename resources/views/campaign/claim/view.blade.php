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
          <div class="text-white fw-bold mf-hd-title">รายก ารใช้แคมเปญ</div>
          <div class="text-white mf-hd-sub">Campaign Usage (On-Top)</div>
        </div>
      </div>

      <div class="card-body pt-3">

        {{-- ── Filter bar ── --}}
        <div class="po-filter-bar d-flex align-items-center justify-content-between gap-2 mb-3 flex-wrap">
          <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#claimReportModal">
            <i class="bx bx-spreadsheet me-1"></i> รายงาน
          </button>
          <div class="d-flex align-items-center gap-3 flex-wrap">
            <div class="d-flex align-items-center gap-2">
              <label for="claimMonthFilter" class="mb-0 fw-semibold text-nowrap">
                <i class="bx bx-calendar me-1"></i>เดือนส่งมอบ :
              </label>
              {{-- เว้นว่าง = ทุกเดือน (ไม่กรอง) — คนละแกนกับสถานะ ใช้ร่วมกันได้ --}}
              <input type="month" id="claimMonthFilter" class="form-control form-control-sm"
                style="width:auto;" title="เว้นว่าง = ทุกเดือน">
              {{-- แถวที่ส่งมอบแล้วแต่ยังไม่ได้กรอกวันที่ — แสดงเสมอไม่ว่าเลือกเดือนไหน --}}
              <span id="claimNullDeliveryBadge" class="badge bg-label-warning text-nowrap"
                style="display:none;" title="แสดงอยู่เสมอทุกเดือน เพราะไม่มีวันที่ให้จัดเข้าเดือน">
                <i class="bx bx-error-circle me-1"></i>ไม่มีวันส่งมอบ
                <span id="claimNullDeliveryCount">0</span> รายการ
              </span>
            </div>
            <label for="claimStatusFilter" class="mb-0 fw-semibold text-nowrap">
              <i class="bx bx-filter-alt me-1"></i>สถานะ :
            </label>
            <select id="claimStatusFilter" class="form-select form-select-sm" style="width:auto;min-width:260px;">
              <option value="">ยังไม่ตรวจสอบ (ค่าเริ่มต้น)</option>
              {{-- "ทั้งหมด" = ทุกสถานะ รวม "รับเงินเรียบร้อย" ที่ซ่อนจากตัวเลือกรายสถานะ
                   (ใช้ตอนอยากค้นหาข้ามสถานะ) --}}
              <option value="all">ทั้งหมด</option>
              @foreach ($status as $s)
                {{-- ซ่อน "รับเงินเรียบร้อย" เพราะถือว่าจบงานแล้ว (ดูย้อนหลังได้จากรายงาน Excel / เลือก "ทั้งหมด") --}}
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
                {{-- <th>รุ่นรถ</th> --}}
                <th>เลขตัวถัง (VIN)</th>
                <th>ประเภทแคมเปญ</th>
                <th>วันที่ส่งมอบ</th>
                {{-- <th>ยอดแคมเปญที่ใช้</th>
                <th>ยอดรับเคลม</th>
                <th>ยอด Diff</th>
                <th>วันที่รับเงิน</th> --}}
                <th class="col-filter-th">
                  <div class="col-filter-wrap">
                    <span>สรุปผลการตรวจสอบ</span>
                    <button class="col-filter-btn" id="claimStatusColBtn" type="button">
                      <i class="bx bx-filter-alt"></i>
                      <span class="col-filter-dot"></span>
                    </button>
                  </div>
                </th>
                {{-- <th>หมายเหตุ</th> --}}
                <th class="tbl-th-action" style="width:90px;">Action</th>
              </tr>
            </thead>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>

{{-- ── Dropdown ฟิลเตอร์คอลัมน์ "สรุปผลการตรวจสอบ" ──
     วางนอกตาราง (position:fixed จัดตำแหน่งด้วย JS) ไม่งั้นโดน overflow ของ .table-responsive ตัด
     รายการสถานะไม่เยอะและคงที่ จึงเรนเดอร์จาก server ตรง ๆ ไม่ต้องมี endpoint แยก --}}
<div class="col-filter-dropdown" id="claimStatusColDropdown">
  <div class="col-filter-search">
    <input type="text" id="claimStatusColSearch" placeholder="ค้นหา...">
  </div>
  <div class="col-filter-list" id="claimStatusColList">
    <div class="col-filter-item col-filter-all">
      <input type="checkbox" id="claimStatusChkAll" checked>
      <label for="claimStatusChkAll">(เลือกทั้งหมด)</label>
    </div>
    {{-- รายการที่ยังไม่มี status_id --}}
    <div class="col-filter-item">
      <input type="checkbox" class="claim-status-chk" id="claimStatusChkNone" value="none" checked>
      <label for="claimStatusChkNone">ยังไม่ตรวจสอบ</label>
    </div>
    @foreach ($status as $s)
      <div class="col-filter-item">
        <input type="checkbox" class="claim-status-chk" id="claimStatusChk{{ $s->id }}" value="{{ $s->id }}" checked>
        <label for="claimStatusChk{{ $s->id }}">{{ $s->name }}</label>
      </div>
    @endforeach
  </div>
  <div class="col-filter-actions">
    <button type="button" class="btn btn-sm btn-light" id="claimStatusColClear">ล้าง</button>
    <button type="button" class="btn btn-sm btn-primary" id="claimStatusColApply">ตกลง</button>
  </div>
</div>

<div id="campaignClaimLoadingOverlay" style="display:flex;">
  <div class="ct-loading-box">
    <div class="spinner-border text-primary" role="status" style="width:1.4rem;height:1.4rem;"></div>
    <span>กำลังโหลด...</span>
  </div>
</div>
@endsection
