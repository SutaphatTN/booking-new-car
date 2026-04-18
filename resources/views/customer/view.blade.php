@extends('layouts/contentNavbarLayout')
@section('title', 'Data Customer')

@section('page-style')
  @vite(['resources/css/app.css'])
@endsection

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
            <div class="d-flex gap-2 mb-3">
              <input type="text" id="customerSearchInput" class="form-control"
                placeholder="ค้นหาด้วย ชื่อ, นามสกุล หรือเบอร์โทร" style="max-width: 300px;">
              <button type="button" id="btnSearchCustomer" class="btn btn-primary">
                <i class="bx bx-search me-1"></i> ค้นหา
              </button>
              <a href="{{ route('customer.create') }}" class="btn btn-success ms-auto">
                <i class="bx bx-plus me-1"></i> เพิ่มข้อมูลลูกค้า
              </a>
            </div>
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
