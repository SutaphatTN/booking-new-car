<div class="modal fade editApproveWaitingOrder" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <div class="d-flex justify-content-between w-100 align-items-center">
          <h4 class="modal-title mb-2">
            ผลการอนุมัติ : <span class="badge bg-label-success">อนุมัติ</span>
          </h4>
          <h5 class="text-secondary mb-0">{{ $waiting->order_code }}</h5>
        </div>
        <button type="button" class="btn-close ms-3" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <form id="approveWaitingForm"
          action="{{ route('car-order.updateApproveWaiting', $waiting->id) }}"
          method="POST"
          enctype="multipart/form-data">
          @csrf
          @method('PUT')

          <div class="row">
            <div class="col-md-4 mb-5">
              <label class="form-label">วันที่สั่งซื้อในระบบ</label>
              <input type="date" class="form-control" name="system_date"
                value="{{ $waiting->system_date }}" required>
            </div>

            <div class="col-md-4 mb-5">
              <label class="form-label">จำนวนที่สั่ง (คัน)</label>
              <input type="text" class="form-control" value="{{ $waiting->count_order }}" disabled>
            </div>

            <div class="col-md-4 mb-5">
              <label class="form-label">ได้รับจริง (คัน)</label>
              <input type="text" class="form-control" value="{{ $waiting->received_order }}" disabled>
            </div>

            <div class="col-md-3 mb-5">
              <label class="form-label">ประเภทการสั่งรถ</label>
              <input type="text" class="form-control" value="{{ $waiting->type }}" disabled>
            </div>

            <div class="col-md-4 mb-5">
              <label class="form-label">แหล่งที่มา</label>
              <input type="text" class="form-control" value="{{ $waiting->purchase_source }}" disabled>
            </div>

            <div class="col-md-5 mb-5">
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
            @elseif (auth()->user()->brand == 3)
              <div class="col-md-4 mb-5">
                <label class="form-label">สี</label>
                <input type="text" class="form-control" value="{{ $waiting->gwmColor->name ?? '-' }}" disabled>
              </div>
              <div class="col-md-4 mb-5">
                <label class="form-label">ปี</label>
                <input type="text" class="form-control" value="{{ $waiting->year ?? '-' }}" disabled>
              </div>
            @else
              <div class="col-md-3 mb-5">
                <label class="form-label">Option</label>
                <input type="text" class="form-control" value="{{ $waiting->option ?? '-' }}" disabled>
              </div>
              <div class="col-md-5 mb-5">
                <label class="form-label">สี</label>
                <input type="text" class="form-control" value="{{ $waiting->color ?? '-' }}" disabled>
              </div>
              <div class="col-md-4 mb-5">
                <label class="form-label">ปี</label>
                <input type="text" class="form-control" value="{{ $waiting->year ?? '-' }}" disabled>
              </div>
            @endif

            <div class="col-md-4 mb-5">
              <label class="form-label">ราคาทุน</label>
              <input type="text" class="form-control text-end"
                value="{{ $waiting->car_DNP !== null ? number_format($waiting->car_DNP, 2) : '-' }}" disabled>
            </div>

            <div class="col-md-4 mb-5">
              <label class="form-label">ราคาขาย</label>
              <input type="text" class="form-control text-end"
                value="{{ $waiting->car_MSRP !== null ? number_format($waiting->car_MSRP, 2) : '-' }}" disabled>
            </div>

            <div class="col-md-4 mb-5">
              <label class="form-label">ผู้อนุมัติ</label>
              <input type="text" class="form-control" value="{{ $waiting->approvers->name ?? '-' }}" disabled>
            </div>

            <div class="col-md-6 mb-5">
              <label class="form-label">วันที่สั่งซื้อ</label>
              <input type="text" class="form-control" value="{{ $waiting->format_order_date }}" disabled>
            </div>

            <div class="col-md-6 mb-5">
              <label class="form-label">วันที่อนุมัติ</label>
              <input type="text" class="form-control"
                value="{{ $waiting->approved_at ? \Carbon\Carbon::parse($waiting->approved_at)->format('d/m/Y') : '-' }}"
                disabled>
            </div>

            <div class="col-md-12 mb-5">
              <label class="form-label">หมายเหตุ</label>
              <textarea class="form-control" rows="2" disabled>{{ $waiting->note ?? '-' }}</textarea>
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">ยกเลิก</button>
            <button type="button" class="btn btn-primary btnUpdateApproveWaitingOrder">บันทึก</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>
