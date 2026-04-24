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
          @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              {{ session('success') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          @endif
          @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              {{ session('error') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          @endif

          <div class="d-flex flex-wrap align-items-center justify-content-end gap-2 mb-3">
            <label for="filterDecision" class="mb-0 text-nowrap">สถานะการตัดสินใจ :</label>
            <select id="filterDecision" class="form-select" style="width:auto;min-width:160px;max-width:100%;">
              <option value="">— ทั้งหมด —</option>
              @foreach ($decisions as $d)
                <option value="{{ $d->id }}">{{ $d->name }}</option>
              @endforeach
            </select>
          </div>

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

@endsection
