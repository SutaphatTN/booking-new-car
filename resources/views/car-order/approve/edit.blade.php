<div class="modal fade editApproveOrder" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--input">

      {{-- Header --}}
      <div class="modal-header mf-header mf-header--input px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-check-shield fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">
              ผลการอนุมัติ :
              @if ($order->status === 'approved')
                <span class="badge bg-success ms-1">อนุมัติ</span>
              @else
                <span class="badge bg-danger ms-1">ไม่อนุมัติ</span>
              @endif
            </h6>
            <small class="text-white mf-hd-sub">{{ $order->order_code }}</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">
        <form id="approveOrderForm" action="{{ route('car-order.updateApprove', $order->id) }}" method="POST"
          enctype="multipart/form-data">
          @csrf
          @method('PUT')

          @php $userRole = auth()->user()->role; @endphp

          {{-- Section 0 : ผลการอนุมัติ --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon {{ $order->status === 'approved' ? 'emerald' : 'rose' }}">
                <i class="bx {{ $order->status === 'approved' ? 'bx-check-circle' : 'bx-x-circle' }}"></i>
              </div>
              <span class="mf-section-title">
                {{ $order->status === 'approved' ? 'ข้อมูลการอนุมัติ' : 'เหตุผลที่ไม่ผ่านการอนุมัติ' }}
              </span>
            </div>
            <div class="mf-section-body">
              @if ($order->status === 'approved')
                <label for="system_date" class="mf-label form-label">
                  <i class="bx bx-calendar-check ci-emerald"></i> วันที่สั่งซื้อในระบบ
                </label>
                <input id="system_date" type="date" class="form-control mf-input-narrow" name="system_date"
                  value="{{ $order->system_date }}" required>
                @error('system_date')
                  <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                @enderror
              @else
                <label for="reason" class="mf-label form-label">
                  <i class="bx bx-comment-x ci-rose"></i> เหตุผลที่ไม่ผ่านการอนุมัติ
                </label>
                <textarea id="reason" class="form-control" rows="2" disabled>{{ $order->reason ?? '-' }}</textarea>
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
                  <input id="type" type="text" class="form-control" value="{{ $order->type }}"
                    style="background:#f8fafc;color:#64748b;" disabled>
                </div>

                <div class="col-md-4">
                  <label for="purchase_source" class="mf-label form-label">
                    <i class="bx bx-store"></i> แหล่งที่มา
                  </label>
                  <input id="purchase_source" type="text" class="form-control" value="{{ $order->purchase_source }}"
                    style="background:#f8fafc;color:#64748b;" disabled>
                </div>

                <div class="col-md-5">
                  <label for="purchase_type" class="mf-label form-label">
                    <i class="bx bx-transfer"></i> ประเภทการซื้อรถ
                  </label>
                  <input id="purchase_type" type="text" class="form-control"
                    value="{{ $order->purchaseType->name }}" style="background:#f8fafc;color:#64748b;" disabled>
                </div>

                <div class="col-12">
                  <div id="fieldPurchase" class="row g-3 d-none">
                    <div class="col-md-12">
                      <label for="CusFullName" class="form-label"
                        style="font-size:.82rem;font-weight:600;color:#374151;margin-bottom:5px;display:flex;align-items:center;gap:5px;">
                        <i class="bx bx-user ci-indigo"></i> ชื่อ - นามสกุล ลูกค้า
                      </label>
                      <input id="CusFullName" type="text" class="form-control"
                        value="{{ $order->saleCus->customer->prefix->Name_TH ?? '' }} {{ $order->saleCus->customer->FirstName ?? '' }} {{ $order->saleCus->customer->LastName ?? '' }}"
                        style="background:#f8fafc;color:#64748b;" disabled>
                    </div>
                  </div>
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
                  <input id="model_id" type="text" class="form-control" value="{{ $order->model->Name_TH }}"
                    style="background:#f8fafc;color:#64748b;" disabled>
                </div>

                <div class="col-md-7">
                  <label for="subModel_id" class="mf-label form-label">
                    <i class="bx bx-barcode ci-sky"></i> รุ่นรถย่อย
                  </label>
                  <input id="subModel_id" type="text" class="form-control"
                    value="{{ !empty($order->subModel) ? ($order->subModel->detail ? $order->subModel->detail . ' - ' . $order->subModel->name : $order->subModel->name) : '' }}"
                    style="background:#f8fafc;color:#64748b;" disabled>
                </div>

                @if (auth()->user()->brand == 2)
                  <div class="col-md-4">
                    <label for="gwm_color" class="mf-label form-label">
                      <i class="bx bx-palette ci-sky"></i> สี
                    </label>
                    <input id="gwm_color" type="text" class="form-control" value="{{ $order->gwmColor->name }}"
                      style="background:#f8fafc;color:#64748b;" disabled>
                  </div>
                  <div class="col-md-4">
                    <label for="interior_color" class="mf-label form-label">
                      <i class="bx bx-color-fill ci-sky"></i> สีภายใน
                    </label>
                    <input id="interior_color" type="text" class="form-control"
                      value="{{ $order->interiorColor->name }}" style="background:#f8fafc;color:#64748b;" disabled>
                  </div>
                  <div class="col-md-4">
                    <label for="year" class="mf-label form-label">
                      <i class="bx bx-calendar ci-sky"></i> ปี
                    </label>
                    <input id="year" type="text" class="form-control" value="{{ $order->year }}"
                      style="background:#f8fafc;color:#64748b;" disabled>
                  </div>
                @elseif (auth()->user()->brand == 3)
                  <div class="col-md-4">
                    <label for="gwm_color" class="mf-label form-label">
                      <i class="bx bx-palette ci-sky"></i> สี
                    </label>
                    <input id="gwm_color" type="text" class="form-control" value="{{ $order->gwmColor->name }}"
                      style="background:#f8fafc;color:#64748b;" disabled>
                  </div>
                  <div class="col-md-4">
                    <label for="year" class="mf-label form-label">
                      <i class="bx bx-calendar ci-sky"></i> ปี
                    </label>
                    <input id="year" type="text" class="form-control" value="{{ $order->year }}"
                      style="background:#f8fafc;color:#64748b;" disabled>
                  </div>
                @else
                  <div class="col-md-3">
                    <label for="option" class="mf-label form-label">
                      <i class="bx bx-list-check ci-sky"></i> Option
                    </label>
                    <input id="option" type="text" class="form-control" value="{{ $order->option }}"
                      style="background:#f8fafc;color:#64748b;" disabled>
                  </div>
                  <div class="col-md-5">
                    <label for="color" class="mf-label form-label">
                      <i class="bx bx-color-fill ci-sky"></i> สี
                    </label>
                    <input id="color" type="text" class="form-control" value="{{ $order->color }}"
                      style="background:#f8fafc;color:#64748b;" disabled>
                  </div>
                  <div class="col-md-4">
                    <label for="year" class="mf-label form-label">
                      <i class="bx bx-calendar ci-sky"></i> ปี
                    </label>
                    <input id="year" type="text" class="form-control" value="{{ $order->year }}"
                      style="background:#f8fafc;color:#64748b;" disabled>
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
                    <input id="car_DNP" type="text" class="form-control text-end"
                      value="{{ $order->car_DNP !== null ? number_format($order->car_DNP, 2) : '-' }}" disabled>
                  </div>
                </div>

                <div class="col-md-3">
                  <label for="car_MSRP" class="mf-label form-label">
                    <i class="bx bx-receipt ci-amber"></i> ราคาขาย
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="car_MSRP" type="text" class="form-control text-end"
                      value="{{ $order->car_MSRP !== null ? number_format($order->car_MSRP, 2) : '-' }}" disabled>
                  </div>
                </div>

                @if (!in_array(auth()->user()->brand, [2, 3]))
                  <div class="col-md-3">
                    <label for="RI" class="mf-label form-label">
                      <i class="bx bx-coin-stack ci-amber"></i> RI
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="RI" type="text" class="form-control text-end"
                        value="{{ $order->RI !== null ? number_format($order->RI, 2) : '-' }}" disabled>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <label for="WS" class="mf-label form-label">
                      <i class="bx bx-coin-stack ci-amber"></i> WS
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="WS" type="text" class="form-control text-end"
                        value="{{ $order->WS !== null ? number_format($order->WS, 2) : '-' }}" disabled>
                    </div>
                  </div>
                @endif

                <div class="col-md-3">
                  <label for="order_date" class="mf-label form-label">
                    <i class="bx bx-calendar ci-amber"></i> วันที่สั่งซื้อ
                  </label>
                  <input id="order_date" type="text" class="form-control"
                    value="{{ $order->format_order_date }}" style="background:#f8fafc;color:#64748b;" disabled>
                </div>

                <div class="col-md-3">
                  <label for="approver_date" class="mf-label form-label">
                    <i class="bx bx-calendar-check ci-amber"></i> วันที่อนุมัติ
                  </label>
                  <input id="approver_date" type="text" class="form-control"
                    value="{{ $order->format_approver_date }}" style="background:#f8fafc;color:#64748b;" disabled>
                </div>

                <div class="col-md-4">
                  <label for="approver" class="mf-label form-label">
                    <i class="bx bx-user-check ci-amber"></i> ผู้อนุมัติ
                  </label>
                  <input id="approver" type="text" class="form-control"
                    value="{{ $order->approvers->name ?? '-' }}" style="background:#f8fafc;color:#64748b;" disabled>
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
              <textarea id="note" name="note" class="form-control" rows="2" disabled>{{ $order->note ?? '-' }}</textarea>
            </div>
          </div>

          {{-- Actions --}}
          @if ($order->status === 'approved')
            <div class="d-flex justify-content-end gap-2 pt-1">
              <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
                <i class="bx bx-x me-1"></i>ยกเลิก
              </button>
              <button type="button" class="btn btn-primary px-5 btnUpdateApproveOrder">
                <i class="bx bx-save me-1"></i>บันทึก
              </button>
            </div>
          @endif

        </form>
      </div>

    </div>
  </div>
</div>
