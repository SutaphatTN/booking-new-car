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

<div class="row">
  <div class="col-12">
    <div class="card tbl-card">

      {{-- ── Card header ── --}}
      <div class="po-card-header d-flex align-items-center gap-3">
        <div class="po-hd-icon">
          <i class="bx bx-file-blank fs-4 text-white"></i>
        </div>
        <div class="flex-grow-1">
          <div class="text-white fw-bold mf-hd-title">รายงาน GWM Incentive</div>
          <div class="text-white mf-hd-sub">GWM Incentive Report</div>
        </div>
        <a href="{{ route('gwm-incentive.index') }}" class="btn btn-sm btn-danger">
          <i class="bx bx-arrow-back me-1"></i>กลับ
        </a>
      </div>

      <div class="card-body pt-3">

        {{-- ── Filter bar ── --}}
        <div class="po-filter-bar d-flex align-items-center gap-3">
          <div class="d-flex align-items-center gap-2">
            <label class="form-label mb-0">เดือน/ปี</label>
            <select id="filterMonth" class="form-select form-select-sm" style="min-width:120px">
              @foreach ($months as $num => $name)
                <option value="{{ $num }}" {{ $num == $currentMonth ? 'selected' : '' }}>
                  {{ str_pad($num, 2, '0', STR_PAD_LEFT) }} - {{ $name }}
                </option>
              @endforeach
            </select>
            <input id="filterYear" type="number" class="form-control form-control-sm"
              value="{{ $currentYear }}" min="2020" max="2099" style="width:90px">
          </div>
          <button id="btnFilterReport" class="btn btn-sm btn-primary">
            <i class="bx bx-search me-1"></i>แสดง
          </button>
          <div class="ms-auto">
            <button id="btnExportExcel" class="btn btn-sm btn-success">
              <i class="bx bx-file me-1"></i>Export Excel
            </button>
          </div>
        </div>

        {{-- ── KPI Summary ── --}}
        <div class="border rounded-3 p-3 mb-3" style="background:#f8fafc; border-color:#c7d2fe !important;">
          <div class="d-flex align-items-center gap-2 mb-2">
            <i class="bx bx-award text-primary"></i>
            <span class="fw-semibold" style="color:#3730a3">
              KPI เดือน {{ $months[$currentMonth] ?? $currentMonth }} {{ $currentYear }}
            </span>
          </div>
          @if ($kpi)
            <div class="d-flex flex-wrap gap-4">
              <div>
                <div class="text-muted small">Sale KPI</div>
                <div class="fw-bold fs-6">{{ number_format($kpi->sale_kpi, 2) }}%</div>
              </div>
              <div>
                <div class="text-muted small">SSI</div>
                <div class="fw-bold fs-6">{{ number_format($kpi->ssi, 2) }}%</div>
              </div>
              <div>
                <div class="text-muted small">After Sale KPI</div>
                <div class="fw-bold fs-6">{{ number_format($kpi->after_sale_kpi, 2) }}%</div>
              </div>
              <div>
                <div class="text-muted small">CSI</div>
                <div class="fw-bold fs-6">{{ number_format($kpi->csi, 2) }}%</div>
              </div>
              <div class="border-start ps-4">
                <div class="text-muted small">รวม KPI</div>
                <div class="fw-bold fs-5 text-primary">{{ number_format($kpiTotal, 2) }}%</div>
              </div>
            </div>
          @else
            <span class="text-muted small"><i class="bx bx-info-circle me-1"></i>ยังไม่มีข้อมูล KPI สำหรับเดือนนี้</span>
          @endif
        </div>

        {{-- ── Report Table ── --}}
        <div class="d-flex align-items-center gap-2 mb-2">
          <span class="fw-semibold" style="color:#3730a3">ผลการคำนวณ Incentive</span>
          <span class="text-muted small">({{ $rows->count() }} รุ่น)</span>
        </div>
        <div class="table-responsive">
          <table class="table table-bordered tbl-table tbl-styled mb-0" style="font-size:0.83rem">
            <thead>
              <tr>
                <th class="tbl-th-no" rowspan="2">No.</th>
                <th rowspan="2" style="min-width:160px">รุ่นรถ</th>
                <th rowspan="2" class="text-center">ขายได้<br>(คัน)</th>
                <th rowspan="2" class="text-center">Target<br>(คัน)</th>
                <th rowspan="2" class="text-center">%ทำได้</th>
                <th rowspan="2" class="text-center">ราคารวม<br>(บาท)</th>
                <th colspan="6" class="text-center" style="background:#ede9fe; color:#3730a3">Incentive Tier (%)</th>
                <th rowspan="2" class="text-center">Fixed<br>(%)</th>
                <th rowspan="2" class="text-center">Tier Rate<br>ที่ได้</th>
                <th rowspan="2" class="text-center">KPI<br>(%)</th>
                <th rowspan="2" class="text-center">รวม<br>(%)</th>
                <th rowspan="2" class="text-center">Max<br>(%)</th>
                <th rowspan="2" class="text-center">Incentive<br>(%)</th>
                <th rowspan="2" class="text-center">ยอด Incentive<br>(บาท)</th>
              </tr>
              <tr>
                <th class="text-center" style="background:#ede9fe; color:#4338ca; font-size:0.75rem">&lt;70%</th>
                <th class="text-center" style="background:#ede9fe; color:#4338ca; font-size:0.75rem">70-85%</th>
                <th class="text-center" style="background:#ede9fe; color:#4338ca; font-size:0.75rem">85-100%</th>
                <th class="text-center" style="background:#ede9fe; color:#4338ca; font-size:0.75rem">100-120%</th>
                <th class="text-center" style="background:#ede9fe; color:#4338ca; font-size:0.75rem">≥120%</th>
                <th class="text-center" style="background:#ede9fe; color:#4338ca; font-size:0.75rem">Max</th>
              </tr>
            </thead>
            <tbody>
              @php $totalAmount = 0; $totalPriceSum = 0; @endphp
              @forelse ($rows as $i => $r)
                @php
                  $totalAmount   += $r['amount'];
                  $totalPriceSum += $r['price_total'];
                  $achieve = $r['achieve_pct'];
                  $activeTier = match(true) {
                      $achieve < 70                     => 'lt70',
                      $achieve >= 70 && $achieve <= 85  => 'gte70_lte85',
                      $achieve > 85 && $achieve <= 100  => 'gt85_lte100',
                      $achieve > 100 && $achieve <= 120 => 'gt100_lte120',
                      default                           => 'gte120',
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

                  @foreach (['lt70','gte70_lte85','gt85_lte100','gt100_lte120','gte120'] as $tier)
                    <td class="text-center {{ $activeTier === $tier ? 'fw-bold' : '' }}"
                      style="{{ $activeTier === $tier ? 'background:#ede9fe; color:#3730a3;' : '' }}">
                      {{ number_format($r[$tier], 2) }}%
                    </td>
                  @endforeach
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
            <tfoot>
              <tr style="background:#ede9fe">
                <td colspan="5" class="text-end fw-bold" style="color:#3730a3">รวมทั้งหมด</td>
                <td class="text-end fw-bold" style="color:#3730a3">{{ number_format($totalPriceSum, 2) }}</td>
                <td colspan="12"></td>
                <td class="text-end fw-bold text-primary">{{ number_format($totalAmount, 2) }}</td>
              </tr>
            </tfoot>
            @endif
          </table>
        </div>

      </div>
    </div>
  </div>
</div>

@endsection
