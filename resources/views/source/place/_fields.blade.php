{{-- ฟิลด์ร่วมของฟอร์มสถานที่ (input/edit) — รับ $place (null ตอนเพิ่ม) และ $offlineSources --}}
@php
  $pfx = empty($place) ? 'placeAdd' : 'placeEdit';
  // สีไอคอนวันที่ให้ตามธีม modal (เพิ่ม=indigo / แก้ไข=amber) — getDateIconColor อ่าน ci-* จาก label ก่อน
  $dateCi = empty($place) ? 'indigo' : 'amber';
  // ล็อก ประมาณค่าใช้จ่าย/เป้า PP เมื่อขออนุมัติแล้ว (รออนุมัติ/อนุมัติแล้ว) — ฉบับร่าง/ถูกส่งกลับ ยังแก้ได้
  $lockBudget = !empty($place) && in_array($place->status, [
      \App\Models\SourcePlace::STATUS_PENDING,
      \App\Models\SourcePlace::STATUS_APPROVED,
  ]);
@endphp
@if (!empty($place) && $place->status === \App\Models\SourcePlace::STATUS_REJECTED && optional($place->request)->reject_reason)
  <div class="alert alert-warning d-flex align-items-start gap-2 mb-3">
    <i class="bx bx-edit-alt fs-5"></i>
    <div>
      <strong>ถูกส่งกลับให้แก้ไข</strong><br>
      สิ่งที่ต้องแก้ไข: {{ $place->request->reject_reason }}
    </div>
  </div>
@endif

<div class="mf-section">
  <div class="mf-section-hd">
    <div class="mf-section-icon {{ $dateCi }}">
      <i class="bx bx-store"></i>
    </div>
    <span class="mf-section-title">ข้อมูลสถานที่</span>
  </div>
  <div class="mf-section-body">
    <div class="row g-3">

      <div class="col-md-6">
        <label for="{{ $pfx }}_salecar_type_id" class="mf-label form-label">
          <i class="bx bx-sitemap"></i> แหล่งที่มาย่อย (Offline) <span class="text-danger">*</span>
        </label>
        <select id="{{ $pfx }}_salecar_type_id" name="salecar_type_id" class="form-select" required>
          <option value="">— เลือก —</option>
          @foreach ($offlineSources as $os)
            <option value="{{ $os->id }}" {{ ($place->salecar_type_id ?? '') == $os->id ? 'selected' : '' }}>
              {{ $os->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-md-6">
        <label for="{{ $pfx }}_las_number" class="mf-label form-label"><i class="bx bx-id-card"></i> LAS Number</label>
        <input id="{{ $pfx }}_las_number" type="text" class="form-control" name="las_number" autocomplete="off"
          value="{{ $place->las_number ?? '' }}" placeholder="เช่น RS-26-05-00385">
      </div>

      <div class="col-md-12">
        <label for="{{ $pfx }}_location" class="mf-label form-label">
          <i class="bx bx-map"></i> ระบุสถานที่ <span class="text-danger">*</span>
        </label>
        <input id="{{ $pfx }}_location" type="text" class="form-control" name="location" autocomplete="off" required
          value="{{ $place->location ?? '' }}" placeholder="เช่น AL (2 June) KOL อบต.ทุ่งสูง">
      </div>

      <div class="col-md-3">
        <label for="{{ $pfx }}_target" class="mf-label form-label"><i class="bx bx-target-lock"></i> เป้า PP</label>
        <input id="{{ $pfx }}_target" type="number" min="0" step="1" class="form-control text-end" name="target" autocomplete="off"
          value="{{ isset($place->target) ? (int) $place->target : '' }}" placeholder="0"
          {{ $lockBudget ? 'readonly' : '' }}>
      </div>

      <div class="col-md-4">
        <label for="{{ $pfx }}_start_date" class="mf-label form-label"><i class="bx bx-calendar ci-{{ $dateCi }}"></i> วันเริ่มงาน</label>
        <input id="{{ $pfx }}_start_date" type="date" class="form-control" name="start_date"
          value="{{ optional($place?->start_date)->format('Y-m-d') }}">
      </div>

      <div class="col-md-4">
        <label for="{{ $pfx }}_end_date" class="mf-label form-label"><i class="bx bx-calendar-check ci-{{ $dateCi }}"></i> วันจบงาน</label>
        <input id="{{ $pfx }}_end_date" type="date" class="form-control" name="end_date"
          value="{{ optional($place?->end_date)->format('Y-m-d') }}">
      </div>

    </div>
  </div>
</div>

{{-- ── แจกแจงประมาณค่าใช้จ่าย (หลายประเภท) — ใช้ชุดประเภทเดียวกับตอนเคลียร์ เพื่อเทียบ ประมาณ vs จริง ได้ ── --}}
@php
  $budgetTypes = config('source.clear_types', []);
  $budgetLines = empty($place) ? collect() : $place->budgetLines();
@endphp
<div class="mf-section mt-3">
  <div class="mf-section-hd">
    <div class="mf-section-icon {{ $dateCi }}">
      <i class="bx bx-money"></i>
    </div>
    <span class="mf-section-title">ประมาณค่าใช้จ่าย (แจกแจงตามประเภท)</span>
  </div>
  <div class="mf-section-body">

    @if ($lockBudget)
      {{-- ขออนุมัติแล้ว = ดูอย่างเดียว --}}
      <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle mb-2">
          <thead>
            <tr>
              <th>ประเภทค่าใช้จ่าย</th>
              <th style="width:180px;" class="text-end">จำนวนเงิน</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($budgetLines as $line)
              <tr>
                <td>{{ $line['type'] }}</td>
                <td class="text-end">{{ number_format($line['amount'], 2) }}</td>
              </tr>
            @empty
              <tr><td colspan="2" class="text-center text-muted">ไม่ได้ตั้งงบไว้</td></tr>
            @endforelse
          </tbody>
          <tfoot>
            <tr>
              <td class="text-end fw-bold">รวม</td>
              <td class="text-end fw-bold">{{ number_format($budgetLines->sum('amount'), 2) }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
      <small class="text-muted"><i class="bx bx-lock-alt"></i>
        ขออนุมัติแล้ว — ไม่สามารถแก้ไขประมาณค่าใช้จ่าย / เป้า PP ได้</small>

    @else
      <div class="table-responsive budget-items-wrap">
        <table class="table table-sm table-bordered align-middle mb-2">
          <thead>
            <tr>
              <th>ประเภทค่าใช้จ่าย</th>
              <th style="width:180px;">จำนวนเงิน</th>
              <th style="width:60px;"></th>
            </tr>
          </thead>
          <tbody class="budget-items-body">
            @foreach ($budgetLines->isEmpty() ? [['type' => '', 'amount' => '']] : $budgetLines as $i => $line)
              <tr class="budget-item-row">
                <td>
                  <select name="budget_items[{{ $i }}][type]" class="form-select form-select-sm budget-type">
                    <option value="">— เลือกประเภท —</option>
                    @foreach ($budgetTypes as $t)
                      <option value="{{ $t }}" {{ $line['type'] === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                  </select>
                </td>
                <td>
                  <input name="budget_items[{{ $i }}][amount]"
                    class="form-control form-control-sm text-end money-input budget-amount"
                    placeholder="0.00" autocomplete="off"
                    value="{{ $line['amount'] === '' ? '' : number_format($line['amount'], 2) }}">
                </td>
                <td class="text-center">
                  <button type="button" class="btn btn-sm btn-outline-danger btnRemoveBudgetItem"><i class="bx bx-trash"></i></button>
                </td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <td class="text-end fw-bold">รวมประมาณค่าใช้จ่าย</td>
              <td><input type="text" class="form-control form-control-sm text-end fw-bold budget-total" readonly></td>
              <td></td>
            </tr>
          </tfoot>
        </table>
        <button type="button" class="btn btn-sm btn-outline-secondary btnAddBudgetItem">
          <i class="bx bx-plus me-1"></i> เพิ่มรายการ
        </button>
      </div>

      @if ($budgetLines->isNotEmpty() && (empty($place) ? false : $place->budgetItems->isEmpty()))
        <small class="text-muted d-block mt-2"><i class="bx bx-info-circle"></i>
          ข้อมูลเดิมยังไม่ได้แจกแจง — กรุณาเลือกประเภทให้ตรงกับยอดที่ตั้งไว้ ({{ $place->expense_type ?? '-' }})</small>
      @endif
    @endif

  </div>
</div>
