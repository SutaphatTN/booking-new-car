<div class="modal fade viewWaitingOrder" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <div class="d-flex justify-content-between w-100 align-items-center">
          <h4 class="modal-title mb-2">รายละเอียดคำขอสั่งรถ (Waiting)</h4>
          <h5 class="text-secondary mb-0">{{ $waiting->order_code }}</h5>
        </div>
        <button type="button" class="btn-close ms-3" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row">
          <div class="col-md-3 mb-5">
            <label class="form-label">ประเภทการสั่งรถ</label>
            <input type="text" class="form-control" value="{{ $waiting->type }}" disabled>
          </div>

          <div class="col-md-3 mb-5">
            <label class="form-label">จำนวนที่สั่ง (คัน)</label>
            <input type="text" class="form-control" value="{{ $waiting->count_order }}" disabled>
          </div>

          <div class="col-md-3 mb-5">
            <label class="form-label">แหล่งที่มา</label>
            <input type="text" class="form-control" value="{{ $waiting->purchase_source }}" disabled>
          </div>

          <div class="col-md-3 mb-5">
            <label class="form-label">ประเภทการซื้อรถ</label>
            <input type="text" class="form-control" value="{{ $waiting->purchaseType->name ?? '-' }}" disabled>
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
              <label class="form-label">สี</label>
              <input type="text" class="form-control" value="{{ $waiting->gwmColor->name ?? '-' }}" disabled>
            </div>

            <div class="col-md-4 mb-5">
              <label class="form-label">สีภายใน</label>
              <input type="text" class="form-control" value="{{ $waiting->interiorColor->name ?? '-' }}" disabled>
            </div>

            <div class="col-md-4 mb-5">
              <label class="form-label">ปี</label>
              <input type="text" class="form-control" value="{{ $waiting->year ?? '-' }}" disabled>
            </div>

            <div class="col-md-4 mb-5">
              <label class="form-label">ราคาทุน</label>
              <input type="text" class="form-control text-end" value="{{ $waiting->car_DNP !== null ? number_format($waiting->car_DNP, 2) : '-' }}" disabled>
            </div>

            <div class="col-md-4 mb-5">
              <label class="form-label">ราคาขาย</label>
              <input type="text" class="form-control text-end" value="{{ $waiting->car_MSRP !== null ? number_format($waiting->car_MSRP, 2) : '-' }}" disabled>
            </div>

            <div class="col-md-4 mb-5">
              <label class="form-label">ผู้อนุมัติ</label>
              <input type="text" class="form-control" value="{{ $waiting->approvers->name ?? '-' }}" disabled>
            </div>
          @elseif (auth()->user()->brand == 3)
            <div class="col-md-4 mb-5">
              <label class="form-label">สี</label>
              <input type="text" class="form-control" value="{{ $waiting->gwmColor->name ?? '-' }}" disabled>
            </div>

            <div class="col-md-4 mb-5">
              <label class="form-label">ปี</label>
              <input type="text" class="form-control" value="{{ $waiting->year ?? '-' }}" disabled>
            </div>

            <div class="col-md-4 mb-5">
              <label class="form-label">ราคาทุน</label>
              <input type="text" class="form-control text-end" value="{{ $waiting->car_DNP !== null ? number_format($waiting->car_DNP, 2) : '-' }}" disabled>
            </div>

            <div class="col-md-4 mb-5">
              <label class="form-label">ราคาขาย</label>
              <input type="text" class="form-control text-end" value="{{ $waiting->car_MSRP !== null ? number_format($waiting->car_MSRP, 2) : '-' }}" disabled>
            </div>

            <div class="col-md-4 mb-5">
              <label class="form-label">ผู้อนุมัติ</label>
              <input type="text" class="form-control" value="{{ $waiting->approvers->name ?? '-' }}" disabled>
            </div>
          @else
            <div class="col-md-2 mb-5">
              <label class="form-label">Option</label>
              <input type="text" class="form-control" value="{{ $waiting->option ?? '-' }}" disabled>
            </div>

            <div class="col-md-3 mb-5">
              <label class="form-label">สี</label>
              <input type="text" class="form-control" value="{{ $waiting->color ?? '-' }}" disabled>
            </div>

            <div class="col-md-3 mb-5">
              <label class="form-label">ปี</label>
              <input type="text" class="form-control" value="{{ $waiting->year ?? '-' }}" disabled>
            </div>

            <div class="col-md-4 mb-5">
              <label class="form-label">ราคาทุน</label>
              <input type="text" class="form-control text-end" value="{{ $waiting->car_DNP !== null ? number_format($waiting->car_DNP, 2) : '-' }}" disabled>
            </div>

            <div class="col-md-3 mb-5">
              <label class="form-label">ราคาขาย</label>
              <input type="text" class="form-control text-end" value="{{ $waiting->car_MSRP !== null ? number_format($waiting->car_MSRP, 2) : '-' }}" disabled>
            </div>

            <div class="col-md-3 mb-5">
              <label class="form-label">RI</label>
              <input type="text" class="form-control text-end" value="{{ $waiting->RI !== null ? number_format($waiting->RI, 2) : '-' }}" disabled>
            </div>

            <div class="col-md-2 mb-5">
              <label class="form-label">WS</label>
              <input type="text" class="form-control text-end" value="{{ $waiting->WS !== null ? number_format($waiting->WS, 2) : '-' }}" disabled>
            </div>

            <div class="col-md-4 mb-5">
              <label class="form-label">ผู้อนุมัติ</label>
              <input type="text" class="form-control" value="{{ $waiting->approvers->name ?? '-' }}" disabled>
            </div>
          @endif

          <div class="col-md-12 mb-5">
            <label class="form-label">หมายเหตุ</label>
            <textarea class="form-control" rows="2" disabled>{{ $waiting->note ?? '-' }}</textarea>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
