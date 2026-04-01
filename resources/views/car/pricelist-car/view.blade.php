@extends('layouts/contentNavbarLayout')
@section('title', 'Data Price List Car')

@section('page-script')
@vite(['resources/assets/js/car.js'])
@endsection

@section('content')
<div class="inputPricelistCarModal"></div>
<div class="editPricelistCarModal"></div>
<div class="row">
  <div class="col-12">
    <div class="card">
      <h4 class="card-header">ข้อมูลราคารถ</h4>
      <div class="card-body">
        <div class="table-responsive text-nowrap">
          <div class="d-flex justify-content-end mb-2">
            <button class="btn btn-secondary btnInputPricelistCar">เพิ่ม</button>
          </div>
          <table class="table table-bordered pricelistCarTable">
            <input type="hidden" id="userBrand" value="{{ auth()->user()->brand }}">
            <thead>
              <tr>
                <th>No.</th>
                <th>รุ่นรถ</th>
                <th>Option</th>
                <th>ปี</th>
                <th>ประเภทสี</th>
                <th>ราคาทุน (DNP)</th>
                <th>ราคาขาย (MSRP)</th>
                <th>DM</th>
                <th>RI</th>
                <th>WS</th>
                <th width="100px">Action</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
