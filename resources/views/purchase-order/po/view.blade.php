@extends('layouts/contentNavbarLayout')
@section('title', 'Date Booking PO')

@section('page-script')
@vite(['resources/js/pages/purchase-order.js'])
@endsection

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <h4 class="card-header">ข้อมูล PO</h4>
      <div class="card-body">
        <div class="table-responsive text-nowrap">
          <table class="table table-bordered poTable">
            <thead>
              <tr>
                <th>No.</th>
                <th>ชื่อ - นามสกุล</th>
                <th>รุ่นรถหลัก</th>
                <th>รุ่นรถย่อย</th>
                <th>เลข PO-Number</th>
                <th>จำนวนวันคงเหลือ</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection