@extends('layouts/contentNavbarLayout')
@section('title', 'Data Customer Add')

@section('page-script')
@vite(['resources/js/app.js'])
@endsection

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <h4 class="card-header">เพิ่มข้อมูลลูกค้าใหม่</h4>
      <div class="card-body">
        <form id="customerInputForm" action="{{ route('customer.store') }}" method="POST" enctype="multipart/form-data">
          @csrf

          <div class="row mb-6">

            <div class="col-md-2 mb-5">
              <label for="PrefixName" class="form-label">คำนำหน้า</label>
              <select id="PrefixName" name="PrefixName" class="form-select" required>
                <option value="">-- เลือกคำนำหน้า --</option>
                @foreach ($perfixName as $item)
                <option value="{{ @$item->id }}">{{ @$item->Name_TH }}</option>
                @endforeach
              </select>

              @error('PrefixName')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-5 mb-5">
              <label for="FirstName" class="form-label">ชื่อ</label>
              <input id="FirstName" type="text"
                class="form-control @error('FirstName') is-invalid @enderror"
                name="FirstName" value="" required>

              @error('FirstName')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-5 mb-5">
              <label for="LastName" class="form-label">นามสกุล</label>
              <input id="LastName" type="text"
                class="form-control @error('LastName') is-invalid @enderror"
                name="LastName" value="" required>

              @error('LastName')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-4 mb-5">
              <label for="IDNumber" class="form-label">เลขบัตรประชาชน</label>
              <input id="IDNumber" type="text"
                class="form-control @error('IDNumber') is-invalid @enderror"
                name="IDNumber" maxlength="17" required>

              @error('IDNumber')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-4 mb-5">
              <label for="NewCardDate" class="form-label">วันออกบัตรประชาชน</label>
              <input id="NewCardDate" type="date"
                class="form-control @error('NewCardDate') is-invalid @enderror"
                name="NewCardDate" value="" required>

              @error('NewCardDate')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-4 mb-5">
              <label for="ExpireCard" class="form-label">วันที่บัตรประชาชนหมดอายุ</label>
              <input id="ExpireCard" type="date"
                class="form-control @error('ExpireCard') is-invalid @enderror"
                name="ExpireCard" value="" required>

              @error('ExpireCard')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-4 mb-5">
              <label for="Birthday" class="form-label">วัน/เดือน/ปีเกิด</label>
              <input id="Birthday" type="date"
                class="form-control @error('Birthday') is-invalid @enderror"
                name="Birthday" max="{{ date('Y-m-d') }}" value="" required>

              @error('Birthday')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-4 mb-5">
              <label for="Gender" class="form-label">เพศ</label>
              <select id="Gender" name="Gender" class="form-select" required>
                <option value="">-- เลือกเพศ --</option>
                <option value="Female">หญิง</option>
                <option value="Male">ชาย</option>
              </select>

              @error('Gender')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-4 mb-5">
              <label for="Nationality" class="form-label">สัญชาติ</label>
              <input id="Nationality" type="text"
                class="form-control @error('Nationality') is-invalid @enderror"
                name="Nationality" value="" required>

              @error('Nationality')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-4 mb-5">
              <label for="religion" class="form-label">ศาสนา</label>
              <select id="religion" name="religion" class="form-select" required>
                <option value="">-- เลือกศาสนา --</option>
                <option value="buddhist">พุทธ</option>
                <option value="islam">อิสลาม</option>
                <option value="christian">คริสต์</option>
                <option value="other">อื่นๆ</option>
              </select>

              @error('religion')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-4 mb-5">
              <label for="Mobilephone1" class="form-label">เบอร์โทรหลัก</label>
              <input id="Mobilephone1" type="text"
                class="form-control @error('Mobilephone1') is-invalid @enderror"
                name="Mobilephone1" maxlength="12" required>

              @error('Mobilephone1')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-4 mb-5">
              <label for="Mobilephone2" class="form-label">เบอร์โทรสำรอง</label>
              <input id="Mobilephone2" type="text"
                class="form-control @error('Mobilephone2') is-invalid @enderror"
                name="Mobilephone2" maxlength="12">

              @error('Mobilephone2')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="row mt-5">
              <h3 class="form-label mb-4 fs-5">ที่อยู่ปัจจุบัน</h3>

              <div class="col-md-2 mb-5">
                <label for="current_house_number" class="form-label">เลขที่</label>
                <input id="current_house_number" type="text" name="current_house_number"
                  class="form-control" required>
              </div>

              <div class="col-md-2 mb-5">
                <label for="current_group" class="form-label">หมู่ที่</label>
                <input id="current_group" type="text" name="current_group"
                  class="form-control" required>
              </div>

              <div class="col-md-4 mb-5">
                <label for="current_village" class="form-label">หมู่บ้าน</label>
                <input id="current_village" type="text" name="current_village"
                  class="form-control">
              </div>

              <div class="col-md-4 mb-5">
                <label for="current_alley" class="form-label">ซอย</label>
                <input id="current_alley" type="text" name="current_alley"
                  class="form-control">
              </div>

              <div class="col-md-4 mb-5">
                <label for="current_road" class="form-label">ถนน</label>
                <input id="current_road" type="text" name="current_road"
                  class="form-control">
              </div>

              <div class="col-md-4 mb-5">
                <label for="current_subdistrict" class="form-label">ตำบล/แขวง</label>
                <input id="current_subdistrict" type="text" name="current_subdistrict"
                  class="form-control" required>
              </div>

              <div class="col-md-4 mb-5">
                <label for="current_district" class="form-label">อำเภอ/เขต</label>
                <input id="current_district" type="text" name="current_district"
                  class="form-control" required>
              </div>

              <div class="col-md-3 mb-5">
                <label for="current_province" class="form-label">จังหวัด</label>
                <input id="current_province" type="text" name="current_province"
                  class="form-control" required>
              </div>

              <div class="col-md-2 mb-5">
                <label for="current_postal_code" class="form-label">เลขไปรษณีย์</label>
                <input id="current_postal_code" type="text" name="current_postal_code"
                  class="form-control" required>
              </div>
            </div>

            <div class="row mt-5">
              <label class="form-label w-100">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="mb-4 fs-5">ที่อยู่สำหรับส่งเอกสาร</span>
                  <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" id="sameAsCurrent">
                    <label class="form-check-label fs-6" for="sameAsCurrent">ใช้ที่อยู่เดียวกับที่อยู่ปัจจุบัน</label>
                  </div>
                </div>
              </label>

              <div class="col-md-2 mb-5">
                <label for="doc_house_number" class="form-label">เลขที่</label>
                <input id="doc_house_number" type="text" name="doc_house_number"
                  class="form-control" required>
              </div>

              <div class="col-md-2 mb-5">
                <label for="doc_group" class="form-label">หมู่ที่</label>
                <input id="doc_group" type="text" name="doc_group"
                  class="form-control" required>
              </div>

              <div class="col-md-4 mb-5">
                <label for="doc_village" class="form-label">หมู่บ้าน</label>
                <input id="doc_village" type="text" name="doc_village"
                  class="form-control">
              </div>

              <div class="col-md-4 mb-5">
                <label for="doc_alley" class="form-label">ซอย</label>
                <input id="doc_alley" type="text" name="doc_alley"
                  class="form-control">
              </div>

              <div class="col-md-4 mb-5">
                <label for="doc_road" class="form-label">ถนน</label>
                <input id="doc_road" type="text" name="doc_road"
                  class="form-control">
              </div>

              <div class="col-md-4 mb-5">
                <label for="doc_subdistrict" class="form-label">ตำบล/แขวง</label>
                <input id="doc_subdistrict" type="text" name="doc_subdistrict"
                  class="form-control" required>
              </div>

              <div class="col-md-4 mb-5">
                <label for="doc_district" class="form-label">อำเภอ/เขต</label>
                <input id="doc_district" type="text" name="doc_district"
                  class="form-control" required>
              </div>

              <div class="col-md-3 mb-5">
                <label for="doc_province" class="form-label">จังหวัด</label>
                <input id="doc_province" type="text" name="doc_province"
                  class="form-control" required>
              </div>

              <div class="col-md-2 mb-5">
                <label for="doc_postal_code" class="form-label">เลขไปรษณีย์</label>
                <input id="doc_postal_code" type="text" name="doc_postal_code"
                  class="form-control" required>
              </div>
            </div>

          </div>

          <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-primary btnSaveCustomer">บันทึก</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>
@endsection