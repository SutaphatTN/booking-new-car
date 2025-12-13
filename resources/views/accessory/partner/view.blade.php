@extends('layouts/contentNavbarLayout')
@section('title', 'Partner Accessory')

@section('page-script')
@vite(['resources/js/app.js'])
@endsection

@section('content')
<div class="inputPartModal"></div>
<div class="editPartModal"></div>
<div class="row">
  <div class="col-12">
    <div class="card">
      <h4 class="card-header">ข้อมูลรายชื่อแหล่งที่มาของประดับยนต์</h4>
      <div class="card-body">
        <div class="table-responsive text-nowrap">
          <div class="d-flex justify-content-end">
            <button class="btn btn-secondary btnInputPart">เพิ่ม</button>
          </div>
          <table class="table table-bordered partnerTable">
            <thead>
              <tr>
                <th>No.</th>
                <th>ชื่อร้าน</th>
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