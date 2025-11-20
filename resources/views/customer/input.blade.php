@extends('layouts/contentNavbarLayout')
@section('title', 'Data Customer - Add')

@section('page-script')
@vite(['resources/assets/js/customer.js'])
@endsection

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <h4 class="card-header">เพิ่มข้อมูลลูกค้าใหม่</h4>
      <div class="card-body">
        <form action="{{ route('customer.store') }}" method="POST" enctype="multipart/form-data">
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
              <label class="form-label" for="FirstName">ชื่อ</label>
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
              <label class="form-label" for="LastName">นามสกุล</label>
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
              <label class="form-label" for="IDNumber">เลขบัตรประชาชน</label>
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
              <label class="form-label" for="NewCardDate">วันออกบัตรประชาชน</label>
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
              <label class="form-label" for="ExpireCard">วันที่บัตรประชาชนหมดอายุ</label>
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
              <label class="form-label" for="Birthday">วัน/เดือน/ปีเกิด</label>
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
              <label class="form-label" for="Gender">เพศ</label>
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
              <label class="form-label" for="Nationality">สัญชาติ</label>
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
              <label class="form-label" for="religion">ศาสนา</label>
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
              <label class="form-label" for="Mobilephone1">เบอร์โทรหลัก</label>
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
              <label class="form-label" for="Mobilephone2">เบอร์โทรสำรอง</label>
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
              <label class="form-label mb-4 fs-5" for="Address">ที่อยู่ปัจจุบัน</label>

              <div class="col-md-2 mb-5">
                <label class="form-label">เลขที่</label>
                <input type="text" name="current_house_number"
                  class="form-control" required>
              </div>

              <div class="col-md-2 mb-5">
                <label class="form-label">หมู่ที่</label>
                <input type="text" name="current_group"
                  class="form-control">
              </div>

              <div class="col-md-4 mb-5">
                <label class="form-label">หมู่บ้าน</label>
                <input type="text" name="current_village"
                  class="form-control">
              </div>

              <div class="col-md-4 mb-5">
                <label class="form-label">ซอย</label>
                <input type="text" name="current_alley"
                  class="form-control">
              </div>

              <div class="col-md-4 mb-5">
                <label class="form-label">ถนน</label>
                <input type="text" name="current_road"
                  class="form-control">
              </div>

              <div class="col-md-4 mb-5">
                <label class="form-label">ตำบล/แขวง</label>
                <input type="text" name="current_subdistrict"
                  class="form-control @error('current_subdistrict') is-invalid @enderror" required>

                @error('current_subdistrict')
                <span class="invalid-feedback" role="alert">
                  <strong>{{ $message }}</strong>
                </span>
                @enderror
              </div>

              <div class="col-md-4 mb-5">
                <label class="form-label">อำเภอ/เขต</label>
                <input type="text" name="current_district"
                  class="form-control @error('current_district') is-invalid @enderror" required>

                @error('current_district')
                <span class="invalid-feedback" role="alert">
                  <strong>{{ $message }}</strong>
                </span>
                @enderror
              </div>

              <div class="col-md-3 mb-5">
                <label class="form-label">จังหวัด</label>
                <input type="text" name="current_province"
                  class="form-control @error('current_province') is-invalid @enderror" required>

                @error('current_province')
                <span class="invalid-feedback" role="alert">
                  <strong>{{ $message }}</strong>
                </span>
                @enderror
              </div>

              <div class="col-md-2 mb-5">
                <label class="form-label">เลขไปรษณีย์</label>
                <input type="text" name="current_postal_code"
                  class="form-control @error('current_postal_code') is-invalid @enderror" required>

                @error('current_postal_code')
                <span class="invalid-feedback" role="alert">
                  <strong>{{ $message }}</strong>
                </span>
                @enderror
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
                <label class="form-label">เลขที่</label>
                <input type="text" name="doc_house_number"
                  class="form-control" required>
              </div>

              <div class="col-md-2 mb-5">
                <label class="form-label">หมู่ที่</label>
                <input type="text" name="doc_group"
                  class="form-control">
              </div>

              <div class="col-md-4 mb-5">
                <label class="form-label">หมู่บ้าน</label>
                <input type="text" name="doc_village"
                  class="form-control">
              </div>

              <div class="col-md-4 mb-5">
                <label class="form-label">ซอย</label>
                <input type="text" name="doc_alley"
                  class="form-control">
              </div>

              <div class="col-md-4 mb-5">
                <label class="form-label">ถนน</label>
                <input type="text" name="doc_road"
                  class="form-control">
              </div>

              <div class="col-md-4 mb-5">
                <label class="form-label">ตำบล/แขวง</label>
                <input type="text" name="doc_subdistrict"
                  class="form-control @error('doc_subdistrict') is-invalid @enderror" required>

                @error('doc_subdistrict')
                <span class="invalid-feedback" role="alert">
                  <strong>{{ $message }}</strong>
                </span>
                @enderror
              </div>

              <div class="col-md-4 mb-5">
                <label class="form-label">อำเภอ/เขต</label>
                <input type="text" name="doc_district"
                  class="form-control @error('doc_district') is-invalid @enderror" required>

                @error('doc_district')
                <span class="invalid-feedback" role="alert">
                  <strong>{{ $message }}</strong>
                </span>
                @enderror
              </div>

              <div class="col-md-3 mb-5">
                <label class="form-label">จังหวัด</label>
                <input type="text" name="doc_province"
                  class="form-control @error('doc_province') is-invalid @enderror" required>

                @error('doc_province')
                <span class="invalid-feedback" role="alert">
                  <strong>{{ $message }}</strong>
                </span>
                @enderror
              </div>

              <div class="col-md-2 mb-5">
                <label class="form-label">เลขไปรษณีย์</label>
                <input type="text" name="doc_postal_code"
                  class="form-control @error('doc_postal_code') is-invalid @enderror" required>

                @error('doc_postal_code')
                <span class="invalid-feedback" role="alert">
                  <strong>{{ $message }}</strong>
                </span>
                @enderror
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