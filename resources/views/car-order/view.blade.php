@extends('layouts/contentNavbarLayout')
@section('title', 'Data Car Order')

@section('page-script')
@vite(['resources/assets/js/car-order.js'])
@endsection

@section('content')
<div class="viewMoreCarOrder"></div>
<div class="inputCarOrderModal"></div>
<div class="editCarOrderModal"></div> 
<div class="row">
  <div class="col-12">
    <div class="card">
      <h4 class="card-header">ข้อมูลการสั่งรถ</h4>
      <div class="card-body">
        <div class="table-responsive text-nowrap">
          <div class="d-flex justify-content-end">
            <button class="btn btn-secondary btnInputCarOrder">เพิ่ม</button>
          </div>
          <table class="table table-bordered carOrderTable">
            <thead>
              <tr>
                <th>No.</th>
                <th>รุ่นรถหลัก</th>
                <th>รุ่นรถย่อย</th>
                <th>Vin Number</th>
                <th>สถานะการสั่งซื้อ</th>
                <th>สถานะรถ</th>
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