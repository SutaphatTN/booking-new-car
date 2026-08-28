@php
  $isBrand13 = in_array((int) $brand, [1, 3], true);
  $isBrand2  = (int) $brand === 2;
  // audit_lead / audit_dp = ดูอย่างเดียว (ไม่มีปุ่มบันทึก + ทุกช่องเป็น readonly/disabled)
  $canEdit = $canEdit ?? true;
  $roInput = $canEdit ? '' : 'readonly';
  $roCheck = $canEdit ? '' : 'disabled';
  $ssiAmount = $ssi['active'] ? (float) $ssi['amount'] : 0.0;
  // $net = คอมพื้นฐาน(CK เดือน P−1) + SSI + ค่าคอมรถจ่ายจริงเดือน P (คิดมาจาก controller)
@endphp

<div class="modal fade commissionDetail" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--view">
      <div class="modal-header mf-header mf-header--view px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon"><i class="bx bx-user fs-5 text-white"></i></div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">{{ $saleUser->name ?? '-' }}</h6>
            <small class="text-white mf-hd-sub">
              สาขา : {{ $saleUser->branchInfo->name ?? '-' }} — ประจำเดือน {{ $monthLabel }}
            </small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">

        {{-- ── รายชื่อลูกค้า / ค่าคอมรายคัน (อ้างอิง) ── --}}
        <style>
          /* หัวตาราง + ช่องตัวเลข ไม่ตัดคำ (ให้เลื่อนแนวนอนแทน) ; ชื่อลูกค้า/รุ่นรถ ห่อบรรทัดได้ */
          .commission-cars-table th { white-space: nowrap; vertical-align: middle; }
          .commission-cars-table td { vertical-align: middle; }
          .commission-cars-table td:nth-child(n+4) { white-space: nowrap; }  /* คอลัมน์ตัวเลขทั้งหมด */
          .commission-cars-table th:nth-child(2), .commission-cars-table th:nth-child(3) { min-width: 150px; }
          /* กลุ่มสรุปท้ายแถว : รวมเงินได้ / หักเกินงบ / คอมสุทธิ — ไล่โทนให้อ่านเป็นก้อนเดียว */
          .commission-cars-table .col-sum-pos { background: #f0fdf4; border-left: 2px solid #86efac; }
          .commission-cars-table .col-sum-neg { background: #fef2f2; }
          .commission-cars-table .col-sum-net { background: #ecfdf5; border-left: 2px solid #34d399; }
          .commission-cars-table .cell-note { font-size: .68rem; line-height: 1.3; }
        </style>
        <div class="fw-bold mb-2"><i class="bx bx-list-ul me-1"></i> รายชื่อลูกค้าที่ส่งมอบในเดือนนี้</div>
        <div class="table-responsive mb-4">
          <table class="table table-bordered table-sm align-middle commission-cars-table" style="font-size:.85rem;">
            <thead class="table-light">
              <tr class="text-center">
                <th style="width:44px;">No.</th>
                <th>ลูกค้า</th>
                <th>รุ่นรถ</th>
                <th class="text-end" title="คอมตัวรถรายคัน — คันที่เกินงบทะลุเพดานไม่ได้คอมตัวรถ (แสดง 0)">คอมตัวรถ</th>
                <th class="text-end" title="คอมงบเหลือ (สูตรอัตโนมัติ) — ถ้าเกินงบจะเป็น 0 แล้วยอดติดลบไปอยู่ช่องหักเกินงบ">งบเหลือ</th>
                <th class="text-end" title="ยอดที่ผู้จัดการ/GM อนุมัติ กรณีเกินงบทะลุเพดาน (เฉพาะยอดที่เป็นบวก — ยอดหักไปอยู่ช่องหักเกินงบ)">คอมที่ได้</th>
                <th class="text-end">ประดับยนต์</th>
                <th class="text-end">ดอกเบี้ย</th>
                <th class="text-end">รถเทิร์น</th>
                <th class="text-end">คอมอื่นๆ</th>
                @if ($isBrand2)
                  <th class="text-end">budget หัก</th>
                @endif
                <th class="text-end col-sum-pos" title="ผลรวมทุกยอดที่เป็นบวกของคันนี้">รวมเงินได้</th>
                <th class="text-end col-sum-neg" title="ยอดหักจากการขายเกินงบ (สูตรอัตโนมัติ + ยอดหักที่ GM อนุมัติ)">หักเกินงบ</th>
                <th class="text-end col-sum-net" title="รวมเงินได้ − หักเกินงบ">คอมสุทธิ</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($cars as $i => $c)
                @php
                  $carCom = (float) ($c['carCommission'] ?? 0);
                  $autoBal = (float) $c['balanceCampaign'];
                  $approved = (float) ($c['approvedCom'] ?? 0);
                  // ยอดบวก/ยอดลบ แยกคนละช่อง : ยอดติดลบทั้งหมดไปรวมที่ "หักเกินงบ"
                  $rowNeg = min($autoBal, 0) + min($approved, 0);
                  $rowBase = $c['commissionSale'] - $c['specialCom'] - $c['budgetDeduct'];
                  $rowNet = $c['commissionSale'] + $carCom;
                  $rowPos = $rowNet - $rowNeg;
                @endphp
                <tr>
                  <td class="text-center text-muted">{{ $i + 1 }}</td>
                  <td>
                    {{ $c['customer'] }}
                    <div class="text-muted" style="font-size:.72rem; line-height:1.4;">
                      <div><i class="bx bx-calendar-check"></i> CK : {{ !empty($c['ckDate']) ? \Illuminate\Support\Carbon::parse($c['ckDate'])->format('d-m-Y') : '-' }}</div>
                      <div><i class="bx bx-car"></i> DD : {{ !empty($c['ddDate']) ? \Illuminate\Support\Carbon::parse($c['ddDate'])->format('d-m-Y') : '— ยังไม่รับรถ' }}</div>
                    </div>
                  </td>
                  <td>
                    {{ $c['model'] }}
                    <div class="text-muted" style="font-size:.78rem;">{{ $c['subModel'] }}</div>
                  </td>

                  {{-- คอมตัวรถ : 0 พร้อมป้ายบอกเหตุผลถ้าเกินงบทะลุเพดาน ;
                       กั๊ก/พักไว้ = เรื่องเวลาจ่าย ไม่ลดยอด → โชว์เป็นบรรทัดรองใต้ยอดคอมตัวรถที่มันถูกแบ่งออกมา --}}
                  <td class="text-end {{ $carCom > 0 ? '' : 'text-muted' }}">
                    {{ number_format($carCom, 2) }}
                    @if (!empty($c['overCeiling']))
                      <div class="text-danger cell-note" title="เกินงบทะลุเพดาน — ไม่ได้คอมตัวรถ (แต่ยังนับจำนวนคัน)">
                        <i class="bx bx-error-circle"></i> เกินงบทะลุเพดาน
                      </div>
                    @endif
                    @if (($rounds['active'] ?? false) && $carCom > 0)
                      @if (empty($c['mainPayDate']))
                        <div class="text-danger cell-note" title="ยังไม่รับรถ (DD ว่าง) — พักคอมตัวรถทั้งก้อนไว้ก่อน">
                          <i class="bx bx-pause-circle"></i> พักไว้ (ยังไม่รับรถ)
                        </div>
                      @elseif (!empty($c['isHeld']) && $c['heldAmount'] > 0)
                        <div class="text-warning cell-note" title="กั๊กไว้จ่ายรอบถัดไป — ไม่ได้ลดยอดคอมสุทธิ เป็นแค่เวลาจ่าย">
                          <i class="bx bx-time-five"></i> กั๊ก {{ number_format($c['heldAmount'], 0) }}
                          → {{ \Illuminate\Support\Carbon::parse($c['heldPayday'])->format('d/m/Y') }}
                        </div>
                      @endif
                    @endif
                  </td>

                  {{-- งบเหลือ : เกินงบบังคับ 0 (ยอดติดลบไปช่อง "หักเกินงบ") --}}
                  <td class="text-end {{ $autoBal > 0 ? '' : 'text-muted' }}">
                    {{ number_format(max($autoBal, 0), 2) }}
                    @if (!empty($c['extraDeduct']))
                      <div class="text-danger cell-note" title="ถูกหักเพื่อชดเก็บงบเพิ่มเติม">
                        − เก็บงบเพิ่มเติม {{ number_format($c['extraDeduct'], 2) }}
                      </div>
                    @endif
                  </td>

                  {{-- คอมที่ได้ : เฉพาะยอดอนุมัติที่เป็นบวก (brand 2/4 เป็นยอดหัก → ไปช่อง "หักเกินงบ") --}}
                  <td class="text-end {{ $approved > 0 ? 'fw-semibold text-success' : 'text-muted' }}">
                    {{ number_format(max($approved, 0), 2) }}
                  </td>

                  <td class="text-end">{{ number_format($c['accessoryCom'], 2) }}</td>
                  <td class="text-end">{{ number_format($c['interestCom'], 2) }}</td>
                  <td class="text-end">{{ number_format($c['turnCarCom'], 2) }}</td>
                  <td class="text-end" style="min-width:120px;">
                    <input type="text" inputmode="decimal"
                      class="form-control form-control-sm text-end car-special-input"
                      data-id="{{ $c['id'] }}"
                      data-rowbase="{{ $rowBase }}"
                      data-rowcar="{{ $carCom }}"
                      data-rowneg="{{ $rowNeg }}"
                      value="{{ $c['specialCom'] }}" {{ $roInput }}>
                  </td>
                  @if ($isBrand2)
                    <td class="text-end" style="min-width:120px;">
                      <input type="text" inputmode="decimal"
                        class="form-control form-control-sm text-end car-budget-input"
                        data-id="{{ $c['id'] }}"
                        value="{{ $c['budgetDeduct'] ?: '' }}" placeholder="0" {{ $roInput }}>
                    </td>
                  @endif

                  <td class="text-end fw-semibold col-sum-pos car-row-positive">{{ number_format($rowPos, 2) }}</td>
                  <td class="text-end col-sum-neg {{ $rowNeg < 0 ? 'fw-semibold text-danger' : 'text-muted' }}">
                    {{ number_format($rowNeg, 2) }}
                    @if ($approved < 0)
                      <div class="cell-note text-danger" title="ยอดหักที่ผู้จัดการ/GM อนุมัติ (เกินงบทะลุเพดาน)">
                        ยอด GM อนุมัติ {{ number_format($approved, 2) }}
                      </div>
                    @elseif ($autoBal < 0)
                      <div class="cell-note text-muted" title="ยอดจากสูตรอัตโนมัติ (balance × 2 × per_budget%)">
                        {{ $c['overCeiling'] ? 'รอยอดอนุมัติ' : 'สูตรอัตโนมัติ' }}
                      </div>
                    @endif
                  </td>
                  <td class="text-end fw-bold col-sum-net car-row-total">{{ number_format($rowNet, 2) }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="{{ $isBrand2 ? 14 : 13 }}" class="text-center py-4 text-muted">ไม่มีรายการส่งมอบในเดือนนี้</td>
                </tr>
              @endforelse
            </tbody>
            <tfoot>
              <tr class="table-light fw-bold">
                <td colspan="3" class="text-end">รวมทั้งหมด</td>
                <td class="text-end" id="carsCarTotal">0.00</td>
                <td colspan="{{ $isBrand2 ? 7 : 6 }}"></td>
                <td class="text-end col-sum-pos" id="carsPositiveTotal">0.00</td>
                <td class="text-end col-sum-neg text-danger" id="carsNegativeTotal">0.00</td>
                <td class="text-end col-sum-net" id="carsNetTotal">0.00</td>
              </tr>
            </tfoot>
          </table>
        </div>

        @unless ($canEdit)
          <div class="alert alert-secondary d-flex align-items-center gap-2 py-2 px-3 mb-3">
            <i class="bx bx-lock-alt"></i>
            <span class="small">โหมดดูอย่างเดียว — แก้ไข/บันทึกค่าคอมได้เฉพาะ admin, ผู้จัดการ, GM และ MD</span>
          </div>
        @endunless

        {{-- ── ฟอร์มค่าคอมเพิ่มเติม (ต่อเซลล์ ต่อเดือน) ── --}}
        <div class="fw-bold mb-2"><i class="bx bx-edit me-1"></i> ค่าคอมเพิ่มเติม (ประจำเดือน)</div>
        <form id="commissionMonthlyForm">
          <input type="hidden" name="SaleID" value="{{ $saleUser->id ?? '' }}">
          <input type="hidden" name="year" value="{{ $year }}">
          <input type="hidden" name="month" value="{{ $month }}">

          <div class="row g-3">
            @if ($isBrand13)
              {{-- brand 1/3 : วินัยเป็น ผ่าน/ไม่ผ่าน (ไม่ผ่านหัก 15%) + ขาด/ลา/มาสาย (ไม่มี lead/clip) --}}
              <div class="col-md-3 col-12">
                <label class="mf-label form-label">
                  <i class="bx bx-medal text-success"></i> ค่าคอมวินัย
                </label>
                <div class="d-flex gap-4 mt-1">
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="discipline_failed" id="disc_pass"
                      value="0" {{ !$adjustment->discipline_failed ? 'checked' : '' }} {{ $roCheck }}>
                    <label class="form-check-label" for="disc_pass">ผ่าน</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="discipline_failed" id="disc_fail"
                      value="1" {{ $adjustment->discipline_failed ? 'checked' : '' }} {{ $roCheck }}>
                    <label class="form-check-label text-danger" for="disc_fail">ไม่ผ่าน (หัก 15%)</label>
                  </div>
                </div>
              </div>
              <div class="col-md-3 col-12">
                <label for="deduct_absence" class="mf-label form-label">
                  <i class="bx bx-minus-circle text-danger"></i> ค่าขาด/ลา/มาสาย (หัก)
                </label>
                <input type="text" inputmode="decimal" class="form-control text-end cmoney" id="deduct_absence"
                  name="deduct_absence" value="{{ $adjustment->deduct_absence ?? 0 }}" {{ $roInput }}>
              </div>
            @else
              {{-- brand 2 : ตัวเงินทั้งหมด --}}
              <div class="col-md-3 col-6">
                <label for="com_discipline" class="mf-label form-label">
                  <i class="bx bx-medal text-success"></i> ค่าคอมวินัย
                </label>
                <input type="text" inputmode="decimal" class="form-control text-end cmoney" id="com_discipline"
                  name="com_discipline" value="{{ $adjustment->com_discipline ?? 0 }}" {{ $roInput }}>
              </div>
              <div class="col-md-3 col-6">
                <label for="deduct_absence" class="mf-label form-label">
                  <i class="bx bx-minus-circle text-danger"></i> ค่าขาด/ลา/มาสาย (หัก)
                </label>
                <input type="text" inputmode="decimal" class="form-control text-end cmoney" id="deduct_absence"
                  name="deduct_absence" value="{{ $adjustment->deduct_absence ?? 0 }}" {{ $roInput }}>
              </div>
              <div class="col-md-3 col-6">
                <label for="com_lead" class="mf-label form-label">
                  <i class="bx bx-target-lock text-primary"></i> คอม Lead
                </label>
                <input type="text" inputmode="decimal" class="form-control text-end cmoney" id="com_lead" name="com_lead"
                  value="{{ $adjustment->com_lead ?? 0 }}" {{ $roInput }}>
              </div>
              <div class="col-md-3 col-6">
                <label for="com_clip" class="mf-label form-label">
                  <i class="bx bx-video text-info"></i> คอม Clip
                </label>
                <input type="text" inputmode="decimal" class="form-control text-end cmoney" id="com_clip" name="com_clip"
                  value="{{ $adjustment->com_clip ?? 0 }}" {{ $roInput }}>
              </div>
            @endif
          </div>

          {{-- ── budget ยกมา (brand 2) — กระเป๋าตังค์จากรถส่งมอบเดือนก่อน × 1,000 ── --}}
          @if ($budget['active'])
            <div class="alert alert-primary d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3 mb-0 py-2 px-3"
              id="budgetWalletBox" data-carried="{{ $budget['carried'] }}">
              <div class="small">
                <i class="bx bx-wallet me-1"></i>
                <strong>budget ยกมา</strong> (จากรถส่งมอบเดือนก่อน × 1,000)
                <div class="mt-1">
                  ยกมา <strong>{{ number_format($budget['carried'], 2) }}</strong> ฿
                  · ใช้ไป (budget หัก) <strong class="text-danger" id="budgetUsedDisplay">{{ number_format($budget['used'], 2) }}</strong> ฿
                </div>
              </div>
              <div class="fw-bold text-primary">
                คงเหลือ = <span id="budgetRemainingDisplay">{{ number_format($budget['remaining'], 2) }}</span> ฿
              </div>
            </div>
          @endif

          {{-- ── คอม SSI (brand 1 เดือน 3/10) — เฉลี่ยแยกสาขา + เกณฑ์ ≥18 คัน/≥1 ทุกเดือน ── --}}
          @if ($ssi['active'])
            @php $ssiOk = $ssi['eligible']; @endphp
            <div class="alert {{ $ssiOk ? 'alert-info' : 'alert-secondary' }} d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3 mb-0 py-2 px-3">
              <div class="small">
                <i class="bx bx-award me-1"></i>
                <strong>คอมค่าครึ่งปี (SSI)</strong>
                — SSI เฉลี่ยสาขา <strong>{{ $ssi['branch'] ?? '-' }}</strong>
                <strong>{{ $ssi['average'] !== null ? number_format($ssi['average'], 2) . '%' : '-' }}</strong>
                → เรต <strong>{{ number_format($ssi['rate'], 0) }}</strong>/คัน
                × <strong>{{ $ssi['count'] }}</strong> คัน (Retail มีคะแนน)
                <div class="mt-1">
                  เงื่อนไข:
                  <span class="{{ $ssi['count'] >= $ssi['min_cars'] ? 'text-success' : 'text-danger' }}">
                    ขาย ≥ {{ $ssi['min_cars'] }} คัน {{ $ssi['count'] >= $ssi['min_cars'] ? '✓' : '✗ (ได้ ' . $ssi['count'] . ')' }}
                  </span>
                  ·
                  <span class="{{ $ssi['every_month'] ? 'text-success' : 'text-danger' }}">
                    มี ≥ 1 คันทุกเดือน {{ $ssi['every_month'] ? '✓' : '✗' }}
                  </span>
                </div>
              </div>
              <div class="fw-bold {{ $ssiOk ? 'text-info' : 'text-muted' }}">
                = {{ number_format($ssiAmount, 2) }} ฿{{ $ssiOk ? '' : ' (ไม่เข้าเกณฑ์)' }}
              </div>
            </div>
          @endif

          {{-- ── คอมตัวรถรายคัน (รายเดือน) — รวมเข้ายอดสุทธิ ── --}}
          @if ($car['active'])
            @php $carSkipped = (int) $car['count'] - (int) $car['paidCount']; @endphp
            <div class="alert alert-info d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3 mb-0 py-2 px-3">
              <div class="small">
                <i class="bx bx-car me-1"></i>
                <strong>คอมตัวรถรายคัน</strong>
                @if ($car['mode'] === 'model')
                  — คิดตามรุ่นรถ (ขาย {{ $car['count'] }} คัน — ได้คอม {{ $car['paidCount'] }} คัน)
                @else
                  — ขาย <strong>{{ $car['count'] }}</strong> คัน
                  (ได้คอม <strong>{{ $car['paidCount'] }}</strong> คัน) ×
                  เรต <strong>{{ number_format($car['rate'], 0) }}</strong>/คัน
                  <span class="badge {{ $car['achieved'] ? 'bg-success' : 'bg-secondary' }}">
                    {{ $car['achieved'] ? 'บรรลุเป้า 120%' : 'ไม่บรรลุเป้า' }}
                  </span>
                @endif
                @if ($carSkipped > 0)
                  <div class="mt-1 text-danger">
                    <i class="bx bx-error-circle"></i>
                    เกินงบทะลุเพดาน <strong>{{ $carSkipped }}</strong> คัน — ไม่ได้คอมตัวรถ (แต่ยังนับจำนวนคัน)
                  </div>
                @endif
              </div>
              <div class="fw-bold text-info">= {{ number_format($car['amount'], 2) }} ฿</div>
            </div>
          @endif

          {{-- ── แตกรอบจ่ายเงิน (brand 1) — รอบหลัก + กั๊กที่ยกไป ── --}}
          @if ($rounds['active'] ?? false)
            <div class="alert alert-warning mt-3 mb-0 py-2 px-3">
              <div class="fw-bold small mb-1">
                <i class="bx bx-calendar-check me-1"></i> รอบจ่ายเงิน
              </div>
              <div class="small">
                <div class="d-flex justify-content-between">
                  <span><i class="bx bx-money text-success"></i> รอบหลัก {{ $rounds['main_date'] }} (คอมพื้นฐาน + SSI + ค่าคอมรถส่วนหลัก)</span>
                  <span class="fw-bold text-success">{{ number_format($rounds['main_own'], 2) }} ฿</span>
                </div>
                @if ($rounds['carried_in'] > 0)
                  <div class="d-flex justify-content-between text-success">
                    <span class="ps-3"><i class="bx bx-log-in"></i> + กั๊กยกมาจากเดือนก่อน (จ่ายรอบนี้ด้วย)</span>
                    <span>{{ number_format($rounds['carried_in'], 2) }} ฿</span>
                  </div>
                @endif
                @foreach ($rounds['gak_items'] as $g)
                  <div class="d-flex justify-content-between text-warning">
                    <span class="ps-3"><i class="bx bx-time-five"></i> กั๊ก 2,000 — {{ $g['customer'] }} → ยกไปจ่าย {{ $g['date'] }}</span>
                    <span class="fw-bold">{{ number_format($g['amount'], 2) }} ฿</span>
                  </div>
                @endforeach
                @if ($rounds['pending'] > 0)
                  <div class="d-flex justify-content-between text-danger">
                    <span class="ps-3"><i class="bx bx-pause-circle"></i> พักไว้ (ยังไม่รับรถ DD ว่าง)</span>
                    <span class="fw-bold">{{ number_format($rounds['pending'], 2) }} ฿</span>
                  </div>
                @endif
              </div>
            </div>
          @endif

          {{-- ── สรุปยอดสุทธิ ── --}}
          <div class="d-flex align-items-center justify-content-end gap-3 mt-4 flex-wrap">
            <div class="text-end">
              <div class="text-muted small">ยอดค่าคอมสุทธิ (คอมทั้งเดือน{{ $ssi['active'] ? ' + SSI' : '' }} + ค่าคอมรถ) — กั๊กเป็นเรื่องเวลาจ่าย</div>
              <div class="fs-4 fw-bold text-success" id="netCommissionDisplay"
                data-base="{{ $baseCommission }}" data-brand="{{ (int) $brand }}"
                data-ssi="{{ $ssiAmount }}" data-car="{{ (float) $car['amount'] }}" data-held="0">
                {{ number_format($net, 2) }} ฿
              </div>
            </div>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
                <i class="bx bx-x me-1"></i>ปิด
              </button>
              @if ($canEdit)
                <button type="submit" class="btn btn-success px-4" id="btnSaveCommissionMonthly">
                  <i class="bx bx-save me-1"></i>บันทึก
                </button>
              @endif
            </div>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>
