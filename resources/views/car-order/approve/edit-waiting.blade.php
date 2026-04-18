<div class="modal fade editApproveWaitingOrder" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--edit">

      {{-- Header --}}
      <div class="modal-header mf-header mf-header--edit px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-check-shield fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">
              ผลการอนุมัติ :
              @if ($waiting->status === 'approved')
                <span class="badge bg-success ms-1">อนุมัติ</span>
              @else
                <span class="badge bg-danger ms-1">ไม่อนุมัติ</span>
              @endif
            </h6>
            <small class="text-white mf-hd-sub">{{ $waiting->order_code }}</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">
        <form id="approveWaitingForm" action="{{ route('car-order.updateApproveWaiting', $waiting->id) }}"
          method="POST" enctype="multipart/form-data">
          @csrf
          @method('PUT')

          {{-- Section 0 : ผลการอนุมัติ --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon {{ $waiting->status === 'approved' ? 'emerald' : 'rose' }}">
                <i class="bx {{ $waiting->status === 'approved' ? 'bx-check-circle' : 'bx-x-circle' }}"></i>
              </div>
              <span class="mf-section-title">
                {{ $waiting->status === 'approved' ? 'ข้อมูลการอนุมัติ' : 'เหตุผลที่ไม่ผ่านการอนุมัติ' }}
              </span>
            </div>
            <div class="mf-section-body">
              @if ($waiting->status === 'approved')
                <div class="row g-3">

                  <div class="col-md-4">
                    <label for="system_date" class="mf-label form-label">
                      <i class="bx bx-calendar-check ci-emerald"></i> วันที่สั่งซื้อในระบบ
                    </label>
                    <input id="system_date" type="date" class="form-control" name="system_date"
                      value="{{ $waiting->system_date }}" required>
                  </div>

                  <div class="col-md-4">
                    <label for="count_order" class="mf-label form-label">
                      <i class="bx bx-hash ci-emerald"></i> จำนวนที่สั่ง (คัน)
                    </label>
                    <input id="count_order" type="text" class="form-control form-control-plaintext-mf" value="{{ $waiting->count_order }}" disabled>
                  </div>

                  <div class="col-md-4">
                    <label for="received_order" class="mf-label form-label">
                      <i class="bx bx-check-double ci-emerald"></i> ได้รับจริง (คัน)
                    </label>
                    <input id="received_order" type="text" class="form-control form-control-plaintext-mf" value="{{ $waiting->received_order }}" disabled>
                  </div>

                </div>
              @else
                <label for="reason" class="mf-label form-label">
                  <i class="bx bx-comment-x ci-rose"></i> เหตุผลที่ไม่ผ่านการอนุมัติ
                </label>
                <textarea id="reason" class="form-control" rows="2" disabled>{{ $waiting->reason ?? '-' }}</textarea>
              @endif
            </div>
          </div>

          {{-- Section 1 : ข้อมูลการสั่งซื้อ --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon indigo">
                <i class="bx bx-list-ul"></i>
              </div>
              <span class="mf-section-title">ข้อมูลการสั่งซื้อ</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-3">
                  <label for="type" class="mf-label form-label">
                    <i class="bx bx-category"></i> ประเภทการสั่งรถ
                  </label>
                  <input id="type" type="text" class="form-control form-control-plaintext-mf" value="{{ $waiting->type }}" disabled>
                </div>

                <div class="col-md-4">
                  <label for="purchase_source" class="mf-label form-label">
                    <i class="bx bx-store"></i> แหล่งที่มา
                  </label>
                  <input id="purchase_source" type="text" class="form-control form-control-plaintext-mf" value="{{ $waiting->purchase_source }}" disabled>
                </div>

                <div class="col-md-5">
                  <label for="purchase_type" class="mf-label form-label">
                    <i class="bx bx-transfer"></i> ประเภทการซื้อรถ
                  </label>
                  <input id="purchase_type" type="text" class="form-control form-control-plaintext-mf" value="{{ $waiting->purchaseType->name ?? '-' }}" disabled>
                </div>

              </div>
            </div>
          </div>

          {{-- Section 2 : ข้อมูลรุ่นรถ --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon sky">
                <i class="bx bx-car"></i>
              </div>
              <span class="mf-section-title">ข้อมูลรุ่นรถ</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-5">
                  <label for="model_id" class="mf-label form-label">
                    <i class="bx bx-car ci-sky"></i> รุ่นรถหลัก
                  </label>
                  <input id="model_id" type="text" class="form-control form-control-plaintext-mf" value="{{ $waiting->model->Name_TH ?? '-' }}" disabled>
                </div>

                <div class="col-md-7">
                  <label for="subModel_id" class="mf-label form-label">
                    <i class="bx bx-barcode ci-sky"></i> รุ่นรถย่อย
                  </label>
                  <input id="subModel_id" type="text" class="form-control form-control-plaintext-mf"
                    value="{{ $waiting->subModel ? ($waiting->subModel->detail ? $waiting->subModel->detail . ' - ' : '') . $waiting->subModel->name : '-' }}"
                    disabled>
                </div>

                @if (auth()->user()->brand == 2)
                  <div class="col-md-4">
                    <label for="gwm_color" class="mf-label form-label">
                      <i class="bx bx-palette ci-sky"></i> สี
                    </label>
                    <input id="gwm_color" type="text" class="form-control form-control-plaintext-mf" value="{{ $waiting->gwmColor->name ?? '-' }}" disabled>
                  </div>
                  <div class="col-md-4">
                    <label for="interior_color" class="mf-label form-label">
                      <i class="bx bx-color-fill ci-sky"></i> สีภายใน
                    </label>
                    <input id="interior_color" type="text" class="form-control form-control-plaintext-mf" value="{{ $waiting->interiorColor->name ?? '-' }}" disabled>
                  </div>
                  <div class="col-md-4">
                    <label for="year" class="mf-label form-label">
                      <i class="bx bx-calendar ci-sky"></i> ปี
                    </label>
                    <input id="year" type="text" class="form-control form-control-plaintext-mf" value="{{ $waiting->year ?? '-' }}" disabled>
                  </div>
                @elseif (auth()->user()->brand == 3)
                  <div class="col-md-4">
                    <label for="gwm_color" class="mf-label form-label">
                      <i class="bx bx-palette ci-sky"></i> สี
                    </label>
                    <input id="gwm_color" type="text" class="form-control form-control-plaintext-mf" value="{{ $waiting->gwmColor->name ?? '-' }}" disabled>
                  </div>
                  <div class="col-md-4">
                    <label for="year" class="mf-label form-label">
                      <i class="bx bx-calendar ci-sky"></i> ปี
                    </label>
                    <input id="year" type="text" class="form-control form-control-plaintext-mf" value="{{ $waiting->year ?? '-' }}" disabled>
                  </div>
                @else
                  <div class="col-md-3">
                    <label for="option" class="mf-label form-label">
                      <i class="bx bx-list-check ci-sky"></i> Option
                    </label>
                    <input id="option" type="text" class="form-control form-control-plaintext-mf" value="{{ $waiting->option ?? '-' }}" disabled>
                  </div>
                  <div class="col-md-5">
                    <label for="color" class="mf-label form-label">
                      <i class="bx bx-color-fill ci-sky"></i> สี
                    </label>
                    <input id="color" type="text" class="form-control form-control-plaintext-mf" value="{{ $waiting->color ?? '-' }}" disabled>
                  </div>
                  <div class="col-md-4">
                    <label for="year" class="mf-label form-label">
                      <i class="bx bx-calendar ci-sky"></i> ปี
                    </label>
                    <input id="year" type="text" class="form-control form-control-plaintext-mf" value="{{ $waiting->year ?? '-' }}" disabled>
                  </div>
                @endif

              </div>
            </div>
          </div>

          {{-- Section 3 : ราคาและวันที่ --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon amber">
                <i class="bx bx-money"></i>
              </div>
              <span class="mf-section-title">ราคาและวันที่</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-3">
                  <label for="car_DNP" class="mf-label form-label">
                    <i class="bx bx-purchase-tag ci-amber"></i> ราคาทุน
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="car_DNP" type="text" class="form-control form-control-plaintext-mf text-end"
                      value="{{ $waiting->car_DNP !== null ? number_format($waiting->car_DNP, 2) : '-' }}" disabled>
                  </div>
                </div>

                <div class="col-md-3">
                  <label for="car_MSRP" class="mf-label form-label">
                    <i class="bx bx-receipt ci-amber"></i> ราคาขาย
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="car_MSRP" type="text" class="form-control form-control-plaintext-mf text-end"
                      value="{{ $waiting->car_MSRP !== null ? number_format($waiting->car_MSRP, 2) : '-' }}" disabled>
                  </div>
                </div>

                @if (!in_array(auth()->user()->brand, [2, 3]))
                  <div class="col-md-3">
                    <label for="RI" class="mf-label form-label">
                      <i class="bx bx-coin-stack ci-amber"></i> RI
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="RI" type="text" class="form-control form-control-plaintext-mf text-end"
                        value="{{ $waiting->RI !== null ? number_format($waiting->RI, 2) : '-' }}" disabled>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <label for="WS" class="mf-label form-label">
                      <i class="bx bx-coin-stack ci-amber"></i> WS
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="WS" type="text" class="form-control form-control-plaintext-mf text-end"
                        value="{{ $waiting->WS !== null ? number_format($waiting->WS, 2) : '-' }}" disabled>
                    </div>
                  </div>
                @endif

                <div class="col-md-3">
                  <label for="order_date" class="mf-label form-label">
                    <i class="bx bx-calendar ci-amber"></i> วันที่สั่งซื้อ
                  </label>
                  <input id="order_date" type="text" class="form-control form-control-plaintext-mf" value="{{ $waiting->format_order_date }}" disabled>
                </div>

                <div class="col-md-3">
                  <label for="approved_at" class="mf-label form-label">
                    <i class="bx bx-calendar-check ci-amber"></i> วันที่อนุมัติ
                  </label>
                  <input id="approved_at" type="text" class="form-control form-control-plaintext-mf"
                    value="{{ $waiting->approved_at ? \Carbon\Carbon::parse($waiting->approved_at)->format('d/m/Y') : '-' }}"
                    disabled>
                </div>

                <div class="col-md-4">
                  <label for="approver" class="mf-label form-label">
                    <i class="bx bx-user-check ci-amber"></i> ผู้อนุมัติ
                  </label>
                  <input id="approver" type="text" class="form-control form-control-plaintext-mf" value="{{ $waiting->approvers->name ?? '-' }}" disabled>
                </div>

              </div>
            </div>
          </div>

          {{-- Section 4 : หมายเหตุ --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon rose">
                <i class="bx bx-note"></i>
              </div>
              <span class="mf-section-title">หมายเหตุ</span>
            </div>
            <div class="mf-section-body">
              <textarea id="note" class="form-control" rows="2" disabled>{{ $waiting->note ?? '-' }}</textarea>
            </div>
          </div>

          {{-- Actions --}}
          @if ($waiting->status === 'approved')
            <div class="d-flex justify-content-end gap-2 pt-1">
              <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
                <i class="bx bx-x me-1"></i>ยกเลิก
              </button>
              <button type="button" class="btn btn-primary px-5 btnUpdateApproveWaitingOrder">
                <i class="bx bx-save me-1"></i>บันทึก
              </button>
            </div>
          @endif

        </form>
      </div>

    </div>
  </div>
</div>
