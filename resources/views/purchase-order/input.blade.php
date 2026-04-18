@extends('layouts/contentNavbarLayout')
@section('title', 'Add Purchase Order')

@section('page-style')
  @vite(['resources/css/app.css'])
@endsection

@section('page-script')
  @vite(['resources/assets/js/purchase-order.js'])
@endsection

@section('content')
  <div id="searchCustomer"></div>

  {{-- Page Title --}}
  <div class="pur-page-title">
    <div class="pur-page-icon">
      <i class="bx bx-plus-circle"></i>
    </div>
    <div>
      <h5 class="pur-page-name">เพิ่มข้อมูลการจอง</h5>
    </div>
  </div>

  <form action="{{ route('purchase-order.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="row g-4">

      <div class="col-md-6">

        {{-- SECTION 1 : ข้อมูลลูกค้า --}}
        <div class="po-section">
          <div class="po-section-header">
            <div class="po-section-icon sky"><i class="bx bx-user"></i></div>
            <h6 class="po-section-title">ข้อมูลลูกค้า</h6>
          </div>
          <div class="po-section-body">

            {{-- แถว 1 : ค้นหา --}}
            <div class="row g-3 mb-3">
              <div class="col-md-5">
                <label class="po-label" for="customerSearch"><i class='bx bx-search-alt'></i> ค้นหาข้อมูลลูกค้า</label>
                <div class="input-group">
                  <input id="customerSearch" type="text" class="form-control" name="customerSearch"
                    placeholder="พิมพ์ชื่อ/เลขบัตร">
                  <button type="button" class="btn btnSearchCustomer px-3 border">
                    <i class="bx bx-search me-1"></i> ค้นหา
                  </button>
                </div>
              </div>

              <div class="col-md-4">
                <label class="po-label" for="payment_mode"><i class='bx bx-wallet'></i> ประเภทการซื้อ</label>
                <select id="payment_mode" name="payment_mode" class="form-select" required>
                  <option value="">— เลือก —</option>
                  <option value="finance" {{ old('payment_mode') == 'finance' ? 'selected' : '' }}>ผ่อนชำระ</option>
                  <option value="non-finance" {{ old('payment_mode') == 'non-finance' ? 'selected' : '' }}>เงินสด</option>
                </select>
              </div>

              <div class="col-md-3">
                <div class="po-label"><i class='bx bx-transfer-alt'></i> รถเทิร์น</div>
                <div class="yn-group mt-1">
                  <input class="@error('hasTurnCar') is-invalid @enderror" type="radio" name="hasTurnCar"
                    id="turnCarYes" value="yes" {{ old('hasTurnCar') == 'yes' ? 'checked' : '' }}>
                  <label for="turnCarYes">มี</label>

                  <input class="@error('hasTurnCar') is-invalid @enderror" type="radio" name="hasTurnCar" id="turnCarNo"
                    value="no" {{ old('hasTurnCar') == 'no' ? 'checked' : '' }}>
                  <label for="turnCarNo">ไม่มี</label>
                </div>
                @error('hasTurnCar')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <input type="hidden" id="CusID" name="CusID">
            <input id="customerName" type="hidden">
            <input id="customerID" type="hidden">
            <input id="customerPhone" type="hidden">

            {{-- แถว 2 : ข้อมูลลูกค้า (auto-fill) --}}
            <div class="customer-info-row mb-3">
              <div class="row g-3">
                <div class="col-12">
                  <div class="po-label"><i class='bx bxs-user'></i> ชื่อ - นามสกุล</div>
                  <div class="info-val empty" id="customerName-display">— ยังไม่ได้เลือกลูกค้า —</div>
                </div>
                <div class="col-md-6">
                  <div class="po-label"><i class='bx bx-id-card'></i> เลขบัตรประชาชน</div>
                  <div class="info-val empty" id="customerID-display">—</div>
                </div>
                <div class="col-md-6">
                  <div class="po-label"><i class='bx bx-phone'></i> เบอร์โทรศัพท์</div>
                  <div class="info-val empty" id="customerPhone-display">—</div>
                </div>
              </div>
            </div>

          </div>
        </div>

        {{-- SECTION 2 : ข้อมูลผู้ขาย --}}
        <div class="po-section">
          <div class="po-section-header">
            <div class="po-section-icon indigo"><i class="bx bx-user-pin"></i></div>
            <h6 class="po-section-title">ข้อมูลผู้ขาย</h6>
          </div>
          <div class="po-section-body">
            <div class="row g-3 pb-2">

              {{-- ผู้ขาย --}}
              @if (auth()->user()->role == 'sale')
                <input type="hidden" name="SaleID" value="{{ Auth::user()->id }}">
                <div class="col-md-6">
                  <div class="po-label"><i class='bx bx-user'></i> ชื่อ - นามสกุล ผู้ขาย</div>
                  <div class="info-pill">
                    <i class="bx bx-check-circle me-2" style="color:#10b981;"></i>
                    {{ Auth::user()->name }}
                  </div>
                </div>
              @else
                <div class="col-md-6">
                  <label class="po-label" for="SaleID"><i class='bx bx-user'></i> ชื่อ - นามสกุล ผู้ขาย</label>
                  <select id="SaleID" name="SaleID" class="form-select">
                    <option value="">— เลือกผู้ขาย —</option>
                    @foreach ($saleUser as $s)
                      <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                  </select>
                  @error('SaleID')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                </div>
              @endif

              {{-- ประเภทการขาย --}}
              <div class="col-md-6">
                <label class="po-label" for="type_sale"><i class='bx bx-tag'></i> ประเภทการขาย</label>
                <select id="type_sale" name="type_sale" class="form-select" required>
                  <option value="">— เลือก —</option>
                  @foreach ($typeSale as $item)
                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                  @endforeach
                </select>
              </div>

              {{-- แหล่งที่มา --}}
              <div class="col-md-6">
                <label class="po-label" for="type"><i class='bx bx-map-pin'></i> แหล่งที่มา</label>
                <select id="type" name="type" class="form-select" required>
                  <option value="">— เลือก —</option>
                  @foreach ($type as $item)
                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                  @endforeach
                </select>
              </div>

              {{-- วันที่จอง --}}
              <div class="col-md-6">
                <label class="po-label" for="BookingDate"><i class='bx bx-calendar'></i> วันที่จอง</label>
                <input id="BookingDate" type="date" name="BookingDate"
                  class="form-control @error('BookingDate') is-invalid @enderror" required>
                @error('BookingDate')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

            </div>
          </div>
        </div>

      </div>{{-- จบส่วนข้อมูลลูกค้าและผู้ขาย --}}

      <div class="col-md-6">

        {{-- SECTION 3 : ข้อมูลรถ --}}
        <div class="po-section">
          <div class="po-section-header">
            <div class="po-section-icon emerald"><i class="bx bx-car"></i></div>
            <h6 class="po-section-title">ข้อมูลรถ</h6>
          </div>
          <div class="po-section-body">
            <div class="row g-3 pb-2">

              {{-- GWM --}}
              @if (auth()->user()->brand == 2)
                <div class="col-md-6">
                  <label class="po-label" for="model_id"><i class='bx bx-cube'></i> รุ่นรถหลัก</label>
                  <select id="model_id" name="model_id" class="form-select" required>
                    <option value="">— เลือกรุ่นรถหลัก —</option>
                    @foreach ($model as $m)
                      <option value="{{ $m->id }}">{{ $m->Name_TH }}</option>
                    @endforeach
                  </select>
                  @error('model_id')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-6">
                  <label class="po-label" for="subModel_id"><i class='bx bx-list-ul'></i> รุ่นรถย่อย</label>
                  <select id="subModel_id" name="subModel_id"
                    class="form-select @error('subModel_id') is-invalid @enderror" required>
                    <option value="">— เลือกรุ่นรถย่อย —</option>
                  </select>
                  @error('subModel_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-4">
                  <label for="pricelist_year" class="po-label"><i class='bx bx-calendar-alt'></i> ปี</label>
                  <select id="pricelist_year" name="Year" class="form-select @error('Year') is-invalid @enderror"
                    required disabled>
                    <option value="">— เลือกปี —</option>
                  </select>
                  @error('Year')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-4">
                  <label class="po-label" for="gwm_color"><i class='bx bx-palette'></i> สี</label>
                  <select id="gwm_color" name="gwm_color" class="form-select" required>
                    <option value="">— เลือกสี —</option>
                  </select>
                  @error('gwm_color')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-4">
                  <label class="po-label" for="interior_color"><i class='bx bx-paint'></i> สีภายใน</label>
                  <select id="interior_color" name="interior_color" class="form-select">
                    <option value="">— เลือกสี —</option>
                    @foreach ($interiorColor as $t)
                      <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                  </select>
                  @error('interior_color')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                </div>
                {{-- Mitsubishi --}}
              @elseif (auth()->user()->brand == 1)
                <div class="col-md-6">
                  <label class="po-label" for="model_id"><i class='bx bx-cube'></i> รุ่นรถหลัก</label>
                  <select id="model_id" name="model_id" class="form-select" required>
                    <option value="">— เลือกรุ่นรถหลัก —</option>
                    @foreach ($model as $m)
                      <option value="{{ $m->id }}">{{ $m->Name_TH }}</option>
                    @endforeach
                  </select>
                  @error('model_id')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-6">
                  <label class="po-label" for="subModel_id"><i class='bx bx-list-ul'></i> รุ่นรถย่อย</label>
                  <select id="subModel_id" name="subModel_id"
                    class="form-select @error('subModel_id') is-invalid @enderror" required>
                    <option value="">— เลือกรุ่นรถย่อย —</option>
                  </select>
                  @error('subModel_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-3">
                  <label for="pricelist_color" class="po-label"><i class='bx bx-droplet'></i> ประเภทสี</label>
                  <select id="pricelist_color" name="type_color" class="form-select" required disabled>
                    <option value="">— เลือก —</option>
                  </select>
                </div>
                <div class="col-md-3">
                  <label for="pricelist_year" class="po-label"><i class='bx bx-calendar-alt'></i> ปี</label>
                  <select id="pricelist_year" name="Year" class="form-select" required disabled>
                    <option value="">— ปี —</option>
                  </select>
                </div>
                <div class="col-md-2">
                  <label class="po-label" for="option"><i class="bx bx-code-block"></i> Option</label>
                  <input id="option" type="text" class="form-control" name="option" readonly>
                </div>
                <div class="col-md-4">
                  <label class="po-label" for="Color"><i class='bx bx-palette'></i> สี</label>
                  <input id="Color" type="text" class="form-control @error('Color') is-invalid @enderror"
                    name="Color" required>
                  @error('Color')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                {{-- Wuling / Others --}}
              @else
                <div class="col-md-6">
                  <label class="po-label" for="model_id"><i class='bx bx-cube'></i> รุ่นรถหลัก</label>
                  <select id="model_id" name="model_id" class="form-select" required>
                    <option value="">— เลือกรุ่นรถหลัก —</option>
                    @foreach ($model as $m)
                      <option value="{{ $m->id }}">{{ $m->Name_TH }}</option>
                    @endforeach
                  </select>
                  @error('model_id')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-6">
                  <label class="po-label" for="subModel_id"><i class='bx bx-list-ul'></i> รุ่นรถย่อย</label>
                  <select id="subModel_id" name="subModel_id"
                    class="form-select @error('subModel_id') is-invalid @enderror" required>
                    <option value="">— เลือกรุ่นรถย่อย —</option>
                  </select>
                  @error('subModel_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-4">
                  <label for="pricelist_year" class="po-label"><i class='bx bx-calendar-alt'></i> ปี</label>
                  <select id="pricelist_year" name="Year" class="form-select @error('Year') is-invalid @enderror"
                    required disabled>
                    <option value="">— เลือกปี —</option>
                  </select>
                  @error('Year')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-8">
                  <label class="po-label" for="gwm_color"><i class='bx bx-palette'></i> สี</label>
                  <select id="gwm_color" name="gwm_color" class="form-select" required>
                    <option value="">— เลือกสี —</option>
                  </select>
                  @error('gwm_color')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                  @enderror
                </div>
              @endif

            </div>
          </div>
        </div>

        {{-- SECTION 4 : ข้อมูลการชำระเงินจอง --}}
        <div class="po-section">
          <div class="po-section-header">
            <div class="po-section-icon amber"><i class="bx bx-credit-card"></i></div>
            <h6 class="po-section-title">ข้อมูลการชำระเงินจอง</h6>
          </div>
          <div class="po-section-body">
            <div class="row g-3 mb-3">

              {{-- ราคารถ --}}
              <div class="col-md-4">
                <label class="po-label" for="price_sub"><i class='bx bx-car'></i> ราคารถ</label>
                <div class="money-wrap">
                  <input id="price_sub" type="text" class="form-control text-end money-input" name="price_sub"
                    required placeholder="0.00">
                  <span class="money-suffix">฿</span>
                </div>
              </div>

              {{-- เงินจอง --}}
              <div class="col-md-4">
                <label class="po-label" for="CashDeposit"><i class="bx bx-wallet"></i> เงินจอง</label>
                <div class="money-wrap">
                  <input id="CashDeposit" type="text" class="form-control text-end money-input" name="CashDeposit"
                    required placeholder="0.00">
                  <span class="money-suffix">฿</span>
                </div>
              </div>

              {{-- วันที่จ่ายเงินจอง --}}
              <div class="col-md-4">
                <label class="po-label" for="reservation_date"><i class='bx bx-calendar-check'></i>
                  วันที่จ่ายเงินจอง</label>
                <input id="reservation_date" type="date" class="form-control" name="reservation_date" required>
              </div>

            </div>

            {{-- แถว 2 : ประเภทการจ่าย --}}
            <div class="row g-9 pb-2">
              <div class="col-12">
                <div class="po-label"><i class='bx bx-money'></i> ประเภทการจ่ายเงินจอง</div>
                <div class="pay-type-group mt-1">
                  <input type="radio" name="reservationCondition" id="cashRes" value="cash">
                  <label for="cashRes"><i class="bx bx-money me-1"></i>เงินสด</label>

                  <input type="radio" name="reservationCondition" id="creditRes" value="credit">
                  <label for="creditRes"><i class="bx bx-credit-card me-1"></i>บัตรเครดิต</label>

                  <input type="radio" name="reservationCondition" id="checkRes" value="check">
                  <label for="checkRes"><i class="bx bx-building-house me-1"></i>เช็คธนาคาร</label>

                  <input type="radio" name="reservationCondition" id="tranRes" value="transfer">
                  <label for="tranRes"><i class="bx bx-transfer-alt me-1"></i>เงินโอน</label>
                </div>
              </div>
            </div>

            <div class="row g-3 pb-2">
              {{-- เงินสด --}}
              <div id="cashSection" class="col-12" style="display:none;">
                <div class="sub-section">
                  <div class="row g-3">
                    @if (auth()->user()->brand == 2)
                      <div class="col-md-6">
                        <label class="po-label" for="danu_date_cash">วันที่ใช้บัตรคุณดนู</label>
                        <input id="danu_date_cash" type="date" class="form-control" name="danu_date">
                      </div>
                    @endif
                    <div class="col-md-8">
                      <label for="attachments_cash" class="po-label">แนบเอกสาร</label>
                      <div class="upload-area">
                        <input id="attachments_cash" type="file" class="form-control border-0 bg-transparent p-0"
                          name="attachments[]" accept=".pdf,.jpg,.jpeg,.png" multiple>
                        <small class="text-muted mt-1 d-block">PDF, JPG, PNG</small>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              {{-- บัตรเครดิต --}}
              <div id="creditSection" class="col-12" style="display:none;">
                <div class="sub-section">
                  <div class="row g-3">
                    <div class="col-md-7">
                      <label class="po-label" for="reservation_credit">บัตรเครดิต</label>
                      <input id="reservation_credit" type="text" class="form-control" name="reservation_credit"
                        placeholder="ชื่อผู้ถือบัตร / ธนาคาร">
                    </div>
                    <div class="col-md-5">
                      <label class="po-label" for="reservation_tax_credit">ค่าธรรมเนียม</label>
                      <div class="money-wrap">
                        <input id="reservation_tax_credit" type="text" class="form-control text-end money-input"
                          name="reservation_tax_credit" placeholder="0.00">
                        <span class="money-suffix">฿</span>
                      </div>
                    </div>
                    <div class="col-md-8">
                      <label for="attachments_credit" class="po-label">แนบเอกสาร</label>
                      <div class="upload-area">
                        <input id="attachments_credit" type="file" class="form-control border-0 bg-transparent p-0"
                          name="attachments[]" accept=".pdf,.jpg,.jpeg,.png" multiple>
                        <small class="text-muted mt-1 d-block">PDF, JPG, PNG</small>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              {{-- เช็คธนาคาร --}}
              <div id="checkSection" class="col-12" style="display:none;">
                <div class="sub-section">
                  <div class="row g-3">
                    <div class="col-md-4">
                      <label class="po-label" for="reservation_check_bank">ธนาคาร</label>
                      <input id="reservation_check_bank" type="text" class="form-control"
                        name="reservation_check_bank" placeholder="ชื่อธนาคาร">
                    </div>
                    <div class="col-md-4">
                      <label class="po-label" for="reservation_check_branch">สาขา</label>
                      <input id="reservation_check_branch" type="text" class="form-control"
                        name="reservation_check_branch" placeholder="สาขา">
                    </div>
                    <div class="col-md-4">
                      <label class="po-label" for="reservation_check_no">เลขที่เช็ค</label>
                      <input id="reservation_check_no" type="text" class="form-control" name="reservation_check_no"
                        placeholder="เลขที่">
                    </div>
                    @if (auth()->user()->brand == 2)
                      <div class="col-md-6">
                        <label class="po-label" for="danu_date_check">วันที่ใช้บัตรคุณดนู</label>
                        <input id="danu_date_check" type="date" class="form-control" name="danu_date">
                      </div>
                    @else
                      <div class="col-md-8">
                        <label for="attachments_check" class="po-label">แนบเอกสาร</label>
                        <div class="upload-area">
                          <input id="attachments_check" type="file" class="form-control border-0 bg-transparent p-0"
                            name="attachments[]" accept=".pdf,.jpg,.jpeg,.png" multiple>
                          <small class="text-muted mt-1 d-block">PDF, JPG, PNG</small>
                        </div>
                      </div>
                    @endif
                  </div>
                  @if (auth()->user()->brand == 2)
                    <div class="row g-3 mt-1">
                      <div class="col-md-8">
                        <label for="attachments_check2" class="po-label">แนบเอกสาร</label>
                        <div class="upload-area">
                          <input id="attachments_check2" type="file"
                            class="form-control border-0 bg-transparent p-0" name="attachments[]"
                            accept=".pdf,.jpg,.jpeg,.png" multiple>
                          <small class="text-muted mt-1 d-block">PDF, JPG, PNG</small>
                        </div>
                      </div>
                    </div>
                  @endif
                </div>
              </div>

              {{-- เงินโอน --}}
              <div id="bankSection" class="col-12" style="display:none;">
                <div class="sub-section">
                  <div class="row g-3">
                    <div class="col-md-4">
                      <label class="po-label" for="reservation_transfer_bank">ธนาคาร</label>
                      <input id="reservation_transfer_bank" type="text" class="form-control"
                        name="reservation_transfer_bank" placeholder="ชื่อธนาคาร">
                    </div>
                    <div class="col-md-4">
                      <label class="po-label" for="reservation_transfer_branch">สาขา</label>
                      <input id="reservation_transfer_branch" type="text" class="form-control"
                        name="reservation_transfer_branch" placeholder="สาขา">
                    </div>
                    <div class="col-md-4">
                      <label class="po-label" for="reservation_transfer_no">เลขที่การโอน</label>
                      <input id="reservation_transfer_no" type="text" class="form-control"
                        name="reservation_transfer_no" placeholder="เลขที่">
                    </div>
                    @if (auth()->user()->brand == 2)
                      <div class="col-md-6">
                        <label class="po-label" for="danu_date_transfer">วันที่ใช้บัตรคุณดนู</label>
                        <input id="danu_date_transfer" type="date" class="form-control" name="danu_date">
                      </div>
                    @else
                      <div class="col-md-8">
                        <label for="attachments_bank" class="po-label">แนบเอกสาร</label>
                        <div class="upload-area">
                          <input id="attachments_bank" type="file" class="form-control border-0 bg-transparent p-0"
                            name="attachments[]" accept=".pdf,.jpg,.jpeg,.png" multiple>
                          <small class="text-muted mt-1 d-block">PDF, JPG, PNG</small>
                        </div>
                      </div>
                    @endif
                  </div>
                  @if (auth()->user()->brand == 2)
                    <div class="row g-3 mt-1">
                      <div class="col-md-8">
                        <label for="attachments_bank2" class="po-label">แนบเอกสาร</label>
                        <div class="upload-area">
                          <input id="attachments_bank2" type="file" class="form-control border-0 bg-transparent p-0"
                            name="attachments[]" accept=".pdf,.jpg,.jpeg,.png" multiple>
                          <small class="text-muted mt-1 d-block">PDF, JPG, PNG</small>
                        </div>
                      </div>
                    </div>
                  @endif
                </div>
              </div>

            </div>
          </div>
        </div>

      </div>{{-- /col-md-6 ขวา --}}

      {{-- SECTION 5 : รถเทิร์น  --}}
      <div class="col-md-12">
        <div id="turnCarFields" style="display:none;">
          <div class="po-section">
            <div class="po-section-header">
              <div class="po-section-icon rose"><i class="bx bxs-car"></i></div>
              <h6 class="po-section-title">ข้อมูลรถเทิร์น</h6>
            </div>
            <div class="po-section-body">
              <div class="row g-3 mb-3">
                <div class="col-md-2">
                  <label class="po-label" for="brand_car"><i class='bx bx-building'></i> ยี่ห้อ</label>
                  <input id="brand_car" type="text" class="form-control" name="brand_car"
                    placeholder="เช่น Toyota">
                </div>
                <div class="col-md-3">
                  <label class="po-label" for="model"><i class='bx bx-cube'></i> รุ่น</label>
                  <input id="model" type="text" class="form-control" name="model"
                    placeholder="เช่น Fortuner">
                </div>
                <div class="col-md-2">
                  <label class="po-label" for="machine"><i class='bx bx-cog'></i> เครื่องยนต์</label>
                  <input id="machine" type="text" class="form-control" name="machine" placeholder="cc / ประเภท">
                </div>
                <div class="col-md-2">
                  <label class="po-label" for="license_plate"><i class='bx bx-id-card'></i> ทะเบียน</label>
                  <input id="license_plate" type="text" class="form-control" name="license_plate">
                </div>
                <div class="col-md-1">
                  <label class="po-label" for="year_turn">ปี</label>
                  <input id="year_turn" type="text" class="form-control" name="year_turn" placeholder="ค.ศ.">
                </div>
                <div class="col-md-2">
                  <label class="po-label" for="color_turn"><i class='bx bx-palette'></i> สี</label>
                  <input id="color_turn" type="text" class="form-control" name="color_turn">
                </div>
              </div>

              <div class="row g-3 pb-2">
                <div class="col-md-3">
                  <label class="po-label" for="cost_turn"><i class='bx bx-money'></i> ยอดเทิร์น</label>
                  <div class="money-wrap">
                    <input id="cost_turn" type="text" class="form-control text-end money-input" name="cost_turn"
                      placeholder="0.00">
                    <span class="money-suffix">฿</span>
                  </div>
                </div>
                <div class="col-md-3">
                  <label class="po-label" for="com_turn"><i class='bx bx-percentage'></i> ค่าคอมยอดเทิร์น</label>
                  <div class="money-wrap">
                    <input id="com_turn" type="text" class="form-control text-end money-input" name="com_turn"
                      placeholder="0.00">
                    <span class="money-suffix">฿</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>{{-- /turnCarFields --}}
      </div>{{-- /col-12 --}}

    </div>{{-- /row g-4 --}}

    {{-- ── Actions ── --}}
    <div class="po-actions">
      <a href="{{ url()->previous() }}" class="btn btn-outline-secondary px-4">
        <i class="bx bx-arrow-back me-1"></i> ยกเลิก
      </a>
      <button type="submit" class="btn btn-primary px-5 btnSavePurchase">
        <i class="bx bx-save me-2"></i> บันทึกการจอง
      </button>
    </div>

  </form>

  @include('purchase-order.search-customer.search')
@endsection
