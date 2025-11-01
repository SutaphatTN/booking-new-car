@extends('layouts/contentNavbarLayout')
@section('title', 'Data Purchase Order')

@section('page-script')
@vite(['resources/assets/js/purchase-order.js'])
@endsection

@section('content')
<div id="viewAccessory"></div>
<div id="viewGift"></div>

<div class="row">
  <div class="col-md-12">
    <h6 class="text-body-secondary">ข้อมูลการจอง</h6>
    <form
      action="{{ route('purchase-order.update', $saleCar->id) }}"
      method="POST"
      enctype="multipart/form-data">
      @csrf
      @method('PUT')

      <div class="nav-align-top">
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
              <span class="d-none d-sm-inline-flex align-items-center"><i class="icon-base bx bx-credit-card icon-sm me-1_5"></i>ข้อมูลการขาย</span>
              <i class="icon-base bx bx-credit-card icon-sm d-sm-none"></i>
            </button>
          </li>

          <li class="nav-item mb-1 mb-sm-0">
            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-accessory-gift" aria-controls="tab-accessory-gift" aria-selected="false">
              <span class="d-none d-sm-inline-flex align-items-center"><i class="icon-base bx bx-wrench icon-sm me-1_5"></i>ประดับยนต์</span>
              <i class="icon-base bx bx-wrench icon-sm d-sm-none"></i>
            </button>
          </li>

          <li class="nav-item mb-1 mb-sm-0">
            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-date" aria-controls="tab-date" aria-selected="false">
              <span class="d-none d-sm-inline-flex align-items-center"><i class="icon-base bx bx-calendar-event icon-sm me-1_5"></i>ข้อมูลวันสำคัญ</span>
              <i class="icon-base bx bx-calendar-event icon-sm d-sm-none"></i>
            </button>
          </li>

          <li class="nav-item mb-1 mb-sm-0">
            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-approved" aria-controls="tab-approved" aria-selected="false">
              <span class="d-none d-sm-inline-flex align-items-center"><i class="icon-base bx bx-group icon-sm me-1_5"></i>ผู้อนุมัติ</span>
              <i class="icon-base bx bx-group icon-sm d-sm-none"></i>
            </button>
          </li>

        </ul>

        <div class="tab-content">

          <!-- detail -->
          <div class="tab-pane fade show active" id="tab-detail" role="tabpanel">
            <div class="row">
              <div class="col-md-12">
                <div class="row g-6">
                  <h4 class="pb-2 mb-3 border-bottom">ข้อมูลลูกค้า</h4>
                  <div class="col-md-3">
                    <label for="SaleID" class="form-label">รหัสผู้ขาย</label>
                    <input class="form-control @error('SaleID') is-invalid @enderror"
                      type="text" id="SaleID" name="SaleID"
                      value="{{ $saleCar->SaleID }}" />

                    @error('SaleID')
                    <span class="invalid-feedback" role="alert">
                      <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                  </div>
                  <div class="col-md-4">
                    <label for="" class="form-label">ชื่อ - นามสกุล ผู้ขาย</label>
                    <input class="form-control" type="text" name="" id=""
                      value="" />
                  </div>
                  <div class="col-md-2">
                    <label class="form-label" for="BookingDate">วันที่จอง</label>
                    <input id="BookingDate" type="date"
                      class="form-control @error('BookingDate') is-invalid @enderror"
                      name="BookingDate" value="{{ $saleCar->BookingDate }}" required>

                    @error('BookingDate')
                    <span class="invalid-feedback" role="alert">
                      <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                  </div>
                  <div class="col-md-3">
                    <label for="CashDeposit" class="form-label">เงินจอง</label>
                    <input class="form-control text-end @error('CashDeposit') is-invalid @enderror" type="text" name="CashDeposit" id="CashDeposit"
                      value="{{ $saleCar->CashDeposit }}" />

                    @error('CashDeposit')
                    <span class="invalid-feedback" role="alert">
                      <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                  </div>
                  <div class="col-md-3">
                    <label for="CarModelID" class="form-label">รุ่นรถหลัก</label>
                    <select id="CarModelID" name="CarModelID" class="form-select">
                      <option selected>-- เลือกรุ่นรถ --</option>
                      @foreach ($carModel as $item)
                      <option value="{{ @$item->id }}" {{ $saleCar->CarModelID == $item->id ? 'selected' : '' }}>{{ @$item->Name_TH }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label for="CarModelID" class="form-label">รุ่นรถย่อย</label>
                    <select id="CarModelID" name="CarModelID" class="form-select">
                      <option selected>-- เลือกรุ่นรถ --</option>
                      @foreach ($carModel as $item)
                      <option value="{{ @$item->id }}" {{ $saleCar->CarModelID == $item->id ? 'selected' : '' }}>{{ @$item->Name_TH }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label for="CarModelID" class="form-label">option</label>
                    <select id="CarModelID" name="CarModelID" class="form-select">
                      <option selected>-- เลือกรุ่นรถ --</option>
                      @foreach ($carModel as $item)
                      <option value="{{ @$item->id }}" {{ $saleCar->CarModelID == $item->id ? 'selected' : '' }}>{{ @$item->Name_TH }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-1">
                    <label for="Color" class="form-label">ปี</label>
                    <input class="form-control" type="text" name="Color" id="Color"
                      value="{{ $saleCar->Color }}" />
                  </div>
                  <div class="col-md-1">
                    <label for="Color" class="form-label">สี</label>
                    <input class="form-control" type="text" name="Color" id="Color"
                      value="{{ $saleCar->Color }}" />
                  </div>

                  <input type="hidden" id="CusID" name="CusID" value="{{ $saleCar->CusID }}">
                  <div class="col-md-3">
                    <label class="form-label">ชื่อ - นามสกุล</label>
                    <input type="text" class="form-control"
                      value="{{ $saleCar->customer->prefix->Name_TH ?? '' }} {{ $saleCar->customer->FirstName }} {{ $saleCar->customer->LastName }}"
                      readonly>
                  </div>
                  <div class="col-md-2">
                    <label for="Mobilephone1" class="form-label">เบอร์โทรศัพท์</label>
                    <input class="form-control" type="text" name="Mobilephone1" id="Mobilephone1"
                      value="{{ $saleCar->customer->formatted_mobile }}" readonly>
                  </div>
                  <div class="col-md-3">
                    <label for="IDNumber" class="form-label">เลขบัตรประชาชน</label>
                    <input class="form-control" type="text" name="IDNumber" id="IDNumber"
                      value="{{ $saleCar->customer->formatted_id_number }}" readonly>
                  </div>

                  <div class="col-md-4">
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

                  <div class="col-md-2">
                    <label for="" class="form-label">Car Order ID</label>
                    <input class="form-control" type="text" id="" name="" />
                  </div>
                  <div class="col-md-3">
                    <label for="" class="form-label">เลขถัง / J-Number</label>
                    <input class="form-control" type="text" id="" name="" />
                  </div>
                  <div class="col-md-1">
                    <label for="" class="form-label">ปี</label>
                    <input class="form-control" type="text" id="" name="" />
                  </div>


                  @if($saleCar->TurnCarID)
                  <h4 class="pt-2 pb-2 border-bottom">ข้อมูลรถเทิร์น</h4>
                  <div class="col-md-2">
                    <label for="brand" class="form-label">ยี่ห้อ</label>
                    <input class="form-control" type="text" id="brand" name="brand"
                      value="{{ $saleCar->turnCar->brand }}" />
                  </div>
                  <div class="col-md-2">
                    <label for="model" class="form-label">รุ่น</label>
                    <input class="form-control" type="text" name="model" id="model"
                      value="{{ $saleCar->turnCar->model }}" />
                  </div>
                  <div class="col-md-3">
                    <label for="machine" class="form-label">เครื่องยนต์</label>
                    <input class="form-control" type="text" name="machine" id="machine"
                      value="{{ $saleCar->turnCar->machine }}" />
                  </div>
                  <div class="col-md-1">
                    <label for="year" class="form-label">ปี</label>
                    <input class="form-control" type="text" id="year" name="year"
                      value="{{ $saleCar->turnCar->year }}" />
                  </div>
                  <div class="col-md-2">
                    <label for="color" class="form-label">สี</label>
                    <input class="form-control" type="text" id="color" name="color"
                      value="{{ $saleCar->turnCar->color }}" />
                  </div>
                  <div class="col-md-2">
                    <label for="license_plate" class="form-label">ทะเบียน</label>
                    <input class="form-control" type="text" name="license_plate" id="license_plate"
                      value="{{ $saleCar->turnCar->license_plate }}" />
                  </div>
                  @endif

                </div>
                <div class="mt-6 d-flex justify-content-end gap-2">
                  <button id="nextAccessory" class="btn btn-primary">ถัดไป</button>
                  <!-- <button id="btnPrevDate" class="btn btn-danger">ย้อนกลับ</button> -->
                </div>

              </div>
            </div>
          </div>

          <!-- Accessory -->
          <div class="tab-pane fade" id="tab-accessory-gift" role="tabpanel">

            <div class="nav-align-top">
              <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                  <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#tab-accessory" aria-controls="tab-accessory" aria-selected="true">รายละเอียดอุปกรณ์ตกแต่ง (แถม)</button>
                </li>
                <li class="nav-item">
                  <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-gift" aria-controls="tab-gift" aria-selected="false">รายการซื้อเพิ่ม</button>
                </li>
              </ul>
              <div class="tab-content">
                <div class="tab-pane fade show active" id="tab-accessory" role="tabpanel">
                  <div class="d-flex align-items-center position-relative pb-2 mb-3">
                    <h4 class="mb-0 position-absolute start-50 translate-middle-x">รายละเอียดอุปกรณ์ตกแต่ง (แถม)</h4>
                    <button type="button" class="btn btn-secondary btn-md btnAccessory ms-auto">
                      <i class="bx bx-plus me-1"></i> เพิ่ม
                    </button>
                  </div>

                  <input type="hidden" id="TotalAccessoryGift" name="TotalAccessoryGift">
                  <input type="hidden" id="accessory_ids" name="accessory_ids">
                  <input type="hidden" id="total_accessory_used" name="total_accessory_used">

                  <div class="table-responsive text-nowrap">
                    <table class="table table-bordered" id="accessoryTablePrice">
                      <thead>
                        <tr>
                          <th>No.</th>
                          <th>รหัส</th>
                          <th>รายละเอียด</th>
                          <th>ประเภทราคา</th>
                          <th>ราคา</th>
                          <th>ค่าคอม</th>
                          <th>ลบ</th>
                        </tr>
                      </thead>
                      <tbody>
                        @if($saleCar->accessories->count() > 0)
                        @foreach($saleCar->accessories->where('pivot.type', 'gift') as $index => $a)
                        <tr data-id="{{ $a->id }}" data-price="{{ $a->pivot->price }}" data-com="{{ $a->pivot->commission }}">
                          <td>{{ $index + 1 }}</td>
                          <td>{{ $a->AccessorySource }}</td>
                          <td>{{ $a->AccessoryDetail }}</td>
                          <td>{{ ucfirst($a->pivot->price_type) }}</td>
                          <td>{{ number_format($a->pivot->price, 2) }}</td>
                          <td>{{ number_format($a->pivot->commission, 2) }}</td>
                          <td>
                            <button type="button" class="btn btn-sm btn-danger btn-delete-accessory">
                              <i class="bx bx-trash"></i>
                            </button>
                          </td>
                        </tr>
                        @endforeach
                        @else
                        <tr id="no-data-row">
                          <td colspan="7" class="text-center">ยังไม่มีข้อมูล</td>
                        </tr>
                        @endif
                      </tbody>

                      <tfoot>
                        <tr id="total-row">
                          <td colspan="6" class="text-end text-black fw-bold">ยอดรวมทั้งหมด</td>
                          <td id="total-price" class="text-end fw-bold">0</td>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>

                <div class="tab-pane fade" id="tab-gift" role="tabpanel">
                  <div class="d-flex align-items-center position-relative pb-2 mb-3">
                    <h4 class="mb-0 position-absolute start-50 translate-middle-x">รายการซื้อเพิ่ม</h4>
                    <button type="button" class="btn btn-secondary btn-md btnGift ms-auto">
                      <i class="bx bx-plus me-1"></i> เพิ่ม
                    </button>
                  </div>

                  <input type="hidden" id="TotalAccessoryExtra" name="TotalAccessoryExtra">
                  <input type="hidden" id="gift_ids" name="gift_ids">
                  <input type="hidden" id="total_gift_used" name="total_gift_used">

                  <div class="table-responsive text-nowrap">
                    <table class="table table-bordered" id="giftTable">
                      <thead>
                        <tr>
                          <th>No.</th>
                          <th>รหัส</th>
                          <th>รายละเอียด</th>
                          <th>ประเภทราคา</th>
                          <th>ราคา</th>
                          <th>ค่าคอม</th>
                          <th>ลบ</th>
                        </tr>
                      </thead>
                      <tbody>
                        @if($saleCar->accessories->count() > 0)
                        @foreach($saleCar->accessories->where('pivot.type', 'extra') as $index => $a)
                        <tr data-id="{{ $a->id }}" data-price="{{ $a->pivot->price }}" data-com="{{ $a->pivot->commission }}">
                          <td>{{ $index + 1 }}</td>
                          <td>{{ $a->AccessorySource }}</td>
                          <td>{{ $a->AccessoryDetail }}</td>
                          <td>{{ ucfirst($a->pivot->price_type) }}</td>
                          <td>{{ number_format($a->pivot->price, 2) }}</td>
                          <td>{{ number_format($a->pivot->commission, 2) }}</td>
                          <td>
                            <button type="button" class="btn btn-sm btn-danger btn-delete-gift">
                              <i class="bx bx-trash"></i>
                            </button>
                          </td>
                        </tr>
                        @endforeach
                        @else
                        <tr id="no-data-gift">
                          <td colspan="7" class="text-center">ยังไม่มีข้อมูล</td>
                        </tr>
                        @endif
                      </tbody>
                      <tfoot>
                        <tr id="total-row">
                          <td colspan="6" class="text-end text-black fw-bold">ยอดรวมทั้งหมด</td>
                          <td id="total-price-gift" class="text-end fw-bold">0</td>
                        </tr>
                      </tfoot>
                    </table>
                  </div>

                </div>
                <div class="mt-2 d-flex justify-content-end gap-2">
                  <button id="prevDetail" class="btn btn-danger">ย้อนกลับ</button>
                  <button id="nextPrice" class="btn btn-primary">ถัดไป</button>
                </div>
              </div>
            </div>

          </div>

          <!-- price -->
          <div class="tab-pane fade" id="tab-price" role="tabpanel">
            <div class="nav-align-top">
              <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                  <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#tab-campaign" aria-controls="tab-campaign" aria-selected="false">แคมเปญ</button>
                </li>
                <li class="nav-item">
                  <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-car" aria-controls="tab-car" aria-selected="true">สรุปค่าใช้จ่ายวันออกรถ</button>
                </li>
                <li class="nav-item">
                  <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-condition-price" aria-controls="tab-condition-price" aria-selected="false">เงื่อนไขการชำระเงิน</button>
                </li>

              </ul>
              <div class="tab-content">
                <div class="tab-pane fade show active" id="tab-campaign" role="tabpanel">
                  <h4 class="pb-2 mb-3">แคมเปญ</h4>

                  <div class="row">
                    <div class="col-md-2"></div>
                    <div class="col-md-6">
                      <label for="CampaignID" class="form-label">เลือกแคมเปญ</label>
                      <select name="CampaignID[]" id="CampaignID" multiple class="form-select">
                        @foreach ($campaigns as $camp)
                        <option value="{{ $camp->id }}"
                          data-cashsupport="{{ $camp->CashSupport }}"
                          {{ in_array($camp->id, $selected_campaigns ?? []) ? 'selected' : '' }}>
                          {{ $camp->campaignType->Name_TH ?? '-' }} - {{ number_format($camp->CashSupport, 2) }} บาท
                        </option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-md-2">
                      <label for="" class="form-label">ยอดรวมค่าแคมเปญ</label>
                      <input class="form-control text-end" type="text" id="TotalSaleCampaign" name="TotalSaleCampaign" placeholder="0.00" readonly />
                    </div>
                    <div class="col-md-2"></div>
                  </div>

                </div>

                <div class="tab-pane fade" id="tab-car" role="tabpanel">
                  <h4 class="pb-2 mb-3">สรุปค่าใช้จ่ายวันออกรถ</h4>

                  <div class="row g-6">
                    <div class="col-md-3">
                      <label class="form-label">รุ่นรถ</label>
                      <input id="summaryCarModel" class="form-control" value="{{ $saleCar->carModel->Name_TH ?? '' }}" disabled />
                    </div>

                    <div class="col-md-3">
                      <label for="" class="form-label">แบบ</label>
                      <input class="form-control" type="text" id="" name="" disabled />
                    </div>

                    <div class="col-md-2">
                      <label class="form-label">สี</label>
                      <input id="summaryColor" class="form-control" value="{{ $saleCar->Color ?? '' }}" disabled />
                    </div>

                    <div class="col-md-2">
                      <label class="form-label">เงินจอง</label>
                      <input id="summaryCashDeposit" class="form-control" value="{{ number_format($saleCar->CashDeposit, 2) ?? 0 }}" disabled />
                    </div>

                    <div class="col-md-2">
                      <label for="TradeinAddition" class="form-label">ราคารถเทิร์น</label>
                      <input class="form-control" type="text" name="TradeinAddition" id="TradeinAddition"
                        value="{{ $saleCar->TradeinAddition }}" />
                    </div>

                    <div class="col-md-3">
                      <label for="CarSalePrice" class="form-label">ราคาเงินสด</label>
                      <input class="form-control text-end" type="text" name="CarSalePrice" id="CarSalePrice"
                        value="{{ $saleCar->CarSalePrice }}" />
                    </div>

                    <div class="col-md-2">
                      <label for="MarkupPrice" class="form-label">บวกหัว</label>
                      <input class="form-control text-end" type="text" id="MarkupPrice" name="MarkupPrice"
                        value="{{ $saleCar->MarkupPrice }}" />
                    </div>
                    <div class="col-md-2">
                      <label for="Markup90" class="form-label">บวกหัว (90%)</label>
                      <input class="form-control text-end" type="text" id="Markup90" name="Markup90"
                        value="{{ $saleCar->Markup90 }}" readonly />
                    </div>

                    <div class="col-md-3">
                      <label for="CarSalePriceFinal" class="form-label">ราคาขายสุทธิ (รวมบวกหัว)</label>
                      <input class="form-control text-end" type="text" name="CarSalePriceFinal" id="CarSalePriceFinal"
                        value="{{ $saleCar->CarSalePriceFinal }}" readonly />
                    </div>

                    <div class="col-md-2">
                      <label for="AdditionFromCustomer" class="form-label">ลูกค้าจ่ายเพิ่ม</label>
                      <input class="form-control text-end" type="text" id="AdditionFromCustomer" name="AdditionFromCustomer"
                        value="{{ $saleCar->AdditionFromCustomer }}" />
                    </div>

                    <div class="col-md-2">
                      <label for="DownPayment" class="form-label">เงินดาวน์</label>
                      <input class="form-control text-end" type="text" name="DownPayment" id="DownPayment"
                        value="{{ $saleCar->DownPayment }}" />
                    </div>
                    <div class="col-md-1">
                      <label for="DownPaymentPercentage" class="form-label">%</label>
                      <input class="form-control text-end" type="text" name="DownPaymentPercentage" id="DownPaymentPercentage"
                        value="{{ $saleCar->DownPaymentPercentage }}" />
                    </div>
                    <div class="col-md-2">
                      <label for="DownPaymentDiscount" class="form-label">ส่วนลดเงินดาวน์</label>
                      <input class="form-control text-end" type="text" name="DownPaymentDiscount" id="DownPaymentDiscount"
                        value="{{ $saleCar->DownPaymentDiscount }}" />
                    </div>

                    <div class="row mt-6">
                      <div class="col-md-12 d-flex justify-content-end">
                        <div class="card shadow-sm p-3" style="min-width: 300px;">
                          <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold fs-4 text-dark">สรุปค่าใช้จ่ายวันออกรถ : </span>
                            <span id="TotalPaymentatDeliveryDisplay" class="fw-bold fs-4 text-primary">{{ $saleCar->TotalPaymentatDelivery }}</span>
                          </div>
                        </div>
                      </div>
                    </div>

                    <input type="hidden" id="TotalPaymentatDelivery" name="TotalPaymentatDelivery">

                  </div>
                </div>

                <div class="tab-pane fade" id="tab-condition-price" role="tabpanel">
                  <h4 class="pb-2 mb-3">เงื่อนไขการชำระเงิน</h4>

                  <div class="row g-2">

                    <table class="table table-bordered" id="">
                      <thead>
                        <tr>
                          <th class="text-center">ยอดที่เหลือ</th>
                          <th colspan="5" class="text-center">เลือกช่องทางการชำระเงิน</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td class="text-center" id="RemainingAmountDisplay">{{ $saleCar->TotalCashSupportUsed }}</td>
                          <td class="text-center"><input class="form-check-input" type="radio" name="paymentCondition" id="" />
                            <label class="form-check-label" for="">เงินสด</label>
                          </td>
                          <td class="text-center"><input class="form-check-input" type="radio" name="paymentCondition" id="creditCheck">
                            <label class="form-check-label ms-1" for="creditCheck">บัตรเครดิต</label>
                          </td>
                          <td class="text-center"><input class="form-check-input" type="radio" name="paymentCondition" id="moneyCheck" />
                            <label class="form-check-label" for="moneyCheck">เช็คธนาคาร</label>
                          </td>
                          <td class="text-center"><input class="form-check-input" type="radio" name="paymentCondition" id="cashCheck" />
                            <label class="form-check-label" for="cashCheck">เงินโอน</label>
                          </td>
                          <td class="text-center"><input class="form-check-input" type="radio" name="paymentCondition" id="financeCheck" />
                            <label class="form-check-label ms-1" for="financeCheck">ไฟแนนซ์</label>
                          </td>
                        </tr>
                      </tbody>
                    </table>

                    {{-- หรือ RemainingCashSupport --}}
                    <input type="hidden" id="TotalCashSupportUsed" name="TotalCashSupportUsed" value="0">

                    <div id="creditFields">
                      <div class="row">
                        <div class="col-md-3"></div>
                        <div class="col-md-3">
                          <label for="financeAmount" class="form-label">จำนวนเงิน</label>
                          <input class="form-control" type="text" id="financeAmount" placeholder="3000..." />
                        </div>
                        <div class="col-md-3">
                          <label for="financeAmount" class="form-label">ค่าธรรมเนียม</label>
                          <input class="form-control" type="text" id="financeAmount" placeholder="3000..." />
                        </div>
                      </div>
                    </div>

                    <div id="moneyFields">
                      <div class="row">
                        <div class="col-md-1"></div>
                        <div class="col-md-2">
                          <label for="financeAmount" class="form-label">สาขา</label>
                          <input class="form-control" type="text" id="financeAmount" placeholder="3000..." />
                        </div>
                        <div class="col-md-3">
                          <label for="financeAmount" class="form-label">เลขที่</label>
                          <input class="form-control" type="text" id="financeAmount" placeholder="3000..." />
                        </div>
                        <div class="col-md-2">
                          <label for="financeAmount" class="form-label">จำนวนเงิน</label>
                          <input class="form-control" type="text" id="financeAmount" placeholder="3000..." />
                        </div>
                        <div class="col-md-2">
                          <label for="financeAmount" class="form-label">วันที่โอน</label>
                          <input class="form-control" type="date" id="financeAmount" />
                        </div>
                      </div>
                    </div>

                    <div id="cashFields">
                      <div class="row">
                        <div class="col-md-1"></div>
                        <div class="col-md-2">
                          <label for="financeAmount" class="form-label">สาขา</label>
                          <input class="form-control" type="text" id="financeAmount" placeholder="3000..." />
                        </div>
                        <div class="col-md-3">
                          <label for="financeAmount" class="form-label">เลขที่</label>
                          <input class="form-control" type="text" id="financeAmount" placeholder="3000..." />
                        </div>
                        <div class="col-md-2">
                          <label for="financeAmount" class="form-label">จำนวนเงิน</label>
                          <input class="form-control" type="text" id="financeAmount" placeholder="3000..." />
                        </div>
                        <div class="col-md-2">
                          <label for="financeAmount" class="form-label">วันที่โอน</label>
                          <input class="form-control" type="date" id="financeAmount" />
                        </div>
                      </div>
                    </div>

                    <div id="financeFields">
                      <div class="row">
                        <div class="col-md-4">
                          <label for="financeAmount" class="form-label">ชื่อไฟแนนซ์</label>
                          <input class="form-control" type="text" id="financeAmount" placeholder="ttb..." />
                        </div>
                        <div class="col-md-3">
                          <label for="financeAmount" class="form-label">ยอดจัดไฟแนนซ์</label>
                          <input class="form-control" type="text" id="financeAmount" placeholder="3000..." />
                        </div>
                        <div class="col-md-2">
                          <label for="financeAmount" class="form-label">ดอกเบี้ย</label>
                          <input class="form-control" type="text" id="financeAmount" placeholder="2.5%..." />
                        </div>
                        <div class="col-md-3">
                          <label for="financePeriod" class="form-label">ระยะเวลาในการจัดไฟแนนซ์</label>
                          <select id="financePeriod" class="form-select" required>
                            <option selected>เลือก</option>
                            <option value="6">6</option>
                            <option value="12">12</option>
                          </select>
                        </div>
                        <div class="col-md-3 mt-4">
                          <label for="financeAmount" class="form-label">ค่างวด (กรณีไม่มี ALP)</label>
                          <input class="form-control" type="text" id="financeAmount" placeholder="3000..." />
                        </div>
                        <div class="col-md-3 mt-4">
                          <label for="financeAmount" class="form-label">ค่างวด (รวม ALP)</label>
                          <input class="form-control" type="text" id="financeAmount" placeholder="3000..." />
                        </div>
                        <div class="col-md-4 mt-4">
                          <label for="financeAmount" class="form-label">ยอดเงิน ALP ที่หักจากใบเสร็จดาวน์</label>
                          <input class="form-control" type="text" id="financeAmount" placeholder="3000..." />
                        </div>
                      </div>
                    </div>
                  </div>

                </div>
                <div class="mt-4 d-flex justify-content-end gap-2">
                  <button id="prevAccessory" class="btn btn-danger">ย้อนกลับ</button>
                  <button id="nextDate" class="btn btn-primary">ถัดไป</button>
                </div>
              </div>
            </div>

          </div>

          <!-- date -->
          <div class="tab-pane fade" id="tab-date" role="tabpanel">
            <div class="row">
              <div class="col-md-12">
                <div class="row g-6">
                  <h4 class="pb-2 mb-3 border-bottom">ข้อมูลวันสำคัญ</h4>
                  <div class="col-md-12">
                    <label for="BookingDate" class="form-label">วันที่จองรถ</label>
                    <input class="form-control" type="date" value="" id="html5-date-input" />
                  </div>
                  <div class="col-md-12">
                    <label for="AdminCheckedDate" class="form-label">วันที่แอดมินเช็ครายการ</label>
                    <input class="form-control" type="date" value="" id="html5-date-input" />
                  </div>
                  <div class="col-md-12">
                    <label for="CheckerCheckedDate" class="form-label">วันที่ฝ่ายตรวจสอบเช็ครายการ</label>
                    <input class="form-control" type="date" value="" id="html5-date-input" />
                  </div>
                  <div class="col-md-12">
                    <label for="KeyInDate" class="form-label">วันที่ส่งเอกสารสรุปการขาย</label>
                    <input class="form-control" type="date" value="" id="html5-date-input" />
                  </div>
                  <div class="col-md-12">
                    <label for="SMCheckedDate" class="form-label">วันที่ผู้จัดการขายอนุมัต</label>
                    <input class="form-control" type="date" value="" id="html5-date-input" />
                  </div>
                  <div class="col-md-12">
                    <label for="DeliveryDate" class="form-label">วันส่งมอบจริง (วันล้อหมุนจริง)</label>
                    <input class="form-control" type="date" value="" id="html5-date-input" />
                  </div>
                  <div class="col-md-12">
                    <label for="DeliveryInDMSDate" class="form-label">วันที่ส่งมอบในระบบ DMS</label>
                    <input class="form-control" type="date" value="" id="html5-date-input" />
                  </div>
                  <div class="col-md-12">
                    <label for="DeliveryInCKDate" class="form-label">วันที่ส่งมอบตามยอดชูเกียรติ </label>
                    <input class="form-control" type="date" value="" id="html5-date-input" />
                  </div>
                </div>
                <div class="mt-6 d-flex justify-content-end gap-2">
                  <button id="prevPrice" class="btn btn-danger">ย้อนกลับ</button>
                  <button id="nextApproved" class="btn btn-primary">ถัดไป</button>
                </div>
              </div>
            </div>
          </div>

          <!-- Approved -->
          <div class="tab-pane fade" id="tab-approved" role="tabpanel">
            <div class="row">
              <div class="col-md-12">
                <h4 class="pb-2 mb-3 border-bottom">ผู้อนุมัติ</h4>
                <div class="mb-6">
                  <label for="" class="form-label">ผู้เช็ครายการ (แอดมินขาย)</label>
                  <select id="" name="" class="form-select" required>
                    <option selected>เลือกผู้เช็ค</option>
                    <option value="นาย">A</option>
                    <option value="นาง">B</option>
                    <option value="นางสาว">C</option>
                  </select>
                </div>

                <div class="mb-6">
                  <label for="" class="form-label">ผู้ตรวจสอบรายการ (IA)</label>
                  <select id="" name="" class="form-select" required>
                    <option selected>เลือกผู้ตรวจสอบ</option>
                    <option value="นาย">A</option>
                    <option value="นาง">B</option>
                    <option value="นางสาว">C</option>
                  </select>
                </div>

                <div class="mb-6">
                  <label for="" class="form-label">ผู้อนุมัติรายการ (ผู้จัดการขาย)</label>
                  <select id="" name="" class="form-select" required>
                    <option selected>เลือกผู้อนุมัติ</option>
                    <option value="นาย">A</option>
                    <option value="นาง">B</option>
                    <option value="นางสาว">C</option>
                  </select>
                </div>

                <div class="mb-6">
                  <label for="" class="form-label">ผู้อนุมัติการขายกรณีเกินจากงบ</label>
                  <select id="" name="" class="form-select" required>
                    <option selected>เลือกผู้อนุมัติ</option>
                    <option value="นาย">A</option>
                    <option value="นาง">B</option>
                    <option value="นางสาว">C</option>
                  </select>
                </div>

                <div class="mb-6">
                  <label for="" class="form-label">GM อนุมัติกรณีงบเกิน (N)</label>
                  <select id="" name="" class="form-select" required>
                    <option selected>เลือกผู้อนุมัติ</option>
                    <option value="นาย">A</option>
                    <option value="นาง">B</option>
                    <option value="นางสาว">C</option>
                  </select>
                </div>

                <!-- <div class="mt-6 d-flex justify-content-end gap-2">
                  <button id="prevAccessory" class="btn btn-danger">ย้อนกลับ</button>
                  <button id="nextPrice" class="btn btn-primary">บันทึก</button>
                </div> -->
                <div class="d-flex justify-content-end gap-2">
                  <button id="prevDate" class="btn btn-danger">ย้อนกลับ</button>
                  <button type="button" class="btn btn-primary btnUpdatePurchase">บันทึก</button>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>


    </form>
  </div>
</div>

@include('purchase-order.accessory-gift.accessory')
@include('purchase-order.accessory-gift.gift')

<style>
  .swal2-container {
    z-index: 20000 !important;
  }

  #TotalPaymentatDelivery {
    padding: 5px 12px;
    border-radius: 6px;
    min-width: 120px;
    text-align: right;
    display: inline-block;
  }

  .card {
    border-radius: 10px;
    background-color: #e7f1ff;
  }
</style>

@endsection