@extends('layouts/contentNavbarLayout')
@section('title', 'Data Color sub-Model Car')

@section('page-script')
@vite(['resources/assets/js/color.js'])
@endsection

@section('content')
<div class="inputColorSubModal"></div>
<div class="editColorSubModal"></div>
<div class="row">
  <div class="col-12">
    <div class="card">
      <h4 class="card-header">ข้อมูลสีรถของแต่ละรุ่นย่อย</h4>
      <div class="card-body">
        <div class="table-responsive text-nowrap">
          <div class="d-flex justify-content-end">
            <button class="btn btn-secondary btnInputColorSub">เพิ่ม</button>
          </div>
          <table class="table table-bordered ColorSubTable">
            <thead>
              <tr>
                <th>No.</th>
                <th>รุ่นหลัก</th>
                <th>รุ่นย่อย</th>
                <th>สี</th>
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