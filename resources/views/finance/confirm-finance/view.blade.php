@extends('layouts/contentNavbarLayout')
@section('title', 'Data Booking Finance')

@section('page-script')
@vite(['resources/assets/js/finance.js'])
@endsection

@section('content')
<div class="editFinConfirmModal"></div>
<div class="viewMoreFinConfirmModal"></div>
<div class="viewExportFirmModel"></div>
<div class="row">
  <div class="col-12">
    <div class="card">
      <h4 class="card-header">ยอดเฟิร์มเงิน FN</h4>
      <div class="card-body">
        <div class="table-responsive text-nowrap">
          <div class="d-flex justify-content-between mb-3">
            <button class="btn btn-warning btnViewExportFirm">รายงาน Firm FN</button>

            <div>
              <select id="fnStatusFilter" class="form-select">
                <option value="unpaid" selected>ยังไม่ได้รับเงิน</option>
                <option value="paid">รับเงินแล้ว</option>
                <option value="all">ทั้งหมด</option>
              </select>
            </div>
          </div>
          <table class="table table-bordered confirmFNTable">
            <thead>
              <tr>
                <th>No.</th>
                <th>ชื่อ - นามสกุล</th>
                <th>ชื่อไฟแนนซ์</th>
                <th>วันที่ส่งมอบ</th>
                <th>วันที่เฟิร์มเคส</th>
                <th>วันที่ได้รับเงิน</th>
                <th>Action</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection