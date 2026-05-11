@extends('layouts/contentNavbarLayout')
@section('title', 'Data Customer')

@section('page-script')
  @vite(['resources/assets/js/customer.js'])
@endsection

@section('content')
<div id="customerContent">
  <div id="viewMore"></div>
  <div id="editCust"></div>
  <div class="row">
    <div class="col-12">
      <div class="card tbl-card">

        {{-- ── Card header ── --}}
        <div class="po-card-header d-flex align-items-center gap-3">
          <div class="po-hd-icon">
            <i class="bx bx-group fs-4 text-white"></i>
          </div>
          <div>
            <div class="text-white fw-bold mf-hd-title">ข้อมูลรายชื่อลูกค้า</div>
            <div class="text-white mf-hd-sub">Customer List</div>
          </div>
        </div>

        <div class="card-body pt-3">

          {{-- ── Search / action bar ── --}}
          <div class="po-filter-bar d-flex align-items-center gap-2 flex-wrap">
            <i class="bx bx-search text-muted"></i>
            <span class="fw-semibold text-muted tbl-filter-text">ค้นหา :</span>
            <input type="text" id="customerSearchInput" class="form-control form-control-sm"
              placeholder="ชื่อ, นามสกุล หรือเบอร์โทร"
              style="max-width:260px;">
            <button type="button" id="btnSearchCustomer" class="btn btn-primary btn-sm">
              <i class="bx bx-search me-1"></i> ค้นหา
            </button>
            <a href="{{ route('customer.create') }}" class="btn btn-success btn-sm ms-auto">
              <i class="bx bx-plus me-1"></i> เพิ่มข้อมูลลูกค้า
            </a>
          </div>

          {{-- ── Table ── --}}
          <div class="table-responsive">
            <table class="table table-bordered tbl-table tbl-styled" id="customerTable">
              <thead>
                <tr>
                  <th class="tbl-th-no">No.</th>
                  <th>ชื่อ - นามสกุล</th>
                  <th>เลขบัตรประชาชน</th>
                  <th>เบอร์โทรศัพท์</th>
                  <th class="tbl-th-action" style="width:130px;">Action</th>
                </tr>
              </thead>
            </table>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>
@endsection
