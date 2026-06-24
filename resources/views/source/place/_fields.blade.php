{{-- ฟิลด์ร่วมของฟอร์มสถานที่ (input/edit) — รับ $place (null ตอนเพิ่ม) และ $offlineSources --}}
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
    <div class="mf-section-icon indigo">
      <i class="bx bx-store"></i>
    </div>
    <span class="mf-section-title">ข้อมูลสถานที่</span>
  </div>
  <div class="mf-section-body">
    <div class="row g-3">

      <div class="col-md-6">
        <label class="mf-label form-label">
          <i class="bx bx-sitemap"></i> แหล่งที่มาย่อย (Offline) <span class="text-danger">*</span>
        </label>
        <select name="salecar_type_id" class="form-select" required>
          <option value="">— เลือก —</option>
          @foreach ($offlineSources as $os)
            <option value="{{ $os->id }}" {{ ($place->salecar_type_id ?? '') == $os->id ? 'selected' : '' }}>
              {{ $os->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-md-6">
        <label class="mf-label form-label"><i class="bx bx-id-card"></i> LAS Number</label>
        <input type="text" class="form-control" name="las_number" autocomplete="off"
          value="{{ $place->las_number ?? '' }}" placeholder="เช่น RS-26-05-00385">
      </div>

      <div class="col-md-12">
        <label class="mf-label form-label">
          <i class="bx bx-map"></i> ระบุสถานที่ <span class="text-danger">*</span>
        </label>
        <input type="text" class="form-control" name="location" autocomplete="off" required
          value="{{ $place->location ?? '' }}" placeholder="เช่น AL (2 June) KOL อบต.ทุ่งสูง">
      </div>

      <div class="col-md-4">
        <label class="mf-label form-label"><i class="bx bx-receipt"></i> ประเภทค่าใช้จ่าย</label>
        <select name="expense_type" class="form-select">
          <option value="">— เลือก —</option>
          @foreach (config('source.expense_types', []) as $et)
            <option value="{{ $et }}" {{ ($place->expense_type ?? '') === $et ? 'selected' : '' }}>
              {{ $et }}</option>
          @endforeach
        </select>
      </div>

      <div class="col-md-4">
        <label class="mf-label form-label"><i class="bx bx-money"></i> ประมาณค่าใช้จ่าย</label>
        <div class="input-group">
          <span class="input-group-text">฿</span>
          <input type="text" class="form-control text-end money-input" name="cost" autocomplete="off"
            value="{{ isset($place->cost) ? number_format($place->cost, 2) : '' }}" placeholder="0.00">
        </div>
      </div>

      <div class="col-md-4">
        <label class="mf-label form-label"><i class="bx bx-target-lock"></i> เป้า PP <small class="text-muted">(จำนวนลูกค้า)</small></label>
        <input type="number" min="0" step="1" class="form-control text-end" name="target" autocomplete="off"
          value="{{ isset($place->target) ? (int) $place->target : '' }}" placeholder="0">
      </div>

      <div class="col-md-6">
        <label class="mf-label form-label"><i class="bx bx-calendar"></i> วันเริ่มงาน</label>
        <input type="date" class="form-control" name="start_date"
          value="{{ optional($place?->start_date)->format('Y-m-d') }}">
      </div>

      <div class="col-md-6">
        <label class="mf-label form-label"><i class="bx bx-calendar-check"></i> วันจบงาน</label>
        <input type="date" class="form-control" name="end_date"
          value="{{ optional($place?->end_date)->format('Y-m-d') }}">
      </div>

    </div>
  </div>
</div>
