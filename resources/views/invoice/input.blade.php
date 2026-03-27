@extends('layouts/contentNavbarLayout')
@section('title', 'create invoice')

@section('page-script')
@vite(['resources/assets/js/invoice.js'])
@endsection

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <h4 class="card-header">สร้างใบสั่งซื้อ</h4>
      <div class="card-body">

        <form id="invoiceForm" action="{{ route('invoice.store') }}" method="POST"
          enctype="multipart/form-data">
          @csrf

          <div class="row g-3 mb-4">

            <div class="col-md-4">
              <label class="form-label">ชื่อลูกค้า <span class="text-danger">*</span></label>
              <input type="text" name="customer_name" class="form-control @error('customer_name') is-invalid @enderror"
                value="{{ old('customer_name') }}" required>
              @error('customer_name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-3">
              <label class="form-label">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
              <input type="text" id="customer_phone" name="customer_phone" class="form-control @error('customer_phone') is-invalid @enderror" maxlength="12"
                value="{{ old('customer_phone') }}" required>
              @error('customer_phone')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-2">
              <label class="form-label">วันที่ <span class="text-danger">*</span></label>
              <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                value="{{ old('date', $today) }}" required>
              @error('date')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="col-md-3">
              <label class="form-label">ป้ายทะเบียน</label>
              <input type="text" name="license_plate" class="form-control"
                value="{{ old('license_plate') }}">
            </div>

            <div class="col-md-4">
              <label class="form-label">เลขเครื่อง</label>
              <input type="text" name="engine_number" class="form-control"
                value="{{ old('engine_number') }}">
            </div>

            <div class="col-md-4">
              <label class="form-label">เลขถัง (VIN)</label>
              <input type="text" name="vin_number" class="form-control"
                value="{{ old('vin_number') }}">
            </div>

            <div class="col-md-4">
              <label class="form-label">ผู้อนุมัติ</label>
              <select name="approved_by" class="form-select">
                <option value="">-- เลือกผู้อนุมัติ --</option>
                @foreach($approvers as $approver)
                  <option value="{{ $approver->id }}" {{ old('approved_by') == $approver->id ? 'selected' : '' }}>
                    {{ $approver->name }}
                  </option>
                @endforeach
              </select>
            </div>

          </div>

          {{-- รายการอุปกรณ์ --}}
          <hr class="mt-8 mb-4">
          <div class="position-relative text-center mb-6">
            <h5 class="mb-0">รายการอุปกรณ์</h5>
            <button type="button" class="btn btn-md btn-secondary position-absolute end-0 top-0" id="btnAddRow">
              <i class="bx bx-plus me-2"></i>เพิ่ม
            </button>
          </div>

          <div class="table-responsive">
            <table class="table table-bordered" id="accessoryTable">
              <thead>
                <tr>
                  <th style="width:30%">ร้านค้า (Partner)</th>
                  <th>รายละเอียด</th>
                  <th style="width:130px">ราคาทุน</th>
                  <th style="width:130px">ราคาขาย</th>
                  <th style="width:60px">ACTION</th>
                </tr>
              </thead>
              <tbody id="accessoryBody">
                <tr class="accessory-row">
                  <td>
                    <select name="accessories[0][acc_partner]" class="form-select form-select-md">
                      <option value="">-- เลือกร้าน --</option>
                      @foreach($partners as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                      @endforeach
                    </select>
                  </td>
                  <td>
                    <input type="text" name="accessories[0][detail]" class="form-control form-control-md" placeholder="รายละเอียด">
                  </td>
                  <td>
                    <input type="text" name="accessories[0][cost_price]" class="form-control form-control-sm money-input" placeholder="0.00">
                  </td>
                  <td>
                    <input type="text" name="accessories[0][sale_price]" class="form-control form-control-sm money-input" placeholder="0.00">
                  </td>
                  <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger btnRemoveRow">
                      <i class="bx bx-trash"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

           <div class="mt-4 d-flex justify-content-end gap-2">
            <button type="submit" class="btn btn-primary">บันทึก</button>
            <a href="{{ route('invoice.index') }}" class="btn btn-danger">ยกเลิก</a>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>
@endsection
