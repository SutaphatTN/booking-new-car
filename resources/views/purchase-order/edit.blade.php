@extends('layouts/contentNavbarLayout')
@section('title', $isHistory ? 'Purchase Order History' : 'Edit Purchase Order')

@section('page-style')
  @vite(['resources/css/app.css'])
@endsection

@section('page-script')
  @vite(['resources/assets/js/purchase-order.js'])
@endsection

@php
  $readonly = $isHistory ? 'readonly' : '';
  $disabled = $isHistory ? 'disabled' : '';
  $carLocked = !empty($saleCar->CarOrderID);
  $carLockedStyle = $carLocked ? 'pointer-events:none;background-color:#f8f9fa;' : '';
@endphp

@section('content')
  <div id="viewGift"></div>
  <div id="viewExtra"></div>
  <div id="searchCarOrder"></div>
  <div id="viewPreviewPurchase"></div>
  <div id="searchCustomer"></div>

  <div class="row">
    <div class="col-md-12">
      <h6 class="text-body-secondary">ข้อมูลการจอง</h6>
      @if (!$isHistory)
        <form id="purchaseForm" action="{{ route('purchase-order.update', $saleCar->id) }}" method="POST"
          enctype="multipart/form-data">
        @else
          <form id="purchaseForm">
      @endif

      @csrf
      @method('PUT')

      <div class="nav-align-top">
        <input type="hidden" id="userRole" value="{{ $userRole }}">
        <input type="hidden" id="userBrand" value="{{ auth()->user()->brand }}">

        <ul class="nav nav-pills mb-4 nav-fill" role="tablist">

          <li class="nav-item mb-1 mb-sm-0">
            <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
              data-bs-target="#tab-detail" aria-controls="tab-detail" aria-selected="true">
              <span class="d-none d-sm-inline-flex align-items-center">
                <i class="icon-base bx bx-spreadsheet icon-sm me-1_5"></i>ข้อมูลลูกค้า
                <!-- <span class="badge rounded-pill badge-center h-px-20 w-px-20 bg-danger ms-1_5">3</span> -->
              </span>
              <i class="icon-base bx bx-spreadsheet icon-sm d-sm-none"></i>
            </button>
          </li>

          <li class="nav-item mb-1 mb-sm-0">
            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-price"
              aria-controls="tab-price" aria-selected="false">
              <span class="d-none d-sm-inline-flex align-items-center"><i
                  class="icon-base bx bx-credit-card icon-sm me-1_5"></i>สรุปการขาย</span>
              <i class="icon-base bx bx-credit-card icon-sm d-sm-none"></i>
            </button>
          </li>

          @if ($userRole == 'admin' || $userRole == 'audit' || $userRole == 'manager' || $userRole == 'md')
            <li class="nav-item mb-1 mb-sm-0">
              <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-more"
                aria-controls="tab-more" aria-selected="false">
                <span class="d-none d-sm-inline-flex align-items-center"><i
                    class="icon-base bx bx-slider icon-sm me-1_5"></i>ข้อมูลเพิ่มเติม</span>
                <i class="icon-base bx bx-slider icon-sm d-sm-none"></i>
              </button>
            </li>
          @endif
        </ul>

        <div class="tab-content">

          <div class="tab-pane fade show active" id="tab-detail" role="tabpanel">

            {{-- hidden fields --}}
            <input type="hidden" name="SaleID" value="{{ $saleCar->SaleID }}">
            <input type="hidden" id="CusID" name="CusID" value="{{ $saleCar->CusID }}">
            <input type="hidden" id="CusCurrentAddress"
              value="{{ $saleCar->customer->currentAddress->full_address ?? '-' }}">
            <input type="hidden" id="CusDocumentAddress"
              value="{{ $saleCar->customer->documentAddress->full_address ?? '-' }}">

            {{-- Section 1 : ข้อมูลลูกค้า --}}
            <div class="po-section-edit">
              <div class="po-section-header">
                <div class="po-section-icon sky"><i class="bx bx-user"></i></div>
                <h6 class="po-section-title">ข้อมูลลูกค้า</h6>
              </div>
              <div class="po-section-body-edit">
                <div class="row g-3">
                  <div class="col-md-3">
                    <label for="sale_name" class="po-label"><i class="bx bx-user-pin"></i> ชื่อ - นามสกุล ผู้ขาย</label>
                    <input id="sale_name" class="form-control" type="text"
                      value="{{ $saleCar->saleUser->name ?? '-' }}" readonly>
                  </div>

                  <div class="col-md-4">
                    <label for="CusFullName" class="po-label"><i class="bx bx-user"></i> ชื่อ - นามสกุล ผู้จอง</label>
                    <input type="text" id="CusFullName" class="form-control"
                      value="{{ $saleCar->customer->prefix->Name_TH ?? '' }} {{ $saleCar->customer->FirstName ?? '' }} {{ $saleCar->customer->LastName ?? '' }}"
                      readonly>
                  </div>

                  <div class="col-md-3">
                    <label for="IDNumber" class="po-label"><i class="bx bx-id-card"></i> เลขบัตรประชาชน</label>
                    <input class="form-control" type="text" name="IDNumber" id="IDNumber"
                      value="{{ $saleCar->customer->formatted_id_number ?? '-' }}" readonly>
                  </div>

                  <div class="col-md-2">
                    <label for="CusMobile" class="po-label"><i class="bx bx-phone"></i> เบอร์โทรศัพท์</label>
                    <input class="form-control" id="CusMobile" type="text" name="Mobilephone1"
                      value="{{ $saleCar->customer->formatted_mobile ?? '-' }}" readonly>
                  </div>

                  <div class="col-md-2">
                    <label for="BookingDate" class="po-label"><i class="bx bx-calendar"></i> วันที่จอง</label>
                    <input id="BookingDate" type="date" class="form-control" name="BookingDate"
                      value="{{ $saleCar->BookingDate }}" required>
                  </div>

                  <div class="col-md-2">
                    <label for="type" class="po-label"><i class="bx bx-map-pin"></i> แหล่งที่มา</label>
                    <select id="type" name="type" class="form-select">
                      <option value="">— เลือก —</option>
                      @foreach ($type as $item)
                        <option value="{{ @$item->id }}" {{ $saleCar->type == $item->id ? 'selected' : '' }}>
                          {{ @$item->name }}
                        </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-2">
                    <label for="type_sale" class="po-label"><i class="bx bx-tag"></i> ประเภทการขาย</label>
                    <select id="type_sale" name="type_sale" class="form-select">
                      <option value="">— เลือก —</option>
                      @foreach ($typeSale as $item)
                        <option value="{{ @$item->id }}" {{ $saleCar->type_sale == $item->id ? 'selected' : '' }}>
                          {{ @$item->name }}
                        </option>
                      @endforeach
                    </select>
                  </div>

                  <div class="col-md-3">
                    <div class="po-label"><i class="bx bx-transfer-alt"></i> รถเทิร์น</div>
                    <div class="yn-group mt-1">
                      <input type="radio" name="hasTurnCar" id="turnCarYes" value="yes"
                        {{ old('hasTurnCar', $saleCar->TurnCarID ? 'yes' : 'no') === 'yes' ? 'checked' : '' }}
                        {{ $disabled }}>
                      <label for="turnCarYes">มี</label>
                      <input type="radio" name="hasTurnCar" id="turnCarNo" value="no"
                        {{ old('hasTurnCar', $saleCar->TurnCarID ? 'yes' : 'no') === 'no' ? 'checked' : '' }}
                        {{ $disabled }}>
                      <label for="turnCarNo">ไม่มี</label>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {{-- Section 2 : ข้อมูลรถ --}}
            <div class="po-section-edit">
              <div class="po-section-header">
                <div class="po-section-icon emerald"><i class="bx bx-car"></i></div>
                <h6 class="po-section-title">ข้อมูลรถ</h6>
              </div>
              <div class="po-section-body-edit">
                <div class="row g-3">
                  @if (auth()->user()->brand == 2)
                    <div class="col-md-2">
                      <label for="model_id" class="po-label"><i class="bx bx-cube"></i> รุ่นรถหลัก</label>
                      <select id="model_id" name="model_id" class="form-select" required
                        style="{{ $carLockedStyle }}">
                        <option value="">— เลือกรุ่นรถหลัก —</option>
                        @foreach ($model as $m)
                          <option value="{{ $m->id }}" data-overbudget="{{ $m->over_budget }}"
                            data-perbudget="{{ $m->per_budget }}" {{ $saleCar->model_id == $m->id ? 'selected' : '' }}>
                            {{ $m->Name_TH }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label for="subModel_id" class="po-label"><i class="bx bx-list-ul"></i> รุ่นรถย่อย</label>
                      <select id="subModel_id" name="subModel_id" class="form-select" required
                        style="{{ $carLockedStyle }}">
                        <option value="">— เลือกรุ่นรถย่อย —</option>
                        @foreach ($subModels as $s)
                          <option value="{{ $s->id }}" {{ $saleCar->subModel_id == $s->id ? 'selected' : '' }}>
                            {{ $s->name }}
                          </option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label for="gwm_color" class="po-label"><i class="bx bx-palette"></i> สี</label>
                      <select id="gwm_color" name="gwm_color" class="form-select" required
                        style="{{ $carLockedStyle }}">
                        <option value="">— เลือกสี —</option>
                        @foreach ($gwmColor as $t)
                          <option value="{{ $t->id }}" {{ $saleCar->gwm_color == $t->id ? 'selected' : '' }}>
                            {{ $t->name }}
                          </option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-md-2">
                      <label for="interior_color" class="po-label"><i class="bx bx-brush"></i> สีภายใน</label>
                      <select id="interior_color" name="interior_color"
                        class="form-select @error('interior_color') is-invalid @enderror" required
                        style="{{ $carLockedStyle }}">
                        <option value="">— เลือกสี —</option>
                        @foreach ($interiorColor as $t)
                          <option value="{{ $t->id }}" data-name="{{ $t->name }}"
                            {{ $saleCar->interior_color == $t->id ? 'selected' : '' }}>
                            {{ $t->name }}
                          </option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-md-2">
                      <label for="pricelist_year" class="po-label"><i class="bx bx-calendar-alt"></i> ปี</label>
                      <select id="pricelist_year" name="Year"
                        class="form-select @error('Year') is-invalid @enderror" required style="{{ $carLockedStyle }}">
                        <option value="">— ปี —</option>
                        @if ($saleCar->Year)
                          <option value="{{ $saleCar->Year }}" selected>{{ $saleCar->Year }}</option>
                        @endif
                      </select>
                    </div>
                  @elseif (auth()->user()->brand == 3)
                    <div class="col-md-3">
                      <label for="model_id" class="po-label"><i class="bx bx-cube"></i> รุ่นรถหลัก</label>
                      <select id="model_id" name="model_id" class="form-select" required
                        style="{{ $carLockedStyle }}">
                        <option value="">— เลือกรุ่นรถหลัก —</option>
                        @foreach ($model as $m)
                          <option value="{{ $m->id }}" data-overbudget="{{ $m->over_budget }}"
                            data-perbudget="{{ $m->per_budget }}" {{ $saleCar->model_id == $m->id ? 'selected' : '' }}>
                            {{ $m->Name_TH }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-md-4">
                      <label for="subModel_id" class="po-label"><i class="bx bx-list-ul"></i> รุ่นรถย่อย</label>
                      <select id="subModel_id" name="subModel_id" class="form-select" required
                        style="{{ $carLockedStyle }}">
                        <option value="">— เลือกรุ่นรถย่อย —</option>
                        @foreach ($subModels as $s)
                          <option value="{{ $s->id }}" {{ $saleCar->subModel_id == $s->id ? 'selected' : '' }}>
                            {{ $s->name }}
                          </option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-md-3">
                      <label for="gwm_color" class="po-label"><i class="bx bx-palette"></i> สี</label>
                      <select id="gwm_color" name="gwm_color" class="form-select" required
                        style="{{ $carLockedStyle }}">
                        <option value="">— เลือกสี —</option>
                        @foreach ($gwmColor as $t)
                          <option value="{{ $t->id }}" {{ $saleCar->gwm_color == $t->id ? 'selected' : '' }}>
                            {{ $t->name }}
                          </option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-md-2">
                      <label for="pricelist_year" class="po-label"><i class="bx bx-calendar-alt"></i> ปี</label>
                      <select id="pricelist_year" name="Year"
                        class="form-select @error('Year') is-invalid @enderror" required style="{{ $carLockedStyle }}">
                        <option value="">— ปี —</option>
                        @if ($saleCar->Year)
                          <option value="{{ $saleCar->Year }}" selected>{{ $saleCar->Year }}</option>
                        @endif
                      </select>
                    </div>
                  @else
                    <div class="col-md-3">
                      <label for="model_id" class="po-label"><i class="bx bx-cube"></i> รุ่นรถหลัก</label>
                      <select id="model_id" name="model_id" class="form-select" required
                        style="{{ $carLockedStyle }}">
                        <option value="">— เลือกรุ่นรถหลัก —</option>
                        @foreach ($model as $m)
                          <option value="{{ $m->id }}" data-overbudget="{{ $m->over_budget }}"
                            data-perbudget="{{ $m->per_budget }}" {{ $saleCar->model_id == $m->id ? 'selected' : '' }}>
                            {{ $m->Name_TH }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-md-5">
                      <label for="subModel_id" class="po-label"><i class="bx bx-list-ul"></i> รุ่นรถย่อย</label>
                      <select id="subModel_id" name="subModel_id" class="form-select" required
                        style="{{ $carLockedStyle }}">
                        <option value="">— เลือกรุ่นรถย่อย —</option>
                        @foreach ($subModels as $s)
                          <option value="{{ $s->id }}" {{ $saleCar->subModel_id == $s->id ? 'selected' : '' }}>
                            {{ $s->detail }} - {{ $s->name }}
                          </option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-md-2">
                      <label for="pricelist_color" class="po-label"><i class='bx bx-droplet'></i> ประเภทสี</label>
                      <select id="pricelist_color" name="type_color" class="form-select" required {{ $disabled }}
                        data-pricelist-rows="{{ json_encode($pricelistRows) }}">
                        <option value="">— เลือก —</option>
                        @foreach ($pricelistRows->pluck('color')->unique() as $color)
                          <option value="{{ $color }}" {{ $saleCar->type_color == $color ? 'selected' : '' }}>
                            {{ $color }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-md-2">
                      <label for="pricelist_year" class="po-label"><i class="bx bx-calendar-alt"></i> ปี</label>
                      <select id="pricelist_year" name="Year"
                        class="form-select @error('Year') is-invalid @enderror" required {{ $disabled }}>
                        <option value="">— ปี —</option>
                        @foreach ($pricelistRows->where('color', $saleCar->type_color)->pluck('year')->unique()->sort() as $year)
                          <option value="{{ $year }}" {{ $saleCar->Year == $year ? 'selected' : '' }}>
                            {{ $year }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-md-2">
                      <label for="option" class="po-label"><i class="bx bx-code-block"></i> Option</label>
                      <input id="option" type="text" class="form-control" name="option"
                        value="{{ $saleCar->option }}" readonly>
                    </div>
                    <div class="col-md-3">
                      <label for="Color" class="po-label"><i class="bx bx-palette"></i> สี</label>
                      <input class="form-control" type="text" name="Color" id="Color"
                        value="{{ $saleCar->Color }}" required {{ $carLocked ? 'readonly' : '' }}>
                    </div>
                  @endif
                </div>
              </div>
            </div>

            {{-- Section 3 : เงินจอง --}}
            <div class="po-section-edit">
              <div class="po-section-header">
                <div class="po-section-icon amber"><i class="bx bx-credit-card"></i></div>
                <h6 class="po-section-title">ข้อมูลเงินจอง</h6>
              </div>
              <div class="po-section-body-edit">
                @php $reservationType = $reservationPayment->type ?? ''; @endphp
                <div class="row g-3 align-items-end">
                  <div class="col-md-2">
                    <label for="CashDeposit" class="po-label"><i class="bx bx-wallet"></i> เงินจอง</label>
                    <div class="money-wrap">
                      <input type="text" class="form-control text-end money-input" id="CashDeposit"
                        name="CashDeposit" value="{{ $saleCar->CashDeposit }}">
                      <span class="money-suffix">฿</span>
                    </div>
                  </div>

                  <div class="col-md-2">
                    <label for="reservation_date" class="po-label"><i class="bx bx-calendar-check"></i>
                      วันที่จ่ายเงินจอง</label>
                    <input id="reservation_date" type="date" class="form-control" name="reservation_date"
                      value="{{ old('reservation_date', $reservationPayment?->date ?? '') }}">
                  </div>

                  <div class="col-md-8">
                    <div class="po-label"><i class='bx bx-money'></i> ประเภทการจ่ายเงินจอง</div>
                    <div class="pay-type-group mt-1">
                      <input type="radio" name="reservationCondition" id="cashReser" value="cash"
                        {{ $reservationType === 'cash' ? 'checked' : '' }} {{ $disabled }}>
                      <label for="cashReser"><i class="bx bx-money me-1"></i>เงินสด</label>

                      <input type="radio" name="reservationCondition" id="creditReser" value="credit"
                        {{ $reservationType === 'credit' ? 'checked' : '' }} {{ $disabled }}>
                      <label for="creditReser"><i class="bx bx-credit-card me-1"></i>บัตรเครดิต</label>

                      <input type="radio" name="reservationCondition" id="checkReser" value="check"
                        {{ $reservationType === 'check' ? 'checked' : '' }} {{ $disabled }}>
                      <label for="checkReser"><i class="bx bx-building-house me-1"></i>เช็คธนาคาร</label>

                      <input type="radio" name="reservationCondition" id="transReser" value="transfer"
                        {{ $reservationType === 'transfer' ? 'checked' : '' }} {{ $disabled }}>
                      <label for="transReser"><i class="bx bx-transfer-alt me-1"></i>เงินโอน</label>
                    </div>
                  </div>
                </div>

                @if (!$isHistory || $reservationType === 'credit')
                  <div id="creditReservation" class="sub-section-edit">
                    <div class="row g-3">
                      <div class="col-md-4">
                        <label for="reservation_credit" class="po-label">บัตรเครดิต</label>
                        <input id="reservation_credit" type="text" class="form-control" name="reservation_credit"
                          value="{{ old('reservation_credit', $reservationPayment->credit ?? '') }}">
                      </div>
                      <div class="col-md-2">
                        <label for="reservation_tax_credit" class="po-label">ค่าธรรมเนียม</label>
                        <div class="money-wrap">
                          <input id="reservation_tax_credit" type="text" class="form-control text-end money-input"
                            name="reservation_tax_credit"
                            value="{{ old('reservation_tax_credit', $reservationPayment->tax_credit ?? '') }}">
                          <span class="money-suffix">฿</span>
                        </div>
                      </div>
                    </div>
                  </div>
                @endif

                @if (!$isHistory || $reservationType === 'check')
                  <div id="checkReservation" class="sub-section-edit">
                    <div class="row g-3">
                      <div class="col-md-3">
                        <label for="reservation_check_bank" class="po-label">ธนาคาร</label>
                        <input id="reservation_check_bank" type="text" class="form-control"
                          name="reservation_check_bank"
                          value="{{ old('reservation_check_bank', $reservationPayment->check_bank ?? '') }}">
                      </div>
                      <div class="col-md-4">
                        <label for="reservation_check_branch" class="po-label">สาขา</label>
                        <input id="reservation_check_branch" type="text" class="form-control"
                          name="reservation_check_branch"
                          value="{{ old('reservation_check_branch', $reservationPayment->check_branch ?? '') }}">
                      </div>
                      <div class="col-md-3">
                        <label for="reservation_check_no" class="po-label">เลขที่</label>
                        <input id="reservation_check_no" type="text" class="form-control"
                          name="reservation_check_no"
                          value="{{ old('reservation_check_no', $reservationPayment->check_no ?? '') }}">
                      </div>
                      @if (auth()->user()->brand == 2)
                        <div class="col-md-2">
                          <label for="danu_date" class="po-label">วันที่ใช้บัตรคุณดนู</label>
                          <input type="date" class="form-control" name="danu_date"
                            value="{{ old('danu_date', $reservationPayment->danu_date ?? '') }}" {{ $disabled }}>
                        </div>
                      @endif
                    </div>
                  </div>
                @endif

                @if (!$isHistory || $reservationType === 'transfer')
                  <div id="bankReservation" class="sub-section-edit">
                    <div class="row g-3">
                      <div class="col-md-3">
                        <label for="reservation_transfer_bank" class="po-label">ธนาคาร</label>
                        <input id="reservation_transfer_bank" type="text" class="form-control"
                          name="reservation_transfer_bank"
                          value="{{ old('reservation_transfer_bank', $reservationPayment->transfer_bank ?? '') }}">
                      </div>
                      <div class="col-md-4">
                        <label for="reservation_transfer_branch" class="po-label">สาขา</label>
                        <input id="reservation_transfer_branch" type="text" class="form-control"
                          name="reservation_transfer_branch"
                          value="{{ old('reservation_transfer_branch', $reservationPayment->transfer_branch ?? '') }}">
                      </div>
                      <div class="col-md-3">
                        <label for="reservation_transfer_no" class="po-label">เลขที่</label>
                        <input id="reservation_transfer_no" type="text" class="form-control"
                          name="reservation_transfer_no"
                          value="{{ old('reservation_transfer_no', $reservationPayment->transfer_no ?? '') }}">
                      </div>
                      @if (auth()->user()->brand == 2)
                        <div class="col-md-2">
                          <label for="danu_date" class="po-label">วันที่ใช้บัตรคุณดนู</label>
                          <input type="date" class="form-control" name="danu_date"
                            value="{{ old('danu_date', $reservationPayment->danu_date ?? '') }}" {{ $disabled }}>
                        </div>
                      @endif
                    </div>
                  </div>
                @endif

                @if (auth()->user()->brand == 2 && (!$isHistory || $reservationType === 'cash'))
                  <div id="danuReservation" class="sub-section-edit"
                    {{ $reservationType !== 'cash' ? 'style=display:none;' : '' }}>
                    <div class="row g-3">
                      <div class="col-md-2">
                        <label for="danu_date" class="po-label">วันที่ใช้บัตรคุณดนู</label>
                        <input type="date" class="form-control" name="danu_date"
                          value="{{ old('danu_date', $reservationPayment->danu_date ?? '') }}" {{ $disabled }}>
                      </div>
                    </div>
                  </div>
                @endif
              </div>
            </div>

            {{-- หลักฐานการจอง --}}
            @if (!empty($saleCar->attachment_url))
              <div class="po-section-edit">
                <div class="po-section-header">
                  <div class="po-section-icon pink"><i class="bx bx-paperclip"></i></div>
                  <h6 class="po-section-title">หลักฐานการจอง</h6>
                </div>
                <div class="po-section-body-edit">
                  <div class="row g-3">
                    @foreach ($saleCar->attachment_url as $url)
                      @php
                        $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
                        $filename = basename(parse_url($url, PHP_URL_PATH));
                      @endphp
                      <div class="col-md-3 col-sm-6">
                        @if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                          <a href="{{ $url }}" target="_blank" class="attach-img-wrap d-block">
                            <img src="{{ $url }}" alt="{{ $filename }}">
                            <div class="attach-img-overlay">
                              <i class="bx bx-zoom-in"></i>
                            </div>
                          </a>
                        @else
                          <a href="{{ $url }}" target="_blank" class="attach-file-card">
                            <div class="attach-file-icon">
                              <i class="bx bx-file"></i>
                            </div>
                            <div class="overflow-hidden">
                              {{-- <div class="fw-semibold" style="font-size:.85rem;">เอกสาร {{ $loop->iteration }}</div> --}}
                              <div class="fw-semibold" style="font-size:.85rem;">หลักฐานการโอน {{ $loop->iteration }}
                              </div>
                              <div style="font-size:.75rem;color:#94a3b8;">{{ strtoupper($ext) }} file</div>
                            </div>
                          </a>
                        @endif
                      </div>
                    @endforeach
                  </div>
                </div>
              </div>
            @endif

            {{-- Section 4 : ข้อมูล Car Order --}}
            @if ($userRole !== 'sale')
              <div class="po-section-edit">
                <div class="po-section-header">
                  <div class="po-section-icon indigo"><i class="bx bx-receipt"></i></div>
                  <h6 class="po-section-title">ข้อมูล Car Order</h6>
                  @if (!empty($saleCar->CarOrderID))
                    <div class="po-section-header-end d-flex align-items-center gap-2">
                      <span class="badge bg-label-indigo" style="font-size:.78rem;">
                        <i class="bx bx-link-alt me-1"></i>ผูกรถแล้ว
                      </span>
                      <button type="button" class="btn btn-outline-danger btn-sm" id="btnCancelCarOrder"
                        data-sale-id="{{ $saleCar->id }}" data-carorder-id="{{ $saleCar->CarOrderID }}"
                        @disabled($isHistory)>
                        <i class="bx bx-unlink me-1"></i>ยกเลิกการผูกรถ
                      </button>
                    </div>
                  @endif
                </div>
                <div class="po-section-body-edit">
                  <input type="hidden" id="CarOrderID" name="CarOrderID" value="{{ $saleCar->CarOrderID ?? '' }}">

                  {{-- แถวค้นหา --}}
                  <div class="row g-3 mb-3">
                    <div class="col-md-4">
                      <label for="carOrderSearch" class="po-label"><i class="bx bx-search-alt"></i> ค้นหา Car Order
                        ID</label>
                      <div class="input-group">
                        <input id="carOrderSearch" type="text" class="form-control" name="carOrderSearch"
                          placeholder="พิมพ์ Vin-Number / รุ่นรถ" @disabled($isHistory)>
                        <button type="button" class="btn btnSearchCarOrder border px-3"
                          style="background:#ede9fe; color:#6366f1; border-color:#c4b5fd !important; cursor:pointer;"
                          @disabled($isHistory)>
                          <i class="bx bx-search me-1"></i> ค้นหา
                        </button>
                      </div>
                    </div>
                  </div>

                  {{-- รายละเอียดรถ --}}
                  <div class="car-order-detail-card">
                    <div class="car-order-detail-header">
                      <i class="bx bx-car me-1"></i> รายละเอียด Car Order
                    </div>
                    <div class="car-order-detail-body">
                      <div class="row g-3">
                        <div class="col-md-2">
                          <label for="carOrderCode" class="po-label"><i class="bx bx-barcode"></i> Car Order ID</label>
                          <input id="carOrderCode" type="text" class="form-control"
                            value="{{ $saleCar->carOrder->order_code ?? '' }}" readonly>
                        </div>

                        <div class="col-md-3">
                          <label for="carOrderModel" class="po-label"><i class="bx bx-cube"></i> รุ่นรถหลัก</label>
                          <input id="carOrderModel" type="text" class="form-control"
                            value="{{ $saleCar->carOrder->model->Name_TH ?? '' }}" readonly>
                        </div>

                        <div class="col-md-4">
                          <label for="carOrderSubModel" class="po-label"><i class="bx bx-list-ul"></i>
                            รุ่นรถย่อย</label>
                          <input id="carOrderSubModel" type="text" class="form-control"
                            value="{{ !empty($saleCar->carOrder->subModel)
                                ? ($saleCar->carOrder->subModel->detail
                                    ? $saleCar->carOrder->subModel->detail . ' - ' . $saleCar->carOrder->subModel->name
                                    : $saleCar->carOrder->subModel->name)
                                : '' }}"
                            readonly>
                        </div>

                        @if (auth()->user()->brand == 2)
                          <div class="col-md-3">
                            <label for="carOrderVin" class="po-label"><i class="bx bx-key"></i> Vin-Number</label>
                            <input id="carOrderVin" type="text" class="form-control"
                              value="{{ $saleCar->carOrder->vin_number ?? '' }}" readonly>
                          </div>
                          <div class="col-md-3">
                            <label for="carOrderColor" class="po-label"><i class="bx bx-palette"></i> สี</label>
                            <input id="carOrderColor" type="text" class="form-control"
                              value="{{ $saleCar->carOrder->gwmColor->name ?? '' }}" readonly>
                          </div>
                          <div class="col-md-3">
                            <label for="carOrderInterior" class="po-label"><i class="bx bx-brush"></i> สีภายใน</label>
                            <input id="carOrderInterior" type="text" class="form-control"
                              value="{{ $saleCar->carOrder->interiorColor->name ?? '' }}" readonly>
                          </div>
                        @elseif (auth()->user()->brand == 3)
                          <div class="col-md-3">
                            <label for="carOrderVin" class="po-label"><i class="bx bx-key"></i> Vin-Number</label>
                            <input id="carOrderVin" type="text" class="form-control"
                              value="{{ $saleCar->carOrder->vin_number ?? '' }}" readonly>
                          </div>
                          <div class="col-md-3">
                            <label for="carOrderColor" class="po-label"><i class="bx bx-palette"></i> สี</label>
                            <input id="carOrderColor" type="text" class="form-control"
                              value="{{ $saleCar->carOrder->gwmColor->name ?? '' }}" readonly>
                          </div>
                        @else
                          <div class="col-md-1">
                            <label for="carOrderOption" class="po-label"><i class="bx bx-code-block"></i>
                              Option</label>
                            <input id="carOrderOption" type="text" class="form-control"
                              value="{{ $saleCar->carOrder->option ?? '' }}" readonly>
                          </div>
                          <div class="col-md-2">
                            <label for="carOrderVin" class="po-label"><i class="bx bx-key"></i> Vin-Number</label>
                            <input id="carOrderVin" type="text" class="form-control"
                              value="{{ $saleCar->carOrder->vin_number ?? '' }}" readonly>
                          </div>
                          <div class="col-md-2">
                            <label for="carOrderColor" class="po-label"><i class="bx bx-palette"></i> สี</label>
                            <input id="carOrderColor" type="text" class="form-control"
                              value="{{ $saleCar->carOrder->color ?? '' }}" readonly>
                          </div>
                        @endif

                        <div class="col-md-2">
                          <label for="carOrderYear" class="po-label"><i class="bx bx-calendar-alt"></i> ปี</label>
                          <input id="carOrderYear" type="text" class="form-control"
                            value="{{ $saleCar->carOrder->year ?? '' }}" readonly>
                        </div>
                        <div class="col-md-2">
                          <label for="carOrderCost" class="po-label"><i class="bx bx-dollar-circle"></i>
                            ราคาทุน</label>
                          <div class="money-wrap">
                            <input id="carOrderCost" type="text" class="form-control text-end money-input"
                              value="{{ $saleCar->carOrder?->car_DNP !== null ? number_format($saleCar->carOrder->car_DNP, 2) : '' }}"
                              readonly>
                            <span class="money-suffix">฿</span>
                          </div>
                        </div>
                        <div class="col-md-2">
                          <label for="carOrderSale" class="po-label"><i class="bx bx-money"></i> ราคาขาย</label>
                          <div class="money-wrap">
                            <input id="carOrderSale" type="text" class="form-control text-end money-input"
                              value="{{ $saleCar->carOrder?->car_MSRP !== null ? number_format($saleCar->carOrder->car_MSRP, 2) : '' }}"
                              readonly>
                            <span class="money-suffix">฿</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
            @endif

            {{-- Section 5 : ข้อมูลรถเทิร์น --}}
            <div id="turnCarFields" class="po-section-edit" @style(['display:block' => $saleCar->TurnCarID, 'display:none' => !$saleCar->TurnCarID])>
              <div class="po-section-header">
                <div class="po-section-icon rose"><i class="bx bxs-car"></i></div>
                <h6 class="po-section-title">ข้อมูลรถเทิร์น</h6>
              </div>
              <div class="po-section-body-edit">
                <div class="row g-3">
                  <div class="col-md-2">
                    <label for="brand_car" class="po-label"><i class="bx bx-car"></i> ยี่ห้อ</label>
                    <input id="brand_car" class="form-control" name="brand_car"
                      value="{{ old('brand_car', $saleCar->turnCar->brand_car ?? '') }}">
                  </div>
                  <div class="col-md-3">
                    <label for="model" class="po-label"><i class="bx bx-list-ul"></i> รุ่น</label>
                    <input id="model" class="form-control" name="model"
                      value="{{ old('model', $saleCar->turnCar->model ?? '') }}">
                  </div>
                  <div class="col-md-3">
                    <label for="machine" class="po-label"><i class="bx bx-cog"></i> เครื่องยนต์</label>
                    <input id="machine" class="form-control" name="machine"
                      value="{{ old('machine', $saleCar->turnCar->machine ?? '') }}">
                  </div>
                  <div class="col-md-2">
                    <label for="license_plate" class="po-label"><i class="bx bx-credit-card-front"></i> ทะเบียน</label>
                    <input id="license_plate" class="form-control" name="license_plate"
                      value="{{ old('license_plate', $saleCar->turnCar->license_plate ?? '') }}">
                  </div>
                  <div class="col-md-2">
                    <label for="year_turn" class="po-label"><i class="bx bx-calendar-alt"></i> ปี</label>
                    <input id="year_turn" class="form-control" name="year_turn"
                      value="{{ old('year_turn', $saleCar->turnCar->year_turn ?? '') }}">
                  </div>
                  <div class="col-md-3">
                    <label for="color_turn" class="po-label"><i class="bx bx-palette"></i> สี</label>
                    <input id="color_turn" class="form-control" name="color_turn"
                      value="{{ old('color_turn', $saleCar->turnCar->color_turn ?? '') }}">
                  </div>
                  <div class="col-md-2">
                    <label for="cost_turn" class="po-label"><i class="bx bx-refresh"></i> ยอดเทิร์น</label>
                    <div class="money-wrap">
                      <input id="cost_turn" class="form-control text-end money-input" name="cost_turn"
                        value="{{ old('cost_turn', isset($saleCar->turnCar) ? number_format($saleCar->turnCar->cost_turn, 2) : '') }}">
                      <span class="money-suffix">฿</span>
                    </div>
                  </div>
                  <div class="col-md-2">
                    <label for="com_turn" class="po-label"><i class="bx bx-wallet"></i> ค่าคอมยอดเทิร์น</label>
                    <div class="money-wrap">
                      <input id="com_turn" class="form-control text-end money-input" name="com_turn"
                        value="{{ old('com_turn', isset($saleCar->turnCar) ? number_format($saleCar->turnCar->com_turn, 2) : '') }}">
                      <span class="money-suffix">฿</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-4 d-flex justify-content-end gap-2">
              <button id="nextCampaign" class="btn btn-primary">ถัดไป</button>
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
                      data-bs-target="#tab-accessory" aria-controls="tab-accessory"
                      aria-selected="false">รายละเอียดอุปกรณ์ตกแต่ง (แถม)</button>
                  </li>
                  <li class="nav-item">
                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                      data-bs-target="#tab-extra" aria-controls="tab-extra"
                      aria-selected="false">รายการซื้อเพิ่ม</button>
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

                  {{-- Header --}}
                  <div class="campaign-header mb-4">
                    <div class="campaign-header-icon">
                      <i class="bx bx-gift"></i>
                    </div>
                    <div>
                      <div class="campaign-header-title">แคมเปญ</div>
                      <div class="campaign-header-sub">เลือกแคมเปญที่ต้องการ (เลือกได้หลายรายการ)</div>
                    </div>
                  </div>

                  {{-- Content --}}
                  <div class="row g-3 align-items-start justify-content-center">
                    <div class="col-md-7">
                      <div class="campaign-select-wrap">
                        <label for="CampaignID" class="po-label">
                          <i class="bx bx-tag-alt"></i> รายการแคมเปญ
                        </label>
                        <select name="CampaignID[]" id="CampaignID" multiple class="form-select campaign-select"
                          {{ $disabled }}>
                          @foreach ($campaigns as $camp)
                            <option value="{{ $camp->id }}"
                              data-cash-support-final="{{ $camp->cashSupport_final }}"
                              {{ in_array($camp->id, $selected_campaigns ?? []) ? 'selected' : '' }}>
                              ({{ $camp->type->name ?? '-' }})
                              {{ $camp->appellation->name ?? '-' }} —
                              {{ number_format((float) $camp->cashSupport_final, 2, '.', ',') }} ฿
                            </option>
                          @endforeach
                        </select>
                        <div id="campaignWarning" class="mt-2 text-danger small"></div>
                        <div class="mt-1 text-muted" style="font-size:.78rem;">
                          <i class="bx bx-info-circle me-1"></i>สามารถเลือกได้หลายแคมเปญ
                        </div>
                      </div>
                    </div>

                    <div class="col-md-3">
                      <div class="campaign-total-card">
                        <div class="campaign-total-label">
                          <i class="bx bx-money-withdraw me-1"></i> ยอดรวมค่าแคมเปญ
                        </div>
                        <div class="campaign-total-body">
                          <div class="money-wrap">
                            <input class="form-control text-end money-input campaign-total-input" type="text"
                              id="TotalSaleCampaign" name="TotalSaleCampaign" placeholder="0.00" readonly />
                            <span class="money-suffix">฿</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <input type="hidden" id="balanceCampaign" name="balanceCampaign">

                  <div class="mt-4 d-flex justify-content-end gap-2">
                    <button id="prevDetail" class="btn btn-outline-secondary">
                      <i class="bx bx-chevron-left me-1"></i> ย้อนกลับ
                    </button>
                    <button id="nextAccessory" class="btn btn-primary">
                      ถัดไป <i class="bx bx-chevron-right ms-1"></i>
                    </button>
                  </div>

                </div>

                <!-- gift -->
                <div class="tab-pane fade" id="tab-accessory" role="tabpanel">

                  {{-- Header --}}
                  <div class="accessory-header mb-4">
                    <div class="accessory-header-icon">
                      <i class="bx bx-wrench"></i>
                    </div>
                    <div class="flex-grow-1">
                      <div class="accessory-header-title">รายละเอียดอุปกรณ์ตกแต่ง (แถม)</div>
                      <div class="accessory-header-sub">อุปกรณ์ตกแต่งที่แถมให้ลูกค้า</div>
                    </div>
                    <button type="button" class="btn btn-amber btnGift" @disabled($isHistory)>
                      <i class="bx bx-plus me-1"></i> เพิ่มรายการ
                    </button>
                  </div>

                  <input type="hidden" id="TotalAccessoryGift" name="TotalAccessoryGift">
                  <input type="hidden" id="gift_ids" name="gift_ids">
                  <input type="hidden" id="total_gift_used" name="total_gift_used">
                  <input type="hidden" id="total_gift_com" name="AccessoryGiftCom">

                  <div class="table-responsive accessory-table-wrap">
                    <table class="accessory-table" id="giftTablePrice">
                      <thead>
                        <tr>
                          <th style="width:54px;" class="text-center">No.</th>
                          <th style="width:150px;">รหัส</th>
                          <th>รายละเอียด</th>
                          <th style="width:140px;">ประเภทราคา</th>
                          <th style="width:170px;" class="text-end">ราคา (ค่าคอม)</th>
                          <th style="width:64px;" class="text-center">ลบ</th>
                        </tr>
                      </thead>
                      <tbody>
                        @if ($saleCar->accessories->count() > 0)
                          @foreach ($saleCar->accessories->where('pivot.type', 'gift') as $a)
                            <tr data-id="{{ $a->id }}" data-price="{{ $a->pivot->price }}"
                              data-com="{{ $a->pivot->commission }}">
                              <td class="text-center text-muted" style="font-size:.85rem;">{{ $loop->index + 1 }}</td>
                              <td style="font-size:.85rem; color:#374151;">{{ $a->accessory_id }}</td>
                              <td class="acc-detail">{{ $a->detail }}</td>
                              <td style="font-size:.85rem; color:#374151;">{{ ucfirst($a->pivot->price_type) }}</td>
                              <td class="text-end">
                                <span class="acc-price">{{ number_format($a->pivot->price, 2) }} ฿</span>
                                @if ($a->pivot->commission && $a->pivot->commission > 0)
                                  <div class="acc-com">คอม {{ number_format($a->pivot->commission, 2) }}</div>
                                @endif
                              </td>
                              <td class="text-center">
                                <button type="button" class="acc-delete-btn btn-delete-gift"
                                  @disabled($isHistory) title="ลบรายการ">
                                  <i class="bx bx-trash-alt"></i>
                                </button>
                              </td>
                            </tr>
                          @endforeach
                        @else
                          <tr id="no-data-row">
                            <td colspan="6" class="text-center py-5 text-muted">
                              <i class="bx bx-package d-block mb-2" style="font-size:2.2rem; opacity:.4;"></i>
                              <div style="font-size:.85rem;">ยังไม่มีรายการอุปกรณ์</div>
                            </td>
                          </tr>
                        @endif
                      </tbody>
                      <tfoot>
                        <tr id="total-row" class="accessory-total-row">
                          <td colspan="4" class="text-end">ยอดรวมทั้งหมด</td>
                          <td id="total-price-gift" class="text-end money-input"></td>
                          <td></td>
                        </tr>
                      </tfoot>
                    </table>
                  </div>

                  <div class="mt-4 d-flex justify-content-end gap-2">
                    <button id="prevCampaign" class="btn btn-outline-secondary">
                      <i class="bx bx-chevron-left me-1"></i> ย้อนกลับ
                    </button>
                    <button id="nextExtra" class="btn btn-primary">
                      ถัดไป <i class="bx bx-chevron-right ms-1"></i>
                    </button>
                  </div>

                </div>

                <!-- extra -->
                <div class="tab-pane fade" id="tab-extra" role="tabpanel">

                  {{-- Header --}}
                  <div class="extra-header mb-4">
                    <div class="extra-header-icon">
                      <i class="bx bx-cart-add"></i>
                    </div>
                    <div class="flex-grow-1">
                      <div class="extra-header-title">รายการซื้อเพิ่ม</div>
                      <div class="extra-header-sub">อุปกรณ์ตกแต่งที่ลูกค้าซื้อเพิ่ม</div>
                    </div>
                    <button type="button" class="btn btn-extra btnExtra" @disabled($isHistory)>
                      <i class="bx bx-plus me-1"></i> เพิ่มรายการ
                    </button>
                  </div>

                  <input type="hidden" id="TotalAccessoryExtra" name="TotalAccessoryExtra">
                  <input type="hidden" id="extra_ids" name="extra_ids">
                  <input type="hidden" id="total_extra_used" name="total_extra_used">
                  <input type="hidden" id="total_extra_com" name="AccessoryExtraCom">

                  <div class="table-responsive extra-table-wrap">
                    <table class="extra-table" id="extraTable">
                      <thead>
                        <tr>
                          <th style="width:54px;" class="text-center">No.</th>
                          <th style="width:150px;">รหัส</th>
                          <th>รายละเอียด</th>
                          <th style="width:140px;">ประเภทราคา</th>
                          <th style="width:170px;" class="text-end">ราคา (ค่าคอม)</th>
                          <th style="width:64px;" class="text-center">ลบ</th>
                        </tr>
                      </thead>
                      <tbody>
                        @if ($saleCar->accessories->count() > 0)
                          @foreach ($saleCar->accessories->where('pivot.type', 'extra') as $a)
                            <tr data-id="{{ $a->id }}" data-price="{{ $a->pivot->price }}"
                              data-com="{{ $a->pivot->commission }}">
                              <td class="text-center text-muted">{{ $loop->index + 1 }}</td>
                              <td>{{ $a->accessory_id }}</td>
                              <td>{{ $a->detail }}</td>
                              <td>{{ ucfirst($a->pivot->price_type) }}</td>
                              <td class="text-end">
                                <span class="acc-price">{{ number_format($a->pivot->price, 2) }} ฿</span>
                                @if ($a->pivot->commission && $a->pivot->commission > 0)
                                  <div class="acc-com">คอม {{ number_format($a->pivot->commission, 2) }}</div>
                                @endif
                              </td>
                              <td class="text-center">
                                <button type="button" class="acc-delete-btn btn-delete-extra"
                                  @disabled($isHistory) title="ลบรายการ">
                                  <i class="bx bx-trash-alt"></i>
                                </button>
                              </td>
                            </tr>
                          @endforeach
                        @else
                          <tr id="no-data-extra">
                            <td colspan="6" class="text-center py-5 text-muted">
                              <i class="bx bx-cart d-block mb-2" style="font-size:2.2rem; opacity:.4;"></i>
                              <div style="font-size:.85rem;">ยังไม่มีรายการซื้อเพิ่ม</div>
                            </td>
                          </tr>
                        @endif
                      </tbody>
                      <tfoot>
                        <tr id="total-row-extra" class="extra-total-row">
                          <td colspan="4" class="text-end">ยอดรวมทั้งหมด</td>
                          <td id="total-price-extra" class="text-end money-input"></td>
                          <td></td>
                        </tr>
                      </tfoot>
                    </table>
                  </div>

                  <div class="mt-4 d-flex justify-content-end gap-2">
                    <button id="prevAccessory" class="btn btn-outline-secondary">
                      <i class="bx bx-chevron-left me-1"></i> ย้อนกลับ
                    </button>
                    <button id="nextCar" class="btn btn-primary">
                      ถัดไป <i class="bx bx-chevron-right ms-1"></i>
                    </button>
                  </div>

                </div>

                <!-- total price -->
                <div class="tab-pane fade" id="tab-car" role="tabpanel">

                  {{-- Header --}}
                  <div class="summary-header mb-4">
                    <div class="summary-header-icon">
                      <i class="bx bx-calculator"></i>
                    </div>
                    <div class="flex-grow-1">
                      <div class="summary-header-title">สรุปยอด</div>
                      <div class="summary-header-sub">ข้อมูลราคาและการชำระเงินทั้งหมด</div>
                    </div>
                  </div>

                  {{-- Stat cards row --}}
                  <div class="row g-3 mb-4">
                    <div class="col-md-3">
                      <div class="summary-stat-card">
                        <div class="summary-stat-label"><i class="bx bx-credit-card me-1"></i> ประเภทการซื้อ</div>
                        <select id="payment_mode" name="payment_mode" class="form-select mt-1" required>
                          <option value="">-- เลือกประเภท --</option>
                          <option value="finance" {{ $saleCar->payment_mode == 'finance' ? 'selected' : '' }}>ผ่อน
                          </option>
                          <option value="non-finance" {{ $saleCar->payment_mode == 'non-finance' ? 'selected' : '' }}>
                            เงินสด</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-2">
                      <div class="summary-stat-card">
                        <div class="summary-stat-label"><i class="bx bx-transfer me-1"></i> ยอดเทิร์น</div>
                        <div class="money-wrap">
                          <input id="summaryTurn" class="form-control text-end money-input mt-1" disabled />
                          <span class="money-suffix">฿</span>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-2">
                      <div class="summary-stat-card">
                        <div class="summary-stat-label"><i class="bx bx-bookmark me-1"></i> เงินจอง</div>
                        <div class="money-wrap">
                          <input id="summaryCashDeposit" class="form-control text-end money-input mt-1" disabled />
                          <span class="money-suffix">฿</span>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-2">
                      <div class="summary-stat-card">
                        <div class="summary-stat-label"><i class="bx bx-plus-circle me-1"></i> ลูกค้าจ่ายเพิ่ม</div>
                        <div class="money-wrap">
                          <input class="form-control text-end money-input mt-1" type="text" id="summaryExtraTotal"
                            disabled />
                          <span class="money-suffix">฿</span>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="summary-stat-card highlight">
                        <div class="summary-stat-label"><i class="bx bx-car me-1"></i> ราคารถ</div>
                        <input id="price_sub" name="price_sub" class="form-control text-end money-input mt-1"
                          value="{{ $saleCar->price_sub ?? '' }}" required readonly
                          {{ $carLocked ? 'readonly' : '' }}>
                      </div>
                    </div>
                  </div>

                  <div>

                    <input type="hidden" id="remaining_date" name="remaining_date"
                      value="{{ old('remaining_date', $remainingPayment->date ?? '') }}">

                    <input type="hidden" id="RegistrationProvince" name="RegistrationProvince"
                      value="{{ old('RegistrationProvince', $saleCar->RegistrationProvince ?? '') }}">

                    <input type="hidden" id="balance" name="balance"
                      value="{{ old('balance', $saleCar->balance ?? '') }}">

                    <input type="hidden" id="remainingCondition" name="remainingCondition"
                      value="{{ old('remainingCondition', $remainingPayment->type ?? '') }}">

                    {{-- ── Inner Summary Tabs ── --}}
                    <ul class="nav nav-tabs mb-3" id="summaryTabs" role="tablist">
                      <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="sum-tab1-tab" data-bs-toggle="tab"
                          data-bs-target="#sum-tab1" type="button" role="tab">
                          <i class="bx bx-credit-card me-1"></i> ประเภทการซื้อ
                        </button>
                      </li>
                      <li class="nav-item" role="presentation">
                        <button class="nav-link" id="sum-tab2-tab" data-bs-toggle="tab" data-bs-target="#sum-tab2"
                          type="button" role="tab">
                          <i class="bx bx-wallet me-1"></i> <span id="sumTab2Label">ข้อมูลการจ่ายเงิน</span>
                        </button>
                      </li>
                      <li class="nav-item" role="presentation">
                        <button class="nav-link" id="sum-tab3-tab" data-bs-toggle="tab" data-bs-target="#sum-tab3"
                          type="button" role="tab">
                          <i class="bx bx-user-voice me-1"></i> ข้อมูลคนแนะนำ
                        </button>
                      </li>
                      <li class="nav-item" role="presentation">
                        <button class="nav-link" id="sum-tab4-tab" data-bs-toggle="tab" data-bs-target="#sum-tab4"
                          type="button" role="tab">
                          <i class="bx bx-calendar-event me-1"></i> ข้อมูลวันส่งมอบ
                        </button>
                      </li>
                      @if (auth()->user()->brand != 2)
                      <li class="nav-item" role="presentation">
                        <button class="nav-link" id="sum-tab5-tab" data-bs-toggle="tab" data-bs-target="#sum-tab5"
                          type="button" role="tab">
                          <i class="bx bx-dollar-circle me-1"></i> ยอดค่าคอม Sale
                        </button>
                      </li>
                      @endif
                    </ul>

                    <div class="tab-content" id="summaryTabsContent">

                      {{-- ══ Tab 1: ประเภทการซื้อ ══ --}}
                      <div class="tab-pane fade show active" id="sum-tab1" role="tabpanel">

                        {{-- ── เงินสด ── --}}
                        <div id="nonFinanceSelect" style="display:none">
                          <div class="po-section-edit">
                            <div class="po-section-header">
                              <div class="po-section-icon sky"><i class="bx bx-money"></i></div>
                              <h6 class="po-section-title">ข้อมูลการซื้อเงินสด</h6>
                            </div>
                            <div class="po-section-body-edit">

                              {{-- กลุ่ม 1: ยอดและการชำระ --}}
                              <div class="fi-group-label"><i class="bx bx-wallet"></i> ยอดและการชำระเงิน</div>
                              <div class="row g-3 mb-4">
                                <div class="col-md-3">
                                  <label for="balance_display" class="po-label">ยอดคงเหลือ</label>
                                  <div class="money-wrap">
                                    <input id="balance_display"
                                      class="form-control text-end money-input balance-display" type="text"
                                      value="{{ $saleCar->balance }}" readonly />
                                    <span class="money-suffix">฿</span>
                                  </div>
                                </div>
                                <div class="col-md-3">
                                  <label for="PaymentDiscount" class="po-label">ส่วนลด</label>
                                  <div class="money-wrap">
                                    <input class="form-control text-end money-input" type="text"
                                      id="PaymentDiscount" name="PaymentDiscount"
                                      value="{{ $saleCar->PaymentDiscount }}" />
                                    <span class="money-suffix">฿</span>
                                  </div>
                                </div>
                                <div class="col-md-3">
                                  <label for="remaining_date_cash" class="po-label">วันที่จ่ายเงิน</label>
                                  <input id="remaining_date_cash" type="date" class="form-control"
                                    name="remaining_date_cash"
                                    value="{{ old('remaining_date', $remainingPayment?->date ?? '') }}">
                                </div>
                                <div class="col-md-3">
                                  <label for="RegistrationProvince_cash"
                                    class="po-label">จังหวัดที่ขึ้นทะเบียน</label>
                                  <select id="RegistrationProvince_cash" name="RegistrationProvince_cash"
                                    class="registration-province form-select" required>
                                    <option value="">-- เลือกจังหวัด --</option>
                                    @foreach ($provinces as $p)
                                      <option value="{{ @$p->id }}"
                                        {{ $saleCar->RegistrationProvince == $p->id ? 'selected' : '' }}>
                                        {{ @$p->name }}</option>
                                    @endforeach
                                  </select>
                                </div>
                                <div class="col-md-3">
                                  <label for="other_cost" class="po-label">ค่าใช้จ่ายอื่นๆ</label>
                                  <div class="money-wrap">
                                    <input class="form-control text-end money-input" type="text" id="other_cost"
                                      name="other_cost" value="{{ $saleCar->other_cost }}" />
                                    <span class="money-suffix">฿</span>
                                  </div>
                                </div>
                                <div class="col-md-9">
                                  <label for="reason_other_cost" class="po-label">หมายเหตุ ค่าใช้จ่ายอื่นๆ</label>
                                  <textarea id="reason_other_cost" class="form-control" name="reason_other_cost" rows="1">{{ $saleCar->reason_other_cost }}</textarea>
                                </div>
                              </div>

                              {{-- กลุ่ม 2: ประเภทการชำระ --}}
                              <div class="fi-group-label"><i class="bx bx-credit-card"></i> ประเภทการชำระเงิน</div>
                              <div class="row g-3">
                                <div class="col-md-3">
                                  <label for="remainingConditionSelect" class="po-label">ประเภท</label>
                                  <select id="remainingConditionSelect" class="form-select">
                                    <option value="">-- เลือกประเภท --</option>
                                    <option value="cash" {{ $remainingPayment?->type == 'cash' ? 'selected' : '' }}>
                                      เงินสด</option>
                                    <option value="credit"
                                      {{ $remainingPayment?->type == 'credit' ? 'selected' : '' }}>
                                      บัตรเครดิต</option>
                                    <option value="check"
                                      {{ $remainingPayment?->type == 'check' ? 'selected' : '' }}>
                                      เช็คธนาคาร</option>
                                    <option value="transfer"
                                      {{ $remainingPayment?->type == 'transfer' ? 'selected' : '' }}>เงินโอน</option>
                                  </select>
                                </div>
                                @if (!$isHistory || $remainingPayment === 'credit')
                                  <div id="creditRemain" class="col-md-9">
                                    <div class="row g-3">
                                      <div class="col-md-5">
                                        <label for="remaining_credit" class="po-label">บัตรเครดิต</label>
                                        <input id="remaining_credit" type="text" class="form-control"
                                          name="remaining_credit"
                                          value="{{ old('remaining_credit', $remainingPayment->credit ?? '') }}">
                                      </div>
                                      <div class="col-md-4">
                                        <label for="remaining_tax_credit" class="po-label">ค่าธรรมเนียม</label>
                                        <input id="remaining_tax_credit" type="text"
                                          class="form-control text-end money-input" name="remaining_tax_credit"
                                          value="{{ old('remaining_tax_credit', $remainingPayment->tax_credit ?? '') }}">
                                      </div>
                                    </div>
                                  </div>
                                @endif
                                @if (!$isHistory || $remainingPayment === 'check')
                                  <div id="checkRemain" class="col-md-9">
                                    <div class="row g-3">
                                      <div class="col-md-4"><label for="remaining_check_bank"
                                          class="po-label">ธนาคาร</label><input id="remaining_check_bank"
                                          type="text" class="form-control" name="remaining_check_bank"
                                          value="{{ old('remaining_check_bank', $remainingPayment->check_bank ?? '') }}">
                                      </div>
                                      <div class="col-md-4"><label for="remaining_check_branch"
                                          class="po-label">สาขา</label><input id="remaining_check_branch"
                                          type="text" class="form-control" name="remaining_check_branch"
                                          value="{{ old('remaining_check_branch', $remainingPayment->check_branch ?? '') }}">
                                      </div>
                                      <div class="col-md-4"><label for="remaining_check_no"
                                          class="po-label">เลขที่</label><input id="remaining_check_no"
                                          type="text" class="form-control" name="remaining_check_no"
                                          value="{{ old('remaining_check_no', $remainingPayment->check_no ?? '') }}">
                                      </div>
                                    </div>
                                  </div>
                                @endif
                                @if (!$isHistory || $remainingPayment === 'transfer')
                                  <div id="bankRemain" class="col-md-9">
                                    <div class="row g-3">
                                      <div class="col-md-4"><label for="remaining_transfer_bank"
                                          class="po-label">ธนาคาร</label><input id="remaining_transfer_bank"
                                          type="text" class="form-control" name="remaining_transfer_bank"
                                          value="{{ old('remaining_transfer_bank', $remainingPayment->transfer_bank ?? '') }}">
                                      </div>
                                      <div class="col-md-4"><label for="remaining_transfer_branch"
                                          class="po-label">สาขา</label><input id="remaining_transfer_branch"
                                          type="text" class="form-control" name="remaining_transfer_branch"
                                          value="{{ old('remaining_transfer_branch', $remainingPayment->transfer_branch ?? '') }}">
                                      </div>
                                      <div class="col-md-4"><label for="remaining_transfer_no"
                                          class="po-label">เลขที่</label><input id="remaining_transfer_no"
                                          type="text" class="form-control" name="remaining_transfer_no"
                                          value="{{ old('remaining_transfer_no', $remainingPayment->check_no ?? '') }}">
                                      </div>
                                    </div>
                                  </div>
                                @endif
                              </div>

                            </div>
                          </div>
                        </div>

                        {{-- ── ผ่อน ── --}}
                        <div id="financeSection1" style="display:none">

                          {{-- Card 1: ราคา เงินดาวน์ และวันออกรถ --}}
                          <div class="po-section-edit">
                            <div class="po-section-header">
                              <div class="po-section-icon indigo"><i class="bx bxs-bank"></i></div>
                              <h6 class="po-section-title">ข้อมูลการซื้อผ่อน (Finance)</h6>
                            </div>
                            <div class="po-section-body-edit">
                              @php $deliveryType = $deliveryPayment->type ?? ''; @endphp

                              {{-- 3 sub-cards เรียง 3 คอลัมน์แนวนอน --}}
                              <div class="row g-3 align-items-stretch mb-3">

                                {{-- Sub-card 1: ราคาและการปรับราคา --}}
                                <div class="col-md-4">
                                  <div class="po-sub-card mb-0 h-100">
                                    <div class="po-sub-card-header">
                                      <div class="sub-icon" style="background:#fef3c7;color:#d97706;"><i
                                          class="bx bx-tag"></i></div>
                                      ราคาและการปรับราคา
                                    </div>
                                    <div class="po-sub-card-body">
                                      <div class="row g-3">
                                        <div class="col-12">
                                          <label for="MarkupPrice" class="po-label">บวกหัว</label>
                                          <div class="money-wrap">
                                            <input class="form-control text-end money-input" type="text"
                                              id="MarkupPrice" name="MarkupPrice"
                                              value="{{ $saleCar->MarkupPrice }}" />
                                            <span class="money-suffix">฿</span>
                                          </div>
                                        </div>
                                        <div class="col-12">
                                          <label for="Markup90" class="po-label">บวกหัว (90%)</label>
                                          <div class="money-wrap">
                                            <input class="form-control text-end money-input" type="text"
                                              id="Markup90" name="Markup90" value="{{ $saleCar->Markup90 }}" />
                                            <span class="money-suffix">฿</span>
                                          </div>
                                        </div>
                                        <div class="col-12">
                                          <label for="discount" class="po-label">ส่วนลดราคารถ</label>
                                          <div class="money-wrap">
                                            <input class="form-control text-end money-input" type="text"
                                              name="discount" id="discount" value="{{ $saleCar->discount }}" />
                                            <span class="money-suffix">฿</span>
                                          </div>
                                        </div>
                                        <div class="col-12">
                                          <label for="CarSalePriceFinal" class="po-label">ราคาขายสุทธิ</label>
                                          <div class="money-wrap">
                                            <input class="form-control text-end money-input fw-bold" type="text"
                                              name="CarSalePriceFinal" id="CarSalePriceFinal"
                                              value="{{ $saleCar->CarSalePriceFinal }}" readonly />
                                            <span class="money-suffix">฿</span>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>

                                {{-- Sub-card 2: เงินดาวน์ --}}
                                <div class="col-md-4">
                                  <div class="po-sub-card mb-0 h-100">
                                    <div class="po-sub-card-header">
                                      <div class="sub-icon" style="background:#d1fae5;color:#059669;"><i
                                          class="bx bx-down-arrow-circle"></i></div>
                                      เงินดาวน์
                                    </div>
                                    <div class="po-sub-card-body">
                                      <div class="row g-3">
                                        <div class="col-7">
                                          <label for="DownPayment" class="po-label">เงินดาวน์</label>
                                          <div class="money-wrap">
                                            <input class="form-control text-end money-input" type="text"
                                              name="DownPayment" id="DownPayment"
                                              value="{{ $saleCar->DownPayment }}" />
                                            <span class="money-suffix">฿</span>
                                          </div>
                                        </div>
                                        <div class="col-5">
                                          <label for="DownPaymentPercentage" class="po-label">% เงินดาวน์</label>
                                          <input class="form-control text-end money-input" type="text"
                                            name="DownPaymentPercentage" id="DownPaymentPercentage"
                                            value="{{ $saleCar->DownPaymentPercentage }}" />
                                        </div>
                                        <div class="col-12">
                                          <label for="DownPaymentDiscount" class="po-label">ส่วนลดเงินดาวน์</label>
                                          <div class="money-wrap">
                                            <input class="form-control text-end money-input" type="text"
                                              name="DownPaymentDiscount" id="DownPaymentDiscount"
                                              value="{{ $saleCar->DownPaymentDiscount }}" />
                                            <span class="money-suffix">฿</span>
                                          </div>
                                        </div>
                                        <div class="col-12">
                                          <label for="other_cost_fi" class="po-label">ค่าใช้จ่ายอื่นๆ</label>
                                          <div class="money-wrap">
                                            <input class="form-control text-end money-input" type="text"
                                              id="other_cost_fi" name="other_cost_fi"
                                              value="{{ $saleCar->other_cost_fi }}" />
                                            <span class="money-suffix">฿</span>
                                          </div>
                                        </div>
                                        <div class="col-12">
                                          <label for="reason_other_cost_fi"
                                            class="po-label">หมายเหตุค่าใช้จ่ายอื่นๆ</label>
                                          <textarea id="reason_other_cost_fi" class="form-control" name="reason_other_cost_fi" rows="2">{{ $saleCar->reason_other_cost_fi }}</textarea>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>

                                {{-- Sub-card 3: วันออกรถ --}}
                                <div class="col-md-4">
                                  <div class="po-sub-card mb-0 h-100">
                                    <div class="po-sub-card-header">
                                      <div class="sub-icon" style="background:#e0f2fe;color:#0284c7;"><i
                                          class="bx bx-calendar-event"></i></div>
                                      วันออกรถ
                                    </div>
                                    <div class="po-sub-card-body">
                                      <div class="row g-3">
                                        <div class="col-12">
                                          <label for="kickback" class="po-label">Kick Back</label>
                                          <div class="money-wrap">
                                            <input class="form-control text-end money-input" type="text"
                                              name="kickback" id="kickback" value="{{ $saleCar->kickback }}" />
                                            <span class="money-suffix">฿</span>
                                          </div>
                                        </div>
                                        <div class="col-12">
                                          <label for="AccessoryGiftVat" class="po-label">Vat ของแถม</label>
                                          <div class="money-wrap">
                                            <input class="form-control text-end money-input" type="text"
                                              name="AccessoryGiftVat" id="AccessoryGiftVat"
                                              value="{{ $saleCar->AccessoryGiftVat }}" />
                                            <span class="money-suffix">฿</span>
                                          </div>
                                        </div>
                                        <div class="col-12">
                                          <label for="AccessoryExtraVat" class="po-label">Vat ซื้อเพิ่ม</label>
                                          <div class="money-wrap">
                                            <input class="form-control text-end money-input" type="text"
                                              name="AccessoryExtraVat" id="AccessoryExtraVat"
                                              value="{{ $saleCar->AccessoryExtraVat }}" />
                                            <span class="money-suffix">฿</span>
                                          </div>
                                        </div>
                                        <input type="hidden" id="TotalPaymentatDelivery"
                                          name="TotalPaymentatDelivery">
                                        <div class="col-12">
                                          <label for="TotalPaymentatDeliveryCar"
                                            class="po-label">ค่าใช้จ่ายวันออกรถ</label>
                                          <div class="money-wrap">
                                            <input class="form-control text-end money-input fw-bold" type="text"
                                              id="TotalPaymentatDeliveryCar" name="delivery_cost"
                                              value="{{ old('delivery_cost', $deliveryPayment->cost ?? '') }}"
                                              readonly />
                                            <span class="money-suffix">฿</span>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>

                              </div>{{-- /row 3 sub-cards --}}

                              {{-- Card: วันที่และประเภทการจ่ายเงินค่าออกรถ --}}
                              <div class="po-sub-card mt-3">
                                <div class="po-sub-card-header">
                                  <div class="sub-icon" style="background:#e0f2fe;color:#0284c7;"><i
                                      class="bx bx-calendar-check"></i></div>
                                  วันที่และประเภทการจ่ายเงินค่าออกรถ
                                </div>
                                <div class="po-sub-card-body">
                                  <div class="row g-3 align-items-end mb-2">
                                    <div class="col-md-3">
                                      <label for="delivery_date" class="po-label"><i
                                          class="bx bx-calendar me-1"></i>
                                        วันที่จ่ายเงินค่าออกรถ</label>
                                      <input id="delivery_date" type="date" class="form-control"
                                        name="delivery_date"
                                        value="{{ old('delivery_date', $deliveryPayment?->date ?? '') }}">
                                    </div>
                                    <div class="col-md-9">
                                      <div class="po-label"><i class="bx bx-money"></i> ประเภทการจ่ายเงินวันออกรถ
                                      </div>
                                      <div class="pay-type-group mt-1">
                                        <input type="radio" name="deliveryCondition" id="cashDeli"
                                          value="cash" {{ $deliveryType === 'cash' ? 'checked' : '' }}
                                          {{ $disabled }}>
                                        <label for="cashDeli"><i class="bx bx-money me-1"></i>เงินสด</label>

                                        <input type="radio" name="deliveryCondition" id="creditDeli"
                                          value="credit" {{ $deliveryType === 'credit' ? 'checked' : '' }}
                                          {{ $disabled }}>
                                        <label for="creditDeli"><i
                                            class="bx bx-credit-card me-1"></i>บัตรเครดิต</label>

                                        <input type="radio" name="deliveryCondition" id="checkDeli"
                                          value="check" {{ $deliveryType === 'check' ? 'checked' : '' }}
                                          {{ $disabled }}>
                                        <label for="checkDeli"><i
                                            class="bx bx-building-house me-1"></i>เช็คธนาคาร</label>

                                        <input type="radio" name="deliveryCondition" id="transferDeli"
                                          value="transfer" {{ $deliveryType === 'transfer' ? 'checked' : '' }}
                                          {{ $disabled }}>
                                        <label for="transferDeli"><i
                                            class="bx bx-transfer-alt me-1"></i>เงินโอน</label>
                                      </div>
                                    </div>
                                  </div>

                                  @if (!$isHistory || $deliveryType === 'credit')
                                    <div id="creditDelivery" class="sub-section-edit">
                                      <div class="row g-3">
                                        <div class="col-md-4">
                                          <label for="delivery_credit" class="po-label">บัตรเครดิต</label>
                                          <input id="delivery_credit" type="text" class="form-control"
                                            name="delivery_credit"
                                            value="{{ old('delivery_credit', $deliveryPayment->credit ?? '') }}">
                                        </div>
                                        <div class="col-md-3">
                                          <label for="delivery_tax_credit" class="po-label">ค่าธรรมเนียม</label>
                                          <input id="delivery_tax_credit" type="text"
                                            class="form-control text-end money-input" name="delivery_tax_credit"
                                            value="{{ old('delivery_tax_credit', $deliveryPayment->tax_credit ?? '') }}">
                                        </div>
                                      </div>
                                    </div>
                                  @endif
                                  @if (!$isHistory || $deliveryType === 'check')
                                    <div id="checkDelivery" class="sub-section-edit">
                                      <div class="row g-3">
                                        <div class="col-md-3">
                                          <label for="delivery_check_bank" class="po-label">ธนาคาร</label>
                                          <input id="delivery_check_bank" type="text" class="form-control"
                                            name="delivery_check_bank"
                                            value="{{ old('delivery_check_bank', $deliveryPayment->check_bank ?? '') }}">
                                        </div>
                                        <div class="col-md-4">
                                          <label for="delivery_check_branch" class="po-label">สาขา</label>
                                          <input id="delivery_check_branch" type="text" class="form-control"
                                            name="delivery_check_branch"
                                            value="{{ old('delivery_check_branch', $deliveryPayment->check_branch ?? '') }}">
                                        </div>
                                        <div class="col-md-3">
                                          <label for="delivery_check_no" class="po-label">เลขที่</label>
                                          <input id="delivery_check_no" type="text" class="form-control"
                                            name="delivery_check_no"
                                            value="{{ old('delivery_check_no', $deliveryPayment->check_no ?? '') }}">
                                        </div>
                                      </div>
                                    </div>
                                  @endif
                                  @if (!$isHistory || $deliveryType === 'transfer')
                                    <div id="bankDelivery" class="sub-section-edit">
                                      <div class="row g-3">
                                        <div class="col-md-3">
                                          <label for="delivery_transfer_bank" class="po-label">ธนาคาร</label>
                                          <input id="delivery_transfer_bank" type="text" class="form-control"
                                            name="delivery_transfer_bank"
                                            value="{{ old('delivery_transfer_bank', $deliveryPayment->transfer_bank ?? '') }}">
                                        </div>
                                        <div class="col-md-4">
                                          <label for="delivery_transfer_branch" class="po-label">สาขา</label>
                                          <input id="delivery_transfer_branch" type="text" class="form-control"
                                            name="delivery_transfer_branch"
                                            value="{{ old('delivery_transfer_branch', $deliveryPayment->transfer_branch ?? '') }}">
                                        </div>
                                        <div class="col-md-3">
                                          <label for="delivery_transfer_no" class="po-label">เลขที่</label>
                                          <input id="delivery_transfer_no" type="text" class="form-control"
                                            name="delivery_transfer_no"
                                            value="{{ old('delivery_transfer_no', $deliveryPayment->transfer_no ?? '') }}">
                                        </div>
                                      </div>
                                    </div>
                                  @endif
                                </div>
                              </div>

                            </div>
                          </div>

                        </div>{{-- /financeSection1 --}}

                      </div>{{-- /sum-tab1 --}}

                      {{-- ══ Tab 2: ข้อมูลการจ่ายเงิน / ข้อมูลไฟแนนซ์ ══ --}}
                      <div class="tab-pane fade" id="sum-tab2" role="tabpanel">

                        {{-- ไฟแนนซ์: ข้อมูลไฟแนนซ์ --}}
                        <div id="financeSection2" style="display:none">
                          {{-- Card 2: ข้อมูลไฟแนนซ์ --}}
                          <div class="po-section-edit">
                            <div class="po-section-header">
                              <div class="po-section-icon sky"><i class="bx bx-buildings"></i></div>
                              <h6 class="po-section-title">ข้อมูลไฟแนนซ์</h6>
                            </div>
                            <div class="po-section-body-edit">
                              <div class="row g-3 align-items-stretch">

                                {{-- Sub-card ซ้าย: ข้อมูลไฟแนนซ์ --}}
                                <div class="col-md-6">
                                  <div class="po-sub-card mb-0 h-100">
                                    <div class="po-sub-card-header">
                                      <div class="sub-icon" style="background:#ede9fe;color:#6366f1;"><i
                                          class="bx bx-buildings"></i></div>
                                      ข้อมูลไฟแนนซ์
                                    </div>
                                    <div class="po-sub-card-body">
                                      <div class="row g-3">
                                        <div class="col-12">
                                          <label for="remaining_finance" class="po-label">ชื่อไฟแนนซ์</label>
                                          <select id="remaining_finance" name="remaining_finance"
                                            class="form-select" required>
                                            <option value="">-- เลือกไฟแนนซ์ --</option>
                                            @foreach ($finances as $f)
                                              <option value="{{ $f->id }}"
                                                data-max-year="{{ $f->max_year }}"
                                                {{ old('remaining_finance', $saleCar->remainingPayment->financeInfo->id ?? '') == $f->id ? 'selected' : '' }}>
                                                {{ $f->FinanceCompany }}</option>
                                            @endforeach
                                          </select>
                                        </div>
                                        <div class="col-6">
                                          <label for="remaining_contract_date"
                                            class="po-label">วันที่เซ็นสัญญา</label>
                                          <input id="remaining_contract_date" type="date" class="form-control"
                                            name="remaining_contract_date"
                                            value="{{ old('remaining_contract_date', $remainingPayment?->contract_date ?? '') }}">
                                        </div>
                                        <div class="col-6">
                                          <label for="balanceFinanceDisplay" class="po-label">ยอดจัดไฟแนนซ์</label>
                                          <div class="money-wrap">
                                            <input class="form-control text-end money-input fw-bold" type="text"
                                              id="balanceFinanceDisplay" value="{{ $saleCar->balanceFinance }}"
                                              readonly />
                                            <span class="money-suffix">฿</span>
                                          </div>
                                          <input type="hidden" id="balanceFinance" name="balanceFinance"
                                            value="{{ old('balanceFinance', $saleCar->balanceFinance ?? '') }}">
                                        </div>
                                        <div class="col-6">
                                          <label for="remaining_interest" class="po-label">ดอกเบี้ย (%)</label>
                                          <input class="form-control text-end" type="text"
                                            id="remaining_interest" name="remaining_interest"
                                            oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1')"
                                            value="{{ old('remaining_interest', $remainingPayment->interest ?? '') }}">
                                        </div>
                                        <div class="col-6">
                                          <label for="remaining_period" class="po-label">งวดผ่อน</label>
                                          <select id="remaining_period" name="remaining_period" class="form-select">
                                            <option value="">-- เลือกงวด --</option>
                                            @foreach ([12, 24, 36, 48, 60, 72, 84] as $p)
                                              <option value="{{ $p }}"
                                                {{ $remainingPayment?->period == $p ? 'selected' : '' }}>
                                                {{ $p }}</option>
                                            @endforeach
                                          </select>
                                        </div>
                                        <div class="col-6">
                                          <label for="remaining_type_com" class="po-label">ดอกเบี้ยคอม</label>
                                          <select id="remaining_type_com" name="remaining_type_com"
                                            class="form-select">
                                            <option value="">-- เลือก --</option>
                                            @foreach (['0' => 'C4', '8' => 'C8', '10' => 'C10', '12' => 'C12', '14' => 'C14', '16' => 'C16'] as $v => $l)
                                              <option value="{{ $v }}"
                                                {{ $remainingPayment?->type_com == $v ? 'selected' : '' }}>
                                                {{ $l }}</option>
                                            @endforeach
                                          </select>
                                        </div>
                                        <div class="col-6">
                                          <label for="remaining_total_com" class="po-label">ยอดเงินค่าคอม</label>
                                          <div class="money-wrap">
                                            <input id="remaining_total_com" name="remaining_total_com"
                                              class="form-control text-end money-input" type="text"
                                              value="{{ old('remaining_total_com', $remainingPayment->total_com ?? '') }}"
                                              readonly>
                                            <span class="money-suffix">฿</span>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>

                                {{-- Sub-card ขวา: ค่างวด / ALP / PO --}}
                                <div class="col-md-6">
                                  <div class="po-sub-card mb-0 h-100">
                                    <div class="po-sub-card-header">
                                      <div class="sub-icon" style="background:#fef3c7;color:#d97706;"><i
                                          class="bx bx-calculator"></i></div>
                                      ค่างวด / ALP / PO
                                    </div>
                                    <div class="po-sub-card-body">
                                      <div class="row g-3">
                                        <div class="col-6">
                                          <label for="remaining_alp" class="po-label">ค่างวด (ไม่มี ALP)</label>
                                          <div class="money-wrap">
                                            <input id="remaining_alp" name="remaining_alp"
                                              class="form-control text-end money-input" type="text"
                                              value="{{ old('remaining_alp', $remainingPayment->alp ?? '') }}"
                                              readonly />
                                            <span class="money-suffix">฿</span>
                                          </div>
                                        </div>
                                        <div class="col-6">
                                          <label for="remaining_including_alp" class="po-label">ค่างวด (รวม
                                            ALP)</label>
                                          <div class="money-wrap">
                                            <input id="remaining_including_alp" name="remaining_including_alp"
                                              class="form-control text-end money-input" type="text"
                                              value="{{ old('remaining_including_alp', $remainingPayment->including_alp ?? '') }}">
                                            <span class="money-suffix">฿</span>
                                          </div>
                                        </div>
                                        <div class="col-12">
                                          <label for="remaining_total_alp" class="po-label">ยอดเงิน ALP</label>
                                          <div class="money-wrap">
                                            <input id="remaining_total_alp" name="remaining_total_alp"
                                              class="form-control text-end money-input" type="text"
                                              value="{{ old('remaining_total_alp', $remainingPayment->total_alp ?? '') }}">
                                            <span class="money-suffix">฿</span>
                                          </div>
                                        </div>
                                        <div class="col-12">
                                          <label for="RegistrationProvince_finance"
                                            class="po-label">จังหวัดที่ขึ้นทะเบียน</label>
                                          <select id="RegistrationProvince_finance"
                                            name="RegistrationProvince_finance"
                                            class="registration-province form-select" required>
                                            <option value="">-- เลือกจังหวัด --</option>
                                            @foreach ($provinces as $p)
                                              <option value="{{ @$p->id }}"
                                                {{ $saleCar->RegistrationProvince == $p->id ? 'selected' : '' }}>
                                                {{ @$p->name }}</option>
                                            @endforeach
                                          </select>
                                        </div>
                                        <div class="col-6">
                                          <label for="remaining_po_number" class="po-label">PO Number</label>
                                          <input id="remaining_po_number" type="text" class="form-control"
                                            name="remaining_po_number"
                                            value="{{ old('remaining_po_number', $remainingPayment?->po_number ?? '') }}">
                                        </div>
                                        <div class="col-6">
                                          <label for="remaining_po_date" class="po-label">วันที่ PO</label>
                                          <input id="remaining_po_date" type="date" class="form-control"
                                            name="remaining_po_date"
                                            value="{{ old('remaining_po_date', $remainingPayment?->po_date ?? '') }}">
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>

                              </div>
                            </div>
                          </div>

                        </div>

                        <!-- ข้อมูลการจ่ายเงิน -->
                        <!-- <option value="cash" {{ $remainingPayment?->type == 'cash' ? 'selected' : '' }}>เงินสด</option> -->

                        <div id="paymentSection" style="display:none;">
                          <div class="po-section-edit">
                            <div class="po-section-header">
                              <div class="po-section-icon indigo"><i class="bx bx-wallet"></i></div>
                              <h6 class="po-section-title">ข้อมูลการจ่ายเงิน</h6>
                              <div class="ms-auto">
                                <button type="button" id="btnAddPayment" class="btn btn-primary btn-sm">
                                  <i class="bx bx-plus me-1"></i> เพิ่มรายการ
                                </button>
                              </div>
                            </div>
                            <div class="po-section-body-edit">
                              <div id="paymentContainer">

                                @php $payRows = $payments->count() > 0 ? $payments : collect([null]); @endphp
                                @foreach ($payRows as $idx => $p)
                                  <div
                                    class="payment-row row g-2 align-items-end {{ $idx > 0 ? 'mt-2 pt-2 border-top' : '' }}">
                                    <input type="hidden" name="payment_id[]" value="{{ $p?->id ?? '' }}">
                                    <div class="col-md-3">
                                      <label for="payment_type_{{ $idx }}" class="po-label"><i
                                          class="bx bx-credit-card me-1"></i>ประเภท</label>
                                      <select id="payment_type_{{ $idx }}" name="payment_type[]"
                                        class="form-select">
                                        <option value="">-- เลือกประเภท --</option>
                                        <option value="cash" {{ $p?->type == 'cash' ? 'selected' : '' }}>เงินสด
                                        </option>
                                        <option value="transfer" {{ $p?->type == 'transfer' ? 'selected' : '' }}>
                                          เงินโอน</option>
                                      </select>
                                    </div>
                                    <div class="col-md-4">
                                      <label for="payment_cost_{{ $idx }}" class="po-label"><i
                                          class="bx bx-money me-1"></i>จำนวนเงิน</label>
                                      <div class="money-wrap">
                                        <input id="payment_cost_{{ $idx }}" type="text"
                                          name="payment_cost[]" class="form-control text-end money-input"
                                          value="{{ $p ? number_format($p->cost, 0) : '' }}">
                                        <span class="money-suffix">฿</span>
                                      </div>
                                    </div>
                                    <div class="col-md-4">
                                      <label for="payment_date_{{ $idx }}" class="po-label"><i
                                          class="bx bx-calendar me-1"></i>วันที่จ่ายเงิน</label>
                                      <input id="payment_date_{{ $idx }}" type="date"
                                        name="payment_date[]" class="form-control" value="{{ $p?->date ?? '' }}">
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                      <button type="button" class="btn btn-outline-danger btnRemove"
                                        title="ลบ">
                                        <i class="bx bx-trash"></i>
                                      </button>
                                    </div>
                                  </div>
                                @endforeach

                              </div>
                              <input type="hidden" id="deletedPayments" name="deletedPayments" value="">
                            </div>
                          </div>
                        </div>{{-- /paymentSection --}}

                      </div>{{-- /sum-tab2 --}}

                      {{-- ══ Tab 3: ข้อมูลคนแนะนำ ══ --}}
                      <div class="tab-pane fade" id="sum-tab3" role="tabpanel">

                        {{-- ── ข้อมูลคนแนะนำ ── --}}
                        <div class="po-section-edit">
                          <div class="po-section-header">
                            <div class="po-section-icon amber"><i class="bx bx-user-voice"></i></div>
                            <h6 class="po-section-title">ข้อมูลคนแนะนำ</h6>
                          </div>
                          <div class="po-section-body-edit">

                            <input type="hidden" id="ReferrerID" name="ReferrerID"
                              value="{{ $saleCar->ReferrerID }}">
                            <input id="customerNameRef" type="hidden"
                              value="{{ trim(($saleCar->customerReferrer->prefix->Name_TH ?? '') . ' ' . ($saleCar->customerReferrer->FirstName ?? '') . ' ' . ($saleCar->customerReferrer->LastName ?? '')) }}">
                            <input id="customerPhoneRef" type="hidden"
                              value="{{ $saleCar->customerReferrer->formatted_mobile ?? '' }}">
                            <input id="customerIDRef" type="hidden"
                              value="{{ $saleCar->customerReferrer->formatted_id_number ?? '' }}">

                            <div class="row g-3 mb-3">
                              <div class="col-md-4">
                                <label class="po-label" for="customerSearchRef"><i class='bx bx-search-alt'></i>
                                  ค้นหาข้อมูลผู้แนะนำ</label>
                                <div class="input-group">
                                  <input id="customerSearchRef" type="text" class="form-control"
                                    name="customerSearchRef" placeholder="พิมพ์ชื่อ/เลขบัตร">
                                  <button type="button" class="btn btnSearchCustomerEdit px-3 border">
                                    <i class="bx bx-search me-1"></i> ค้นหา
                                  </button>
                                </div>
                              </div>

                              <div class="col-md-2">
                                <label class="po-label" for="ReferrerAmount">ยอดเงินค่าแนะนำ</label>
                                <div class="money-wrap">
                                  <input id="ReferrerAmount" type="text"
                                    class="form-control text-end money-input" name="ReferrerAmount"
                                    value="{{ $saleCar->ReferrerAmount }}" />
                                  <span class="money-suffix">฿</span>
                                </div>
                              </div>
                            </div>

                            @php
                              $refName = trim(
                                  ($saleCar->customerReferrer->prefix->Name_TH ?? '') .
                                      ' ' .
                                      ($saleCar->customerReferrer->FirstName ?? '') .
                                      ' ' .
                                      ($saleCar->customerReferrer->LastName ?? ''),
                              );
                            @endphp
                            <div class="customer-info-row-edit mb-1">
                              <div class="row g-2">
                                <div class="col-md-4">
                                  <div class="po-label"><i class='bx bxs-user'></i> ชื่อ - นามสกุล</div>
                                  <div class="info-val {{ $refName ? '' : 'empty' }}" id="customerNameRef-display">
                                    {{ $refName ?: '—' }}
                                  </div>
                                </div>
                                <div class="col-md-4">
                                  <div class="po-label"><i class='bx bx-id-card'></i> เลขบัตรประชาชน</div>
                                  <div class="info-val {{ $saleCar->customerReferrer ? '' : 'empty' }}"
                                    id="customerIDRef-display">
                                    {{ $saleCar->customerReferrer->formatted_id_number ?? '—' }}
                                  </div>
                                </div>
                                <div class="col-md-4">
                                  <div class="po-label"><i class='bx bx-phone'></i> เบอร์โทรศัพท์</div>
                                  <div class="info-val {{ $saleCar->customerReferrer ? '' : 'empty' }}"
                                    id="customerPhoneRef-display">
                                    {{ $saleCar->customerReferrer->formatted_mobile ?? '—' }}
                                  </div>
                                </div>
                              </div>
                            </div>

                          </div>
                        </div>

                      </div>{{-- /sum-tab3 --}}

                      {{-- ══ Tab 4: ข้อมูลวันส่งมอบ ══ --}}
                      <div class="tab-pane fade" id="sum-tab4" role="tabpanel">

                        {{-- ── ข้อมูลวันส่งมอบ ── --}}
                        <div class="po-section mt-2">
                          <div class="po-section-header">
                            <div class="po-section-icon sky"><i class="bx bx-calendar-event"></i></div>
                            <h6 class="po-section-title">ข้อมูลวันส่งมอบ</h6>
                          </div>
                          <div class="po-section-body-edit">
                            <div class="row g-3">

                              <div class="col-md-4">
                                <div class="date-card">
                                  <div class="date-card-icon" style="background:#e0f2fe;color:#0284c7;">
                                    <i class="bx bx-file-blank"></i>
                                  </div>
                                  <div class="date-card-body">
                                    <label for="KeyInDate" class="date-card-label">วันที่ส่งเอกสารสรุปการขาย</label>
                                    <input class="form-control" type="date" id="KeyInDate" name="KeyInDate"
                                      value="{{ $saleCar->KeyInDate }}" />
                                  </div>
                                </div>
                              </div>

                              <div class="col-md-4">
                                <div class="date-card">
                                  <div class="date-card-icon" style="background:#d1fae5;color:#059669;">
                                    <i class="bx bx-car"></i>
                                  </div>
                                  <div class="date-card-body">
                                    <label for="DeliveryDate" class="date-card-label">วันส่งมอบจริง
                                      (วันที่แจ้งประกัน)</label>
                                    {{-- <label for="DeliveryDate" class="date-card-label">วันส่งมอบจริง<br><span style="color:#6b7280;font-weight:400;">(วันที่แจ้งประกัน)</span></label> --}}
                                    <input class="form-control" type="date" id="DeliveryDate"
                                      name="DeliveryDate" value="{{ $saleCar->DeliveryDate }}" />
                                  </div>
                                </div>
                              </div>

                              @if ($userRole == 'admin' || $userRole == 'audit' || $userRole == 'manager' || $userRole == 'md')
                                <div class="col-md-4">
                                  <div class="date-card">
                                    <div class="date-card-icon" style="background:#fee2e2;color:#dc2626;">
                                      <i class="bx bx-purchase-tag"></i>
                                    </div>
                                    <div class="date-card-body">
                                      <label for="red_license" class="date-card-label">ป้ายแดง</label>
                                      <select id="red_license" name="red_license" class="form-select" required
                                        {{ $disabled }}>
                                        <option value="">-- เลือก --</option>
                                        @foreach ($licensePlateRed as $r)
                                          <option value="{{ @$r->id }}"
                                            {{ $saleCar->red_license == $r->id ? 'selected' : '' }}>
                                            {{ @$r->number }}
                                          </option>
                                        @endforeach
                                      </select>
                                    </div>
                                  </div>
                                </div>
                              @endif

                            </div>
                          </div>
                        </div>

                      </div>{{-- /sum-tab4 --}}

                      {{-- ══ Tab 5: ยอดค่าคอม Sale ══ --}}
                      @if (auth()->user()->brand != 2)
                      <div class="tab-pane fade" id="sum-tab5" role="tabpanel">

                          {{-- ── ยอดค่าคอม sale ── --}}
                          <div class="po-section mt-2">
                            <div class="po-section-header">
                              <div class="po-section-icon emerald"><i class="bx bx-dollar-circle"></i></div>
                              <h6 class="po-section-title">ยอดค่าคอม Sale</h6>
                            </div>
                            <div class="po-section-body-edit">

                              <input type="hidden" name="CommissionSale" id="CommissionSale"
                                value="{{ old('CommissionSale', $saleCar->CommissionSale ?? '') }}">

                              <div class="com-stat-strip">

                                <div class="com-stat-cell">
                                  <span class="com-lbl">คอมงบเหลือ</span>
                                  <input type="text" class="com-readonly-val money-input"
                                    id="TotalbalanceCampaign" readonly>
                                </div>

                                <div class="com-stat-cell">
                                  <span class="com-lbl">คอมประดับยนต์</span>
                                  <input type="text" class="com-readonly-val money-input" id="ComGiftDisplay"
                                    readonly>
                                </div>

                                <div class="com-stat-cell">
                                  <span class="com-lbl">คอมดอกเบี้ย</span>
                                  <input type="text" class="com-readonly-val money-input" id="ComInterestDisplay"
                                    readonly>
                                </div>

                                <div class="com-stat-cell">
                                  <span class="com-lbl">คอมรถเทิร์น</span>
                                  <input type="text" class="com-readonly-val money-input" id="summaryComTurn"
                                    readonly>
                                </div>

                                <div class="com-stat-cell">
                                  <span class="com-lbl">คอมอื่นๆ</span>
                                  <div class="money-wrap">
                                    <input type="text" class="form-control text-end money-input"
                                      id="CommissionSpecial" name="CommissionSpecial"
                                      value="{{ $saleCar->CommissionSpecial }}">
                                    <span class="money-suffix">฿</span>
                                  </div>
                                </div>

                                <div class="com-stat-cell com-total-cell">
                                  <span class="com-lbl">รวมค่าคอม</span>
                                  <input type="text" class="com-readonly-val money-input"
                                    id="CommissionSaleDisplay" value="{{ $saleCar->CommissionSale }}" readonly>
                                </div>

                              </div>
                            </div>
                          </div>
                      </div>{{-- /sum-tab5 --}}
                      @endif

                    </div>{{-- /tab-content #summaryTabsContent --}}

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

                <div class="row">

                  {{-- ── Card 1 : วันส่งมอบและสถานะ ── --}}
                  <div class="col-12">
                    <div class="po-section-edit">
                      <div class="po-section-header">
                        <div class="po-section-icon sky"><i class="bx bx-calendar-check"></i></div>
                        <h6 class="po-section-title">วันส่งมอบและสถานะ</h6>
                      </div>
                      <div class="po-section-body-edit">
                        <div class="row g-3">

                          <div class="col-md-3">
                            <label for="DeliveryInDMSDate" class="po-label"><i class="bx bx-building me-1"></i>
                              วันที่ส่งมอบของบริษัท</label>
                            <input class="form-control" type="date" id="DeliveryInDMSDate"
                              name="DeliveryInDMSDate" value="{{ $saleCar->DeliveryInDMSDate }}" />
                          </div>

                          <div class="col-md-3">
                            <label for="DeliveryEstimateDate" class="po-label"><i class="bx bx-time me-1"></i>
                              ประมาณการส่งมอบ</label>
                            <input class="form-control" type="month" id="DeliveryEstimateDate"
                              name="DeliveryEstimateDate"
                              value="{{ old('DeliveryEstimateDate', $saleCar->delivery_estimate_date_month) }}" />
                          </div>

                          <div class="col-md-3">
                            <label for="DeliveryInCKDate" class="po-label"><i class="bx bx-user-check me-1"></i>
                              วันที่ส่งมอบของฝ่ายขาย</label>
                            <input class="form-control" type="date" id="DeliveryInCKDate"
                              name="DeliveryInCKDate" value="{{ $saleCar->DeliveryInCKDate }}" />
                          </div>

                          <div class="col-md-3">
                            <label for="con_status" class="po-label"><i class="bx bx-flag me-1"></i> สถานะ</label>
                            <select id="con_status" name="con_status" class="form-select" required>
                              <option value="">-- เลือกสถานะ --</option>
                              @foreach ($conStatus as $con)
                                @if ($con->id != 9)
                                  <option value="{{ @$con->id }}"
                                    {{ $saleCar->con_status == $con->id ? 'selected' : '' }}>{{ @$con->name }}
                                  </option>
                                @endif
                              @endforeach
                            </select>
                          </div>

                          <div class="col-md-12">
                            <label for="Note" class="po-label"><i class="bx bx-notepad me-1"></i>
                              หมายเหตุ</label>
                            <textarea id="Note" class="form-control" name="Note" rows="2">{{ $saleCar->Note }}</textarea>
                          </div>

                        </div>
                      </div>
                    </div>
                  </div>

                  {{-- ── Card 2 : การเช็คและอนุมัติ ── --}}
                  <div class="col-12">
                    <div class="po-section-edit">
                      <div class="po-section-header">
                        <div class="po-section-icon emerald"><i class="bx bx-check-shield"></i></div>
                        <h6 class="po-section-title">การเช็คและอนุมัติ</h6>
                      </div>
                      <div class="po-section-body-edit">
                        <div class="row g-3">

                          <div class="col-md-6">
                            <div class="approval-card @if ($saleCar->AdminSignature) approved @endif">
                              <div class="approval-card-header">
                                <div class="approval-icon sky"><i class="bx bx-user"></i></div>
                                <div class="approval-title">เช็ครายการ (แอดมินขาย)</div>
                                <div class="ms-auto">
                                  <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" id="AdminSignature"
                                      name="AdminSignature" value="1"
                                      {{ $saleCar->AdminSignature ? 'checked' : '' }}>
                                  </div>
                                </div>
                              </div>
                              <div class="approval-card-body">
                                <label for="AdminCheckedDate" class="more-field-label mb-1">วันที่เช็ครายการ</label>
                                <input class="form-control" type="date" id="AdminCheckedDate"
                                  name="AdminCheckedDate" value="{{ $saleCar->AdminCheckedDate }}">
                              </div>
                            </div>
                          </div>

                          <div class="col-md-6">
                            <div class="approval-card @if ($saleCar->CheckerID) approved @endif">
                              <div class="approval-card-header">
                                <div class="approval-icon indigo"><i class="bx bx-search-alt-2"></i></div>
                                <div class="approval-title">ตรวจสอบรายการ (IA)</div>
                                <div class="ms-auto">
                                  <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" id="CheckerID"
                                      name="CheckerID" value="1" {{ $saleCar->CheckerID ? 'checked' : '' }}>
                                  </div>
                                </div>
                              </div>
                              <div class="approval-card-body">
                                <label for="CheckerCheckedDate" class="more-field-label mb-1">วันที่ตรวจสอบ</label>
                                <input class="form-control" type="date" id="CheckerCheckedDate"
                                  name="CheckerCheckedDate" value="{{ $saleCar->CheckerCheckedDate }}">
                              </div>
                            </div>
                          </div>

                          <div class="col-md-6">
                            <div class="approval-card @if ($saleCar->SMSignature) approved @endif">
                              <div class="approval-card-header">
                                <div class="approval-icon amber"><i class="bx bx-medal"></i></div>
                                <div class="approval-title">ผู้จัดการ อนุมัติการขาย</div>
                                <div class="ms-auto">
                                  <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" id="SMSignature"
                                      name="SMSignature" value="1"
                                      {{ $saleCar->SMSignature ? 'checked' : '' }}>
                                  </div>
                                </div>
                              </div>
                              <div class="approval-card-body">
                                <label for="SMCheckedDate" class="more-field-label mb-1">วันที่อนุมัติ</label>
                                <input class="form-control" type="date" id="SMCheckedDate" name="SMCheckedDate"
                                  value="{{ $saleCar->SMCheckedDate }}">
                              </div>
                            </div>
                          </div>

                          <div class="col-md-6">
                            <div class="approval-card @if ($saleCar->ApprovalSignature) approved @endif">
                              <div class="approval-card-header">
                                <div class="approval-icon rose"><i class="bx bx-trending-up"></i></div>
                                <div class="approval-title">ผู้จัดการ อนุมัติกรณีงบเกิน</div>
                                <div class="ms-auto">
                                  <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" id="ApprovalSignature"
                                      name="ApprovalSignature" value="1"
                                      {{ $saleCar->ApprovalSignature ? 'checked' : '' }}>
                                  </div>
                                </div>
                              </div>
                              <div class="approval-card-body">
                                <label for="ApprovalSignatureDate" class="more-field-label mb-1">วันที่อนุมัติ</label>
                                <input class="form-control" type="date" id="ApprovalSignatureDate"
                                  name="ApprovalSignatureDate" value="{{ $saleCar->ApprovalSignatureDate }}">
                              </div>
                            </div>
                          </div>

                          <div class="col-md-6">
                            <div class="approval-card @if ($saleCar->GMApprovalSignature) approved @endif">
                              <div class="approval-card-header">
                                <div class="approval-icon pink"><i class="bx bx-crown"></i></div>
                                <div class="approval-title">GM อนุมัติกรณีงบเกิน (N)</div>
                                <div class="ms-auto">
                                  <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" id="GMApprovalSignature"
                                      name="GMApprovalSignature" value="1"
                                      {{ $saleCar->GMApprovalSignature ? 'checked' : '' }}>
                                  </div>
                                </div>
                              </div>
                              <div class="approval-card-body">
                                <label for="GMApprovalSignatureDate"
                                  class="more-field-label mb-1">วันที่อนุมัติ</label>
                                <input class="form-control" type="date" id="GMApprovalSignatureDate"
                                  name="GMApprovalSignatureDate" value="{{ $saleCar->GMApprovalSignatureDate }}">
                              </div>
                            </div>
                          </div>

                        </div>
                      </div>
                    </div>
                  </div>

                </div>

                <input type="hidden" name="reason_campaign" id="reason_campaign">

                <input type="hidden" id="approvalRequested"
                  value="{{ $saleCar->approval_requested_at ? 1 : 0 }}">

                <input type="hidden" id="approvalType" value="{{ $saleCar->approval_type ?? '' }}">

                @if (!$isHistory)
                  <div class="mt-6 d-flex justify-content-end gap-2">
                    <button id="prevCar" class="btn btn-danger">ย้อนกลับ</button>
                    <button type="button" class="btn btn-info" id="btnPreviewMore">
                      ตรวจสอบ
                    </button>
                    <!-- <button type="submit" class="btn btn-primary btnUpdatePurchase">บันทึก</button> -->
                  </div>
                @endif

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

  @if ($isHistory)
    <style>
      #purchaseForm input,
      #purchaseForm select,
      #purchaseForm textarea {
        pointer-events: none;
        background-color: #f8f9fa;
      }
    </style>
  @endif

@endsection
