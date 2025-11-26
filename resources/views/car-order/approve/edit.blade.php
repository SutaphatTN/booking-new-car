<div class="modal fade editApproveOrder" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <div class="d-flex justify-content-between w-100 align-items-center">
          <h4 class="modal-title mb-2" id="editApproveOrderLabel">
            ผลการอนุมัติ :
            @if ($order->status === 'approved')
            <span class="badge bg-label-success">อนุมัติ</span>
            @else
            <span class="badge bg-label-danger">ไม่อนุมัติ</span>
            @endif
          </h4>

          <h5 class="text-secondary mb-0">
            {{ $order->order_code }}
          </h5>
        </div>

        <button type="button" class="btn-close ms-3" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <form id="approveOrderForm"
          action="{{ route('car-order.updateApprove', $order->id) }}"
          method="POST"
          enctype="multipart/form-data">
          @csrf
          @method('PUT')

          @php
          $userRole = auth()->user()->role;
          @endphp

          <div class="row">
            @if ($order->status === 'approved')
            <div class="col-md-12 mb-5">
              <label for="system_date" class="form-label">วันที่สั่งซื้อในระบบ</label>
              <input id="system_date" type="date"
                class="form-control"
                name="system_date"
                value="{{ $order->system_date }}"
                required>

              @error('system_date')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>
            @else
            <div class="col-md-12 mb-5">
              <label for="reason" class="form-label">เหตุผลที่ไม่ผ่านการอนุมัติ</label>
              <textarea name="reason"
                class="form-control"
                disabled>{{ $order->reason ?? '-' }}</textarea>
            </div>
            @endif

            <div class="col-md-2 mb-5">
              <label for="type" class="form-label">ประเภท</label>
              <input class="form-control" type="text" value="{{ $order->type }}" disabled />
            </div>

            <div class="col-md-4 mb-5">
              <label for="model_id" class="form-label">รุ่นรถหลัก</label>
              <input class="form-control" type="text" value="{{ $order->model->Name_TH }}" disabled />
            </div>

            <div class="col-md-6 mb-5">
              <label for="subModel_id" class="form-label">รุ่นรถย่อย</label>
              <input class="form-control" type="text" value="{{ $order->subModel->name }}" disabled />
            </div>

            <div class="col-md-2 mb-5">
              <label for="option" class="form-label">Option</label>
              <input class="form-control" type="text" value="{{ $order->option }}" disabled />
            </div>

            <div class="col-md-2 mb-5">
              <label for="color" class="form-label">สี</label>
              <input class="form-control" type="text" value="{{ $order->color }}" disabled />
            </div>

            <div class="col-md-2 mb-5">
              <label for="year" class="form-label">ปี</label>
              <input class="form-control" type="text" value="{{ $order->year }}" disabled />
            </div>

            <div class="col-md-3 mb-5">
              <label for="car_DNP" class="form-label">ราคาทุน</label>
              <input class="form-control text-end" type="text" 
              value="{{ $order->car_DNP !== null ? number_format($order->car_DNP, 2) : '-' }}" disabled />
            </div>

            <div class="col-md-3 mb-5">
              <label for="car_MSRP" class="form-label">ราคาขาย</label>
              <input class="form-control text-end" type="text" 
              value="{{ $order->car_MSRP !== null ? number_format($order->car_MSRP, 2) : '-' }}" disabled />
            </div>

            <div class="col-md-4 mb-5">
              <label for="purchase_source" class="form-label">แหล่งที่มา</label>
              <input class="form-control" type="text" value="{{ $order->purchase_source }}" disabled />
            </div>

            <div class="col-md-4 mb-5">
              <label for="purchase_type" class="form-label">ประเภทการซื้อรถ</label>
              <input class="form-control" type="text" value="{{ $order->purchase_type }}" disabled />
            </div>

            <div class="col-md-4 mb-5">
              <label for="approver" class="form-label">ผู้อนุมัติ</label>
              <input class="form-control" type="text" value="{{ $order->approvers->name ?? '-' }}" disabled />
            </div>

            <div class="col-md-12 mb-5">
              <label for="note" class="form-label">หมายเหตุ</label>
              <textarea name="note"
                class="form-control"
                disabled>{{ $order->note ?? '-' }}</textarea>
            </div>

          </div>

          @if ($order->status === 'approved')
          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">ยกเลิก</button>
            <button type="button" class="btn btn-primary btnUpdateApproveOrder">บันทึก</button>
          </div>
          @endif

        </form>
      </div>
    </div>
  </div>
</div>