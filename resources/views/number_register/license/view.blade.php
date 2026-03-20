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
      <div class="card">
        <h4 class="card-header">ข้อมูลป้ายแดง</h4>
        <div class="card-body">
          <div class="table-responsive text-nowrap">
            <div class="d-flex justify-content-between mb-3">
              <div class="dropdown">
                <button class="btn btn-warning dropdown-toggle" type="button" data-bs-toggle="dropdown">
                  รายงาน
                </button>

                <ul class="dropdown-menu">
                  <li>
                    <a class="dropdown-item" data-type="stock" href="{{ route('license.stock-export') }}">
                      Stock
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item btnExportLicenseAll" data-type="all" href="#">
                      ทั้งหมด
                    </a>
                  </li>
                </ul>
              </div>
            </div>
            <table class="table table-bordered licenseTable">
              <thead>
                <tr>
                  <th>No.</th>
                  <th>เลขป้ายแดง</th>
                  <th>ลูกค้า</th>
                  <th>ฝ่ายขาย</th>
                  <th>วันที่ส่งมอบจริง</th>
                  <th width="150px">Action</th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
