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
              <label class="form-label" for="SaleID">รหัสผู้ขาย</label>
              <input id="SaleID" type="text"
                class="form-control @error('SaleID') is-invalid @enderror"
                name="SaleID" required>

              @error('SaleID')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>
            <div class="col-md-3">
              <label class="form-label" for="">ชื่อ - นามสกุล ผู้ขาย</label>
              <input id="" type="text"
                class="form-control"
                name="">
            </div>
            <div class="col-md-2">
              <label class="form-label" for="BookingDate">วันที่จอง</label>
              <input id="BookingDate" type="date"
                class="form-control @error('BookingDate') is-invalid @enderror"
                name="BookingDate" required>

              @error('BookingDate')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>
            <div class="col-md-2">
              <label class="form-label" for="SaleConsultantID">เงินจอง</label>
              <input id="SaleConsultantID" type="text"
                class="form-control text-end @error('SaleConsultantID') is-invalid @enderror"
                name="SaleConsultantID" required>

              @error('SaleConsultantID')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>
            <div class="col-md-3">
              <label class="form-label d-block">ประเภทการจ่ายเงินจอง</label>

              <div class="form-check form-check-inline">
                <input class="form-check-input @error('hasTurnCar') is-invalid @enderror" type="radio" name="hasTurnCar" id="turnCarYes" value="yes" {{ old('hasTurnCar') == 'yes' ? 'checked' : '' }}>
                <label class="form-check-label" for="turnCarYes">เงินสด</label>
              </div>

              <div class="form-check form-check-inline" style="margin-left: 10px">
                <input class="form-check-input @error('hasTurnCar') is-invalid @enderror" type="radio" name="hasTurnCar" id="turnCarNo" value="no" {{ old('hasTurnCar') == 'no' ? 'checked' : '' }}>
                <label class="form-check-label" for="turnCarNo">เงินโอน</label>
              </div>

              <div class="form-check form-check-inline">
                <input class="form-check-input @error('hasTurnCar') is-invalid @enderror" type="radio" name="hasTurnCar" id="turnCarYes" value="yes" {{ old('hasTurnCar') == 'yes' ? 'checked' : '' }}>
                <label class="form-check-label" for="turnCarYes">เช็ค</label>
              </div>

              <div class="form-check form-check-inline" style="margin-left: 10px">
                <input class="form-check-input @error('hasTurnCar') is-invalid @enderror" type="radio" name="hasTurnCar" id="turnCarNo" value="no" {{ old('hasTurnCar') == 'no' ? 'checked' : '' }}>
                <label class="form-check-label" for="turnCarNo">บัตรเครดิต</label>
              </div>

              @error('hasTurnCar')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>
            <div class="col-md-3">
              <label class="form-label" for="CarModelID">รุ่นรถหลัก</label>
              <select id="CarModelID" name="CarModelID" class="form-select" required>
                <option>-- เลือกรุ่นรถ --</option>
                @foreach ($carModel as $item)
                <option value="{{ @$item->id }}">{{ @$item->Name_TH }}</option>
                @endforeach
              </select>

              @error('CarModelID')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>
            <div class="col-md-4">
              <label class="form-label" for="CarModelID">รุ่นรถย่อย</label>
              <select id="CarModelID" name="CarModelID" class="form-select" required>
                <option>-- เลือกรุ่นรถ --</option>
                @foreach ($carModel as $item)
                <option value="{{ @$item->id }}">{{ @$item->Name_TH }}</option>
                @endforeach
              </select>

              @error('CarModelID')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>
            <div class="col-md-3">
              <label class="form-label" for="CarModelID">option</label>
              <select id="CarModelID" name="CarModelID" class="form-select" required>
                <option>-- เลือกรุ่นรถ --</option>
                @foreach ($carModel as $item)
                <option value="{{ @$item->id }}">{{ @$item->Name_TH }}</option>
                @endforeach
              </select>

              @error('CarModelID')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>
            <div class="col-md-1">
              <label class="form-label" for="color">ปี</label>
              <input id="color" type="text"
                class="form-control @error('color') is-invalid @enderror"
                name="color" required>

              @error('color')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>
            <div class="col-md-1">
              <label class="form-label" for="color">สี</label>
              <input id="color" type="text"
                class="form-control @error('color') is-invalid @enderror"
                name="color" required>

              @error('color')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div class="col-md-4">
              <label class="form-label" for="customerSearch">ค้นหาข้อมูลลูกค้า</label>
              <div class="input-group">
                <input id="customerSearch" type="text" class="form-control" name="customerSearch" placeholder="พิมพ์ข้อมูลลูกค้า">
                <span class="input-group-text btnSearchCustomer" style="cursor:pointer;">
                  <i class="bx bx-search"></i>
                </span>
              </div>
            </div>

            <input type="hidden" id="CusID" name="CusID">

            <div class="col-md-4">
              <label class="form-label">ชื่อ - นามสกุล</label>
              <input id="customerName" type="text" class="form-control" readonly>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="">เลขบัตรประชาชน</label>
              <input id="customerID" type="text" class="form-control" readonly>
            </div>
            <div class="col-md-3">
              <label class="form-label" for="">เบอร์โทรศัพท์</label>
              <input id="customerPhone" type="text" class="form-control" readonly>
            </div>


            <!-- <div class="col-md-3">
              <label class="form-label" for="FinanceID">รหัสไฟแนนซ์</label>
              <input id="FinanceID" type="text"
                class="form-control @error('FinanceID') is-invalid @enderror"
                name="FinanceID" required>

              @error('FinanceID')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>
            
            <div class="col-md-3">
              <label class="form-label" for="CashDeposit">เงินจอง</label>
              <input id="CashDeposit" type="text"
                class="form-control @error('CashDeposit') is-invalid @enderror"
                name="CashDeposit" required>

              @error('CashDeposit')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div> -->

            <div class="col-md-3">
              <label class="form-label d-block">รถเทิร์น</label>

              <div class="form-check form-check-inline">
                <input class="form-check-input @error('hasTurnCar') is-invalid @enderror" type="radio" name="hasTurnCar" id="turnCarYes" value="yes" {{ old('hasTurnCar') == 'yes' ? 'checked' : '' }}>
                <label class="form-check-label" for="turnCarYes">มี</label>
              </div>

              <div class="form-check form-check-inline" style="margin-left: 10px">
                <input class="form-check-input @error('hasTurnCar') is-invalid @enderror" type="radio" name="hasTurnCar" id="turnCarNo" value="no" {{ old('hasTurnCar') == 'no' ? 'checked' : '' }}>
                <label class="form-check-label" for="turnCarNo">ไม่มี</label>
              </div>

              @error('hasTurnCar')
              <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
              </span>
              @enderror
            </div>

            <div id="turnCarFields" class="row mt-6 g-5" style="display:none;">
              <h4>กรณีมีรถเทิร์น</h4>
              <div class="col-md-2">
                <label class="form-label" for="brand">ยี่ห้อ</label>
                <input id="brand" type="text"
                  class="form-control"
                  name="brand">
              </div>
              <div class="col-md-2">
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
              <div class="col-md-1">
                <label class="form-label" for="year">ปี</label>
                <input id="year" type="text"
                  class="form-control"
                  name="year">
              </div>
              <div class="col-md-2">
                <label class="form-label" for="color">สี</label>
                <input id="color" type="text"
                  class="form-control"
                  name="color">
              </div>
              <div class="col-md-2">
                <label class="form-label" for="license_plate">ทะเบียน</label>
                <input id="license_plate" type="text"
                  class="form-control"
                  name="license_plate">
              </div>
              <div class="col-md-2">
                <label class="form-label" for="priceCost">ยอดเทิร์น</label>
                <input id="priceCost" type="text"
                  class="form-control text-end"
                  name="priceCost">
              </div>
              <div class="col-md-2">
                <label class="form-label" for="priceCom">ค่าคอมยอดเทิร์น</label>
                <input id="priceCom" type="text"
                  class="form-control text-end"
                  name="priceCom">
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