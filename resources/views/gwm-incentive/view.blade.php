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
    <div class="card">
      <h4 class="card-header">ตั้งค่า GWM Incentive</h4>
      <div class="card-body">

        {{-- Filter --}}
        <div class="row g-2 mb-3 align-items-end">
          <div class="col-auto">
            <label class="form-label mb-1">เดือน</label>
            <select id="filterMonth" class="form-select form-select-sm" style="min-width:110px">
              @foreach ($months as $num => $name)
                <option value="{{ $num }}" {{ $num == $currentMonth ? 'selected' : '' }}>
                  {{ str_pad($num, 2, '0', STR_PAD_LEFT) }} - {{ $name }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-auto">
            <label class="form-label mb-1">ปี (ค.ศ.)</label>
            <input id="filterYear" type="number" class="form-control form-control-sm"
              value="{{ $currentYear }}" min="2020" max="2099" style="width:90px">
          </div>
          <div class="col-auto">
            <button id="btnFilterIncentive" class="btn btn-sm btn-primary">
              <i class="bx bx-search me-1"></i>แสดง
            </button>
          </div>
        </div>

        <div class="table-responsive text-nowrap">
          <div class="d-flex justify-content-end mb-2">
            <button class="btn btn-secondary btnInputGwmIncentive">
              <i class="bx bx-plus me-1"></i>เพิ่ม
            </button>
          </div>
          <table class="table table-bordered gwmIncentiveTable">
            <thead>
              <tr>
                <th>No.</th>
                <th>รุ่นรถ</th>
                <th>Fixed</th>
                <th>&lt;70%</th>
                <th>70%≤x≤85%</th>
                <th>85%&lt;x≤100%</th>
                <th>100%&lt;x≤120%</th>
                <th>x≥120%</th>
                <th>Max</th>
                <th>Target (คัน)</th>
                <th width="120px">Action</th>
              </tr>
            </thead>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection
