@extends('layouts/contentNavbarLayout')
@section('title', 'Data Name Campaign')

@section('page-script')
@vite(['resources/assets/js/campaign.js'])
@endsection

@section('content')
<div class="inputCamAppellationModal"></div>
<div class="editCamAppellationModal"></div>
<div class="row">
  <div class="col-12">
    <div class="card tbl-card">

      {{-- ── Card header ── --}}
      <div class="po-card-header d-flex align-items-center gap-3">
        <div class="po-hd-icon">
          <i class="bx bx-purchase-tag fs-4 text-white"></i>
        </div>
        <div>
          <div class="text-white fw-bold mf-hd-title">ข้อมูลชื่อแคมเปญ</div>
          <div class="text-white mf-hd-sub">Campaign Appellation</div>
        </div>
      </div>

      <div class="card-body pt-3">

        {{-- ── Action bar ── --}}
        <div class="po-filter-bar d-flex align-items-center justify-content-end">
          <button class="btn btn-secondary btn-sm btnInputCamAppellation">
            <i class="bx bx-plus me-1"></i> เพิ่ม
          </button>
        </div>

        {{-- ── Table ── --}}
        <div class="table-responsive">
          <table class="table table-bordered tbl-table tbl-styled campaignAppellationTable">
            <thead>
              <tr>
                <th class="tbl-th-no">No.</th>
                <th>ชื่อแคมเปญ</th>
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
