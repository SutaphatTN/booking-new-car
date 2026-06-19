@extends('layouts/contentNavbarLayout')
@section('title', 'Data Vehicle Plate')

@section('page-script')
  @vite(['resources/assets/js/vehicle.js'])
@endsection

@section('content')
  <div class="viewMoreVehicleModel"></div>
  <div class="editVehicleModel"></div>
  <div class="viewWithdrawalModel"></div>
  <div class="viewExportVehicleModel"></div>
  <div class="row">
    <div class="col-12">
      <div class="card tbl-card">

        {{-- ── Card header ── --}}
        <div class="po-card-header d-flex align-items-center gap-3">
          <div class="po-hd-icon">
            <i class="bx bx-car fs-4 text-white"></i>
          </div>
          <div>
            <div class="text-white fw-bold mf-hd-title">ข้อมูลป้ายทะเบียน</div>
            <div class="text-white mf-hd-sub">Vehicle Registration</div>
          </div>
        </div>

        <div class="card-body pt-3">

          {{-- ── Filter / action bar ── --}}
          <div class="po-filter-bar d-flex align-items-center gap-2 flex-wrap">
            <div class="d-flex gap-2">
              <div class="btn-group">
                <button type="button" class="btn btn-warning btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                  <i class="bx bx-file me-1"></i> รายงาน
                </button>
                <ul class="dropdown-menu">
                  <li><button type="button" class="dropdown-item btnViewExportVehicle">รายงานการส่งเบิก/เคลียร์</button></li>
                  <li><a class="dropdown-item" href="{{ route('vehicle.export-license-plate') }}">รายงานป้ายทะเบียน</a></li>
                </ul>
              </div>
              <button class="btn btn-info btn-sm btnViewWithdrawal">
                <i class="bx bx-transfer me-1"></i> ส่งเบิก/เคลียร์
              </button>
            </div>
            <div class="ms-auto d-flex align-items-center gap-2">
              <i class="bx bx-filter-alt text-muted"></i>
              <label for="withdrawalStatusFilter" class="mb-0 text-muted">สถานะ :</label>
              <select id="withdrawalStatusFilter" class="form-select form-select-sm" style="width:180px;">
                <option value="unWithdrawal" selected>ยังไม่ได้ตั้งเบิก</option>
                <option value="withdrawal">รอเคลียร์</option>
                <option value="cleared">เคลียร์แล้ว</option>
                <option value="all">ทั้งหมด</option>
              </select>
            </div>
          </div>

          {{-- ── Table ── --}}
          <div class="table-responsive">
            <table class="table table-bordered tbl-table tbl-styled vehicleTable">
              <thead>
                <tr>
                  <th class="tbl-th-no">No.</th>
                  <th>ชื่อ - สกุล</th>
                  <th>ข้อมูลเลข</th>
                  <th>จังหวัดที่ขึ้นทะเบียน</th>
                  <th>ยอดตั้งเบิก</th>
                  <th>ยอดเคลียร์</th>
                  <th class="tbl-th-action" style="width:150px;">Action</th>
                </tr>
              </thead>
            </table>
          </div>

        </div>
      </div>
    </div>
  </div>

<div id="vehicleLoadingOverlay" style="display:flex;">
  <div class="ct-loading-box">
    <div class="spinner-border text-primary" role="status" style="width:1.4rem;height:1.4rem;"></div>
    <span>กำลังโหลด...</span>
  </div>
</div>
@endsection
