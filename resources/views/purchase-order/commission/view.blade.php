@extends('layouts/contentNavbarLayout')
@section('title', 'Data Commission Sales')

@section('page-script')
@vite(['resources/assets/js/commission.js'])
@endsection

@section('content')
<div class="viewExportComModel"></div>
<div class="row">
  <div class="col-12">
    <div class="card tbl-card">

      {{-- ── Card header ── --}}
      <div class="po-card-header d-flex align-items-center gap-3">
        <div class="po-hd-icon">
          <i class="bx bx-coin-stack fs-4 text-white"></i>
        </div>
        <div>
          <div class="text-white fw-bold mf-hd-title">ข้อมูลค่าคอมมิชชั่นฝ่ายขาย</div>
          <div class="text-white mf-hd-sub">Commission Report</div>
        </div>
      </div>

      <div class="card-body pt-3">

        {{-- ── Action bar ── --}}
        <div class="po-filter-bar d-flex align-items-center justify-content-end">
          <button class="btn btn-warning btn-sm btnViewExportCom">
            <i class="bx bx-file me-1"></i> รายงานค่าคอม
          </button>
        </div>

        {{-- ── Table ── --}}
        <div class="table-responsive">
          <table class="table table-bordered tbl-table tbl-styled commissionTable">
            <thead>
              <tr>
                <th class="tbl-th-no">No.</th>
                <th>ฝ่ายขาย</th>
                <th>จำนวนคัน</th>
                <th>ยอดค่าคอมมิชชั่น</th>
              </tr>
            </thead>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection
