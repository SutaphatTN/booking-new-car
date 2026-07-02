@extends('layouts/contentNavbarLayout')
@section('title', 'Data Campaign')

@section('page-script')
@vite(['resources/assets/js/campaign.js'])
@endsection

@section('content')
<div class="viewMoreCamModal"></div>
<div class="inputCamModal"></div>
<div class="editCamModal"></div>
<div class="row">
  <div class="col-12">
    <div class="card tbl-card">

      {{-- ── Card header ── --}}
      <div class="po-card-header d-flex align-items-center gap-3">
        <div class="po-hd-icon">
          <i class="bx bx-gift fs-4 text-white"></i>
        </div>
        <div>
          <div class="text-white fw-bold mf-hd-title">ข้อมูลแคมเปญ</div>
          <div class="text-white mf-hd-sub">Campaign</div>
        </div>
      </div>

      <div class="card-body pt-3">

        {{-- ── Filter / action bar ── --}}
        <div class="po-filter-bar d-flex align-items-end gap-2 flex-wrap">
          <div>
            <label for="filterModel" class="mb-1 text-muted small">รุ่นหลัก</label>
            <select id="filterModel" class="form-select form-select-sm" style="min-width:160px;">
              <option value="">ทั้งหมด</option>
              @foreach ($model as $m)
                <option value="{{ $m->id }}">{{ $m->Name_TH }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label for="filterSubModel" class="mb-1 text-muted small">รุ่นย่อย</label>
            <select id="filterSubModel" class="form-select form-select-sm" style="min-width:180px;" disabled>
              <option value="">ทั้งหมด</option>
            </select>
          </div>
          <button id="btnClearCamFilter" class="btn btn-outline-secondary btn-sm" type="button">
            <i class="bx bx-x"></i> ล้าง
          </button>

          <button class="btn btn-secondary btn-sm btnInputCam ms-auto">
            <i class="bx bx-plus me-1"></i> เพิ่ม
          </button>
        </div>

        {{-- ── Table ── --}}
        <div class="table-responsive">
          <table class="table table-bordered tbl-table tbl-styled campaignTable">
            <thead>
              <tr>
                <th class="tbl-th-no">No.</th>
                <th>รุ่นรถ</th>
                <th>ชื่อแคมเปญ</th>
                <th>ปี</th>
                <th class="col-filter-th">
                  <div class="col-filter-wrap">
                    <span>ประเภท</span>
                    <button class="col-filter-btn" id="typeFilterBtn" type="button" title="กรองประเภท">
                      <i class="bx bx-filter-alt"></i>
                      <span class="col-filter-dot"></span>
                    </button>
                  </div>
                </th>
                <th>จำนวนเงิน</th>
                <th>สถานะ</th>
                <th class="tbl-th-action" style="width:150px;">Action</th>
              </tr>
            </thead>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>

{{-- ── Type filter dropdown (positioned by JS, outside table overflow) ── --}}
<div class="col-filter-dropdown" id="typeFilterDropdown">
  <div class="col-filter-search">
    <input type="text" id="typeFilterSearch" placeholder="ค้นหา...">
  </div>
  <div class="col-filter-list" id="typeFilterList">
    <div class="col-filter-item col-filter-all">
      <input type="checkbox" id="typeChkAll" checked>
      <label for="typeChkAll">(เลือกทั้งหมด)</label>
    </div>
    @foreach ($type as $t)
      <div class="col-filter-item">
        <input type="checkbox" class="type-chk-item" id="typeChk{{ $t->id }}" value="{{ $t->id }}" checked>
        <label for="typeChk{{ $t->id }}">{{ $t->name }}</label>
      </div>
    @endforeach
  </div>
  <div class="col-filter-actions">
    <button class="btn btn-sm btn-light" id="typeFilterClear">ล้าง</button>
    <button class="btn btn-sm btn-primary" id="typeFilterApply">ตกลง</button>
  </div>
</div>

<div id="campaignLoadingOverlay" style="display:flex;">
  <div class="ct-loading-box">
    <div class="spinner-border text-primary" role="status" style="width:1.4rem;height:1.4rem;"></div>
    <span>กำลังโหลด...</span>
  </div>
</div>
@endsection
