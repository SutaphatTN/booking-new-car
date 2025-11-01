<div class="modal fade viewCarOrder" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h4 class="modal-title mb-2" id="viewCarOrderLabel">ข้อมูลการสั่งรถ</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-3 mb-5">
            <label for="model_id" class="form-label">รุ่นรถหลัก</label>
            <input class="form-control" type="text" value="{{ $order->model->Name_TH }}" disabled />
          </div>

          <div class="col-md-3 mb-5">
            <label for="subModel_id" class="form-label">รุ่นรถย่อย</label>
            <input class="form-control" type="text" value="{{ $order->subModel->name }}" disabled />
          </div>

          <div class="col-md-3 mb-5">
            <label for="vinNo" class="form-label">Vin Number</label>
            <input class="form-control" type="text" value="{{ $order->vinNo }}" disabled />
          </div>

          <div class="col-md-3 mb-5">
            <label for="order_code" class="form-label">รหัสการสั่งซื้อ</label>
            <input class="form-control" type="text" value="{{ $order->order_code }}" disabled />
          </div>

          <div class="col-md-2 mb-5">
            <label for="purchase_source" class="form-label">แหล่งที่มา</label>
            <input class="form-control" type="text" value="{{ $order->purchase_source }}" disabled />
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
            <label for="purchase_type" class="form-label">ประเภทการซื้อรถ</label>
            <input class="form-control" type="text" value="{{ $order->purchase_type }}" disabled />
          </div>

          <div class="col-md-3 mb-5">
            <label for="order_status" class="form-label">สถานะการสั่งซื้อ</label>
            <input class="form-control" type="text" value="{{ $order->order_status }}" disabled />
          </div>
          <div class="col-md-2 mb-5">
            <label for="order_date" class="form-label">วันที่สั่งซื้อ</label>
            <input class="form-control" type="text" value="{{ $order->format_order_date }}" disabled />
          </div>

          <div class="col-md-2 mb-5">
            <label for="order_invoice_date" class="form-label">วันที่ออกใบแจ้งหนี้</label>
            <input class="form-control" type="text" value="{{ $order->format_order_invoice_date }}" disabled />
          </div>

          <div class="col-md-2 mb-5">
            <label for="stock_id" class="form-label">Stock ID</label>
            <input class="form-control" type="text" value="{{ $order->format_stock_id }}" disabled />
          </div>

          <div class="col-md-2 mb-5">
            <label for="order_stock_date" class="form-label">วันที่สต็อค</label>
            <input class="form-control" type="text" value="{{ $order->format_order_stock_date }}" disabled />
          </div>

          <div class="col-md-2 mb-5">
            <label for="estimated_stock_date" class="form-label">วันที่รับรถเข้าสต็อค</label>
            <input class="form-control" type="text" value="{{ $order->format_estimated_stock_date }}" disabled />
          </div>

          <div class="col-md-2 mb-5">
            <label for="cancel_date" class="form-label">วันที่ยกเลิก</label>
            <input class="form-control" type="text" value="{{ $order->format_cancel_date }}" disabled />
          </div>

          <div class="col-md-2 mb-5">
            <label for="car_DNP" class="form-label">ราคาทุน</label>
            <input class="form-control text-end" type="text" value="{{ $order->car_DNP }}" disabled />
          </div>

          <div class="col-md-2 mb-5">
            <label for="car_MSRP" class="form-label">ราคาขาย</label>
            <input class="form-control text-end" type="text" value="{{ $order->car_MSRP }}" disabled />
          </div>

          <div class="col-md-2 mb-5">
            <label for="car_status" class="form-label">สถานะรถ</label>
            <input class="form-control" type="text" value="{{ $order->car_status }}" disabled />
          </div>

        </div>
      </div>
    </div>
  </div>
</div>