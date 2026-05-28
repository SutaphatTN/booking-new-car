@extends('layouts/contentNavbarLayout')
@section('title', 'เพิ่มการติดตามลูกค้า')

@section('page-style')
  @vite(['resources/css/app.css'])
@endsection

@section('page-script')
  @vite(['resources/assets/js/customer-tracking.js'])
@endsection

@section('content')
  {{-- Page Title --}}
  <div class="pur-page-title">
    <div class="pur-page-icon">
      <i class="bx bx-plus-circle"></i>
    </div>
    <div>
      <h5 class="pur-page-name">เพิ่มการติดตามลูกค้า</h5>
    </div>
  </div>

  @if ($errors->any())
    <div class="alert alert-danger mb-3">
      <ul class="mb-0">
        @foreach ($errors->all() as $err)
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
              <div class="col-md-4">
                <label class="po-label" for="ct_prefix">คำนำหน้า</label>
                <select id="ct_prefix" name="ct_prefix" class="form-select">
                  <option value="">— เลือก —</option>
                  @foreach ($prefixes as $p)
                    <option value="{{ $p->id }}">{{ $p->Name_TH }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-4">
                <label class="po-label" for="ct_first_name">ชื่อ <span class="text-danger">*</span></label>
                <input id="ct_first_name" name="ct_first_name" type="text" class="form-control" required>
              </div>
              <div class="col-md-4">
                <label class="po-label" for="ct_last_name">นามสกุล</label>
                <input id="ct_last_name" name="ct_last_name" type="text" class="form-control">
              </div>
              <div class="col-md-3">
                <label class="po-label" for="ct_phone">เบอร์โทร</label>
                <input id="ct_phone" name="ct_phone" type="text" class="form-control" maxlength="12" placeholder="xxx-xxxx-xxxx">
              </div>
              <div class="col-md-4">
                <label class="po-label" for="ct_line_id"><i class='bx bxl-whatsapp'></i> LineID</label>
                <input id="ct_line_id" name="ct_line_id" type="text" class="form-control">
              </div>
              <div class="col-md-5">
                <label class="po-label" for="ct_facebook"><i class='bx bxl-facebook-circle'></i> Facebook</label>
                <input id="ct_facebook" name="ct_facebook" type="text" class="form-control">
              </div>
            </div>
            <input type="hidden" id="CusID" name="customer_id">
          </div>
        </div>

        {{-- SECTION 2 : ข้อมูลรถ --}}
        <div class="po-section">
          <div class="po-section-header">
            <div class="po-section-icon emerald"><i class="bx bx-car"></i></div>
            <h6 class="po-section-title">ข้อมูลรถที่สนใจ</h6>
          </div>
          <div class="po-section-body">
            <div class="row g-3 pb-2">

              @if (auth()->user()->brand == 1)
                {{-- Mitsubishi --}}
                {{-- รุ่นหลัก --}}
                <div class="col-md-12">
                  <label class="po-label" for="model_id"><i class='bx bx-cube'></i> รุ่นรถหลัก <span class="text-danger">*</span></label>
                  <select id="model_id" name="model_id" class="form-select" required>
                    <option value="">— เลือกรุ่นรถหลัก —</option>
                    @foreach ($model as $m)
                      <option value="{{ $m->id }}" {{ old('model_id') == $m->id ? 'selected' : '' }}>
                        {{ $m->Name_TH }}</option>
                    @endforeach
                  </select>
                </div>

                {{-- รุ่นย่อย (ทุก brand) --}}
                <div class="col-md-9">
                  <label class="po-label" for="sub_model_id"><i class='bx bx-list-ul'></i> รุ่นรถย่อย <span class="text-danger">*</span></label>
                  <select id="sub_model_id" name="sub_model_id" class="form-select" disabled required>
                    <option value="">— เลือกรุ่นรถย่อย —</option>
                  </select>
                </div>

                <div class="col-md-3">
                  <label class="po-label" for="pricelist_color"><i class='bx bx-droplet'></i> ประเภทสี <span class="text-danger">*</span></label>
                  <select id="pricelist_color" name="pricelist_color" class="form-select" disabled required>
                    <option value="">— เลือก —</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="po-label" for="year"><i class='bx bx-calendar-alt'></i> ปี <span class="text-danger">*</span></label>
                  <select id="year" name="year" class="form-select" disabled required>
                    <option value="">— เลือกปี —</option>
                  </select>
                </div>
                <div class="col-md-3">
                  <label class="po-label" for="option"><i class="bx bx-code-block"></i> Option</label>
                  <input id="option" type="text" class="form-control" name="option" readonly>
                </div>
                <div class="col-md-5">
                  <label class="po-label" for="color_text"><i class='bx bx-palette'></i> สี <span class="text-danger">*</span></label>
                  <input id="color_text" name="color_text" type="text" class="form-control" placeholder="สี..." required>
                </div>
              @elseif(auth()->user()->brand == 2)
                {{-- GWM --}}
                {{-- รุ่นหลัก --}}
                <div class="col-md-6">
                  <label class="po-label" for="model_id"><i class='bx bx-cube'></i> รุ่นรถหลัก <span class="text-danger">*</span></label>
                  <select id="model_id" name="model_id" class="form-select" required>
                    <option value="">— เลือกรุ่นรถหลัก —</option>
                    @foreach ($model as $m)
                      <option value="{{ $m->id }}" {{ old('model_id') == $m->id ? 'selected' : '' }}>
                        {{ $m->Name_TH }}</option>
                    @endforeach
                  </select>
                </div>

                {{-- รุ่นย่อย (ทุก brand) --}}
                <div class="col-md-6">
                  <label class="po-label" for="sub_model_id"><i class='bx bx-list-ul'></i> รุ่นรถย่อย <span class="text-danger">*</span></label>
                  <select id="sub_model_id" name="sub_model_id" class="form-select" disabled required>
                    <option value="">— เลือกรุ่นรถย่อย —</option>
                  </select>
                </div>

                <div class="col-md-4">
                  <label class="po-label" for="year"><i class='bx bx-calendar-alt'></i> ปี <span class="text-danger">*</span></label>
                  <select id="year" name="year" class="form-select" disabled required>
                    <option value="">— เลือกปี —</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="po-label" for="color_id"><i class='bx bx-palette'></i> สีภายนอก <span class="text-danger">*</span></label>
                  <select id="color_id" name="color_id" class="form-select" disabled required>
                    <option value="">— เลือกสี —</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="po-label" for="interior_color_id"><i class='bx bx-paint'></i> สีภายใน <span class="text-danger">*</span></label>
                  <select id="interior_color_id" name="interior_color_id" class="form-select" disabled required>
                    <option value="">— เลือกสี —</option>
                  </select>
                </div>
              @else
                {{-- Wuling / others --}}
                {{-- รุ่นหลัก --}}
                <div class="col-md-6">
                  <label class="po-label" for="model_id"><i class='bx bx-cube'></i> รุ่นรถหลัก <span class="text-danger">*</span></label>
                  <select id="model_id" name="model_id" class="form-select" required>
                    <option value="">— เลือกรุ่นรถหลัก —</option>
                    @foreach ($model as $m)
                      <option value="{{ $m->id }}" {{ old('model_id') == $m->id ? 'selected' : '' }}>
                        {{ $m->Name_TH }}</option>
                    @endforeach
                  </select>
                </div>

                {{-- รุ่นย่อย (ทุก brand) --}}
                <div class="col-md-6">
                  <label class="po-label" for="sub_model_id"><i class='bx bx-list-ul'></i> รุ่นรถย่อย <span class="text-danger">*</span></label>
                  <select id="sub_model_id" name="sub_model_id" class="form-select" disabled required>
                    <option value="">— เลือกรุ่นรถย่อย —</option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="po-label" for="year"><i class='bx bx-calendar-alt'></i> ปี <span class="text-danger">*</span></label>
                  <select id="year" name="year" class="form-select" disabled required>
                    <option value="">— เลือกปี —</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="po-label" for="color_id"><i class='bx bx-palette'></i> สี <span class="text-danger">*</span></label>
                  <select id="color_id" name="color_id" class="form-select" disabled required>
                    <option value="">— เลือกสี —</option>
                  </select>
                </div>
              @endif

            </div>
          </div>
        </div>

      </div>{{-- จบคอลัมน์ซ้าย --}}

      <div class="col-md-6">

        {{-- SECTION 3 : ข้อมูลผู้ขาย --}}
        <div class="po-section">
          <div class="po-section-header">
            <div class="po-section-icon indigo"><i class="bx bx-user-pin"></i></div>
            <h6 class="po-section-title">ข้อมูลผู้ขาย</h6>
          </div>
          <div class="po-section-body">
            <div class="row g-3 pb-2">

              {{-- ผู้ขาย --}}
              @if (in_array(auth()->user()->role, ['sale', 'lead_sale']))
                <input type="hidden" name="sale_id" value="{{ Auth::user()->id }}">
                <div class="col-md-7">
                  <div class="po-label"><i class='bx bx-user'></i> ชื่อ - นามสกุล ผู้ขาย</div>
                  <div class="info-pill">
                    <i class="bx bx-check-circle me-2" style="color:#10b981;"></i>
                    {{ Auth::user()->name }}
                  </div>
                </div>
              @else
                <div class="col-md-7">
                  <label class="po-label" for="sale_id"><i class='bx bx-user'></i> ชื่อ - นามสกุล ผู้ขาย</label>
                  <select id="sale_id" name="sale_id" class="form-select @error('sale_id') is-invalid @enderror"
                    required>
                    <option value="">— เลือกผู้ขาย —</option>
                    @foreach ($saleUser as $s)
                      <option value="{{ $s->id }}" {{ old('sale_id') == $s->id ? 'selected' : '' }}>
                        {{ $s->name }}</option>
                    @endforeach
                  </select>
                  @error('sale_id')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                </div>
              @endif

              {{-- แหล่งที่มา --}}
              <div class="col-md-5">
                <label class="po-label" for="source_id"><i class='bx bx-map-pin'></i> แหล่งที่มา</label>
                <select id="source_id" name="source_id" class="form-select @error('source_id') is-invalid @enderror"
                  required>
                  <option value="">— เลือก —</option>
                  @foreach ($sources as $s)
                    <option value="{{ $s->id }}" {{ old('source_id') == $s->id ? 'selected' : '' }}>
                      {{ $s->name }}</option>
                  @endforeach
                </select>
                @error('source_id')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-12">
                <label class="po-label" for="clip_add"><i class='bx bx-link'></i> คลิปที่ยิงแอด<span class="text-secondary">(กรณีลุกค้าจาก Online)</span></label>
                <textarea id="clip_add" name="clip_add" class="form-control" rows="2"
                  placeholder="ชื่อคลิปที่ใช้โฆษณา...">{{ old('clip_add') }}</textarea>
              </div>

            </div>
          </div>
        </div>

        {{-- SECTION 4 : รายละเอียดการติดตาม --}}
        <div class="po-section">
          <div class="po-section-header">
            <div class="po-section-icon amber"><i class="bx bx-notepad"></i></div>
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
                @error('contact_date')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              {{-- สถานะการตัดสินใจ --}}
              <div class="col-md-7">
                <label class="po-label" for="decision_id"><i class='bx bx-target-lock'></i> สถานะการตัดสินใจ</label>
                <select id="decision_id" name="decision_id" class="form-select" required>
                  <option value="">— เลือก —</option>
                  @foreach ($decisions as $d)
                    <option value="{{ $d->id }}" {{ old('decision_id') == $d->id ? 'selected' : '' }}>
                      {{ $d->name }}</option>
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
                @error('contact_status')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>

              {{-- Comment sale --}}
              <div class="col-12">
                <label class="po-label" for="comment_sale"><i class='bx bx-comment-detail'></i> หมายเหตุ</label>
                <textarea id="comment_sale" name="comment_sale" class="form-control" rows="3"
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
