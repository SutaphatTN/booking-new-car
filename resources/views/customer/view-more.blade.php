<div class="modal fade viewCust" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h4 class="modal-title mb-2" id="viewCustLabel">ข้อมูลลูกค้า</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6 mb-5">
            <label for="customer_fullname" class="form-label">ชื่อ - นามสกุล</label>
            <input id="customer_fullname"
              name="customer_fullname"
              class="form-control"
              type="text"
              value="{{ $customers->prefix->Name_TH ?? '' }} {{ $customers->FirstName ?? '' }} {{ $customers->LastName ?? '' }}"
              disabled>
          </div>

          <div class="col-md-3 mb-5">
            <label for="Gender" class="form-label">เพศ</label>
            <input id="Gender" class="form-control" type="text" value="{{ $customers->gender_th }}" disabled />
          </div>

          <div class="col-md-3 mb-5">
            <label for="Birthday" class="form-label">วัน/เดือน/ปีเกิด</label>
            <input id="Birthday" class="form-control" type="text" value="{{ $customers->formatted_Birthday }}" disabled />
          </div>

          <div class="col-md-4 mb-5">
            <label for="IDNumber" class="form-label">เลขบัตรประชาชน</label>
            <input id="IDNumber" class="form-control" type="text" value="{{ $customers->formatted_id_number }}" disabled />
          </div>

          <div class="col-md-4 mb-5">
            <label for="NewCardDate" class="form-label">วันออกบัตรประชาชน</label>
            <input id="NewCardDate" class="form-control" type="text" value="{{ $customers->formatted_new_card_date }}" disabled />
          </div>

          <div class="col-md-4 mb-5">
            <label for="ExpireCard" class="form-label">วันที่บัตรประชาชนหมดอายุ</label>
            <input id="ExpireCard" class="form-control" type="text" value="{{ $customers->formatted_expire_card }}" disabled />
          </div>

          <div class="col-md-4 mb-5">
            <label for="Nationality" class="form-label">สัญชาติ</label>
            <input id="Nationality" class="form-control" type="text" value="{{ $customers->Nationality }}" disabled />
          </div>

          <div class="col-md-2 mb-5">
            <label for="religion" class="form-label">ศาสนา</label>
            <input id="religion" class="form-control" type="text" value="{{ $customers->religion_th }}" disabled />
          </div>

          <div class="col-md-3 mb-5">
            <label for="Mobilephone1" class="form-label">เบอร์โทรหลัก</label>
            <input id="Mobilephone1" class="form-control" type="text" value="{{ $customers->formatted_mobile }}" disabled />
          </div>

          <div class="col-md-3 mb-5">
            <label for="Mobilephone2" class="form-label">เบอร์โทรสำรอง</label>
            <input id="Mobilephone2" class="form-control" type="text" value="{{ $customers->formatted_mobile_up }}" disabled />
          </div>

          <div class="row mt-2">
            <h3 class="form-label mb-3 fs-5">ที่อยู่ปัจจุบัน</h3>

            <div class="col-md-2 mb-3">
              <label for="current_house_number" class="form-label">บ้านเลขที่</label>
              <input id="current_house_number" class="form-control" value="{{ $currentAddress->house_number ?? '-' }}" disabled />
            </div>

            <div class="col-md-2 mb-3">
              <label for="current_group" class="form-label">หมู่ที่</label>
              <input id="current_group" class="form-control" value="{{ $currentAddress->group ?? '-' }}" disabled />
            </div>

            <div class="col-md-4 mb-3">
              <label for="current_village" class="form-label">หมู่บ้าน</label>
              <input id="current_village" class="form-control" value="{{ $currentAddress->village ?? '-' }}" disabled />
            </div>

            <div class="col-md-4 mb-3">
              <label for="current_alley" class="form-label">ซอย</label>
              <input id="current_alley" class="form-control" value="{{ $currentAddress->alley ?? '-' }}" disabled />
            </div>

            <div class="col-md-4 mb-3">
              <label for="current_road" class="form-label">ถนน</label>
              <input id="current_road" class="form-control" value="{{ $currentAddress->road ?? '-' }}" disabled />
            </div>

            <div class="col-md-4 mb-3">
              <label for="current_subdistrict" class="form-label">ตำบล/แขวง</label>
              <input id="current_subdistrict" class="form-control" value="{{ $currentAddress->subdistrict ?? '-' }}" disabled />
            </div>

            <div class="col-md-4 mb-3">
              <label for="current_district" class="form-label">อำเภอ/เขต</label>
              <input id="current_district" class="form-control" value="{{ $currentAddress->district ?? '-' }}" disabled />
            </div>

            <div class="col-md-3 mb-3">
              <label for="current_province" class="form-label">จังหวัด</label>
              <input id="current_province" class="form-control" value="{{ $currentAddress->province ?? '-' }}" disabled />
            </div>

            <div class="col-md-2 mb-3">
              <label for="current_postal_code" class="form-label">เลขไปรษณีย์</label>
              <input id="current_postal_code" class="form-control" value="{{ $currentAddress->postal_code ?? '-' }}" disabled />
            </div>

          </div>

          <div class="row mt-2">
            <h3 class="form-label mb-3 fs-5">ที่อยู่สำหรับส่งเอกสาร</h3>

            <div class="col-md-2 mb-3">
              <label for="doc_house_number" class="form-label">บ้านเลขที่</label>
              <input id="doc_house_number" class="form-control" value="{{ $docAddress->house_number ?? '-' }}" disabled />
            </div>

            <div class="col-md-2 mb-3">
              <label for="doc_group" class="form-label">หมู่ที่</label>
              <input id="doc_group" class="form-control" value="{{ $docAddress->group ?? '-' }}" disabled />
            </div>

            <div class="col-md-4 mb-3">
              <label for="doc_village" class="form-label">หมู่บ้าน</label>
              <input id="doc_village" class="form-control" value="{{ $docAddress->village ?? '-' }}" disabled />
            </div>

            <div class="col-md-4 mb-3">
              <label for="doc_alley" class="form-label">ซอย</label>
              <input id="doc_alley" class="form-control" value="{{ $docAddress->alley ?? '-' }}" disabled />
            </div>

            <div class="col-md-4 mb-3">
              <label for="doc_road" class="form-label">ถนน</label>
              <input id="doc_road" class="form-control" value="{{ $docAddress->road ?? '-' }}" disabled />
            </div>

            <div class="col-md-4 mb-3">
              <label for="doc_subdistrict" class="form-label">ตำบล/แขวง</label>
              <input id="doc_subdistrict" class="form-control" value="{{ $docAddress->subdistrict ?? '-' }}" disabled />
            </div>

            <div class="col-md-4 mb-3">
              <label for="doc_district" class="form-label">อำเภอ/เขต</label>
              <input id="doc_district" class="form-control" value="{{ $docAddress->district ?? '-' }}" disabled />
            </div>

            <div class="col-md-3 mb-3">
              <label for="doc_province" class="form-label">จังหวัด</label>
              <input id="doc_province" class="form-control" value="{{ $docAddress->province ?? '-' }}" disabled />
            </div>

            <div class="col-md-2 mb-3">
              <label for="doc_postal_code" class="form-label">เลขไปรษณีย์</label>
              <input id="doc_postal_code" class="form-control" value="{{ $docAddress->postal_code ?? '-' }}" disabled />
            </div>

          </div>

        </div>
      </div>
    </div>
  </div>
</div>