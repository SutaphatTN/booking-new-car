@extends('layouts/contentNavbarLayout')
@section('title', 'เพิ่มการติดตามลูกค้า')

@section('page-style')
  @vite(['resources/css/app.css'])
@endsection

@section('page-script')
  @vite(['resources/assets/js/customer-tracking.js'])
@endsection

@section('content')
  {{-- modal ค้นหาลูกค้า --}}
  <div class="modal fade" id="modalSearchCustomer" tabindex="-1" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">เลือกข้อมูลลูกค้า</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="table-responsive">
            <table class="table table-bordered" id="tableSelectCustomer">
              <thead>
                <tr>
                  <th>ชื่อ - นามสกุล</th>
                  <th>เบอร์โทรศัพท์</th>
                  <th>เลขบัตรประชาชน</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Page Title --}}
  <div class="pur-page-title">
    <div class="pur-page-icon">
      <i class="bx bx-plus-circle"></i>
    </div>
    <div>
      <h5 class="pur-page-name">เพิ่มการติดตามลูกค้า</h5>
    </div>
  </div>

  @if($errors->any())
    <div class="alert alert-danger mb-3">
      <ul class="mb-0">
        @foreach($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form action="{{ route('customer-tracking.store') }}" method="POST">
    @csrf

    <div class="row g-4">

      <div class="col-md-6">

        {{-- SECTION 1 : ข้อมูลลูกค้า --}}
        <div class="po-section">
          <div class="po-section-header">
            <div class="po-section-icon sky"><i class="bx bx-user"></i></div>
            <h6 class="po-section-title">ข้อมูลลูกค้า</h6>
          </div>
          <div class="po-section-body">

            <div class="row g-3 mb-3">
              <div class="col-12">
                <label class="po-label" for="customerSearch"><i class='bx bx-search-alt'></i> ค้นหาข้อมูลลูกค้า</label>
                <div class="input-group">
                  <input id="customerSearch" type="text" class="form-control" placeholder="พิมพ์ชื่อ/เลขบัตร/เบอร์โทร">
                  <button type="button" class="btn btnSearchCustomer px-3 border">
                    <i class="bx bx-search me-1"></i> ค้นหา
                  </button>
                </div>
              </div>
            </div>

            <input type="hidden" id="CusID" name="customer_id">

            <div class="customer-info-row mb-3">
              <div class="row g-3">
                <div class="col-12">
                  <div class="po-label"><i class='bx bxs-user'></i> ชื่อ - นามสกุล</div>
                  <div class="info-val empty" id="customerName-display">— ยังไม่ได้เลือกลูกค้า —</div>
                </div>
                <div class="col-md-6">
                  <div class="po-label"><i class='bx bx-id-card'></i> เลขบัตรประชาชน</div>
                  <div class="info-val empty" id="customerID-display">—</div>
                </div>
                <div class="col-md-6">
                  <div class="po-label"><i class='bx bx-phone'></i> เบอร์โทรศัพท์</div>
                  <div class="info-val empty" id="customerPhone-display">—</div>
                </div>
              </div>
            </div>

          </div>
        </div>

        {{-- SECTION 2 : ข้อมูลผู้ขาย --}}
        <div class="po-section">
          <div class="po-section-header">
            <div class="po-section-icon indigo"><i class="bx bx-user-pin"></i></div>
            <h6 class="po-section-title">ข้อมูลผู้ขาย</h6>
          </div>
          <div class="po-section-body">
            <div class="row g-3 pb-2">

              {{-- ผู้ขาย --}}
              @if(auth()->user()->role == 'sale')
                <input type="hidden" name="sale_id" value="{{ Auth::user()->id }}">
                <div class="col-md-6">
                  <div class="po-label"><i class='bx bx-user'></i> ชื่อ - นามสกุล ผู้ขาย</div>
                  <div class="info-pill">
                    <i class="bx bx-check-circle me-2" style="color:#10b981;"></i>
                    {{ Auth::user()->name }}
                  </div>
                </div>
              @else
                <div class="col-md-6">
                  <label class="po-label" for="sale_id"><i class='bx bx-user'></i> ชื่อ - นามสกุล ผู้ขาย</label>
                  <select id="sale_id" name="sale_id" class="form-select @error('sale_id') is-invalid @enderror">
                    <option value="">— เลือกผู้ขาย —</option>
                    @foreach($saleUser as $s)
                      <option value="{{ $s->id }}" {{ old('sale_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                  </select>
                  @error('sale_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
              @endif

              {{-- แหล่งที่มา --}}
              <div class="col-md-6">
                <label class="po-label" for="source_id"><i class='bx bx-map-pin'></i> แหล่งที่มา</label>
                <select id="source_id" name="source_id" class="form-select @error('source_id') is-invalid @enderror" required>
                  <option value="">— เลือก —</option>
                  @foreach($sources as $s)
                    <option value="{{ $s->id }}" {{ old('source_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                  @endforeach
                </select>
                @error('source_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>

            </div>
          </div>
        </div>

      </div>{{-- จบคอลัมน์ซ้าย --}}

      <div class="col-md-6">

        {{-- SECTION 3 : ข้อมูลรถ --}}
        <div class="po-section">
          <div class="po-section-header">
            <div class="po-section-icon emerald"><i class="bx bx-car"></i></div>
            <h6 class="po-section-title">ข้อมูลรถที่สนใจ</h6>
          </div>
          <div class="po-section-body">
            <div class="row g-3 pb-2">

              <div class="col-md-6">
                <label class="po-label" for="model_id"><i class='bx bx-cube'></i> รุ่นรถหลัก</label>
                <select id="model_id" name="model_id" class="form-select">
                  <option value="">— เลือกรุ่นรถหลัก —</option>
                  @foreach($model as $m)
                    <option value="{{ $m->id }}" {{ old('model_id') == $m->id ? 'selected' : '' }}>{{ $m->Name_TH }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-6">
                <label class="po-label" for="sub_model_id"><i class='bx bx-list-ul'></i> รุ่นรถย่อย</label>
                <select id="sub_model_id" name="sub_model_id" class="form-select" disabled>
                  <option value="">— เลือกรุ่นรถย่อย —</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="po-label" for="year"><i class='bx bx-calendar-alt'></i> ปี</label>
                <select id="year" name="year" class="form-select" disabled>
                  <option value="">— เลือกปี —</option>
                </select>
              </div>

              <div class="col-md-6">
                <label class="po-label" for="color_id"><i class='bx bx-palette'></i> สี</label>
                <select id="color_id" name="color_id" class="form-select" disabled>
                  <option value="">— เลือกสี —</option>
                </select>
              </div>

            </div>
          </div>
        </div>

        {{-- SECTION 4 : รายละเอียดการติดตาม --}}
        <div class="po-section">
          <div class="po-section-header">
            <div class="po-section-icon" style="background:#f59e0b22;color:#f59e0b;"><i class="bx bx-notepad"></i></div>
            <h6 class="po-section-title">รายละเอียดการติดตาม</h6>
          </div>
          <div class="po-section-body">
            <div class="row g-3 pb-2">

              {{-- วันที่ติดต่อ --}}
              <div class="col-md-5">
                <label class="po-label" for="contact_date"><i class='bx bx-calendar'></i> วันที่ติดต่อ</label>
                <input id="contact_date" type="date" name="contact_date"
                  class="form-control @error('contact_date') is-invalid @enderror"
                  value="{{ old('contact_date', date('Y-m-d')) }}" required>
                @error('contact_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              {{-- สถานะการตัดสินใจ --}}
              <div class="col-md-7">
                <label class="po-label" for="decision_id"><i class='bx bx-target-lock'></i> สถานะการตัดสินใจ</label>
                <select id="decision_id" name="decision_id" class="form-select">
                  <option value="">— เลือก —</option>
                  @foreach($decisions as $d)
                    <option value="{{ $d->id }}" {{ old('decision_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                  @endforeach
                </select>
              </div>

              {{-- สถานะการติดต่อ --}}
              <div class="col-12">
                <div class="po-label"><i class='bx bx-phone-call'></i> สถานะการติดต่อ</div>
                <div class="yn-group mt-1">
                  <input type="radio" name="contact_status" id="contactYes" value="1"
                    {{ old('contact_status', '1') == '1' ? 'checked' : '' }}>
                  <label for="contactYes">ติดต่อได้</label>
                  <input type="radio" name="contact_status" id="contactNo" value="0"
                    {{ old('contact_status') == '0' ? 'checked' : '' }}>
                  <label for="contactNo">ติดต่อไม่ได้</label>
                </div>
                @error('contact_status')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>

              {{-- Comment sale --}}
              <div class="col-12">
                <label class="po-label" for="comment_sale"><i class='bx bx-comment-detail'></i> Comment Sale</label>
                <textarea id="comment_sale" name="comment_sale" class="form-control" rows="2"
                  placeholder="รายละเอียดเพิ่มเติม...">{{ old('comment_sale') }}</textarea>
              </div>

            </div>
          </div>
        </div>

      </div>{{-- จบคอลัมน์ขวา --}}

    </div>{{-- จบ row --}}

    <div class="po-actions">
      <a href="{{ route('customer-tracking.index') }}" class="btn btn-outline-secondary px-4">
        <i class="bx bx-arrow-back me-1"></i> ยกเลิก
      </a>
      <button type="button" class="btn btn-primary px-5 btnSaveTracking">
        <i class="bx bx-save me-2"></i> บันทึก
      </button>
    </div>

  </form>
@endsection
