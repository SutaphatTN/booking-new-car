@extends('layouts/contentNavbarLayout')
@section('title', 'Data Booking Car')

@section('page-script')
@vite(['resources/assets/js/purchase-order.js'])
@endsection

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <h4 class="card-header">ข้อมูลการจองรถ</h4>
      <div class="card-body">

        <div class="row mb-3">
          <div class="col-md-4">
            <label>รุ่นรถหลัก</label>
            <select id="filterModel" class="form-select">
              <option value="">-- ทั้งหมด --</option>
              @foreach($models as $m)
              <option value="{{ $m->id }}">{{ $m->Name_TH }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-4">
            <label>รุ่นรถย่อย</label>
            <select id="filterSubModel" class="form-select" disabled>
              <option value="">-- ทั้งหมด --</option>
            </select>
          </div>

          <div class="col-md-2">
            <label>วันที่จอง (เริ่ม)</label>
            <input type="date" id="filterBookingStart" class="form-control">
          </div>
          <div class="col-md-2">
            <label>วันที่จอง (สิ้นสุด)</label>
            <input type="date" id="filterBookingEnd" class="form-control">
          </div>
          <div class="col-md-3 mt-3">
            <label>สถานะ</label>
            <select id="filterStatus" class="form-select">
              <option value="">-- ทั้งหมด --</option>
              @foreach($statuses as $status)
              <option value="{{ $status->id }}">{{ $status->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-4  mt-3">
            <label>&nbsp;</label>
            <button id="btnSearch" class="btn btn-primary w-100">
              ค้นหา
            </button>
          </div>
        </div>

        <div class="table-responsive text-nowrap">
          <table class="table table-bordered bookingTable">
            <thead>
              <tr>
                <th>No.</th>
                <th>รุ่นรถหลัก</th>
                <th>รุ่นรถย่อย</th>
                <th>Option</th>
                <th>รหัส Car Order</th>
                <th>ชื่อ - นามสกุล ผู้จอง</th>
                <th>ชื่อผู้ขาย</th>
                <th>วันที่จอง</th>
                <th>ผูกรถแล้วกี่วัน</th>
                <th>สถานะ</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection