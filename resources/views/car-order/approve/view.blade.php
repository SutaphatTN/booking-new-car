@extends('layouts/contentNavbarLayout')
@section('title', 'Car Order Approve')

@section('page-script')
@vite(['resources/js/app.js'])
@endsection

@section('content')
<div class="editApproveOrderModal"></div>
<div class="row">
  <div class="col-12">
    <div class="card">
      <h4 class="card-header">ผลการอนุมัติ</h4>
      <div class="card-body">
        <div class="table-responsive text-nowrap">
          <table class="table table-bordered approveOrderTable">
            <thead>
              <tr>
                <th>No.</th>
                <th>วันที่อนุมัติ</th>
                <th>ประเภทการสั่ง</th>
                <th>รุ่นรถหลัก</th>
                <th>รุ่นรถย่อย</th>
                <th>สี</th>
                <th>ราคาขาย</th>
                <th>สถานะ</th>
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