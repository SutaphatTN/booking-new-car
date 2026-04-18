<div class="modal fade editLicense" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--edit">

      {{-- Header --}}
      <div class="modal-header mf-header mf-header--edit px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-edit fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">แก้ไขข้อมูลป้ายแดง</h6>
            <small class="text-white mf-hd-sub">{{ $lic->licenseLic?->number ?? '-' }}</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">
        <form action="{{ route('vehicle.license.update', $lic->id) }}" method="POST" enctype="multipart/form-data">
          @csrf
          @method('PUT')

          {{-- Section 1 : ป้ายและวันที่ --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon sky">
                <i class="bx bx-id-card"></i>
              </div>
              <span class="mf-section-title">ข้อมูลป้ายและวันที่</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-3">
                  <label for="licenseID" class="mf-label form-label">
                    <i class="bx bx-hash ci-sky"></i> เลขป้ายแดง
                  </label>
                  <input id="licenseID" type="text" class="form-control" name="licenseID"
                    value="{{ $lic->licenseLic?->number ?? '' }}"
                    style="background:#f8fafc;color:#64748b;" disabled>
                </div>

                <div class="col-md-3">
                  <label for="license_full" class="mf-label form-label">
                    <i class="bx bx-card ci-sky"></i> เลขป้ายขาว
                  </label>
                  <input id="license_full" type="text" class="form-control"
                    value="{{ $lic->saleCarLic?->vehicleLicense?->license_name ?? '' }} {{ $lic->saleCarLic?->vehicleLicense?->license_number ?? '' }}"
                    style="background:#f8fafc;color:#64748b;" disabled>
                </div>

                <div class="col-md-3">
                  <label for="delivery_date" class="mf-label form-label">
                    <i class="bx bx-calendar ci-sky"></i> วันที่ส่งมอบ
                  </label>
                  <input id="delivery_date" type="text" class="form-control"
                    value="{{ $lic->saleCarLic?->format_delivery_date ?? '-' }}"
                    style="background:#f8fafc;color:#64748b;" disabled>
                </div>

                <div class="col-md-3">
                  <label for="clear_date" class="mf-label form-label">
                    <i class="bx bx-calendar-check ci-sky"></i> วันที่รับป้ายขาว
                  </label>
                  <input id="clear_date" type="text" class="form-control"
                    value="{{ $lic->saleCarLic?->vehicleLicense?->format_backup_clear_date ?? '-' }}"
                    style="background:#f8fafc;color:#64748b;" disabled>
                </div>

              </div>
            </div>
          </div>

          {{-- Section 2 : ผู้เกี่ยวข้อง --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon indigo">
                <i class="bx bx-user"></i>
              </div>
              <span class="mf-section-title">ผู้เกี่ยวข้อง</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-6">
                  <label for="customer_fullname" class="mf-label form-label">
                    <i class="bx bx-user ci-indigo"></i> ลูกค้า
                  </label>
                  <input id="customer_fullname" type="text" class="form-control"
                    value="{{ $lic->saleCarLic?->customer?->prefix?->Name_TH ?? '' }} {{ $lic->saleCarLic?->customer?->FirstName ?? '' }} {{ $lic->saleCarLic?->customer?->LastName ?? '' }}"
                    style="background:#f8fafc;color:#64748b;" disabled>
                </div>

                <div class="col-md-6">
                  <label for="sale_fullname" class="mf-label form-label">
                    <i class="bx bx-user-check ci-indigo"></i> ฝ่ายขาย
                  </label>
                  <input id="sale_fullname" type="text" class="form-control"
                    value="{{ $lic->saleCarLic?->saleUser?->name ?? '-' }}"
                    style="background:#f8fafc;color:#64748b;" disabled>
                </div>

              </div>
            </div>
          </div>

          {{-- Section 3 : เอกสารป้ายแดง --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon emerald">
                <i class="bx bx-file-blank"></i>
              </div>
              <span class="mf-section-title">เอกสารป้ายแดง</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-2">

                <div class="col-md-4">
                  <label class="border rounded p-2 d-flex align-items-center gap-2 w-100" style="cursor:pointer;font-size:.82rem;">
                    <input type="checkbox" name="license_red_front" value="1"
                      {{ $lic->license_red_front ? 'checked' : '' }}>
                    ป้ายแดงหน้า
                  </label>
                </div>

                <div class="col-md-4">
                  <label class="border rounded p-2 d-flex align-items-center gap-2 w-100" style="cursor:pointer;font-size:.82rem;">
                    <input type="checkbox" name="license_red_back" value="1"
                      {{ $lic->license_red_back ? 'checked' : '' }}>
                    ป้ายแดงหลัง
                  </label>
                </div>

                <div class="col-md-4">
                  <label class="border rounded p-2 d-flex align-items-center gap-2 w-100" style="cursor:pointer;font-size:.82rem;">
                    <input type="checkbox" name="license_red_book" value="1"
                      {{ $lic->license_red_book ? 'checked' : '' }}>
                    สมุดป้ายแดง
                  </label>
                </div>

              </div>
            </div>
          </div>

          {{-- Section 4 : การคืนเงิน --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon amber">
                <i class="bx bx-money"></i>
              </div>
              <span class="mf-section-title">การคืนเงิน</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-3">
                  <label for="cust_refund_date" class="mf-label form-label">
                    <i class="bx bx-calendar ci-amber"></i> วันที่คืนเงินลูกค้า
                  </label>
                  <input id="cust_refund_date" name="cust_refund_date" type="date" class="form-control"
                    value="{{ $lic->cust_refund_date ?? '' }}">
                </div>

                <div class="col-md-3">
                  <label for="refund_amount" class="mf-label form-label">
                    <i class="bx bx-coin ci-amber"></i> ยอดคืนเงิน
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="refund_amount" name="refund_amount" type="text"
                      class="form-control text-end money-input"
                      value="{{ number_format($lic->refund_amount ?? 3000, 2) }}">
                  </div>
                </div>

                <div class="col-md-2">
                  <label for="type_refund" class="mf-label form-label">
                    <i class="bx bx-transfer ci-amber"></i> ประเภท
                  </label>
                  <select id="type_refund" name="type_refund" class="form-select">
                    <option value="">- เลือก -</option>
                    <option value="cash" {{ $lic->type_refund == 'cash' ? 'selected' : '' }}>เงินสด</option>
                    <option value="transfer" {{ $lic->type_refund == 'transfer' ? 'selected' : '' }}>โอน</option>
                  </select>
                </div>

                <div class="col-md-4">
                  <label for="note" class="mf-label form-label">
                    <i class="bx bx-note ci-amber"></i> หมายเหตุ
                  </label>
                  <textarea id="note" class="form-control" name="note" rows="1">{{ $lic->note }}</textarea>
                </div>

              </div>
            </div>
          </div>

          {{-- Actions --}}
          <div class="d-flex justify-content-end gap-2 pt-1">
            <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
              <i class="bx bx-x me-1"></i>ยกเลิก
            </button>
            <button type="button" class="btn btn-primary px-5 btnUpdateLicense">
              <i class="bx bx-save me-1"></i>บันทึก
            </button>
          </div>

        </form>
      </div>

    </div>
  </div>
</div>
