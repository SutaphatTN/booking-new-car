@extends('layouts/contentNavbarLayout')
@section('title', 'Data License Plate')

@section('page-script')
  @vite(['resources/assets/js/license.js'])
@endsection

@section('content')
  <div class="viewMoreLicenseModel"></div>
  <div class="editLicenseModel"></div>
  <div class="viewExportLicenseAllModel"></div>
  <div class="row">
    <div class="col-12">
      <div class="card tbl-card">

        {{-- ── Card header ── --}}
        <div class="po-card-header d-flex align-items-center gap-3">
          <div class="po-hd-icon">
            <i class="bx bx-id-card fs-4 text-white"></i>
          </div>
          <div>
            <div class="text-white fw-bold mf-hd-title">ข้อมูลป้ายแดง</div>
            <div class="text-white mf-hd-sub">License Plate</div>
          </div>
        </div>

        <div class="card-body pt-3">

          {{-- ── Action bar ── --}}
          <div class="po-filter-bar d-flex align-items-center gap-2 justify-content-end">
            <div class="dropdown">
              <button class="btn btn-warning btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="bx bx-file me-1"></i> รายงาน
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li>
                  <a class="dropdown-item" data-type="stock" href="{{ route('license.stock-export') }}">
                    ทั้งหมด
                  </a>
                </li>
                <li>
                  <a class="dropdown-item btnExportLicenseAll" data-type="all" href="#">
                    ประวัติการใช้
                  </a>
                </li>
              </ul>
            </div>
          </div>

          {{-- ── Table ── --}}
          <div class="table-responsive">
            <table class="table table-bordered tbl-table tbl-styled licenseTable">
              <thead>
                <tr>
                  <th class="tbl-th-no">No.</th>
                  <th>เลขป้ายแดง</th>
                  <th>ลูกค้า</th>
                  <th>ฝ่ายขาย</th>
                  <th>วันที่ส่งมอบจริง</th>
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
