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

          <!-- <div class="row mb-3">
            <div class="col-md-3">
              <label class="form-label">สถานะ</label>
              <select id="filterStatus" class="form-select">
                <option value="">-- ทั้งหมด --</option>
                @foreach ($conStatus as $st)
                <option value="{{ $st->name }}">{{ $st->name }}</option>
                @endforeach
              </select>
            </div>
          </div> -->

          <div class="table-responsive text-nowrap">
            <table class="table table-bordered" id="purchaseTable">
              <thead>
                <tr>
                  <th>No.</th>
                  <th>ชื่อ - นามสกุล</th>
                  <th>รุ่นรถย่อย</th>
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