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
  <div class="modal fade" id="confirmReceiptModal" tabindex="-1" role="dialog" data-bs-backdrop="static">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
      <div class="modal-content border-0 shadow mf-content mf-content--input">

        {{-- Header --}}
        <div class="modal-header mf-header mf-header--input px-4">
          <div class="d-flex align-items-center gap-3">
            <div class="mf-hd-icon">
              <i class="bx bx-receipt fs-5 text-white"></i>
            </div>
            <div>
              <h6 class="mb-0 fw-bold text-white mf-hd-title">ยืนยันออกใบเสร็จ</h6>
              <small class="text-white mf-hd-sub">Confirm Receipt</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body mf-body">
          <label class="form-label">วันที่จ่ายเงิน <span class="text-danger">*</span></label>
          <input type="date" id="receiptConfirmedDate" class="form-control">

          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">ยกเลิก</button>
            <button type="button" id="btnSubmitConfirmReceipt" class="btn btn-primary text-white">ยืนยัน</button>
          </div>
        </div>

      </div>
    </div>
  </div>

@endsection
