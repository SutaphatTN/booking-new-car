<div class="modal fade viewCust" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h4 class="modal-title mb-2" id="viewCustLabel">ข้อมูลลูกค้า</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6 mb-5">
            <label for="model_id" class="form-label">ชื่อ - นามสกุล</label>
            <input class="form-control" type="text" value="{{ $customers->prefix->Name_TH }} {{ $customers->FirstName }} {{ $customers->LastName }}" disabled />
          </div>

          <div class="col-md-3 mb-5">
            <label for="model_id" class="form-label">เพศ</label>
            <input class="form-control" type="text" value="{{ $customers->Gender }}" disabled />
          </div>

          <div class="col-md-3 mb-5">
            <label for="model_id" class="form-label">วัน/เดือน/ปีเกิด</label>
            <input class="form-control" type="text" value="{{ $customers->formatted_Birthday }}" disabled />
          </div>

          <div class="col-md-4 mb-5">
            <label for="model_id" class="form-label">เลขบัตรประชาชน</label>
            <input class="form-control" type="text" value="{{ $customers->formatted_id_number }}" disabled />
          </div>

          <div class="col-md-4 mb-5">
            <label for="model_id" class="form-label">วันออกบัตรประชาชน</label>
            <input class="form-control" type="text" value="{{ $customers->formatted_new_card_date }}" disabled />
          </div>

          <div class="col-md-4 mb-5">
            <label for="model_id" class="form-label">วันที่บัตรประชาชนหมดอายุ</label>
            <input class="form-control" type="text" value="{{ $customers->formatted_expire_card }}" disabled />
          </div>

          <div class="col-md-4 mb-5">
            <label for="model_id" class="form-label">สัญชาติ</label>
            <input class="form-control" type="text" value="{{ $customers->Nationality }}" disabled />
          </div>

          <div class="col-md-2 mb-5">
            <label for="model_id" class="form-label">ศาสนา</label>
            <input class="form-control" type="text" value="{{ $customers->religion }}" disabled />
          </div>

          <div class="col-md-3 mb-5">
            <label for="model_id" class="form-label">เบอร์โทรหลัก</label>
            <input class="form-control" type="text" value="{{ $customers->formatted_mobile }}" disabled />
          </div>

          <div class="col-md-3 mb-5">
            <label for="model_id" class="form-label">เบอร์โทรสำรอง</label>
            <input class="form-control" type="text" value="{{ $customers->formatted_mobile_up }}" disabled />
          </div>

          <div class="row mt-2">
            <label class="form-label mb-3 fs-5">ที่อยู่ปัจจุบัน</label>

            <div class="col-md-2 mb-3">
              <label class="form-label">บ้านเลขที่</label>
              <input class="form-control" value="{{ $currentAddress->house_number ?? '-' }}" disabled />
            </div>

            <div class="col-md-2 mb-3">
              <label class="form-label">หมู่ที่</label>
              <input class="form-control" value="{{ $currentAddress->group ?? '-' }}" disabled />
            </div>

            <div class="col-md-4 mb-3">
              <label class="form-label">หมู่บ้าน</label>
              <input class="form-control" value="{{ $currentAddress->village ?? '-' }}" disabled />
            </div>

            <div class="col-md-4 mb-3">
              <label class="form-label">ซอย</label>
              <input class="form-control" value="{{ $currentAddress->alley ?? '-' }}" disabled />
            </div>

            <div class="col-md-4 mb-3">
              <label class="form-label">ถนน</label>
              <input class="form-control" value="{{ $currentAddress->road ?? '-' }}" disabled />
            </div>

            <div class="col-md-4 mb-3">
              <label class="form-label">ตำบล/แขวง</label>
              <input class="form-control" value="{{ $currentAddress->subdistrict ?? '-' }}" disabled />
            </div>

            <div class="col-md-4 mb-3">
              <label class="form-label">อำเภอ/เขต</label>
              <input class="form-control" value="{{ $currentAddress->district ?? '-' }}" disabled />
            </div>

            <div class="col-md-3 mb-3">
              <label class="form-label">จังหวัด</label>
              <input class="form-control" value="{{ $currentAddress->province ?? '-' }}" disabled />
            </div>

            <div class="col-md-2 mb-3">
              <label class="form-label">เลขไปรษณีย์</label>
              <input class="form-control" value="{{ $currentAddress->postal_code ?? '-' }}" disabled />
            </div>

          </div>

          <div class="row mt-2">
            <label class="form-label mb-3 fs-5">ที่อยู่สำหรับส่งเอกสาร</label>

            <div class="col-md-2 mb-3">
              <label class="form-label">บ้านเลขที่</label>
              <input class="form-control" value="{{ $docAddress->house_number ?? '-' }}" disabled />
            </div>

            <div class="col-md-2 mb-3">
              <label class="form-label">หมู่ที่</label>
              <input class="form-control" value="{{ $docAddress->group ?? '-' }}" disabled />
            </div>

            <div class="col-md-4 mb-3">
              <label class="form-label">หมู่บ้าน</label>
              <input class="form-control" value="{{ $docAddress->village ?? '-' }}" disabled />
            </div>

            <div class="col-md-4 mb-3">
              <label class="form-label">ซอย</label>
              <input class="form-control" value="{{ $docAddress->alley ?? '-' }}" disabled />
            </div>

            <div class="col-md-4 mb-3">
              <label class="form-label">ถนน</label>
              <input class="form-control" value="{{ $docAddress->road ?? '-' }}" disabled />
            </div>

            <div class="col-md-4 mb-3">
              <label class="form-label">ตำบล/แขวง</label>
              <input class="form-control" value="{{ $docAddress->subdistrict ?? '-' }}" disabled />
            </div>

            <div class="col-md-4 mb-3">
              <label class="form-label">อำเภอ/เขต</label>
              <input class="form-control" value="{{ $docAddress->district ?? '-' }}" disabled />
            </div>

            <div class="col-md-3 mb-3">
              <label class="form-label">จังหวัด</label>
              <input class="form-control" value="{{ $docAddress->province ?? '-' }}" disabled />
            </div>

            <div class="col-md-2 mb-3">
              <label class="form-label">เลขไปรษณีย์</label>
              <input class="form-control" value="{{ $docAddress->postal_code ?? '-' }}" disabled />
            </div>

          </div>

        </div>
      </div>
    </div>
  </div>
</div>