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
          <div class="po-filter-bar d-flex align-items-center gap-2">
            <i class="bx bx-filter-alt text-muted"></i>
            <label for="filterDecision" class="mb-0 text-muted">สถานะ :</label>
            <select id="filterDecision" class="form-select form-select-sm" style="width:200px;">
              <option value="">— ทั้งหมด —</option>
              @foreach ($decisions as $d)
                <option value="{{ $d->id }}">{{ $d->name }}</option>
              @endforeach
            </select>
          </div>

          {{-- ── Table ── --}}
          <div class="table-responsive">
            <table class="table table-bordered tbl-table tbl-styled" id="trackingTable">
              <thead>
                <tr>
                  <th class="tbl-th-no">No.</th>
                  <th>ชื่อ - นามสกุล</th>
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
                  <th>วันที่</th>
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

@endsection
