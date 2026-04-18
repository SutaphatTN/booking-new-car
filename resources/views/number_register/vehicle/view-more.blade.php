<div class="modal fade viewVehicle" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--view">

      {{-- Header --}}
      <div class="modal-header mf-header mf-header--view px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-info-circle fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">ข้อมูลป้ายทะเบียน</h6>
            <small class="text-white mf-hd-sub">Vehicle License Detail</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">

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
                  disabled>
              </div>

              <div class="col-md-4">
                <label for="vin_number" class="mf-label form-label">
                  <i class="bx bx-barcode ci-indigo"></i> Vin-Number
                </label>
                <input id="vin_number" type="text" class="form-control" value="{{ $veh->carOrder?->vin_number ?? '-' }}" disabled>
              </div>

              <div class="col-md-3">
                <label for="engine_number" class="mf-label form-label">
                  <i class="bx bx-hash ci-indigo"></i> เลขถัง
                </label>
                <input id="engine_number" type="text" class="form-control" value="{{ $veh->carOrder?->engine_number ?? '-' }}" disabled>
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
                <label for="withdrawal_total" class="mf-label form-label">
                  <i class="bx bx-wallet ci-amber"></i> ยอดตั้งเบิก
                </label>
                <div class="input-group">
                  <span class="input-group-text ig-amber">฿</span>
                  <input id="withdrawal_total" type="text" class="form-control text-end"
                    value="{{ number_format($veh->vehicleLicense?->withdrawal_total ?? 0, 2) }}" disabled>
                </div>
              </div>

              <div class="col-md-4">
                <label for="receipt_total" class="mf-label form-label">
                  <i class="bx bx-check-circle ci-amber"></i> ยอดเคลียร์
                </label>
                <div class="input-group">
                  <span class="input-group-text ig-amber">฿</span>
                  <input id="receipt_total" type="text" class="form-control text-end"
                    value="{{ number_format($veh->vehicleLicense?->receipt_total ?? 0, 2) }}" disabled>
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
                <label for="withdrawal_date" class="mf-label form-label">
                  <i class="bx bx-calendar-plus ci-emerald"></i> วันที่ตั้งเบิก
                </label>
                <input id="withdrawal_date" type="text" class="form-control"
                  value="{{ $veh->vehicleLicense?->format_withdrawal_date ?? '' }}" disabled>
              </div>

              <div class="col-md-3">
                <label for="backup_clear_date" class="mf-label form-label">
                  <i class="bx bx-calendar-check ci-emerald"></i> วันที่รับป้ายจากขนส่ง
                </label>
                <input id="backup_clear_date" type="text" class="form-control"
                  value="{{ $veh->vehicleLicense?->format_backup_clear_date ?? '-' }}" disabled>
              </div>

              <div class="col-md-3">
                <label for="number" class="mf-label form-label">
                  <i class="bx bx-error-circle ci-emerald"></i> เลขป้ายแดง
                </label>
                <input id="number" type="text" class="form-control" value="{{ $veh->licensePlateRed?->number ?? '-' }}" disabled>
              </div>

              <div class="col-md-3">
                <label for="license_name" class="mf-label form-label">
                  <i class="bx bx-font ci-emerald"></i> ตัวอักษร
                </label>
                <input id="license_name" type="text" class="form-control" value="{{ $veh->vehicleLicense?->license_name ?? '' }}"
                  disabled>
              </div>

              <div class="col-md-3">
                <label for="license_number" class="mf-label form-label">
                  <i class="bx bx-sort-a-z ci-emerald"></i> ตัวเลข
                </label>
                <input id="license_number" type="text" class="form-control" value="{{ $veh->vehicleLicense?->license_number ?? '' }}"
                  disabled>
              </div>

              <div class="col-md-5">
                <label for="province_name" class="mf-label form-label">
                  <i class="bx bx-map ci-emerald"></i> จังหวัด
                </label>
                <input id="province_name" type="text" class="form-control"
                  value="{{ $veh->vehicleLicense?->provincesV?->name ?? '' }}" disabled>
              </div>

            </div>
          </div>
        </div>

        {{-- Actions --}}
        {{-- <div class="d-flex justify-content-end pt-1">
          <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
            <i class="bx bx-x me-1"></i>ปิด
          </button>
        </div> --}}

      </div>

    </div>
  </div>
</div>
