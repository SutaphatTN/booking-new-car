@php
  $clears    = $place->clears;
  $effBudget = $place->effectiveBudget();
  $cleared   = $place->clearedTotal();
  $remaining = $place->remainingBudget();
  $isSettled = $place->isSettled();
  $canSettle = $place->canSettle();
  // ปุ่ม "เปิดใหม่" ให้บัญชี + admin เห็นเสมอ
  $canReopen = $canAccount || auth()->user()->role === 'admin';
@endphp
<div class="modal fade clearPlace" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
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

      <div class="modal-body mf-body" id="clearModalInner">

        {{-- ── แจ้งเตือน: ปิดยอดแล้ว ── --}}
        @if ($isSettled)
          <div class="alert alert-secondary d-flex justify-content-between align-items-center py-2 mb-3">
            <div>
              <i class="bx bx-lock-alt me-1"></i> <strong>ปิดยอดแล้ว</strong>
              @if ($place->settled_at) — เมื่อ {{ $place->settled_at->format('d/m/Y H:i') }} @endif
              @if ($place->settledBy) โดย {{ $place->settledBy->full_name ?: $place->settledBy->name }} @endif
            </div>
            @if ($canReopen)
              <button type="button" class="btn btn-sm btn-warning fw-semibold btnReopenPlace" data-id="{{ $place->id }}">
                <i class="bx bx-lock-open-alt me-1"></i> เปิดใหม่
              </button>
            @endif
          </div>
        @endif

        {{-- ── ข้อมูลสถานที่ + สรุปงบ (อ่านอย่างเดียว) ── --}}
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
                <div class="po-label">ประเภทค่าใช้จ่าย</div>
                <div class="info-pill">{{ $place->expense_type ?? '-' }}</div>
              </div>

              {{-- สรุปงบ : งบรวม / เคลียร์แล้ว / คงเหลือ --}}
              <div class="col-md-4">
                <div class="po-label">งบประมาณ{{ $place->extra_cost ? ' (รวมงบเพิ่ม)' : '' }}</div>
                <div class="info-pill text-end">{{ $effBudget !== null ? number_format($effBudget, 2) : '-' }} ฿</div>
              </div>
              <div class="col-md-4">
                <div class="po-label">เคลียร์ไปแล้ว</div>
                <div class="info-pill text-end fw-semibold">{{ number_format($cleared, 2) }} ฿</div>
              </div>
              <div class="col-md-4">
                <div class="po-label">คงเหลือเคลียร์ได้</div>
                <div class="info-pill text-end fw-bold {{ $remaining !== null && $remaining < 0 ? 'text-danger' : 'text-success' }}">
                  {{ $remaining !== null ? number_format($remaining, 2) : '-' }} ฿
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- ── ใบเคลียร์ทั้งหมด (หลายงวด) ── --}}
        <div class="mf-section mt-3">
          <div class="mf-section-hd">
            <div class="mf-section-icon indigo"><i class="bx bx-list-ul"></i></div>
            <span class="mf-section-title">ใบเคลียร์ที่บันทึกไว้ ({{ $clears->count() }} งวด)</span>
          </div>
          <div class="mf-section-body">
            @forelse ($clears as $idx => $c)
              @php
                $itemsData = $c->items->map(fn($it) => ['type' => $it->type, 'amount' => (float) $it->amount])->values();
              @endphp
              <div class="border rounded p-2 mb-2">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <span class="badge bg-secondary">งวดที่ {{ $idx + 1 }}</span>
                    <span class="text-muted small ms-1">
                      <i class="bx bx-calendar-check"></i>
                      เคลียร์ {{ optional($c->clear_date)->format('d/m/Y') ?? '-' }}
                    </span>
                  </div>
                  <div class="fw-bold">{{ number_format($c->total, 2) }} ฿</div>
                </div>

                {{-- รายการย่อย --}}
                <div class="small mt-1 ps-1">
                  @foreach ($c->items as $it)
                    <div class="d-flex justify-content-between" style="max-width:340px;">
                      <span class="text-muted">{{ $it->type }}</span>
                      <span>{{ number_format($it->amount, 2) }}</span>
                    </div>
                  @endforeach
                </div>

                {{-- สถานะจ่าย + จัดการ --}}
                <div class="d-flex justify-content-between align-items-end mt-2 flex-wrap gap-2">
                  <div>
                    @if ($c->pay_approved)
                      <span class="badge bg-success"><i class="bx bx-check-circle"></i> จ่ายแล้ว
                        @if ($c->pay_date) {{ $c->pay_date->format('d/m/Y') }} @endif
                      </span>
                      @if ($c->payApprover)
                        <span class="text-muted small">โดย {{ $c->payApprover->full_name ?: $c->payApprover->name }}</span>
                      @endif
                    @else
                      <span class="badge bg-warning text-dark"><i class="bx bx-time"></i> ยังไม่จ่าย</span>
                    @endif

                    {{-- บัญชี: อนุมัติ/อัปเดตการจ่ายรายงวด (ปิดยอดแล้ว = ดูอย่างเดียว) --}}
                    @if ($canAccount && !$isSettled)
                      <div class="input-group input-group-sm mt-2" style="max-width:320px;">
                        <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                        <input type="date" class="form-control clear-pay-date"
                          value="{{ optional($c->pay_date)->format('Y-m-d') }}">
                        <button type="button" class="btn btn-success btnApproveClearPay"
                          data-id="{{ $place->id }}" data-clear-id="{{ $c->id }}">
                          <i class="bx bx-check me-1"></i>{{ $c->pay_approved ? 'อัปเดตจ่าย' : 'อนุมัติจ่าย' }}
                        </button>
                      </div>
                    @endif
                  </div>

                  @unless ($isSettled)
                    <div class="d-flex gap-1">
                      <button type="button" class="btn btn-sm btn-outline-primary btnEditClear"
                        data-clear-id="{{ $c->id }}"
                        data-clear-date="{{ optional($c->clear_date)->format('Y-m-d') }}"
                        data-items="{{ $itemsData->toJson() }}">
                        <i class="bx bx-edit"></i> แก้ไข
                      </button>
                      <button type="button" class="btn btn-sm btn-outline-danger btnDeleteClear"
                        data-id="{{ $place->id }}" data-clear-id="{{ $c->id }}">
                        <i class="bx bx-trash"></i>
                      </button>
                    </div>
                  @endunless
                </div>
              </div>
            @empty
              <div class="text-center text-muted py-3"><i class="bx bx-info-circle me-1"></i> ยังไม่มีใบเคลียร์ — เพิ่มงวดแรกด้านล่าง</div>
            @endforelse
          </div>
        </div>

        {{-- ── ฟอร์มเพิ่ม/แก้ไขใบเคลียร์ (ปิดยอดแล้ว = ดูอย่างเดียว ไม่แสดงฟอร์ม) ── --}}
        @unless ($isSettled)
        <form id="clearForm" action="{{ route('source.place.clear.store', $place->id) }}" method="POST"
          data-budget="{{ $effBudget !== null ? $effBudget : '' }}" data-cleared="{{ $cleared }}">
          @csrf
          <input type="hidden" name="clear_id" id="clearEditId" value="">
          <div class="mf-section mt-3">
            <div class="mf-section-hd">
              <div class="mf-section-icon amber"><i class="bx bx-money"></i></div>
              <span class="mf-section-title" id="clearFormTitle">เพิ่มใบเคลียร์ (งวดใหม่)</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3 mb-3">
                <div class="col-md-4">
                  <label class="mf-label form-label"><i class="bx bx-calendar-check ci-amber"></i> วันที่เคลียร์</label>
                  <input type="date" class="form-control" name="clear_date" id="clearDate" value="">
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
                  <tbody id="clearItemsBody" data-next-index="1">
                    <tr class="clear-item-row">
                      <td>
                        <select name="items[0][type]" class="form-select form-select-sm clear-type" required>
                          <option value="">— เลือกประเภท —</option>
                          @foreach ($clearTypes as $t)
                            <option value="{{ $t }}">{{ $t }}</option>
                          @endforeach
                        </select>
                      </td>
                      <td>
                        <input name="items[0][amount]" class="form-control form-control-sm text-end money-input clear-amount"
                          placeholder="0.00" autocomplete="off" value="">
                      </td>
                      <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-danger btnRemoveClearItem"><i class="bx bx-trash"></i></button>
                      </td>
                    </tr>
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
        @endunless

        {{-- ── ปุ่มทั้งหมด (ด้านล่างสุด) ── --}}
        <div class="d-flex justify-content-end gap-2 pt-2">
          @unless ($isSettled)
            <button type="button" class="btn btn-outline-secondary px-4 btnCancelEditClear d-none">
              <i class="bx bx-x me-1"></i>ยกเลิกแก้ไข
            </button>
          @endunless
          <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal"><i class="bx bx-x me-1"></i>ปิด</button>
          @if ($canAccount && !$isSettled)
            <button type="button" class="btn btn-dark px-4 btnSettlePlace {{ $canSettle ? '' : 'd-none' }}"
              data-id="{{ $place->id }}">
              <i class="bx bx-lock-alt me-1"></i>ปิดยอด/จบงาน
            </button>
          @endif
          @unless ($isSettled)
            <button type="button" class="btn btn-primary px-4 btnSaveClear" data-id="{{ $place->id }}">
              <i class="bx bx-send me-1"></i><span class="btnSaveClearLabel">บันทึกใบเคลียร์</span>
            </button>
          @endunless
        </div>

      </div>
    </div>
  </div>
</div>
