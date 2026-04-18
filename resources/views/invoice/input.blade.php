@extends('layouts/contentNavbarLayout')
@section('title', 'create invoice')

@section('page-style')
  @vite(['resources/assets/css/invoice.css'])
@endsection

@section('page-script')
  @vite(['resources/assets/js/invoice.js'])
@endsection

@section('content')

  <form id="invoiceForm" action="{{ route('invoice.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    {{-- Page Title --}}
    <div class="inv-page-title">
      <div class="inv-page-icon">
        <i class="bx bxs-file-plus"></i>
      </div>
      <div>
        <h5 class="inv-page-name">สร้างใบสั่งซื้อ</h5>
      </div>
    </div>

    <div class="row">
      {{-- Section 1 : ข้อมูลลูกค้า --}}
      <div class="col-md-6">
        <div class="inv-section">
          <div class="inv-section-hd">
            <div class="inv-section-icon indigo"><i class="bx bx-user"></i></div>
            <h6 class="inv-section-title">ข้อมูลลูกค้า</h6>
          </div>
          <div class="inv-section-body">
            <div class="row g-3 pb-2">

              <div class="col-md-8">
                <label for="customer_name" class="inv-label">
                  <i class="bx bx-user"></i> ชื่อลูกค้า <span class="text-danger">*</span>
                </label>
                <input id="customer_name" type="text" name="customer_name"
                  class="form-control @error('customer_name') is-invalid @enderror" value="{{ old('customer_name') }}"
                  required>
                @error('customer_name')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-4">
                <label for="date" class="inv-label">
                  <i class="bx bx-calendar"></i> วันที่ <span class="text-danger">*</span>
                </label>
                <input id="date" type="date" name="date"
                  class="form-control @error('date') is-invalid @enderror" value="{{ old('date', $today) }}" required>
                @error('date')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-5">
                <label for="customer_phone" class="inv-label">
                  <i class="bx bx-phone"></i> เบอร์โทรศัพท์ <span class="text-danger">*</span>
                </label>
                <input id="customer_phone" type="text" name="customer_phone" maxlength="12"
                  class="form-control @error('customer_phone') is-invalid @enderror" value="{{ old('customer_phone') }}"
                  required>
                @error('customer_phone')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-7">
                <label for="approved_by" class="inv-label">
                  <i class="bx bx-user-check"></i> ผู้อนุมัติ <span class="text-danger">*</span>
                </label>
                <select id="approved_by" name="approved_by" class="form-select" required>
                  <option value="">-- เลือกผู้อนุมัติ --</option>
                  @foreach ($approvers as $approver)
                    <option value="{{ $approver->id }}" {{ old('approved_by') == $approver->id ? 'selected' : '' }}>
                      {{ $approver->name }}
                    </option>
                  @endforeach
                </select>
              </div>

            </div>
          </div>
        </div>
      </div>

      {{-- Section 2 : ข้อมูลรถ --}}
      <div class="col-md-6">
        <div class="inv-section">
          <div class="inv-section-hd">
            <div class="inv-section-icon sky"><i class="bx bx-car"></i></div>
            <h6 class="inv-section-title">ข้อมูลรถ</h6>
          </div>
          <div class="inv-section-body">
            <div class="row g-3 pb-2">

              <div class="col-md-4">
                <label for="license_plate" class="inv-label">
                  <i class="bx bx-id-card"></i> ป้ายทะเบียน
                </label>
                <input id="license_plate" type="text" name="license_plate" class="form-control"
                  value="{{ old('license_plate') }}">
              </div>

              <div class="col-md-8">
                <label for="engine_number" class="inv-label">
                  <i class="bx bx-cog"></i> เลขเครื่อง
                </label>
                <input id="engine_number" type="text" name="engine_number" class="form-control"
                  value="{{ old('engine_number') }}">
              </div>

              <div class="col-md-8">
                <label for="vin_number" class="inv-label">
                  <i class="bx bx-barcode"></i> เลขถัง (VIN)
                </label>
                <input id="vin_number" type="text" name="vin_number" class="form-control"
                  value="{{ old('vin_number') }}">
              </div>

            </div>
          </div>
        </div>
      </div>

      {{-- Section 3 : รายการอุปกรณ์ --}}
      <div class="col-md-12">
        <div class="inv-section">
          <div class="inv-section-hd" style="justify-content:space-between;">
            <div class="d-flex align-items-center gap-2">
              <div class="inv-section-icon emerald"><i class="bx bx-package"></i></div>
              <h6 class="inv-section-title">รายการอุปกรณ์</h6>
            </div>
            <button type="button" class="btn btn-success" id="btnAddRow">
              <i class="bx bx-plus me-1"></i>เพิ่มรายการ
            </button>
          </div>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="accessoryTable" style="min-width:700px;">
              <thead class="table-success">
                <tr>
                  <th style="width:30%;min-width:180px;padding:10px 22px;">ร้านค้า (Partner)</th>
                  <th style="padding:10px 14px;">รายละเอียด</th>
                  <th class="text-center" style="width:150px;min-width:150px;padding:10px 14px;">ราคาทุน</th>
                  <th class="text-center" style="width:150px;min-width:150px;padding:10px 14px;">ราคาขาย</th>
                  <th class="text-center" style="width:60px;padding:10px 14px;">ลบ</th>
                </tr>
              </thead>
              <tbody id="accessoryBody">
                <tr class="accessory-row">
                  <td style="padding:8px 22px;">
                    <select name="accessories[0][acc_partner]" class="form-select" required>
                      <option value="">-- เลือกร้าน --</option>
                      @foreach ($partners as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                      @endforeach
                    </select>
                  </td>
                  <td style="padding:8px 14px;">
                    <input type="text" name="accessories[0][detail]" class="form-control"
                      placeholder="รายละเอียด" required>
                  </td>
                  <td style="padding:8px 14px;">
                    <input type="text" name="accessories[0][cost_price]"
                      class="form-control money-input text-end" placeholder="0.00" required>
                  </td>
                  <td style="padding:8px 14px;">
                    <input type="text" name="accessories[0][sale_price]"
                      class="form-control money-input text-end" placeholder="0.00" required>
                  </td>
                  <td class="text-center" style="padding:8px 14px;">
                    <button type="button" class="btn btn-danger btnRemoveRow">
                      <i class="bx bx-trash"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>

    {{-- Actions --}}
    <div class="inv-actions">
      <a href="{{ route('invoice.index') }}" class="btn btn-outline-secondary px-4">
        <i class="bx bx-arrow-back me-1"></i> ยกเลิก
      </a>
      <button type="submit" class="btn btn-primary px-5">
        <i class="bx bx-save me-1"></i> บันทึก
      </button>
    </div>

  </form>

@endsection
