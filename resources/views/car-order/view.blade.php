@extends('layouts/contentNavbarLayout')
@section('title', 'Data Car Order')

@section('page-script')
@vite(['resources/assets/js/car-order.js'])
@endsection

@section('content')
<div class="viewMoreCarOrder"></div>
<div class="editCarOrderModal"></div>
<div class="row">
  <div class="col-12">
    <div class="card">
      <h4 class="card-header">รายการรถ</h4>
      <div class="card-body">
        <div class="table-responsive text-nowrap">
          <table class="table table-bordered carOrderTable">
            <thead>
              <tr>
                <th>No.</th>
                <th>วันที่สั่งซื้อในระบบ</th>
                <th>รุ่นรถ</th>
                <th>Vin Number</th>
                <th>J Number</th>
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