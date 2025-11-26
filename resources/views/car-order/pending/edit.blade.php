<div class="modal fade editPendingOrder" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <div class="d-flex justify-content-between w-100 align-items-center">
          <h4 class="modal-title mb-2" id="editPendingOrderLabel">
            แก้ไขข้อมูลคำขอสั่งรถ
          </h4>

          <h5 class="text-secondary mb-0">
            {{ $order->order_code }}
          </h5>
        </div>

        <button type="button" class="btn-close ms-3" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <form id="pendingOrderForm"
          action="{{ route('car-order.updatePending', $order->id) }}"
          method="POST"
          enctype="multipart/form-data">
          @csrf
          @method('PUT')

          <div class="row">
            <div class="col-md-2 mb-5">
              <label for="type" class="form-label">ประเภท</label>
              <select id="type" name="type" class="form-select" required>
                <option value="">เลือก</option>
                <option value="ลูกค้า" {{ $order->type == 'ลูกค้า' ? 'selected' : '' }}>ลูกค้า</option>
                <option value="stock" {{ $order->type == 'stock' ? 'selected' : '' }}>stock</option>
              </select>

              @error('type')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-4 mb-5">
              <label for="model_id" class="form-label">รุ่นรถหลัก</label>
              <select id="model_id" name="model_id" class="form-select @error('model_id') is-invalid @enderror" required>
                <option value="">-- เลือกรุ่นรถหลัก --</option>
                @foreach ($model as $m)
                <option value="{{ $m->id }}" {{ $order->model_id == $m->id ? 'selected' : '' }}>{{ $m->Name_TH }}</option>
                @endforeach
              </select>

              @error('model_id')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-6 mb-5">
              <label for="subModel_id" class="form-label">รุ่นรถย่อย</label>
              <select id="subModel_id" name="subModel_id" class="form-select @error('subModel_id') is-invalid @enderror" required>
                @foreach ($subModels as $s)
                <option value="{{ $s->id }}" {{ $order->subModel_id == $s->id ? 'selected' : '' }}>
                  {{ $s->name }}
                </option>
                @endforeach
              </select>

              @error('subModel_id')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-2 mb-5">
              <label for="option" class="form-label">Option</label>
              <input id="option" type="text"
                class="form-control @error('option') is-invalid @enderror"
                name="option" value="{{ $order->option }}" required>

              @error('option')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-2 mb-5">
              <label for="color" class="form-label">สี</label>
              <input id="color" type="text"
                class="form-control @error('color') is-invalid @enderror"
                name="color" value="{{ $order->color }}" required>

              @error('color')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-2 mb-5">
              <label for="year" class="form-label">ปี</label>
              <input id="year" type="text"
                class="form-control @error('year') is-invalid @enderror"
                name="year" value="{{ $order->year }}" required>

              @error('year')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-3 mb-5">
              <label for="car_DNP" class="form-label">ราคาทุน</label>
              <input id="car_DNP" type="text"
                class="form-control text-end money-input @error('car_DNP') is-invalid @enderror"
                name="car_DNP"
                value="{{ $order->car_DNP !== null ? number_format($order->car_DNP, 2) : '' }}"
                required>

              @error('car_DNP')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-3 mb-5">
              <label for="car_MSRP" class="form-label">ราคาขาย</label>
              <input id="car_MSRP" type="text"
                class="form-control text-end money-input @error('car_MSRP') is-invalid @enderror"
                name="car_MSRP"
                value="{{ $order->car_MSRP !== null ? number_format($order->car_MSRP, 2) : '' }}"
                required>

              @error('car_MSRP')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-4 mb-5">
              <label for="purchase_source" class="form-label">แหล่งที่มา</label>
              <select id="purchase_source" name="purchase_source" class="form-select" required>
                <option value="">-- เลือกแหล่งที่มา --</option>
                <option value="MMTH" {{ $order->purchase_source == 'MMTH' ? 'selected' : '' }}>MMTH</option>
                <option value="OTHDealer" {{ $order->purchase_source == 'OTHDealer' ? 'selected' : '' }}>OTHDealer</option>
              </select>

              @error('purchase_source')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-4 mb-5">
              <label for="purchase_type" class="form-label">ประเภทการซื้อรถ</label>
              <select id="purchase_type" name="purchase_type" class="form-select" required>
                <option value="">-- เลือกประเภท --</option>
                <option value="TestDrive" {{ $order->purchase_type == 'TestDrive' ? 'selected' : '' }}>TestDrive</option>
                <option value="Retail" {{ $order->purchase_type == 'Retail' ? 'selected' : '' }}>Retail</option>
                <option value="ActivityCar" {{ $order->purchase_type == 'ActivityCar' ? 'selected' : '' }}>ActivityCar</option>
              </select>

              @error('purchase_type')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-4 mb-5">
              <label for="approver" class="form-label">ผู้อนุมัติ</label>
              <select id="approver" name="approver" class="form-select" readonly>
                @foreach ($approvers as $u)
                <option value="{{ $u->id }}" {{ $order->approver == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
              </select>

              @error('approver')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-12 mb-5">
              <label for="note" class="form-label">หมายเหตุ</label>
              <textarea id="note"
                class="form-control"
                name="note"
                rows="2">{{ $order->note }}</textarea>
            </div>

          </div>

          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">ยกเลิก</button>
            <button type="button" class="btn btn-primary btnUpdatePendingOrder">บันทึก</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>