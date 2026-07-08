@php
  $isBrand13 = in_array((int) $brand, [1, 3], true);
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
          .commission-cars-table td:nth-child(n+4) { white-space: nowrap; }  /* คอลัมน์ตัวเลข 4–9 */
          .commission-cars-table th:nth-child(2), .commission-cars-table th:nth-child(3) { min-width: 150px; }
        </style>
        <div class="fw-bold mb-2"><i class="bx bx-list-ul me-1"></i> รายชื่อลูกค้าที่ส่งมอบในเดือนนี้</div>
        <div class="table-responsive mb-4">
          <table class="table table-bordered table-sm align-middle commission-cars-table" style="font-size:.85rem;">
            <thead class="table-light">
              <tr class="text-center">
                <th style="width:44px;">No.</th>
                <th>ลูกค้า</th>
                <th>รุ่นรถ</th>
                <th class="text-end">งบเหลือ</th>
                <th class="text-end">ประดับยนต์</th>
                <th class="text-end">คอมอื่นๆ</th>
                <th class="text-end">ดอกเบี้ย</th>
                <th class="text-end">รถเทิร์น</th>
                <th class="text-end">รวมค่าคอมรถ</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($cars as $i => $c)
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
                  <td class="text-end">{{ number_format($c['balanceCampaign'], 2) }}</td>
                  <td class="text-end">{{ number_format($c['accessoryCom'], 2) }}</td>
                  <td class="text-end" style="min-width:120px;">
                    <input type="number" step="0.01"
                      class="form-control form-control-sm text-end car-special-input"
                      data-id="{{ $c['id'] }}"
                      data-rowbase="{{ $c['commissionSale'] - $c['specialCom'] }}"
                      value="{{ $c['specialCom'] }}">
                  </td>
                  <td class="text-end">{{ number_format($c['interestCom'], 2) }}</td>
                  <td class="text-end">{{ number_format($c['turnCarCom'], 2) }}</td>
                  <td class="text-end fw-semibold car-row-total">{{ number_format($c['commissionSale'], 2) }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="9" class="text-center py-4 text-muted">ไม่มีรายการส่งมอบในเดือนนี้</td>
                </tr>
              @endforelse
            </tbody>
            <tfoot>
              <tr class="table-light fw-bold">
                <td colspan="8" class="text-end">รวมค่าคอมรถทั้งหมด</td>
                <td class="text-end" id="carsBaseTotal">{{ number_format($baseCommission, 2) }}</td>
              </tr>
            </tfoot>
          </table>
        </div>

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
                      value="0" {{ !$adjustment->discipline_failed ? 'checked' : '' }}>
                    <label class="form-check-label" for="disc_pass">ผ่าน</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="discipline_failed" id="disc_fail"
                      value="1" {{ $adjustment->discipline_failed ? 'checked' : '' }}>
                    <label class="form-check-label text-danger" for="disc_fail">ไม่ผ่าน (หัก 15%)</label>
                  </div>
                </div>
              </div>
              <div class="col-md-3 col-12">
                <label for="deduct_absence" class="mf-label form-label">
                  <i class="bx bx-minus-circle text-danger"></i> ค่าขาด/ลา/มาสาย (หัก)
                </label>
                <input type="number" step="0.01" class="form-control text-end" id="deduct_absence"
                  name="deduct_absence" value="{{ $adjustment->deduct_absence ?? 0 }}">
              </div>
            @else
              {{-- brand 2 : ตัวเงินทั้งหมด --}}
              <div class="col-md-3 col-6">
                <label for="com_discipline" class="mf-label form-label">
                  <i class="bx bx-medal text-success"></i> ค่าคอมวินัย
                </label>
                <input type="number" step="0.01" class="form-control text-end" id="com_discipline"
                  name="com_discipline" value="{{ $adjustment->com_discipline ?? 0 }}">
              </div>
              <div class="col-md-3 col-6">
                <label for="deduct_absence" class="mf-label form-label">
                  <i class="bx bx-minus-circle text-danger"></i> ค่าขาด/ลา/มาสาย (หัก)
                </label>
                <input type="number" step="0.01" class="form-control text-end" id="deduct_absence"
                  name="deduct_absence" value="{{ $adjustment->deduct_absence ?? 0 }}">
              </div>
              <div class="col-md-3 col-6">
                <label for="com_lead" class="mf-label form-label">
                  <i class="bx bx-target-lock text-primary"></i> คอม Lead
                </label>
                <input type="number" step="0.01" class="form-control text-end" id="com_lead" name="com_lead"
                  value="{{ $adjustment->com_lead ?? 0 }}">
              </div>
              <div class="col-md-3 col-6">
                <label for="com_clip" class="mf-label form-label">
                  <i class="bx bx-video text-info"></i> คอม Clip
                </label>
                <input type="number" step="0.01" class="form-control text-end" id="com_clip" name="com_clip"
                  value="{{ $adjustment->com_clip ?? 0 }}">
              </div>
            @endif
          </div>

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
            <div class="alert alert-info d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3 mb-0 py-2 px-3">
              <div class="small">
                <i class="bx bx-car me-1"></i>
                <strong>คอมตัวรถรายคัน</strong>
                @if ($car['mode'] === 'model')
                  — คิดตามรุ่นรถ (ขาย {{ $car['count'] }} คัน)
                @else
                  — ขาย <strong>{{ $car['count'] }}</strong> คัน ×
                  เรต <strong>{{ number_format($car['rate'], 0) }}</strong>/คัน
                  <span class="badge {{ $car['achieved'] ? 'bg-success' : 'bg-secondary' }}">
                    {{ $car['achieved'] ? 'บรรลุเป้า 120%' : 'ไม่บรรลุเป้า' }}
                  </span>
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
              <button type="submit" class="btn btn-success px-4" id="btnSaveCommissionMonthly">
                <i class="bx bx-save me-1"></i>บันทึก
              </button>
            </div>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>
