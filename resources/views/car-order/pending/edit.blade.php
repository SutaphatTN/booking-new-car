<div class="modal fade editPendingOrder" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--edit">

      {{-- Header --}}
      <div class="modal-header mf-header mf-header--edit px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-edit-alt fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">แก้ไขข้อมูลคำขอสั่งรถ</h6>
            <small class="text-white mf-hd-sub">{{ $order->order_code }}</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">
        <form id="pendingOrderForm" action="{{ route('car-order.updatePending', $order->id) }}" method="POST"
          enctype="multipart/form-data">
          @csrf
          @method('PUT')

          <div class="searchPurchaseCus"></div>

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
                  <input id="type" type="text" class="form-control" value="{{ $order->type }}"
                    style="background:#f8fafc;color:#64748b;" disabled>
                </div>

                <div class="col-md-3">
                  <label for="purchase_source" class="mf-label form-label">
                    <i class="bx bx-store ci-indigo"></i> แหล่งที่มา
                  </label>
                  <select id="purchase_source" name="purchase_source" class="form-select" required>
                    <option value="">-- เลือกแหล่งที่มา --</option>
                    @if (auth()->user()->brand == 1)
                      <option value="MMTH" {{ $order->purchase_source == 'MMTH' ? 'selected' : '' }}>MMTH</option>
                    @endif
                    @if (auth()->user()->brand == 2)
                      <option value="GWM" {{ $order->purchase_source == 'GWM' ? 'selected' : '' }}>GWM</option>
                    @endif
                    @if (auth()->user()->brand == 3)
                      <option value="WULING" {{ $order->purchase_source == 'WULING' ? 'selected' : '' }}>WULING</option>
                    @endif
                    <option value="OTHDealer" {{ $order->purchase_source == 'OTHDealer' ? 'selected' : '' }}>OTHDealer
                    </option>
                  </select>
                  @error('purchase_source')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

                <div class="col-md-3">
                  <label for="purchase_type" class="mf-label form-label">
                    <i class="bx bx-transfer ci-indigo"></i> ประเภทการซื้อรถ
                  </label>
                  <select id="purchase_type" name="purchase_type" class="form-select" required>
                    <option value="">-- เลือกประเภท --</option>
                    @foreach ($purchaseType as $t)
                      <option value="{{ $t->id }}" data-name="{{ $t->name }}"
                        {{ $order->purchase_type == $t->id ? 'selected' : '' }}>
                        {{ $t->name }}
                      </option>
                    @endforeach
                  </select>
                  @error('purchase_type')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

                <div class="col-md-3">
                  <label for="order_date" class="mf-label form-label">
                    <i class="bx bx-calendar ci-indigo"></i> วันที่สั่งซื้อ
                  </label>
                  <input id="order_date" type="text" class="form-control" value="{{ $order->format_order_date }}"
                    style="background:#f8fafc;color:#64748b;" disabled>
                </div>

                <input type="hidden" name="salecar_id" id="salecar_id">
                <div class="col-12">
                  <div id="fieldPurchase" class="row g-3 d-none">
                    <div class="col-md-12">
                      <label for="CusFullName" class="mf-label form-label">
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
                  <input id="model_id" type="text" class="form-control" value="{{ $order->model->Name_TH ?? '' }}"
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
                        <option value="{{ $t->id }}" {{ $order->gwm_color == $t->id ? 'selected' : '' }}>
                          {{ $t->name }}
                        </option>
                      @endforeach
                    </select>
                    @error('gwm_color')
                      <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                  </div>

                  <div class="col-md-4">
                    <label for="interior_color" class="mf-label form-label">
                      <i class="bx bx-color-fill ci-amber"></i> สีภายใน
                    </label>
                    <select id="interior_color" name="interior_color"
                      class="form-select @error('interior_color') is-invalid @enderror">
                      @foreach ($interiorColor as $t)
                        <option value="{{ $t->id }}" data-name="{{ $t->name }}"
                          {{ $order->interior_color == $t->id ? 'selected' : '' }}>
                          {{ $t->name }}
                        </option>
                      @endforeach
                    </select>
                    @error('interior_color')
                      <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                  </div>

                  <div class="col-md-4">
                    <label for="year" class="mf-label form-label">
                      <i class="bx bx-calendar ci-amber"></i> ปี
                    </label>
                    <input id="year" type="text" class="form-control @error('year') is-invalid @enderror"
                      name="year" value="{{ $order->year }}" readonly>
                    @error('year')
                      <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                  </div>

                  <div class="col-md-4">
                    <label for="car_DNP" class="mf-label form-label">
                      <i class="bx bx-purchase-tag ci-amber"></i> ราคาทุน
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="car_DNP" type="text"
                        class="form-control text-end money-input @error('car_DNP') is-invalid @enderror"
                        name="car_DNP"
                        value="{{ $order->car_DNP !== null ? number_format($order->car_DNP, 2) : '' }}" readonly>
                    </div>
                    @error('car_DNP')
                      <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                  </div>

                  <div class="col-md-4">
                    <label for="car_MSRP" class="mf-label form-label">
                      <i class="bx bx-receipt ci-amber"></i> ราคาขาย
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="car_MSRP" type="text"
                        class="form-control text-end money-input @error('car_MSRP') is-invalid @enderror"
                        name="car_MSRP"
                        value="{{ $order->car_MSRP !== null ? number_format($order->car_MSRP, 2) : '' }}" readonly>
                    </div>
                    @error('car_MSRP')
                      <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                  </div>

                  <div class="col-md-4">
                    <label for="approver" class="mf-label form-label">
                      <i class="bx bx-user-check ci-amber"></i> ผู้อนุมัติ
                    </label>
                    <input id="approver" type="text" class="form-control"
                      value="{{ $order->approvers->name ?? '-' }}"
                      style="background:#f8fafc;color:#64748b;" disabled>
                  </div>
                @elseif(auth()->user()->brand == 3)
                  <div class="col-md-4">
                    <label for="gwm_color" class="mf-label form-label">
                      <i class="bx bx-palette ci-amber"></i> สี
                    </label>
                    <select id="gwm_color" name="gwm_color" class="form-select" required>
                      <option value="">-- เลือกสี --</option>
                      @foreach ($gwmColor as $t)
                        <option value="{{ $t->id }}" {{ $order->gwm_color == $t->id ? 'selected' : '' }}>
                          {{ $t->name }}
                        </option>
                      @endforeach
                    </select>
                    @error('gwm_color')
                      <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                  </div>

                  <div class="col-md-4">
                    <label for="year" class="mf-label form-label">
                      <i class="bx bx-calendar ci-amber"></i> ปี
                    </label>
                    <input id="year" type="text" class="form-control @error('year') is-invalid @enderror"
                      name="year" value="{{ $order->year }}" readonly>
                    @error('year')
                      <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                  </div>

                  <div class="col-md-4">
                    <label for="car_DNP" class="mf-label form-label">
                      <i class="bx bx-purchase-tag ci-amber"></i> ราคาทุน
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="car_DNP" type="text"
                        class="form-control text-end money-input @error('car_DNP') is-invalid @enderror"
                        name="car_DNP"
                        value="{{ $order->car_DNP !== null ? number_format($order->car_DNP, 2) : '' }}" readonly>
                    </div>
                    @error('car_DNP')
                      <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                  </div>

                  <div class="col-md-4">
                    <label for="car_MSRP" class="mf-label form-label">
                      <i class="bx bx-receipt ci-amber"></i> ราคาขาย
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="car_MSRP" type="text"
                        class="form-control text-end money-input @error('car_MSRP') is-invalid @enderror"
                        name="car_MSRP"
                        value="{{ $order->car_MSRP !== null ? number_format($order->car_MSRP, 2) : '' }}" readonly>
                    </div>
                    @error('car_MSRP')
                      <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                  </div>

                  <div class="col-md-4">
                    <label for="approver" class="mf-label form-label">
                      <i class="bx bx-user-check ci-amber"></i> ผู้อนุมัติ
                    </label>
                    <input id="approver" type="text" class="form-control"
                      value="{{ $order->approvers->name ?? '-' }}"
                      style="background:#f8fafc;color:#64748b;" disabled>
                  </div>
                @else
                  <div class="col-md-2">
                    <label for="option" class="mf-label form-label">
                      <i class="bx bx-list-check ci-amber"></i> Option
                    </label>
                    <input id="option" type="text" class="form-control @error('option') is-invalid @enderror"
                      name="option" value="{{ $order->option }}" readonly>
                    @error('option')
                      <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                  </div>

                  <div class="col-md-3">
                    <label for="color" class="mf-label form-label">
                      <i class="bx bx-color-fill ci-amber"></i> สี
                    </label>
                    <input id="color" type="text" class="form-control @error('color') is-invalid @enderror"
                      name="color" value="{{ $order->color }}" required>
                    @error('color')
                      <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                  </div>

                  <div class="col-md-3">
                    <label for="year" class="mf-label form-label">
                      <i class="bx bx-calendar ci-amber"></i> ปี
                    </label>
                    <input id="year" type="text" class="form-control @error('year') is-invalid @enderror"
                      name="year" value="{{ $order->year }}" readonly>
                    @error('year')
                      <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                  </div>

                  <div class="col-md-4">
                    <label for="car_DNP" class="mf-label form-label">
                      <i class="bx bx-purchase-tag ci-amber"></i> ราคาทุน
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="car_DNP" type="text"
                        class="form-control text-end money-input @error('car_DNP') is-invalid @enderror"
                        name="car_DNP"
                        value="{{ $order->car_DNP !== null ? number_format($order->car_DNP, 2) : '' }}" readonly>
                    </div>
                    @error('car_DNP')
                      <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                  </div>

                  <div class="col-md-3">
                    <label for="car_MSRP" class="mf-label form-label">
                      <i class="bx bx-receipt ci-amber"></i> ราคาขาย
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="car_MSRP" type="text"
                        class="form-control text-end money-input @error('car_MSRP') is-invalid @enderror"
                        name="car_MSRP"
                        value="{{ $order->car_MSRP !== null ? number_format($order->car_MSRP, 2) : '' }}" readonly>
                    </div>
                    @error('car_MSRP')
                      <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                  </div>

                  <div class="col-md-3">
                    <label for="RI" class="mf-label form-label">
                      <i class="bx bx-coin-stack ci-amber"></i> RI
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="RI" type="text"
                        class="form-control text-end money-input @error('RI') is-invalid @enderror" name="RI"
                        value="{{ $order->RI !== null ? number_format($order->RI, 2) : '' }}" readonly>
                    </div>
                    @error('RI')
                      <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                  </div>

                  <div class="col-md-2">
                    <label for="WS" class="mf-label form-label">
                      <i class="bx bx-coin-stack ci-amber"></i> WS
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="WS" type="text"
                        class="form-control text-end money-input @error('WS') is-invalid @enderror" name="WS"
                        value="{{ $order->WS !== null ? number_format($order->WS, 2) : '' }}" readonly>
                    </div>
                    @error('WS')
                      <span class="invalid-feedback d-block" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                  </div>

                  <div class="col-md-4">
                    <label for="approver" class="mf-label form-label">
                      <i class="bx bx-user-check ci-amber"></i> ผู้อนุมัติ
                    </label>
                    <input id="approver" type="text" class="form-control"
                      value="{{ $order->approvers->name ?? '-' }}"
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
              <textarea id="note" class="form-control" name="note" rows="2">{{ $order->note }}</textarea>
            </div>
          </div>

          {{-- Actions --}}
          <div class="d-flex justify-content-end gap-2 pt-1">
            <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
              <i class="bx bx-x me-1"></i>ยกเลิก
            </button>
            <button type="button" class="btn btn-primary px-5 btnUpdatePendingOrder">
              <i class="bx bx-save me-1"></i>บันทึก
            </button>
          </div>

        </form>
      </div>

    </div>
  </div>
</div>

@include('car-order.pending.search-sale-customer.search')
