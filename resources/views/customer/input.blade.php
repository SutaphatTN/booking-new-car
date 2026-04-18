@extends('layouts/contentNavbarLayout')
@section('title', 'Data Customer Add')

@section('page-style')
@vite(['resources/assets/css/customer.css'])
@endsection

@section('page-script')
  @vite(['resources/assets/js/customer.js'])
@endsection

@section('content')

  <form id="customerInputForm" action="{{ route('customer.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    {{-- Page Title --}}
    <div class="cus-page-title">
      <div class="cus-page-icon">
        <i class="bx bx-user-plus"></i>
      </div>
      <div>
        <h5 class="cus-page-name">เพิ่มข้อมูลลูกค้าใหม่</h5>
      </div>
    </div>

    <div class="row">

      {{-- CARD 1 : ข้อมูลส่วนตัว --}}
      <div class="col-md-12">
        <div class="po-section">
          <div class="po-section-header">
            <div class="po-section-icon indigo"><i class="bx bxs-user-rectangle"></i></div>
            <h6 class="po-section-title">ข้อมูลส่วนตัว</h6>
          </div>
          <div class="po-section-body">
            <div class="row g-3 mb-3">

              <div class="col-md-2">
                <label for="PrefixName" class="po-label"><i class='bx bx-list-ul'></i> คำนำหน้า</label>
                <select id="PrefixName" name="PrefixName" class="form-select">
                  <option value="">— เลือก —</option>
                  @foreach ($perfixName as $item)
                    <option value="{{ @$item->id }}">{{ @$item->Name_TH }}</option>
                  @endforeach
                </select>
                @error('PrefixName')
                  <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                @enderror
              </div>

              <div class="col-md-3">
                <label for="FirstName" class="po-label"><i class='bx bx-user'></i> ชื่อ <span class="text-danger">*</span></label>
                <input id="FirstName" type="text" class="form-control @error('FirstName') is-invalid @enderror"
                  name="FirstName" value="" required>
                @error('FirstName')
                  <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                @enderror
              </div>

              <div class="col-md-3">
                <label for="LastName" class="po-label"><i class="bx bx-user-pin"></i> นามสกุล</label>
                <input id="LastName" type="text" class="form-control @error('LastName') is-invalid @enderror"
                  name="LastName" value="">
                @error('LastName')
                  <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                @enderror
              </div>

              <div class="col-md-2">
                <label for="Gender" class="po-label"><i class='bx bx-user-circle'></i> เพศ</label>
                <select id="Gender" name="Gender" class="form-select">
                  <option value="">— เลือก —</option>
                  <option value="Female">หญิง</option>
                  <option value="Male">ชาย</option>
                </select>
                @error('Gender')
                  <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                @enderror
              </div>

              <div class="col-md-2">
                <label for="Birthday" class="po-label"><i class='bx bx-calendar'></i> วันเกิด</label>
                <input id="Birthday" type="date" class="form-control @error('Birthday') is-invalid @enderror"
                  name="Birthday" max="{{ date('Y-m-d') }}" value="">
                @error('Birthday')
                  <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                @enderror
              </div>

              <div class="col-md-3">
                <label for="IDNumber" class="po-label"><i class='bx bx-id-card'></i> เลขบัตรประชาชน <span class="text-danger">*</span></label>
                <input id="IDNumber" type="text" class="form-control @error('IDNumber') is-invalid @enderror"
                  name="IDNumber" maxlength="17" required>
                @error('IDNumber')
                  <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                @enderror
              </div>

              <div class="col-md-2">
                <label for="NewCardDate" class="po-label"><i class='bx bx-calendar-check'></i> วันออกบัตร</label>
                <input id="NewCardDate" type="date" class="form-control @error('NewCardDate') is-invalid @enderror"
                  name="NewCardDate" value="">
                @error('NewCardDate')
                  <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                @enderror
              </div>

              <div class="col-md-2">
                <label for="ExpireCard" class="po-label"><i class='bx bx-calendar-x'></i> วันหมดอายุบัตร</label>
                <input id="ExpireCard" type="date" class="form-control @error('ExpireCard') is-invalid @enderror"
                  name="ExpireCard" value="">
                @error('ExpireCard')
                  <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                @enderror
              </div>

              <div class="col-md-3">
                <label for="Nationality" class="po-label"><i class='bx bx-flag'></i> สัญชาติ</label>
                <input id="Nationality" type="text" class="form-control @error('Nationality') is-invalid @enderror"
                  name="Nationality" value="">
                @error('Nationality')
                  <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                @enderror
              </div>

              <div class="col-md-2">
                <label for="religion" class="po-label"><i class='bx bx-book-open'></i> ศาสนา</label>
                <select id="religion" name="religion" class="form-select">
                  <option value="">— เลือก —</option>
                  <option value="buddhist">พุทธ</option>
                  <option value="islam">อิสลาม</option>
                  <option value="christian">คริสต์</option>
                  <option value="other">อื่นๆ</option>
                </select>
                @error('religion')
                  <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                @enderror
              </div>

              <div class="col-md-2">
                <label for="Mobilephone1" class="po-label"><i class='bx bx-phone'></i> เบอร์โทรหลัก <span class="text-danger">*</span></label>
                <input id="Mobilephone1" type="text"
                  class="form-control @error('Mobilephone1') is-invalid @enderror" name="Mobilephone1" maxlength="12"
                  required>
                @error('Mobilephone1')
                  <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                @enderror
              </div>

              <div class="col-md-2">
                <label for="Mobilephone2" class="po-label"><i class='bx bxs-phone'></i> เบอร์โทรสำรอง</label>
                <input id="Mobilephone2" type="text"
                  class="form-control @error('Mobilephone2') is-invalid @enderror" name="Mobilephone2" maxlength="12">
                @error('Mobilephone2')
                  <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                @enderror
              </div>

            </div>
          </div>
        </div>
      </div>

      {{-- CARD 2 : ที่อยู่ปัจจุบัน --}}
      <div class="col-md-6">
        <div class="po-section">
          <div class="po-section-header">
            <div class="po-section-icon emerald"><i class="bx bx-home"></i></div>
            <h6 class="po-section-title">ที่อยู่ปัจจุบัน</h6>
          </div>
          <div class="po-section-body">
            <div class="row g-3 pb-2">

              <div class="col-md-2">
                <label for="current_house_number" class="po-label"><i class='bx bx-home'></i> เลขที่ <span class="text-danger">*</span></label>
                <input id="current_house_number" type="text" name="current_house_number" class="form-control"
                  required>
              </div>

              <div class="col-md-2">
                <label for="current_group" class="po-label"><i class='bx bx-map'></i> หมู่ที่</label>
                <input id="current_group" type="text" name="current_group" class="form-control">
              </div>

              <div class="col-md-4">
                <label for="current_alley" class="po-label"><i class="bx bx-navigation"></i> ซอย</label>
                <input id="current_alley" type="text" name="current_alley" class="form-control">
              </div>

              <div class="col-md-4">
                <label for="current_village" class="po-label"><i class='bx bx-buildings'></i> หมู่บ้าน</label>
                <input id="current_village" type="text" name="current_village" class="form-control">
              </div>

              <div class="col-md-6">
                <label for="current_road" class="po-label"><i class="bx bx-trip"></i> ถนน</label>
                <input id="current_road" type="text" name="current_road" class="form-control">
              </div>

              <div class="col-md-6">
                <label for="current_province" class="po-label"><i class='bx bx-map'></i> จังหวัด <span class="text-danger">*</span></label>
                <select id="current_province" name="current_province" class="form-select" required>
                  <option value="">— เลือกจังหวัด —</option>
                </select>
              </div>

              <div class="col-md-5">
                <label for="current_district" class="po-label"><i class='bx bx-map-alt'></i> อำเภอ/เขต <span class="text-danger">*</span></label>
                <select id="current_district" name="current_district" class="form-select" required disabled>
                  <option value="">— เลือกอำเภอ —</option>
                </select>
              </div>

              <div class="col-md-4">
                <label for="current_subdistrict" class="po-label"><i class="bx bx-navigation"></i> ตำบล/แขวง <span class="text-danger">*</span></label>
                <select id="current_subdistrict" name="current_subdistrict" class="form-select" required disabled>
                  <option value="">— เลือกตำบล —</option>
                </select>
              </div>

              <div class="col-md-3">
                <label for="current_postal_code" class="po-label"><i class='bx bx-envelope'></i> รหัสไปรษณีย์</label>
                <input id="current_postal_code" type="text" name="current_postal_code" class="form-control"
                  readonly>
              </div>
              <input type="hidden" name="current_post_id">

            </div>
          </div>
        </div>
      </div>

      {{-- CARD 3 : ที่อยู่สำหรับส่งเอกสาร --}}
      <div class="col-md-6">
        <div class="po-section">
          <div class="po-section-header" style="justify-content:space-between;">
            <div class="d-flex align-items-center gap-2">
              <div class="po-section-icon amber"><i class="bx bx-file"></i></div>
              <h6 class="po-section-title">ที่อยู่สำหรับส่งเอกสาร</h6>
            </div>
            <div class="form-check mb-0">
              <input class="form-check-input" type="checkbox" id="sameAsCurrent">
              <label class="form-check-label" for="sameAsCurrent">ใช้ที่อยู่เดียวกับปัจจุบัน</label>
            </div>
          </div>
          <div class="po-section-body">
            <div class="row g-3 pb-2">

              <div class="col-md-2">
                <label for="doc_house_number" class="po-label"><i class='bx bx-home'></i> เลขที่ <span class="text-danger">*</span></label>
                <input id="doc_house_number" type="text" name="doc_house_number" class="form-control" required>
              </div>

              <div class="col-md-2">
                <label for="doc_group" class="po-label"><i class='bx bx-map'></i> หมู่ที่</label>
                <input id="doc_group" type="text" name="doc_group" class="form-control">
              </div>

              <div class="col-md-4">
                <label for="doc_alley" class="po-label"><i class="bx bx-navigation"></i> ซอย</label>
                <input id="doc_alley" type="text" name="doc_alley" class="form-control">
              </div>

              <div class="col-md-4">
                <label for="doc_village" class="po-label"><i class='bx bx-buildings'></i> หมู่บ้าน</label>
                <input id="doc_village" type="text" name="doc_village" class="form-control">
              </div>

              <div class="col-md-6">
                <label for="doc_road" class="po-label"><i class="bx bx-trip"></i> ถนน</label>
                <input id="doc_road" type="text" name="doc_road" class="form-control">
              </div>

              <div class="col-md-6">
                <label for="doc_province" class="po-label"><i class='bx bx-map'></i> จังหวัด <span class="text-danger">*</span></label>
                <select id="doc_province" name="doc_province" class="form-select" required>
                  <option value="">— เลือกจังหวัด —</option>
                </select>
              </div>

              <div class="col-md-5">
                <label for="doc_district" class="po-label"><i class='bx bx-map-alt'></i> อำเภอ/เขต <span class="text-danger">*</span></label>
                <select id="doc_district" name="doc_district" class="form-select" required disabled>
                  <option value="">— เลือกอำเภอ —</option>
                </select>
              </div>

              <div class="col-md-4">
                <label for="doc_subdistrict" class="po-label"><i class="bx bx-navigation"></i> ตำบล/แขวง <span class="text-danger">*</span></label>
                <select id="doc_subdistrict" name="doc_subdistrict" class="form-select" required disabled>
                  <option value="">— เลือกตำบล —</option>
                </select>
              </div>

              <div class="col-md-3">
                <label for="doc_postal_code" class="po-label"><i class='bx bx-envelope'></i> รหัสไปรษณีย์</label>
                <input id="doc_postal_code" type="text" name="doc_postal_code" class="form-control" readonly>
              </div>
              <input type="hidden" name="doc_post_id">

            </div>
          </div>
        </div>
      </div>

    </div>{{-- /row g-4 --}}

    {{-- Actions --}}
    <div class="po-actions">
      <a href="{{ route('customer.index') }}" class="btn btn-outline-secondary px-4">
        <i class="bx bx-arrow-back me-1"></i> ยกเลิก
      </a>
      <button type="submit" class="btn btn-primary px-5 btnSaveCustomer">
        <i class="bx bx-save me-1"></i> บันทึก
      </button>
    </div>

  </form>

@endsection
