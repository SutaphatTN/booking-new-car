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
                    value="{{ $lic->licenseLic?->number ?? '' }}" style="background:#f8fafc;color:#64748b;" disabled>
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
                    value="{{ $lic->saleCarLic?->saleUser?->display_name ?? '-' }}"
                    style="background:#f8fafc;color:#64748b;" disabled>
                </div>

              </div>
            </div>
          </div>

          {{-- Section 3 : เอกสารป้ายแดง --}}
          <div class="mf-section">
            <div class="mf-section-hd flex-wrap">
              <div class="mf-section-icon emerald">
                <i class="bx bx-file-blank"></i>
              </div>
              <span class="mf-section-title">เอกสารป้ายแดง</span>

              {{-- คืนครบทุกรายการ = ติ๊กทีเดียวได้ทั้ง 3 ช่อง (เคสลูกค้าคืนของครบ ซึ่งเป็นเคสส่วนใหญ่)
                   ตัวมันเองไม่ได้ถูกส่งไป server — เป็นแค่สวิตช์คุมช่องจริง 3 ช่องด้านล่าง --}}
              <label class="ms-auto d-flex align-items-center gap-2 mb-0"
                style="cursor:pointer;font-size:.82rem;color:#475569;">
                <input type="checkbox" class="licRedDocAll">
                คืนครบทุกรายการ
              </label>
            </div>
            <div class="mf-section-body">
              <div class="row g-2">

                <div class="col-md-4">
                  <label class="border rounded p-2 d-flex align-items-center gap-2 w-100"
                    style="cursor:pointer;font-size:.82rem;">
                    <input type="checkbox" class="licRedDoc" name="license_red_front" value="1"
                      {{ $lic->license_red_front ? 'checked' : '' }}>
                    ป้ายแดงหน้า
                  </label>
                </div>

                <div class="col-md-4">
                  <label class="border rounded p-2 d-flex align-items-center gap-2 w-100"
                    style="cursor:pointer;font-size:.82rem;">
                    <input type="checkbox" class="licRedDoc" name="license_red_back" value="1"
                      {{ $lic->license_red_back ? 'checked' : '' }}>
                    ป้ายแดงหลัง
                  </label>
                </div>

                <div class="col-md-4">
                  <label class="border rounded p-2 d-flex align-items-center gap-2 w-100"
                    style="cursor:pointer;font-size:.82rem;">
                    <input type="checkbox" class="licRedDoc" name="license_red_book" value="1"
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

                {{-- วันที่ลูกค้าจ่ายเงินค่าป้ายแดง — เก็บอยู่บนใบขาย (salecars.red_license_pay_date)
                     ไม่ใช่บน license_plate_history ; แก้จากหน้านี้ได้ คอนโทรลเลอร์เขียนกลับไปที่ใบขายให้
                     ใบที่ยังไม่ผูกใบขาย (ไม่มี saleCarLic) ช่องนี้จะกรอกไม่ได้ --}}
                <div class="col-md-3">
                  <label for="red_license_pay_date" class="mf-label form-label">
                    <i class="bx bx-calendar-check ci-amber"></i> วันที่ลูกค้าจ่ายเงิน
                  </label>
                  <input id="red_license_pay_date" name="red_license_pay_date" type="date" class="form-control"
                    value="{{ $lic->saleCarLic?->red_license_pay_date ? \Illuminate\Support\Carbon::parse($lic->saleCarLic->red_license_pay_date)->format('Y-m-d') : '' }}"
                    {{ $lic->saleCarLic ? '' : 'disabled' }}>
                  <div class="form-text">ค่าป้ายแดงที่ลูกค้าจ่าย</div>
                </div>

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

                <div class="col-md-3">
                  <label for="type_refund" class="mf-label form-label">
                    <i class="bx bx-transfer ci-amber"></i> ประเภท
                  </label>
                  <select id="type_refund" name="type_refund" class="form-select">
                    <option value="">- เลือก -</option>
                    <option value="cash" {{ $lic->type_refund == 'cash' ? 'selected' : '' }}>เงินสด</option>
                    <option value="transfer" {{ $lic->type_refund == 'transfer' ? 'selected' : '' }}>โอน</option>
                  </select>
                </div>

                {{-- หมายเหตุ — ขึ้นก่อนบล็อกไฟล์แนบ (การ์ดไฟล์สูงไม่เท่ากัน ถ้าอยู่บนจะดันช่องนี้เบี้ยว)
                     title อยู่ที่ตัว label ทั้งอัน ไม่ใช่แค่ไอคอน + ใส่ placeholder ให้เห็นโดยไม่ต้องเอาเมาส์ไปชี้ --}}
                <div class="col-md-12">
                  <label for="note" class="mf-label form-label" title="เลขที่ EMS และวันที่ส่ง">
                    <i class="bx bx-note ci-amber"></i> หมายเหตุ
                    <span class="mf-label-note ms-1">(เลขที่ EMS และวันที่ส่ง)</span>
                  </label>
                  <textarea id="note" class="form-control" name="note" rows="2"
                    placeholder="เลขที่ EMS และวันที่ส่ง">{{ $lic->note }}</textarea>
                </div>

                {{-- หลักฐานการโอนเงินค่าป้ายแดง — ไฟล์ของ "ใบขาย" (salecars.red_license_slip_url)
                     ไฟล์ชุดเดียวกับที่หน้าใบจอง/หน้าประวัติเห็น แนบเพิ่มจากหน้านี้ได้ ขึ้น OneDrive
                     โฟลเดอร์เดียวกัน : New Car/{แบรนด์}/ป้ายแดง/หลักฐานลูกค้าโอนเงิน/{id-ชื่อลูกค้า}
                     ปุ่มลบใช้ endpoint ของใบขาย จึงโชว์เฉพาะ role ที่จัดการป้ายแดงได้ (RED_PLATE_ROLES)
                     ใบที่ยังไม่ผูกใบขาย แนบไม่ได้ (ไม่รู้จะเก็บไปไว้ที่ใบไหน) --}}
                @php
                  $payslips = is_array($lic->saleCarLic?->red_license_slip_url)
                      ? $lic->saleCarLic->red_license_slip_url
                      : [];
                  $canDelPaySlip = auth()->user()->canManageRedPlate();
                @endphp
                <div class="col-md-6">
                  <label for="red_license_slips" class="mf-label form-label mb-1">
                    <i class="bx bx-receipt ci-emerald"></i> หลักฐานการโอนเงิน (ค่าป้ายแดง)
                  </label>

                  @if ($payslips)
                    <div class="d-flex flex-wrap mb-2" id="paySlipList">
                      @include('_partials.file-cards', [
                          'files' => $payslips,
                          'proxyBase' => route('purchase-order.proxy', $lic->saleCarLic->id),
                          'deleteUrl' => $canDelPaySlip
                              ? route('purchase-order.red-plate.delete-slip', $lic->saleCarLic->id)
                              : null,
                      ])
                    </div>
                  @endif

                  @if ($lic->saleCarLic)
                    <input type="file" id="red_license_slips" name="red_license_slips[]" class="form-control"
                      accept=".pdf,.jpg,.jpeg,.png" multiple>
                    <div class="form-text">
                      ไฟล์ชุดเดียวกับหน้าใบจอง — แนบเพิ่มได้ ไฟล์เดิมไม่ถูกลบทิ้ง
                    </div>
                    <div id="paySlipPreview" class="d-flex flex-wrap mt-1"></div>
                  @else
                    <div class="text-muted" style="font-size:.82rem;">
                      <i class="bx bx-info-circle me-1"></i>ใบนี้ยังไม่ได้ผูกใบขาย จึงแนบไฟล์ไม่ได้
                    </div>
                  @endif
                </div>

                {{-- สลิปคืนเงินลูกค้า — ไฟล์ของหน้านี้เอง เก็บที่ license_plate_history.refund_slip_url
                     ขึ้น OneDrive : New Car/{แบรนด์}/ป้ายแดง/หลักฐานคืนเงินลูกค้า/{id-ชื่อลูกค้า} --}}
                <div class="col-md-6">
                  <label for="refund_slips" class="mf-label form-label mb-1">
                    <i class="bx bx-money-withdraw ci-amber"></i> สลิปคืนเงินลูกค้า
                  </label>
                  @php $refundSlips = is_array($lic->refund_slip_url) ? $lic->refund_slip_url : []; @endphp
                  @if ($refundSlips)
                    <div class="d-flex flex-wrap mb-2" id="refundSlipList">
                      @include('_partials.file-cards', [
                          'files' => $refundSlips,
                          'proxyBase' => route('vehicle.license.slip-proxy', $lic->id),
                          'deleteUrl' => route('vehicle.license.delete-refund-slip', $lic->id),
                      ])
                    </div>
                  @endif
                  <input type="file" id="refund_slips" name="refund_slips[]" class="form-control"
                    accept=".pdf,.jpg,.jpeg,.png" multiple>
                  <div class="form-text">รองรับ PDF, JPG, PNG — แนบได้หลายไฟล์ ไฟล์เดิมไม่ถูกลบทิ้ง</div>
                  {{-- พรีวิวไฟล์ที่เพิ่งเลือก (ยังไม่อัปโหลดจนกว่าจะกดบันทึก) --}}
                  <div id="refundSlipPreview" class="d-flex flex-wrap mt-1"></div>
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
