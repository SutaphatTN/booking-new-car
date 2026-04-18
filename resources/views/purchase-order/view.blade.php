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
        <h4 class="card-header">ข้อมูลรายการจองของลูกค้า</h4>
        <div class="card-body">

          <div class="d-flex align-items-center justify-content-end gap-2 mb-3">
            <label for="filterStatus" class="mb-0 text-nowrap">สถานะ :</label>
            <select id="filterStatus" class="form-select w-auto">
              <option value="">-- ทั้งหมด --</option>
              @foreach($conStatus as $status)
                <option value="{{ $status->id }}">{{ $status->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="table-responsive text-nowrap">
            <table class="table table-bordered" id="purchaseTable">
              <thead>
                <tr>
                  <th>No.</th>
                  <th>ชื่อ - นามสกุล</th>
                  <th>รุ่นรถ</th>
                  <th>รหัส Car Order</th>
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
</div>
@endsection