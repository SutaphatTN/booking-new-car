@extends('layouts/contentNavbarLayout')
@section('title', 'Car Order Pending')

@section('page-script')
@vite(['resources/assets/js/car-order.js'])
@endsection

@section('content')
<div class="inputCarOrderModal"></div>
<div class="editPendingOrderModal"></div>
<div class="row">
  <div class="col-12">
    <div class="card">
      <h4 class="card-header">รายการคำขอสั่งรถ</h4>
      <div class="card-body">
        <div class="table-responsive text-nowrap">
          <div class="d-flex justify-content-end">
            <button class="btn btn-secondary btnInputCarOrder">เพิ่ม</button>
          </div>
          <table class="table table-bordered pendingOrderTable">
            <thead>
              <tr>
                <th>No.</th>
                <th>รหัส Car Order</th>
                <th>วันที่สั่งซื้อ</th>
                <th>ประเภทการสั่ง</th>
                <th>รุ่นรถ</th>
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