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

        {{-- ── Action bar ── --}}
        <div class="po-filter-bar d-flex align-items-center justify-content-end">
          <button class="btn btn-secondary btn-sm btnInputCam">
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
                <th>ประเภท</th>
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
@endsection
