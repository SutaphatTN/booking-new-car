@extends('layouts/contentNavbarLayout')
@section('title', 'Data Invoice')

@section('page-script')
  @vite(['resources/assets/js/invoice.js'])
@endsection

@section('content')
  <div class="row">
    <div class="col-12">
      <div class="card">
        <h4 class="card-header">รายการใบสั่งซื้อ</h4>
        
        <div class="card-body">
          @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              {{ session('success') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          @endif

          <div class="table-responsive text-nowrap">
            <div class="d-flex justify-content-between mb-3">
              <a href="{{ route('invoice.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-2"></i>สร้างใบสั่งซื้อ
              </a>

              <div>
                <select id="invoiceStatusFilter" class="form-select">
                  <option value="pending" selected>วางบิล</option>
                  <option value="paid">จ่ายเงิน</option>
                  <option value="all">ทั้งหมด</option>
                </select>
              </div>
            </div>
            <table class="table table-bordered invoiceTable">
              <thead>
                <tr>
                  <th>No.</th>
                  <th>ชื่อลูกค้า</th>
                  <th>ชื่อร้าน</th>
                  <th>รายละเอียด</th>
                  <th>ยอดเงิน</th>
                  <th>วันที่</th>
                  <th width="80px">Action</th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Modal ยืนยันออกใบเสร็จ --}}
  <div class="modal fade" id="confirmReceiptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">ยืนยันออกใบเสร็จ</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <label class="form-label">วันที่รับเงิน <span class="text-danger">*</span></label>
          <input type="date" id="receiptConfirmedDate" class="form-control">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="button" id="btnSubmitConfirmReceipt" class="btn btn-warning text-white">ยืนยัน</button>
        </div>
      </div>
    </div>
  </div>

@endsection
