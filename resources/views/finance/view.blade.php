@extends('layouts/contentNavbarLayout')
@section('title', 'Data Finance Com Extra')

@section('page-script')
@vite(['resources/js/pages/finance.js'])
@endsection

@section('content')
<div class="inputFinModal"></div>
<div class="editFinModal"></div>
<div class="row">
  <div class="col-12">
    <div class="card">
      <h4 class="card-header">ข้อมูลไฟแนนซ์</h4>
      <div class="card-body">
        <div class="table-responsive text-nowrap">
          <div class="d-flex justify-content-end">
            <button class="btn btn-secondary btnInputFin">เพิ่ม</button>
          </div>
          <table class="table table-bordered financeTable">
            <thead>
              <tr>
                <th>No.</th>
                <th>ชื่อไฟแนนซ์</th>
                <th>ภาษีที่หัก ณ ที่จ่าย</th>
                <th>จำนวนปีสูงสุด</th>
                <th>Update ล่าสุด</th>
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