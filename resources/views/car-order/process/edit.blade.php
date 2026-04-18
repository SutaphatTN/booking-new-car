<div class="modal fade editProcessOrder" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--view">

      {{-- Header --}}
      <div class="modal-header mf-header mf-header--view px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-check-shield fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">รออนุมัติคำขอสั่งรถ</h6>
            <small class="text-white mf-hd-sub">{{ $order->order_code }}</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">
        <form id="processOrderForm" action="{{ route('car-order.updateProcess', $order->id) }}" method="POST"
          enctype="multipart/form-data">
          @csrf
          @method('PUT')

          @php $userRole = auth()->user()->role; @endphp

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
                    <i class="bx bx-category ci-indigo"></i> ประเภทการสั่งรถ
                  </label>
                  <input id="type" type="text" class="form-control" value="{{ $order->type }}"
                    style="background:#f8fafc;color:#64748b;" disabled>
                </div>

                <div class="col-md-3">
                  <label for="purchase_source" class="mf-label form-label">
                    <i class="bx bx-store ci-indigo"></i> แหล่งที่มา
                  </label>
                  <input id="purchase_source" type="text" class="form-control" value="{{ $order->purchase_source }}"
                    style="background:#f8fafc;color:#64748b;" disabled>
                </div>

                <div class="col-md-3">
                  <label for="purchase_type" class="mf-label form-label">
                    <i class="bx bx-transfer ci-indigo"></i> ประเภทการซื้อรถ
                  </label>
                  <input id="purchase_type" type="text" class="form-control" value="{{ $order->purchaseType->name }}"
                    style="background:#f8fafc;color:#64748b;" disabled>
                </div>

                <div class="col-md-3">
                  <label for="order_date" class="mf-label form-label">
                    <i class="bx bx-calendar ci-indigo"></i> วันที่สั่งซื้อ
                  </label>
                  <input id="order_date" type="text" class="form-control" value="{{ $order->format_order_date }}"
                    style="background:#f8fafc;color:#64748b;" disabled>
                </div>

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
                    <input id="gwm_color" type="text" class="form-control" value="{{ $order->gwmColor->name }}"
                      style="background:#f8fafc;color:#64748b;" disabled>
                  </div>

                  <div class="col-md-4">
                    <label for="interior_color" class="mf-label form-label">
                      <i class="bx bx-color-fill ci-amber"></i> สีภายใน
                    </label>
                    <input id="interior_color" type="text" class="form-control"
                      value="{{ $order->interiorColor->name }}" style="background:#f8fafc;color:#64748b;" disabled>
                  </div>

                  <div class="col-md-4">
                    <label for="year" class="mf-label form-label">
                      <i class="bx bx-calendar ci-amber"></i> ปี
                    </label>
                    <input id="year" type="text" class="form-control" value="{{ $order->year }}"
                      style="background:#f8fafc;color:#64748b;" disabled>
                  </div>

                  <div class="col-md-4">
                    <label for="car_DNP" class="mf-label form-label">
                      <i class="bx bx-purchase-tag ci-amber"></i> ราคาทุน
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="car_DNP" type="text" class="form-control text-end"
                        value="{{ $order->car_DNP !== null ? number_format($order->car_DNP, 2) : '-' }}" disabled>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <label for="car_MSRP" class="mf-label form-label">
                      <i class="bx bx-receipt ci-amber"></i> ราคาขาย
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="car_MSRP" type="text" class="form-control text-end"
                        value="{{ $order->car_MSRP !== null ? number_format($order->car_MSRP, 2) : '-' }}" disabled>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <label for="approver" class="mf-label form-label">
                      <i class="bx bx-user-check ci-amber"></i> ผู้อนุมัติ
                    </label>
                    <input id="approver" type="text" class="form-control"
                      value="{{ $order->approvers->name ?? '-' }}" style="background:#f8fafc;color:#64748b;"
                      disabled>
                  </div>
                @elseif (auth()->user()->brand == 3)
                  <div class="col-md-4">
                    <label for="gwm_color" class="mf-label form-label">
                      <i class="bx bx-palette ci-amber"></i> สี
                    </label>
                    <input id="gwm_color" type="text" class="form-control" value="{{ $order->gwmColor->name }}"
                      style="background:#f8fafc;color:#64748b;" disabled>
                  </div>

                  <div class="col-md-4">
                    <label for="year" class="mf-label form-label">
                      <i class="bx bx-calendar ci-amber"></i> ปี
                    </label>
                    <input id="year" type="text" class="form-control" value="{{ $order->year }}"
                      style="background:#f8fafc;color:#64748b;" disabled>
                  </div>

                  <div class="col-md-4">
                    <label for="car_DNP" class="mf-label form-label">
                      <i class="bx bx-purchase-tag ci-amber"></i> ราคาทุน
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="car_DNP" type="text" class="form-control text-end"
                        value="{{ $order->car_DNP !== null ? number_format($order->car_DNP, 2) : '-' }}" disabled>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <label for="car_MSRP" class="mf-label form-label">
                      <i class="bx bx-receipt ci-amber"></i> ราคาขาย
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="car_MSRP" type="text" class="form-control text-end"
                        value="{{ $order->car_MSRP !== null ? number_format($order->car_MSRP, 2) : '-' }}" disabled>
                    </div>
                  </div>

                  {{-- <div class="col-md-3">
                    <label class="form-label" ...>RI</label>
                    <input type="text" class="form-control text-end" value="{{ $order->RI !== null ? number_format($order->RI, 2) : '-' }}" disabled />
                  </div> --}}

                  <div class="col-md-4">
                    <label for="approver" class="mf-label form-label">
                      <i class="bx bx-user-check ci-amber"></i> ผู้อนุมัติ
                    </label>
                    <input id="approver" type="text" class="form-control"
                      value="{{ $order->approvers->name ?? '-' }}" style="background:#f8fafc;color:#64748b;"
                      disabled>
                  </div>
                @else
                  <div class="col-md-2">
                    <label for="option" class="mf-label form-label">
                      <i class="bx bx-list-check ci-amber"></i> Option
                    </label>
                    <input id="option" type="text" class="form-control" value="{{ $order->option }}"
                      style="background:#f8fafc;color:#64748b;" disabled>
                  </div>

                  <div class="col-md-3">
                    <label for="color" class="mf-label form-label">
                      <i class="bx bx-color-fill ci-amber"></i> สี
                    </label>
                    <input id="color" type="text" class="form-control" value="{{ $order->color }}"
                      style="background:#f8fafc;color:#64748b;" disabled>
                  </div>

                  <div class="col-md-3">
                    <label for="year" class="mf-label form-label">
                      <i class="bx bx-calendar ci-amber"></i> ปี
                    </label>
                    <input id="year" type="text" class="form-control" value="{{ $order->year }}"
                      style="background:#f8fafc;color:#64748b;" disabled>
                  </div>

                  <div class="col-md-4">
                    <label for="car_DNP" class="mf-label form-label">
                      <i class="bx bx-purchase-tag ci-amber"></i> ราคาทุน
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="car_DNP" type="text" class="form-control text-end"
                        value="{{ $order->car_DNP !== null ? number_format($order->car_DNP, 2) : '-' }}" disabled>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <label for="car_MSRP" class="mf-label form-label">
                      <i class="bx bx-receipt ci-amber"></i> ราคาขาย
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="car_MSRP" type="text" class="form-control text-end"
                        value="{{ $order->car_MSRP !== null ? number_format($order->car_MSRP, 2) : '-' }}" disabled>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <label for="RI" class="mf-label form-label">
                      <i class="bx bx-coin-stack ci-amber"></i> RI
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="RI" type="text" class="form-control text-end"
                        value="{{ $order->RI !== null ? number_format($order->RI, 2) : '-' }}" disabled>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <label for="WS" class="mf-label form-label">
                      <i class="bx bx-coin-stack ci-amber"></i> WS
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="WS" type="text" class="form-control text-end"
                        value="{{ $order->WS !== null ? number_format($order->WS, 2) : '-' }}" disabled>
                    </div>
                  </div>

                  <div class="col-md-5">
                    <label for="approver" class="mf-label form-label">
                      <i class="bx bx-user-check ci-amber"></i> ผู้อนุมัติ
                    </label>
                    <input id="approver" type="text" class="form-control"
                      value="{{ $order->approvers->name ?? '-' }}" style="background:#f8fafc;color:#64748b;"
                      disabled>
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
              <textarea id="note" name="note" class="form-control" rows="2" disabled>{{ $order->note ?? '-' }}</textarea>
            </div>
          </div>

          <textarea class="form-control d-none" name="reason" id="reason"></textarea>
          <input type="hidden" name="action_status" id="action_status">

          {{-- <!-- <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-danger btnRejectOrder" data-value="reject">ไม่อนุมัติ</button>
            <button type="button" class="btn btn-success btnApproverOrder" data-value="approve">อนุมัติ</button>
          </div> --> --}}

        </form>
      </div>

    </div>
  </div>
</div>
