<div class="modal fade inputCarOrder" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--input">

      {{-- Header --}}
      <div class="modal-header mf-header mf-header--input px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-package fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">เพิ่มข้อมูลการสั่งซื้อ</h6>
            <small class="text-white mf-hd-sub">Add Car Order</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">
        <form action="{{ route('car-order.store') }}" method="POST" enctype="multipart/form-data">
          @csrf

          <div class="searchPurchaseCus"></div>

          {{-- Section 1 : ข้อมูลการสั่งซื้อ --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon indigo">
                <i class="bx bx-list-ul"></i>
              </div>
              <span class="mf-section-title">ข้อมูลการสั่งซื้อ</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-3">
                  <label for="type" class="mf-label form-label">
                    <i class="bx bx-category"></i> ประเภทการสั่งรถ
                  </label>
                  <select id="type" name="type" class="form-select" required>
                    <option value="">-- เลือก --</option>
                    <option value="customer">Customer</option>
                    <option value="stock">Stock</option>
                    <option value="auction">Auction</option>
                  </select>
                </div>

                <div id="fieldCountOrder" class="col-md-3 d-none">
                  <label for="count_order" class="mf-label form-label">
                    <i class="bx bx-hash"></i> จำนวนที่สั่ง (คัน)
                  </label>
                  <input id="count_order" type="number" class="form-control" name="count_order" value="1"
                    min="1">
                </div>

                <div id="wrapPurchaseSource" class="col-md-5">
                  <label for="purchase_source" class="mf-label form-label">
                    <i class="bx bx-store"></i> แหล่งที่มา
                  </label>
                  <select id="purchase_source" name="purchase_source" class="form-select" required>
                    <option value="">-- เลือกแหล่งที่มา --</option>
                    @if (auth()->user()->brand == 1)
                      <option value="MMTH">MMTH</option>
                    @endif
                    @if (auth()->user()->brand == 2)
                      <option value="GWM">GWM</option>
                    @endif
                    @if (auth()->user()->brand == 3)
                      <option value="WULING">WULING</option>
                    @endif
                    <option value="OTHDealer">OTHDealer</option>
                  </select>
                </div>

                <div id="wrapPurchaseType" class="col-md-4">
                  <label for="purchase_type" class="mf-label form-label">
                    <i class="bx bx-transfer"></i> ประเภทการซื้อรถ
                  </label>
                  <select id="purchase_type" name="purchase_type" class="form-select" required>
                    <option value="">-- เลือกประเภท --</option>
                    @foreach ($purchaseType as $t)
                      <option value="{{ @$t->id }}">{{ @$t->name }}</option>
                    @endforeach
                  </select>
                </div>

                <input type="hidden" name="salecar_id" id="salecar_id">
                <div class="col-12 mt-6">
                  <div id="fieldPurchase" class="row g-3 d-none">
                    <div class="col-md-5">
                      <label class="mf-label form-label" for="purchaseCus">
                        <i class="bx bx-search"></i> ค้นหาข้อมูลการจองของลูกค้า
                      </label>
                      <div class="input-group">
                        <input id="purchaseCus" type="text" class="form-control" name="purchaseCus"
                          placeholder="พิมพ์ข้อมูลลูกค้า">
                        <span class="btn btn-outline-secondary btnPurchaseCus">
                          <i class="bx bx-search"></i>
                        </span>
                      </div>
                    </div>
                    <div class="col-md-7">
                      <label for="purchaseCusName" class="mf-label form-label">
                        <i class="bx bx-user"></i> ชื่อ - นามสกุล
                      </label>
                      <input id="purchaseCusName" type="text" class="form-control w-100" readonly>
                    </div>
                    <div id="modelError" class="text-danger small d-none mt-3" style="margin-top:-4px;margin-bottom:2px;">
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>

          {{-- Section 2 : ข้อมูลรุ่นรถ --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon sky">
              <i class="bx bx-car"></i>
              </div>
              <span class="mf-section-title">ข้อมูลรุ่นรถ</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-5">
                  <label for="model_id" class="mf-label form-label">
                    <i class="bx bx-car ci-sky"></i> รุ่นรถหลัก
                  </label>
                  <select id="model_id" name="model_id" class="form-select @error('model_id') is-invalid @enderror"
                    required>
                    <option value="">-- เลือกรุ่นรถหลัก --</option>
                    @foreach ($model as $m)
                      <option value="{{ $m->id }}">{{ $m->Name_TH }}</option>
                    @endforeach
                  </select>
                </div>

                <div class="col-md-7">
                  <label for="subModel_id" class="mf-label form-label">
                    <i class="bx bx-barcode ci-sky"></i> รุ่นรถย่อย
                  </label>
                  <select id="subModel_id" name="subModel_id"
                    class="form-select @error('subModel_id') is-invalid @enderror" required>
                    <option value="">-- เลือกรุ่นรถย่อย --</option>
                  </select>
                </div>

              </div>
            </div>
          </div>

          {{-- Section 3 : รายละเอียดรถและราคา --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon amber">
                <i class="bx bx-money"></i>
              </div>
              <span class="mf-section-title">รายละเอียดรถและราคา</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                @if (auth()->user()->brand == 2)
                  <div class="col-md-4">
                    <label for="gwm_color" class="mf-label form-label">
                      <i class="bx bx-palette ci-amber"></i> สี
                    </label>
                    <select id="gwm_color" name="gwm_color" class="form-select" required>
                      <option value="">-- เลือกสี --</option>
                    </select>
                  </div>

                  <div class="col-md-4">
                    <label for="interior_color" class="mf-label form-label">
                      <i class="bx bx-color-fill ci-amber"></i> สีภายใน
                    </label>
                    <select id="interior_color" name="interior_color" class="form-select">
                      <option value="">-- เลือกสี --</option>
                      @foreach ($interiorColor as $t)
                        <option value="{{ @$t->id }}">{{ @$t->name }}</option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-4">
                    <label for="pricelist_year" class="mf-label form-label">
                      <i class="bx bx-calendar ci-amber"></i> ปี
                    </label>
                    <select id="pricelist_year" name="year" class="form-select" required disabled>
                      <option value="">-- เลือกปี --</option>
                    </select>
                  </div>

                  <div class="col-md-4">
                    <label for="car_DNP" class="mf-label form-label">
                      <i class="bx bx-purchase-tag ci-amber"></i> ราคาทุน
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="car_DNP" type="text"
                        class="form-control text-end money-input @error('car_DNP') is-invalid @enderror"
                        name="car_DNP" readonly>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <label for="car_MSRP" class="mf-label form-label">
                      <i class="bx bx-receipt ci-amber"></i> ราคาขาย
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="car_MSRP" type="text"
                        class="form-control text-end money-input @error('car_MSRP') is-invalid @enderror"
                        name="car_MSRP" readonly>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <label for="approver" class="mf-label form-label">
                      <i class="bx bx-user-check ci-amber"></i> ผู้อนุมัติ
                    </label>
                    <select id="approver" name="approver" class="form-select" required>
                      <option value="">-- เลือกผู้อนุมัติ --</option>
                      @foreach ($approvers as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                      @endforeach
                    </select>
                  </div>
                @elseif(auth()->user()->brand == 3)
                  <div class="col-md-4">
                    <label for="gwm_color" class="mf-label form-label">
                      <i class="bx bx-palette ci-amber"></i> สี
                    </label>
                    <select id="gwm_color" name="gwm_color" class="form-select" required>
                      <option value="">-- เลือกสี --</option>
                    </select>
                  </div>

                  <div class="col-md-4">
                    <label for="pricelist_year" class="mf-label form-label">
                      <i class="bx bx-calendar ci-amber"></i> ปี
                    </label>
                    <select id="pricelist_year" name="year" class="form-select" required disabled>
                      <option value="">-- เลือกปี --</option>
                    </select>
                  </div>

                  <div class="col-md-4">
                    <label for="car_DNP" class="mf-label form-label">
                      <i class="bx bx-purchase-tag ci-amber"></i> ราคาทุน
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="car_DNP" type="text"
                        class="form-control text-end money-input @error('car_DNP') is-invalid @enderror"
                        name="car_DNP" readonly>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <label for="car_MSRP" class="mf-label form-label">
                      <i class="bx bx-receipt ci-amber"></i> ราคาขาย
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="car_MSRP" type="text"
                        class="form-control text-end money-input @error('car_MSRP') is-invalid @enderror"
                        name="car_MSRP" readonly>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <label for="approver" class="mf-label form-label">
                      <i class="bx bx-user-check ci-amber"></i> ผู้อนุมัติ
                    </label>
                    <select id="approver" name="approver" class="form-select" required>
                      <option value="">-- เลือกผู้อนุมัติ --</option>
                      @foreach ($approvers as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                      @endforeach
                    </select>
                  </div>
                @else
                  <div class="col-md-3">
                    <label for="pricelist_color" class="mf-label form-label">
                      <i class="bx bx-palette ci-amber"></i> ประเภทสี
                    </label>
                    <select id="pricelist_color" name="type_color" class="form-select" required disabled>
                      <option value="">-- เลือก --</option>
                    </select>
                  </div>

                  <div class="col-md-3">
                    <label for="pricelist_year" class="mf-label form-label">
                      <i class="bx bx-calendar ci-amber"></i> ปี
                    </label>
                    <select id="pricelist_year" name="year" class="form-select" required disabled>
                      <option value="">-- เลือก --</option>
                    </select>
                  </div>

                  <div class="col-md-3">
                    <label for="color" class="mf-label form-label">
                      <i class="bx bx-color-fill ci-amber"></i> สี
                    </label>
                    <input id="color" type="text" class="form-control @error('color') is-invalid @enderror"
                      name="color" required>
                  </div>

                  <div class="col-md-3">
                    <label for="option" class="mf-label form-label">
                      <i class="bx bx-list-check ci-amber"></i> Option
                    </label>
                    <input id="option" type="text" class="form-control @error('option') is-invalid @enderror"
                      name="option" readonly>
                  </div>

                  <div class="col-md-3">
                    <label for="car_DNP" class="mf-label form-label">
                      <i class="bx bx-purchase-tag ci-amber"></i> ราคาทุน
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="car_DNP" type="text"
                        class="form-control text-end money-input @error('car_DNP') is-invalid @enderror"
                        name="car_DNP" readonly>
                    </div>
                  </div>

                  <div class="col-md-3">
                    <label for="car_MSRP" class="mf-label form-label">
                      <i class="bx bx-receipt ci-amber"></i> ราคาขาย
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="car_MSRP" type="text"
                        class="form-control text-end money-input @error('car_MSRP') is-invalid @enderror"
                        name="car_MSRP" readonly>
                    </div>
                  </div>

                  <div class="col-md-3">
                    <label for="RI" class="mf-label form-label">
                      <i class="bx bx-coin-stack ci-amber"></i> RI
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="RI" type="text"
                        class="form-control text-end money-input @error('RI') is-invalid @enderror" name="RI"
                        readonly>
                    </div>
                  </div>

                  <div class="col-md-3">
                    <label for="WS" class="mf-label form-label">
                      <i class="bx bx-coin-stack ci-amber"></i> WS
                    </label>
                    <div class="input-group">
                      <span class="input-group-text ig-amber">฿</span>
                      <input id="WS" type="text"
                        class="form-control text-end money-input @error('WS') is-invalid @enderror" name="WS"
                        readonly>
                    </div>
                  </div>

                  <div class="col-md-5">
                    <label for="approver" class="mf-label form-label">
                      <i class="bx bx-user-check ci-amber"></i> ผู้อนุมัติ
                    </label>
                    <select id="approver" name="approver" class="form-select" required>
                      <option value="">-- เลือกผู้อนุมัติ --</option>
                      @foreach ($approvers as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                      @endforeach
                    </select>
                  </div>
                @endif

              </div>
            </div>
          </div>

          {{-- Section 4 : หมายเหตุ --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon rose">
                <i class="bx bx-note"></i>
              </div>
              <span class="mf-section-title">หมายเหตุ</span>
            </div>
            <div class="mf-section-body">
              <label for="note" class="mf-label form-label">
                <i class="bx bx-info-circle text-primary" data-bs-toggle="tooltip" data-bs-trigger="click"
                  data-bs-placement="right"
                  title="กรณีสั่งให้ลูกค้า ให้ใส่ข้อมูล ชื่อลูกค้า / วันที่ PO (ถ้ามี) / จำนวนเงินจอง ทุกครั้ง">
                </i> หมายเหตุ
              </label>
              <textarea id="note" class="form-control" name="note" rows="2"
                placeholder="กรณีสั่งให้ลูกค้า ให้ใส่ข้อมูล ชื่อลูกค้า / วันที่ PO / จำนวนเงินจอง ทุกครั้ง">{{ old('note') }}</textarea>
            </div>
          </div>

          {{-- Actions --}}
          <div class="d-flex justify-content-end gap-2 pt-1">
            <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
              <i class="bx bx-x me-1"></i>ยกเลิก
            </button>
            <button type="button" class="btn btn-primary px-5 btnStoreCarOrder">
              <i class="bx bx-save me-1"></i>บันทึก
            </button>
          </div>

        </form>
      </div>

    </div>
  </div>
</div>

@include('car-order.pending.search-sale-customer.search')

<style>
  .bx-info-circle {
    cursor: pointer;
  }
</style>
