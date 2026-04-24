@extends('layouts/contentNavbarLayout')
@section('title', 'รายการติดตามเช็คระยะ')

@section('page-script')
  @vite(['resources/assets/js/service-check-tracking.js'])
@endsection

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      {{-- <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">รายการติดตามเช็คระยะ</h5>
        <a href="{{ route('service-check-tracking.create') }}" class="btn btn-primary btn-sm">
          <i class="bx bx-plus me-1"></i> เพิ่มการติดตาม
        </a>
      </div> --}}
       <h4 class="card-header">รายการติดตามเช็คระยะ</h4>

      <div class="card-body">
        <div class="table-responsive text-nowrap">
          <table class="table table-bordered" id="serviceCheckTrackingTable">
            <thead>
              <tr>
                <th>No.</th>
                <th>ชื่อ - นามสกุล</th>
                <th>รุ่นรถ / สี</th>
                <th>VIN Number</th>
                <th>วันส่งมอบ</th>
                <th>เช็คระยะล่าสุด</th>
                <th width="120px">Action</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection
