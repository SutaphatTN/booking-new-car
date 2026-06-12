@php
  $claim = $sc->claim;
  $cus = $sc->saleCar?->customer;
  $customer = $cus ? trim(($cus->FirstName ?? '') . ' ' . ($cus->LastName ?? '')) : '-';
  $customer = $customer !== '' ? $customer : '-';
  $used = (float) ($sc->CashSupportFinal ?? 0);
@endphp
<div class="modal fade editClaim" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--input">

      {{-- Header --}}
      <div class="modal-header mf-header mf-header--input px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-receipt fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">ข้อมูลการเคลมแคมเปญ</h6>
            <small class="text-white mf-hd-sub">Campaign Claim</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">
        <form action="{{ route('campaign.claim.update', $sc->id) }}" method="POST">
          @csrf

          {{-- Section 1 : ข้อมูลแคมเปญ (อ่านอย่างเดียว) --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon indigo">
                <i class="bx bx-purchase-tag"></i>
              </div>
              <span class="mf-section-title">ข้อมูลแคมเปญ</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="mf-label form-label"><i class="bx bx-user"></i> ลูกค้า</label>
                  <input type="text" class="form-control" value="{{ $customer }}" readonly>
                </div>
                <div class="col-md-6">
                  <label class="mf-label form-label"><i class="bx bx-list-ul"></i> ประเภทแคมเปญ</label>
                  <input type="text" class="form-control" value="{{ $sc->campaignType?->name ?? '-' }}" readonly>
                </div>
                <div class="col-md-4">
                  <label class="mf-label form-label"><i class="bx bx-calendar"></i> วันที่ส่งมอบ</label>
                  <input type="text" class="form-control" value="{{ $sc->saleCar?->format_delivery_date ?? '-' }}" readonly>
                </div>
                <div class="col-md-4">
                  <label class="mf-label form-label"><i class="bx bx-barcode"></i> Vin Number</label>
                  <input type="text" class="form-control" value="{{ $sc->saleCar?->carOrder?->vin_number ?? '-' }}" readonly>
                </div>
                <div class="col-md-4">
                  <label class="mf-label form-label"><i class="bx bx-wallet"></i> ยอดแคมเปญที่ใช้</label>
                  <div class="input-group">
                    <span class="input-group-text ig-indigo">฿</span>
                    <input id="claim_used" type="text" class="form-control text-end"
                      value="{{ number_format($used, 2) }}" data-raw="{{ $used }}" readonly>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- Section 2 : ข้อมูลการเคลม --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon amber">
                <i class="bx bx-money"></i>
              </div>
              <span class="mf-section-title">ข้อมูลการเคลม</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-4">
                  <label for="claim_amount" class="mf-label form-label">
                    <i class="bx bx-wallet ci-amber"></i> ยอดรับเคลม
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="claim_amount" type="text" class="form-control text-end money-input claim-amount"
                      name="claim_amount" placeholder="0.00"
                      value="{{ $claim && $claim->claim_amount !== null ? number_format($claim->claim_amount, 2) : '' }}">
                  </div>
                </div>

                <div class="col-md-4">
                  <label for="claim_diff" class="mf-label form-label">
                    <i class="bx bx-transfer ci-amber"></i> ยอด Diff
                    <span class="mf-label-note">(คำนวณอัตโนมัติ)</span>
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-slate">฿</span>
                    <input id="claim_diff" type="text" class="form-control text-end" placeholder="0.00" readonly>
                  </div>
                </div>

                <div class="col-md-4">
                  <label for="received_date" class="mf-label form-label">
                    <i class="bx bx-calendar-check ci-amber"></i> วันที่รับเงิน
                  </label>
                  <input id="received_date" type="date" class="form-control" name="received_date"
                    value="{{ $claim?->received_date }}">
                </div>

                <div class="col-md-12">
                  <label for="status_id" class="mf-label form-label">
                    <i class="bx bx-check-shield ci-amber"></i> สรุปผลการตรวจสอบ
                  </label>
                  <select id="status_id" name="status_id" class="form-select">
                    <option value="">— เลือกสถานะ —</option>
                    @foreach ($status as $s)
                      <option value="{{ $s->id }}" {{ $claim && $claim->status_id == $s->id ? 'selected' : '' }}>
                        {{ $s->name }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div class="col-md-12">
                  <label for="note" class="mf-label form-label">
                    <i class="bx bx-note ci-amber"></i> หมายเหตุ
                  </label>
                  <textarea id="note" name="note" class="form-control" rows="2"
                    placeholder="หมายเหตุเพิ่มเติม">{{ $claim?->note }}</textarea>
                </div>

              </div>
            </div>
          </div>

          {{-- Actions --}}
          <div class="d-flex justify-content-end gap-2 pt-1">
            <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
              <i class="bx bx-x me-1"></i>ยกเลิก
            </button>
            <button type="button" class="btn btn-primary px-5 btnUpdateClaim">
              <i class="bx bx-save me-1"></i>บันทึก
            </button>
          </div>

        </form>
      </div>

    </div>
  </div>
</div>
