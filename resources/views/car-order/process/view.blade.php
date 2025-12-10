@extends('layouts/contentNavbarLayout')
@section('title', 'Car Order Process')

@section('page-script')
@vite(['resources/assets/js/car-order.js'])
@endsection

@section('content')
<div class="editProcessOrderModal"></div>
<div id="openIdHolder" data-open-id="{{ $openId ?? '' }}"></div>
<div class="row">
  <div class="col-12">
    <div class="card">
      <h4 class="card-header">รออนุมัติคำขอสั่งรถ</h4>
      <div class="card-body">
        <div class="table-responsive text-nowrap">
          <table class="table table-bordered processOrderTable">
            <thead>
              <tr>
                <th>No.</th>
                <th>วันที่สั่งซื้อ</th>
                <th>ประเภทการสั่ง</th>
                <th>รุ่นรถหลัก</th>
                <th>รุ่นรถย่อย</th>
                <th>สี</th>
                <th>ราคาขาย</th>
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