@extends('layouts/contentNavbarLayout')
@section('title', 'Data Purchase Order')

@section('page-script')
@vite(['resources/assets/js/purchase-order.js'])
@endsection

@section('content')
<div id="purchaseContent">
  <div id="viewMore"></div>
  <div class="row">
    <div class="col-12">
      <div class="card tbl-card">

        {{-- ── Card header ── --}}
        <div class="po-card-header d-flex align-items-center gap-3">
          <div class="po-hd-icon">
            <i class="bx bx-list-ul fs-4 text-white"></i>
          </div>
          <div>
            <div class="text-white fw-bold mf-hd-title">ข้อมูลรายการจองของลูกค้า</div>
            <div class="text-white mf-hd-sub">Purchase Order List</div>
          </div>
        </div>

        <div class="card-body pt-3">

          {{-- ── Filter bar ── --}}
          <div class="po-filter-bar d-flex align-items-center gap-2 flex-wrap">
            <i class="bx bx-filter-alt text-muted"></i>
            <span class="fw-semibold text-muted tbl-filter-text">กรองข้อมูล :</span>
            <div class="d-flex align-items-center gap-2">
              <label for="filterStatus" class="mb-0 text-muted">สถานะ</label>
              <select id="filterStatus" class="form-select form-select-sm" style="min-width:160px;">
                <option value="">— ทั้งหมด —</option>
                @foreach($conStatus as $status)
                  <option value="{{ $status->id }}">{{ $status->name }}</option>
                @endforeach
              </select>
            </div>
          </div>

          {{-- ── Table ── --}}
          <div class="table-responsive">
            <table class="table table-bordered tbl-table tbl-styled" id="purchaseTable">
              <thead>
                <tr>
                  <th class="tbl-th-no">No.</th>
                  <th>ชื่อ - นามสกุล</th>
                  <th>รุ่นรถ</th>
                  <th>รหัส Car Order</th>
                  <th>วันที่</th>
                  <th class="col-filter-th">
                    <div class="col-filter-wrap">
                      <span>ชื่อฝ่ายขาย</span>
                      <button class="col-filter-btn" id="saleFilterBtn" type="button" title="กรองชื่อฝ่ายขาย">
                        <i class="bx bx-filter-alt"></i>
                        <span class="col-filter-dot"></span>
                      </button>
                    </div>
                  </th>
                  <th>สถานะ</th>
                  <th class="tbl-th-action" style="width:140px;">Action</th>
                </tr>
              </thead>
            </table>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

{{-- ── Sale filter dropdown (outside table/overflow containers, positioned by JS) ── --}}
<div class="col-filter-dropdown" id="saleFilterDropdown">
  <div class="col-filter-search">
    <input type="text" id="saleFilterSearch" placeholder="ค้นหา...">
  </div>
  <div class="col-filter-list" id="saleFilterList"></div>
  <div class="col-filter-actions">
    <button class="btn btn-sm btn-light" id="saleFilterClear">ล้าง</button>
    <button class="btn btn-sm btn-primary" id="saleFilterApply">ตกลง</button>
  </div>
</div>
@endsection
