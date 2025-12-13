@extends('layouts/contentNavbarLayout')
@section('title', 'Purchase Order History')

@section('page-script')
@vite(['resources/js/pages/purchase-order.js'])
@endsection

@section('content')
<div class="viewMoreHistory"></div>
<div class="row">
  <div class="col-12">
    <div class="card">
      <h4 class="card-header">ประวัติคำสั่งซื้อที่ส่งมอบแล้ว</h4>
      <div class="card-body">
        <div class="table-responsive text-nowrap">
          <table class="table table-bordered historyFinalTable">
            <thead>
              <tr>
                <th>No.</th>
                <th>ชื่อ - นามสกุล ลูกค้า</th>
                <th>รหัส Car Order</th>
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