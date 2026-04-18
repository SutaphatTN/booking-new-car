<div class="modal fade viewCust" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content border-0 shadow">

      {{-- ── Header ── --}}
      <div class="modal-header border-0 px-4 py-3"
        style="background:linear-gradient(135deg,#0ea5e9 0%,#38bdf8 100%);border-radius:0.5rem 0.5rem 0 0;">
        <div class="d-flex align-items-center gap-3">
          <div class="vm-section-icon"
            style="background:rgba(255,255,255,.2);color:#fff;width:40px;height:40px;border-radius:10px;">
            <i class="bx bx-user fs-5"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">ข้อมูลลูกค้า</h6>
            <small class="text-white mf-hd-sub">Customer Profile</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body p-4" style="background:#f5f6fa;border-radius:0 0 0.5rem 0.5rem;">

        {{-- ── Card 1 : ข้อมูลส่วนตัว ── --}}
        <div class="vm-section mb-3">
          <div class="vm-section-header">
            <div class="vm-section-icon indigo"><i class="bx bxs-user-rectangle"></i></div>
            <h6 class="vm-section-title">ข้อมูลส่วนตัว</h6>
          </div>
          <div class="vm-section-body">
            <div class="row g-3">

              @php $fullname = trim(($customers->prefix->Name_TH ?? '').' '.($customers->FirstName ?? '').' '.($customers->LastName ?? '')); @endphp

              <div class="col-md-6">
                <div class="vm-label"><i class="bx bx-user"></i> ชื่อ - นามสกุล</div>
                <div class="vm-val {{ $fullname ? '' : 'is-empty' }}">{{ $fullname ?: '—' }}</div>
              </div>
              <div class="col-md-4">
                <div class="vm-label"><i class="bx bx-id-card"></i> เลขบัตรประชาชน</div>
                <div class="vm-val font-monospace {{ $customers->formatted_id_number ? '' : 'is-empty' }}">
                  {{ $customers->formatted_id_number ?: '—' }}</div>
              </div>
              <div class="col-md-2">
                <div class="vm-label"><i class="bx bx-user-circle"></i> เพศ</div>
                <div class="vm-val {{ $customers->gender_th ? '' : 'is-empty' }}">{{ $customers->gender_th ?: '—' }}
                </div>
              </div>

              <div class="col-md-3">
                <div class="vm-label"><i class="bx bx-calendar"></i> วัน/เดือน/ปีเกิด</div>
                <div class="vm-val {{ $customers->formatted_Birthday ? '' : 'is-empty' }}">
                  {{ $customers->formatted_Birthday ?: '—' }}</div>
              </div>
              <div class="col-md-3">
                <div class="vm-label"><i class="bx bx-calendar-check"></i> วันออกบัตรประชาชน</div>
                <div class="vm-val {{ $customers->formatted_new_card_date ? '' : 'is-empty' }}">
                  {{ $customers->formatted_new_card_date ?: '—' }}</div>
              </div>
              <div class="col-md-3">
                <div class="vm-label"><i class="bx bx-calendar-x"></i> วันที่บัตรหมดอายุ</div>
                <div class="vm-val {{ $customers->formatted_expire_card ? '' : 'is-empty' }}">
                  {{ $customers->formatted_expire_card ?: '—' }}</div>
              </div>

              <div class="col-md-3">
                <div class="vm-label"><i class="bx bx-flag"></i> สัญชาติ</div>
                <div class="vm-val {{ $customers->Nationality ? '' : 'is-empty' }}">
                  {{ $customers->Nationality ?: '—' }}</div>
              </div>
              <div class="col-md-3">
                <div class="vm-label"><i class="bx bx-book-open"></i> ศาสนา</div>
                <div class="vm-val {{ $customers->religion_th ? '' : 'is-empty' }}">
                  {{ $customers->religion_th ?: '—' }}</div>
              </div>
              <div class="col-md-3">
                <div class="vm-label"><i class="bx bx-phone"></i> เบอร์โทรหลัก</div>
                <div class="vm-val {{ $customers->formatted_mobile ? '' : 'is-empty' }}">
                  {{ $customers->formatted_mobile ?: '—' }}</div>
              </div>
              <div class="col-md-3">
                <div class="vm-label"><i class="bx bxs-phone"></i> เบอร์โทรสำรอง</div>
                <div class="vm-val {{ $customers->formatted_mobile_up ? '' : 'is-empty' }}">
                  {{ $customers->formatted_mobile_up ?: '—' }}</div>
              </div>

            </div>
          </div>
        </div>

        {{-- ── Card 2 & 3 : ที่อยู่ (side-by-side) ── --}}
        <div class="row g-3">

          {{-- ที่อยู่ปัจจุบัน --}}
          <div class="col-md-6">
            <div class="vm-section h-100">
              <div class="vm-section-header">
                <div class="vm-section-icon emerald"><i class="bx bx-home-alt"></i></div>
                <h6 class="vm-section-title">ที่อยู่ปัจจุบัน</h6>
              </div>
              <div class="vm-section-body">
                <div class="row g-3">
                  <div class="col-4">
                    <div class="vm-label"><i class="bx bx-home"></i> บ้านเลขที่</div>
                    <div class="vm-val {{ $currentAddress->house_number ?? '' ? '' : 'is-empty' }}">
                      {{ $currentAddress->house_number ?? '—' }}</div>
                  </div>
                  <div class="col-4">
                    <div class="vm-label"><i class="bx bx-map"></i> หมู่ที่</div>
                    <div class="vm-val {{ $currentAddress->group ?? '' ? '' : 'is-empty' }}">
                      {{ $currentAddress->group ?? '—' }}</div>
                  </div>
                  <div class="col-4">
                    <div class="vm-label"><i class="bx bx-navigation"></i> ซอย</div>
                    <div class="vm-val {{ $currentAddress->alley ?? '' ? '' : 'is-empty' }}">
                      {{ $currentAddress->alley ?? '—' }}</div>
                  </div>
                  <div class="col-6">
                    <div class="vm-label"><i class="bx bx-buildings"></i> หมู่บ้าน</div>
                    <div class="vm-val {{ $currentAddress->village ?? '' ? '' : 'is-empty' }}">
                      {{ $currentAddress->village ?? '—' }}</div>
                  </div>
                  <div class="col-6">
                    <div class="vm-label"><i class="bx bx-trip"></i> ถนน</div>
                    <div class="vm-val {{ $currentAddress->road ?? '' ? '' : 'is-empty' }}">
                      {{ $currentAddress->road ?? '—' }}</div>
                  </div>
                  <div class="col-6">
                    <div class="vm-label"><i class="bx bx-map-alt"></i> ตำบล/แขวง</div>
                    <div class="vm-val {{ $currentAddress->subdistrict ?? '' ? '' : 'is-empty' }}">
                      {{ $currentAddress->subdistrict ?? '—' }}</div>
                  </div>
                  <div class="col-6">
                    <div class="vm-label"><i class="bx bx-map-alt"></i> อำเภอ/เขต</div>
                    <div class="vm-val {{ $currentAddress->district ?? '' ? '' : 'is-empty' }}">
                      {{ $currentAddress->district ?? '—' }}</div>
                  </div>
                  <div class="col-8">
                    <div class="vm-label"><i class="bx bx-map"></i> จังหวัด</div>
                    <div class="vm-val {{ $currentAddress->province ?? '' ? '' : 'is-empty' }}">
                      {{ $currentAddress->province ?? '—' }}</div>
                  </div>
                  <div class="col-4">
                    <div class="vm-label"><i class="bx bx-envelope"></i> รหัสไปรษณีย์</div>
                    <div class="vm-val font-monospace {{ $currentAddress->postal_code ?? '' ? '' : 'is-empty' }}">
                      {{ $currentAddress->postal_code ?? '—' }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- ที่อยู่สำหรับส่งเอกสาร --}}
          <div class="col-md-6">
            <div class="vm-section h-100">
              <div class="vm-section-header">
                <div class="vm-section-icon amber"><i class="bx bx-file"></i></div>
                <h6 class="vm-section-title">ที่อยู่สำหรับส่งเอกสาร</h6>
              </div>
              <div class="vm-section-body">
                <div class="row g-3">
                  <div class="col-4">
                    <div class="vm-label"><i class="bx bx-home"></i> บ้านเลขที่</div>
                    <div class="vm-val {{ $docAddress->house_number ?? '' ? '' : 'is-empty' }}">
                      {{ $docAddress->house_number ?? '—' }}</div>
                  </div>
                  <div class="col-4">
                    <div class="vm-label"><i class="bx bx-map"></i> หมู่ที่</div>
                    <div class="vm-val {{ $docAddress->group ?? '' ? '' : 'is-empty' }}">
                      {{ $docAddress->group ?? '—' }}</div>
                  </div>
                  <div class="col-4">
                    <div class="vm-label"><i class="bx bx-navigation"></i> ซอย</div>
                    <div class="vm-val {{ $docAddress->alley ?? '' ? '' : 'is-empty' }}">
                      {{ $docAddress->alley ?? '—' }}</div>
                  </div>
                  <div class="col-6">
                    <div class="vm-label"><i class="bx bx-buildings"></i> หมู่บ้าน</div>
                    <div class="vm-val {{ $docAddress->village ?? '' ? '' : 'is-empty' }}">
                      {{ $docAddress->village ?? '—' }}</div>
                  </div>
                  <div class="col-6">
                    <div class="vm-label"><i class="bx bx-trip"></i> ถนน</div>
                    <div class="vm-val {{ $docAddress->road ?? '' ? '' : 'is-empty' }}">
                      {{ $docAddress->road ?? '—' }}</div>
                  </div>
                  <div class="col-6">
                    <div class="vm-label"><i class="bx bx-map-alt"></i> ตำบล/แขวง</div>
                    <div class="vm-val {{ $docAddress->subdistrict ?? '' ? '' : 'is-empty' }}">
                      {{ $docAddress->subdistrict ?? '—' }}</div>
                  </div>
                  <div class="col-6">
                    <div class="vm-label"><i class="bx bx-map-alt"></i> อำเภอ/เขต</div>
                    <div class="vm-val {{ $docAddress->district ?? '' ? '' : 'is-empty' }}">
                      {{ $docAddress->district ?? '—' }}</div>
                  </div>
                  <div class="col-8">
                    <div class="vm-label"><i class="bx bx-map"></i> จังหวัด</div>
                    <div class="vm-val {{ $docAddress->province ?? '' ? '' : 'is-empty' }}">
                      {{ $docAddress->province ?? '—' }}</div>
                  </div>
                  <div class="col-4">
                    <div class="vm-label"><i class="bx bx-envelope"></i> รหัสไปรษณีย์</div>
                    <div class="vm-val font-monospace {{ $docAddress->postal_code ?? '' ? '' : 'is-empty' }}">
                      {{ $docAddress->postal_code ?? '—' }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>{{-- /row --}}
      </div>{{-- /modal-body --}}

    </div>
  </div>
</div>
