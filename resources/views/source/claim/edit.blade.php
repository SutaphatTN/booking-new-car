@php
  $sharePct = (int) (config('source.claim_share', 0.5) * 100);
  $diff     = $claim?->diff();
@endphp

<div class="modal fade editClaim" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--edit">

      <div class="modal-header mf-header mf-header--edit px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-wallet fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">แก้ไขข้อมูลเงินเคลม</h6>
            <small class="text-white mf-hd-sub">{{ $place->location }}</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">

        {{-- ── การ์ด 1: ข้อมูลสถานที่ (อ่านอย่างเดียว — แก้ที่เมนู "สถานที่") ── --}}
        <div class="mf-section">
          <div class="mf-section-hd">
            <div class="mf-section-icon amber"><i class="bx bx-store"></i></div>
            <span class="mf-section-title">ข้อมูลสถานที่</span>
          </div>
          <div class="mf-section-body">
            <div class="row g-3">
              <div class="col-md-4">
                <div class="po-label"><i class="bx bx-id-card"></i> LAS Number</div>
                <div class="info-pill">{{ $place->las_number ?: '-' }}</div>
              </div>
              <div class="col-md-4">
                <div class="po-label"><i class="bx bx-buildings"></i> สาขา</div>
                <div class="info-pill">{{ $branchName ?: '-' }}</div>
              </div>
              <div class="col-md-4">
                <div class="po-label"><i class="bx bx-map"></i> ระบุสถานที่</div>
                <div class="info-pill">{{ $place->location }}</div>
              </div>
              <div class="col-md-4">
                <div class="po-label"><i class="bx bx-calendar ci-amber"></i> วันเริ่มงาน</div>
                <div class="info-pill">{{ optional($place->start_date)->format('d/m/Y') ?: '-' }}</div>
              </div>
              <div class="col-md-4">
                <div class="po-label"><i class="bx bx-calendar-check ci-amber"></i> วันจบงาน</div>
                <div class="info-pill">{{ optional($place->end_date)->format('d/m/Y') ?: '-' }}</div>
              </div>
            </div>
          </div>
        </div>

        {{-- ── การ์ด 2: ข้อมูลเงินเคลม (กรอกได้) ── --}}
        <form action="{{ route('source.claim.update', $place->id) }}" method="POST">
          @csrf
          @method('PUT')

          <div class="mf-section mt-3">
            <div class="mf-section-hd">
              <div class="mf-section-icon amber"><i class="bx bx-wallet"></i></div>
              <span class="mf-section-title">ข้อมูลเงินเคลม</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-4">
                  <label for="claim_form_b" class="mf-label form-label"><i class="bx bx-file"></i> Form B</label>
                  <input id="claim_form_b" type="text" name="form_b" class="form-control text-end claim-money"
                    autocomplete="off" placeholder="0.00"
                    value="{{ $claim && $claim->form_b !== null ? number_format($claim->form_b, 2) : '' }}"
                    data-share="{{ config('source.claim_share', 0.5) }}">
                </div>

                <div class="col-md-4">
                  <label for="claim_form_b_half" class="mf-label form-label">
                    <i class="bx bx-calculator"></i> Form B ({{ $sharePct }}%)
                  </label>
                  {{-- คำนวณอัตโนมัติจาก Form B — ไม่ได้ส่งไป server (server คิดใหม่เอง) --}}
                  <input id="claim_form_b_half" type="text" class="form-control text-end fw-bold" readonly
                    value="{{ $claim && $claim->form_b_half !== null ? number_format($claim->form_b_half, 2) : '' }}">
                </div>

                <div class="col-md-4">
                  <label for="claim_status" class="mf-label form-label"><i class="bx bx-flag"></i> สถานะ</label>
                  <select id="claim_status" name="status" class="form-select">
                    <option value="">— เลือก —</option>
                    @foreach ($statuses as $key => $st)
                      <option value="{{ $key }}" {{ ($claim->status ?? '') === $key ? 'selected' : '' }}>
                        {{ $st['label'] }}</option>
                    @endforeach
                  </select>
                </div>

                <div class="col-md-4">
                  <label for="claim_actual_amount" class="mf-label form-label">
                    <i class="bx bx-money"></i> ยอดเงินจริงในบัญชี
                  </label>
                  <input id="claim_actual_amount" type="text" name="actual_amount"
                    class="form-control text-end claim-money" autocomplete="off" placeholder="0.00"
                    value="{{ $claim && $claim->actual_amount !== null ? number_format($claim->actual_amount, 2) : '' }}">
                </div>

                <div class="col-md-4">
                  <label for="claim_diff" class="mf-label form-label">
                    <i class="bx bx-transfer"></i> Diff
                  </label>
                  {{-- Form B (50%) − ยอดเงินจริงในบัญชี : บวก = ยังได้ไม่ครบ / ลบ = ได้เกิน --}}
                  <input id="claim_diff" type="text" class="form-control text-end fw-bold" readonly
                    value="{{ $diff === null ? '' : number_format($diff, 2) }}">
                </div>

                <div class="col-md-4">
                  <label for="claim_received_date" class="mf-label form-label">
                    <i class="bx bx-calendar-check ci-amber"></i> วันที่ได้รับเงิน
                  </label>
                  <input id="claim_received_date" type="date" name="received_date" class="form-control"
                    value="{{ optional($claim?->received_date)->format('Y-m-d') }}">
                </div>

                <div class="col-md-4">
                  <label for="claim_check_status" class="mf-label form-label">
                    <i class="bx bx-check-shield"></i> สถานะการตรวจสอบ
                  </label>
                  <select id="claim_check_status" name="check_status" class="form-select">
                    <option value="">— เลือก —</option>
                    @foreach ($checkStatuses as $key => $st)
                      <option value="{{ $key }}" {{ ($claim->check_status ?? '') === $key ? 'selected' : '' }}>
                        {{ $st['label'] }}</option>
                    @endforeach
                  </select>
                </div>

                <div class="col-12">
                  <label for="claim_note" class="mf-label form-label">
                    <i class="bx bx-comment-detail"></i> หมายเหตุ
                  </label>
                  <textarea id="claim_note" name="note" class="form-control" rows="2" maxlength="500"
                    placeholder="ระบุหมายเหตุ...">{{ $claim->note ?? '' }}</textarea>
                </div>

              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2 pt-3">
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
