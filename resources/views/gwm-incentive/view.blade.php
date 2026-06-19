@extends('layouts/contentNavbarLayout')
@section('title', 'GWM Incentive Settings')

@section('page-script')
<script>
  window.gwmIncentiveUpsertUrl = "{{ route('gwm-incentive.upsert-row') }}";
  window.gwmKpiStoreUrl        = "{{ route('gwm-incentive.kpi.store') }}";
</script>
@vite(['resources/assets/js/gwm-incentive.js'])
@endsection

@section('content')
<div class="gwm-view-page">

  <div class="row">
    <div class="col-12">
      <div class="card tbl-card">

        {{-- Card header --}}
        <div class="po-card-header d-flex align-items-center gap-3">
          <div class="po-hd-icon">
            <i class="bx bx-award fs-4 text-white"></i>
          </div>
          <div>
            <div class="text-white fw-bold mf-hd-title">ตั้งค่า GWM Incentive</div>
            <div class="text-white mf-hd-sub">GWM Incentive Settings</div>
          </div>
        </div>

        <div class="card-body pt-3">

          {{-- Filter bar (GET form → reload page) --}}
          <form method="GET" action="{{ route('gwm-incentive.index') }}"
                class="po-filter-bar d-flex align-items-center gap-3 mb-3 flex-wrap">
            <div class="d-flex align-items-center gap-2">
              <label class="form-label mb-0">เดือน</label>
              <select name="month" class="form-select form-select-sm" style="min-width:110px">
                @foreach ($months as $num => $name)
                  <option value="{{ $num }}" {{ $num == $currentMonth ? 'selected' : '' }}>
                    {{ str_pad($num, 2, '0', STR_PAD_LEFT) }} - {{ $name }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="d-flex align-items-center gap-2">
              <label class="form-label mb-0">ปี (ค.ศ.)</label>
              <input name="year" type="number" class="form-control form-control-sm"
                value="{{ $currentYear }}" min="2020" max="2099" style="width:90px">
            </div>
            <button type="submit" class="btn btn-sm btn-primary">
              <i class="bx bx-search me-1"></i>แสดง
            </button>
          </form>

          {{-- Inline editable table --}}
          <div class="table-responsive">
            <table class="table table-bordered tbl-table tbl-styled">
              <thead>
                <tr>
                  <th class="tbl-th-no">No.</th>
                  <th>รุ่นรถ</th>
                  <th style="min-width:88px">Fixed (%)</th>
                  <th style="min-width:88px">&lt;70%</th>
                  <th style="min-width:88px">70%≤x≤85%</th>
                  <th style="min-width:88px">85%&lt;x≤100%</th>
                  <th style="min-width:95px">100%&lt;x≤120%</th>
                  <th style="min-width:88px">x≥120%</th>
                  <th style="min-width:88px">Max (%)</th>
                  <th style="min-width:95px">Target (คัน)</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($subcarmodels as $index => $sub)
                  @php $inc = $incentives[$sub->id] ?? null; @endphp
                  <tr class="gwm-row align-middle"
                      data-sub-id="{{ $sub->id }}"
                      data-month="{{ $currentMonth }}"
                      data-year="{{ $currentYear }}">
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="small" style="white-space:nowrap">
                      {{ $sub->model->Name_EN ?? '' }} {{ $sub->name }}
                    </td>
                    <td><input type="text" inputmode="decimal"
                      class="form-control form-control-sm text-end gwm-row-input" name="fixed"
                      value="{{ $inc ? $inc->fixed : 0 }}"></td>
                    <td><input type="text" inputmode="decimal"
                      class="form-control form-control-sm text-end gwm-row-input" name="lt70"
                      value="{{ $inc ? $inc->lt70 : 0 }}"></td>
                    <td><input type="text" inputmode="decimal"
                      class="form-control form-control-sm text-end gwm-row-input" name="gte70_lte85"
                      value="{{ $inc ? $inc->gte70_lte85 : 0 }}"></td>
                    <td><input type="text" inputmode="decimal"
                      class="form-control form-control-sm text-end gwm-row-input" name="gt85_lte100"
                      value="{{ $inc ? $inc->gt85_lte100 : 0 }}"></td>
                    <td><input type="text" inputmode="decimal"
                      class="form-control form-control-sm text-end gwm-row-input" name="gt100_lte120"
                      value="{{ $inc ? $inc->gt100_lte120 : 0 }}"></td>
                    <td><input type="text" inputmode="decimal"
                      class="form-control form-control-sm text-end gwm-row-input" name="gte120"
                      value="{{ $inc ? $inc->gte120 : 0 }}"></td>
                    <td><input type="text" inputmode="decimal"
                      class="form-control form-control-sm text-end gwm-row-input" name="max_val"
                      value="{{ $inc ? $inc->max_val : 0 }}"></td>
                    <td><input type="text" inputmode="numeric"
                      class="form-control form-control-sm text-end gwm-row-input" name="monthly_target"
                      value="{{ $inc ? $inc->monthly_target : 0 }}"></td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>

        </div>
      </div>
    </div>
  </div>

  {{-- KPI Section --}}
  <div class="row mt-3">
    <div class="col-12">
      <div class="card">
        <div class="card-header py-2 d-flex align-items-center gap-2 mt-4">
          <i class="bx bx-award text-primary"></i>
          <span class="fw-semibold">ตั้งค่า KPI รายเดือน</span>
          <span class="text-muted small ms-2">
            ({{ str_pad($currentMonth, 2, '0', STR_PAD_LEFT) }} - {{ $months[$currentMonth] }} {{ $currentYear }})
          </span>
        </div>
        <div class="card-body">
          <form id="gwmKpiForm">
            @csrf
            <input type="hidden" name="month" value="{{ $currentMonth }}">
            <input type="hidden" name="year"  value="{{ $currentYear }}">

            <div class="row g-3 align-items-end">
              <div class="col-md-6">
                <label class="form-label fw-semibold small">
                  <i class="bx bx-line-chart me-1"></i>Sale KPI
                </label>
                <div class="input-group">
                  <input type="number" step="0.01" min="0" max="100"
                    class="form-control text-end" name="sale_kpi" id="kpi_sale_kpi"
                    value="{{ $kpi ? $kpi->sale_kpi : 0 }}">
                  <span class="input-group-text">%</span>
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold small">
                  <i class="bx bx-smile me-1"></i>SSI
                </label>
                <div class="input-group">
                  <input type="number" step="0.01" min="0" max="100"
                    class="form-control text-end" name="ssi" id="kpi_ssi"
                    value="{{ $kpi ? $kpi->ssi : 0 }}">
                  <span class="input-group-text">%</span>
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold small">
                  <i class="bx bx-wrench me-1"></i>After Sale KPI
                </label>
                <div class="input-group">
                  <input type="number" step="0.01" min="0" max="100"
                    class="form-control text-end" name="after_sale_kpi" id="kpi_after_sale_kpi"
                    value="{{ $kpi ? $kpi->after_sale_kpi : 0 }}">
                  <span class="input-group-text">%</span>
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold small">
                  <i class="bx bx-heart me-1"></i>CSI
                </label>
                <div class="input-group">
                  <input type="number" step="0.01" min="0" max="100"
                    class="form-control text-end" name="csi" id="kpi_csi"
                    value="{{ $kpi ? $kpi->csi : 0 }}">
                  <span class="input-group-text">%</span>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
              <div id="kpiStatusMsg" class="text-muted small"></div>
              <button type="button" id="btnSaveKpi" class="btn btn-success px-4">
                <i class="bx bx-save me-1"></i>บันทึก KPI
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

</div>

<style>
  @keyframes gwmRowSaved {
    0%   { background-color: #d1fae5; }
    70%  { background-color: #d1fae5; }
    100% { background-color: transparent; }
  }
  @keyframes gwmRowError {
    0%   { background-color: #fee2e2; }
    70%  { background-color: #fee2e2; }
    100% { background-color: transparent; }
  }
  .gwm-row.row-saved  { animation: gwmRowSaved 1.4s ease forwards; }
  .gwm-row.row-error  { animation: gwmRowError 1.4s ease forwards; }

  /* ── Mobile adjustments ── */
  @media (max-width: 575.98px) {
    .gwm-row-input { font-size: .78rem; padding: .2rem .3rem; }
    .po-filter-bar { row-gap: .4rem; }
  }
</style>

<div id="gwmLoadingOverlay" style="display:flex;">
  <div class="ct-loading-box">
    <div class="spinner-border text-primary" role="status" style="width:1.4rem;height:1.4rem;"></div>
    <span>กำลังโหลด...</span>
  </div>
</div>
@endsection
