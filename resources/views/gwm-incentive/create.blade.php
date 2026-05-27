@extends('layouts/contentNavbarLayout')
@section('title', 'เพิ่ม GWM Incentive')

@section('page-script')
<script>
  window.gwmCreatePreSubId    = {{ $preSubId ?? 'null' }};
  window.gwmCreatePreMonth    = {{ $preMonth }};
  window.gwmCreatePreYear     = {{ $preYear }};
  window.gwmIncentiveStoreUrl = "{{ route('gwm-incentive.store') }}";
  window.gwmIncentiveCheckUrl = "{{ url('/api/gwm-incentive/check') }}";
</script>
@vite(['resources/assets/js/gwm-incentive.js'])
@endsection

@section('content')
<div class="gwm-create-page">

  {{-- Header --}}
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="mb-0">เพิ่มข้อมูล GWM Incentive</h4>
    <a href="{{ route('gwm-incentive.index') }}" class="btn btn-outline-secondary btn-sm">
      <i class="bx bx-arrow-back me-1"></i>กลับ
    </a>
  </div>

  {{-- Month/Year bar --}}
  <div class="card mb-3">
    <div class="card-body py-2">
      <div class="row g-2 align-items-center">
        <div class="col-auto">
          <label class="form-label mb-0 fw-semibold">เดือน/ปีที่ต้องการเพิ่ม</label>
        </div>
        <div class="col-auto">
          <select id="createMonth" class="form-select form-select-sm" style="min-width:120px">
            @foreach ($months as $num => $name)
              <option value="{{ $num }}" {{ $num == $preMonth ? 'selected' : '' }}>
                {{ str_pad($num, 2, '0', STR_PAD_LEFT) }} - {{ $name }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-auto">
          <input id="createYear" type="number" class="form-control form-control-sm"
            value="{{ $preYear }}" min="2020" max="2099" style="width:90px">
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3">

    {{-- LEFT: รายชื่อรุ่นรถ --}}
    <div class="col-md-4">
      <div class="card h-100">
        <div class="card-header py-2">
          <div class="d-flex align-items-center gap-2">
            <i class="bx bx-car"></i>
            <span class="fw-semibold">รุ่นรถทั้งหมด</span>
          </div>
          <input type="text" id="searchSubmodel" class="form-control form-control-sm mt-2"
            placeholder="ค้นหารุ่นรถ...">
        </div>
        <div class="card-body p-0" style="max-height:68vh; overflow-y:auto;">
          @foreach ($models as $model)
            @if (isset($subcarmodels[$model->id]) && $subcarmodels[$model->id]->isNotEmpty())
              <div class="gwm-model-group px-3 py-1 bg-light border-bottom fw-bold text-primary small">
                {{ $model->Name_EN }}
              </div>
              @foreach ($subcarmodels[$model->id] as $sub)
                <div class="gwm-sub-item d-flex justify-content-between align-items-center px-3 py-2 border-bottom"
                  data-sub-id="{{ $sub->id }}"
                  data-sub-name="{{ $model->Name_EN }} {{ $sub->name }}"
                  style="cursor:pointer">
                  <span>{{ $sub->name }}</span>
                  <span class="gwm-has-data-badge-{{ $sub->id }}"></span>
                </div>
              @endforeach
            @endif
          @endforeach
        </div>
      </div>
    </div>

    {{-- RIGHT: Form กรอกข้อมูล --}}
    <div class="col-md-8">
      <div class="card">
        <div class="card-header py-2">
          <span class="fw-semibold" id="formCarTitle">— กรุณาเลือกรุ่นรถจากรายการทางซ้าย —</span>
        </div>
        <div class="card-body">

          <form id="gwmCreateForm" action="{{ route('gwm-incentive.store') }}" method="POST">
            @csrf
            <input type="hidden" name="subcarmodel_id" id="formSubId">
            <input type="hidden" name="month" id="formMonth" value="{{ $preMonth }}">
            <input type="hidden" name="year"  id="formYear"  value="{{ $preYear }}">

            {{-- Incentive fields --}}
            <div id="formFields" style="display:none">
              <div class="row g-3">
                @php
                  $fields = [
                    ['name' => 'fixed',        'label' => 'Fixed %',         'icon' => 'bx-lock-alt',      'unit' => '%'],
                    ['name' => 'lt70',         'label' => '< 70%',           'icon' => 'bx-trending-down', 'unit' => '%'],
                    ['name' => 'gte70_lte85',  'label' => '70% ≤ x ≤ 85%',  'icon' => 'bx-bar-chart',     'unit' => '%'],
                    ['name' => 'gt85_lte100',  'label' => '85% < x ≤ 100%', 'icon' => 'bx-bar-chart',     'unit' => '%'],
                    ['name' => 'gt100_lte120', 'label' => '100% < x ≤ 120%','icon' => 'bx-trending-up',   'unit' => '%'],
                    ['name' => 'gte120',       'label' => 'x ≥ 120%',        'icon' => 'bx-rocket',        'unit' => '%'],
                    ['name' => 'max_val',      'label' => 'Max',             'icon' => 'bx-trophy',        'unit' => '%'],
                  ];
                @endphp

                @foreach ($fields as $f)
                  <div class="col-md-6">
                    <label class="form-label fw-semibold small">
                      <i class="bx {{ $f['icon'] }} me-1"></i>{{ $f['label'] }}
                    </label>
                    <div class="input-group">
                      <input type="number" step="0.01" min="0" max="100"
                        class="form-control text-end gwm-pct-input"
                        id="field_{{ $f['name'] }}"
                        name="{{ $f['name'] }}"
                        value="0" required>
                      <span class="input-group-text">%</span>
                    </div>
                  </div>
                @endforeach

                {{-- Monthly Target --}}
                <div class="col-md-6">
                  <label class="form-label fw-semibold small">
                    <i class="bx bx-target-lock me-1"></i>Monthly Target (คัน)
                  </label>
                  <div class="input-group">
                    <input type="number" min="0" step="1"
                      class="form-control text-end"
                      id="field_monthly_target"
                      name="monthly_target"
                      value="0" required>
                    <span class="input-group-text">คัน</span>
                  </div>
                </div>

              </div>

              <div class="d-flex justify-content-between align-items-center mt-4 pt-2 border-top">
                <div id="formStatusMsg" class="text-muted small"></div>
                <div class="d-flex gap-2">
                  <button type="button" id="btnResetFields" class="btn btn-outline-secondary">
                    <i class="bx bx-reset me-1"></i>ล้างค่า
                  </button>
                  <button type="button" id="btnSaveIncentive" class="btn btn-primary px-4">
                    <i class="bx bx-save me-1"></i>บันทึก
                  </button>
                </div>
              </div>
            </div>

            {{-- Placeholder --}}
            <div id="formPlaceholder" class="text-center text-muted py-5">
              <i class="bx bx-car" style="font-size:3rem; opacity:.3"></i>
              <p class="mt-2">เลือกรุ่นรถจากรายการทางซ้าย</p>
            </div>

          </form>
        </div>
      </div>
    </div>

  </div>
</div>

<style>
  .gwm-sub-item:hover { background: #f0f4ff; }
  .gwm-sub-item.active { background: #e7edff; border-left: 3px solid #696cff; font-weight: 600; }
  .gwm-pct-input { font-size: 1rem; }
</style>
@endsection
