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
            <label class="form-label">ประเภท</label>
            <input class="form-control" type="text" value="{{ $order->type }}" disabled />
          </div>

          <div class="col-md-3 mb-5">
            <label class="form-label">รหัสการสั่งซื้อ</label>
            <input class="form-control" type="text" value="{{ $order->order_code }}" disabled />
          </div>

          <div class="col-md-3 mb-5">
            <label class="form-label">รุ่นรถหลัก</label>
            <input class="form-control" type="text" value="{{ $order->model->Name_TH }}" disabled />
          </div>

          <div class="col-md-4 mb-5">
            <label class="form-label">รุ่นรถย่อย</label>
            <input class="form-control" type="text" value="{{ $order->subModel->name }}" disabled />
          </div>

          <div class="col-md-4 mb-5">
            <label class="form-label">Vin Number</label>
            <input class="form-control" type="text" value="{{ $order->vin_number }}" disabled />
          </div>

          <div class="col-md-4 mb-5">
            <label class="form-label">J-Number</label>
            <input class="form-control" type="text" value="{{ $order->j_number }}" disabled />
          </div>

          <div class="col-md-4 mb-5">
            <label class="form-label">หมายเลขเครื่องยนต์</label>
            <input class="form-control" type="text" value="{{ $order->engine_number }}" disabled />
          </div>

          <div class="col-md-2 mb-5">
            <label class="form-label">Option</label>
            <input class="form-control" type="text" value="{{ $order->option }}" disabled />
          </div>

          <div class="col-md-2 mb-5">
            <label class="form-label">สี</label>
            <input class="form-control" type="text" value="{{ $order->color }}" disabled />
          </div>

          <div class="col-md-2 mb-5">
            <label class="form-label">ปี</label>
            <input class="form-control" type="text" value="{{ $order->year }}" disabled />
          </div>

          <div class="col-md-3 mb-5">
            <label class="form-label">ราคาทุน</label>
            <input class="form-control text-end" type="text"
              value="{{ $order->car_DNP !== null ? number_format($order->car_DNP, 2) : '-' }}"
              disabled />
          </div>

          <div class="col-md-3 mb-5">
            <label class="form-label">ราคาขาย</label>
            <input class="form-control text-end" type="text"
              value="{{ $order->car_MSRP !== null ? number_format($order->car_MSRP, 2) : '-' }}"
              disabled />
          </div>

          <div class="col-md-3 mb-5">
            <label class="form-label">แหล่งที่มา</label>
            <input class="form-control" type="text" value="{{ $order->purchase_source }}" disabled />
          </div>

          <div class="col-md-3 mb-5">
            <label class="form-label">ประเภทการซื้อรถ</label>
            <input class="form-control" type="text" value="{{ $order->purchase_type }}" disabled />
          </div>

          <div class="col-md-3 mb-5">
            <label class="form-label">สถานะรถ</label>
            <input class="form-control" type="text" value="{{ $order->car_status }}" disabled />
          </div>

          <div class="col-md-3 mb-5">
            <label class="form-label">สถานะ Car Order</label>
            <input class="form-control" type="text" value="{{ $order->orderStatus->name }}" disabled />
          </div>

          <div class="col-md-12 mb-5">
            <label class="form-label">หมายเหตุ</label>
            <textarea class="form-control" name="note" disabled>{{ $order->note }}</textarea>
          </div>

          <div class="col-md-2 mb-5">
            <label class="form-label">วันที่สั่งซื้อ</label>
            <input class="form-control" type="text" value="{{ $order->format_order_date }}" disabled />
          </div>

          <div class="col-md-2 mb-5">
            <label class="form-label">วันที่อนุมัติ</label>
            <input class="form-control" type="text" value="{{ $order->format_approver_date }}" disabled />
          </div>

          <div class="col-md-2 mb-5">
            <label class="form-label">วันที่สั่งซื้อในระบบ</label>
            <input class="form-control" type="text" value="{{ $order->format_system_date }}" disabled />
          </div>

          <div class="col-md-2 mb-5">
            <label class="form-label">วันที่ออกใบกำกับ</label>
            <input class="form-control" type="text" value="{{ $order->format_order_invoice_date }}" disabled />
          </div>


          <div class="col-md-2 mb-5">
            <label class="form-label">วันที่สต็อค</label>
            <input class="form-control" type="text" value="{{ $order->format_order_stock_date }}" disabled />
          </div>

          <div class="col-md-2 mb-5">
            <label class="form-label">วันที่รับรถเข้าสต็อค</label>
            <input class="form-control" type="text" value="{{ $order->format_estimated_stock_date }}" disabled />
          </div>




        </div>
      </div>
    </div>
  </div>
</div>