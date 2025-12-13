@extends('layouts/contentNavbarLayout')
@section('title', 'Data Model Car')

@section('page-script')
@vite(['resources/js/app.js'])
@endsection

@section('content')
<div class="inputCarModal"></div>
<div class="editCarModal"></div>
<div class="row">
  <div class="col-12">
    <div class="card">
      <h4 class="card-header">ข้อมูลรุ่นรถหลัก</h4>
      <div class="card-body">
        <div class="table-responsive text-nowrap">
          <div class="d-flex justify-content-end">
            <button class="btn btn-secondary btnInputCar">เพิ่ม</button>
          </div>
          <table class="table table-bordered carTable">
            <thead>
              <tr>
                <th>No.</th>
                <th>ชื่อรุ่นรถภาษาไทย</th>
                <th>ชื่อรุ่นรถภาษาอังกฤษ</th>
                <th>ยอดเงินเกินงบ</th>
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