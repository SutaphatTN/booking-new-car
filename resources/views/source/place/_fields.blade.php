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

      <div class="col-md-6">
        <label for="{{ $pfx }}_expense_type" class="mf-label form-label"><i class="bx bx-receipt"></i> ประเภทค่าใช้จ่าย</label>
        <select id="{{ $pfx }}_expense_type" name="expense_type" class="form-select">
          <option value="">— เลือก —</option>
          @foreach (config('source.expense_types', []) as $et)
            <option value="{{ $et }}" {{ ($place->expense_type ?? '') === $et ? 'selected' : '' }}>
              {{ $et }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-md-4">
        <label for="{{ $pfx }}_cost" class="mf-label form-label"><i class="bx bx-money"></i> ประมาณค่าใช้จ่าย</label>
        <div class="input-group">
          <span class="input-group-text">฿</span>
          <input id="{{ $pfx }}_cost" type="text" class="form-control text-end money-input" name="cost" autocomplete="off"
            value="{{ isset($place->cost) ? number_format($place->cost, 2) : '' }}" placeholder="0.00"
            {{ $lockBudget ? 'readonly' : '' }}>
        </div>
      </div>

      <div class="col-md-2">
        <label for="{{ $pfx }}_target" class="mf-label form-label"><i class="bx bx-target-lock"></i> เป้า PP</label>
        <input id="{{ $pfx }}_target" type="number" min="0" step="1" class="form-control text-end" name="target" autocomplete="off"
          value="{{ isset($place->target) ? (int) $place->target : '' }}" placeholder="0"
          {{ $lockBudget ? 'readonly' : '' }}>
      </div>

      @if ($lockBudget)
        <div class="col-12">
          <small class="text-muted"><i class="bx bx-lock-alt"></i>
            ขออนุมัติแล้ว — ไม่สามารถแก้ไขประมาณค่าใช้จ่าย / เป้า PP ได้</small>
        </div>
      @endif

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
