@extends('layouts/contentNavbarLayout')
@section('title', 'GWM Incentive Settings')

@section('page-script')
<script>
  window.gwmIncentiveCurrentMonth = {{ $currentMonth }};
  window.gwmIncentiveCurrentYear  = {{ $currentYear }};
</script>
@vite(['resources/assets/js/gwm-incentive.js'])
@endsection

@section('content')
<div class="inputGwmIncentiveModal"></div>
<div class="editGwmIncentiveModal"></div>

<div class="row">
  <div class="col-12">
    <div class="card tbl-card">

      {{-- ── Card header ── --}}
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

        {{-- ── Filter bar ── --}}
        <div class="po-filter-bar d-flex align-items-center gap-3">
          <div class="d-flex align-items-center gap-2">
            <label class="form-label mb-0">เดือน</label>
            <select id="filterMonth" class="form-select form-select-sm" style="min-width:110px">
              @foreach ($months as $num => $name)
                <option value="{{ $num }}" {{ $num == $currentMonth ? 'selected' : '' }}>
                  {{ str_pad($num, 2, '0', STR_PAD_LEFT) }} - {{ $name }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="d-flex align-items-center gap-2">
            <label class="form-label mb-0">ปี (ค.ศ.)</label>
            <input id="filterYear" type="number" class="form-control form-control-sm"
              value="{{ $currentYear }}" min="2020" max="2099" style="width:90px">
          </div>
          <button id="btnFilterIncentive" class="btn btn-sm btn-primary">
            <i class="bx bx-search me-1"></i>แสดง
          </button>
          <div class="ms-auto">
            <button class="btn btn-secondary btn-sm btnInputGwmIncentive">
              <i class="bx bx-plus me-1"></i>เพิ่ม
            </button>
          </div>
        </div>

        {{-- ── Table ── --}}
        <div class="table-responsive">
          <table class="table table-bordered tbl-table tbl-styled gwmIncentiveTable">
            <thead>
              <tr>
                <th class="tbl-th-no">No.</th>
                <th>รุ่นรถ</th>
                <th>Fixed</th>
                <th>&lt;70%</th>
                <th>70%≤x≤85%</th>
                <th>85%&lt;x≤100%</th>
                <th>100%&lt;x≤120%</th>
                <th>x≥120%</th>
                <th>Max</th>
                <th>Target (คัน)</th>
                <th class="tbl-th-action" style="width:120px">Action</th>
              </tr>
            </thead>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection
