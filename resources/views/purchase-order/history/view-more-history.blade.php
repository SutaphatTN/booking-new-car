<div class="modal fade viewPurchaseHistory" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--view">

      {{-- Header --}}
      <div class="modal-header mf-header mf-header--view px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-history fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title" id="viewPurchaseHistoryLabel">ประวัติคำสั่งซื้อ</h6>
            <small class="text-white mf-hd-sub">Purchase Order History</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">
        <div class="row g-3">

          {{-- ── LEFT COLUMN ── --}}
          <div class="col-md-6 d-flex flex-column gap-3">

            {{-- Section : ข้อมูลลูกค้า --}}
            <div class="mf-section mb-0">
              <div class="mf-section-hd">
                <div class="mf-section-icon sky"><i class="bx bx-user"></i></div>
                <span class="mf-section-title">ข้อมูลลูกค้า</span>
              </div>
              <div class="mf-section-body">
                <div class="mf-info-row">
                  <span class="mf-info-label">วันที่จอง</span>
                  <span class="mf-info-val">{{ $saleCar->format_booking_date ?? '-' }}</span>
                </div>
                <div class="mf-info-row">
                  <span class="mf-info-label">ชื่อลูกค้า</span>
                  <span class="mf-info-val">
                    {{ $saleCar->customer?->prefix->Name_TH ?? '' }}
                    {{ $saleCar->customer?->FirstName ?? '' }}
                    {{ $saleCar->customer?->LastName ?? '' }}
                  </span>
                </div>
                <div class="mf-info-row">
                  <span class="mf-info-label">ชื่อฝ่ายขาย</span>
                  <span class="mf-info-val">{{ $saleCar->saleUser?->name ?? '-' }}</span>
                </div>
                <div class="mf-info-row">
                  <span class="mf-info-label">ที่อยู่ปัจจุบัน</span>
                  <span class="mf-info-val">{{ $saleCar->customer->currentAddress?->full_address ?? '-' }}</span>
                </div>
                <div class="mf-info-row">
                  <span class="mf-info-label">ที่อยู่สำหรับส่งเอกสาร</span>
                  <span class="mf-info-val">{{ $saleCar->customer->documentAddress?->full_address ?? '-' }}</span>
                </div>
                <div class="mf-info-row">
                  <span class="mf-info-label">เบอร์มือถือ</span>
                  <span class="mf-info-val">{{ $saleCar->customer?->formatted_mobile }}</span>
                </div>
              </div>
            </div>

            {{-- Section : ข้อมูลการขาย --}}
            <div class="mf-section mb-0">
              <div class="mf-section-hd">
                <div class="mf-section-icon indigo"><i class="bx bx-car"></i></div>
                <span class="mf-section-title">ข้อมูลการขาย</span>
              </div>
              <div class="mf-section-body">
                <div class="mf-info-row">
                  <span class="mf-info-label">รุ่นรถหลัก</span>
                  <span class="mf-info-val">{{ $saleCar->model?->Name_TH }}</span>
                </div>
                <div class="mf-info-row">
                  <span class="mf-info-label">รุ่นรถย่อย</span>
                  <span class="mf-info-val">
                    {{ !empty($saleCar->subModel)
                        ? ($saleCar->subModel->detail
                            ? $saleCar->subModel->detail . ' - ' . $saleCar->subModel->name
                            : $saleCar->subModel->name)
                        : '-' }}
                  </span>
                </div>
                <div class="mf-info-row">
                  <span class="mf-info-label">Vin-Number</span>
                  <span class="mf-info-val">{{ $saleCar->carOrder?->vin_number ?? '-' }}</span>
                </div>
                @if (auth()->user()->brand == 2)
                  <div class="mf-info-row">
                    <span class="mf-info-label">สี</span>
                    <span class="mf-info-val">{{ $saleCar->gwmColor->name ?? '-' }}</span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">สีภายใน</span>
                    <span class="mf-info-val">{{ $saleCar->interiorColor->name ?? '-' }}</span>
                  </div>
                @elseif (auth()->user()->brand == 3)
                  <div class="mf-info-row">
                    <span class="mf-info-label">สี</span>
                    <span class="mf-info-val">{{ $saleCar->gwmColor->name ?? '-' }}</span>
                  </div>
                @else
                  <div class="mf-info-row">
                    <span class="mf-info-label">Option</span>
                    <span class="mf-info-val">{{ $saleCar->option ?? '-' }}</span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">สี</span>
                    <span class="mf-info-val">{{ $saleCar->Color ?? '-' }}</span>
                  </div>
                @endif
                <div class="mf-info-row">
                  <span class="mf-info-label">ปี</span>
                  <span class="mf-info-val">{{ $saleCar->Year ?? '-' }}</span>
                </div>
                <div class="mf-info-row">
                  <span class="mf-info-label">ราคา</span>
                  <span class="mf-info-val">
                    {{ $saleCar->carOrder?->car_DNP !== null ? number_format($saleCar->carOrder->car_DNP, 2) : '-' }} บาท
                  </span>
                </div>
                <div class="mf-info-row">
                  <span class="mf-info-label">เงินจอง</span>
                  <span class="mf-info-val">
                    {{ $saleCar->CashDeposit !== null ? number_format($saleCar->CashDeposit, 2) : '-' }} บาท
                  </span>
                </div>
                <div class="mf-info-row">
                  <span class="mf-info-label">รถเทิร์น</span>
                  <span class="mf-info-val">
                    {{ $saleCar->turnCar?->cost_turn !== null ? number_format($saleCar->turnCar->cost_turn, 2) : '-' }} บาท
                  </span>
                </div>
                <div class="mf-info-row">
                  <span class="mf-info-label">ลูกค้าจ่ายเพิ่ม</span>
                  <span class="mf-info-val">
                    {{ $saleCar->total_extra_used !== null ? number_format($saleCar->total_extra_used, 2) : '-' }} บาท
                  </span>
                </div>
                <div class="mf-info-row">
                  <span class="mf-info-label">จังหวัดที่ขึ้นทะเบียน</span>
                  <span class="mf-info-val">{{ $saleCar->provinces?->name ?? '-' }}</span>
                </div>
                <div class="mf-info-row">
                  <span class="mf-info-label">ผู้แนะนำ</span>
                  <span class="mf-info-val">{{ $saleCar->customerReferrer?->formatted_id_number ?? '-' }}</span>
                </div>
                <div class="mf-info-row">
                  <span class="mf-info-label">ยอดเงินค่าแนะนำ</span>
                  <span class="mf-info-val">
                    {{ $saleCar->ReferrerAmount !== null ? number_format($saleCar->ReferrerAmount, 2) : '-' }} บาท
                  </span>
                </div>
              </div>
            </div>

            {{-- Section : ข้อมูลการเงิน --}}
            <div class="mf-section mb-0">
              <div class="mf-section-hd">
                <div class="mf-section-icon amber"><i class="bx bx-money"></i></div>
                <span class="mf-section-title">ข้อมูลการเงิน</span>
              </div>
              <div class="mf-section-body">
                @if ($saleCar->payment_mode === 'finance')
                  <div class="mf-info-row">
                    <span class="mf-info-label">เงินดาวน์</span>
                    <span class="mf-info-val">
                      {{ $saleCar->DownPayment !== null ? number_format($saleCar->DownPayment, 2) : '-' }} บาท
                    </span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">เปอร์เซ็นต์เงินดาวน์</span>
                    <span class="mf-info-val">{{ $saleCar->DownPaymentPercentage }} %</span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">ส่วนลดเงินดาวน์</span>
                    <span class="mf-info-val">
                      {{ $saleCar->DownPaymentDiscount !== null ? number_format($saleCar->DownPaymentDiscount, 2) : '-' }} บาท
                    </span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">ส่วนลดราคารถ</span>
                    <span class="mf-info-val">
                      {{ $saleCar->discount !== null ? number_format($saleCar->discount, 2) : '-' }} บาท
                    </span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">ค่าใช้จ่ายอื่นๆ</span>
                    <span class="mf-info-val">
                      {{ $saleCar->other_cost_fi !== null ? number_format($saleCar->other_cost_fi, 2) : '-' }} บาท
                    </span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">หมายเหตุ ค่าใช้จ่ายอื่นๆ</span>
                    <span class="mf-info-val">{{ $saleCar->reason_other_cost_fi ?? '-' }}</span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">Vat ซื้อเพิ่ม</span>
                    <span class="mf-info-val">
                      {{ $saleCar->AccessoryExtraVat !== null ? number_format($saleCar->AccessoryExtraVat, 2) : '-' }} บาท
                    </span>
                  </div>
                  <div class="mf-sub-heading">สรุปไฟแนนซ์</div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">สรุปค่าใช้จ่ายวันออกรถ</span>
                    <span class="mf-info-val">
                      {{ $saleCar->TotalPaymentatDeliveryCar !== null ? number_format($saleCar->TotalPaymentatDeliveryCar, 2) : '-' }} บาท
                    </span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">Po Number</span>
                    <span class="mf-info-val">{{ $saleCar->remainingPayment?->po_number ?? '-' }}</span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">ไฟแนนซ์</span>
                    <span class="mf-info-val">{{ $saleCar->remainingPayment->financeInfo?->FinanceCompany }}</span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">ยอดจัดไฟแนนซ์</span>
                    <span class="mf-info-val">
                      {{ $saleCar->balanceFinance !== null ? number_format($saleCar->balanceFinance, 2) : '-' }} บาท
                    </span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">ดอกเบี้ย</span>
                    <span class="mf-info-val">{{ $saleCar->remainingPayment?->interest }} %</span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">งวดผ่อน</span>
                    <span class="mf-info-val">{{ $saleCar->remainingPayment?->period }} งวด</span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">ค่างวด (กรณีไม่มี ALP)</span>
                    <span class="mf-info-val">
                      {{ $saleCar->remainingPayment?->alp !== null ? number_format($saleCar->remainingPayment->alp, 2) : '-' }} บาท
                    </span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">ค่างวด (รวม ALP)</span>
                    <span class="mf-info-val">
                      {{ $saleCar->remainingPayment?->including_alp !== null ? number_format($saleCar->remainingPayment->including_alp, 2) : '-' }} บาท
                    </span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">ยอดเงิน ALP ที่หักจากใบเสร็จดาวน์</span>
                    <span class="mf-info-val">
                      {{ $saleCar->remainingPayment?->total_alp !== null ? number_format($saleCar->remainingPayment->total_alp, 2) : '-' }} บาท
                    </span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">ดอกเบี้ยคอม</span>
                    <span class="mf-info-val">{{ 'C' . $saleCar->remainingPayment?->type_com }}</span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">ยอดเงินค่าคอม</span>
                    <span class="mf-info-val">
                      {{ $saleCar->remainingPayment?->total_com !== null ? number_format($saleCar->remainingPayment->total_com, 2) : '-' }} บาท
                    </span>
                  </div>
                @else
                  <div class="mf-info-row">
                    <span class="mf-info-label">ส่วนลด</span>
                    <span class="mf-info-val">
                      {{ $saleCar->PaymentDiscount !== null ? number_format($saleCar->PaymentDiscount, 2) : '-' }} บาท
                    </span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">ค่าใช้จ่ายอื่นๆ</span>
                    <span class="mf-info-val">
                      {{ $saleCar->other_cost !== null ? number_format($saleCar->other_cost, 2) : '-' }} บาท
                    </span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">หมายเหตุ ค่าใช้จ่ายอื่นๆ</span>
                    <span class="mf-info-val">{{ $saleCar->reason_other_cost ?? '-' }}</span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">คงเหลือ</span>
                    <span class="mf-info-val">
                      {{ $saleCar->balance !== null ? number_format($saleCar->balance, 2) : '-' }} บาท
                    </span>
                  </div>
                @endif
              </div>
            </div>

          </div>{{-- end left col --}}

          {{-- ── RIGHT COLUMN ── --}}
          <div class="col-md-6 d-flex flex-column gap-3">

            {{-- Section : อุปกรณ์ตกแต่ง (แถม) --}}
            @php $giftAccessories = $saleCar->accessories->where('pivot.type', 'gift'); @endphp
            <div class="mf-section mb-0">
              <div class="mf-section-hd">
                <div class="mf-section-icon emerald"><i class="bx bx-gift"></i></div>
                <span class="mf-section-title">รายละเอียดอุปกรณ์ตกแต่ง (แถม)</span>
              </div>
              <div class="mf-section-body">
                @if ($giftAccessories->isNotEmpty())
                  <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0" style="font-size:.82rem;">
                      <thead class="table-light">
                        <tr>
                          <th class="text-center">#</th>
                          <th class="text-center">รหัส</th>
                          <th>รายละเอียด</th>
                          <th class="text-center">ประเภท</th>
                          <th class="text-end">ราคา</th>
                          <th class="text-end">ค่าคอม</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach ($giftAccessories as $index => $giftAccessories)
                          <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="text-center">{{ $giftAccessories->accessory_id ?? '-' }}</td>
                            <td>{{ $giftAccessories->detail ?? '-' }}</td>
                            <td class="text-center">{{ $giftAccessories->pivot->price_type ?? '-' }}</td>
                            <td class="text-end">{{ number_format($giftAccessories->pivot->price, 2) }}</td>
                            <td class="text-end">{{ number_format($giftAccessories->pivot->commission, 2) }}</td>
                          </tr>
                        @endforeach
                        <tr class="table-light fw-bold">
                          <td colspan="4" class="text-center">รวมทั้งหมด</td>
                          <td class="text-end">{{ number_format($saleCar->TotalAccessoryGift ?? 0, 2) }}</td>
                          <td class="text-end">{{ number_format($saleCar->AccessoryGiftCom ?? 0, 2) }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                @else
                  <p class="text-center text-muted mb-0" style="font-size:.85rem;">— ไม่มีข้อมูล —</p>
                @endif
              </div>
            </div>

            {{-- Section : รายการซื้อเพิ่ม --}}
            @php $extraAccessories = $saleCar->accessories->where('pivot.type', 'extra'); @endphp
            <div class="mf-section mb-0">
              <div class="mf-section-hd">
                <div class="mf-section-icon sky"><i class="bx bx-shopping-bag"></i></div>
                <span class="mf-section-title">รายการซื้อเพิ่ม</span>
              </div>
              <div class="mf-section-body">
                @if ($extraAccessories->isNotEmpty())
                  <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0" style="font-size:.82rem;">
                      <thead class="table-light">
                        <tr>
                          <th class="text-center">#</th>
                          <th class="text-center">รหัส</th>
                          <th>รายละเอียด</th>
                          <th class="text-center">ประเภท</th>
                          <th class="text-end">ราคา</th>
                          <th class="text-end">ค่าคอม</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach ($extraAccessories as $index => $extraAccessories)
                          <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="text-center">{{ $extraAccessories->accessory_id ?? '-' }}</td>
                            <td>{{ $extraAccessories->detail ?? '-' }}</td>
                            <td class="text-center">{{ $extraAccessories->pivot->price_type ?? '-' }}</td>
                            <td class="text-end">{{ number_format($extraAccessories->pivot->price, 2) }}</td>
                            <td class="text-end">{{ number_format($extraAccessories->pivot->commission, 2) }}</td>
                          </tr>
                        @endforeach
                        <tr class="table-light fw-bold">
                          <td colspan="4" class="text-center">รวมทั้งหมด</td>
                          <td class="text-end">{{ number_format($saleCar->TotalAccessoryExtra ?? 0, 2) }}</td>
                          <td class="text-end">{{ number_format($saleCar->AccessoryExtraCom ?? 0, 2) }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                @else
                  <p class="text-center text-muted mb-0" style="font-size:.85rem;">— ไม่มีข้อมูล —</p>
                @endif
              </div>
            </div>

            {{-- Section : แคมเปญ --}}
            @php
              $totalCam    = $saleCar->TotalSaleCampaign;
              $gift        = $saleCar->TotalAccessoryGift;
              $refA        = $saleCar->ReferrerAmount;
              $vatGift     = $saleCar->AccessoryGiftVat;
              $kickback    = $saleCar->kickback;
              $markUp      = $saleCar->Markup90;
              $totalCamMark = $totalCam + $markUp + $kickback;
              $downPay     = $saleCar->DownPaymentDiscount;
              $disC        = $saleCar->discount;
              $totalUseFi  = $downPay + $gift + $refA + $vatGift + $disC;
              $totalBalanceFi  = $totalCamMark - $totalUseFi;
              $totalBalanceFi2 = $totalBalanceFi * 2;
              $discount        = $saleCar->PaymentDiscount;
              $totalUseCash    = $discount + $gift + $refA;
              $totalBalanceCash  = $totalCam - $totalUseCash;
              $totalBalanceCash2 = $totalBalanceCash * 2;
            @endphp
            <div class="mf-section mb-0">
              <div class="mf-section-hd">
                <div class="mf-section-icon emerald" style="background:#d1fae5;color:#059669;">
                  <i class="bx bx-purchase-tag"></i>
                </div>
                <span class="mf-section-title">แคมเปญ</span>
              </div>
              <div class="mf-section-body">
                <div class="mf-info-row">
                  <span class="mf-info-label">ข้อมูลแคมเปญ</span>
                  <span class="mf-info-val" id="viewMoreCampaignText" data-campaign="{{ $campaignText }}">
                    {{ $campaignText ?: '-' }}
                  </span>
                </div>
                @if ($saleCar->payment_mode === 'finance')
                  <div class="mf-info-row">
                    <span class="mf-info-label">รวมงบแคมเปญ</span>
                    <span class="mf-info-val">{{ $saleCar->TotalSaleCampaign !== null ? number_format($saleCar->TotalSaleCampaign, 2) : '-' }} บาท</span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">บวกหัว (90%)</span>
                    <span class="mf-info-val">{{ $saleCar->Markup90 !== null ? number_format($saleCar->Markup90, 2) : '-' }} บาท</span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">Kick Back</span>
                    <span class="mf-info-val">{{ $saleCar->kickback !== null ? number_format($saleCar->kickback, 2) : '-' }} บาท</span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">ยอดรวมแคมเปญ (รวมบวกหัว 90%)</span>
                    <span class="mf-info-val">{{ $totalCamMark !== null ? number_format($totalCamMark, 2) : '-' }} บาท</span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">ส่วนลดเงินดาวน์</span>
                    <span class="mf-info-val">{{ $saleCar->DownPaymentDiscount !== null ? number_format($saleCar->DownPaymentDiscount, 2) : '-' }} บาท</span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">ส่วนลดราคารถ</span>
                    <span class="mf-info-val">{{ $saleCar->discount !== null ? number_format($saleCar->discount, 2) : '-' }} บาท</span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">ส่วนต่างของแถม</span>
                    <span class="mf-info-val">{{ $saleCar->TotalAccessoryGift !== null ? number_format($saleCar->TotalAccessoryGift, 2) : '-' }} บาท</span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">Vat ของแถม</span>
                    <span class="mf-info-val">{{ $saleCar->AccessoryGiftVat !== null ? number_format($saleCar->AccessoryGiftVat, 2) : '-' }} บาท</span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">ค่าแนะนำ</span>
                    <span class="mf-info-val">{{ $saleCar->ReferrerAmount !== null ? number_format($saleCar->ReferrerAmount, 2) : '-' }} บาท</span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">ยอดรวมรายการที่ใช้</span>
                    <span class="mf-info-val">{{ $totalUseFi !== null ? number_format($totalUseFi, 2) : '-' }} บาท</span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">คงเหลือ</span>
                    <span class="mf-info-val">{{ $totalBalanceFi2 !== null ? number_format($totalBalanceFi2, 2) : '-' }} บาท</span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">คงเหลือ (แบ่ง 2 ส่วน)</span>
                    <span class="mf-info-val">{{ $totalBalanceFi !== null ? number_format($totalBalanceFi, 2) : '-' }} บาท</span>
                  </div>
                @else
                  <div class="mf-info-row">
                    <span class="mf-info-label">รวมงบแคมเปญ</span>
                    <span class="mf-info-val">{{ $saleCar->TotalSaleCampaign !== null ? number_format($saleCar->TotalSaleCampaign, 2) : '-' }} บาท</span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">ส่วนลด</span>
                    <span class="mf-info-val">{{ $saleCar->PaymentDiscount !== null ? number_format($saleCar->PaymentDiscount, 2) : '-' }} บาท</span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">ส่วนต่างของแถม</span>
                    <span class="mf-info-val">{{ $saleCar->TotalAccessoryGift !== null ? number_format($saleCar->TotalAccessoryGift, 2) : '-' }} บาท</span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">ค่าแนะนำ</span>
                    <span class="mf-info-val">{{ $saleCar->ReferrerAmount !== null ? number_format($saleCar->ReferrerAmount, 2) : '-' }} บาท</span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">ยอดรวมรายการที่ใช้</span>
                    <span class="mf-info-val">{{ $totalUseCash !== null ? number_format($totalUseCash, 2) : '-' }} บาท</span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">คงเหลือ</span>
                    <span class="mf-info-val">{{ $totalBalanceCash2 !== null ? number_format($totalBalanceCash2, 2) : '-' }} บาท</span>
                  </div>
                  <div class="mf-info-row">
                    <span class="mf-info-label">คงเหลือ (แบ่ง 2 ส่วน)</span>
                    <span class="mf-info-val">{{ $totalBalanceCash !== null ? number_format($totalBalanceCash, 2) : '-' }} บาท</span>
                  </div>
                @endif
              </div>
            </div>

            {{-- Section : ข้อมูลวันส่งมอบ --}}
            <div class="mf-section mb-0">
              <div class="mf-section-hd">
                <div class="mf-section-icon sky"><i class="bx bx-calendar-check"></i></div>
                <span class="mf-section-title">ข้อมูลวันส่งมอบ</span>
              </div>
              <div class="mf-section-body">
                <div class="mf-info-row">
                  <span class="mf-info-label">วันที่ส่งเอกสารสรุปการขาย</span>
                  <span class="mf-info-val">{{ $saleCar->format_key_date ?? '-' }}</span>
                </div>
                <div class="mf-info-row">
                  <span class="mf-info-label">วันส่งมอบจริง (วันที่แจ้งประกัน)</span>
                  <span class="mf-info-val">{{ $saleCar->format_delivery_date ?? '-' }}</span>
                </div>
                <div class="mf-info-row">
                  <span class="mf-info-label">วันที่ส่งมอบของบริษัท</span>
                  <span class="mf-info-val">{{ $saleCar->format_dms_date ?? '-' }}</span>
                </div>
                <div class="mf-info-row">
                  <span class="mf-info-label">วันที่ส่งมอบของฝ่ายขาย</span>
                  <span class="mf-info-val">{{ $saleCar->format_ck_date ?? '-' }}</span>
                </div>
                <div class="mf-info-row">
                  <span class="mf-info-label">ประมาณการส่งมอบ</span>
                  <span class="mf-info-val">{{ $saleCar->format_delivery_estimate_date ?? '-' }}</span>
                </div>
              </div>
            </div>

            {{-- Section : ผู้อนุมัติ --}}
            <div class="mf-section mb-0">
              <div class="mf-section-hd">
                <div class="mf-section-icon indigo"><i class="bx bx-check-shield"></i></div>
                <span class="mf-section-title">ผู้อนุมัติ</span>
              </div>
              <div class="mf-section-body">
                <div class="mf-info-row">
                  <span class="mf-info-label">ผู้เช็ครายการ (แอดมินขาย)</span>
                  <span class="mf-info-val">
                    {{ ($saleCar->AdminSignature ?? null) == 1 ? 'เช็ครายการเรียบร้อยแล้ว' : '-' }}
                  </span>
                </div>
                <div class="mf-info-row">
                  <span class="mf-info-label">วันที่แอดมินเช็ครายการ</span>
                  <span class="mf-info-val">{{ $saleCar->format_admin_check_date ?? '-' }}</span>
                </div>
                <div class="mf-info-row">
                  <span class="mf-info-label">ผู้ตรวจสอบรายการ (IA)</span>
                  <span class="mf-info-val">
                    {{ ($saleCar->CheckerID ?? null) == 1 ? 'เช็ครายการเรียบร้อยแล้ว' : '-' }}
                  </span>
                </div>
                <div class="mf-info-row">
                  <span class="mf-info-label">วันที่ฝ่ายตรวจสอบเช็ครายการ</span>
                  <span class="mf-info-val">{{ $saleCar->format_checker_date ?? '-' }}</span>
                </div>
                <div class="mf-info-row">
                  <span class="mf-info-label">ผู้จัดการ อนุมัติการขาย</span>
                  <span class="mf-info-val">
                    {{ ($saleCar->SMSignature ?? null) == 1 ? 'อนุมัติเรียบร้อยแล้ว' : '-' }}
                  </span>
                </div>
                <div class="mf-info-row">
                  <span class="mf-info-label">วันที่ผู้จัดการขายอนุมัติ</span>
                  <span class="mf-info-val">{{ $saleCar->format_sm_date ?? '-' }}</span>
                </div>
                <div class="mf-info-row">
                  <span class="mf-info-label">ผู้จัดการ อนุมัติกรณีงบเกิน</span>
                  <span class="mf-info-val">
                    {{ ($saleCar->ApprovalSignature ?? null) == 1 ? 'อนุมัติเรียบร้อยแล้ว' : '-' }}
                  </span>
                </div>
                <div class="mf-info-row">
                  <span class="mf-info-label">วันที่ผู้จัดการอนุมัติการขาย</span>
                  <span class="mf-info-val">{{ $saleCar->format_approval_date ?? '-' }}</span>
                </div>
                <div class="mf-info-row">
                  <span class="mf-info-label">GM อนุมัติกรณีงบเกิน (N)</span>
                  <span class="mf-info-val">
                    {{ ($saleCar->GMApprovalSignature ?? null) == 1 ? 'อนุมัติเรียบร้อยแล้ว' : '-' }}
                  </span>
                </div>
                <div class="mf-info-row">
                  <span class="mf-info-label">วันที่ GM อนุมัติกรณีงบเกิน</span>
                  <span class="mf-info-val">{{ $saleCar->format_gm_date ?? '-' }}</span>
                </div>
              </div>
            </div>

            {{-- Section : สถานะ --}}
            <div class="mf-section mb-0">
              <div class="mf-section-hd">
                <div class="mf-section-icon rose"><i class="bx bx-info-circle"></i></div>
                <span class="mf-section-title">สถานะ</span>
              </div>
              <div class="mf-section-body">
                <div class="mf-info-row">
                  <span class="mf-info-label">สถานะ</span>
                  <span class="mf-info-val">{{ $saleCar->conStatus->name ?? '-' }}</span>
                </div>
              </div>
            </div>

          </div>{{-- end right col --}}

        </div>{{-- end row --}}
      </div>{{-- end mf-body --}}

    </div>
  </div>
</div>
