<div class="modal fade editCarOrder" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h4 class="modal-title mb-2" id="CarOrderLabel">แก้ไขข้อมูลการสั่งซื้อ</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form
          action="{{ route('car-order.update', $order->id) }}"
          method="POST"
          enctype="multipart/form-data">
          @csrf
          @method('PUT')

          <div class="row">
            <div class="col-md-2 mb-5">
              <label for="type" class="form-label">ประเภทการสั่งรถ</label>
              <input id="type" type="text"
                class="form-control"
                value="{{ $order->type ?? '-' }}" disabled>
            </div>

            <div class="col-md-4 mb-5">
              <label for="model_id" class="form-label">รุ่นรถหลัก</label>
              <input id="model_id" type="text"
                class="form-control"
                value="{{ $order->model->Name_TH }}" disabled>
            </div>

            <div class="col-md-6 mb-5">
              <label for="subModel_id" class="form-label">รุ่นรถย่อย</label>
              <input id="subModel_id" type="text"
                class="form-control"
                value="{{ $order->subModel->detail }} - {{ $order->subModel->name }}" disabled>
            </div>

            <div class="col-12">
              <div id="fieldPurchase" class="row d-none">
                <div class="col-md-12 mb-5">
                  <label for="CusFullName" class="form-label">ชื่อ - นามสกุล ลูกค้า</label>
                  <input id="CusFullName" type="text"
                    class="form-control"
                    value="{{ $order->saleCus->customer->prefix->Name_TH ?? '' }} {{ $order->saleCus->customer->FirstName ?? '' }} {{ $order->saleCus->customer->LastName ?? '' }}" disabled>
                </div>
              </div>
            </div>

            <div class="col-md-4 mb-5">
              <label for="vin_number" class="form-label">Vin Number</label>
              <input id="vin_number" type="text"
                class="form-control @error('vin_number') is-invalid @enderror"
                name="vin_number" value="{{ $order->vin_number }}" required>

              @error('vin_number')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-4 mb-5">
              <label for="j_number" class="form-label">J-Number</label>
              <input id="j_number" type="text"
                class="form-control @error('j_number') is-invalid @enderror"
                name="j_number" value="{{ $order->j_number }}" required>

              @error('j_number')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-4 mb-5">
              <label for="engine_number" class="form-label">หมายเลขเครื่องยนต์</label>
              <input id="engine_number" type="text"
                class="form-control @error('engine_number') is-invalid @enderror"
                name="engine_number" value="{{ $order->engine_number }}" required>

              @error('engine_number')
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
                @foreach ($purchaseType as $t)
                <option value="{{ $t->id }}"
                  data-name="{{ $t->name }}"
                  {{ $order->purchase_type == $t->id ? 'selected' : '' }}>
                  {{ $t->name }}
                </option>
                @endforeach
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

            <div class="col-12">
              <div id="fieldTestDrive" class="row d-none">
                <div class="col-md-12 mb-5">
                  <label for="cam_testdrive" class="form-label">แคมเปญทดลองขับ</label>
                  <input id="cam_testdrive" name="cam_testdrive" type="text"
                    class="form-control"
                    value="{{ $order->cam_testdrive ?? '' }}">
                </div>
              </div>
            </div>

            <div class="col-md-12 mb-5">
              <label for="note" class="form-label">หมายเหตุ</label>
              <textarea id="note"
                class="form-control"
                name="note"
                rows="2">{{ $order->note }}</textarea>
            </div>

            <div class="col-md-4 mb-5">
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

            <div class="col-md-4 mb-5">
              <label for="car_status" class="form-label">สถานะรถ</label>
              <select id="car_status" name="car_status" class="form-select" required>
                <option value="">-- เลือกสถานะ --</option>
                <option value="Available" {{ $order->car_status == 'Available' ? 'selected' : '' }}>Available</option>
                <option value="Booked" {{ $order->car_status == 'Booked' ? 'selected' : '' }}>Booked</option>
                <option value="Delivered" {{ $order->car_status == 'Delivered' ? 'selected' : '' }}>Delivered</option>
              </select>

              @error('car_status')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-4 mb-5">
              <label for="order_status" class="form-label">สถานะ Car Order</label>
              <select id="order_status" name="order_status" class="form-select" required>
                <option value="">-- เลือกสถานะ --</option>
                @foreach ($orderStatus as $status)
                <option value="{{ $status->id }}"
                  data-name="{{ $status->name }}"
                  {{ $order->order_status == $status->id ? 'selected' : '' }}>
                  {{ $status->name }}
                </option>
                @endforeach
              </select>

              @error('order_status')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div id="fieldOnWeb" class="col-md-12 row d-none">
              <div class="col-md-3 mb-3">
                <label for="estimated_stock_date" class="form-label">วันที่คาดว่าสินค้ามาถึง</label>
                <input type="date" id="estimated_stock_date" name="estimated_stock_date" class="form-control" value="{{ $order->estimated_stock_date }}">
              </div>
            </div>

            <div id="fieldInvoice" class="col-md-3 mb-5 d-none">
              <label for="order_invoice_date" class="form-label">วันที่ซื้อ (วันที่ออกใบกำกับ)</label>
              <input type="date" id="order_invoice_date" name="order_invoice_date" class="form-control" value="{{ $order->order_invoice_date }}">
            </div>

            <div id="fieldStock" class="col-md-12 row d-none">
              <div class="col-md-3 mb-3">
                <label for="order_stock_date" class="form-label">วันที่สต็อก</label>
                <input type="date" id="order_stock_date" name="order_stock_date" class="form-control" value="{{ $order->order_stock_date }}">
              </div>
            </div>

          </div>

          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">ยกเลิก</button>
            <button type="button" class="btn btn-primary btnUpdateCarOrder">บันทึก</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>