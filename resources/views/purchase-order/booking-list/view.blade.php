@extends('layouts/contentNavbarLayout')
@section('title', 'Data Booking Car')

@section('page-script')
@vite(['resources/assets/js/purchase-order.js'])
@endsection

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <h4 class="card-header">ข้อมูลการจองรถ</h4>
      <div class="card-body">
        <div class="table-responsive text-nowrap">
          <table class="table table-bordered bookingTable">
            <thead>
              <tr>
                <th>No.</th>
                <th>รุ่นรถหลัก</th>
                <th>รุ่นรถย่อย</th>
                <th>Option</th>
                <th>รหัส Car Order</th>
                <th>ชื่อ - นามสกุล ผู้จอง</th>
                <th>วันที่จอง</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection