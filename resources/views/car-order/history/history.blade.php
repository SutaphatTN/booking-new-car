@extends('layouts/contentNavbarLayout')
@section('title', 'Data Car Order')

@section('page-script')
@vite(['resources/assets/js/car-order.js'])
@endsection

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <h4 class="card-header">ประวัติการสั่งรถ</h4>
      <div class="card-body">
        <div class="table-responsive text-nowrap">
          <table class="table table-bordered historyCarOrderTable">
            <thead>
              <tr>
                <th>No.</th>
                <th>ชื่อ - นามสกุล ลูกค้า</th>
                <th>รหัส Car Order</th>
                <th>รุ่นรถหลัก</th>
                <th>รุ่นรถย่อย</th>
                <th>วันที่จอง</th>
                <th>สถานะใบจอง</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection