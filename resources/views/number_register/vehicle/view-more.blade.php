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

        @php
          // ลูกค้าไปจดทะเบียนเอง — ไม่มียอดเบิก/เคลียร์ให้ดู ซ่อนส่วนการเงินทิ้งทั้งก้อน
          $selfRegistered = $veh->vehicleLicense?->isSelfRegistered() ?? false;
        @endphp

        @if ($selfRegistered)
          <div class="alert alert-info d-flex align-items-center gap-2 py-2 mb-3" style="font-size:.85rem;">
            <i class="bx bx-user-check fs-5"></i>
            <div>
              <span class="fw-semibold">ลูกค้าไปจดทะเบียนเอง</span> — ไม่มีรายการส่งเบิก/เคลียร์
              @if ($veh->vehicleLicense?->reg_by_marked_at)
                <span class="text-muted">(บันทึกเมื่อ {{ \Illuminate\Support\Carbon::parse($veh->vehicleLicense->reg_by_marked_at)->format('d-m-Y H:i') }})</span>
              @endif
            </div>
          </div>
        @endif

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
                  value="{{ $veh->customer?->prefix?->Name_TH ?? '' }} {{ $veh->customer?->FirstName ?? '' }} {{ $veh->customer?->LastName ?? '' }}"
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
                  <i class="bx bx-hash ci-indigo"></i> เลขเครื่อง
                </label>
                <input id="engine_number" type="text" class="form-control" value="{{ $veh->carOrder?->engine_number ?? '-' }}" disabled>
              </div>

            </div>
          </div>
        </div>

        {{-- Section 2 : ข้อมูลการเงิน (เคสลูกค้าจดเองไม่มียอดเบิก/เคลียร์ ไม่ต้องโชว์) --}}
        @unless ($selfRegistered)
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

              {{-- ยอด "อื่นๆ" ที่รวมอยู่ในยอดตั้งเบิก/ยอดเคลียร์ข้างบน พร้อมหมายเหตุกำกับว่าเป็นค่าอะไร
                   โชว์เฉพาะใบที่มียอดจริง ใบที่ไม่มีจะได้ไม่มีช่องว่างเปล่ารก --}}
              @php $vl = $veh->vehicleLicense; @endphp

              @if ((float) ($vl?->withdrawal_other ?? 0) > 0)
                <div class="col-md-4">
                  <label for="withdrawal_other" class="mf-label form-label">
                    <i class="bx bx-plus-circle ci-amber"></i> อื่นๆ (ตั้งเบิก)
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="withdrawal_other" type="text" class="form-control text-end"
                      value="{{ number_format($vl->withdrawal_other, 2) }}" disabled>
                  </div>
                </div>
                <div class="col-md-8">
                  <label for="withdrawal_other_note" class="mf-label form-label">
                    <i class="bx bx-note ci-amber"></i> หมายเหตุ (อื่นๆ ตั้งเบิก)
                  </label>
                  <input id="withdrawal_other_note" type="text" class="form-control"
                    value="{{ $vl->withdrawal_other_note }}" disabled>
                </div>
              @endif

              @if ((float) ($vl?->receipt_other ?? 0) > 0)
                <div class="col-md-4">
                  <label for="receipt_other" class="mf-label form-label">
                    <i class="bx bx-plus-circle ci-amber"></i> อื่นๆ (เคลียร์)
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="receipt_other" type="text" class="form-control text-end"
                      value="{{ number_format($vl->receipt_other, 2) }}" disabled>
                  </div>
                </div>
                <div class="col-md-8">
                  <label for="receipt_other_note" class="mf-label form-label">
                    <i class="bx bx-note ci-amber"></i> หมายเหตุ (อื่นๆ เคลียร์)
                  </label>
                  <input id="receipt_other_note" type="text" class="form-control"
                    value="{{ $vl->receipt_other_note }}" disabled>
                </div>
              @endif

            </div>
          </div>
        </div>

        @endunless

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

              {{-- ลูกค้าจดเอง : ไม่มีวันตั้งเบิก/วันรับป้ายจากขนส่ง ไม่ต้องโชว์ช่องวันที่เลย --}}
              @unless ($selfRegistered)
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
              @endunless

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

        {{-- Section 4 : ไฟล์แนบ (ดูอย่างเดียว — ลบ/เพิ่มทำที่โมดัลแก้ไข) --}}
        @php $regFiles = is_array($veh->vehicleLicense?->attachment_url) ? $veh->vehicleLicense->attachment_url : []; @endphp
        <div class="mf-section">
          <div class="mf-section-hd">
            <div class="mf-section-icon sky">
              <i class="bx bx-paperclip"></i>
            </div>
            <span class="mf-section-title">ไฟล์แนบ</span>
          </div>
          <div class="mf-section-body">
            @if ($regFiles)
              <div class="d-flex flex-wrap">
                @include('_partials.file-cards', [
                    'files' => $regFiles,
                    'proxyBase' => route('vehicle.attachment-proxy', $veh->id),
                    'readonly' => true,
                ])
              </div>
            @else
              <div class="text-muted small text-center py-2">— ไม่มีไฟล์แนบ —</div>
            @endif
          </div>
        </div>

        {{-- Section 5 : รายการเกี่ยวกับทะเบียน (accessory ที่ is_registration) --}}
        @php
          $regAccs = $veh->accessories?->where('is_registration', true) ?? collect();
          $regTypeLabel = ['gift' => 'แถม', 'extra' => 'ซื้อเพิ่ม'];
        @endphp
        <div class="mf-section">
          <div class="mf-section-hd">
            <div class="mf-section-icon indigo">
              <i class="bx bx-id-card"></i>
            </div>
            <span class="mf-section-title">รายการเกี่ยวกับทะเบียน</span>
          </div>
          <div class="mf-section-body">
            @if ($regAccs->count() > 0)
              <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                  <thead>
                    <tr style="font-size:.8rem;color:#64748b;">
                      <th class="text-center" style="width:48px;">#</th>
                      <th>รหัส</th>
                      <th>รายการ</th>
                      <th>ประเภท</th>
                      <th class="text-end">ราคา</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($regAccs as $a)
                      <tr style="font-size:.85rem;color:#374151;">
                        <td class="text-center text-muted">{{ $loop->index + 1 }}</td>
                        <td>{{ $a->accessory_id }}</td>
                        <td>{{ $a->detail }}</td>
                        <td>{{ $regTypeLabel[$a->pivot->type] ?? $a->pivot->type }}</td>
                        <td class="text-end">{{ number_format($a->pivot->price, 2) }} ฿</td>
                      </tr>
                    @endforeach
                  </tbody>
                  <tfoot>
                    <tr style="font-size:.85rem;font-weight:600;background:#eef4ff;">
                      <td colspan="4" class="text-end">รวม</td>
                      <td class="text-end">{{ number_format($regAccs->sum(fn($a) => (float) $a->pivot->price), 2) }} ฿</td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            @else
              <div class="text-muted small text-center py-2">— ไม่มีรายการเกี่ยวกับทะเบียนในการจองนี้ —</div>
            @endif
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
