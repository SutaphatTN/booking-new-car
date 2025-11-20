<div class="modal fade inputCarOrder" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h4 class="modal-title mb-2" id="inputCarOrderLabel">เพิ่มข้อมูลการสั่งซื้อ</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form
          action="{{ route('car-order.store') }}"
          method="POST"
          enctype="multipart/form-data">
          @csrf

          <div class="row">
            <div class="col-md-6 mb-5">
              <label for="model_id" class="form-label">รุ่นรถหลัก</label>
              <select id="model_id" name="model_id" class="form-select @error('model_id') is-invalid @enderror" required>
                <option value="">-- เลือกรุ่นรถหลัก --</option>
                @foreach ($model as $m)
                <option value="{{ @$m->id }}">{{ @$m->Name_TH }}</option>
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
                <option value="">-- เลือกรุ่นรถย่อย --</option>
              </select>

              @error('subModel_id')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-5 mb-5">
              <label for="vin_number" class="form-label">Vin Number</label>
              <input id="vin_number" type="text"
                class="form-control @error('vin_number') is-invalid @enderror"
                name="vin_number" required>

              @error('vin_number')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-5 mb-5">
              <label for="engine_number" class="form-label">หมายเลขเครื่องยนต์</label>
              <input id="engine_number" type="text"
                class="form-control @error('engine_number') is-invalid @enderror"
                name="engine_number" required>

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
                name="option" required>

              @error('option')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-3 mb-5">
              <label for="color" class="form-label">สี</label>
              <input id="color" type="text"
                class="form-control @error('color') is-invalid @enderror"
                name="color" required>

              @error('color')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-3 mb-5">
              <label for="year" class="form-label">ปี</label>
              <input id="year" type="text"
                class="form-control @error('year') is-invalid @enderror"
                name="year" required>

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
                name="car_DNP" required>

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
                name="car_MSRP" required>

              @error('car_MSRP')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-3 mb-5">
              <label for="purchase_source" class="form-label">แหล่งที่มา</label>
              <select id="purchase_source" name="purchase_source" class="form-select" required>
                <option value="">-- เลือกแหล่งที่มา --</option>
                <option value="MMTH">MMTH</option>
                <option value="OTHDealer">OTHDealer</option>
              </select>

              @error('purchase_source')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-3 mb-5">
              <label for="purchase_type" class="form-label">ประเภทการซื้อรถ</label>
              <select id="purchase_type" name="purchase_type" class="form-select" required>
                <option value="">-- เลือกประเภท --</option>
                <option value="TestDrive">TestDrive</option>
                <option value="Retail">Retail</option>
                <option value="ActivityCar">ActivityCar</option>
              </select>

              @error('purchase_type')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-3 mb-5">
              <label for="car_status" class="form-label">สถานะรถ</label>
              <select id="car_status" name="car_status" class="form-select" required>
                <option value="">-- เลือกสถานะรถ --</option>
                <option value="Null">Null</option>
                <option value="Book">Book</option>
                <option value="Send">Send</option>
              </select>

              @error('car_status')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-3 mb-5">
              <label for="order_status" class="form-label">สถานะ Car Order</label>
              <select id="order_status" name="order_status" class="form-select" required>
                <option value="">-- เลือกสถานะ --</option>
                @foreach ($orderStatus as $order)
                <option value="{{ @$order->id }}" data-name="{{ $order->name }}">{{ @$order->name }}</option>
                @endforeach
              </select>
              
              @error('order_status')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div id="fieldInvoice" class="col-md-3 mb-5 d-none">
              <label for="order_invoice_date" class="form-label">วันที่ออกใบกำกับ</label>
              <input type="date" id="order_invoice_date" name="order_invoice_date" class="form-control">
            </div>

            <div id="fieldStock" class="col-md-12 row d-none">
              <div class="col-md-3 mb-3">
                <label for="order_stock_date" class="form-label">วันที่สต็อก</label>
                <input type="date" id="order_stock_date" name="order_stock_date" class="form-control">
              </div>

              <div class="col-md-3 mb-3">
                <label for="estimated_stock_date" class="form-label">วันที่รับรถเข้าสต็อค</label>
                <input type="date" id="estimated_stock_date" name="estimated_stock_date" class="form-control">
              </div>
            </div>

          </div>

          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">ยกเลิก</button>
            <button type="button" class="btn btn-primary btnStoreCarOrder">บันทึก</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>