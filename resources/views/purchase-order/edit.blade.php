@extends('layouts/contentNavbarLayout')
@section('title', 'Data Purchase Order')

@section('page-script')
@vite(['resources/assets/js/purchase-order.js'])
@endsection

@section('content')
<div id="viewGift"></div>
<div id="viewExtra"></div>
<div id="searchCarOrder"></div>
<div id="viewPreviewPurchase"></div>
<div id="searchCustomer"></div>

<div class="row">
  <div class="col-md-12">
    <h6 class="text-body-secondary">ข้อมูลการจอง</h6>
    <form id="purchaseForm"
      action="{{ route('purchase-order.update', $saleCar->id) }}"
      method="POST"
      enctype="multipart/form-data">
      @csrf
      @method('PUT')

      <div class="nav-align-top">
        <input type="hidden" id="userRole" value="{{ $userRole }}">

        <ul class="nav nav-pills mb-4 nav-fill" role="tablist">

          <li class="nav-item mb-1 mb-sm-0">
            <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#tab-detail" aria-controls="tab-detail" aria-selected="true">
              <span class="d-none d-sm-inline-flex align-items-center">
                <i class="icon-base bx bx-spreadsheet icon-sm me-1_5"></i>ข้อมูลลูกค้า
                <!-- <span class="badge rounded-pill badge-center h-px-20 w-px-20 bg-danger ms-1_5">3</span> -->
              </span>
              <i class="icon-base bx bx-spreadsheet icon-sm d-sm-none"></i>
            </button>
          </li>

          <li class="nav-item mb-1 mb-sm-0">
            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-price" aria-controls="tab-price" aria-selected="false">
              <span class="d-none d-sm-inline-flex align-items-center"><i class="icon-base bx bx-credit-card icon-sm me-1_5"></i>สรุปการขาย</span>
              <i class="icon-base bx bx-credit-card icon-sm d-sm-none"></i>
            </button>
          </li>

          @if( ($userRole == 'audit' || $userRole == 'manager' || $userRole == 'md') )
          <li class="nav-item mb-1 mb-sm-0">
            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-more" aria-controls="tab-more" aria-selected="false">
              <span class="d-none d-sm-inline-flex align-items-center"><i class="icon-base bx bx-slider icon-sm me-1_5"></i>ข้อมูลเพิ่มเติม</span>
              <i class="icon-base bx bx-slider icon-sm d-sm-none"></i>
            </button>
          </li>
          @endif
        </ul>

        <div class="tab-content">

          <!-- detail -->
          <div class="tab-pane fade show active" id="tab-detail" role="tabpanel">
            <div class="row">
              <div class="col-md-12">
                <div class="row g-6">
                  <h4 class="pb-2 mb-3 border-bottom">ข้อมูลลูกค้า</h4>
                  <div class="col-md-2">
                    <label for="sale_card" class="form-label">รหัสผู้ขาย</label>
                    <input id="sale_card" class="form-control" type="text" value="{{ $saleCar->saleUser->format_card_id }}" readonly>
                  </div>
                  <div class="col-md-3">
                    <label for="sale_name" class="form-label">ชื่อ - นามสกุล ผู้ขาย</label>
                    <input id="sale_name" class="form-control" type="text" value="{{ $saleCar->saleUser->name }}" readonly>
                  </div>
                  <input type="hidden" name="SaleID" value="{{ $saleCar->SaleID }}">

                  <div class="col-md-2">
                    <label class="form-label" for="BookingDate">วันที่จอง</label>
                    <input id="BookingDate" type="date"
                      class="form-control"
                      name="BookingDate" value="{{ $saleCar->BookingDate }}" required>
                  </div>

                  <input type="hidden" id="CusID" name="CusID" value="{{ $saleCar->CusID }}">

                  <input type="hidden" id="CusCurrentAddress" value="{{ $saleCar->customer->currentAddress->full_address ?? '-' }}">
                  <input type="hidden" id="CusDocumentAddress" value="{{ $saleCar->customer->documentAddress->full_address ?? '-' }}">

                  <div class="col-md-3">
                    <label for="CusFullName" class="form-label">ชื่อ - นามสกุล</label>
                    <input type="text" id="CusFullName" class="form-control"
                      value="{{ $saleCar->customer->prefix->Name_TH ?? '' }} {{ $saleCar->customer->FirstName }} {{ $saleCar->customer->LastName }}"
                      readonly>
                  </div>
                  <div class="col-md-2">
                    <fieldset class="mb-0">
                      <legend class="form-label fw-semibold mb-2" style="font-size: 1rem;">รถเทิร์น</legend>

                      <div class="form-check form-check-inline">
                        <input class="form-check-input"
                          type="radio"
                          name="hasTurnCar"
                          id="turnCarYes"
                          value="yes"
                          {{ old('hasTurnCar', $saleCar->TurnCarID ? 'yes' : 'no') === 'yes' ? 'checked' : '' }}>
                        <label class="form-check-label" for="turnCarYes">มี</label>
                      </div>

                      <div class="form-check form-check-inline">
                        <input class="form-check-input"
                          type="radio"
                          name="hasTurnCar"
                          id="turnCarNo"
                          value="no"
                          {{ old('hasTurnCar', $saleCar->TurnCarID ? 'yes' : 'no') === 'no' ? 'checked' : '' }}>
                        <label class="form-check-label" for="turnCarNo">ไม่มี</label>
                      </div>
                    </fieldset>
                  </div>

                  <div class="col-md-2">
                    <label for="CusMobile" class="form-label">เบอร์โทรศัพท์</label>
                    <input class="form-control" id="CusMobile" type="text" name="Mobilephone1" id="Mobilephone1"
                      value="{{ $saleCar->customer->formatted_mobile }}" readonly>
                  </div>
                  <div class="col-md-2">
                    <label for="IDNumber" class="form-label">เลขบัตรประชาชน</label>
                    <input class="form-control" type="text" name="IDNumber" id="IDNumber"
                      value="{{ $saleCar->customer->formatted_id_number }}" readonly>
                  </div>

                  <div class="col-md-3">
                    <label for="model_id" class="form-label">รุ่นรถหลัก</label>
                    <select id="model_id" name="model_id" class="form-select" required>
                      <option value="">-- เลือกรุ่นรถหลัก --</option>
                      @foreach ($model as $m)
                      <option value="{{ $m->id }}" data-overbudget="{{ $m->over_budget }}" {{ $saleCar->model_id == $m->id ? 'selected' : '' }}>{{ $m->Name_TH }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label for="subModel_id" class="form-label">รุ่นรถย่อย</label>
                    <select id="subModel_id" name="subModel_id" class="form-select" required>
                      <option value="">-- เลือกรุ่นรถย่อย --</option>
                      @foreach ($subModels as $s)
                      <option value="{{ $s->id }}" {{ $saleCar->subModel_id == $s->id ? 'selected' : '' }}>
                        {{ $s->detail }} - {{ $s->name }}
                      </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-1">
                    <label class="form-label" for="option">Option</label>
                    <input id="option" type="text"
                      class="form-control"
                      name="option" value="{{ $saleCar->option }}" required>
                  </div>

                  <div class="col-md-1">
                    <label for="Year" class="form-label">ปี</label>
                    <input class="form-control" type="number" name="Year" id="Year" min="2020" max="2100"
                      value="{{ $saleCar->Year }}" required>
                  </div>
                  <div class="col-md-2">
                    <label for="Color" class="form-label">สี</label>
                    <input class="form-control" type="text" name="Color" id="Color"
                      value="{{ $saleCar->Color }}" required>
                  </div>

                  <div class="col-md-2">
                    <label for="CashDeposit" class="form-label">เงินจอง</label>
                    <input type="text" class="form-control text-end money-input" id="CashDeposit" name="CashDeposit"
                      value="{{ $saleCar->CashDeposit }}">
                  </div>

                  <div class="col-md-2">
                    <label for="reservation_date" class="form-label">วันที่จ่ายเงินจอง</label>
                    <input id="reservation_date" type="date"
                      class="form-control"
                      name="reservation_date" value="{{ old('reservation_date', $reservationPayment?->date ?? '') }}">
                  </div>

                  @php
                  $reservationType = $reservationPayment->type ?? '';
                  @endphp
                  <div class="col-md-5">
                    <fieldset class="mb-0">
                      <legend class="form-label fw-semibold mb-2" style="font-size: 1rem;">ประเภทการจ่ายเงินวันออกรถ</legend>

                      <div class="form-check form-check-inline" style="margin-left: 15px">
                        <input class="form-check-input" type="radio" name="reservationCondition" id="cashReser" value="cash"
                          {{ $reservationType === 'cash' ? 'checked' : '' }}>
                        <label class="form-check-label" for="cashReser">เงินสด</label>
                      </div>

                      <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="reservationCondition" id="creditReser" value="credit"
                          {{ $reservationType === 'credit' ? 'checked' : '' }}>
                        <label class="form-check-label" for="creditReser">บัตรเครดิต</label>
                      </div>

                      <div class="form-check form-check-inline" style="margin-left: 15px">
                        <input class="form-check-input" type="radio" name="reservationCondition" id="checkReser" value="check"
                          {{ $reservationType === 'check' ? 'checked' : '' }}>
                        <label class="form-check-label" for="checkReser">เช็คธนาคาร</label>
                      </div>

                      <div class="form-check form-check-inline" style="margin-left: 15px">
                        <input class="form-check-input" type="radio" name="reservationCondition" id="transReser" value="transfer"
                          {{ $reservationType === 'transfer' ? 'checked' : '' }}>
                        <label class="form-check-label" for="transReser">เงินโอน</label>
                      </div>
                    </fieldset>
                  </div>

                  <div id="creditReservation">
                    <div class="row">
                      <div class="col-md-4">
                        <label for="reservation_credit" class="form-label">บัตรเครดิต</label>
                        <input id="reservation_credit" type="text"
                          class="form-control"
                          name="reservation_credit"
                          value="{{ old('reservation_credit', $reservationPayment->credit ?? '') }}" readonly>
                      </div>

                      <div class="col-md-2">
                        <label for="reservation_tax_credit" class="form-label">ค่าธรรมเนียม</label>
                        <input id="reservation_tax_credit" type="text"
                          class="form-control text-end money-input"
                          name="reservation_tax_credit"
                          value="{{ old('reservation_tax_credit', $reservationPayment->tax_credit ?? '') }}" readonly>
                      </div>
                    </div>
                  </div>

                  <div id="checkReservation">
                    <div class="row">
                      <div class="col-md-3">
                        <label for="reservation_check_bank" class="form-label">ธนาคาร</label>
                        <input id="reservation_check_bank" type="text"
                          class="form-control"
                          name="reservation_check_bank"
                          value="{{ old('reservation_check_bank', $reservationPayment->check_bank ?? '') }}" readonly>
                      </div>

                      <div class="col-md-4">
                        <label for="reservation_check_branch" class="form-label">สาขา</label>
                        <input id="reservation_check_branch" type="text"
                          class="form-control"
                          name="reservation_check_branch"
                          value="{{ old('reservation_check_branch', $reservationPayment->check_branch ?? '') }}" readonly>
                      </div>

                      <div class="col-md-3">
                        <label for="reservation_check_no" class="form-label">เลขที่</label>
                        <input id="reservation_check_no" type="text"
                          class="form-control"
                          name="reservation_check_no"
                          value="{{ old('reservation_check_no', $reservationPayment->check_no ?? '') }}" readonly>
                      </div>
                    </div>
                  </div>

                  <div id="bankReservation">
                    <div class="row">
                      <div class="col-md-3">
                        <label for="reservation_transfer_bank" class="form-label">ธนาคาร</label>
                        <input id="reservation_transfer_bank" type="text"
                          class="form-control"
                          name="reservation_transfer_bank"
                          value="{{ old('reservation_transfer_bank', $reservationPayment->transfer_bank ?? '') }}" readonly>
                      </div>

                      <div class="col-md-4">
                        <label for="reservation_transfer_branch" class="form-label">สาขา</label>
                        <input id="reservation_transfer_branch" type="text"
                          class="form-control"
                          name="reservation_transfer_branch"
                          value="{{ old('reservation_transfer_branch', $reservationPayment->transfer_branch ?? '') }}" readonly>
                      </div>

                      <div class="col-md-3">
                        <label for="reservation_transfer_no" class="form-label">เลขที่</label>
                        <input id="reservation_transfer_no" type="text"
                          class="form-control"
                          name="reservation_transfer_no"
                          value="{{ old('reservation_transfer_no', $reservationPayment->transfer_no ?? '') }}" readonly>
                      </div>
                    </div>
                  </div>

                  @if ($userRole !== 'sale')
                  <h4 class="pt-2 pb-2 border-bottom">ข้อมูล Car Order</h4>
                  <div class="col-md-2 mt-2">
                    <label for="carOrderSearch" class="form-label">ค้นหา Car Order ID</label>
                    <div class="input-group">
                      <input id="carOrderSearch" type="text" class="form-control" name="carOrderSearch" placeholder="ค้นหา Car Order ID">
                      <span class="btn btn-outline-secondary btnSearchCarOrder" style="cursor:pointer;">
                        <i class="bx bx-search"></i>
                      </span>
                    </div>
                  </div>

                  <input type="hidden" id="CarOrderID" name="CarOrderID" value="{{ $saleCar->CarOrderID ?? '' }}">

                  <div class="col-md-2 mt-2">
                    <label for="carOrderCode" class="form-label">Car Order ID</label>
                    <input id="carOrderCode" type="text" class="form-control" value="{{ $saleCar->carOrder->order_code ?? '' }}" readonly>
                  </div>
                  <div class="col-md-3 mt-2">
                    <label for="carOrderModel" class="form-label">รุ่นรถหลัก</label>
                    <input id="carOrderModel" type="text" class="form-control" value="{{ $saleCar->carOrder->model->Name_TH ?? '' }}" readonly>
                  </div>
                  <div class="col-md-5 mt-2">
                    <label for="carOrderSubModel" class="form-label">รุ่นรถย่อย</label>
                    <input id="carOrderSubModel"
                      type="text"
                      class="form-control"
                      value="{{ 
                          !empty($saleCar->carOrder->subModel)
                            ? $saleCar->carOrder->subModel->detail . ' - ' . $saleCar->carOrder->subModel->name
                            : '' 
                        }}"
                      readonly>
                  </div>
                  <div class="col-md-2">
                    <label for="carOrderOption" class="form-label">Option</label>
                    <input id="carOrderOption" type="text" class="form-control" value="{{ $saleCar->carOrder->option ?? '' }}" readonly>
                  </div>
                  <div class="col-md-2">
                    <label for="carOrderVin" class="form-label">Vin-Number</label>
                    <input id="carOrderVin" type="text" class="form-control" value="{{ $saleCar->carOrder->vin_number ?? '' }}" readonly>
                  </div>

                  <div class="col-md-2">
                    <label for="carOrderColor" class="form-label">สี</label>
                    <input id="carOrderColor" type="text" class="form-control" value="{{ $saleCar->carOrder->year ?? '' }}" readonly>
                  </div>
                  <div class="col-md-2">
                    <label for="carOrderYear" class="form-label">ปี</label>
                    <input id="carOrderYear" type="text" class="form-control" value="{{ $saleCar->carOrder->color ?? '' }}" readonly>
                  </div>
                  <div class="col-md-2">
                    <label for="carOrderCost" class="form-label">ราคาทุน</label>
                    <input
                      id="carOrderCost"
                      type="text"
                      class="form-control text-end money-input"
                      value="{{ $saleCar->carOrder?->car_DNP !== null ? number_format($saleCar->carOrder->car_DNP, 2) : '' }}"
                      readonly>
                  </div>
                  <div class="col-md-2">
                    <label for="carOrderSale" class="form-label">ราคาขาย</label>
                    <input id="carOrderSale" type="text" class="form-control text-end money-input"
                      value="{{ $saleCar->carOrder?->car_MSRP !== null ? number_format($saleCar->carOrder->car_MSRP, 2) : '' }}"
                      readonly>
                  </div>
                  @endif

                  <div id="turnCarFields"
                    class="row mt-6 g-5"
                    @style([ 'display:flex'=> $saleCar->TurnCarID,
                    'display:none' => !$saleCar->TurnCarID,
                    ])>

                    <h4>ข้อมูลรถเทิร์น</h4>

                    <div class="col-md-3">
                      <label for="brand" class="form-label">ยี่ห้อ</label>
                      <input id="brand" class="form-control" name="brand"
                        value="{{ old('brand', $saleCar->turnCar->brand ?? '') }}">
                    </div>

                    <div class="col-md-4">
                      <label for="model" class="form-label">รุ่น</label>
                      <input id="model" class="form-control" name="model"
                        value="{{ old('model', $saleCar->turnCar->model ?? '') }}">
                    </div>

                    <div class="col-md-3">
                      <label for="machine" class="form-label">เครื่องยนต์</label>
                      <input id="machine" class="form-control" name="machine"
                        value="{{ old('machine', $saleCar->turnCar->machine ?? '') }}">
                    </div>

                    <div class="col-md-2">
                      <label for="license_plate" class="form-label">ทะเบียน</label>
                      <input id="license_plate" class="form-control" name="license_plate"
                        value="{{ old('license_plate', $saleCar->turnCar->license_plate ?? '') }}">
                    </div>

                    <div class="col-md-2">
                      <label for="year_turn" class="form-label">ปี</label>
                      <input id="year_turn" class="form-control" name="year_turn"
                        value="{{ old('year_turn', $saleCar->turnCar->year_turn ?? '') }}">
                    </div>

                    <div class="col-md-2">
                      <label for="color_turn" class="form-label">สี</label>
                      <input id="color_turn" class="form-control" name="color_turn"
                        value="{{ old('color_turn', $saleCar->turnCar->color_turn ?? '') }}">
                    </div>

                    <div class="col-md-3">
                      <label for="cost_turn" class="form-label">ยอดเทิร์น</label>
                      <input id="cost_turn" class="form-control text-end money-input" name="cost_turn"
                        value="{{ old('cost_turn', isset($saleCar->turnCar) ? number_format($saleCar->turnCar->cost_turn, 2) : '') }}">
                    </div>

                    <div class="col-md-3">
                      <label for="com_turn" class="form-label">ค่าคอมยอดเทิร์น</label>
                      <input id="com_turn" class="form-control text-end money-input" name="com_turn"
                        value="{{ old('com_turn', isset($saleCar->turnCar) ? number_format($saleCar->turnCar->com_turn, 2) : '') }}">
                    </div>
                  </div>

                </div>

              </div>
            </div>

            <div class="mt-6 d-flex justify-content-end gap-2">
              <button id="nextCampaign" class="btn btn-primary">ถัดไป</button>
              <!-- <button id="btnPrevDate" class="btn btn-danger">ย้อนกลับ</button> -->
            </div>

          </div>

          <!-- price -->
          <div class="tab-pane fade" id="tab-price" role="tabpanel">
            <div class="nav-align-top">
              <div class="nav-tabs-wrapper" style="overflow-x: auto; white-space: nowrap;">
                <ul class="nav nav-tabs flex-nowrap" role="tablist">
                  <li class="nav-item">
                    <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                      data-bs-target="#tab-campaign" aria-controls="tab-campaign" aria-selected="true">แคมเปญ</button>
                  </li>
                  <li class="nav-item">
                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                      data-bs-target="#tab-accessory" aria-controls="tab-accessory" aria-selected="false">รายละเอียดอุปกรณ์ตกแต่ง (แถม)</button>
                  </li>
                  <li class="nav-item">
                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                      data-bs-target="#tab-extra" aria-controls="tab-extra" aria-selected="false">รายการซื้อเพิ่ม</button>
                  </li>
                  <li class="nav-item">
                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                      data-bs-target="#tab-car" aria-controls="tab-car" aria-selected="false">สรุปยอด</button>
                  </li>
                </ul>
              </div>

              <div class="tab-content">

                <!-- campaign -->
                <div class="tab-pane fade show active" id="tab-campaign" role="tabpanel">
                  <h4 class="pb-2 mb-3">แคมเปญ</h4>

                  <div class="row g-2">
                    <div class="col-md-2"></div>
                    <div class="col-md-6">
                      <label for="CampaignID" class="form-label">เลือกแคมเปญ</label>
                      <select name="CampaignID[]" id="CampaignID" multiple class="form-select">
                        @foreach ($campaigns as $camp)
                        <option value="{{ $camp->id }}"
                          data-cash-support-final="{{ $camp->cashSupport_final }}"
                          {{ in_array($camp->id, $selected_campaigns ?? []) ? 'selected' : '' }}>
                          ({{ $camp->type->name ?? '-' }}) {{ $camp->appellation->name ?? '-' }} - {{ number_format((float) $camp->cashSupport_final, 2, '.', ',') }}
                        </option>
                        @endforeach
                      </select>
                      <div id="campaignWarning" class="mt-2 text-danger"></div>
                    </div>
                    <div class="col-md-2">
                      <label for="TotalSaleCampaign" class="form-label">ยอดรวมค่าแคมเปญ</label>
                      <input class="form-control text-end money-input" type="text" id="TotalSaleCampaign" name="TotalSaleCampaign" placeholder="0.00" readonly />
                    </div>
                    <input type="hidden" id="balanceCampaign" name="balanceCampaign">
                    <div class="col-md-2"></div>
                  </div>

                  <div class="mt-6 d-flex justify-content-end gap-2">
                    <button id="prevDetail" class="btn btn-danger">ย้อนกลับ</button>
                    <button id="nextAccessory" class="btn btn-primary">ถัดไป</button>
                  </div>

                </div>

                <!-- gift -->
                <div class="tab-pane fade" id="tab-accessory" role="tabpanel">
                  <div class="d-flex align-items-center justify-content-between pb-2 mb-3 position-relative">
                    <div class="flex-grow-1 text-center">
                      <h4 class="mb-0">รายละเอียดอุปกรณ์ตกแต่ง (แถม)</h4>
                    </div>
                    <button type="button" class="btn btn-secondary btn-md btnGift ms-2">
                      <i class="bx bx-plus me-1"></i> เพิ่ม
                    </button>
                  </div>

                  <input type="hidden" id="TotalAccessoryGift" name="TotalAccessoryGift">
                  <input type="hidden" id="gift_ids" name="gift_ids">
                  <input type="hidden" id="total_gift_used" name="total_gift_used">
                  <input type="hidden" id="total_gift_com" name="AccessoryGiftCom">

                  <div class="table-responsive text-nowrap">
                    <table class="table table-bordered" id="giftTablePrice">
                      <thead>
                        <tr>
                          <th>No.</th>
                          <th>รหัส</th>
                          <th>รายละเอียด</th>
                          <th>ประเภทราคา</th>
                          <th>ราคา (ค่าคอม)</th>
                          <th>ลบ</th>
                        </tr>
                      </thead>
                      <tbody>
                        @if($saleCar->accessories->count() > 0)
                        @foreach($saleCar->accessories->where('pivot.type', 'gift') as $index => $a)
                        <tr data-id="{{ $a->id }}" data-price="{{ $a->pivot->price }}" data-com="{{ $a->pivot->commission }}">
                          <td>{{ $index + 1 }}</td>
                          <td>{{ $a->accessory_id }}</td>
                          <td>{{ $a->detail }}</td>
                          <td>{{ ucfirst($a->pivot->price_type) }}</td>
                          <td>
                            {{ number_format($a->pivot->price, 2) }}
                            (
                            @if($a->pivot->commission && $a->pivot->commission > 0)
                            {{ number_format($a->pivot->commission, 2) }}
                            @else
                            -
                            @endif
                            )
                          </td>
                          <td>
                            <button type="button" class="btn btn-sm btn-danger btn-delete-gift">
                              <i class="bx bx-trash"></i>
                            </button>
                          </td>
                        </tr>
                        @endforeach
                        @else
                        <tr id="no-data-row">
                          <td colspan="6" class="text-center">ยังไม่มีข้อมูล</td>
                        </tr>
                        @endif
                      </tbody>

                      <tfoot>
                        <tr id="total-row">
                          <td colspan="5" class="text-end money-input text-black fw-bold">ยอดรวมทั้งหมด</td>
                          <td id="total-price-gift" class="text-end money-input fw-bold">0</td>
                        </tr>
                      </tfoot>
                    </table>
                  </div>

                  <div class="mt-6 d-flex justify-content-end gap-2">
                    <button id="prevCampaign" class="btn btn-danger">ย้อนกลับ</button>
                    <button id="nextExtra" class="btn btn-primary">ถัดไป</button>
                  </div>

                </div>

                <!-- extra -->
                <div class="tab-pane fade" id="tab-extra" role="tabpanel">
                  <div class="d-flex align-items-center justify-content-between pb-2 mb-3 position-relative">
                    <div class="flex-grow-1 text-center">
                      <h4 class="mb-0">รายการซื้อเพิ่ม</h4>
                    </div>
                    <button type="button" class="btn btn-secondary btn-md btnExtra ms-2">
                      <i class="bx bx-plus me-1"></i> เพิ่ม
                    </button>
                  </div>

                  <input type="hidden" id="TotalAccessoryExtra" name="TotalAccessoryExtra">
                  <input type="hidden" id="extra_ids" name="extra_ids">
                  <input type="hidden" id="total_extra_used" name="total_extra_used">
                  <input type="hidden" id="total_extra_com" name="AccessoryExtraCom">

                  <div class="table-responsive text-nowrap">
                    <table class="table table-bordered" id="extraTable">
                      <thead>
                        <tr>
                          <th>No.</th>
                          <th>รหัส</th>
                          <th>รายละเอียด</th>
                          <th>ประเภทราคา</th>
                          <th>ราคา (ค่าคอม)</th>
                          <th>ลบ</th>
                        </tr>
                      </thead>
                      <tbody>
                        @if($saleCar->accessories->count() > 0)
                        @foreach($saleCar->accessories->where('pivot.type', 'extra') as $index => $a)
                        <tr data-id="{{ $a->id }}" data-price="{{ $a->pivot->price }}" data-com="{{ $a->pivot->commission }}">
                          <td>{{ $index + 1 }}</td>
                          <td>{{ $a->accessory_id }}</td>
                          <td>{{ $a->detail }}</td>
                          <td>{{ ucfirst($a->pivot->price_type) }}</td>
                          <td>
                            {{ number_format($a->pivot->price, 2) }}
                            (
                            @if($a->pivot->commission && $a->pivot->commission > 0)
                            {{ number_format($a->pivot->commission, 2) }}
                            @else
                            -
                            @endif
                            )
                          </td>
                          <td>
                            <button type="button" class="btn btn-sm btn-danger btn-delete-extra">
                              <i class="bx bx-trash"></i>
                            </button>
                          </td>
                        </tr>
                        @endforeach
                        @else
                        <tr id="no-data-extra">
                          <td colspan="6" class="text-center">ยังไม่มีข้อมูล</td>
                        </tr>
                        @endif
                      </tbody>

                      <tfoot>
                        <tr id="total-row">
                          <td colspan="5" class="text-end money-input text-black fw-bold">ยอดรวมทั้งหมด</td>
                          <td id="total-price-extra" class="text-end money-input fw-bold">0</td>
                        </tr>
                      </tfoot>
                    </table>
                  </div>

                  <div class="mt-6 d-flex justify-content-end gap-2">
                    <button id="prevAccessory" class="btn btn-danger">ย้อนกลับ</button>
                    <button id="nextCar" class="btn btn-primary">ถัดไป</button>
                  </div>

                </div>

                <!-- total price -->
                <div class="tab-pane fade" id="tab-car" role="tabpanel">
                  <h4 class="pb-2 mb-3">สรุปยอด</h4>
                  <div class="row g-6">
                    <div class="col-md-3">
                      <label for="payment_mode" class="form-label">ประเภทการซื้อ</label>
                      <select id="payment_mode" name="payment_mode" class="form-select" required>
                        <option value="">-- เลือกประเภท --</option>
                        <option value="finance" {{ $saleCar->payment_mode == 'finance' ? 'selected' : '' }}>ผ่อน</option>
                        <option value="non-finance" {{ $saleCar->payment_mode == 'non-finance' ? 'selected' : '' }}>เงินสด</option>
                      </select>
                    </div>

                    <div class="col-md-2">
                      <label for="summaryTurn" class="form-label">ยอดเทิร์น</label>
                      <input id="summaryTurn" class="form-control text-end money-input" disabled />
                    </div>

                    <div class="col-md-2">
                      <label for="summaryCashDeposit" class="form-label">เงินจอง</label>
                      <input id="summaryCashDeposit" class="form-control text-end money-input" disabled />
                    </div>

                    <div class="col-md-2">
                      <label for="summaryExtraTotal" class="form-label">ลูกค้าจ่ายเพิ่ม</label>
                      <input class="form-control text-end money-input" type="text" id="summaryExtraTotal" disabled />
                    </div>

                    <div class="col-md-3">
                      <label for="summaryCarSale" class="form-label">ราคารถ</label>
                      <input id="summaryCarSale" class="form-control text-end money-input" disabled />
                    </div>

                    <input type="hidden" id="remaining_date"
                      name="remaining_date"
                      value="{{ old('remaining_date', $remainingPayment->date ?? '') }}">

                    <input type="hidden" id="RegistrationProvince"
                      name="RegistrationProvince"
                      value="{{ old('RegistrationProvince', $saleCar->RegistrationProvince ?? '') }}">

                    <input type="hidden" id="balance"
                      name="balance"
                      value="{{ old('balance', $saleCar->balance ?? '') }}">

                    <input type="hidden" id="remainingCondition"
                      name="remainingCondition"
                      value="{{ old('remainingCondition', $remainingPayment->type ?? '') }}">

                    <div id="nonFinanceSelect" style="display:none">
                      <div class="row g-6">
                        <div class="col-md-3">
                          <label for="remainingConditionSelect" class="form-label">ประเภทการชำระ</label>
                          <select id="remainingConditionSelect" class="form-select">
                            <option value="">-- เลือกประเภท --</option>
                            <option value="cash" {{ $remainingPayment?->type == 'cash' ? 'selected' : '' }}>เงินสด</option>
                            <option value="credit" {{ $remainingPayment?->type == 'credit' ? 'selected' : '' }}>บัตรเครดิต</option>
                            <option value="check" {{ $remainingPayment?->type == 'check' ? 'selected' : '' }}>เช็คธนาคาร</option>
                            <option value="transfer" {{ $remainingPayment?->type == 'transfer' ? 'selected' : '' }}>เงินโอน</option>
                          </select>
                        </div>

                        <div class="col-md-2">
                          <label for="PaymentDiscount" class="form-label">ส่วนลด</label>
                          <input class="form-control text-end money-input" type="text" id="PaymentDiscount" name="PaymentDiscount"
                            value="{{ $saleCar->PaymentDiscount }}" />
                        </div>

                        <div class="col-md-3">
                          <label for="balance_display" class="form-label">ยอดคงเหลือ</label>
                          <input id="balance_display" class="form-control text-end money-input balance-display" type="text"
                            value="{{ $saleCar->balance }}" readonly />
                        </div>

                        <div class="col-md-2">
                          <label for="remaining_date_cash" class="form-label">วันที่จ่ายเงิน</label>
                          <input id="remaining_date_cash" type="date"
                            class="form-control"
                            name="remaining_date_cash" value="{{ old('remaining_date', $remainingPayment?->date ?? '') }}">
                        </div>

                        <div class="col-md-2">
                          <label for="RegistrationProvince_cash" class="form-label">จังหวัดที่ขึ้นทะเบียน</label>
                          <select id="RegistrationProvince_cash" name="RegistrationProvince_cash" class="registration-province form-select" required>
                            <option value="">-- เลือกจังหวัด --</option>
                            @foreach ($provinces as $p)
                            <option value="{{ @$p->id }}" {{ $saleCar->RegistrationProvince == $p->id ? 'selected' : '' }}>{{ @$p->name }}</option>
                            @endforeach
                          </select>
                        </div>
                      </div>
                    </div>

                    <div id="financeRemain">
                      <div class="row g-6">
                        <div class="col-md-2">
                          <label for="MarkupPrice" class="form-label">บวกหัว</label>
                          <input class="form-control text-end money-input" type="text" id="MarkupPrice" name="MarkupPrice"
                            value="{{ $saleCar->MarkupPrice }}" />
                        </div>
                        <div class="col-md-2">
                          <label for="Markup90" class="form-label">บวกหัว (90%)</label>
                          <input class="form-control text-end money-input" type="text" id="Markup90" name="Markup90"
                            value="{{ $saleCar->Markup90 }}" />
                        </div>

                        <div class="col-md-3">
                          <label for="CarSalePriceFinal" class="form-label">ราคาขายสุทธิ (รวมบวกหัว)</label>
                          <input class="form-control text-end money-input" type="text" name="CarSalePriceFinal" id="CarSalePriceFinal"
                            value="{{ $saleCar->CarSalePriceFinal }}" readonly />
                        </div>

                        <div class="col-md-2">
                          <label for="DownPayment" class="form-label">เงินดาวน์</label>
                          <input class="form-control text-end money-input" type="text" name="DownPayment" id="DownPayment"
                            value="{{ $saleCar->DownPayment }}" />
                        </div>
                        <div class="col-md-1">
                          <label for="DownPaymentPercentage" class="form-label">%</label>
                          <input class="form-control text-end money-input" type="text" name="DownPaymentPercentage" id="DownPaymentPercentage"
                            value="{{ $saleCar->DownPaymentPercentage }}" />
                        </div>
                        <div class="col-md-2">
                          <label for="DownPaymentDiscount" class="form-label">ส่วนลดเงินดาวน์</label>
                          <input class="form-control text-end money-input" type="text" name="DownPaymentDiscount" id="DownPaymentDiscount"
                            value="{{ $saleCar->DownPaymentDiscount }}" />
                        </div>

                        <div class="col-md-3">
                          <label for="remaining_finance" class="form-label">ชื่อไฟแนนซ์</label>
                          <select id="remaining_finance" name="remaining_finance" class="form-select" required>
                            <option value="">-- เลือกไฟแนนซ์ --</option>
                            @foreach ($finances as $f)
                            <option value="{{ $f->id }}"
                              data-max-year="{{ $f->max_year }}"
                              {{ old('remaining_finance', $saleCar->remainingPayment->financeInfo->id ?? '') == $f->id ? 'selected' : '' }}>
                              {{ $f->FinanceCompany }}
                            </option>
                            @endforeach
                          </select>
                        </div>

                        <div class="col-md-2">
                          <label for="balanceFinanceDisplay" class="form-label">ยอดจัดไฟแนนซ์</label>
                          <input class="form-control text-end money-input" type="text" id="balanceFinanceDisplay"
                            value="{{ $saleCar->balanceFinance }}" readonly />
                        </div>

                        <input type="hidden" id="balanceFinance" name="balanceFinance"
                          value="{{ old('balanceFinance', $saleCar->balanceFinance ?? '') }}">

                        <div class="col-md-1">
                          <label for="remaining_interest" class="form-label">ดอกเบี้ย</label>
                          <input class="form-control text-end"
                            type="text"
                            id="remaining_interest"
                            name="remaining_interest"
                            oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1')"
                            value="{{ old('remaining_interest', $remainingPayment->interest ?? '') }}">
                        </div>

                        <div class="col-md-2">
                          <label for="remaining_period" class="form-label">งวดผ่อน</label>
                          <select id="remaining_period"
                            name="remaining_period"
                            class="form-select"
                            data-selected="{{ $remainingPayment->period ?? '' }}">
                            <option value="">-- เลือกงวด --</option>
                          </select>
                        </div>

                        <div class="col-md-2">
                          <label for="remaining_type_com" class="form-label">ดอกเบี้ยคอม</label>
                          <select id="remaining_type_com" name="remaining_type_com" class="form-select" required>
                            <option value="">-- เลือก --</option>
                            <option value="0" {{ $remainingPayment?->type_com == '0' ? 'selected' : '' }}>C4</option>
                            <option value="8" {{ $remainingPayment?->type_com == '8' ? 'selected' : '' }}>C8</option>
                            <option value="10" {{ $remainingPayment?->type_com == '10' ? 'selected' : '' }}>C10</option>
                            <option value="12" {{ $remainingPayment?->type_com == '12' ? 'selected' : '' }}>C12</option>
                            <option value="14" {{ $remainingPayment?->type_com == '14' ? 'selected' : '' }}>C14</option>
                            <option value="16" {{ $remainingPayment?->type_com == '16' ? 'selected' : '' }}>C16</option>
                          </select>
                        </div>

                        <div class="col-md-2">
                          <label for="remaining_total_com" class="form-label">ยอดเงินค่าคอม</label>
                          <input id="remaining_total_com" name="remaining_total_com"
                            class="form-control text-end money-input" type="text"
                            value="{{ old('remaining_total_com', $remainingPayment->total_com ?? '') }}">
                        </div>

                        <div class="col-md-2">
                          <label for="remaining_alp" class="form-label">ค่างวด (กรณีไม่มี ALP)</label>
                          <input id="remaining_alp" name="remaining_alp"
                            class="form-control text-end money-input" type="text"
                            value="{{ old('remaining_alp', $remainingPayment->alp ?? '') }}" readonly />
                        </div>
                        <div class="col-md-2">
                          <label for="remaining_including_alp" class="form-label">ค่างวด (รวม ALP)</label>
                          <input id="remaining_including_alp" name="remaining_including_alp"
                            class="form-control text-end money-input" type="text"
                            value="{{ old('remaining_including_alp', $remainingPayment->including_alp ?? '') }}">
                        </div>

                        <div class="col-md-2">
                          <label for="remaining_total_alp" class="form-label">ยอดเงิน ALP</label>
                          <input id="remaining_total_alp" name="remaining_total_alp"
                            class="form-control text-end money-input" type="text"
                            value="{{ old('remaining_total_alp', $remainingPayment->total_alp ?? '') }}">
                        </div>

                        <div class="col-md-2">
                          <label for="remaining_date_finance" class="form-label">วันที่ไฟแนนซ์จ่ายเงิน</label>
                          <input id="remaining_date_finance" type="date"
                            class="form-control"
                            name="remaining_date_finance" value="{{ old('remaining_date', $remainingPayment?->date ?? '') }}">
                        </div>

                        <div class="col-md-2">
                          <label for="remaining_po_number" class="form-label">PO-Number</label>
                          <input id="remaining_po_number" type="text"
                            class="form-control"
                            name="remaining_po_number" value="{{ old('remaining_po_number', $remainingPayment?->po_number ?? '') }}">
                        </div>

                        <div class="col-md-2">
                          <label for="remaining_po_date" class="form-label">วันที่ PO</label>
                          <input id="remaining_po_date" type="date"
                            class="form-control"
                            name="remaining_po_date" value="{{ old('remaining_po_date', $remainingPayment?->po_date ?? '') }}">
                        </div>

                        @php
                        $deliveryType = $deliveryPayment->type ?? '';
                        @endphp

                        <div class="col-md-2">
                          <label for="RegistrationProvince_finance" class="form-label">จังหวัดที่ขึ้นทะเบียน</label>
                          <select id="RegistrationProvince_finance" name="RegistrationProvince_finance" class="registration-province form-select" required>
                            <option value="">-- เลือกจังหวัด --</option>
                            @foreach ($provinces as $p)
                            <option value="{{ @$p->id }}" {{ $saleCar->RegistrationProvince == $p->id ? 'selected' : '' }}>{{ @$p->name }}</option>
                            @endforeach
                          </select>
                        </div>

                        <div class="col-md-2">
                          <label for="TotalPaymentatDeliveryCar" class="form-label">สรุปค่าใช้จ่ายวันออกรถ</label>
                          <input class="form-control text-end money-input" type="text" id="TotalPaymentatDeliveryCar" name="delivery_cost"
                            value="{{ old('delivery_cost', $deliveryPayment->cost ?? '') }}" readonly />
                        </div>

                        <input type="hidden" id="TotalPaymentatDelivery" name="TotalPaymentatDelivery">

                        <div class="col-md-2">
                          <label for="delivery_date" class="form-label">วันที่จ่ายเงินค่าออกรถ</label>
                          <input id="delivery_date" type="date"
                            class="form-control"
                            name="delivery_date" value="{{ old('delivery_date', $deliveryPayment?->date ?? '') }}">
                        </div>

                        <div class="col-md-6">
                          <fieldset class="mb-0">
                            <legend class="form-label fw-semibold mb-2" style="font-size: 1rem;">ประเภทการจ่ายเงินวันออกรถ</legend>

                            <div class="form-check form-check-inline" style="margin-left: 15px">
                              <input class="form-check-input" type="radio" name="deliveryCondition" id="cashDeli" value="cash"
                                {{ $deliveryType === 'cash' ? 'checked' : '' }}>
                              <label class="form-check-label" for="cashDeli">เงินสด</label>
                            </div>

                            <div class="form-check form-check-inline">
                              <input class="form-check-input" type="radio" name="deliveryCondition" id="creditDeli" value="credit"
                                {{ $deliveryType === 'credit' ? 'checked' : '' }}>
                              <label class="form-check-label" for="creditDeli">บัตรเครดิต</label>
                            </div>

                            <div class="form-check form-check-inline" style="margin-left: 15px">
                              <input class="form-check-input" type="radio" name="deliveryCondition" id="checkDeli" value="check"
                                {{ $deliveryType === 'check' ? 'checked' : '' }}>
                              <label class="form-check-label" for="checkDeli">เช็คธนาคาร</label>
                            </div>

                            <div class="form-check form-check-inline" style="margin-left: 15px">
                              <input class="form-check-input" type="radio" name="deliveryCondition" id="transDeli" value="transfer"
                                {{ $deliveryType === 'transfer' ? 'checked' : '' }}>
                              <label class="form-check-label" for="transDeli">เงินโอน</label>
                            </div>
                          </fieldset>
                        </div>

                        <div id="creditDelivery">
                          <div class="row">
                            <div class="col-md-4">
                              <label for="delivery_credit" class="form-label">บัตรเครดิต</label>
                              <input id="delivery_credit" type="text"
                                class="form-control"
                                name="delivery_credit" value="{{ old('delivery_credit', $deliveryPayment->credit ?? '') }}">
                            </div>

                            <div class="col-md-2">
                              <label for="delivery_tax_credit" class="form-label">ค่าธรรมเนียม</label>
                              <input id="delivery_tax_credit" type="text"
                                class="form-control text-end money-input"
                                name="delivery_tax_credit" value="{{ old('delivery_tax_credit', $deliveryPayment->tax_credit ?? '') }}">
                            </div>
                          </div>
                        </div>

                        <div id="checkDelivery">
                          <div class="row">
                            <div class="col-md-3">
                              <label for="delivery_check_bank" class="form-label">ธนาคาร</label>
                              <input id="delivery_check_bank" type="text"
                                class="form-control"
                                name="delivery_check_bank" value="{{ old('delivery_check_bank', $deliveryPayment->check_bank ?? '') }}">
                            </div>

                            <div class="col-md-4">
                              <label for="delivery_check_branch" class="form-label">สาขา</label>
                              <input id="delivery_check_branch" type="text"
                                class="form-control"
                                name="delivery_check_branch" value="{{ old('delivery_check_branch', $deliveryPayment->check_branch ?? '') }}">
                            </div>

                            <div class="col-md-3">
                              <label for="delivery_check_no" class="form-label">เลขที่</label>
                              <input id="delivery_check_no" type="text"
                                class="form-control"
                                name="delivery_check_no" value="{{ old('delivery_check_no', $deliveryPayment->check_no ?? '') }}">
                            </div>
                          </div>
                        </div>

                        <div id="bankDelivery">
                          <div class="row">
                            <div class="col-md-3">
                              <label for="delivery_transfer_bank" class="form-label">ธนาคาร</label>
                              <input id="delivery_transfer_bank" type="text"
                                class="form-control"
                                name="delivery_transfer_bank" value="{{ old('delivery_transfer_bank', $deliveryPayment->transfer_bank ?? '') }}">
                            </div>

                            <div class="col-md-4">
                              <label for="delivery_transfer_branch" class="form-label">สาขา</label>
                              <input id="delivery_transfer_branch" type="text"
                                class="form-control"
                                name="delivery_transfer_branch" value="{{ old('delivery_transfer_branch', $deliveryPayment->transfer_branch ?? '') }}">
                            </div>

                            <div class="col-md-3">
                              <label for="delivery_transfer_no" class="form-label">เลขที่</label>
                              <input id="delivery_transfer_no" type="text"
                                class="form-control"
                                name="delivery_transfer_no" value="{{ old('delivery_transfer_no', $deliveryPayment->transfer_no ?? '') }}">
                            </div>
                          </div>
                        </div>

                      </div>
                    </div>

                    <div id="creditRemain">
                      <div class="row">
                        <div class="col-md-3">
                          <label for="remaining_credit" class="form-label">บัตรเครดิต</label>
                          <input id="remaining_credit" type="text"
                            class="form-control"
                            name="remaining_credit"
                            value="{{ old('remaining_credit', $remainingPayment->credit ?? '') }}">
                        </div>

                        <div class="col-md-2">
                          <label for="remaining_tax_credit" class="form-label">ค่าธรรมเนียม</label>
                          <input id="remaining_tax_credit" type="text"
                            class="form-control text-end money-input"
                            name="remaining_tax_credit"
                            value="{{ old('remaining_tax_credit', $remainingPayment->tax_credit ?? '') }}">
                        </div>

                      </div>
                    </div>

                    <div id="checkRemain">
                      <div class="row">
                        <div class="col-md-3">
                          <label for="remaining_check_bank" class="form-label">ธนาคาร</label>
                          <input id="remaining_check_bank" type="text"
                            class="form-control"
                            name="remaining_check_bank"
                            value="{{ old('remaining_check_bank', $remainingPayment->check_bank ?? '') }}">
                        </div>

                        <div class="col-md-2">
                          <label for="remaining_check_branch" class="form-label">สาขา</label>
                          <input id="remaining_check_branch" type="text"
                            class="form-control"
                            name="remaining_check_branch"
                            value="{{ old('remaining_check_branch', $remainingPayment->check_branch ?? '') }}">
                        </div>

                        <div class="col-md-3">
                          <label for="remaining_check_no" class="form-label">เลขที่</label>
                          <input id="remaining_check_no" type="text"
                            class="form-control"
                            name="remaining_check_no"
                            value="{{ old('remaining_check_no', $remainingPayment->check_no ?? '') }}">
                        </div>
                      </div>
                    </div>

                    <div id="bankRemain">
                      <div class="row">
                        <div class="col-md-3">
                          <label for="remaining_transfer_bank" class="form-label">ธนาคาร</label>
                          <input id="remaining_transfer_bank" type="text"
                            class="form-control"
                            name="remaining_transfer_bank"
                            value="{{ old('remaining_transfer_bank', $remainingPayment->transfer_bank ?? '') }}">
                        </div>

                        <div class="col-md-2">
                          <label for="remaining_transfer_branch" class="form-label">สาขา</label>
                          <input id="remaining_transfer_branch" type="text"
                            class="form-control"
                            name="remaining_transfer_branch"
                            value="{{ old('remaining_transfer_branch', $remainingPayment->transfer_branch ?? '') }}">
                        </div>

                        <div class="col-md-3">
                          <label for="remaining_transfer_no" class="form-label">เลขที่</label>
                          <input id="remaining_transfer_no" type="text"
                            class="form-control"
                            name="remaining_transfer_no"
                            value="{{ old('remaining_transfer_no', $remainingPayment->check_no ?? '') }}">
                        </div>

                      </div>
                    </div>

                    <!-- ข้อมูลการจ่ายเงิน -->
                    <!-- <option value="cash" {{ $remainingPayment?->type == 'cash' ? 'selected' : '' }}>เงินสด</option> -->

                    <div id="paymentSection" style="display:none;">

                      <div class="position-relative pt-2 pb-2 border-bottom text-center">
                        <h4 class="mb-0">ข้อมูลการจ่ายเงิน</h4>

                        <button type="button" id="btnAddPayment"
                          class="btn btn-primary position-absolute"
                          style="right: 0; top: 50%; transform: translateY(-50%);">
                          <i class="bx bx-plus"></i> เพิ่ม
                        </button>
                      </div>

                      <div id="paymentContainer">

                        @if($payments->count() > 0)
                        @foreach($payments as $p)
                        <div class="row g-3 mt-3 payment-row">
                          <input type="hidden" name="payment_id[]" value="{{ $p->id }}">

                          <div class="col-md-4">
                            <label for="payment_type_{{ $loop->index ?? 0 }}" class="form-label">ประเภท</label>
                            <select id="payment_type_{{ $loop->index ?? 0 }}" name="payment_type[]" class="form-select">
                              <option value="">-- เลือกประเภท --</option>
                              <option value="cash" {{ $p->type == 'cash' ? 'selected' : '' }}>เงินสด</option>
                              <option value="transfer" {{ $p->type == 'transfer' ? 'selected' : '' }}>เงินโอน</option>
                            </select>
                          </div>

                          <div class="col-md-4">
                            <label for="payment_cost_{{ $loop->index }}" class="form-label">จำนวนเงิน</label>
                            <input id="payment_cost_{{ $loop->index }}" type="text" name="payment_cost[]" class="form-control text-end money-input"
                              value="{{ number_format($p->cost, 0) }}">
                          </div>

                          <div class="col-md-3">
                            <label for="payment_date_{{ $loop->index }}" class="form-label">วันที่จ่ายเงิน</label>
                            <input id="payment_date_{{ $loop->index }}" type="date" name="payment_date[]" class="form-control"
                              value="{{ $p->date }}">
                          </div>

                          <div class="col-md-1 d-flex align-items-end">
                            <button type="button" class="btn btn-danger btnRemove">
                              <i class="bx bx-trash"></i>
                            </button>
                          </div>

                        </div>
                        @endforeach

                        @else
                        <div class="row g-3 mt-3 payment-row">
                          <div class="col-md-4">
                            <label for="payment_type_0" class="form-label">ประเภท</label>
                            <select id="payment_type_0" name="payment_type[]" class="form-select">
                              <option value="">-- เลือกประเภท --</option>
                              <option value="cash">เงินสด</option>
                              <option value="transfer">เงินโอน</option>
                            </select>
                          </div>

                          <div class="col-md-4">
                            <label for="payment_cost_0" class="form-label">จำนวนเงิน</label>
                            <input id="payment_cost_0" type="text" name="payment_cost[]" class="form-control text-end money-input">
                          </div>

                          <div class="col-md-3">
                            <label for="payment_date_0" class="form-label">วันที่จ่ายเงิน</label>
                            <input id="payment_date_0" type="date" name="payment_date[]" class="form-control">
                          </div>

                          <div class="col-md-1 d-flex align-items-end">
                            <button type="button" class="btn btn-danger btnRemove">
                              <i class="bx bx-trash"></i>
                            </button>
                          </div>
                        </div>
                        @endif

                      </div>

                      <input type="hidden" id="deletedPayments" name="deletedPayments" value="">

                    </div>

                    <h4 class="pt-2 pb-2 border-bottom">แนะนำ</h4>
                    <div class="col-md-3 mt-2">
                      <label class="form-label" for="customerSearchRef">ค้นหาข้อมูล</label>
                      <div class="input-group">
                        <input id="customerSearchRef" type="text" class="form-control" name="customerSearchRef" placeholder="พิมพ์ข้อมูล">
                        <button type="button" class="btn btn-outline-secondary btnSearchCustomer" style="cursor:pointer;">
                          <i class="bx bx-search"></i>
                        </button>
                      </div>
                    </div>

                    <!-- แนะนำ -->
                    <input type="hidden" id="ReferrerID" name="ReferrerID" value="{{ $saleCar->ReferrerID }}">

                    <div class="col-md-4 mt-2">
                      <label for="customerIDRef" class="form-label">เลขบัตรประชาชน</label>
                      <input id="customerIDRef" type="text" class="form-control"
                        value="{{ $saleCar->customerReferrer->formatted_id_number ?? '' }}" readonly>
                    </div>

                    <div class="col-md-2 mt-2">
                      <label class="form-label" for="ReferrerAmount">ยอดเงินค่าแนะนำ</label>
                      <input id="ReferrerAmount" type="text"
                        class="form-control text-end money-input"
                        name="ReferrerAmount" value="{{ $saleCar->ReferrerAmount }}" />
                    </div>

                    @if ($userRole === 'sale' && ($saleCar->GMApprovalSignature || $saleCar->ApprovalSignature))
                    <h4 class="pt-2 pb-2 border-bottom">ข้อมูลวันส่งมอบ</h4>

                    <div class="col-md-12">
                      <label for="KeyInDate" class="form-label">วันที่ส่งเอกสารสรุปการขาย</label>
                      <input class="form-control" type="date" id="KeyInDate" name="KeyInDate" value="{{ $saleCar->KeyInDate }}" />
                    </div>

                    <div class="col-md-12">
                      <label for="DeliveryDate" class="form-label">วันส่งมอบจริง (วันที่แจ้งประกัน)</label>
                      <input class="form-control" type="date" id="DeliveryDate" name="DeliveryDate" value="{{ $saleCar->DeliveryDate }}" />
                    </div>
                    @else
                    <h4 class="pt-2 pb-2 border-bottom">ข้อมูลวันส่งมอบ</h4>

                    <div class="col-md-12">
                      <label for="KeyInDate" class="form-label">วันที่ส่งเอกสารสรุปการขาย</label>
                      <input class="form-control" type="date" id="KeyInDate" name="KeyInDate" value="{{ $saleCar->KeyInDate }}" />
                    </div>

                    <div class="col-md-12">
                      <label for="DeliveryDate" class="form-label">วันส่งมอบจริง (วันที่แจ้งประกัน)</label>
                      <input class="form-control" type="date" id="DeliveryDate" name="DeliveryDate" value="{{ $saleCar->DeliveryDate }}" />
                    </div>
                    @endif

                    <h4 class="pt-2 pb-2 border-bottom">ยอดค่าคอม sale</h4>

                    <div class="col-md-3">
                      <label for="CommissionSaleDisplay" class="form-label">Commission Sale</label>
                      <input type="text"
                        class="form-control text-end money-input"
                        id="CommissionSaleDisplay"
                        value="{{ $saleCar->CommissionSale }}"
                        readonly>
                    </div>

                    <input type="hidden" name="CommissionSale" id="CommissionSale"
                      value="{{ old('CommissionSale', $saleCar->CommissionSale ?? '') }}">

                  </div>

                  @if ($userRole === 'sale')
                  <div class="mt-6 d-flex justify-content-end gap-2">
                    <button id="prevExtra" class="btn btn-danger">ย้อนกลับ</button>
                    <button type="button" class="btn btn-info" id="btnPreviewCar">
                      ตรวจสอบ
                    </button>
                  </div>
                  @else
                  <div class="mt-6 d-flex justify-content-end gap-2">
                    <button id="prevExtra" class="btn btn-danger">ย้อนกลับ</button>
                    <button id="nextDate" class="btn btn-primary">ถัดไป</button>
                  </div>
                  @endif

                </div>

              </div>
            </div>

          </div>

          <!-- more : date, approve -->
          <div class="tab-pane fade" id="tab-more" role="tabpanel">
            <div class="nav-align-top">
              <div class="tab-content">

                <div class="row g-6">
                  <h4 class="pt-2 pb-2 border-bottom">วันส่งมอบและผู้อนุมัติ</h4>

                  <div class="col-md-12">
                    <label for="DeliveryInDMSDate" class="form-label">วันที่ส่งมอบในระบบ DMS</label>
                    <input class="form-control" type="date" id="DeliveryInDMSDate" name="DeliveryInDMSDate" value="{{ $saleCar->DeliveryInDMSDate }}" />
                  </div>
                  <div class="col-md-12">
                    <label for="DeliveryInCKDate" class="form-label">วันที่ส่งมอบตามยอดชูเกียรติ</label>
                    <input class="form-control" type="date" id="DeliveryInCKDate" name="DeliveryInCKDate" value="{{ $saleCar->DeliveryInCKDate }}" />
                  </div>

                  <div class="col-md-12">
                    <fieldset class="mb-0">
                      <legend class="form-label fw-semibold mb-2" style="font-size: 1rem;">เช็ครายการ (แอดมินขาย)</legend>
                      <div class="d-flex align-items-center justify-content-between p-3 border rounded-3">

                        <div class="form-check m-0">
                          <input class="form-check-input" type="checkbox" id="AdminSignature" name="AdminSignature"
                            value="1" {{ $saleCar->AdminSignature ? 'checked' : '' }}>
                          <label class="form-check-label" for="AdminSignature">
                            เช็คเรียบร้อยแล้ว
                          </label>
                        </div>

                        <div class="ms-3 d-flex align-items-center flex-nowrap">
                          <label for="AdminCheckedDate"
                            style="white-space: nowrap; font-size: 1.0rem; color: #1d1c1cff; margin-right: 12px;">
                            วันที่แอดมินเช็ครายการ :
                          </label>

                          <input class="form-control"
                            type="date"
                            id="AdminCheckedDate"
                            name="AdminCheckedDate"
                            style="min-width: 220px; max-width: 260px;"
                            value="{{ $saleCar->AdminCheckedDate }}">
                        </div>

                      </div>
                    </fieldset>
                  </div>

                  <div class="col-md-12">
                    <fieldset class="mb-0">
                      <legend class="form-label fw-semibold mb-2" style="font-size: 1rem;">ตรวจสอบรายการ (IA)</legend>
                      <div class="d-flex align-items-center justify-content-between p-3 border rounded-3">

                        <div class="form-check m-0">
                          <input class="form-check-input" type="checkbox" id="CheckerID" name="CheckerID"
                            value="1" {{ $saleCar->CheckerID ? 'checked' : '' }}>
                          <label class="form-check-label" for="CheckerID">
                            เช็คเรียบร้อยแล้ว
                          </label>
                        </div>

                        <div class="ms-3 d-flex align-items-center flex-nowrap">
                          <label for="CheckerCheckedDate"
                            style="white-space: nowrap; font-size: 1.0rem; color: #1d1c1cff; margin-right: 12px;">
                            วันที่ฝ่ายตรวจสอบเช็ครายการ :
                          </label>

                          <input class="form-control"
                            type="date"
                            id="CheckerCheckedDate"
                            name="CheckerCheckedDate"
                            style="min-width: 220px; max-width: 260px;"
                            value="{{ $saleCar->CheckerCheckedDate }}">
                        </div>

                      </div>
                    </fieldset>
                  </div>

                  <div class="col-md-12">
                    <fieldset class="mb-0">
                      <legend class="form-label fw-semibold mb-2" style="font-size: 1rem;">อนุมัติรายการ (ผู้จัดการขาย)</legend>
                      <div class="d-flex align-items-center justify-content-between p-3 border rounded-3">

                        <div class="form-check m-0">
                          <input class="form-check-input" type="checkbox" id="SMSignature" name="SMSignature"
                            value="1" {{ $saleCar->SMSignature ? 'checked' : '' }}>
                          <label class="form-check-label" for="SMSignature">
                            เช็คเรียบร้อยแล้ว
                          </label>
                        </div>

                        <div class="ms-3 d-flex align-items-center flex-nowrap">
                          <label for="SMCheckedDate"
                            style="white-space: nowrap; font-size: 1.0rem; color: #1d1c1cff; margin-right: 12px;">
                            วันที่ผู้จัดการขายอนุมัติ :
                          </label>

                          <input class="form-control"
                            type="date"
                            id="SMCheckedDate"
                            name="SMCheckedDate"
                            style="min-width: 220px; max-width: 260px;"
                            value="{{ $saleCar->SMCheckedDate }}">
                        </div>

                      </div>
                    </fieldset>
                  </div>

                  <div class="col-md-12">
                    <fieldset class="mb-0">
                      <legend class="form-label fw-semibold mb-2" style="font-size: 1rem;">ผู้จัดการอนุมัติการขาย</legend>
                      <div class="d-flex align-items-center justify-content-between p-3 border rounded-3">

                        <div class="form-check m-0">
                          <input class="form-check-input" type="checkbox" id="ApprovalSignature" name="ApprovalSignature"
                            value="1" {{ $saleCar->ApprovalSignature ? 'checked' : '' }}>
                          <label class="form-check-label" for="ApprovalSignature">
                            อนุมัติ
                          </label>
                        </div>

                        <div class="ms-3 d-flex align-items-center flex-nowrap">
                          <label for="ApprovalSignatureDate"
                            style="white-space: nowrap; font-size: 1.0rem; color: #1d1c1cff; margin-right: 12px;">
                            วันที่ผู้จัดการอนุมัติการขาย :
                          </label>

                          <input class="form-control"
                            type="date"
                            id="ApprovalSignatureDate"
                            name="ApprovalSignatureDate"
                            style="min-width: 220px; max-width: 260px;"
                            value="{{ $saleCar->ApprovalSignatureDate }}">
                        </div>

                      </div>
                    </fieldset>
                  </div>

                  <div class="col-md-12">
                    <fieldset class="mb-0">
                      <legend class="form-label fw-semibold mb-2" style="font-size: 1rem;">GM อนุมัติกรณีงบเกิน (N)</legend>
                      <div class="d-flex align-items-center justify-content-between p-3 border rounded-3">

                        <div class="form-check m-0">
                          <input class="form-check-input" type="checkbox" id="GMApprovalSignature" name="GMApprovalSignature"
                            value="1" {{ $saleCar->GMApprovalSignature ? 'checked' : '' }}>
                          <label class="form-check-label fw-semibold" for="GMApprovalSignature">
                            อนุมัติ
                          </label>
                        </div>

                        <div class="ms-3 d-flex align-items-center flex-nowrap">
                          <label for="GMApprovalSignatureDate"
                            style="white-space: nowrap; font-size: 1.0rem; color: #1d1c1cff; margin-right: 12px;">
                            วันที่ GM อนุมัติกรณีงบเกิน :
                          </label>

                          <input class="form-control"
                            type="date"
                            id="GMApprovalSignatureDate"
                            name="GMApprovalSignatureDate"
                            style="min-width: 220px; max-width: 260px;"
                            value="{{ $saleCar->GMApprovalSignatureDate }}">
                        </div>

                      </div>
                    </fieldset>
                  </div>

                  <h4 class="pt-2 pb-2 border-bottom">สถานะ</h4>
                  <div class="col-md-12">
                    <label for="con_status" class="form-label">สถานะ</label>
                    <select id="con_status" name="con_status" class="form-select" required>
                      <option value="">-- เลือกสถานะ --</option>
                      @foreach ($conStatus as $con)
                      <option value="{{ @$con->id }}" {{ $saleCar->con_status == $con->id ? 'selected' : '' }}>{{ @$con->name }}</option>
                      @endforeach
                    </select>
                  </div>

                </div>

                <div class="mt-6 d-flex justify-content-end gap-2">
                  <button id="prevCar" class="btn btn-danger">ย้อนกลับ</button>
                  <button type="button" class="btn btn-info" id="btnPreviewMore">
                    ตรวจสอบ
                  </button>
                  <!-- <button type="submit" class="btn btn-primary btnUpdatePurchase">บันทึก</button> -->
                </div>

              </div>
            </div>
          </div>

        </div>
      </div>

    </form>
  </div>
</div>

@include('purchase-order.accessory-gift.gift')
@include('purchase-order.accessory-gift.extra')
@include('purchase-order.search-car-order.order')
@include('purchase-order.search-customer.search')
@include('purchase-order.preview.preview')

<style>
  .swal2-container {
    z-index: 20000 !important;
  }

  .card {
    border-radius: 10px;
    background-color: #e7f1ff;
  }

  .select2-container .select2-selection--multiple {
    min-height: 38px !important;
    height: auto !important;
    padding-top: 4px !important;
  }
</style>

@endsection