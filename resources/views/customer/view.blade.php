@extends('layouts/contentNavbarLayout')
@section('title', 'Data Customer')

@section('page-script')
@vite(['resources/assets/js/customer.js'])
@endsection

@section('content')
<div id="customerContent">
<div id="viewMore"></div>
<div id="editCust"></div>
<div class="row">
  <div class="col-12">
    <div class="card">
      <h4 class="card-header">ข้อมูลรายชื่อลูกค้า</h4>
      <div class="card-body">
        <div class="table-responsive text-nowrap">
          <table class="table table-bordered" id="customerTable">
            <thead>
              <tr>
                <th>No.</th>
                <th>ชื่อ - นามสกุล</th>
                <th>เลขบัตรประชาชน</th>
                <th>เบอร์โทรศัพท์</th>
                <th width="150px">Action</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
</div>
@endsection