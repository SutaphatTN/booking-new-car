@extends('layouts/contentNavbarLayout')
@section('title', 'Add Purchase Order')

@section('page-script')
@vite(['resources/assets/js/purchase-order.js'])
@endsection

@section('content')
<div id="searchCustomer"></div>

<div class="row">
  <div class="col-md-12">
    <div class="card">
      <h4 class="card-header">เพิ่มข้อมูลการจอง</h4>

      <div class="card-body">
        <form action="{{ route('purchase-order.store') }}" method="POST" enctype="multipart/form-data">
          @csrf

          <div class="row g-5">

            <div class="col-md-2">
              <label for="sale_card" class="form-label">รหัสผู้ขาย</label>
              <input id="sale_card" type="text" class="form-control" value="{{ Auth::user()->format_card_id }}" readonly>
            </div>
            <div class="col-md-3">
              <label for="sale_name" class="form-label">ชื่อ - นามสกุล ผู้ขาย</label>
              <input id="sale_name" type="text" class="form-control" value="{{ Auth::user()->name }}" readonly>
            </div>

            <div class="col-md-2 mb-5">
              <label for="type" class="form-label">แหล่งที่มา</label>
              <select id="type" name="type" class="form-select" required>
                <option value="">-- เลือก --</option>
                @foreach ($type as $item)
                <option value="{{ @$item->id }}">{{ @$item->name }}</option>
                @endforeach
              </select>
            </div>

            <input type="hidden"
              name="SaleID"
              value="{{ Auth::user()->id }}">

            <div class="col-md-2">
              <label for="BookingDate" class="form-label" for="BookingDate">วันที่จอง</label>
              <input id="BookingDate" type="date"
                class="form-control @error('BookingDate') is-invalid @enderror"
                name="BookingDate" required>

              @error('BookingDate')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-3">
              <label class="form-label" for="customerSearch">ค้นหาข้อมูลลูกค้า</label>
              <div class="input-group">
                <input id="customerSearch" type="text" class="form-control" name="customerSearch" placeholder="พิมพ์ข้อมูลลูกค้า">
                <span class="btn btn-outline-secondary btnSearchCustomer" style="cursor:pointer;">
                  <i class="bx bx-search"></i>
                </span>
              </div>
            </div>

            <input type="hidden" id="CusID" name="CusID">

            <div class="col-md-3">
              <label for="customerName" class="form-label">ชื่อ - นามสกุล</label>
              <input id="customerName" type="text" class="form-control" readonly>
            </div>
            <div class="col-md-3">
              <label for="customerID" class="form-label">เลขบัตรประชาชน</label>
              <input id="customerID" type="text" class="form-control" readonly>
            </div>
            <div class="col-md-2">
              <label for="customerPhone" class="form-label">เบอร์โทรศัพท์</label>
              <input id="customerPhone" type="text" class="form-control" readonly>
            </div>
            <div class="col-md-2">
              <label for="payment_mode" class="form-label">ประเภทการซื้อ</label>
              <select id="payment_mode" name="payment_mode" class="form-select" required>
                <option value="">-- เลือกประเภท --</option>
                <option value="finance" {{ old('payment_mode') == 'finance' ? 'selected' : '' }}>ผ่อน</option>
                <option value="non-finance" {{ old('payment_mode') == 'non-finance' ? 'selected' : '' }}>เงินสด</option>
              </select>
            </div>
            <div class="col-md-2">
              <fieldset class="mb-0">
                <legend class="form-label fw-semibold mb-2" style="font-size: 1rem;">รถเทิร์น</legend>

                <div class="form-check form-check-inline">
                  <input class="form-check-input @error('hasTurnCar') is-invalid @enderror" type="radio" name="hasTurnCar" id="turnCarYes" value="yes" {{ old('hasTurnCar') == 'yes' ? 'checked' : '' }}>
                  <label class="form-check-label" for="turnCarYes">มี</label>
                </div>

                <div class="form-check form-check-inline ms-2">
                  <input class="form-check-input @error('hasTurnCar') is-invalid @enderror" type="radio" name="hasTurnCar" id="turnCarNo" value="no" {{ old('hasTurnCar') == 'no' ? 'checked' : '' }}>
                  <label class="form-check-label" for="turnCarNo">ไม่มี</label>
                </div>

                @error('hasTurnCar')
                <span class="invalid-feedback" role="alert">
                  <strong>{{ $message }}</strong>
                </span>
                @enderror
              </fieldset>
            </div>

            <div class="col-md-3">
              <label class="form-label" for="model_id">รุ่นรถหลัก</label>
              <select id="model_id" name="model_id" class="form-select" required>
                <option value="">-- เลือกรุ่นรถหลัก --</option>
                @foreach ($model as $m)
                <option value="{{ $m->id }}">{{ $m->Name_TH }}</option>
                @endforeach
              </select>

              @error('model_id')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>
            <div class="col-md-4">
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
            <div class="col-md-1">
              <label class="form-label" for="option">Option</label>
              <input id="option" type="text"
                class="form-control @error('option') is-invalid @enderror"
                name="option" required>

              @error('option')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-1">
              <label class="form-label" for="Year">ปี</label>
              <input id="Year" type="text"
                class="form-control @error('Year') is-invalid @enderror"
                name="Year" required>

              @error('Year')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>
            <div class="col-md-3">
              <label class="form-label" for="Color">สี</label>
              <input id="Color" type="text"
                class="form-control @error('Color') is-invalid @enderror"
                name="Color" required>

              @error('Color')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-2">
              <label class="form-label" for="price_sub">ราคารถ</label>
              <input id="price_sub" type="text"
                class="form-control text-end money-input"
                name="price_sub" required>
            </div>

            <div class="col-md-2">
              <label class="form-label" for="CashDeposit">เงินจอง</label>
              <input id="CashDeposit" type="text"
                class="form-control text-end money-input"
                name="CashDeposit" required>
            </div>

            <div class="col-md-2">
              <label class="form-label" for="reservation_date">วันที่จ่ายเงินจอง</label>
              <input id="reservation_date" type="date"
                class="form-control"
                name="reservation_date" required>
            </div>

            <div class="col-md-6">
              <fieldset class="mb-0">
                <legend class="form-label fw-semibold mb-2" style="font-size: 1rem;">ประเภทการจ่ายเงินจอง</legend>

                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="reservationCondition" id="cashRes" value="cash">
                  <label class="form-check-label" for="cashRes">เงินสด</label>
                </div>

                <div class="form-check form-check-inline" style="margin-left: 15px">
                  <input class="form-check-input" type="radio" name="reservationCondition" id="creditRes" value="credit">
                  <label class="form-check-label" for="creditRes">บัตรเครดิต</label>
                </div>

                <div class="form-check form-check-inline" style="margin-left: 15px">
                  <input class="form-check-input" type="radio" name="reservationCondition" id="checkRes" value="check">
                  <label class="form-check-label" for="checkRes">เช็คธนาคาร</label>
                </div>

                <div class="form-check form-check-inline" style="margin-left: 15px">
                  <input class="form-check-input" type="radio" name="reservationCondition" id="tranRes" value="transfer">
                  <label class="form-check-label" for="tranRes">เงินโอน</label>
                </div>
              </fieldset>
            </div>

            <div id="creditSection" style="display:none;">
              <div class="row">
                <div class="col-md-4">
                  <label class="form-label" for="reservation_credit">บัตรเครดิต</label>
                  <input id="reservation_credit" type="text"
                    class="form-control"
                    name="reservation_credit">
                </div>

                <div class="col-md-2">
                  <label class="form-label" for="reservation_tax_credit">ค่าธรรมเนียม</label>
                  <input id="reservation_tax_credit" type="text"
                    class="form-control text-end money-input"
                    name="reservation_tax_credit">
                </div>
              </div>
            </div>

            <div id="checkSection" style="display:none;">
              <div class="row">
                <div class="col-md-3">
                  <label class="form-label" for="reservation_check_bank">ธนาคาร</label>
                  <input id="reservation_check_bank" type="text"
                    class="form-control"
                    name="reservation_check_bank">
                </div>

                <div class="col-md-4">
                  <label class="form-label" for="reservation_check_branch">สาขา</label>
                  <input id="reservation_check_branch" type="text"
                    class="form-control"
                    name="reservation_check_branch">
                </div>

                <div class="col-md-3">
                  <label class="form-label" for="reservation_check_no">เลขที่</label>
                  <input id="reservation_check_no" type="text"
                    class="form-control"
                    name="reservation_check_no">
                </div>
              </div>
            </div>

            <div id="bankSection" style="display:none;">
              <div class="row">
                <div class="col-md-3">
                  <label class="form-label" for="reservation_transfer_bank">ธนาคาร</label>
                  <input id="reservation_transfer_bank" type="text"
                    class="form-control"
                    name="reservation_transfer_bank">
                </div>

                <div class="col-md-4">
                  <label class="form-label" for="reservation_transfer_branch">สาขา</label>
                  <input id="reservation_transfer_branch" type="text"
                    class="form-control"
                    name="reservation_transfer_branch">
                </div>

                <div class="col-md-3">
                  <label class="form-label" for="reservation_transfer_no">เลขที่</label>
                  <input id="reservation_transfer_no" type="text"
                    class="form-control"
                    name="reservation_transfer_no">
                </div>
              </div>
            </div>

            <div id="turnCarFields" class="row mt-6 g-5" style="display:none;">
              <h4>รถเทิร์น</h4>
              <div class="col-md-3">
                <label class="form-label" for="brand">ยี่ห้อ</label>
                <input id="brand" type="text"
                  class="form-control"
                  name="brand">
              </div>
              <div class="col-md-4">
                <label class="form-label" for="model">รุ่น</label>
                <input id="model" type="text"
                  class="form-control"
                  name="model">
              </div>
              <div class="col-md-3">
                <label class="form-label" for="machine">เครื่องยนต์</label>
                <input id="machine" type="text"
                  class="form-control"
                  name="machine">
              </div>
              <div class="col-md-2">
                <label class="form-label" for="license_plate">ทะเบียน</label>
                <input id="license_plate" type="text"
                  class="form-control"
                  name="license_plate">
              </div>
              <div class="col-md-2">
                <label class="form-label" for="year_turn">ปี</label>
                <input id="year_turn" type="text"
                  class="form-control"
                  name="year_turn">
              </div>
              <div class="col-md-2">
                <label class="form-label" for="color_turn">สี</label>
                <input id="color_turn" type="text"
                  class="form-control"
                  name="color_turn">
              </div>

              <div class="col-md-3">
                <label class="form-label" for="cost_turn">ยอดเทิร์น</label>
                <input id="cost_turn" type="text"
                  class="form-control text-end money-input"
                  name="cost_turn">
              </div>
              <div class="col-md-3">
                <label class="form-label" for="com_turn">ค่าคอมยอดเทิร์น</label>
                <input id="com_turn" type="text"
                  class="form-control text-end money-input"
                  name="com_turn">
              </div>
            </div>

          </div>

          <div class="mt-6 d-flex justify-content-end gap-2">
            <button class="btn btn-primary btnSavePurchase">บันทึก</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@include('purchase-order.search-customer.search')
@endsection