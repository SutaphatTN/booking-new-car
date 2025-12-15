@extends('layouts/contentNavbarLayout')
@section('title', 'Data Booking Finance')

@section('page-script')
@vite(['resources/assets/js/finance.js'])
@endsection

@section('content')
<div class="editFinConfirmModal"></div>
<div class="viewMoreFinConfirmModal"></div>
<div class="row">
  <div class="col-12">
    <div class="card">
      <h4 class="card-header">ยอดเฟิร์มเงิน FN</h4>
      <div class="card-body">
        <div class="table-responsive text-nowrap">
          <table class="table table-bordered confirmFNTable">
            <thead>
              <tr>
                <th>No.</th>
                <th>ชื่อ - นามสกุล</th>
                <th>รุ่นรถหลัก</th>
                <th>รุ่นรถย่อย</th>
                <th>เลข PO-Number</th>
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