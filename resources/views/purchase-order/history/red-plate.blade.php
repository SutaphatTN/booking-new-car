<div class="modal fade redPlateModal" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--edit">

      {{-- Header --}}
      <div class="modal-header mf-header mf-header--edit px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-purchase-tag fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">ป้ายแดง</h6>
            <small class="text-white mf-hd-sub">{{ $saleCar->carOrder->vin_number ?? 'ยังไม่ได้ผูกรถ' }}</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">
        <input type="hidden" id="rp_sale_id" value="{{ $saleCar->id }}">

        <div class="mf-section">
          <div class="mf-section-hd">
            <div class="mf-section-icon rose"><i class="bx bx-id-card"></i></div>
            <span class="mf-section-title">กำหนดป้ายแดง</span>
          </div>
          <div class="mf-section-body">

            {{-- ป้ายปัจจุบัน — เคสได้ป้ายมาทีหลังส่งมอบจะยังว่างอยู่ --}}
            <div class="mb-3">
              <div class="mf-label form-label mb-1">
                <i class="bx bx-info-circle ci-indigo"></i> ป้ายแดงปัจจุบัน
              </div>
              @if ($saleCar->licensePlateRed)
                <span class="badge bg-label-danger" style="font-size:.85rem;">
                  <i class="bx bx-purchase-tag me-1"></i>{{ $saleCar->licensePlateRed->number }}
                </span>
              @else
                <span class="badge bg-label-secondary" style="font-size:.85rem;">ยังไม่มีป้ายแดง</span>
              @endif
            </div>

            <div class="row g-3">
              <div class="col-md-6">
                <label for="rp_red_license" class="mf-label form-label">
                  <i class="bx bx-purchase-tag ci-rose"></i> เลือกป้ายแดง
                </label>
                <select id="rp_red_license" class="form-select">
                  <option value="">— ไม่ระบุ (นำป้ายออก) —</option>
                  @foreach ($licensePlateRed as $r)
                    <option value="{{ $r->id }}" {{ $saleCar->red_license == $r->id ? 'selected' : '' }}>
                      {{ $r->number }}
                    </option>
                  @endforeach
                </select>
                <div class="form-text">
                  แสดงเฉพาะป้ายที่ยังว่างของแบรนด์ตัวเอง ป้ายที่ยืมมา และป้ายที่ใบนี้ถืออยู่
                </div>
              </div>

              {{-- วันที่ลูกค้าจ่ายเงินค่าป้ายแดง — บังคับเมื่อมีป้าย ; เปลี่ยนเลขป้ายทีหลังจะเห็นวันที่เคยกรอกไว้ --}}
              <div class="col-md-6">
                <label for="rp_pay_date" class="mf-label form-label">
                  <i class="bx bx-calendar-check ci-emerald"></i> วันที่ลูกค้าจ่ายเงิน
                  <span class="text-danger">*</span>
                </label>
                <input type="date" id="rp_pay_date" class="form-control"
                  value="{{ $saleCar->red_license_pay_date ? \Illuminate\Support\Carbon::parse($saleCar->red_license_pay_date)->format('Y-m-d') : '' }}">
                <div class="form-text">
                  ค่าป้ายแดงที่ลูกค้าจ่าย — เลือกป้ายแดงแล้วต้องระบุวันที่ด้วย
                </div>
              </div>
            </div>

            {{-- หลักฐานการโอนเงินค่าป้ายแดง — ไฟล์ขึ้น OneDrive
                 New Car/{แบรนด์}/ป้ายแดง/หลักฐานลูกค้าโอนเงิน/{id-ชื่อลูกค้า}
                 บังคับให้มีอย่างน้อย 1 ไฟล์เมื่อมีป้ายแดง ; ไฟล์เดิมนับด้วย ไม่ต้องแนบใหม่ทุกครั้ง --}}
            @php
              $rpSlips = is_array($saleCar->red_license_slip_url) ? $saleCar->red_license_slip_url : [];
              $rpProxyBase = route('purchase-order.proxy', $saleCar->id);
            @endphp

            <label for="rp_slips" class="mf-label form-label mt-3">
              <i class="bx bx-receipt ci-indigo"></i> หลักฐานการโอนเงิน (ค่าป้ายแดง)
              <span class="text-danger">*</span>
            </label>

            @if ($rpSlips)
              <div class="d-flex flex-wrap mb-2" id="rpSlipList">
                @include('_partials.file-cards', [
                    'files' => $rpSlips,
                    'proxyBase' => $rpProxyBase,
                    'deleteUrl' => route('purchase-order.red-plate.delete-slip', $saleCar->id),
                ])
              </div>
            @endif

            <input type="hidden" id="rp_slip_count" value="{{ count($rpSlips) }}">
            <input type="file" id="rp_slips" class="form-control" accept=".pdf,.jpg,.jpeg,.png" multiple>
            <div class="form-text">
              รองรับ PDF, JPG, PNG — แนบได้หลายไฟล์ ไฟล์ที่แนบไว้แล้วจะไม่ถูกลบทิ้ง
            </div>
            {{-- พรีวิวไฟล์ที่เพิ่งเลือก (ยังไม่อัปโหลด) — กดกากบาทเอาออกทีละไฟล์ได้ --}}
            <div id="rp_slip_preview" class="d-flex flex-wrap mt-1"></div>

          </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
          <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
            <i class="bx bx-x me-1"></i>ยกเลิก
          </button>
          <button type="button" class="btn btn-primary px-5 btnSaveRedPlate">
            <i class="bx bx-save me-1"></i>บันทึก
          </button>
        </div>

      </div>
    </div>
  </div>
</div>
