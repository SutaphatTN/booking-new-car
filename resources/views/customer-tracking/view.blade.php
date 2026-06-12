@extends('layouts/contentNavbarLayout')
@section('title', 'รายการติดตามลูกค้า')

@section('page-script')
  @vite(['resources/assets/js/customer-tracking.js'])
@endsection

@section('content')
  <div class="row">
    <div class="col-12">
      <div class="card tbl-card">

        {{-- ── Card header ── --}}
        <div class="po-card-header d-flex align-items-center gap-3">
          <div class="po-hd-icon">
            <i class="bx bx-radar fs-4 text-white"></i>
          </div>
          <div>
            <div class="text-white fw-bold mf-hd-title">รายการติดตามลูกค้า</div>
            <div class="text-white mf-hd-sub">Customer Tracking</div>
          </div>
        </div>

        <div class="card-body pt-3">

          @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              {{ session('success') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          @endif
          @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              {{ session('error') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          @endif

          {{-- ── Filter bar ── --}}
          <div class="po-filter-bar d-flex flex-column gap-2">

            {{-- แถว 1: รายงานประจำวัน + กรองสถานะ (desktop เท่านั้น) --}}
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
              <div class="d-flex align-items-center flex-wrap gap-2">
                <i class="bx bx-file-export text-muted"></i>
                <span class="text-muted small" style="min-width:105px;">รายงานการกรอกข้อมูลประจำวัน :</span>
                <input type="date" id="reportDailyDate" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" style="width:155px;" data-no-icon>
                <button type="button" class="btn btn-success btn-sm" id="btnExportDaily">
                  <i class="bx bx-download me-1"></i>Excel
                </button>
              </div>

              {{-- สถานะ: desktop เท่านั้น --}}
              <div class="d-none d-md-flex align-items-center gap-2">
                <i class="bx bx-filter-alt text-muted"></i>
                <label for="filterDecision" class="mb-0 text-muted">สถานะ :</label>
                <select id="filterDecision" class="form-select form-select-sm" style="width:200px;">
                  <option value="">— ทั้งหมด —</option>
                  @foreach ($decisions as $d)
                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>

            {{-- แถว 2: รายงานเพิ่มลูกค้าประจำวัน --}}
            @if (Auth::user()->role !== 'sale')
            <div class="d-flex align-items-center flex-wrap gap-2 mt-2">
              <i class="bx bx-file-export text-muted"></i>
              <span class="text-muted small" style="min-width:105px;">รายงานเพิ่มลูกค้าประจำวัน :</span>
              <input type="date" id="reportDateFrom" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" style="width:155px;" data-no-icon>
              <span class="text-muted small">–</span>
              <input type="date" id="reportDateTo" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" style="width:155px;" data-no-icon>
              <button type="button" class="btn btn-success btn-sm" id="btnExportByDate">
                <i class="bx bx-download me-1"></i>Excel
              </button>
            </div>
            @endif

            {{-- แถว 3: รายงานเลยกำหนดติดตามลูกค้า --}}
            @if (Auth::user()->role !== 'sale')
            <div class="d-flex align-items-center flex-wrap gap-2 mt-2">
              <i class="bx bx-file-export text-muted"></i>
              <span class="text-muted small">รายงานเลยกำหนดติดตาม (ผจก.) :</span>
              <input type="month" id="reportOverdueMonth" class="form-control form-control-sm" value="{{ date('Y-m') }}" style="width:155px;">
              <button type="button" class="btn btn-success btn-sm" id="btnExportOverdue">
                <i class="bx bx-download me-1"></i>Excel
              </button>
            </div>
            @endif

            {{-- แถว 4: สถานะ (mobile เท่านั้น) --}}
            <div class="d-flex d-md-none align-items-center gap-2">
              <i class="bx bx-filter-alt text-muted"></i>
              <label for="filterDecisionMobile" class="mb-0 text-muted">สถานะ :</label>
              <select id="filterDecisionMobile" class="form-select form-select-sm" style="width:200px;">
                <option value="">— ทั้งหมด —</option>
                @foreach ($decisions as $d)
                  <option value="{{ $d->id }}">{{ $d->name }}</option>
                @endforeach
              </select>
            </div>

          </div>

          {{-- ── Table ── --}}
          <div class="table-responsive">
            <table class="table table-bordered tbl-table tbl-styled" id="trackingTable" style="min-width:1300px;">
              <thead>
                <tr>
                  <th class="tbl-th-no">No.</th>
                  <th>ชื่อ - นามสกุล</th>
                  <th>ข้อมูลติดต่อ</th>
                  <th>ข้อมูลรถ</th>
                  <th class="col-filter-th">
                    <div class="col-filter-wrap">
                      <span>ผู้ขาย</span>
                      <button class="col-filter-btn" id="ctSaleFilterBtn" type="button" title="กรองผู้ขาย">
                        <i class="bx bx-filter-alt"></i>
                        <span class="col-filter-dot"></span>
                      </button>
                    </div>
                  </th>
                  <th class="col-filter-th">
                    <div class="col-filter-wrap">
                      <span>แหล่งที่มา</span>
                      <button class="col-filter-btn" id="ctSourceFilterBtn" type="button" title="กรองแหล่งที่มา">
                        <i class="bx bx-filter-alt"></i>
                        <span class="col-filter-dot"></span>
                      </button>
                    </div>
                  </th>
                  <th class="col-filter-th">
                    <div class="col-filter-wrap">
                      <span>วันที่ติดต่อล่าสุด</span>
                      <button class="col-filter-btn" id="ctLastDateFilterBtn" type="button" title="กรองวันที่ติดต่อล่าสุด">
                        <i class="bx bx-filter-alt"></i>
                        <span class="col-filter-dot"></span>
                      </button>
                    </div>
                  </th>
                  <th class="col-filter-th">
                    <div class="col-filter-wrap">
                      <span>วันที่ติดต่อครั้งถัดไป</span>
                      <button class="col-filter-btn" id="ctNextDateFilterBtn" type="button" title="กรองวันที่ติดต่อครั้งถัดไป">
                        <i class="bx bx-filter-alt"></i>
                        <span class="col-filter-dot"></span>
                      </button>
                    </div>
                  </th>
                  <th class="col-filter-th">
                    <div class="col-filter-wrap">
                      <span>สถานะ</span>
                      <button class="col-filter-btn" id="ctStatusFilterBtn" type="button" title="กรองสถานะ">
                        <i class="bx bx-filter-alt"></i>
                        <span class="col-filter-dot"></span>
                      </button>
                    </div>
                  </th>
                  <th class="tbl-th-action" style="width:120px;">Action</th>
                </tr>
              </thead>
            </table>
          </div>

        </div>
      </div>
    </div>
  </div>

{{-- ── ผู้ขาย filter dropdown ── --}}
<div class="col-filter-dropdown" id="ctSaleFilterDropdown">
  <div class="col-filter-search">
    <input type="text" id="ctSaleFilterSearch" placeholder="ค้นหา...">
  </div>
  <div class="col-filter-list" id="ctSaleFilterList"></div>
  <div class="col-filter-actions">
    <button class="btn btn-sm btn-light" id="ctSaleFilterClear">ล้าง</button>
    <button class="btn btn-sm btn-primary" id="ctSaleFilterApply">ตกลง</button>
  </div>
</div>

{{-- ── แหล่งที่มา filter dropdown ── --}}
<div class="col-filter-dropdown" id="ctSourceFilterDropdown">
  <div class="col-filter-search">
    <input type="text" id="ctSourceFilterSearch" placeholder="ค้นหา...">
  </div>
  <div class="col-filter-list" id="ctSourceFilterList"></div>
  <div class="col-filter-actions">
    <button class="btn btn-sm btn-light" id="ctSourceFilterClear">ล้าง</button>
    <button class="btn btn-sm btn-primary" id="ctSourceFilterApply">ตกลง</button>
  </div>
</div>

{{-- ── วันที่ติดต่อล่าสุด filter dropdown ── --}}
<div class="col-filter-dropdown" id="ctLastDateFilterDropdown">
  <div class="col-filter-search">
    <input type="text" id="ctLastDateFilterSearch" placeholder="ค้นหา...">
  </div>
  <div class="col-filter-list" id="ctLastDateFilterList"></div>
  <div class="col-filter-actions">
    <button class="btn btn-sm btn-light" id="ctLastDateFilterClear">ล้าง</button>
    <button class="btn btn-sm btn-primary" id="ctLastDateFilterApply">ตกลง</button>
  </div>
</div>

{{-- ── วันที่ติดต่อครั้งถัดไป filter dropdown ── --}}
<div class="col-filter-dropdown" id="ctNextDateFilterDropdown">
  <div class="col-filter-search">
    <input type="text" id="ctNextDateFilterSearch" placeholder="ค้นหา...">
  </div>
  <div class="col-filter-list" id="ctNextDateFilterList"></div>
  <div class="col-filter-actions">
    <button class="btn btn-sm btn-light" id="ctNextDateFilterClear">ล้าง</button>
    <button class="btn btn-sm btn-primary" id="ctNextDateFilterApply">ตกลง</button>
  </div>
</div>

{{-- ── สถานะ filter dropdown ── --}}
<div class="col-filter-dropdown" id="ctStatusFilterDropdown">
  <div class="col-filter-search">
    <input type="text" id="ctStatusFilterSearch" placeholder="ค้นหา...">
  </div>
  <div class="col-filter-list" id="ctStatusFilterList"></div>
  <div class="col-filter-actions">
    <button class="btn btn-sm btn-light" id="ctStatusFilterClear">ล้าง</button>
    <button class="btn btn-sm btn-primary" id="ctStatusFilterApply">ตกลง</button>
  </div>
</div>

<div id="ctLoadingOverlay" style="display:flex;">
  <div class="ct-loading-box">
    <div class="spinner-border text-primary" role="status" style="width:1.4rem;height:1.4rem;"></div>
    <span>กำลังโหลด...</span>
  </div>
</div>

@endsection
