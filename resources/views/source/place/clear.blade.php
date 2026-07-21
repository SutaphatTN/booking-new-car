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
  {{-- ไม่ใช้ modal-dialog-scrollable — ธีมตัด shadow/transform ของปุ่มปิดใน modal แบบเลื่อนได้
       ทำให้กากบาทหน้าตาไม่เหมือน modal ตัวอื่น (เลื่อนทั้ง modal แทน) --}}
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
              {{-- ไม่แสดงแจกแจงงบตรงนี้ — ฟอร์มเพิ่มใบเคลียร์ด้านล่างมีประเภท + ยอดที่ตั้งไว้อยู่แล้ว --}}
              <div class="col-md-4">
                <div class="po-label">แหล่งที่มาย่อย</div>
                <div class="info-pill">{{ $place->source->name ?? '-' }}</div>
              </div>
              <div class="col-md-3">
                <div class="po-label">LAS Number</div>
                <div class="info-pill">{{ $place->las_number ?: '-' }}</div>
              </div>
              <div class="col-md-5">
                <div class="po-label">ช่วงวันที่</div>
                <div class="info-pill">
                  {{ optional($place->start_date)->format('d/m/Y') ?? '-' }} –
                  {{ optional($place->end_date)->format('d/m/Y') ?? '-' }}
                </div>
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
              <div class="clr-card {{ $c->pay_approved ? 'is-paid' : '' }}" data-clear-id="{{ $c->id }}">

                {{-- หัวการ์ด: งวด + วันที่เคลียร์ | สถานะจ่าย + ยอดรวมงวด --}}
                <div class="clr-hd">
                  <span class="clr-seq">งวดที่ {{ $idx + 1 }}</span>
                  <span class="clr-date">
                    <i class="bx bx-calendar-check"></i>
                    {{ optional($c->clear_date)->format('d/m/Y') ?? '-' }}
                  </span>
                  <span class="clr-hd-spacer"></span>
                  @if ($c->pay_approved)
                    <span class="clr-chip clr-chip--paid">
                      <i class="bx bx-check-circle"></i> จ่ายแล้ว
                      @if ($c->pay_date) {{ $c->pay_date->format('d/m/Y') }} @endif
                    </span>
                  @else
                    <span class="clr-chip clr-chip--unpaid"><i class="bx bx-time"></i> ยังไม่จ่าย</span>
                  @endif
                  <span class="clr-total">{{ number_format($c->total, 2) }} ฿</span>
                </div>

                {{-- รายการย่อย: ประเภท ..... ยอด (ยอดชิดขวาเป็นคอลัมน์เดียวกันทุกบรรทัด) --}}
                <div class="clr-items">
                  @foreach ($c->items as $it)
                    <div class="clr-item">
                      <span class="clr-item-type">{{ $it->type }}</span>
                      <span class="clr-item-amt">{{ number_format($it->amount, 2) }}</span>
                    </div>
                  @endforeach
                </div>

                {{-- ท้ายการ์ด: ผู้อนุมัติจ่าย/ช่องอนุมัติ | ปุ่มจัดการ --}}
                {{-- ปิดยอดแล้ว = ดูอย่างเดียว เหลือแค่ชื่อผู้อนุมัติจ่าย (ถ้ามี) --}}
                @if (!$isSettled || ($c->pay_approved && $c->payApprover))
                  <div class="clr-ft">
                    <div class="clr-ft-left">
                      @if ($c->pay_approved && $c->payApprover)
                        <span class="clr-approver">
                          <i class="bx bx-user-check"></i>
                          โดย {{ $c->payApprover->full_name ?: $c->payApprover->name }}
                        </span>
                      @endif

                      {{-- บัญชี: อนุมัติ/อัปเดตการจ่ายรายงวด (ปิดยอดแล้ว = ดูอย่างเดียว) --}}
                      @if ($canAccount && !$isSettled)
                        <div class="input-group input-group-sm clr-pay-group">
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
                        <button type="button" class="btn btn-sm btn-outline-warning btnEditClear"
                          data-clear-id="{{ $c->id }}"
                          data-clear-date="{{ optional($c->clear_date)->format('Y-m-d') }}"
                          data-items="{{ $itemsData->toJson() }}">
                          <i class="bx bx-edit"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger btnDeleteClear"
                          data-id="{{ $place->id }}" data-clear-id="{{ $c->id }}">
                          <i class="bx bx-trash"></i>
                        </button>
                      </div>
                    @endunless
                  </div>
                @endif
              </div>
            @empty
              <div class="text-center text-muted py-3"><i class="bx bx-info-circle me-1"></i> ยังไม่มีใบเคลียร์ — เพิ่มงวดแรกด้านล่าง</div>
            @endforelse
          </div>
        </div>

        {{-- ── ฟอร์มเพิ่ม/แก้ไขใบเคลียร์ (ปิดยอดแล้ว = ดูอย่างเดียว ไม่แสดงฟอร์ม) ── --}}
        @unless ($isSettled)
        @php
          // ตั้งต้นฟอร์มด้วยประเภทที่ "ยังไม่ได้เคลียร์" (ยอดเว้นว่างให้กรอกเอง)
          // ประเภทไหนเคลียร์ไปแล้วถือว่าจบ ไม่โผล่ซ้ำในงวดถัดไป แม้จ่ายจริงน้อยกว่างบที่ตั้งไว้
          // (ส่วนต่างคืองบเหลือที่ไม่ได้จ่าย ไม่ใช่ยอดค้างเคลียร์) — ถ้าต้องแก้ ใช้ปุ่ม "แก้ไข" ที่งวดนั้น
          $clearedTypes = $place->clears
              ->flatMap(fn($c) => $c->items->pluck('type'))
              ->unique()
              ->all();

          $budgetTypeCount = $place->budgetLines()->filter(fn($l) => in_array($l['type'], $clearTypes))->count();

          $prefill = $place->budgetLines()
              ->filter(fn($l) => in_array($l['type'], $clearTypes) && !in_array($l['type'], $clearedTypes))
              ->map(fn($l) => [
                  'type' => $l['type'],
                  'ph'   => 'ตั้งงบไว้ ' . number_format($l['amount'], 2),
              ])
              ->values();
        @endphp
        <form id="clearForm" action="{{ route('source.place.clear.store', $place->id) }}" method="POST"
          data-budget="{{ $effBudget !== null ? $effBudget : '' }}" data-cleared="{{ $cleared }}"
          data-prefill="{{ $prefill->toJson() }}">
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

              @if ($prefill->isNotEmpty())
                <div class="small text-muted mb-2">
                  <i class="bx bx-info-circle"></i>
                  เติมประเภทที่ยังไม่ได้เคลียร์ให้แล้ว — กรอกยอดที่จ่ายจริง ลบแถวที่ไม่ได้ใช้ หรือกด "เพิ่มรายการ" เพื่อใส่ประเภทอื่นได้
                </div>
              @elseif ($budgetTypeCount > 0)
                <div class="small text-muted mb-2">
                  <i class="bx bx-check-circle"></i>
                  เคลียร์ครบทุกประเภทที่ตั้งงบไว้แล้ว — ถ้ามีค่าใช้จ่ายอื่นเพิ่ม เลือกประเภทแล้วกรอกยอดได้เลย
                  (แก้ยอดของงวดเดิม ใช้ปุ่ม "แก้ไข" ที่งวดนั้น)
                </div>
              @endif

              <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-2">
                  <thead>
                    <tr>
                      <th>ประเภท</th>
                      <th style="width:180px;">จำนวนเงิน</th>
                      <th style="width:60px;"></th>
                    </tr>
                  </thead>
                  <tbody id="clearItemsBody">
                    {{-- แถวต้นแบบ (ซ่อนไว้) — JS ใช้ clone ตอนเพิ่ม/รีเซ็ต/แก้ไข
                         disabled ไว้เพื่อกันไม่ให้ checkValidity() ไปติด required ของแถวที่ซ่อนอยู่ (JS ปลดให้ตอน clone) --}}
                    <tr class="clear-item-tmpl d-none">
                      <td>
                        <select class="form-select form-select-sm clear-type" required disabled>
                          <option value="">— เลือกประเภท —</option>
                          @foreach ($clearTypes as $t)
                            <option value="{{ $t }}">{{ $t }}</option>
                          @endforeach
                        </select>
                      </td>
                      <td>
                        <input class="form-control form-control-sm text-end money-input clear-amount"
                          placeholder="0.00" autocomplete="off" value="" disabled>
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
