@php
  $clear = $place->clear;
  $effBudget = $place->effectiveBudget();
@endphp
<div class="modal fade clearPlace" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--input">

      <div class="modal-header mf-header mf-header--input px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon"><i class="bx bx-receipt fs-5 text-white"></i></div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">เคลียร์ค่าใช้จ่าย</h6>
            <small class="text-white mf-hd-sub">{{ $place->location }}</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">

        {{-- ── ข้อมูลสถานที่ (อ่านอย่างเดียว) ── --}}
        <div class="mf-section">
          <div class="mf-section-hd">
            <div class="mf-section-icon sky"><i class="bx bx-info-circle"></i></div>
            <span class="mf-section-title">ข้อมูลสถานที่</span>
          </div>
          <div class="mf-section-body">
            <div class="row g-3">
              <div class="col-md-6">
                <div class="po-label">แหล่งที่มาย่อย</div>
                <div class="info-pill">{{ $place->source->name ?? '-' }}</div>
              </div>
              <div class="col-md-6">
                <div class="po-label">สถานที่</div>
                <div class="info-pill">{{ $place->location }}</div>
              </div>
              <div class="col-md-4">
                <div class="po-label">งบประมาณ{{ $place->extra_cost ? ' (รวมงบเพิ่ม)' : '' }}</div>
                <div class="info-pill text-end">{{ $effBudget !== null ? number_format($effBudget, 2) : '-' }} ฿</div>
                @if ($place->extra_cost)
                  <small class="text-muted">ประมาณ {{ number_format($place->cost ?? 0, 2) }} + เพิ่ม {{ number_format($place->extra_cost, 2) }}</small>
                @endif
              </div>
              <div class="col-md-2">
                <div class="po-label">เป้า PP</div>
                <div class="info-pill text-end">{{ $place->target !== null ? number_format($place->target, 0) : '-' }}</div>
              </div>
              <div class="col-md-6">
                <div class="po-label">ประเภทค่าใช้จ่าย</div>
                <div class="info-pill">{{ $place->expense_type ?? '-' }}</div>
              </div>
            </div>
          </div>
        </div>

        {{-- ── ฟอร์มเคลียร์ ── --}}
        <form id="clearForm" action="{{ route('source.place.clear.store', $place->id) }}" method="POST"
          data-budget="{{ $effBudget !== null ? $effBudget : '' }}">
          @csrf
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon amber"><i class="bx bx-money"></i></div>
              <span class="mf-section-title">รายการค่าใช้จ่ายจริง</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3 mb-3">
                <div class="col-md-4">
                  <label class="mf-label form-label"><i class="bx bx-calendar-check ci-amber"></i> วันที่เคลียร์</label>
                  <input type="date" class="form-control" name="clear_date"
                    value="{{ optional($clear?->clear_date)->format('Y-m-d') }}">
                </div>
              </div>

              <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-2">
                  <thead>
                    <tr>
                      <th>ประเภท</th>
                      <th style="width:180px;">จำนวนเงิน</th>
                      <th style="width:60px;"></th>
                    </tr>
                  </thead>
                  <tbody id="clearItemsBody" data-next-index="{{ $clear && $clear->items->count() ? $clear->items->count() : 1 }}">
                    @php $rows = $clear && $clear->items->count() ? $clear->items : collect([null]); @endphp
                    @foreach ($rows as $i => $it)
                      <tr class="clear-item-row">
                        <td>
                          <select name="items[{{ $i }}][type]" class="form-select form-select-sm clear-type" required>
                            <option value="">— เลือกประเภท —</option>
                            @foreach ($clearTypes as $t)
                              <option value="{{ $t }}" {{ $it && $it->type === $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                          </select>
                        </td>
                        <td>
                          <input name="items[{{ $i }}][amount]" class="form-control form-control-sm text-end money-input clear-amount"
                            placeholder="0.00" autocomplete="off"
                            value="{{ $it && $it->amount !== null ? number_format($it->amount, 2) : '' }}">
                        </td>
                        <td class="text-center">
                          <button type="button" class="btn btn-sm btn-outline-danger btnRemoveClearItem"><i class="bx bx-trash"></i></button>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                  <tfoot>
                    <tr>
                      <td class="text-end fw-bold">รวม</td>
                      <td><input type="text" id="clearTotal" class="form-control form-control-sm text-end fw-bold" readonly></td>
                      <td></td>
                    </tr>
                  </tfoot>
                </table>
                <button type="button" class="btn btn-sm btn-outline-secondary btnAddClearItem">
                  <i class="bx bx-plus me-1"></i> เพิ่มรายการ
                </button>
              </div>
            </div>
          </div>
        </form>

        {{-- ── ส่วนบัญชี (เฉพาะ account / admin / md) ── --}}
        @if ($canAccount)
          <div class="mf-section mt-3">
            <div class="mf-section-hd">
              <div class="mf-section-icon indigo"><i class="bx bx-wallet"></i></div>
              <span class="mf-section-title">บัญชี — การจ่ายเงิน</span>
            </div>
            <div class="mf-section-body">
              @if (optional($clear)->pay_approved)
                <div class="alert alert-success py-2 mb-3">
                  <i class="bx bx-check-circle me-1"></i> อนุมัติจ่ายแล้ว
                  @if ($clear->pay_date) (วันที่จ่าย {{ $clear->pay_date->format('d/m/Y') }}) @endif
                  @if ($clear->payApprover) โดย {{ $clear->payApprover->full_name ?: $clear->payApprover->name }} @endif
                </div>
              @endif
              <div class="row g-3">
                <div class="col-md-4">
                  <label class="mf-label form-label"><i class="bx bx-calendar ci-indigo"></i> วันที่จ่าย</label>
                  <input type="date" id="clearPayDate" class="form-control"
                    value="{{ optional($clear?->pay_date)->format('Y-m-d') }}">
                </div>
              </div>
              @unless ($clear)
                <small class="text-muted d-block mt-2">* ต้องส่งเคลียร์ก่อนจึงจะอนุมัติการจ่ายได้</small>
              @endunless
            </div>
          </div>
        @endif

        {{-- ── ปุ่มทั้งหมด (ด้านล่างสุด) ── --}}
        <div class="d-flex justify-content-end gap-2 pt-2">
          <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal"><i class="bx bx-x me-1"></i>ปิด</button>
          <button type="button" class="btn btn-primary px-4 btnSaveClear" data-id="{{ $place->id }}">
            <i class="bx bx-send me-1"></i>ส่งเคลียร์
          </button>
          @if ($canAccount && $clear)
            <button type="button" class="btn btn-success px-4 btnApproveClearPay" data-id="{{ $place->id }}">
              <i class="bx bx-check me-1"></i>{{ $clear->pay_approved ? 'อัปเดตการจ่าย' : 'อนุมัติ' }}
            </button>
          @endif
        </div>

      </div>
    </div>
  </div>
</div>
