@extends('layouts/contentNavbarLayout')
@section('title', 'Data Accessory')

@section('page-script')
@vite(['resources/assets/js/accessory.js'])
@endsection

@section('content')
<div class="viewMoreAccModal"></div>
<div class="inputAccModal"></div>
<div class="editAccModal"></div>
<div class="row">
  <div class="col-12">
    <div class="card">
      <h4 class="card-header">ข้อมูลประดับยนต์</h4>
      <div class="card-body">
        <div class="table-responsive text-nowrap">
          <div class="d-flex justify-content-end">
            <button class="btn btn-secondary btnInputAcc">เพิ่ม</button>
          </div>
          <table class="table table-bordered accessoryTable">
            <thead>
              <tr>
                <th>No.</th>
                <th>ชื่อร้าน</th>
                <th>รหัสอะไหล่</th>
                <th>ราคาทุน</th>
                <th>ราคาขาย (ค่าคอม)</th>
                <th>ราคาพิเศษ</th>
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