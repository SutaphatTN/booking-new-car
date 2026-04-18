<div class="modal fade editVehicle" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--edit">

      {{-- Header --}}
      <div class="modal-header mf-header mf-header--edit px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-edit-alt fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">แก้ไขข้อมูลป้ายทะเบียน</h6>
            <small class="text-white mf-hd-sub">Edit Vehicle License</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">
        <form action="{{ route('vehicle.update', $veh->id) }}" method="POST" enctype="multipart/form-data">
          @csrf
          @method('PUT')

          {{-- Section 1 : ข้อมูลรถและลูกค้า --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon indigo">
                <i class="bx bx-car"></i>
              </div>
              <span class="mf-section-title">ข้อมูลรถและลูกค้า</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-5">
                  <label for="FullName" class="mf-label form-label">
                    <i class="bx bx-user ci-indigo"></i> ลูกค้า
                  </label>
                  <input id="FullName" type="text" class="form-control"
                    value="{{ $veh->customer?->prefix?->Name_TH ?? '' }} {{ $veh->customer?->FirstName ?? '' }} {{ $lic->saleCarLic?->customer?->LastName ?? '' }}"
                    style="background:#f8fafc;color:#64748b;" disabled>
                </div>

                <div class="col-md-4">
                  <label for="vin_number" class="mf-label form-label">
                    <i class="bx bx-barcode ci-indigo"></i> Vin-Number
                  </label>
                  <input id="vin_number" type="text" class="form-control" value="{{ $veh->carOrder?->vin_number ?? '-' }}"
                    style="background:#f8fafc;color:#64748b;" disabled>
                </div>

                <div class="col-md-3">
                  <label for="engine_number" class="mf-label form-label">
                    <i class="bx bx-hash ci-indigo"></i> เลขถัง
                  </label>
                  <input id="engine_number" type="text" class="form-control" value="{{ $veh->carOrder?->engine_number ?? '-' }}"
                    style="background:#f8fafc;color:#64748b;" disabled>
                </div>

              </div>
            </div>
          </div>

          {{-- Section 2 : ข้อมูลการเงิน --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon amber">
                <i class="bx bx-money"></i>
              </div>
              <span class="mf-section-title">ข้อมูลการเงิน</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-4">
                  <label for="edit_veh_withdrawal_total" class="mf-label form-label">
                    <i class="bx bx-wallet ci-amber"></i> ยอดตั้งเบิก
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="edit_veh_withdrawal_total" name="withdrawal_total" type="text"
                      class="form-control text-end money-input"
                      value="{{ number_format($veh->vehicleLicense?->withdrawal_total ?? 0, 2) }}">
                  </div>
                </div>

                <div class="col-md-4">
                  <label for="edit_veh_receipt_total" class="mf-label form-label">
                    <i class="bx bx-check-circle ci-amber"></i> ยอดเคลียร์
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="edit_veh_receipt_total" name="receipt_total" type="text"
                      class="form-control text-end money-input"
                      value="{{ number_format($veh->vehicleLicense?->receipt_total ?? 0, 2) }}">
                  </div>
                </div>

              </div>
            </div>
          </div>

          {{-- Section 3 : ข้อมูลป้ายทะเบียน --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon emerald">
                <i class="bx bx-id-card"></i>
              </div>
              <span class="mf-section-title">ข้อมูลป้ายทะเบียน</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-3">
                  <label for="edit_veh_withdrawal_date" class="mf-label form-label">
                    <i class="bx bx-calendar-plus ci-emerald"></i> วันที่ตั้งเบิก
                  </label>
                  <input id="edit_veh_withdrawal_date" name="withdrawal_date" type="date" class="form-control"
                    value="{{ $veh->vehicleLicense?->withdrawal_date ?? '' }}">
                </div>

                <div class="col-md-3">
                  <label for="edit_veh_backup_clear_date" class="mf-label form-label">
                    <i class="bx bx-calendar-check ci-emerald"></i> วันที่รับป้ายจากขนส่ง
                  </label>
                  <input id="edit_veh_backup_clear_date" name="backup_clear_date" type="date" class="form-control"
                    value="{{ $veh->vehicleLicense?->backup_clear_date ?? '' }}">
                </div>

                <div class="col-md-3">
                  <label for="license_number" class="mf-label form-label">
                    <i class="bx bx-error-circle ci-emerald"></i> เลขป้ายแดง
                  </label>
                  <input id="license_number" type="text" class="form-control" value="{{ $veh->licensePlateRed?->number ?? '-' }}"
                    style="background:#f8fafc;color:#64748b;" disabled>
                </div>

                <div class="col-md-3">
                  <label for="edit_veh_license_name" class="mf-label form-label">
                    <i class="bx bx-font ci-emerald"></i> ตัวอักษร
                  </label>
                  <input id="edit_veh_license_name" name="license_name" type="text" class="form-control"
                    value="{{ $veh->vehicleLicense?->license_name ?? '' }}"
                    oninput="this.value = this.value.replace(/[^a-zA-Zก-ฮ]/g, '')">
                </div>

                <div class="col-md-3">
                  <label for="edit_veh_license_number" class="mf-label form-label">
                    <i class="bx bx-sort-a-z ci-emerald"></i> ตัวเลข
                  </label>
                  <input id="edit_veh_license_number" name="license_number" type="text" class="form-control"
                    value="{{ $veh->vehicleLicense?->license_number ?? '' }}"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </div>

                @php $selectedProvince = $veh->vehicleLicense?->license_province ?? $veh->provinces?->id; @endphp
                <div class="col-md-5">
                  <label for="edit_veh_license_province" class="mf-label form-label">
                    <i class="bx bx-map ci-emerald"></i> จังหวัด
                  </label>
                  <select id="edit_veh_license_province" name="license_province" class="form-select">
                    <option value="">— เลือกจังหวัด —</option>
                    @foreach ($provincesV as $p)
                      <option value="{{ $p->id }}" {{ $selectedProvince == $p->id ? 'selected' : '' }}>
                        {{ $p->name }}
                      </option>
                    @endforeach
                  </select>
                </div>

              </div>
            </div>
          </div>

          {{-- Actions --}}
          <div class="d-flex justify-content-end gap-2 pt-1">
            <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
              <i class="bx bx-x me-1"></i>ยกเลิก
            </button>
            <button type="button" class="btn btn-primary px-5 btnUpdateVehicle">
              <i class="bx bx-save me-1"></i>บันทึก
            </button>
          </div>

        </form>
      </div>

    </div>
  </div>
</div>
