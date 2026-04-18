<div class="modal fade editWaitingOrder" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--edit">

      {{-- Header --}}
      <div class="modal-header mf-header mf-header--edit px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-edit-alt fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">แก้ไขคำขอสั่งรถ (Waiting)</h6>
            <small class="text-white mf-hd-sub">{{ $waiting->order_code }}</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">
        <form id="waitingOrderForm" action="{{ route('car-order.updateWaiting', $waiting->id) }}" method="POST"
          enctype="multipart/form-data">
          @csrf
          @method('PUT')

          {{-- Section 1 : ข้อมูลการสั่งซื้อ --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon indigo">
                <i class="bx bx-list-ul ci-indigo"></i>
              </div>
              <span class="mf-section-title">ข้อมูลการสั่งซื้อ</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-3">
                  <label for="type" class="mf-label form-label">
                    <i class="bx bx-category ci-indigo"></i> ประเภทการสั่งรถ
                  </label>
                  <input id="type" type="text" class="form-control" value="{{ $waiting->type }}"
                    style="background:#f8fafc;color:#64748b;" disabled>
                </div>

                <div class="col-md-3">
                  <label for="count_order" class="mf-label form-label">
                    <i class="bx bx-hash ci-indigo"></i> จำนวนที่สั่ง (คัน)
                  </label>
                  <input id="count_order" type="number" class="form-control" name="count_order"
                    value="{{ $waiting->count_order }}" min="1" required>
                </div>

                <div class="col-md-3">
                  <label for="purchase_source" class="mf-label form-label">
                    <i class="bx bx-store ci-indigo"></i> แหล่งที่มา
                  </label>
                  <select id="purchase_source" name="purchase_source" class="form-select" required>
                    <option value="">-- เลือกแหล่งที่มา --</option>
                    @if (auth()->user()->brand == 1)
                      <option value="MMTH" {{ $waiting->purchase_source == 'MMTH' ? 'selected' : '' }}>MMTH</option>
                    @endif
                    @if (auth()->user()->brand == 2)
                      <option value="GWM" {{ $waiting->purchase_source == 'GWM' ? 'selected' : '' }}>GWM</option>
                    @endif
                    @if (auth()->user()->brand == 3)
                      <option value="WULING" {{ $waiting->purchase_source == 'WULING' ? 'selected' : '' }}>WULING
                      </option>
                    @endif
                    <option value="OTHDealer" {{ $waiting->purchase_source == 'OTHDealer' ? 'selected' : '' }}>OTHDealer
                    </option>
                  </select>
                </div>

                <div class="col-md-3">
                  <label for="purchase_type" class="mf-label form-label">
                    <i class="bx bx-transfer ci-indigo"></i> ประเภทการซื้อรถ
                  </label>
                  <select id="purchase_type" name="purchase_type" class="form-select" required>
                    <option value="">-- เลือกประเภท --</option>
                    @foreach ($purchaseType as $t)
                      <option value="{{ $t->id }}" {{ $waiting->purchase_type == $t->id ? 'selected' : '' }}>
                        {{ $t->name }}
                      </option>
                    @endforeach
                  </select>
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
                  <input id="model_id" type="text" class="form-control"
                    value="{{ $waiting->model->Name_TH ?? '-' }}" style="background:#f8fafc;color:#64748b;" disabled>
                </div>

                <div class="col-md-7">
                  <label for="subModel_id" class="mf-label form-label">
                    <i class="bx bx-barcode ci-sky"></i> รุ่นรถย่อย
                  </label>
                  <input id="subModel_id" type="text" class="form-control"
                    value="{{ $waiting->subModel ? ($waiting->subModel->detail ? $waiting->subModel->detail . ' - ' : '') . $waiting->subModel->name : '-' }}"
                    style="background:#f8fafc;color:#64748b;" disabled>
                </div>

              </div>
            </div>
          </div>

          {{-- Section 3 : รายละเอียดรถและราคา --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon amber">
                <i class="bx bx-money"></i>
              </div>
              <span class="mf-section-title">รายละเอียดรถและราคา</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                @if (auth()->user()->brand == 2)
                  <div class="col-md-4">
                    <label for="gwm_color" class="mf-label form-label">
                      <i class="bx bx-palette ci-amber"></i> สี
                    </label>
                    <select id="gwm_color" name="gwm_color" class="form-select" required>
                      <option value="">-- เลือกสี --</option>
                      @foreach ($gwmColor as $t)
                        <option value="{{ $t->id }}" {{ $waiting->gwm_color == $t->id ? 'selected' : '' }}>
                          {{ $t->name }}
                        </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-4">
                    <label for="interior_color" class="mf-label form-label">
                      <i class="bx bx-color-fill ci-amber"></i> สีภายใน
                    </label>
                    <select id="interior_color" name="interior_color" class="form-select">
                      @foreach ($interiorColor as $t)
                        <option value="{{ $t->id }}"
                          {{ $waiting->interior_color == $t->id ? 'selected' : '' }}>
                          {{ $t->name }}
                        </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-4">
                    <label for="year" class="mf-label form-label">
                      <i class="bx bx-calendar ci-amber"></i> ปี
                    </label>
                    <input id="year" type="text" class="form-control" name="year"
                      value="{{ $waiting->year }}" readonly>
                  </div>

                  <div class="col-md-4">
                    <label for="car_DNP" class="mf-label form-label">
                      <i class="bx bx-purchase-tag ci-amber"></i> ราคาทุน
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="car_DNP" type="text" class="form-control text-end money-input" name="car_DNP"
                        value="{{ $waiting->car_DNP !== null ? number_format($waiting->car_DNP, 2) : '' }}" readonly>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <label for="car_MSRP" class="mf-label form-label">
                      <i class="bx bx-receipt ci-amber"></i> ราคาขาย
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="car_MSRP" type="text" class="form-control text-end money-input" name="car_MSRP"
                        value="{{ $waiting->car_MSRP !== null ? number_format($waiting->car_MSRP, 2) : '' }}"
                        readonly>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <label for="approver" class="mf-label form-label">
                      <i class="bx bx-user-check ci-amber"></i> ผู้อนุมัติ
                    </label>
                    <input id="approver" type="text" class="form-control"
                      value="{{ $waiting->approvers->name ?? '-' }}"
                      style="background:#f8fafc;color:#64748b;" disabled>
                  </div>
                @elseif (auth()->user()->brand == 3)
                  <div class="col-md-4">
                    <label for="gwm_color" class="mf-label form-label">
                      <i class="bx bx-palette ci-amber"></i> สี
                    </label>
                    <select id="gwm_color" name="gwm_color" class="form-select" required>
                      <option value="">-- เลือกสี --</option>
                      @foreach ($gwmColor as $t)
                        <option value="{{ $t->id }}" {{ $waiting->gwm_color == $t->id ? 'selected' : '' }}>
                          {{ $t->name }}
                        </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-4">
                    <label for="year" class="mf-label form-label">
                      <i class="bx bx-calendar ci-amber"></i> ปี
                    </label>
                    <input id="year" type="text" class="form-control" name="year"
                      value="{{ $waiting->year }}" readonly>
                  </div>

                  <div class="col-md-4">
                    <label for="car_DNP" class="mf-label form-label">
                      <i class="bx bx-purchase-tag ci-amber"></i> ราคาทุน
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="car_DNP" type="text" class="form-control text-end money-input" name="car_DNP"
                        value="{{ $waiting->car_DNP !== null ? number_format($waiting->car_DNP, 2) : '' }}" readonly>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <label for="car_MSRP" class="mf-label form-label">
                      <i class="bx bx-receipt ci-amber"></i> ราคาขาย
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="car_MSRP" type="text" class="form-control text-end money-input" name="car_MSRP"
                        value="{{ $waiting->car_MSRP !== null ? number_format($waiting->car_MSRP, 2) : '' }}"
                        readonly>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <label for="approver" class="mf-label form-label">
                      <i class="bx bx-user-check ci-amber"></i> ผู้อนุมัติ
                    </label>
                    <input id="approver" type="text" class="form-control"
                      value="{{ $waiting->approvers->name ?? '-' }}"
                      style="background:#f8fafc;color:#64748b;" disabled>
                  </div>
                @else
                  <div class="col-md-2">
                    <label for="option" class="mf-label form-label">
                      <i class="bx bx-list-check ci-amber"></i> Option
                    </label>
                    <input id="option" type="text" class="form-control" name="option"
                      value="{{ $waiting->option }}" readonly>
                  </div>

                  <div class="col-md-3">
                    <label for="color" class="mf-label form-label">
                      <i class="bx bx-color-fill ci-amber"></i> สี
                    </label>
                    <input id="color" type="text" class="form-control" name="color"
                      value="{{ $waiting->color }}" required>
                  </div>

                  <div class="col-md-3">
                    <label for="year" class="mf-label form-label">
                      <i class="bx bx-calendar ci-amber"></i> ปี
                    </label>
                    <input id="year" type="text" class="form-control" name="year"
                      value="{{ $waiting->year }}" readonly>
                  </div>

                  <div class="col-md-4">
                    <label for="car_DNP" class="mf-label form-label">
                      <i class="bx bx-purchase-tag ci-amber"></i> ราคาทุน
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="car_DNP" type="text" class="form-control text-end money-input" name="car_DNP"
                        value="{{ $waiting->car_DNP !== null ? number_format($waiting->car_DNP, 2) : '' }}" readonly>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <label for="car_MSRP" class="mf-label form-label">
                      <i class="bx bx-receipt ci-amber"></i> ราคาขาย
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="car_MSRP" type="text" class="form-control text-end money-input" name="car_MSRP"
                        value="{{ $waiting->car_MSRP !== null ? number_format($waiting->car_MSRP, 2) : '' }}"
                        readonly>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <label for="RI" class="mf-label form-label">
                      <i class="bx bx-coin-stack ci-amber"></i> RI
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="RI" type="text" class="form-control text-end money-input" name="RI"
                        value="{{ $waiting->RI !== null ? number_format($waiting->RI, 2) : '' }}" readonly>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <label for="WS" class="mf-label form-label">
                      <i class="bx bx-coin-stack ci-amber"></i> WS
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="WS" type="text" class="form-control text-end money-input" name="WS"
                        value="{{ $waiting->WS !== null ? number_format($waiting->WS, 2) : '' }}" readonly>
                    </div>
                  </div>

                  <div class="col-md-5">
                    <label for="approver" class="mf-label form-label">
                      <i class="bx bx-user-check ci-amber"></i> ผู้อนุมัติ
                    </label>
                    <input id="approver" type="text" class="form-control"
                      value="{{ $waiting->approvers->name ?? '-' }}"
                      style="background:#f8fafc;color:#64748b;" disabled>
                  </div>
                @endif

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
              <label for="note" class="mf-label form-label">
                <i class="bx bx-info-circle text-primary" data-bs-toggle="tooltip" data-bs-trigger="click"
                  data-bs-placement="right"
                  title="กรณีสั่งให้ลูกค้า ให้ใส่ข้อมูล ชื่อลูกค้า / วันที่ PO (ถ้ามี) / จำนวนเงินจอง ทุกครั้ง">
                </i> หมายเหตุ
              </label>
              <textarea id="note" class="form-control" name="note" rows="2">{{ $waiting->note }}</textarea>
            </div>
          </div>

          {{-- Actions --}}
          <div class="d-flex justify-content-end gap-2 pt-1">
            <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
              <i class="bx bx-x me-1"></i>ยกเลิก
            </button>
            <button type="button" class="btn btn-primary px-5 btnUpdateWaitingOrder">
              <i class="bx bx-save me-1"></i>บันทึก
            </button>
          </div>

        </form>
      </div>

    </div>
  </div>
</div>
