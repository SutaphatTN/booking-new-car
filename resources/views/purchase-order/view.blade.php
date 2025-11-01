@extends('layouts/contentNavbarLayout')
@section('title', 'Data Purchase Order')

@section('page-script')
@vite(['resources/assets/js/purchase-order.js'])
@endsection

@section('content')
<div id="purchaseContent">
<div id="viewMore"></div>
<div class="row">
  <div class="col-12">
    <div class="card">
      <h4 class="card-header" style="text-align:center;">ข้อมูลรายการจองของลูกค้า</h4>
      <div class="card-body">
        <div class="table-responsive text-nowrap">
          <table class="table table-bordered" id="purchaseTable">
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