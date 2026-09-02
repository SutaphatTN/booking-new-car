@php
  // brand ที่วินัยเป็น "ผ่าน/ไม่ผ่าน" (ไม่ผ่านหัก 15%) และไม่มีคอม lead/clip
  // ต้องตรงกับกลุ่ม [1,3,4] ใน SaleCommissionMonthly::computeNet เป๊ะ ๆ
  // (เดิมฟอร์ม brand 4 โชว์ช่อง วินัย/lead/clip แบบ brand 2 ทั้งที่ computeNet ไม่เอามาคิด → กรอกแล้วยอดไม่ขยับ)
  $isBrand13 = in_array((int) $brand, [1, 3, 4], true);
  // คอลัมน์ "budget หัก" ผูกกับระบบ budget ไม่ใช่แค่ brand 2 — เดือนที่เลิกใช้ budget แล้วต้องไม่มีคอลัมน์นี้
  $showBudgetCol = (int) $brand === 2 && ($budget["active"] ?? false);
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
          .commission-cars-table th {
            white-space: nowrap;
            vertical-align: middle;
          }

          .commission-cars-table td {
            vertical-align: middle;
          }

          .commission-cars-table td:nth-child(n+4) {
            white-space: nowrap;
          }

          /* คอลัมน์ตัวเลขทั้งหมด */
          .commission-cars-table th:nth-child(2),
          .commission-cars-table th:nth-child(3) {
            min-width: 150px;
          }

          /* กลุ่มสรุปท้ายแถว : รวมเงินได้ / หักเกินงบ / คอมสุทธิ — ไล่โทนให้อ่านเป็นก้อนเดียว */
          .commission-cars-table .col-sum-pos {
            background: #f0fdf4;
            border-left: 2px solid #86efac;
          }

          .commission-cars-table .col-sum-neg {
            background: #fef2f2;
          }

          .commission-cars-table .col-sum-net {
            background: #ecfdf5;
            border-left: 2px solid #34d399;
          }

          .commission-cars-table .cell-note {
            font-size: .68rem;
            line-height: 1.3;
          }
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
                <th class="text-end"
                  title="คอมงบเหลือ (สูตรอัตโนมัติ) — ถ้าเกินงบจะเป็น 0 แล้วยอดติดลบไปอยู่ช่องหักเกินงบ">งบเหลือ</th>
                <th class="text-end"
                  title="ยอดที่ผู้จัดการ/GM อนุมัติ กรณีเกินงบทะลุเพดาน — เฉพาะยอดที่เป็นบวก (ใบเก่า brand 1/3) ; กติกาใหม่กรอกเป็นยอดหัก จะไปอยู่ช่องหักเกินงบ">
                  คอมที่ได้</th>
                <th class="text-end">ประดับยนต์</th>
                <th class="text-end">ดอกเบี้ย</th>
                <th class="text-end">รถเทิร์น</th>
                <th class="text-end">คอมอื่นๆ</th>
                @if ($showBudgetCol)
                  <th class="text-end">budget หัก</th>
                @endif
                <th class="text-end col-sum-pos" title="ผลรวมทุกยอดที่เป็นบวกของคันนี้">รวมเงินได้</th>
                <th class="text-end col-sum-neg" title="ยอดหักจากการขายเกินงบ (สูตรอัตโนมัติ + ยอดหักที่ GM อนุมัติ)">
                  หักเกินงบ</th>
                <th class="text-end col-sum-net" title="รวมเงินได้ − หักเกินงบ">คอมสุทธิ</th>
              </tr>
            </thead>
            <tbody>
              {{-- ยอดรวมของคอลัมน์ที่ค่าคงที่ (ช่องที่แก้สดได้ — คอมอื่นๆ / budget หัก — รวมฝั่ง JS) --}}
              @php
                $sumBalance = 0.0;
                $sumApproved = 0.0;
                $sumAccessory = 0.0;
                $sumInterest = 0.0;
                $sumTurn = 0.0;
                $sumWithheld = 0.0; // รวมยอดที่ถูกกั๊ก/พักไว้ ยังไม่จ่ายรอบนี้
              @endphp
              @forelse ($cars as $i => $c)
                @php
                  $carCom = (float) ($c['carCommission'] ?? 0);
                  $autoBal = (float) $c['balanceCampaign'];
                  $approved = (float) ($c['approvedCom'] ?? 0);
                  // ยอดบวก/ยอดลบ แยกคนละช่อง : ยอดติดลบทั้งหมดไปรวมที่ "หักเกินงบ"
                  $rowNeg = min($autoBal, 0) + min($approved, 0);
                  // ฐานคงที่ของแถว (ถอดเฉพาะช่องที่แก้สดได้) — budget หักไม่อยู่ในสูตรคอมแล้ว จึงไม่ต้องถอด
                  $rowBase = $c['commissionSale'] - $c['specialCom'];
                  $rowNet = $c['commissionSale'] + $carCom;
                  $rowPos = $rowNet - $rowNeg;

                  // สะสมยอดรวมท้ายตาราง — บวกด้วย "ค่าที่แสดงในช่อง" ให้ตรงกับที่ตาเห็น
                  // (งบเหลือ/คอมที่ได้ ตัดยอดติดลบออก เพราะติดลบไปโผล่ช่อง "หักเกินงบ" แล้ว)
                  $sumBalance += max($autoBal, 0);
                  $sumApproved += max($approved, 0);
                  $sumAccessory += (float) $c['accessoryCom'];
                  $sumInterest += (float) $c['interestCom'];
                  $sumTurn += (float) $c['turnCarCom'];

                  // ยอดของคันนี้ที่ "ยังไม่จ่ายรอบนี้" — กั๊กยกไปรอบหน้า หรือพักไว้เพราะยังไม่รับรถ
                  // (ตรงกับที่ controller ใช้คิด $rounds['gak_total'] / $rounds['pending'])
                  $rowPending = ($rounds['active'] ?? false) && empty($c['mainPayDate']) ? $carCom : 0.0;
                  $rowHeld =
                      ($rounds['active'] ?? false) && !empty($c['mainPayDate']) && !empty($c['isHeld'])
                          ? (float) $c['heldAmount']
                          : 0.0;
                  $sumWithheld += $rowPending + $rowHeld;
                @endphp
                <tr>
                  <td class="text-center text-muted">{{ $i + 1 }}</td>
                  <td>
                    {{ $c['customer'] }}
                    <div class="text-muted" style="font-size:.72rem; line-height:1.4;">
                      <div><i class="bx bx-calendar-check"></i> CK :
                        {{ !empty($c['ckDate']) ? \Illuminate\Support\Carbon::parse($c['ckDate'])->format('d-m-Y') : '-' }}
                      </div>
                      <div><i class="bx bx-car"></i> DD :
                        {{ !empty($c['ddDate']) ? \Illuminate\Support\Carbon::parse($c['ddDate'])->format('d-m-Y') : '— ยังไม่รับรถ' }}
                      </div>
                    </div>
                  </td>
                  <td>
                    {{ $c['model'] }}
                    <div class="text-muted" style="font-size:.78rem;">{{ $c['subModel'] }}</div>
                  </td>

                  {{-- คอมตัวรถ : 0 พร้อมป้ายบอกเหตุผลถ้าเกินงบทะลุเพดาน
                       (ป้ายกั๊ก/พักไว้ ย้ายไปช่อง "คอมสุทธิ" แล้ว เพราะกั๊กหักจากคอมสุทธิของคัน ไม่ใช่คอมตัวรถ) --}}
                  <td class="text-end {{ $carCom > 0 ? '' : 'text-muted' }}">
                    {{ number_format($carCom, 2) }}
                    {{-- ป้ายบอกสถานะงบของคันนี้ — ยอดคอมตัวรถด้านบนไม่เปลี่ยน แค่บอกว่าคันไหนเกินงบ
                         · แดง = เกินเพดาน + CK ก่อนวันตัด → ไม่ได้คอมตัวรถจริง ๆ (ยอดเป็น 0)
                         · ส้ม = เกินงบ/เกินเพดาน แต่ยังได้คอมตัวรถเต็ม (เช่น brand 2 ที่ไม่สนใจการเกินงบแล้ว) --}}
                    @if (!empty($c['noCarCom']))
                      <div class="text-danger cell-note"
                        title="เกินงบทะลุเพดาน (ก่อนวันเปลี่ยนกติกา) — ไม่ได้คอมตัวรถ แต่ยังนับจำนวนคัน">
                        <i class="bx bx-error-circle"></i> เกินงบทะลุเพดาน
                      </div>
                    @elseif (!empty($c['overCeiling']))
                      <div class="text-warning cell-note"
                        title="เกินงบทะลุเพดาน — แต่ยังได้คอมตัวรถเต็มตามกติกาปัจจุบัน">
                        <i class="bx bx-error-circle"></i> เกินงบทะลุเพดาน
                      </div>
                    @elseif (!empty($c['isOverBudget']))
                      <div class="text-warning cell-note"
                        title="ใบนี้เกินงบ (งบเหลือติดลบ) — ยังได้คอมตัวรถเต็ม">
                        <i class="bx bx-error-circle"></i> เกินงบ
                      </div>
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

                  {{-- คอมที่ได้ : เฉพาะยอดอนุมัติที่เป็นบวก (ใบเก่า brand 1/3 เท่านั้น)
                       กติกาใหม่ทุกแบรนด์กรอกเป็นยอดหัก → ติดลบ ไปโผล่ช่อง "หักเกินงบ" --}}
                  <td class="text-end {{ $approved > 0 ? 'fw-semibold text-success' : 'text-muted' }}">
                    {{ number_format(max($approved, 0), 2) }}
                  </td>

                  <td class="text-end">{{ number_format($c['accessoryCom'], 2) }}</td>
                  <td class="text-end">{{ number_format($c['interestCom'], 2) }}</td>
                  <td class="text-end">{{ number_format($c['turnCarCom'], 2) }}</td>
                  <td class="text-end" style="min-width:120px;">
                    <input type="text" inputmode="decimal"
                      class="form-control form-control-sm text-end car-special-input" data-id="{{ $c['id'] }}"
                      data-rowbase="{{ $rowBase }}" data-rowcar="{{ $carCom }}"
                      data-rowneg="{{ $rowNeg }}" value="{{ $c['specialCom'] }}" {{ $roInput }}>
                  </td>
                  @if ($showBudgetCol)
                    <td class="text-end" style="min-width:120px;">
                      <input type="text" inputmode="decimal"
                        class="form-control form-control-sm text-end car-budget-input" data-id="{{ $c['id'] }}"
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
                  {{-- คอมสุทธิ : ยอดที่คันนี้ได้ทั้งก้อน + บรรทัดรองบอกว่าถูกกั๊ก/พักไว้เท่าไร
                       (ยอดในช่องไม่ลด — กั๊กเป็นเรื่องเวลาจ่าย ; แถว "รวมทั้งหมด" ถึงจะหักออกให้เห็นเงินที่ได้รอบนี้) --}}
                  <td class="text-end fw-bold col-sum-net">
                    <div class="car-row-total">{{ number_format($rowNet, 2) }}</div>
                    @if ($rounds['active'] ?? false)
                      @if (empty($c['mainPayDate']) && $carCom > 0)
                        <div class="text-danger cell-note fw-normal"
                          title="ยังไม่รับรถ (DD ว่าง) — พักคอมตัวรถไว้ก่อน จ่ายเมื่อมีวันรับรถ">
                          <i class="bx bx-pause-circle"></i> − พักไว้ {{ number_format($rowPending, 2) }}
                        </div>
                      @elseif ($rowHeld > 0)
                        <div class="text-warning cell-note fw-normal"
                          title="กั๊กไว้จ่ายรอบถัดไป — ยอดของคันนี้ไม่ได้หายไป แค่เลื่อนวันจ่าย">
                          <i class="bx bx-time-five"></i> − กั๊ก {{ number_format($rowHeld, 2) }}
                          → {{ \Illuminate\Support\Carbon::parse($c['heldPayday'])->format('d/m/Y') }}
                        </div>
                      @endif
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="{{ $showBudgetCol ? 14 : 13 }}" class="text-center py-4 text-muted">
                    ไม่มีรายการส่งมอบในเดือนนี้</td>
                </tr>
              @endforelse
            </tbody>
            <tfoot>
              <tr class="table-light fw-bold">
                <td colspan="3" class="text-end">รวมทั้งหมด</td>
                <td class="text-end" id="carsCarTotal">0.00</td>
                <td class="text-end">{{ number_format($sumBalance, 2) }}</td>
                <td class="text-end">{{ number_format($sumApproved, 2) }}</td>
                <td class="text-end">{{ number_format($sumAccessory, 2) }}</td>
                <td class="text-end">{{ number_format($sumInterest, 2) }}</td>
                <td class="text-end">{{ number_format($sumTurn, 2) }}</td>
                {{-- ช่องที่แก้สดได้ → รวมฝั่ง JS (recomputeCarsTable) --}}
                <td class="text-end" id="carsSpecialTotal">0.00</td>
                @if ($showBudgetCol)
                  <td class="text-end" id="carsBudgetTotal">0.00</td>
                @endif
                <td class="text-end col-sum-pos" id="carsPositiveTotal">0.00</td>
                <td class="text-end col-sum-neg text-danger" id="carsNegativeTotal">0.00</td>
                {{-- คอมสุทธิรวม = หักยอดที่กั๊ก/พักไว้ออกแล้ว → เท่ากับเงินส่วนของเดือนนี้ที่เข้ารอบจ่ายจริง --}}
                <td class="text-end col-sum-net" data-withheld="{{ (float) $sumWithheld }}">
                  <span id="carsNetTotal">0.00</span>
                  @if ($sumWithheld > 0)
                    <div class="cell-note fw-normal text-warning"
                      title="ยอดที่ถูกกั๊ก/พักไว้ ยังไม่จ่ายรอบนี้ — หักออกจากยอดรวมแล้ว">
                      หักกั๊ก/พักไว้ {{ number_format($sumWithheld, 2) }}
                    </div>
                  @endif
                </td>
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
              <div class="col-md-2 col-6">
                <label for="deduct_absence" class="mf-label form-label">
                  <i class="bx bx-minus-circle text-danger"></i> ขาด/ลา/มาสาย (หัก)
                </label>
                <input type="text" inputmode="decimal" class="form-control form-control-sm text-end cmoney"
                  id="deduct_absence" name="deduct_absence" value="{{ $adjustment->deduct_absence ?? 0 }}"
                  {{ $roInput }}>
              </div>
            @else
              {{-- brand 2 : ตัวเงินทั้งหมด --}}
              <div class="col-md-2 col-6">
                <label for="com_discipline" class="mf-label form-label">
                  <i class="bx bx-medal text-success"></i> ค่าคอมวินัย
                </label>
                <input type="text" inputmode="decimal" class="form-control form-control-sm text-end cmoney"
                  id="com_discipline" name="com_discipline" value="{{ $adjustment->com_discipline ?? 0 }}"
                  {{ $roInput }}>
              </div>
              <div class="col-md-2 col-6">
                <label for="deduct_absence" class="mf-label form-label">
                  <i class="bx bx-minus-circle text-danger"></i> ขาด/ลา/มาสาย (หัก)
                </label>
                <input type="text" inputmode="decimal" class="form-control form-control-sm text-end cmoney"
                  id="deduct_absence" name="deduct_absence" value="{{ $adjustment->deduct_absence ?? 0 }}"
                  {{ $roInput }}>
              </div>
              <div class="col-md-2 col-6">
                <label for="com_lead" class="mf-label form-label">
                  <i class="bx bx-target-lock text-primary"></i> คอม Lead
                </label>
                <input type="text" inputmode="decimal" class="form-control form-control-sm text-end cmoney"
                  id="com_lead" name="com_lead" value="{{ $adjustment->com_lead ?? 0 }}" {{ $roInput }}>
              </div>
              <div class="col-md-2 col-6">
                <label for="com_clip" class="mf-label form-label">
                  <i class="bx bx-video text-info"></i> คอม Clip
                </label>
                <input type="text" inputmode="decimal" class="form-control form-control-sm text-end cmoney"
                  id="com_clip" name="com_clip" value="{{ $adjustment->com_clip ?? 0 }}" {{ $roInput }}>
              </div>
            @endif

            {{-- คอมประดับยนต์ (หน้าร้าน) — ใช้ทุก brand : ผู้จัดการ/GM กรอกเอง บวกเข้ายอดคอม
                 บวกนอกฐาน จึงไม่โดนหัก 15% ตอนวินัยไม่ผ่าน (ดู SaleCommissionMonthly::computeNet) --}}
            <div class="col-md-2 col-6">
              <label for="com_accessory_sold" class="mf-label form-label">
                <i class="bx bx-plus-circle text-success"></i> คอมประดับยนต์ (หน้าร้าน)
              </label>
              <input type="text" inputmode="decimal" class="form-control form-control-sm text-end cmoney"
                id="com_accessory_sold" name="com_accessory_sold" value="{{ $adjustment->com_accessory_sold ?? 0 }}"
                {{ $roInput }}>
            </div>

            {{-- หักอื่นๆ + หมายเหตุ — ไว้ท้ายสุดให้กรอกต่อกันได้ (ใช้ทุก brand เป็นช่องหักปลายเปิด) --}}
            <div class="col-md-2 col-6">
              <label for="deduct_other" class="mf-label form-label">
                <i class="bx bx-minus-circle text-danger"></i> หักอื่นๆ
              </label>
              <input type="text" inputmode="decimal" class="form-control form-control-sm text-end cmoney"
                id="deduct_other" name="deduct_other" value="{{ $adjustment->deduct_other ?? 0 }}"
                {{ $roInput }}>
            </div>
            <div class="col-md-{{ $isBrand13 ? 3 : 12 }} col-12">
              <label for="deduct_other_note" class="mf-label form-label">
                <i class="bx bx-note text-secondary"></i> หมายเหตุหักอื่นๆ <span
                  class="text-muted">(ระบุว่าหักค่าอะไร)</span>
              </label>
              <input type="text" class="form-control form-control-sm" id="deduct_other_note"
                name="deduct_other_note" maxlength="255" placeholder="เช่น ค่าปรับผิดระเบียบ / ค่าเสียหายรถทดลองขับ"
                value="{{ $adjustment->deduct_other_note ?? '' }}" {{ $roInput }}>
            </div>
          </div>

          {{-- ── budget ยกมา (brand 2) — กระเป๋าตังค์จากรถส่งมอบเดือนก่อน × 1,000 ── --}}
          @if ($budget['active'])
            <div
              class="alert alert-primary d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3 mb-0 py-2 px-3"
              id="budgetWalletBox" data-carried="{{ $budget['carried'] }}"
              data-bonus-rate="{{ $budget['bonus_rate'] }}" data-bonus="{{ $budget['bonus'] }}">
              <div class="small">
                <i class="bx bx-wallet me-1"></i>
                <strong>budget ยกมา</strong> (จากรถส่งมอบเดือนก่อน × 1,000)
                <div class="mt-1">
                  ยกมา <strong>{{ number_format($budget['carried'], 2) }}</strong> ฿
                  · ใช้ไป (budget หัก) <strong class="text-danger"
                    id="budgetUsedDisplay">{{ number_format($budget['used'], 2) }}</strong> ฿
                </div>
              </div>
              <div class="fw-bold text-primary text-end">
                คงเหลือ = <span id="budgetRemainingDisplay">{{ number_format($budget['remaining'], 2) }}</span> ฿
                {{-- budget ที่เหลือคืนเซลล์ 30% — รวมอยู่ในยอดค่าคอมสุทธิด้านล่างแล้ว --}}
                <div class="small fw-normal text-success mt-1">
                  + คืนเซลล์ {{ (int) ($budget['bonus_rate'] * 100) }}% =
                  <strong id="budgetBonusDisplay">{{ number_format($budget['bonus'], 2) }}</strong> ฿
                  <span class="text-muted">(รวมในยอดสุทธิแล้ว)</span>
                </div>
              </div>
            </div>
          @endif

          {{-- ── คอม SSI (brand 1 เดือน 3/10) — เฉลี่ยแยกสาขา + เกณฑ์ ≥18 คัน/≥1 ทุกเดือน ── --}}
          @if ($ssi['active'])
            @php $ssiOk = $ssi['eligible']; @endphp
            <div
              class="alert {{ $ssiOk ? 'alert-info' : 'alert-secondary' }} d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3 mb-0 py-2 px-3">
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
                    ขาย ≥ {{ $ssi['min_cars'] }} คัน
                    {{ $ssi['count'] >= $ssi['min_cars'] ? '✓' : '✗ (ได้ ' . $ssi['count'] . ')' }}
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

          {{-- ── คอมตัวรถรายคัน (รายเดือน) ──
               ปิดการแสดงผลไว้ตามที่ผู้ใช้ขอ (2026-09-02) — ยอดยังถูกคิดรวมในยอดสุทธิเหมือนเดิม
               และดูรายคันได้จากคอลัมน์ "คอมตัวรถ" ในตารางด้านบนอยู่แล้ว
               เปิดคืน: เอา false && ออกจากบรรทัดล่าง (ห้ามใช้ blade comment ครอบ เพราะข้างในมี comment ซ้อน) --}}
          @if (false && $car['active'])
            @php $carSkipped = (int) $car['count'] - (int) $car['paidCount']; @endphp
            <div
              class="alert alert-info d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3 mb-0 py-2 px-3">
              <div class="small">
                <i class="bx bx-car me-1"></i>
                <strong>คอมตัวรถรายคัน</strong>
                @if ($car['mode'] === 'model')
                  — คิดตามรุ่นรถ (ขาย {{ $car['count'] }} คัน — ได้คอม {{ $car['paidCount'] }} คัน)
                @else
                  — ขาย <strong>{{ $car['count'] }}</strong> คัน
                  (ได้คอม <strong>{{ $car['paidCount'] }}</strong> คัน) ×
                  เรต <strong>{{ number_format($car['rate'], 0) }}</strong>/คัน
                  {{-- แบรนด์ที่ไม่มีเป้า (brand 3/4 — ดู config brands_without_target) ไม่ต้องโชว์ป้ายเลย
                       เพราะเรตสองคอลัมน์เท่ากัน ป้ายจะขึ้น "ไม่บรรลุเป้า" ตลอดทั้งที่ไม่เคยมีเป้าให้บรรลุ
                       ส่วนแบรนด์ที่ใช้เป้าแต่ยังไม่ได้ตั้งเป้าเดือนนั้น โชว์ว่า "ยังไม่ได้ตั้งเป้า"
                       จะได้รู้ว่าต้องไปตั้ง ไม่ใช่เงียบหายไปเฉย ๆ --}}
                  @unless (in_array((int) $car['brand'], config('car_commission.brands_without_target', []), true))
                    @if (!empty($car['hasTarget']))
                      <span class="badge {{ $car['achieved'] ? 'bg-success' : 'bg-secondary' }}">
                        {{ $car['achieved'] ? 'บรรลุเป้า 120%' : 'ไม่บรรลุเป้า' }}
                      </span>
                    @else
                      <span class="badge bg-warning text-dark"
                        title="ยังไม่ได้ตั้งเป้าของเดือนนี้ — ระบบคิดเรตแบบไม่บรรลุเป้าไปก่อน">
                        ยังไม่ได้ตั้งเป้า
                      </span>
                    @endif
                  @endunless
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
                  <span><i class="bx bx-money text-success"></i> รอบหลัก {{ $rounds['main_date'] }} (คอมพื้นฐาน + SSI
                    + ค่าคอมรถส่วนหลัก)</span>
                  <span class="fw-bold text-success">{{ number_format($rounds['main_own'], 2) }} ฿</span>
                </div>
                @if ($rounds['carried_in'] > 0)
                  <div class="d-flex justify-content-between text-success">
                    <span class="ps-3"><i class="bx bx-log-in"></i> + กั๊กยกมาจากเดือนก่อน (จ่ายรอบนี้ด้วย)</span>
                    <span>{{ number_format($rounds['carried_in'], 2) }} ฿</span>
                  </div>
                  {{-- เงินที่โอนจริงในรอบนี้ — คนละตัวกับ "ยอดค่าคอมสุทธิ" ด้านล่าง
                       (ยอดสุทธิ = คอมที่เกิดในเดือนนี้ ; ก้อนยกมาเป็นคอมของเดือนก่อนที่มาจ่ายรอบเดียวกัน
                        ถ้าเอาไปบวกในยอดสุทธิด้วยจะกลายเป็นนับซ้ำกับเดือนที่มันเกิด) --}}
                  <div class="d-flex justify-content-between fw-bold border-top mt-1 pt-1">
                    <span><i class="bx bx-wallet"></i> รวมเงินเข้ารอบ {{ $rounds['main_date'] }}</span>
                    <span>{{ number_format($rounds['main_own'] + $rounds['carried_in'], 2) }} ฿</span>
                  </div>
                @endif
                @foreach ($rounds['gak_items'] as $g)
                  <div class="d-flex justify-content-between text-warning">
                    <span class="ps-3"><i class="bx bx-time-five"></i> กั๊ก 2,000 — {{ $g['customer'] }} →
                      ยกไปจ่าย {{ $g['date'] }}</span>
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
            {{-- ตัวเลขใหญ่ = "เงินเข้ารอบนี้" (รอบหลัก + กั๊กยกมา) ไม่ใช่คอมที่เกิดในเดือน
                 — ก้อนที่กั๊กยกไป/พักไว้ ยังไม่จ่ายรอบนี้ จึงไม่ถูกนับ (ดู $rounds['net_offset'])
                 brand อื่นที่ไม่มีระบบกั๊ก ใช้ยอดคอมของเดือนตามเดิม --}}
            <div class="text-end">
              <div class="text-muted small">
                @if ($rounds['active'] ?? false)
                  เงินเข้ารอบ {{ $rounds['main_date'] }} (คอมเดือนนี้ส่วนที่ถึงกำหนด + กั๊กยกมา)
                @else
                  ยอดค่าคอมสุทธิ (คอมทั้งเดือน{{ $ssi['active'] ? ' + SSI' : '' }} + ค่าคอมรถ)
                @endif
              </div>
              <div class="fs-4 fw-bold text-success" id="netCommissionDisplay" data-base="{{ $baseCommission }}"
                data-brand="{{ (int) $brand }}" data-ssi="{{ $ssiAmount }}"
                data-car="{{ (float) $car['amount'] }}" data-held="{{ (float) ($rounds['net_offset'] ?? 0) }}">
                {{ number_format($rounds['pay_total'] ?? $net, 2) }} ฿
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
