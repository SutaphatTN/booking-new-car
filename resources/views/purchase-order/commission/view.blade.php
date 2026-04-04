@extends('layouts/contentNavbarLayout')
@section('title', 'Data Commission Sales')

@section('page-script')
@vite(['resources/assets/js/commission.js'])
@endsection

@section('content')
<div class="viewExportComModel"></div>
<div class="row">
  <div class="col-12">
    <div class="card">
      <h4 class="card-header">ข้อมูลค่าคอมมิชชั่นฝ่ายขาย</h4>
      <div class="card-body">
        <div class="table-responsive text-nowrap">
          <div class="d-flex justify-content-end mb-2">
            <button class="btn btn-warning btnViewExportCom">
              <i class="bx bx-file me-1"></i>รายงานค่าคอม
            </button>
          </div>
          <table class="table table-bordered commissionTable">
            <thead>
              <tr>
                <th>No.</th>
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