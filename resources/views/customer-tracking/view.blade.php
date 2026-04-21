@extends('layouts/contentNavbarLayout')
@section('title', 'รายการติดตามลูกค้า')

@section('page-script')
  @vite(['resources/assets/js/customer-tracking.js'])
@endsection

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <h4 class="card-header">รายการติดตามลูกค้า</h4>
      {{-- <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">รายการติดตามลูกค้า</h5>
        <a href="{{ route('customer-tracking.create') }}" class="btn btn-primary btn-sm">
          <i class="bx bx-plus me-1"></i> เพิ่มการติดตาม
        </a> 
      </div> --}}
      <div class="card-body">
        @if(session('success'))
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        @endif
        @if(session('error'))
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        @endif

        <div class="table-responsive text-nowrap">
          <table class="table table-bordered" id="trackingTable">
            <thead>
              <tr>
                <th>No.</th>
                <th>ชื่อ - นามสกุล</th>
                <th>ข้อมูลรถ</th>
                <th>ผู้ขาย</th>
                <th>รายละเอียด</th>
                <th width="120px">Action</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Modal ยืนยันลบ --}}
<div class="modal fade" id="modalConfirmDelete" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">ยืนยันการลบ</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">ต้องการลบรายการนี้ใช่หรือไม่?</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="button" class="btn btn-danger" id="btnConfirmDelete">ลบ</button>
      </div>
    </div>
  </div>
</div>
@endsection
