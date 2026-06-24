<div class="modal fade viewLicense" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--view">

      {{-- Header --}}
      <div class="modal-header mf-header mf-header--view px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-id-card fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">ข้อมูลป้ายแดง</h6>
            <small class="text-white mf-hd-sub">{{ $lic->licenseLic?->number ?? '-' }}</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">

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
                <input id="licenseID" type="text" class="form-control"
                  value="{{ $lic->licenseLic?->number ?? '-' }}"
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

              <div class="col-md-5">
                <label for="province_name" class="mf-label form-label">
                  <i class="bx bx-map ci-sky"></i> จังหวัดป้ายทะเบียน
                </label>
                <input id="province_name" type="text" class="form-control"
                  value="{{ $lic->saleCarLic?->vehicleLicense?->provincesV?->name ?? '-' }}"
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
                    {{ $lic->license_red_front ? 'checked' : '' }} disabled>
                  ป้ายแดงหน้า
                </label>
              </div>

              <div class="col-md-4">
                <label class="border rounded p-2 d-flex align-items-center gap-2 w-100" style="cursor:pointer;font-size:.82rem;">
                  <input type="checkbox" name="license_red_back" value="1"
                    {{ $lic->license_red_back ? 'checked' : '' }} disabled>
                  ป้ายแดงหลัง
                </label>
              </div>

              <div class="col-md-4">
                <label class="border rounded p-2 d-flex align-items-center gap-2 w-100" style="cursor:pointer;font-size:.82rem;">
                  <input type="checkbox" name="license_red_book" value="1"
                    {{ $lic->license_red_book ? 'checked' : '' }} disabled>
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

              <div class="col-md-4">
                <label for="cust_refund_date" class="mf-label form-label">
                  <i class="bx bx-calendar ci-amber"></i> วันที่คืนเงินลูกค้า
                </label>
                <input id="cust_refund_date" type="text" class="form-control"
                  value="{{ $lic->format_cust_refund_date ?? '-' }}"
                  style="background:#f8fafc;color:#64748b;" disabled>
              </div>

              <div class="col-md-4">
                <label for="refund_amount" class="mf-label form-label">
                  <i class="bx bx-coin ci-amber"></i> ยอดคืนเงิน
                </label>
                <div class="input-group">
                  <span class="input-group-text ig-amber">฿</span>
                  <input id="refund_amount" type="text" class="form-control text-end"
                    value="{{ number_format($lic->refund_amount, 2) ?? '' }}" disabled>
                </div>
              </div>

              @php
                $statusType = ['cash' => 'เงินสด', 'transfer' => 'โอน'];
              @endphp
              <div class="col-md-4">
                <label for="type_refund" class="mf-label form-label">
                  <i class="bx bx-transfer ci-amber"></i> ประเภท
                </label>
                <input id="type_refund" type="text" class="form-control"
                  value="{{ $statusType[$lic->type_refund] ?? '-' }}"
                  style="background:#f8fafc;color:#64748b;" disabled>
              </div>

              <div class="col-md-12">
                <label for="note" class="mf-label form-label">
                  <i class="bx bx-note ci-amber"></i> หมายเหตุ
                </label>
                <textarea id="note" class="form-control" rows="2" disabled>{{ $lic->note }}</textarea>
              </div>

            </div>
          </div>
        </div>

        {{-- Section 5 : ข้อมูลมัดจำป้าย (accessory ที่เลือกจากหน้าการจอง ตาม id ที่กำหนด) --}}
        @php
          $depositIds = config('vehicle.plate_deposit_accessory_ids', []);
          $deposits = $lic->saleCarLic?->accessories?->whereIn('id', $depositIds) ?? collect();
        @endphp
        <div class="mf-section">
          <div class="mf-section-hd">
            <div class="mf-section-icon emerald">
              <i class="bx bx-id-card"></i>
            </div>
            <span class="mf-section-title">ข้อมูลมัดจำป้าย</span>
          </div>
          <div class="mf-section-body">
            @if ($deposits->count() > 0)
              <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                  <thead>
                    <tr style="font-size:.8rem;color:#64748b;">
                      <th class="text-center" style="width:48px;">#</th>
                      <th>รหัส</th>
                      <th>รายการ</th>
                      <th class="text-end">ยอดที่ใช้</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($deposits as $a)
                      <tr style="font-size:.85rem;color:#374151;">
                        <td class="text-center text-muted">{{ $loop->index + 1 }}</td>
                        <td>{{ $a->id }}</td>
                        <td>{{ $a->detail }}</td>
                        <td class="text-end">{{ number_format((float) $a->pivot->price, 2) }} ฿</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @else
              <div class="text-muted small text-center py-2">— ลูกค้าไม่รับป้าย —</div>
            @endif
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
