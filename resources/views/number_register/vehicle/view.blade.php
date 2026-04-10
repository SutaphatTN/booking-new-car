@extends('layouts/contentNavbarLayout')
@section('title', 'Data Vehicle Plate')

@section('page-script')
  @vite(['resources/assets/js/vehicle.js'])
@endsection

@section('content')
  <div class="viewMoreVehicleModel"></div>
  <div class="editVehicleModel"></div>
  <div class="viewWithdrawalModel"></div>
  <div class="viewExportVehicleModel"></div>
  <div class="row">
    <div class="col-12">
      <div class="card">
        <h4 class="card-header">ข้อมูลป้ายทะเบียน</h4>
        <div class="card-body">
          <div class="table-responsive text-nowrap">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div class="d-flex gap-2">
                <div class="btn-group">
                  <button type="button" class="btn btn-warning dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    รายงาน
                  </button>
                  <ul class="dropdown-menu">
                    <li><button type="button" class="dropdown-item btnViewExportVehicle">รายงานการส่งเบิก/เคลียร์</button></li>
                    <li><a class="dropdown-item" href="{{ route('vehicle.export-license-plate') }}">รายงานป้ายทะเบียน</a></li>
                  </ul>
                </div>

                <button class="btn btn-info btnViewWithdrawal">
                  ส่งเบิก/เคลียร์
                </button>
              </div>

              <div>
                <select id="withdrawalStatusFilter" class="form-select">
                  <option value="unWithdrawal" selected>ยังไม่ได้ตั้งเบิก</option>
                  <option value="withdrawal">รอเคลียร์</option>
                  <option value="cleared">เคลียร์แล้ว</option>
                  <option value="all">ทั้งหมด</option>
                </select>
              </div>

            </div>
            <table class="table table-bordered vehicleTable">
              <thead>
                <tr>
                  <th>No.</th>
                  <th>ชื่อ - สกุล</th>
                  <th>ข้อมูลเลข</th>
                  <th>จังหวัดที่ขึ้นทะเบียน</th>
                  <th>ยอดตั้งเบิก</th>
                  <th>ยอดเคลียร์</th>
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
