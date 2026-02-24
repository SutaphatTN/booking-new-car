<div class="modal fade viewPurchaseHistory" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h4 class="modal-title mb-2" id="viewPurchaseHistoryLabel">ประวัติคำสั่งซื้อ</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6 border-end pe-3">
            <h5 class="border-bottom pb-2 mb-3">ข้อมูลลูกค้า</h5>
            <div class="d-flex justify-content-between mb-2">
              <strong>วันที่จอง :</strong>
              <span>{{ $saleCar->format_booking_date ?? '-' }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>ชื่อลูกค้า :</strong>
              <span>{{ $saleCar->customer?->prefix->Name_TH ?? '' }} {{ $saleCar->customer?->FirstName ?? '' }} {{ $saleCar->customer?->LastName ?? '' }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>ที่อยู่ปัจจุบัน :</strong>
              <span style="width:60%; text-align:right;">{{ $saleCar->customer->currentAddress?->full_address ?? '-' }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>ที่อยู่สำหรับส่งเอกสาร :</strong>
              <span style="width:60%; text-align:right;">{{ $saleCar->customer->documentAddress?->full_address ?? '-' }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>เบอร์มือถือ :</strong>
              <span>{{ $saleCar->customer?->formatted_mobile }}</span>
            </div>

            <h5 class="border-bottom pb-2 mb-3">ข้อมูลการขาย</h5>
            <div class="d-flex justify-content-between mb-2">
              <strong>รุ่นรถหลัก :</strong>
              <span>{{ $saleCar->model?->Name_TH }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>รุ่นรถย่อย :</strong>
              <span>{{ $saleCar->subModel?->detail }} - {{ $saleCar->subModel?->name }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>แบบ :</strong>
              <span>{{ $saleCar->option ?? '-' }}</span>
            </div>
            @if(auth()->user()->brand == 2)
            <div class="d-flex justify-content-between mb-2">
              <strong>สี :</strong>
              <span>{{ $saleCar->gwmColor->name ?? '-' }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>สีภายใน :</strong>
              <span>{{ $saleCar->interiorColor->name ?? '-' }}</span>
            </div>
            @else
            <div class="d-flex justify-content-between mb-2">
              <strong>สี :</strong>
              <span>{{ $saleCar->Color ?? '-' }}</span>
            </div>
            @endif

            <div class="d-flex justify-content-between mb-2">
              <strong>ปี :</strong>
              <span>{{ $saleCar->Year ?? '-' }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>ราคา :</strong>
              <span>{{ $saleCar->carOrder?->car_DNP !== null ? number_format($saleCar->carOrder->car_DNP, 2) : '-' }} บาท</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>เงินจอง :</strong>
              <span>{{ $saleCar->CashDeposit !== null ? number_format($saleCar->CashDeposit, 2) : '-' }} บาท</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>รถเทิร์น :</strong>
              <span>{{ $saleCar->turnCar?->cost_turn !== null ? number_format($saleCar->turnCar->cost_turn, 2) : '-' }} บาท</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>ลูกค้าจ่ายเพิ่ม :</strong>
              <span>{{ $saleCar->total_extra_used !== null ? number_format($saleCar->total_extra_used, 2) : '-' }} บาท</span>
            </div>

            @if ($saleCar->payment_mode === 'finance')
            <div class="d-flex justify-content-between mb-2">
              <strong>เงินดาวน์ :</strong>
              <span>{{ $saleCar->DownPayment !== null ? number_format($saleCar->DownPayment, 2) : '-' }} บาท</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>เปอร์เซ็นต์เงินดาวน์ :</strong>
              <span>{{ $saleCar->DownPaymentPercentage }} %</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>ส่วนลดเงินดาวน์ :</strong>
              <span>{{ $saleCar->downPaymentDiscount !== null ? number_format($saleCar->downPaymentDiscount, 2) : '-' }} บาท</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>ส่วนลด :</strong>
              <span>{{ $saleCar->discount !== null ? number_format($saleCar->discount, 2) : '-' }} บาท</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>ค่าใช้จ่ายอื่นๆ :</strong>
              <span>{{ $saleCar->other_cost_fi !== null ? number_format($saleCar->other_cost_fi, 2) : '-' }} บาท</span>
            </div>

            <h5 class="pb-2 mb-3"></h5>
            <div class="d-flex justify-content-between mb-2">
              <strong>สรุปค่าใช้จ่ายวันออกรถ :</strong>
              <span>{{ $saleCar->TotalPaymentatDeliveryCar !== null ? number_format($saleCar->TotalPaymentatDeliveryCar, 2) : '-' }} บาท</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>Po Number :</strong>
              <span>{{ $saleCar->remainingPayment?->po_number ?? '-' }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>ไฟแนนซ์ :</strong>
              <span>{{ $saleCar->remainingPayment->financeInfo?->FinanceCompany }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>ยอดจัดไฟแนนซ์ :</strong>
              <span>{{ $saleCar->balanceFinance !== null ? number_format($saleCar->balanceFinance, 2) : '-' }} บาท</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>ดอกเบี้ย :</strong>
              <span>{{ $saleCar->remainingPayment?->interest }} %</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>งวดผ่อน :</strong>
              <span>{{ $saleCar->remainingPayment?->period }} งวด</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>ค่างวด (กรณีไม่มี ALP) :</strong>
              <span>{{ $saleCar->remainingPayment?->alp !== null ? number_format($saleCar->remainingPayment->alp, 2) : '-' }} บาท</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>ค่างวด (รวม ALP) :</strong>
              <span>{{ $saleCar->remainingPayment?->including_alp !== null ? number_format($saleCar->remainingPayment->including_alp, 2) : '-' }} บาท</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>ยอดเงิน ALP ที่หักจากใบเสร็จดาวน์ :</strong>
              <span>{{ $saleCar->remainingPayment?->total_alp !== null ? number_format($saleCar->remainingPayment->total_alp, 2) : '-' }} บาท</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>ดอกเบี้ยคอม :</strong>
              <span>{{ 'C' . $saleCar->remainingPayment?->type_com }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>ยอดเงินค่าคอม :</strong>
              <span>{{ $saleCar->remainingPayment?->total_com !== null ? number_format($saleCar->remainingPayment->total_com, 2) : '-' }} บาท</span>
            </div>
            @else
            <div class="d-flex justify-content-between mb-2">
              <strong>ส่วนลด :</strong>
              <span>{{ $saleCar->PaymentDiscount !== null ? number_format($saleCar->PaymentDiscount, 2) : '-' }} บาท</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>ค่าใช้จ่ายอื่นๆ :</strong>
              <span>{{ $saleCar->other_cost !== null ? number_format($saleCar->other_cost, 2) : '-' }} บาท</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>คงเหลือ :</strong>
              <span>{{ $saleCar->balance !== null ? number_format($saleCar->balance, 2) : '-' }} บาท</span>
            </div>
            @endif

            <h5 class="border-bottom pb-2 mb-3">จังหวัดที่ขึ้นทะเบียน</h5>
            <div class="d-flex justify-content-between mb-2">
              <strong>จังหวัดที่ขึ้นทะเบียน :</strong>
              <span>{{ $saleCar->provinces?->name ?? '-' }}</span>
            </div>

            <h5 class="border-bottom pb-2 mb-3">แนะนำ</h5>
            <div class="d-flex justify-content-between mb-2">
              <strong>ผู้แนะนำ :</strong>
              <span>{{ $saleCar->customerReferrer?->formatted_id_number ?? '' }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>ยอดเงินค่าแนะนำ :</strong>
              <span>{{ $saleCar->ReferrerAmount !== null ? number_format($saleCar->ReferrerAmount, 2) : '-' }} บาท</span>
            </div>

            <h5 class="border-bottom pb-2 mb-3">แคมเปญ</h5>
            <div class="d-flex justify-content-between mb-2">
              <strong>ข้อมูลแคมเปญ :</strong>
              <span id="viewMoreCampaignText"
                data-campaign="{{ $campaignText }}">
                {{ $campaignText ?: '-' }}
              </span>
            </div>

            @php

            $totalCam = $saleCar->TotalSaleCampaign;
            $gift = $saleCar->TotalAccessoryGift;
            $refA = $saleCar->ReferrerAmount;

            $markUp = $saleCar->Markup90;
            $totalCamMark = $totalCam + $markUp;

            $downPay = $saleCar->DownPaymentDiscount;
            $totalUseFi = $downPay + $gift + $refA;

            $totalBalanceFi = $totalCamMark - $totalUseFi;

            $discount = $saleCar->PaymentDiscount;
            $totalUseCash = $discount + $gift + $refA;

            $totalBalanceCash = $totalCam - $totalUseCash;

            @endphp

            @if ($saleCar->payment_mode === 'finance')
            <div class="d-flex justify-content-between mb-2">
              <strong>รวมงบแคมเปญ :</strong>
              <span>{{ $saleCar->TotalSaleCampaign !== null ? number_format($saleCar->TotalSaleCampaign, 2) : '-' }} บาท</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>บวกหัว (90%) :</strong>
              <span>{{ $saleCar->Markup90 !== null ? number_format($saleCar->Markup90, 2) : '-' }} บาท</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>ยอดรวมแคมเปญ (รวมบวกหัว 90%) :</strong>
              <span>{{ $totalCamMark !== null ? number_format($totalCamMark, 2) : '-' }} บาท</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>ส่วนลดเงินดาวน์ :</strong>
              <span>{{ $saleCar->DownPaymentDiscount !== null ? number_format($saleCar->DownPaymentDiscount, 2) : '-' }} บาท</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>ส่วนต่างของแถม :</strong>
              <span>{{ $saleCar->TotalAccessoryGift !== null ? number_format($saleCar->TotalAccessoryGift, 2) : '-' }} บาท</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>ค่าแนะนำ :</strong>
              <span>{{ $saleCar->ReferrerAmount !== null ? number_format($saleCar->ReferrerAmount, 2) : '-' }} บาท</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>ยอดรวมรายการที่ใช้ :</strong>
              <span>{{ $totalUseFi !== null ? number_format($totalUseFi, 2) : '-' }} บาท</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>คงเหลือ :</strong>
              <span>{{ $totalBalanceFi !== null ? number_format($totalBalanceFi, 2) : '-' }} บาท</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>คงเหลือ(แบ่ง 2 ส่วน) :</strong>
              <span>{{ $saleCar->CommissionSale !== null ? number_format($saleCar->CommissionSale, 2) : '-' }} บาท</span>
            </div>
            @else
            <div class="d-flex justify-content-between mb-2">
              <strong>รวมงบแคมเปญ :</strong>
              <span>{{ $saleCar->TotalSaleCampaign !== null ? number_format($saleCar->TotalSaleCampaign, 2) : '-' }} บาท</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>ส่วนลด :</strong>
              <span>{{ $saleCar->PaymentDiscount !== null ? number_format($saleCar->PaymentDiscount, 2) : '-' }} บาท</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>ส่วนต่างของแถม :</strong>
              <span>{{ $saleCar->TotalAccessoryGift !== null ? number_format($saleCar->TotalAccessoryGift, 2) : '-' }} บาท</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>ค่าแนะนำ :</strong>
              <span>{{ $saleCar->ReferrerAmount !== null ? number_format($saleCar->ReferrerAmount, 2) : '-' }} บาท</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>ยอดรวมรายการที่ใช้ :</strong>
              <span>{{ $totalUseCash !== null ? number_format($totalUseCash, 2) : '-' }} บาท</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>คงเหลือ :</strong>
              <span>{{ $totalBalanceCash !== null ? number_format($totalBalanceCash, 2) : '-' }} บาท</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>คงเหลือ(แบ่ง 2 ส่วน) :</strong>
              <span>{{ $saleCar->CommissionSale !== null ? number_format($saleCar->CommissionSale, 2) : '-' }} บาท</span>
            </div>
            @endif

          </div>
          <div class="col-md-6 ps-3">
            <h5 class="border-bottom pb-2 mb-3">รายละเอียดอุปกรณ์ตกแต่ง (แถม)</h5>
            @php
            $giftAccessories = $saleCar->accessories->where('pivot.type', 'gift');
            @endphp
            @if($giftAccessories->isNotEmpty())
            <div class="table-responsive text-nowrap">
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th>ลำดับ</th>
                    <th>รหัส</th>
                    <th>รายละเอียด</th>
                    <th>ประเภทราคา</th>
                    <th>ราคา</th>
                    <th>ค่าคอม</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($giftAccessories as $index => $giftAccessories)
                  <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td style="text-align: center;">{{ $giftAccessories->accessory_id ?? '-' }}</td>
                    <td style="text-align: center;">{{ $giftAccessories->detail ?? '-' }}</td>
                    <td style="text-align: center;">{{ $giftAccessories->pivot->price_type ?? '-' }}</td>
                    <td style="text-align: right;">{{ number_format($giftAccessories->pivot->price, 2) }}</td>
                    <td style="text-align: right;">{{ number_format($giftAccessories->pivot->commission, 2) }}</td>
                  </tr>
                  @endforeach
                  <tr>
                    <td colspan="4" style="text-align: center; font-weight: bold;">รวมทั้งหมด</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($saleCar->TotalAccessoryGift ?? 0, 2) }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($saleCar->AccessoryGiftCom ?? 0, 2) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            @else
            <p class="text-center">- ไม่มีข้อมูลรายการซื้อเพิ่ม -</p>
            @endif

            <h5 class="border-bottom pb-2 mb-3 mt-3">รายการซื้อเพิ่ม</h5>
            @php
            $extraAccessories = $saleCar->accessories->where('pivot.type', 'extra');
            @endphp
            @if($extraAccessories->isNotEmpty())
            <div class="table-responsive text-nowrap">
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th>ลำดับ</th>
                    <th>รหัส</th>
                    <th>รายละเอียด</th>
                    <th>ประเภทราคา</th>
                    <th>ราคา</th>
                    <th>ค่าคอม</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($extraAccessories as $index => $extraAccessories)
                  <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td style="text-align: center;">{{ $extraAccessories->accessory_id ?? '-' }}</td>
                    <td style="text-align: center;">{{ $extraAccessories->detail ?? '-' }}</td>
                    <td style="text-align: center;">{{ $extraAccessories->pivot->price_type ?? '-' }}</td>
                    <td style="text-align: right;">{{ number_format($extraAccessories->pivot->price, 2) }}</td>
                    <td style="text-align: right;">{{ number_format($extraAccessories->pivot->commission, 2) }}</td>
                  </tr>
                  @endforeach
                  <tr>
                    <td colspan="4" style="text-align: center; font-weight: bold;">รวมทั้งหมด</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($saleCar->TotalAccessoryExtra ?? 0, 2) }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($saleCar->AccessoryExtraCom ?? 0, 2) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            @else
            <p class="text-center">- ไม่มีข้อมูลรายการซื้อเพิ่ม -</p>
            @endif

            <h5 class="border-bottom pb-2 mb-3">ข้อมูลวันส่งมอบ</h5>
            <div class="d-flex justify-content-between mb-2">
              <strong>วันที่ส่งเอกสารสรุปการขาย :</strong>
              <span>{{ $saleCar->format_key_date ?? '-' }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>วันส่งมอบจริง (วันที่แจ้งประกัน) :</strong>
              <span>{{ $saleCar->format_delivery_date ?? '-' }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>วันที่ส่งมอบของบริษัท :</strong>
              <span>{{ $saleCar->format_dms_date ?? '-' }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>วันที่ส่งมอบของฝ่ายขาย :</strong>
              <span>{{ $saleCar->format_ck_date ?? '-' }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>ประมาณการส่งมอบ :</strong>
              <span>{{ $saleCar->format_delivery_estimate_date ?? '-' }}</span>
            </div>

            <h5 class="border-bottom pb-2 mb-3">ผู้อนุมัติ</h5>
            <div class="d-flex justify-content-between mb-2">
              <strong>ผู้เช็ครายการ (แอดมินขาย) :</strong>
              <span>
                @if(($saleCar->AdminSignature ?? null) == 1)
                เช็ครายการเรียบร้อยแล้ว
                @else
                -
                @endif
              </span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>วันที่แอดมินเช็ครายการ :</strong>
              <span>{{ $saleCar->format_admin_check_date ?? '-' }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>ผู้ตรวจสอบรายการ (IA) :</strong>
              <span>
                @if(($saleCar->CheckerID ?? null) == 1)
                เช็ครายการเรียบร้อยแล้ว
                @else
                -
                @endif
              </span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>วันที่ฝ่ายตรวจสอบเช็ครายการ :</strong>
              <span>{{ $saleCar->format_checker_date ?? '-' }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>ผู้จัดการ อนุมัติการขาย :</strong>
              <span>
                @if(($saleCar->SMSignature ?? null) == 1)
                อนุมัติเรียบร้อยแล้ว
                @else
                -
                @endif
              </span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>วันที่ผู้จัดการขายอนุมัติ :</strong>
              <span>{{ $saleCar->format_sm_date ?? '-' }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>ผู้จัดการ อนุมัติกรณีงบเกิน :</strong>
              <span>
                @if(($saleCar->ApprovalSignature ?? null) == 1)
                อนุมัติเรียบร้อยแล้ว
                @else
                -
                @endif
              </span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>วันที่ผู้จัดการอนุมัติการขาย :</strong>
              <span>{{ $saleCar->format_approval_date ?? '-' }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>GM อนุมัติกรณีงบเกิน (N) :</strong>
              <span>
                @if(($saleCar->GMApprovalSignature ?? null) == 1)
                อนุมัติเรียบร้อยแล้ว
                @else
                -
                @endif
              </span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <strong>วันที่ GM อนุมัติกรณีงบเกิน :</strong>
              <span>{{ $saleCar->format_gm_date ?? '-' }}</span>
            </div>

            <h5 class="border-bottom pb-2 mb-3">สถานะ</h5>
            <div class="d-flex justify-content-between mb-2">
              <strong>สถานะ :</strong>
              <span>{{ $saleCar->conStatus->name ?? '-' }}</span>
            </div>


          </div>
        </div>
      </div>
    </div>
  </div>
</div>