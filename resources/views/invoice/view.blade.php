@extends('layouts/contentNavbarLayout')
@section('title', 'Data Invoice')

@section('page-script')
  @vite(['resources/assets/js/invoice.js'])
@endsection

@section('content')
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="d-flex card-header justify-content-center align-items-center position-relative">
          <h4 class="mb-0">รายการใบสั่งซื้อ</h4>
          <a href="{{ route('invoice.create') }}" class="btn btn-primary btn-md position-absolute end-0 me-3">
            <i class="bx bx-plus me-2"></i>สร้างใบสั่งซื้อ
          </a>
        </div>

        <div class="card-body">
          @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              {{ session('success') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          @endif
          <div class="table-responsive text-nowrap">
            <table class="table table-bordered invoiceTable">
              <thead>
                <tr>
                  <th>No.</th>
                  <th>รหัส</th>
                  <th>ชื่อลูกค้า</th>
                  <th>เบอร์</th>
                  <th>ป้ายทะเบียน</th>
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
@endsection
