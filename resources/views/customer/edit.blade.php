<div class="modal fade editCust" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h4 class="modal-title mb-2" id="editCustLabel">แก้ไขข้อมูลลูกค้า</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form
          action="{{ route('customer.update', $customers->id) }}"
          method="POST"
          enctype="multipart/form-data">
          @csrf
          @method('PUT')

          <div class="row">
            <div class="col-md-2 mb-5">
              <label for="PrefixName" class="form-label">คำนำหน้า</label>
              <select id="PrefixName" name="PrefixName" class="form-select">
                <option value="">-- เลือกคำนำหน้า --</option>
                @foreach ($perfixName as $item)
                <option value="{{ @$item->id }}" {{ $customers->PrefixName == $item->id ? 'selected' : '' }}>
                  {{ @$item->Name_TH }}
                </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-5 mb-5">
              <label for="FirstName" class="form-label">ชื่อ</label>
              <input id="FirstName" type="text"
                class="form-control"
                name="FirstName" value="{{ $customers->FirstName }}" required>
            </div>

            <div class="col-md-5 mb-5">
              <label for="LastName" class="form-label">นามสกุล</label>
              <input id="LastName" type="text"
                class="form-control"
                name="LastName" value="{{ $customers->LastName }}">
            </div>

            <div class="col-md-4 mb-5">
              <label for="IDNumber" class="form-label">เลขบัตรประชาชน</label>
              <input id="IDNumber" type="text"
                class="form-control"
                name="IDNumber" maxlength="17" value="{{ $customers->formatted_id_number }}" required>
            </div>

            <div class="col-md-4 mb-5">
              <label for="NewCardDate" class="form-label">วันออกบัตรประชาชน</label>
              <input id="NewCardDate" type="date"
                class="form-control"
                name="NewCardDate" value="{{ $customers->NewCardDate }}" >
            </div>

            <div class="col-md-4 mb-5">
              <label for="ExpireCard" class="form-label">วันที่บัตรประชาชนหมดอายุ</label>
              <input id="ExpireCard" type="date"
                class="form-control"
                name="ExpireCard" value="{{ $customers->ExpireCard }}" >
            </div>

            <div class="col-md-4 mb-5">
              <label for="Birthday" class="form-label">วัน/เดือน/ปีเกิด</label>
              <input id="Birthday" type="date"
                class="form-control"
                name="Birthday" max="{{ date('Y-m-d') }}" value="{{ $customers->Birthday }}" >
            </div>

            <div class="col-md-4 mb-5">
              <label for="Gender" class="form-label">เพศ</label>
              <select id="Gender" name="Gender" class="form-select" >
                <option value="">-- เลือกเพศ --</option>
                <option value="Female" {{ $customers->Gender == 'Female' ? 'selected' : '' }}>หญิง</option>
                <option value="Male" {{ $customers->Gender == 'Male' ? 'selected' : '' }}>ชาย</option>
              </select>
            </div>

            <div class="col-md-4 mb-5">
              <label for="Nationality" class="form-label">สัญชาติ</label>
              <input id="Nationality" type="text"
                class="form-control"
                name="Nationality" value="{{ $customers->Nationality }}" >
            </div>

            <div class="col-md-4 mb-5">
              <label for="religion" class="form-label">ศาสนา</label>
              <select id="religion" name="religion" class="form-select" >
                <option value="">-- เลือกศาสนา --</option>
                <option value="buddhist" {{ $customers->religion == 'buddhist' ? 'selected' : '' }}>พุทธ</option>
                <option value="islam" {{ $customers->religion == 'islam' ? 'selected' : '' }}>อิสลาม</option>
                <option value="christian" {{ $customers->religion == 'Female' ? 'selected' : '' }}>คริสต์</option>
                <option value="other" {{ $customers->religion == 'Male' ? 'selected' : '' }}>อื่นๆ</option>
              </select>
            </div>

            <div class="col-md-4 mb-5">
              <label for="Mobilephone1" class="form-label">เบอร์โทรหลัก</label>
              <input id="Mobilephone1" type="text"
                class="form-control"
                name="Mobilephone1" maxlength="12" value="{{ $customers->formatted_mobile }}" required>
            </div>

            <div class="col-md-4 mb-5">
              <label for="Mobilephone2" class="form-label">เบอร์โทรสำรอง</label>
              <input id="Mobilephone2" type="text"
                class="form-control"
                name="Mobilephone2" maxlength="12" value="{{ $customers->formatted_mobile_up}}">
            </div>

            <div class="row mt-5">
              <h3 class="form-label mb-4 fs-5" for="Address">ที่อยู่ปัจจุบัน</h3>

              <div class="col-md-2 mb-5">
                <label for="current_house_number" class="form-label">เลขที่</label>
                <input id="current_house_number" type="text" name="current_house_number"
                  class="form-control"
                  value="{{ old('current_house_number', $currentAddress->house_number ?? '') }}"
                  required>
              </div>

              <div class="col-md-2 mb-5">
                <label for="current_group" class="form-label">หมู่ที่</label>
                <input id="current_group" type="text" name="current_group"
                  class="form-control"
                  value="{{ old('current_group', $currentAddress->group ?? '') }}">
              </div>

              <div class="col-md-4 mb-5">
                <label for="current_village" class="form-label">หมู่บ้าน</label>
                <input id="current_village" type="text" name="current_village"
                  class="form-control"
                  value="{{ old('current_village', $currentAddress->village ?? '') }}">
              </div>

              <div class="col-md-4 mb-5">
                <label for="current_alley" class="form-label">ซอย</label>
                <input id="current_alley" type="text" name="current_alley"
                  class="form-control"
                  value="{{ old('current_alley', $currentAddress->alley ?? '') }}">
              </div>

              <div class="col-md-4 mb-5">
                <label for="current_road" class="form-label">ถนน</label>
                <input id="current_road" type="text" name="current_road"
                  class="form-control"
                  value="{{ old('current_road', $currentAddress->road ?? '') }}">
              </div>

              <div class="col-md-4 mb-5">
                <label for="current_province" class="form-label">จังหวัด</label>
                <select id="current_province" name="current_province" class="form-select" required>
                  <option value="">-- เลือกจังหวัด --</option>
                  @foreach($provinces as $p)
                    <option value="{{ $p }}" {{ ($currentAddress->province ?? '') === $p ? 'selected' : '' }}>{{ $p }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-4 mb-5">
                <label for="current_district" class="form-label">อำเภอ/เขต</label>
                <select id="current_district" name="current_district" class="form-select" required
                  {{ $currentDistricts->isEmpty() ? 'disabled' : '' }}>
                  <option value="">-- เลือกอำเภอ --</option>
                  @foreach($currentDistricts as $d)
                    <option value="{{ $d }}" {{ ($currentAddress->district ?? '') === $d ? 'selected' : '' }}>{{ $d }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-3 mb-5">
                <label for="current_subdistrict" class="form-label">ตำบล/แขวง</label>
                <select id="current_subdistrict" name="current_subdistrict" class="form-select" required
                  {{ $currentTambons->isEmpty() ? 'disabled' : '' }}>
                  <option value="">-- เลือกตำบล --</option>
                  @foreach($currentTambons as $t)
                    <option value="{{ $t->Tambon_pro }}"
                      data-postal="{{ $t->Postcode_pro }}"
                      data-post-id="{{ $t->id }}"
                      {{ ($currentAddress->subdistrict ?? '') === $t->Tambon_pro ? 'selected' : '' }}>{{ $t->Tambon_pro }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-2 mb-5">
                <label for="current_postal_code" class="form-label">เลขไปรษณีย์</label>
                <input id="current_postal_code" type="text" name="current_postal_code"
                  class="form-control" readonly
                  value="{{ old('current_postal_code', $currentAddress->postal_code ?? '') }}">
              </div>
              <input type="hidden" name="current_post_id" value="{{ old('current_post_id', $currentAddress->post_id ?? '') }}">
            </div>

            @php
            $isSameAddress =
            isset($currentAddress, $docAddress) &&
            $currentAddress->house_number == $docAddress->house_number &&
            $currentAddress->group == $docAddress->group &&
            $currentAddress->village == $docAddress->village &&
            $currentAddress->alley == $docAddress->alley &&
            $currentAddress->road == $docAddress->road &&
            $currentAddress->subdistrict == $docAddress->subdistrict &&
            $currentAddress->district == $docAddress->district &&
            $currentAddress->province == $docAddress->province &&
            $currentAddress->postal_code == $docAddress->postal_code;
            @endphp

            <div class="row mt-5">
              <label class="form-label w-100">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="mb-4 fs-5">ที่อยู่สำหรับส่งเอกสาร</span>
                  <div class="form-check mb-0">
                    <input
                      class="form-check-input"
                      type="checkbox"
                      id="sameAsCurrent"
                      {{ $isSameAddress ? 'checked' : '' }}>
                    <label for="sameAsCurrent" class="form-check-label fs-6">
                      ใช้ที่อยู่เดียวกับที่อยู่ปัจจุบัน
                    </label>
                  </div>
                </div>
              </label>

              <div class="col-md-2 mb-5">
                <label for="doc_house_number" class="form-label">เลขที่</label>
                <input id="doc_house_number" type="text" name="doc_house_number"
                  class="form-control"
                  value="{{ old('doc_house_number', $docAddress->house_number ?? '') }}">
              </div>

              <div class="col-md-2 mb-5">
                <label for="doc_group" class="form-label">หมู่ที่</label>
                <input id="doc_group" type="text" name="doc_group"
                  class="form-control"
                  value="{{ old('doc_group', $docAddress->group ?? '') }}">
              </div>

              <div class="col-md-4 mb-5">
                <label for="doc_village" class="form-label">หมู่บ้าน</label>
                <input id="doc_village" type="text" name="doc_village"
                  class="form-control"
                  value="{{ old('doc_village', $docAddress->village ?? '') }}">
              </div>

              <div class="col-md-4 mb-5">
                <label for="doc_alley" class="form-label">ซอย</label>
                <input id="doc_alley" type="text" name="doc_alley"
                  class="form-control"
                  value="{{ old('doc_alley', $docAddress->alley ?? '') }}">
              </div>

              <div class="col-md-4 mb-5">
                <label for="doc_road" class="form-label">ถนน</label>
                <input id="doc_road" type="text" name="doc_road"
                  class="form-control"
                  value="{{ old('doc_road', $docAddress->road ?? '') }}">
              </div>

              <div class="col-md-4 mb-5">
                <label for="doc_province" class="form-label">จังหวัด</label>
                <select id="doc_province" name="doc_province" class="form-select" required>
                  <option value="">-- เลือกจังหวัด --</option>
                  @foreach($provinces as $p)
                    <option value="{{ $p }}" {{ ($docAddress->province ?? '') === $p ? 'selected' : '' }}>{{ $p }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-4 mb-5">
                <label for="doc_district" class="form-label">อำเภอ/เขต</label>
                <select id="doc_district" name="doc_district" class="form-select" required
                  {{ $docDistricts->isEmpty() ? 'disabled' : '' }}>
                  <option value="">-- เลือกอำเภอ --</option>
                  @foreach($docDistricts as $d)
                    <option value="{{ $d }}" {{ ($docAddress->district ?? '') === $d ? 'selected' : '' }}>{{ $d }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-3 mb-5">
                <label for="doc_subdistrict" class="form-label">ตำบล/แขวง</label>
                <select id="doc_subdistrict" name="doc_subdistrict" class="form-select" required
                  {{ $docTambons->isEmpty() ? 'disabled' : '' }}>
                  <option value="">-- เลือกตำบล --</option>
                  @foreach($docTambons as $t)
                    <option value="{{ $t->Tambon_pro }}"
                      data-postal="{{ $t->Postcode_pro }}"
                      data-post-id="{{ $t->id }}"
                      {{ ($docAddress->subdistrict ?? '') === $t->Tambon_pro ? 'selected' : '' }}>{{ $t->Tambon_pro }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-2 mb-5">
                <label for="doc_postal_code" class="form-label">เลขไปรษณีย์</label>
                <input id="doc_postal_code" type="text" name="doc_postal_code"
                  class="form-control" readonly
                  value="{{ old('doc_postal_code', $docAddress->postal_code ?? '') }}">
              </div>
              <input type="hidden" name="doc_post_id" value="{{ old('doc_post_id', $docAddress->post_id ?? '') }}">
            </div>

          </div>

          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">ยกเลิก</button>
            <button type="button" id="btnUpdateCustomer" class="btn btn-primary">บันทึก</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>