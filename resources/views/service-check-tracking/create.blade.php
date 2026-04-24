@extends('layouts/contentNavbarLayout')
@section('title', 'ติดตามเช็คระยะ')

@section('page-script')
  @vite(['resources/assets/js/service-check-tracking.js'])
@endsection

@section('content')
  <div class="row">
    <div class="col-12">
      <div class="card">
        <h4 class="card-header">ค้นหาลูกค้าเพื่อเพิ่มการติดตามเช็คระยะ</h4>
        <div class="card-body">

          <div class="row mb-3 d-flex justify-content-center">
            <div class="col-md-5">
              <input type="text" id="salecarSearchInput" class="form-control"
                placeholder="ค้นหาด้วย ชื่อ, นามสกุล, เบอร์โทร หรือ VIN Number">
            </div>
            <div class="col-md-2 d-flex align-items-end">
              <button type="button" id="btnSearchSalecar" class="btn btn-primary">
                <i class="bx bx-search me-1"></i> ค้นหา
              </button>
            </div>
          </div>

          <div id="searchResultArea" style="display:none;">
            <div class="table-responsive text-nowrap">
              <table class="table table-bordered" id="searchResultTable">
                <thead>
                  <tr>
                    <th>No.</th>
                    <th>ชื่อ - นามสกุล</th>
                    <th>เบอร์โทร</th>
                    <th>รุ่นรถ / สี</th>
                    <th>VIN Number</th>
                    <th>วันส่งมอบ</th>
                    <th width="150px">Action</th>
                  </tr>
                </thead>
                <tbody id="searchResultBody">
                </tbody>
              </table>
            </div>
          </div>

          <div id="noResultMsg" class="text-center text-muted py-4" style="display:none;">
            <i class="bx bx-search-alt fs-3 d-block mb-1 opacity-50"></i>
            ไม่พบข้อมูล
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
