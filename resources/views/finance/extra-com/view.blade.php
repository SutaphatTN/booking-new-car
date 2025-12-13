@extends('layouts/contentNavbarLayout')
@section('title', 'Data Finance')

@section('page-script')
@vite(['resources/js/pages/finance.js'])
@endsection

@section('content')
<div class="inputFinExtraComModal"></div>
<div class="editFinExtraComModal"></div>
<div class="row">
  <div class="col-12">
    <div class="card">
      <h4 class="card-header">ข้อมูลไฟแนนซ์ Com Extra</h4>
      <div class="card-body">
        <div class="table-responsive text-nowrap">
          <div class="d-flex justify-content-end">
            <button class="btn btn-secondary btnInputFinExtraCom">เพิ่ม</button>
          </div>
          <table class="table table-bordered financeExtraComTable">
            <thead>
              <tr>
                <th>No.</th>
                <th>ชื่อไฟแนนซ์</th>
                <th>รุ่นรถหลัก</th>
                <th>ยอด Com Extra</th>
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