<div class="modal fade viewCarOrder" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h4 class="modal-title mb-2" id="viewCarOrderLabel">ข้อมูลรถ</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">

          <div class="col-md-2 mb-5">
            <label for="type" class="form-label">ประเภทการสั่งรถ</label>
            <input id="type" class="form-control" type="text" value="{{ $order->type }}" disabled />
          </div>

          <div class="col-md-2 mb-5">
            <label for="order_code" class="form-label">รหัสการสั่งซื้อ</label>
            <input id="order_code" class="form-control" type="text" value="{{ $order->order_code }}" disabled />
          </div>

          <div class="col-md-3 mb-5">
            <label for="model_id" class="form-label">รุ่นรถหลัก</label>
            <input id="model_id" class="form-control" type="text" value="{{ $order->model->Name_TH }}" disabled />
          </div>

          <div class="col-md-5 mb-5">
            <label for="subModel_id" class="form-label">รุ่นรถย่อย</label>
            <input id="subModel_id" class="form-control" type="text" value="{{ $order->subModel->detail }} - {{ $order->subModel->name }}" disabled />
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

          <div class="col-md-3 mb-5">
            <label for="vin_number" class="form-label">Vin Number</label>
            <input id="vin_number" class="form-control" type="text" value="{{ $order->vin_number }}" disabled />
          </div>

          <div class="col-md-3 mb-5">
            <label for="j_number" class="form-label">J-Number</label>
            <input id="j_number" class="form-control" type="text" value="{{ $order->j_number }}" disabled />
          </div>

          <div class="col-md-4 mb-5">
            <label for="engine_number" class="form-label">หมายเลขเครื่องยนต์</label>
            <input id="engine_number" class="form-control" type="text" value="{{ $order->engine_number }}" disabled />
          </div>

          <div class="col-md-2 mb-5">
            <label for="option" class="form-label">Option</label>
            <input id="option" class="form-control" type="text" value="{{ $order->option }}" disabled />
          </div>

          @if(auth()->user()->brand == 2)
          <div class="col-md-2 mb-5">
            <label for="gwm_color" class="form-label">สี</label>
            <input id="gwm_color" class="form-control" type="text" value="{{ $order->gwmColor->name ?? '-' }}" disabled />
          </div>

          <div class="col-md-2 mb-5">
            <label for="interior_color" class="form-label">สีภายใน</label>
            <input id="interior_color" class="form-control" type="text" value="{{ $order->interiorColor->name ?? '-' }}" disabled />
          </div>

          <div class="col-md-2 mb-5">
            <label for="year" class="form-label">ปี</label>
            <input id="year" class="form-control" type="text" value="{{ $order->year }}" disabled />
          </div>

          <div class="col-md-2 mb-5">
            <label for="car_DNP" class="form-label">ราคาทุน</label>
            <input id="car_DNP" class="form-control text-end" type="text"
              value="{{ $order->car_DNP !== null ? number_format($order->car_DNP, 2) : '-' }}"
              disabled />
          </div>

          <div class="col-md-2 mb-5">
            <label for="car_MSRP" class="form-label">ราคาขาย</label>
            <input id="car_MSRP" class="form-control text-end" type="text"
              value="{{ $order->car_MSRP !== null ? number_format($order->car_MSRP, 2) : '-' }}"
              disabled />
          </div>

          <div class="col-md-2 mb-5">
            <label for="RI" class="form-label">RI</label>
            <input id="RI" class="form-control text-end" type="text"
              value="{{ $order->RI !== null ? number_format($order->RI, 2) : '-' }}"
              disabled />
          </div>
          @else
          <div class="col-md-2 mb-5">
            <label for="color" class="form-label">สี</label>
            <input id="color" class="form-control" type="text" value="{{ $order->color }}" disabled />
          </div>

          <div class="col-md-2 mb-5">
            <label for="year" class="form-label">ปี</label>
            <input id="year" class="form-control" type="text" value="{{ $order->year }}" disabled />
          </div>

          <div class="col-md-3 mb-5">
            <label for="car_DNP" class="form-label">ราคาทุน</label>
            <input id="car_DNP" class="form-control text-end" type="text"
              value="{{ $order->car_DNP !== null ? number_format($order->car_DNP, 2) : '-' }}"
              disabled />
          </div>

          <div class="col-md-3 mb-5">
            <label for="car_MSRP" class="form-label">ราคาขาย</label>
            <input id="car_MSRP" class="form-control text-end" type="text"
              value="{{ $order->car_MSRP !== null ? number_format($order->car_MSRP, 2) : '-' }}"
              disabled />
          </div>

          <div class="col-md-2 mb-5">
            <label for="RI" class="form-label">RI</label>
            <input id="RI" class="form-control text-end" type="text"
              value="{{ $order->RI !== null ? number_format($order->RI, 2) : '-' }}"
              disabled />
          </div>
          @endif

          <div class="col-md-3 mb-5">
            <label for="purchase_source" class="form-label">แหล่งที่มา</label>
            <input id="purchase_source" class="form-control" type="text" value="{{ $order->purchase_source }}" disabled />
          </div>

          <div class="col-md-3 mb-5">
            <label for="purchase_type" class="form-label">ประเภทการซื้อรถ</label>
            <input id="purchase_type" class="form-control" type="text" value="{{ $order->purchaseType->name }}" disabled />
          </div>

          <div class="col-md-3 mb-5">
            <label for="car_status" class="form-label">สถานะรถ</label>
            <input id="car_status" class="form-control" type="text" value="{{ $order->car_status }}" disabled />
          </div>

          <div class="col-md-3 mb-5">
            <label for="order_status" class="form-label">สถานะ Car Order</label>
            <input id="order_status" class="form-control" type="text" value="{{ $order->orderStatus->name }}" disabled />
          </div>

          @if($order->purchase_type == 1)
          <div class="col-6">
            <div class="col-md-12 mb-5">
              <label class="form-label">แคมเปญทดลองขับ</label>
              <input type="text"
                class="form-control"
                value="{{ $order->cam_testdrive ?? '' }}" disabled>
            </div>
          </div>

          <div class="col-6">
            <div class="col-md-12 mb-5">
              <label class="form-label">เลขไมล์รถทดลองขับ</label>
              <input type="text"
                class="form-control"
                value="{{ $order->mileage_test ?? '' }}" disabled>
            </div>
          </div>
          @endif

          <div class="col-md-12 mb-5">
            <label for="note_accessory" class="form-label">ประดับยนต์ของรถ</label>
            <textarea id="note_accessory" class="form-control" name="note_accessory" disabled>{{ $order->note_accessory }}</textarea>
          </div>

          <div class="col-md-12 mb-5">
            <label for="note" class="form-label">หมายเหตุ</label>
            <textarea id="note" class="form-control" name="note" disabled>{{ $order->note }}</textarea>
          </div>

          <div class="col-md-3 mb-5">
            <label for="order_date" class="form-label">วันที่สั่งซื้อ</label>
            <input id="order_date" class="form-control" type="text" value="{{ $order->format_order_date }}" disabled />
          </div>

          <div class="col-md-3 mb-5">
            <label for="approver_date" class="form-label">วันที่อนุมัติ</label>
            <input id="approver_date" class="form-control" type="text" value="{{ $order->format_approver_date }}" disabled />
          </div>

          <div class="col-md-3 mb-5">
            <label for="system_date" class="form-label">วันที่สั่งซื้อในระบบ</label>
            <input id="system_date" class="form-control" type="text" value="{{ $order->format_system_date }}" disabled />
          </div>

          <div class="col-md-3 mb-3">
            <label for="estimated_stock_date" class="form-label">วันที่คาดว่าสินค้ามาถึง</label>
            <input id="estimated_stock_date" class="form-control" type="text" value="{{ $order->format_estimated_stock_date }}" disabled />
          </div>

          <div class="col-md-3 mb-5">
            <label for="order_invoice_date" class="form-label">วันที่ซื้อ (วันที่ออกใบกำกับ)</label>
            <input id="order_invoice_date" class="form-control" type="text" value="{{ $order->format_order_invoice_date }}" disabled />
          </div>

          <div class="col-md-3 mb-5">
            <label for="order_stock_date" class="form-label">วันที่สต็อค</label>
            <input id="order_stock_date" class="form-control" type="text" value="{{ $order->format_order_stock_date }}" disabled />
          </div>

        </div>
      </div>
    </div>
  </div>
</div>