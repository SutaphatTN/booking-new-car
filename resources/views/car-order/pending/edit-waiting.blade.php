<div class="modal fade editWaitingOrder" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <div class="d-flex justify-content-between w-100 align-items-center">
          <h4 class="modal-title mb-2">แก้ไขคำขอสั่งรถ (Waiting)</h4>
          <h5 class="text-secondary mb-0">{{ $waiting->order_code }}</h5>
        </div>
        <button type="button" class="btn-close ms-3" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <form id="waitingOrderForm" action="{{ route('car-order.updateWaiting', $waiting->id) }}" method="POST"
          enctype="multipart/form-data">
          @csrf
          @method('PUT')

          <div class="row">
            <div class="col-md-3 mb-5">
              <label class="form-label">ประเภทการสั่งรถ</label>
              <input type="text" class="form-control" value="{{ $waiting->type }}" disabled>
            </div>

            <div class="col-md-3 mb-5">
              <label for="count_order" class="form-label">จำนวนที่สั่ง (คัน)</label>
              <input id="count_order" type="number" class="form-control" name="count_order"
                value="{{ $waiting->count_order }}" min="1" required>
            </div>

            <div class="col-md-3 mb-5">
              <label for="purchase_source" class="form-label">แหล่งที่มา</label>
              <select id="purchase_source" name="purchase_source" class="form-select" required>
                <option value="">-- เลือกแหล่งที่มา --</option>
                @if (auth()->user()->brand == 1)
                  <option value="MMTH" {{ $waiting->purchase_source == 'MMTH' ? 'selected' : '' }}>MMTH</option>
                @endif
                @if (auth()->user()->brand == 2)
                  <option value="GWM" {{ $waiting->purchase_source == 'GWM' ? 'selected' : '' }}>GWM</option>
                @endif
                @if (auth()->user()->brand == 3)
                  <option value="WULING" {{ $waiting->purchase_source == 'WULING' ? 'selected' : '' }}>WULING</option>
                @endif
                <option value="OTHDealer" {{ $waiting->purchase_source == 'OTHDealer' ? 'selected' : '' }}>OTHDealer</option>
              </select>
            </div>

            <div class="col-md-3 mb-5">
              <label for="purchase_type" class="form-label">ประเภทการซื้อรถ</label>
              <select id="purchase_type" name="purchase_type" class="form-select" required>
                <option value="">-- เลือกประเภท --</option>
                @foreach ($purchaseType as $t)
                  <option value="{{ $t->id }}" {{ $waiting->purchase_type == $t->id ? 'selected' : '' }}>
                    {{ $t->name }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-5 mb-5">
              <label class="form-label">รุ่นรถหลัก</label>
              <input type="text" class="form-control" value="{{ $waiting->model->Name_TH ?? '-' }}" disabled>
            </div>

            <div class="col-md-7 mb-5">
              <label class="form-label">รุ่นรถย่อย</label>
              <input type="text" class="form-control"
                value="{{ $waiting->subModel ? (($waiting->subModel->detail ? $waiting->subModel->detail . ' - ' : '') . $waiting->subModel->name) : '-' }}"
                disabled>
            </div>

            @if (auth()->user()->brand == 2)
              <div class="col-md-4 mb-5">
                <label for="gwm_color" class="form-label">สี</label>
                <select id="gwm_color" name="gwm_color" class="form-select" required>
                  <option value="">-- เลือกสี --</option>
                  @foreach ($gwmColor as $t)
                    <option value="{{ $t->id }}" {{ $waiting->gwm_color == $t->id ? 'selected' : '' }}>
                      {{ $t->name }}
                    </option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-4 mb-5">
                <label for="interior_color" class="form-label">สีภายใน</label>
                <select id="interior_color" name="interior_color" class="form-select">
                  @foreach ($interiorColor as $t)
                    <option value="{{ $t->id }}" {{ $waiting->interior_color == $t->id ? 'selected' : '' }}>
                      {{ $t->name }}
                    </option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-4 mb-5">
                <label class="form-label">ปี</label>
                <input type="text" class="form-control" name="year" value="{{ $waiting->year }}" readonly>
              </div>

              <div class="col-md-4 mb-5">
                <label class="form-label">ราคาทุน</label>
                <input type="text" class="form-control text-end money-input" name="car_DNP"
                  value="{{ $waiting->car_DNP !== null ? number_format($waiting->car_DNP, 2) : '' }}" readonly>
              </div>

              <div class="col-md-4 mb-5">
                <label class="form-label">ราคาขาย</label>
                <input type="text" class="form-control text-end money-input" name="car_MSRP"
                  value="{{ $waiting->car_MSRP !== null ? number_format($waiting->car_MSRP, 2) : '' }}" readonly>
              </div>

              <div class="col-md-4 mb-5">
                <label for="approver" class="form-label">ผู้อนุมัติ</label>
                <select id="approver" name="approver" class="form-select" required>
                  @foreach ($approvers as $u)
                    <option value="{{ $u->id }}" {{ $waiting->approver == $u->id ? 'selected' : '' }}>
                      {{ $u->name }}
                    </option>
                  @endforeach
                </select>
              </div>
            @elseif (auth()->user()->brand == 3)
              <div class="col-md-4 mb-5">
                <label for="gwm_color" class="form-label">สี</label>
                <select id="gwm_color" name="gwm_color" class="form-select" required>
                  <option value="">-- เลือกสี --</option>
                  @foreach ($gwmColor as $t)
                    <option value="{{ $t->id }}" {{ $waiting->gwm_color == $t->id ? 'selected' : '' }}>
                      {{ $t->name }}
                    </option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-4 mb-5">
                <label class="form-label">ปี</label>
                <input type="text" class="form-control" name="year" value="{{ $waiting->year }}" readonly>
              </div>

              <div class="col-md-4 mb-5">
                <label class="form-label">ราคาทุน</label>
                <input type="text" class="form-control text-end money-input" name="car_DNP"
                  value="{{ $waiting->car_DNP !== null ? number_format($waiting->car_DNP, 2) : '' }}" readonly>
              </div>

              <div class="col-md-4 mb-5">
                <label class="form-label">ราคาขาย</label>
                <input type="text" class="form-control text-end money-input" name="car_MSRP"
                  value="{{ $waiting->car_MSRP !== null ? number_format($waiting->car_MSRP, 2) : '' }}" readonly>
              </div>

              <div class="col-md-4 mb-5">
                <label for="approver" class="form-label">ผู้อนุมัติ</label>
                <select id="approver" name="approver" class="form-select" required>
                  @foreach ($approvers as $u)
                    <option value="{{ $u->id }}" {{ $waiting->approver == $u->id ? 'selected' : '' }}>
                      {{ $u->name }}
                    </option>
                  @endforeach
                </select>
              </div>
            @else
              <div class="col-md-2 mb-5">
                <label class="form-label">Option</label>
                <input type="text" class="form-control" name="option" value="{{ $waiting->option }}" readonly>
              </div>

              <div class="col-md-3 mb-5">
                <label class="form-label">สี</label>
                <input type="text" class="form-control" name="color" value="{{ $waiting->color }}" required>
              </div>

              <div class="col-md-3 mb-5">
                <label class="form-label">ปี</label>
                <input type="text" class="form-control" name="year" value="{{ $waiting->year }}" readonly>
              </div>

              <div class="col-md-4 mb-5">
                <label class="form-label">ราคาทุน</label>
                <input type="text" class="form-control text-end money-input" name="car_DNP"
                  value="{{ $waiting->car_DNP !== null ? number_format($waiting->car_DNP, 2) : '' }}" readonly>
              </div>

              <div class="col-md-3 mb-5">
                <label class="form-label">ราคาขาย</label>
                <input type="text" class="form-control text-end money-input" name="car_MSRP"
                  value="{{ $waiting->car_MSRP !== null ? number_format($waiting->car_MSRP, 2) : '' }}" readonly>
              </div>

              <div class="col-md-3 mb-5">
                <label class="form-label">RI</label>
                <input type="text" class="form-control text-end money-input" name="RI"
                  value="{{ $waiting->RI !== null ? number_format($waiting->RI, 2) : '' }}" readonly>
              </div>

              <div class="col-md-2 mb-5">
                <label class="form-label">WS</label>
                <input type="text" class="form-control text-end money-input" name="WS"
                  value="{{ $waiting->WS !== null ? number_format($waiting->WS, 2) : '' }}" readonly>
              </div>

              <div class="col-md-4 mb-5">
                <label for="approver" class="form-label">ผู้อนุมัติ</label>
                <select id="approver" name="approver" class="form-select" required>
                  @foreach ($approvers as $u)
                    <option value="{{ $u->id }}" {{ $waiting->approver == $u->id ? 'selected' : '' }}>
                      {{ $u->name }}
                    </option>
                  @endforeach
                </select>
              </div>
            @endif

            <div class="col-md-12 mb-5">
              <label class="form-label">หมายเหตุ</label>
              <textarea class="form-control" name="note" rows="2">{{ $waiting->note }}</textarea>
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">ยกเลิก</button>
            <button type="button" class="btn btn-primary btnUpdateWaitingOrder">บันทึก</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>
