<div class="modal fade viewCarOrder" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--view">

      {{-- Header --}}
      <div class="modal-header mf-header mf-header--view px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-info-circle fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title" id="viewCarOrderLabel">ข้อมูลรถ</h6>
            <small class="text-white mf-hd-sub">Car Order Detail</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">

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
                <input id="type" class="form-control" type="text" value="{{ $order->type }}" disabled>
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
                <input id="model_id" class="form-control" type="text" value="{{ $order->model->Name_TH }}" disabled>
              </div>

              <div class="col-md-5">
                <label for="subModel_id" class="mf-label form-label">
                  <i class="bx bx-subdirectory-right ci-sky"></i> รุ่นรถย่อย
                </label>
                <input id="subModel_id" class="form-control" type="text"
                  value="{{ !empty($order->subModel) ? ($order->subModel->detail ? $order->subModel->detail . ' - ' . $order->subModel->name : $order->subModel->name) : '' }}"
                  disabled>
              </div>

              <div class="col-md-3">
                <label for="branch" class="mf-label form-label">
                  <i class="bx bx-building-house ci-sky"></i> สาขา
                </label>
                <input id="branch" class="form-control" type="text"
                  value="{{ $order->branchInfo->name ?? '-' }}" disabled>
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
                <input id="vin_number" class="form-control" type="text" value="{{ $order->vin_number }}" disabled>
              </div>

              <div class="col-md-3">
                <label for="j_number" class="mf-label form-label">
                  <i class="bx bx-key ci-indigo"></i> J-Number
                </label>
                <input id="j_number" class="form-control" type="text" value="{{ $order->j_number }}" disabled>
              </div>

              <div class="col-md-4">
                <label for="engine_number" class="mf-label form-label">
                  <i class="bx bx-cog ci-indigo"></i> หมายเลขเครื่องยนต์
                </label>
                <input id="engine_number" class="form-control" type="text" value="{{ $order->engine_number }}"
                  disabled>
              </div>

              @if (auth()->user()->brand == 2)
                <div class="col-md-2">
                  <label for="year" class="mf-label form-label">
                    <i class="bx bx-calendar ci-indigo"></i> ปี
                  </label>
                  <input id="year" class="form-control" type="text" value="{{ $order->year }}" disabled>
                </div>

                <div class="col-md-3">
                  <label for="gwm_color" class="mf-label form-label">
                    <i class="bx bx-palette ci-indigo"></i> สี
                  </label>
                  <input id="gwm_color" class="form-control" type="text"
                    value="{{ $order->gwmColor->name ?? '-' }}" disabled>
                </div>

                <div class="col-md-3">
                  <label for="interior_color" class="mf-label form-label">
                    <i class="bx bx-paint-roll ci-indigo"></i> สีภายใน
                  </label>
                  <input id="interior_color" class="form-control" type="text"
                    value="{{ $order->interiorColor->name ?? '-' }}" disabled>
                </div>
              @elseif (auth()->user()->brand == 3)
                <div class="col-md-2">
                  <label for="year" class="mf-label form-label">
                    <i class="bx bx-calendar ci-indigo"></i> ปี
                  </label>
                  <input id="year" class="form-control" type="text" value="{{ $order->year }}" disabled>
                </div>

                <div class="col-md-4">
                  <label for="gwm_color" class="mf-label form-label">
                    <i class="bx bx-palette ci-indigo"></i> สี
                  </label>
                  <input id="gwm_color" class="form-control" type="text"
                    value="{{ $order->gwmColor->name ?? '-' }}" disabled>
                </div>
              @else
                <div class="col-md-2">
                  <label for="option" class="mf-label form-label">
                    <i class="bx bx-list-check ci-indigo"></i> Option
                  </label>
                  <input id="option" class="form-control" type="text" value="{{ $order->option }}" disabled>
                </div>

                <div class="col-md-2">
                  <label for="color" class="mf-label form-label">
                    <i class="bx bx-palette ci-indigo"></i> สี
                  </label>
                  <input id="color" class="form-control" type="text" value="{{ $order->color }}" disabled>
                </div>

                <div class="col-md-2">
                  <label for="year" class="mf-label form-label">
                    <i class="bx bx-calendar ci-indigo"></i> ปี
                  </label>
                  <input id="year" class="form-control" type="text" value="{{ $order->year }}" disabled>
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
                    <input id="car_DNP" class="form-control text-end" type="text"
                      value="{{ $order->car_DNP !== null ? number_format($order->car_DNP, 2) : '-' }}" disabled>
                  </div>
                </div>

                <div class="col-md-3">
                  <label for="car_MSRP" class="mf-label form-label">
                    <i class="bx bx-money ci-amber"></i> ราคาขาย
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="car_MSRP" class="form-control text-end" type="text"
                      value="{{ $order->car_MSRP !== null ? number_format($order->car_MSRP, 2) : '-' }}" disabled>
                  </div>
                </div>
              @elseif (auth()->user()->brand == 3)
                <div class="col-md-3">
                  <label for="car_DNP" class="mf-label form-label">
                    <i class="bx bx-wallet ci-amber"></i> ราคาทุน
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="car_DNP" class="form-control text-end" type="text"
                      value="{{ $order->car_DNP !== null ? number_format($order->car_DNP, 2) : '-' }}" disabled>
                  </div>
                </div>

                <div class="col-md-3">
                  <label for="car_MSRP" class="mf-label form-label">
                    <i class="bx bx-money ci-amber"></i> ราคาขาย
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="car_MSRP" class="form-control text-end" type="text"
                      value="{{ $order->car_MSRP !== null ? number_format($order->car_MSRP, 2) : '-' }}" disabled>
                  </div>
                </div>
              @else
                <div class="col-md-3">
                  <label for="car_DNP" class="mf-label form-label">
                    <i class="bx bx-wallet ci-amber"></i> ราคาทุน
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="car_DNP" class="form-control text-end" type="text"
                      value="{{ $order->car_DNP !== null ? number_format($order->car_DNP, 2) : '-' }}" disabled>
                  </div>
                </div>

                <div class="col-md-3">
                  <label for="car_MSRP" class="mf-label form-label">
                    <i class="bx bx-money ci-amber"></i> ราคาขาย
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="car_MSRP" class="form-control text-end" type="text"
                      value="{{ $order->car_MSRP !== null ? number_format($order->car_MSRP, 2) : '-' }}" disabled>
                  </div>
                </div>

                <div class="col-md-3">
                  <label for="RI" class="mf-label form-label">
                    <i class="bx bx-receipt ci-amber"></i> RI
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="RI" class="form-control text-end" type="text"
                      value="{{ $order->RI !== null ? number_format($order->RI, 2) : '-' }}" disabled>
                  </div>
                </div>

                <div class="col-md-3">
                  <label for="WS" class="mf-label form-label">
                    <i class="bx bx-store ci-amber"></i> WS
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="WS" class="form-control text-end" type="text"
                      value="{{ $order->WS !== null ? number_format($order->WS, 2) : '-' }}" disabled>
                  </div>
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
                <input id="purchase_source" class="form-control" type="text"
                  value="{{ $order->purchase_source }}" disabled>
              </div>

              <div class="col-md-3">
                <label for="purchase_type" class="mf-label form-label">
                  <i class="bx bx-transfer ci-emerald"></i> ประเภทการซื้อรถ
                </label>
                <input id="purchase_type" class="form-control" type="text"
                  value="{{ $order->purchaseType->name }}" disabled>
              </div>

              <div class="col-md-3">
                <label for="car_status" class="mf-label form-label">
                  <i class="bx bx-check-shield ci-emerald"></i> สถานะรถ
                </label>
                <input id="car_status" class="form-control" type="text" value="{{ $order->car_status }}"
                  disabled>
              </div>

              <div class="col-md-3">
                <label for="order_status" class="mf-label form-label">
                  <i class="bx bx-list-check ci-emerald"></i> สถานะ Car Order
                </label>
                <input id="order_status" class="form-control" type="text"
                  value="{{ $order->orderStatus->name }}" disabled>
              </div>

              <div class="col-md-4">
                <label for="approver" class="mf-label form-label">
                  <i class="bx bx-user-check ci-emerald"></i> ผู้อนุมัติ
                </label>
                <input id="approver" class="form-control" type="text" value="{{ $order->approvers->name }}"
                  disabled>
              </div>

              @if ($order->purchase_type == 1)
                <div class="col-md-3">
                  <label for="mileage_test" class="mf-label form-label">
                    <i class="bx bx-tachometer ci-emerald"></i> เลขไมล์รถทดลองขับ
                  </label>
                  <input type="text" id="mileage_test" class="form-control"
                    value="{{ $order->mileage_test ?? '' }}" disabled>
                </div>

                <div class="col-md-5">
                  <label for="cam_testdrive" class="mf-label form-label">
                    <i class="bx bx-purchase-tag ci-emerald"></i> แคมเปญทดลองขับ
                  </label>
                  <input type="text" id="cam_testdrive" class="form-control"
                    value="{{ $order->cam_testdrive ?? '' }}" disabled>
                </div>
              @endif

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
                <textarea id="note_accessory" class="form-control" disabled>{{ $order->note_accessory }}</textarea>
              </div>

              <div class="col-12">
                <label for="note" class="mf-label form-label">
                  <i class="bx bx-comment-detail ci-pink"></i> หมายเหตุ
                </label>
                <textarea id="note" class="form-control" disabled>{{ $order->note }}</textarea>
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

              <div class="col-md-3">
                <label for="order_date" class="mf-label form-label">
                  <i class="bx bx-calendar-plus ci-rose"></i> วันที่สั่งซื้อ
                </label>
                <input id="order_date" class="form-control" type="text" value="{{ $order->format_order_date ?? '-' }}"
                  disabled>
              </div>

              <div class="col-md-3">
                <label for="approver_date" class="mf-label form-label">
                  <i class="bx bx-calendar-check ci-rose"></i> วันที่อนุมัติ
                </label>
                <input id="approver_date" class="form-control" type="text"
                  value="{{ $order->format_approver_date ?? '-' }}" disabled>
              </div>

              <div class="col-md-3">
                <label for="system_date" class="mf-label form-label">
                  <i class="bx bx-calendar-alt ci-rose"></i> วันที่สั่งซื้อในระบบ
                </label>
                <input id="system_date" class="form-control" type="text"
                  value="{{ $order->format_system_date ?? '-' }}" disabled>
              </div>

              <div class="col-md-3">
                <label for="estimated_stock_date" class="mf-label form-label">
                  <i class="bx bx-calendar-event ci-rose"></i> วันที่คาดว่าสินค้ามาถึง
                </label>
                <input id="estimated_stock_date" class="form-control" type="text"
                  value="{{ $order->format_estimated_stock_date ?? '-' }}" disabled>
              </div>

              <div class="col-md-3">
                <label for="order_invoice_date" class="mf-label form-label">
                  <i class="bx bx-receipt ci-rose"></i> วันที่ซื้อ (วันที่ออกใบกำกับ)
                </label>
                <input id="order_invoice_date" class="form-control" type="text"
                  value="{{ $order->format_order_invoice_date ?? '-' }}" disabled>
              </div>

              <div class="col-md-3">
                <label for="fp_date" class="mf-label form-label">
                  <i class="bx bx-money ci-rose"></i> วันที่จ่าย FP
                </label>
                <input id="fp_date" class="form-control" type="text" value="{{ $order->format_fp_date ?? '-' }}"
                  disabled>
              </div>

              <div class="col-md-3">
                <label for="order_stock_date" class="mf-label form-label">
                  <i class="bx bx-package ci-rose"></i> วันที่สต็อก
                </label>
                <input id="order_stock_date" class="form-control" type="text"
                  value="{{ $order->format_order_stock_date ?? '-' }}" disabled>
              </div>

            </div>
          </div>
        </div>

        {{-- <div class="d-flex justify-content-end pt-1">
          <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
            <i class="bx bx-x me-1"></i>ปิด
          </button>
        </div> --}}

      </div>
    </div>
  </div>
</div>
