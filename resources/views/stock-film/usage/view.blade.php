@extends('layouts/contentNavbarLayout')
@section('title', 'บันทึกการใช้ฟิล์ม')

@section('page-script')
@vite(['resources/assets/js/film-usage.js'])
@endsection

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card tbl-card">

      <div class="po-card-header d-flex align-items-center gap-3">
        <div class="po-hd-icon">
          <i class="bx bx-film fs-4 text-white"></i>
        </div>
        <div>
          <div class="text-white fw-bold mf-hd-title">บันทึกการใช้ฟิล์ม</div>
          <div class="text-white mf-hd-sub">Film Usage Record</div>
        </div>
      </div>

      <div class="card-body pt-3">

        <div class="po-filter-bar d-flex align-items-center justify-content-end">
          <a href="{{ route('film-usage.create') }}" class="btn btn-secondary btn-sm">
            <i class="bx bx-plus me-1"></i> บันทึก
          </a>
        </div>

        <div class="table-responsive mt-2">
          <table class="table table-bordered tbl-table tbl-styled filmUsageTable">
            <thead>
              <tr>
                <th class="tbl-th-no">No.</th>
                <th class="text-center">ประเภท</th>
                <th>วันที่สั่งงาน</th>
                <th>เลข VIN</th>
                <th>ชื่อลูกค้า</th>
                <th>รุ่นรถ</th>
                <th>ยี่ห้อฟิล์ม</th>
                <th class="text-end">รวม ตร.ฟุต</th>
                {{-- <th class="text-end">รวมราคา</th> --}}
                <th class="tbl-th-action">Action</th>
              </tr>
            </thead>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection
