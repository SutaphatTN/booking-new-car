<div class="modal fade editCarOrder" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--edit">

      {{-- Header --}}
      <div class="modal-header mf-header mf-header--edit px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-edit-alt fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title" id="CarOrderLabel">แก้ไขข้อมูลการสั่งซื้อ</h6>
            <small class="text-white mf-hd-sub">Edit Car Order</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">
        <form action="{{ route('car-order.update', $order->id) }}" method="POST" enctype="multipart/form-data">
          @csrf
          @method('PUT')

          {{-- Section 1 : ข้อมูลการสั่งซื้อ --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon sky">
                <i class="bx bx-cart-alt"></i>
              </div>
              <span class="mf-section-title">ข้อมูลการสั่งซื้อ</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-2">
                  <label for="type" class="mf-label form-label">
                    <i class="bx bx-category ci-sky"></i> ประเภทการสั่งรถ
                  </label>
                  <input id="type" type="text" class="form-control" value="{{ $order->type ?? '-' }}" disabled>
                </div>

                <div class="col-md-2">
                  <label for="order_code" class="mf-label form-label">
                    <i class="bx bx-hash ci-sky"></i> รหัสการสั่งซื้อ
                  </label>
                  <input id="order_code" class="form-control" type="text" value="{{ $order->order_code }}" disabled>
                </div>

                <div class="col-md-3">
                  <label for="model_id" class="mf-label form-label">
                    <i class="bx bx-car ci-sky"></i> รุ่นรถหลัก
                  </label>
                  <input id="model_id" type="text" class="form-control" value="{{ $order->model->Name_TH }}"
                    disabled>
                </div>

                <div class="col-md-5">
                  <label for="subModel_id" class="mf-label form-label">
                    <i class="bx bx-subdirectory-right ci-sky"></i> รุ่นรถย่อย
                  </label>
                  <input id="subModel_id" type="text" class="form-control"
                    value="{{ !empty($order->subModel) ? ($order->subModel->detail ? $order->subModel->detail . ' - ' . $order->subModel->name : $order->subModel->name) : '' }}"
                    disabled>
                </div>

                <div class="col-md-3">
                  <label for="branch" class="mf-label form-label">
                    <i class="bx bx-building-house ci-sky"></i> สาขา
                  </label>
                  <select id="branch" name="branch" class="form-select">
                    <option value="">-- เลือกสาขา --</option>
                    @foreach ($branches as $b)
                      <option value="{{ $b->id }}" {{ $order->branch == $b->id ? 'selected' : '' }}>
                        {{ $b->name }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div class="col-12">
                  <div id="fieldPurchase" class="row g-3 d-none">
                    <div class="col-12">
                      <label for="CusFullName" class="mf-label form-label">
                        <i class="bx bx-user-circle ci-sky"></i> ชื่อ - นามสกุล ลูกค้า
                      </label>
                      <input id="CusFullName" type="text" class="form-control"
                        value="{{ $order->saleCus->customer->prefix->Name_TH ?? '' }} {{ $order->saleCus->customer->FirstName ?? '' }} {{ $order->saleCus->customer->LastName ?? '' }}"
                        disabled>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>

          {{-- Section 2 : รายละเอียดรถ --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon indigo">
                <i class="bx bx-car"></i>
              </div>
              <span class="mf-section-title">รายละเอียดรถ</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-3">
                  <label for="vin_number" class="mf-label form-label">
                    <i class="bx bx-barcode ci-indigo"></i> Vin Number
                  </label>
                  <input id="vin_number" type="text" class="form-control @error('vin_number') is-invalid @enderror"
                    name="vin_number" value="{{ $order->vin_number }}" required>
                  @error('vin_number')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

                <div class="col-md-3">
                  <label for="j_number" class="mf-label form-label">
                    <i class="bx bx-key ci-indigo"></i> J-Number
                  </label>
                  <input id="j_number" type="text" class="form-control @error('j_number') is-invalid @enderror"
                    name="j_number" value="{{ $order->j_number }}" required>
                  @error('j_number')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

                <div class="col-md-4">
                  <label for="engine_number" class="mf-label form-label">
                    <i class="bx bx-cog ci-indigo"></i> หมายเลขเครื่องยนต์
                  </label>
                  <input id="engine_number" type="text"
                    class="form-control @error('engine_number') is-invalid @enderror" name="engine_number"
                    value="{{ $order->engine_number }}" required>
                  @error('engine_number')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

                @if (auth()->user()->brand == 2)
                  <div class="col-md-2">
                    <label for="year" class="mf-label form-label">
                      <i class="bx bx-calendar ci-indigo"></i> ปี
                    </label>
                    <input id="year" type="text" class="form-control @error('year') is-invalid @enderror"
                      name="year" value="{{ $order->year }}" readonly>
                    @error('year')
                      <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                  </div>

                  <div class="col-md-3">
                    <label for="gwm_color" class="mf-label form-label">
                      <i class="bx bx-palette ci-indigo"></i> สี
                    </label>
                    <input id="gwm_color" type="text" class="form-control" name="gwm_color"
                      value="{{ $order->gwmColor->name ?? '-' }}" disabled>
                  </div>

                  <div class="col-md-3">
                    <label for="interior_color" class="mf-label form-label">
                      <i class="bx bx-paint-roll ci-indigo"></i> สีภายใน
                    </label>
                    <input id="interior_color" type="text" class="form-control" name="interior_color"
                      value="{{ $order->interiorColor->name ?? '-' }}" disabled>
                  </div>
                @elseif (auth()->user()->brand == 3)
                  <div class="col-md-2">
                    <label for="year" class="mf-label form-label">
                      <i class="bx bx-calendar ci-indigo"></i> ปี
                    </label>
                    <input id="year" type="text" class="form-control @error('year') is-invalid @enderror"
                      name="year" value="{{ $order->year }}" readonly>
                    @error('year')
                      <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                  </div>

                  <div class="col-md-4">
                    <label for="gwm_color" class="mf-label form-label">
                      <i class="bx bx-palette ci-indigo"></i> สี
                    </label>
                    <input id="gwm_color" type="text" class="form-control" name="gwm_color"
                      value="{{ $order->gwmColor->name ?? '-' }}" disabled>
                  </div>
                @else
                  <div class="col-md-2">
                    <label for="option" class="mf-label form-label">
                      <i class="bx bx-list-check ci-indigo"></i> Option
                    </label>
                    <input id="option" type="text" class="form-control @error('option') is-invalid @enderror"
                      name="option" value="{{ $order->option }}" required>
                    @error('option')
                      <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                  </div>

                  <div class="col-md-2">
                    <label for="color" class="mf-label form-label">
                      <i class="bx bx-palette ci-indigo"></i> สี
                    </label>
                    <input id="color" type="text" class="form-control @error('color') is-invalid @enderror"
                      name="color" value="{{ $order->color }}" disabled>
                    @error('color')
                      <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                  </div>

                  <div class="col-md-2">
                    <label for="year" class="mf-label form-label">
                      <i class="bx bx-calendar ci-indigo"></i> ปี
                    </label>
                    <input id="year" type="text" class="form-control @error('year') is-invalid @enderror"
                      name="year" value="{{ $order->year }}" readonly>
                    @error('year')
                      <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                  </div>
                @endif

              </div>
            </div>
          </div>

          {{-- Section 3 : ราคา --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon amber">
                <i class="bx bx-money"></i>
              </div>
              <span class="mf-section-title">ราคา</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                @if (auth()->user()->brand == 2)
                  <div class="col-md-3">
                    <label for="car_DNP" class="mf-label form-label">
                      <i class="bx bx-wallet ci-amber"></i> ราคาทุน
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="car_DNP" type="text"
                        class="form-control text-end money-input form-control-plaintext-mf @error('car_DNP') is-invalid @enderror"
                        name="car_DNP"
                        value="{{ $order->car_DNP !== null ? number_format($order->car_DNP, 2) : '' }}" readonly>
                    </div>
                    @error('car_DNP')
                      <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                    @enderror
                  </div>

                  <div class="col-md-3">
                    <label for="car_MSRP" class="mf-label form-label">
                      <i class="bx bx-money ci-amber"></i> ราคาขาย
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="car_MSRP" type="text"
                        class="form-control text-end money-input form-control-plaintext-mf @error('car_MSRP') is-invalid @enderror"
                        name="car_MSRP"
                        value="{{ $order->car_MSRP !== null ? number_format($order->car_MSRP, 2) : '' }}" readonly>
                    </div>
                    @error('car_MSRP')
                      <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                    @enderror
                  </div>
                @elseif (auth()->user()->brand == 3)
                  <div class="col-md-3">
                    <label for="car_DNP" class="mf-label form-label">
                      <i class="bx bx-wallet ci-amber"></i> ราคาทุน
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="car_DNP" type="text"
                        class="form-control text-end money-input form-control-plaintext-mf @error('car_DNP') is-invalid @enderror"
                        name="car_DNP"
                        value="{{ $order->car_DNP !== null ? number_format($order->car_DNP, 2) : '' }}" readonly>
                    </div>
                    @error('car_DNP')
                      <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                    @enderror
                  </div>

                  <div class="col-md-3">
                    <label for="car_MSRP" class="mf-label form-label">
                      <i class="bx bx-money ci-amber"></i> ราคาขาย
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="car_MSRP" type="text"
                        class="form-control text-end money-input form-control-plaintext-mf @error('car_MSRP') is-invalid @enderror"
                        name="car_MSRP"
                        value="{{ $order->car_MSRP !== null ? number_format($order->car_MSRP, 2) : '' }}" readonly>
                    </div>
                    @error('car_MSRP')
                      <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                    @enderror
                  </div>
                @else
                  <div class="col-md-3">
                    <label for="car_DNP" class="mf-label form-label">
                      <i class="bx bx-wallet ci-amber"></i> ราคาทุน
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="car_DNP" type="text"
                        class="form-control text-end money-input form-control-plaintext-mf @error('car_DNP') is-invalid @enderror"
                        name="car_DNP"
                        value="{{ $order->car_DNP !== null ? number_format($order->car_DNP, 2) : '' }}" readonly>
                    </div>
                    @error('car_DNP')
                      <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                    @enderror
                  </div>

                  <div class="col-md-3">
                    <label for="car_MSRP" class="mf-label form-label">
                      <i class="bx bx-money ci-amber"></i> ราคาขาย
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="car_MSRP" type="text"
                        class="form-control text-end money-input form-control-plaintext-mf @error('car_MSRP') is-invalid @enderror"
                        name="car_MSRP"
                        value="{{ $order->car_MSRP !== null ? number_format($order->car_MSRP, 2) : '' }}" readonly>
                    </div>
                    @error('car_MSRP')
                      <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                    @enderror
                  </div>

                  <div class="col-md-3">
                    <label for="RI" class="mf-label form-label">
                      <i class="bx bx-receipt ci-amber"></i> RI
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="RI" type="text"
                        class="form-control text-end money-input @error('RI') is-invalid @enderror" name="RI"
                        value="{{ $order->RI !== null ? number_format($order->RI, 2) : '' }}" required>
                    </div>
                    @error('RI')
                      <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                    @enderror
                  </div>

                  <div class="col-md-3">
                    <label for="WS" class="mf-label form-label">
                      <i class="bx bx-store ci-amber"></i> WS
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="WS" type="text"
                        class="form-control text-end money-input @error('WS') is-invalid @enderror" name="WS"
                        value="{{ $order->WS !== null ? number_format($order->WS, 2) : '' }}" required>
                    </div>
                    @error('WS')
                      <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                    @enderror
                  </div>
                @endif

              </div>
            </div>
          </div>

          {{-- Section 4 : ข้อมูลการซื้อ --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon emerald">
                <i class="bx bx-transfer"></i>
              </div>
              <span class="mf-section-title">ข้อมูลการซื้อ</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-3">
                  <label for="purchase_source" class="mf-label form-label">
                    <i class="bx bx-store ci-emerald"></i> แหล่งที่มา
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
                      <option value="WULING" {{ $order->purchase_source == 'WULING' ? 'selected' : '' }}>WULING
                      </option>
                    @endif
                    <option value="OTHDealer" {{ $order->purchase_source == 'OTHDealer' ? 'selected' : '' }}>OTHDealer
                    </option>
                  </select>
                  @error('purchase_source')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

                <div class="col-md-3">
                  <label for="purchase_type" class="mf-label form-label">
                    <i class="bx bx-transfer ci-emerald"></i> ประเภทการซื้อรถ
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
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

                <div class="col-md-3">
                  <label for="car_status" class="mf-label form-label">
                    <i class="bx bx-check-shield ci-emerald"></i> สถานะรถ
                  </label>
                  <select id="car_status" name="car_status" class="form-select" required>
                    <option value="">-- เลือกสถานะ --</option>
                    <option value="Available" {{ $order->car_status == 'Available' ? 'selected' : '' }}>Available
                    </option>
                    <option value="Booked" {{ $order->car_status == 'Booked' ? 'selected' : '' }}>Booked</option>
                    <option value="Delivered" {{ $order->car_status == 'Delivered' ? 'selected' : '' }}>Delivered
                    </option>
                  </select>
                  @error('car_status')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

                <div class="col-md-3">
                  <label for="order_status" class="mf-label form-label">
                    <i class="bx bx-list-check ci-emerald"></i> สถานะ Car Order
                  </label>
                  <select id="order_status" name="order_status" class="form-select" required>
                    <option value="">-- เลือกสถานะ --</option>
                    @foreach ($orderStatus as $status)
                      <option value="{{ $status->id }}" data-name="{{ $status->name }}"
                        {{ $order->order_status == $status->id ? 'selected' : '' }}>
                        {{ $status->name }}
                      </option>
                    @endforeach
                  </select>
                  @error('order_status')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

                <div class="col-md-4">
                  <label for="approver" class="mf-label form-label">
                    <i class="bx bx-user-check ci-emerald"></i> ผู้อนุมัติ
                  </label>
                  <input id="approver" type="text" class="form-control"
                    value="{{ $order->approvers->name ?? '-' }}" style="background:#f8fafc;color:#64748b;" disabled>
                </div>

                <div class="col-md-3 fieldTestDrive d-none">
                  <label for="mileage_test" class="mf-label form-label">
                    <i class="bx bx-tachometer ci-emerald"></i> เลขไมล์รถทดลองขับ
                  </label>
                  <input id="mileage_test" name="mileage_test" type="text" class="form-control"
                    value="{{ $order->mileage_test ?? '' }}">
                </div>

                <div class="col-md-5 fieldTestDrive d-none">
                  <label for="cam_testdrive" class="mf-label form-label">
                    <i class="bx bx-purchase-tag ci-emerald"></i> แคมเปญทดลองขับ
                  </label>
                  <input id="cam_testdrive" name="cam_testdrive" type="text" class="form-control"
                    value="{{ $order->cam_testdrive ?? '' }}">
                </div>

              </div>
            </div>
          </div>

          {{-- Section 5 : หมายเหตุ --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon pink">
                <i class="bx bx-note"></i>
              </div>
              <span class="mf-section-title">หมายเหตุ</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-12">
                  <label for="note_accessory" class="mf-label form-label">
                    <i class="bx bx-wrench ci-pink"></i> ประดับยนต์ของรถ
                  </label>
                  <textarea id="note_accessory" class="form-control" name="note_accessory" rows="2">{{ $order->note_accessory }}</textarea>
                </div>

                <div class="col-12">
                  <label for="note" class="mf-label form-label">
                    <i class="bx bx-comment-detail ci-pink"></i> หมายเหตุ
                  </label>
                  <textarea id="note" class="form-control" name="note" rows="2">{{ $order->note }}</textarea>
                </div>

              </div>
            </div>
          </div>

          {{-- Section 6 : วันที่ --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon rose">
                <i class="bx bx-calendar"></i>
              </div>
              <span class="mf-section-title">วันที่</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-4">
                  <label for="system_date" class="mf-label form-label">
                    <i class="bx bx-calendar-alt ci-rose"></i> วันที่สั่งซื้อในระบบ
                  </label>
                  <input id="system_date" type="date"
                    class="form-control @error('system_date') is-invalid @enderror" name="system_date"
                    value="{{ $order->system_date }}" required>
                  @error('system_date')
                    <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                  @enderror
                </div>

                <div id="fieldOnWeb" class="col-md-4 d-none">
                  <label for="estimated_stock_date" class="mf-label form-label">
                    <i class="bx bx-calendar-event ci-rose"></i> วันที่คาดว่าสินค้ามาถึง
                  </label>
                  <input type="date" id="estimated_stock_date" name="estimated_stock_date" class="form-control"
                    value="{{ $order->estimated_stock_date }}">
                </div>

                <div id="fieldInvoice" class="col-md-8 d-none">
                  <div class="row g-3">
                    <div class="col-6">
                      <label for="order_invoice_date" class="mf-label form-label">
                        <i class="bx bx-receipt ci-rose"></i> วันที่ซื้อ (วันที่ออกใบกำกับ)
                      </label>
                      <input type="date" id="order_invoice_date" name="order_invoice_date" class="form-control"
                        value="{{ $order->order_invoice_date }}">
                    </div>

                    <div class="col-6">
                      <label for="fp_date" class="mf-label form-label">
                        <i class="bx bx-money ci-rose"></i> วันที่จ่าย FP
                      </label>
                      <input type="date" id="fp_date" name="fp_date" class="form-control"
                        value="{{ $order->fp_date }}">
                    </div>
                  </div>
                </div>

                <div id="fieldStock" class="col-md-4 d-none">
                  <label for="order_stock_date" class="mf-label form-label">
                    <i class="bx bx-package ci-rose"></i> วันที่สต็อก
                  </label>
                  <input type="date" id="order_stock_date" name="order_stock_date" class="form-control"
                    value="{{ $order->order_stock_date }}">
                </div>

              </div>
            </div>
          </div>

          {{-- Actions --}}
          <div class="d-flex justify-content-end gap-2 pt-1">
            <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
              <i class="bx bx-x me-1"></i>ยกเลิก
            </button>
            <button type="button" class="btn btn-primary px-5 btnUpdateCarOrder">
              <i class="bx bx-save me-1"></i>บันทึก
            </button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>
