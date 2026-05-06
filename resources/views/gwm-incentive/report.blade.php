@extends('layouts/contentNavbarLayout')
@section('title', 'รายงาน GWM Incentive')

@section('page-script')
<script>
  $(document).ready(function () {

    // Filter button
    $('#btnFilterReport').on('click', function () {
      const month = $('#filterMonth').val();
      const year  = $('#filterYear').val();
      window.location.href = '/gwm-incentive/report?month=' + month + '&year=' + year;
    });

    // Export button
    $('#btnExportExcel').on('click', function () {
      const month = $('#filterMonth').val();
      const year  = $('#filterYear').val();
      window.location.href = '/gwm-incentive/report/export?month=' + month + '&year=' + year;
    });

  });
</script>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-3">
  <h4 class="mb-0">รายงาน GWM Incentive</h4>
  <a href="{{ route('gwm-incentive.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="bx bx-arrow-back me-1"></i>กลับ
  </a>
</div>

{{-- Filter --}}
<div class="card mb-3">
  <div class="card-body py-2">
    <div class="row g-2 align-items-center">
      <div class="col-auto">
        <label class="form-label mb-0 fw-semibold">เดือน/ปี</label>
      </div>
      <div class="col-auto">
        <select id="filterMonth" class="form-select form-select-sm" style="min-width:120px">
          @foreach ($months as $num => $name)
            <option value="{{ $num }}" {{ $num == $currentMonth ? 'selected' : '' }}>
              {{ str_pad($num, 2, '0', STR_PAD_LEFT) }} - {{ $name }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-auto">
        <input id="filterYear" type="number" class="form-control form-control-sm"
          value="{{ $currentYear }}" min="2020" max="2099" style="width:90px">
      </div>
      <div class="col-auto">
        <button id="btnFilterReport" class="btn btn-sm btn-primary">
          <i class="bx bx-search me-1"></i>แสดง
        </button>
      </div>
      <div class="col-auto ms-auto">
        <button id="btnExportExcel" class="btn btn-sm btn-success">
          <i class="bx bx-file me-1"></i>Export Excel
        </button>
      </div>
    </div>
  </div>
</div>

{{-- KPI Summary --}}
<div class="card mb-3">
  <div class="card-header py-2 d-flex align-items-center gap-2">
    <i class="bx bx-award text-primary"></i>
    <span class="fw-semibold">KPI เดือน {{ $months[$currentMonth] ?? $currentMonth }} {{ $currentYear }}</span>
  </div>
  <div class="card-body py-2">
    @if ($kpi)
      <div class="row g-3">
        <div class="col-auto">
          <span class="text-muted small">Sale KPI</span>
          <div class="fw-bold">{{ number_format($kpi->sale_kpi, 2) }}%</div>
        </div>
        <div class="col-auto">
          <span class="text-muted small">SSI</span>
          <div class="fw-bold">{{ number_format($kpi->ssi, 2) }}%</div>
        </div>
        <div class="col-auto">
          <span class="text-muted small">After Sale KPI</span>
          <div class="fw-bold">{{ number_format($kpi->after_sale_kpi, 2) }}%</div>
        </div>
        <div class="col-auto">
          <span class="text-muted small">CSI</span>
          <div class="fw-bold">{{ number_format($kpi->csi, 2) }}%</div>
        </div>
        <div class="col-auto border-start ps-3">
          <span class="text-muted small">รวม KPI</span>
          <div class="fw-bold text-primary">{{ number_format($kpiTotal, 2) }}%</div>
        </div>
      </div>
    @else
      <span class="text-muted small"><i class="bx bx-info-circle me-1"></i>ยังไม่มีข้อมูล KPI สำหรับเดือนนี้</span>
    @endif
  </div>
</div>

{{-- Report Table --}}
<div class="card">
  <div class="card-header py-2">
    <span class="fw-semibold">ผลการคำนวณ Incentive</span>
    <span class="text-muted small ms-2">({{ $rows->count() }} รุ่น)</span>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-bordered table-hover mb-0" style="font-size:0.85rem">
        <thead class="table-dark">
          <tr>
            <th rowspan="2" class="text-center align-middle" style="min-width:30px">No.</th>
            <th rowspan="2" class="align-middle" style="min-width:160px">รุ่นรถ</th>
            <th rowspan="2" class="text-center align-middle">ขายได้<br>(คัน)</th>
            <th rowspan="2" class="text-center align-middle">Target<br>(คัน)</th>
            <th rowspan="2" class="text-center align-middle">%ทำได้</th>
            <th rowspan="2" class="text-center align-middle">ราคารวม<br>(บาท)</th>
            <th colspan="6" class="text-center bg-warning bg-opacity-25">Incentive Tier (%)</th>
            <th rowspan="2" class="text-center align-middle">Fixed<br>(%)</th>
            <th rowspan="2" class="text-center align-middle">Tier Rate<br>ที่ได้</th>
            <th rowspan="2" class="text-center align-middle">KPI<br>(%)</th>
            <th rowspan="2" class="text-center align-middle">รวม<br>(%)</th>
            <th rowspan="2" class="text-center align-middle">Max<br>(%)</th>
            <th rowspan="2" class="text-center align-middle">Incentive<br>(%)</th>
            <th rowspan="2" class="text-center align-middle">ยอด Incentive<br>(บาท)</th>
          </tr>
          <tr>
            <th class="text-center" style="background:#fff3cd; font-size:0.75rem">&lt;70%</th>
            <th class="text-center" style="background:#fff3cd; font-size:0.75rem">70-85%</th>
            <th class="text-center" style="background:#fff3cd; font-size:0.75rem">85-100%</th>
            <th class="text-center" style="background:#fff3cd; font-size:0.75rem">100-120%</th>
            <th class="text-center" style="background:#fff3cd; font-size:0.75rem">≥120%</th>
            <th class="text-center" style="background:#fff3cd; font-size:0.75rem">Max</th>
          </tr>
        </thead>
        <tbody>
          @php $totalAmount = 0; $totalPriceSum = 0; @endphp
          @forelse ($rows as $i => $r)
            @php
              $totalAmount   += $r['amount'];
              $totalPriceSum += $r['price_total'];
              // highlight active tier cell
              $tiers = ['lt70','gte70_lte85','gt85_lte100','gt100_lte120','gte120'];
              $achieve = $r['achieve_pct'];
              $activeTier = match(true) {
                  $achieve < 70                    => 'lt70',
                  $achieve >= 70 && $achieve <= 85 => 'gte70_lte85',
                  $achieve > 85 && $achieve <= 100 => 'gt85_lte100',
                  $achieve > 100 && $achieve <= 120 => 'gt100_lte120',
                  default                          => 'gte120',
              };
            @endphp
            <tr>
              <td class="text-center">{{ $i + 1 }}</td>
              <td>
                <div class="fw-semibold">{{ $r['model_name'] }}</div>
                <div class="text-muted small">{{ $r['sub_name'] }}</div>
              </td>
              <td class="text-center fw-bold {{ $r['count'] > 0 ? 'text-success' : 'text-muted' }}">
                {{ number_format($r['count']) }}
              </td>
              <td class="text-center">{{ number_format($r['target']) }}</td>
              <td class="text-center">
                @if ($r['target'] > 0)
                  <span class="badge {{ $r['achieve_pct'] >= 100 ? 'bg-success' : ($r['achieve_pct'] >= 70 ? 'bg-warning text-dark' : 'bg-danger') }}">
                    {{ number_format($r['achieve_pct'], 1) }}%
                  </span>
                @else
                  <span class="text-muted">-</span>
                @endif
              </td>
              <td class="text-end">{{ number_format($r['price_total'], 2) }}</td>

              {{-- Tier columns --}}
              @foreach (['lt70','gte70_lte85','gt85_lte100','gt100_lte120','gte120'] as $tier)
                <td class="text-center {{ $activeTier === $tier ? 'table-warning fw-bold' : '' }}">
                  {{ number_format($r[$tier], 2) }}%
                </td>
              @endforeach

              {{-- max_val in tier header --}}
              <td class="text-center">{{ number_format($r['max_val'], 2) }}%</td>

              <td class="text-center">{{ number_format($r['fixed'], 2) }}%</td>
              <td class="text-center fw-bold text-primary">{{ number_format($r['tier_rate'], 2) }}%</td>
              <td class="text-center">{{ number_format($r['kpi_total'], 2) }}%</td>
              <td class="text-center">{{ number_format($r['total_pct'], 2) }}%</td>
              <td class="text-center">{{ number_format($r['max_val'], 2) }}%</td>
              <td class="text-center fw-bold {{ $r['total_pct'] > $r['max_val'] ? 'text-danger' : 'text-success' }}">
                {{ number_format($r['capped_pct'], 2) }}%
                @if ($r['total_pct'] > $r['max_val'])
                  <i class="bx bx-info-circle text-danger ms-1" title="ถูก cap ที่ Max"></i>
                @endif
              </td>
              <td class="text-end fw-bold text-primary">{{ number_format($r['amount'], 2) }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="19" class="text-center text-muted py-4">
                <i class="bx bx-info-circle me-1"></i>ไม่มีข้อมูล Incentive สำหรับเดือนนี้
                <div class="small mt-1">กรุณาตั้งค่า Incentive ก่อน</div>
              </td>
            </tr>
          @endforelse
        </tbody>
        @if ($rows->count() > 0)
        <tfoot class="table-secondary fw-bold">
          <tr>
            <td colspan="5" class="text-end">รวมทั้งหมด</td>
            <td class="text-end">{{ number_format($totalPriceSum, 2) }}</td>
            <td colspan="12"></td>
            <td class="text-end text-primary">{{ number_format($totalAmount, 2) }}</td>
          </tr>
        </tfoot>
        @endif
      </table>
    </div>
  </div>
</div>

@endsection
