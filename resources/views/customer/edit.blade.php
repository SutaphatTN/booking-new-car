<div class="modal fade editCust" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content border-0 shadow">

      {{-- ── Header ── --}}
      <div class="modal-header border-0 px-4 py-3"
        style="background: linear-gradient(135deg, #e97f07 0%, #f3a319 100%); border-radius:0.5rem 0.5rem 0 0;">
        <div class="d-flex align-items-center gap-3">
          <div class="vm-section-icon"
            style="background:rgba(255,255,255,.2);color:#fff;width:40px;height:40px;border-radius:10px;">
            <i class="bx bx-edit fs-5"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">แก้ไขข้อมูลลูกค้า</h6>
            <small class="text-white mf-hd-sub">Edit Customer Profile</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4" style="background:#f5f6fa;border-radius:0 0 0.5rem 0.5rem;">
        <form action="{{ route('customer.update', $customers->id) }}" method="POST" enctype="multipart/form-data">
          @csrf
          @method('PUT')

          {{-- ── Card 1 : ข้อมูลส่วนตัว ── --}}
          <div class="vm-section mb-3">
            <div class="vm-section-header">
              <div class="vm-section-icon indigo"><i class="bx bx-id-card"></i></div>
              <h6 class="vm-section-title">ข้อมูลส่วนตัว</h6>
            </div>
            <div class="vm-section-body">
              <div class="row g-3">

                <div class="col-md-2">
                  <label for="PrefixName" class="vm-label"><i class="bx bx-list-ul"></i> คำนำหน้า</label>
                  <select id="PrefixName" name="PrefixName" class="form-select">
                    <option value="">— เลือก —</option>
                    @foreach ($perfixName as $item)
                      <option value="{{ $item->id }}" {{ $customers->PrefixName == $item->id ? 'selected' : '' }}>
                        {{ $item->Name_TH }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div class="col-md-4">
                  <label for="FirstName" class="vm-label"><i class="bx bx-user"></i> ชื่อ <span
                      class="text-danger">*</span></label>
                  <input id="FirstName" type="text" class="form-control" name="FirstName"
                    value="{{ $customers->FirstName }}" required>
                </div>

                <div class="col-md-4">
                  <label for="LastName" class="vm-label"><i class="bx bx-user-pin"></i> นามสกุล</label>
                  <input id="LastName" type="text" class="form-control" name="LastName"
                    value="{{ $customers->LastName }}">
                </div>

                <div class="col-md-2">
                  <label for="Gender" class="vm-label"><i class="bx bx-user-circle"></i> เพศ</label>
                  <select id="Gender" name="Gender" class="form-select">
                    <option value="">— เลือก —</option>
                    <option value="Female" {{ $customers->Gender == 'Female' ? 'selected' : '' }}>หญิง</option>
                    <option value="Male" {{ $customers->Gender == 'Male' ? 'selected' : '' }}>ชาย</option>
                  </select>
                </div>

                <div class="col-md-3">
                  <label for="Birthday" class="vm-label"><i class="bx bx-calendar"></i> วัน/เดือน/ปีเกิด</label>
                  <input id="Birthday" type="date" class="form-control" name="Birthday" max="{{ date('Y-m-d') }}"
                    value="{{ $customers->Birthday }}">
                </div>

                <div class="col-md-3">
                  <label for="IDNumber" class="vm-label"><i class="bx bx-id-card"></i> เลขบัตรประชาชน <span
                      class="text-danger">*</span></label>
                  <input id="IDNumber" type="text" class="form-control" name="IDNumber" maxlength="17"
                    value="{{ $customers->formatted_id_number }}" required>
                </div>

                <div class="col-md-3">
                  <label for="NewCardDate" class="vm-label"><i class="bx bx-calendar-check"></i>
                    วันออกบัตรประชาชน</label>
                  <input id="NewCardDate" type="date" class="form-control" name="NewCardDate"
                    value="{{ $customers->NewCardDate }}">
                </div>

                <div class="col-md-3">
                  <label for="ExpireCard" class="vm-label"><i class="bx bx-calendar-x"></i> วันที่บัตรหมดอายุ</label>
                  <input id="ExpireCard" type="date" class="form-control" name="ExpireCard"
                    value="{{ $customers->ExpireCard }}">
                </div>

                <div class="col-md-2">
                  <label for="Nationality" class="vm-label"><i class="bx bx-flag"></i> สัญชาติ</label>
                  <input id="Nationality" type="text" class="form-control" name="Nationality"
                    value="{{ $customers->Nationality }}">
                </div>

                <div class="col-md-2">
                  <label for="religion" class="vm-label"><i class="bx bx-book-open"></i> ศาสนา</label>
                  <select id="religion" name="religion" class="form-select">
                    <option value="">— เลือก —</option>
                    <option value="buddhist" {{ $customers->religion == 'buddhist' ? 'selected' : '' }}>พุทธ</option>
                    <option value="islam" {{ $customers->religion == 'islam' ? 'selected' : '' }}>อิสลาม</option>
                    <option value="christian"{{ $customers->religion == 'christian' ? 'selected' : '' }}>คริสต์
                    </option>
                    <option value="other" {{ $customers->religion == 'other' ? 'selected' : '' }}>อื่นๆ</option>
                  </select>
                </div>

                <div class="col-md-3">
                  <label for="Mobilephone1" class="vm-label"><i class="bx bx-phone"></i> เบอร์โทรหลัก <span
                      class="text-danger">*</span></label>
                  <input id="Mobilephone1" type="text" class="form-control" name="Mobilephone1" maxlength="12"
                    value="{{ $customers->formatted_mobile }}" required>
                </div>

                <div class="col-md-3">
                  <label for="Mobilephone2" class="vm-label"><i class="bx bxs-phone"></i> เบอร์โทรสำรอง</label>
                  <input id="Mobilephone2" type="text" class="form-control" name="Mobilephone2" maxlength="12"
                    value="{{ $customers->formatted_mobile_up }}">
                </div>

              </div>
            </div>
          </div>

          {{-- ── Card 2 & 3 : ที่อยู่ ── --}}
          <div class="row g-3 mb-3">

            {{-- ที่อยู่ปัจจุบัน --}}
            <div class="col-md-6">
              <div class="vm-section h-100">
                <div class="vm-section-header">
                  <div class="vm-section-icon emerald"><i class="bx bx-home-alt"></i></div>
                  <h6 class="vm-section-title">ที่อยู่ปัจจุบัน</h6>
                </div>
                <div class="vm-section-body">
                  <div class="row g-3">

                    <div class="col-md-2">
                      <label for="current_house_number" class="vm-label"><i class="bx bx-home"></i> เลขที่ <span
                          class="text-danger">*</span></label>
                      <input id="current_house_number" type="text" name="current_house_number"
                        class="form-control"
                        value="{{ old('current_house_number', $currentAddress->house_number ?? '') }}" required>
                    </div>

                    <div class="col-md-2">
                      <label for="current_group" class="vm-label"><i class="bx bx-map"></i> หมู่ที่</label>
                      <input id="current_group" type="text" name="current_group" class="form-control"
                        value="{{ old('current_group', $currentAddress->group ?? '') }}">
                    </div>

                    <div class="col-md-4">
                      <label for="current_alley" class="vm-label"><i class="bx bx-navigation"></i> ซอย</label>
                      <input id="current_alley" type="text" name="current_alley" class="form-control"
                        value="{{ old('current_alley', $currentAddress->alley ?? '') }}">
                    </div>

                    <div class="col-md-4">
                      <label for="current_village" class="vm-label"><i class="bx bx-buildings"></i> หมู่บ้าน</label>
                      <input id="current_village" type="text" name="current_village" class="form-control"
                        value="{{ old('current_village', $currentAddress->village ?? '') }}">
                    </div>

                    <div class="col-md-6">
                      <label for="current_road" class="vm-label"><i class="bx bx-trip"></i> ถนน</label>
                      <input id="current_road" type="text" name="current_road" class="form-control"
                        value="{{ old('current_road', $currentAddress->road ?? '') }}">
                    </div>

                    <div class="col-md-6">
                      <label for="current_province" class="vm-label"><i class="bx bx-map"></i> จังหวัด <span
                          class="text-danger">*</span></label>
                      <select id="current_province" name="current_province" class="form-select" required>
                        <option value="">— เลือกจังหวัด —</option>
                        @foreach ($provinces as $p)
                          <option value="{{ $p }}"
                            {{ ($currentAddress->province ?? '') === $p ? 'selected' : '' }}>{{ $p }}
                          </option>
                        @endforeach
                      </select>
                    </div>

                    <div class="col-md-5">
                      <label for="current_district" lass="vm-label"><i class="bx bx-map-alt"></i> อำเภอ/เขต <span
                          class="text-danger">*</span></label>
                      <select id="current_district" name="current_district" class="form-select" required
                        {{ $currentDistricts->isEmpty() ? 'disabled' : '' }}>
                        <option value="">— เลือกอำเภอ —</option>
                        @foreach ($currentDistricts as $d)
                          <option value="{{ $d }}"
                            {{ ($currentAddress->district ?? '') === $d ? 'selected' : '' }}>{{ $d }}
                          </option>
                        @endforeach
                      </select>
                    </div>

                    <div class="col-md-4">
                      <label for="current_subdistrict" class="vm-label"><i class="bx bx-map-alt"></i> ตำบล/แขวง <span
                          class="text-danger">*</span></label>
                      <select id="current_subdistrict" name="current_subdistrict" class="form-select" required
                        {{ $currentTambons->isEmpty() ? 'disabled' : '' }}>
                        <option value="">— เลือกตำบล —</option>
                        @foreach ($currentTambons as $t)
                          <option value="{{ $t->Tambon_pro }}" data-postal="{{ $t->Postcode_pro }}"
                            data-post-id="{{ $t->id }}"
                            {{ ($currentAddress->subdistrict ?? '') === $t->Tambon_pro ? 'selected' : '' }}>
                            {{ $t->Tambon_pro }}</option>
                        @endforeach
                      </select>
                    </div>

                    <div class="col-md-3">
                      <label for="current_postal_code" class="vm-label"><i class="bx bx-envelope"></i>
                        รหัสไปรษณีย์</label>
                      <input id="current_postal_code" type="text" name="current_postal_code" class="form-control"
                        readonly value="{{ old('current_postal_code', $currentAddress->postal_code ?? '') }}">
                    </div>

                    <input type="hidden" name="current_post_id"
                      value="{{ old('current_post_id', $currentAddress->post_id ?? '') }}">
                  </div>
                </div>
              </div>
            </div>

            {{-- ที่อยู่สำหรับส่งเอกสาร --}}
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

            <div class="col-md-6">
              <div class="vm-section h-100">
                <div class="vm-section-header" style="justify-content:space-between;">
                  <div class="d-flex align-items-center gap-2">
                    <div class="vm-section-icon amber"><i class="bx bx-file"></i></div>
                    <h6 class="vm-section-title">ที่อยู่สำหรับส่งเอกสาร</h6>
                  </div>
                  <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" id="sameAsCurrent"
                      {{ $isSameAddress ? 'checked' : '' }}>
                    <label class="form-check-label" for="sameAsCurrent" style="font-size:.82rem;">
                      ใช้ที่อยู่เดียวกัน
                    </label>
                  </div>
                </div>
                <div class="vm-section-body">
                  <div class="row g-3">

                    <div class="col-md-2">
                      <label for="doc_house_number" class="vm-label"><i class="bx bx-home"></i> เลขที่</label>
                      <input id="doc_house_number" type="text" name="doc_house_number" class="form-control"
                        value="{{ old('doc_house_number', $docAddress->house_number ?? '') }}">
                    </div>

                    <div class="col-md-2">
                      <label for="doc_group" class="vm-label"><i class="bx bx-map"></i> หมู่ที่</label>
                      <input id="doc_group" type="text" name="doc_group" class="form-control"
                        value="{{ old('doc_group', $docAddress->group ?? '') }}">
                    </div>

                    <div class="col-md-4">
                      <label for="doc_alley" class="vm-label"><i class="bx bx-navigation"></i> ซอย</label>
                      <input id="doc_alley" type="text" name="doc_alley" class="form-control"
                        value="{{ old('doc_alley', $docAddress->alley ?? '') }}">
                    </div>

                    <div class="col-md-4">
                      <label for="doc_village" class="vm-label"><i class="bx bx-buildings"></i> หมู่บ้าน</label>
                      <input id="doc_village" type="text" name="doc_village" class="form-control"
                        value="{{ old('doc_village', $docAddress->village ?? '') }}">
                    </div>

                    <div class="col-md-6">
                      <label for="doc_road" class="vm-label"><i class="bx bx-trip"></i> ถนน</label>
                      <input id="doc_road" type="text" name="doc_road" class="form-control"
                        value="{{ old('doc_road', $docAddress->road ?? '') }}">
                    </div>

                    <div class="col-md-6">
                      <label for="doc_province" class="vm-label"><i class="bx bx-map"></i> จังหวัด <span
                          class="text-danger">*</span></label>
                      <select id="doc_province" name="doc_province" class="form-select" required>
                        <option value="">— เลือกจังหวัด —</option>
                        @foreach ($provinces as $p)
                          <option value="{{ $p }}"
                            {{ ($docAddress->province ?? '') === $p ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                      </select>
                    </div>

                    <div class="col-md-5">
                      <label for="doc_district" class="vm-label"><i class="bx bx-map-alt"></i> อำเภอ/เขต <span
                          class="text-danger">*</span></label>
                      <select id="doc_district" name="doc_district" class="form-select" required
                        {{ $docDistricts->isEmpty() ? 'disabled' : '' }}>
                        <option value="">— เลือกอำเภอ —</option>
                        @foreach ($docDistricts as $d)
                          <option value="{{ $d }}"
                            {{ ($docAddress->district ?? '') === $d ? 'selected' : '' }}>{{ $d }}</option>
                        @endforeach
                      </select>
                    </div>

                    <div class="col-md-4">
                      <label for="doc_subdistrict" class="vm-label"><i class="bx bx-map-alt"></i> ตำบล/แขวง <span
                          class="text-danger">*</span></label>
                      <select id="doc_subdistrict" name="doc_subdistrict" class="form-select" required
                        {{ $docTambons->isEmpty() ? 'disabled' : '' }}>
                        <option value="">— เลือกตำบล —</option>
                        @foreach ($docTambons as $t)
                          <option value="{{ $t->Tambon_pro }}" data-postal="{{ $t->Postcode_pro }}"
                            data-post-id="{{ $t->id }}"
                            {{ ($docAddress->subdistrict ?? '') === $t->Tambon_pro ? 'selected' : '' }}>
                            {{ $t->Tambon_pro }}</option>
                        @endforeach
                      </select>
                    </div>

                    <div class="col-md-3">
                      <label for="doc_postal_code" class="vm-label"><i class="bx bx-envelope"></i>
                        รหัสไปรษณีย์</label>
                      <input id="doc_postal_code" type="text" name="doc_postal_code" class="form-control"
                        readonly value="{{ old('doc_postal_code', $docAddress->postal_code ?? '') }}">
                    </div>

                    <input type="hidden" name="doc_post_id"
                      value="{{ old('doc_post_id', $docAddress->post_id ?? '') }}">
                  </div>
                </div>
              </div>
            </div>

          </div>{{-- /row cards 2&3 --}}

          {{-- ── Actions ── --}}
          <div class="d-flex justify-content-end gap-2 pt-1">
            <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
              <i class="bx bx-x me-1"></i>ยกเลิก
            </button>
            <button type="button" id="btnUpdateCustomer" class="btn btn-primary px-5">
              <i class="bx bx-save me-1"></i>บันทึก
            </button>
          </div>

        </form>
      </div>

    </div>
  </div>
</div>
