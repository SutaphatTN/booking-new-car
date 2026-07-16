@extends('layouts/contentNavbarLayout')
@section('title', 'รายการ FP')

@php
  $showOption   = $brand == 1;   // option เฉพาะ brand 1
  $showInterior = $brand == 2;   // สีภายใน เฉพาะ brand 2
  $grandTotal   = collect($rows)->sum(fn ($r) => $r['totalInterest'] ?? 0);
@endphp

@section('content')
  <div class="row">
    <div class="col-12">
      <div class="card tbl-card">

        {{-- ── Card header ── --}}
        <div class="po-card-header d-flex align-items-center gap-3">
          <div class="po-hd-icon">
            <i class="bx bx-list-ul fs-4 text-white"></i>
          </div>
          <div>
            <div class="text-white fw-bold mf-hd-title">รายการ FP</div>
            <div class="text-white mf-hd-sub">Floor Plan — FP List</div>
          </div>
        </div>

        <div class="card-body pt-3 fp-list">

          {{-- ── ฟิลเตอร์ สถานะ + เดือน (รอบ 16-15 ตาม Billing date) ── --}}
          <form method="GET" action="{{ route('floor-plan.fp') }}" id="fpFilterForm">
            <div class="fp-filter mb-2">
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="fp-chip fp-chip--period"><i class="bx bx-calendar-event"></i> งวด : {{ $periodLabel }}</span>
              </div>
              <div class="d-flex align-items-center gap-3 flex-wrap">
                <div class="d-flex align-items-center gap-2">
                  <label for="status" class="fw-semibold text-muted mb-0 small text-nowrap">สถานะ</label>
                  <select id="status" name="status" class="form-select form-select-sm" style="max-width:150px;">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>ทั้งหมด</option>
                    <option value="closed" {{ $status === 'closed' ? 'selected' : '' }}>ปิดแล้ว</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>รอปิด FP</option>
                  </select>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <label for="month" class="fw-semibold text-muted mb-0 small text-nowrap">เดือน (Billing)</label>
                  <input type="month" id="month" name="month" class="form-control form-control-sm"
                    style="max-width:170px;" value="{{ $month }}" {{ $status === 'pending' ? 'disabled' : '' }}>
                </div>
              </div>
            </div>
            <div class="text-muted small mb-3">
              <i class="bx bx-info-circle"></i>
              กรอง "ปิดแล้ว" ตามงวด Billing date (รอบ 16–15) — <b>รอปิด FP แสดงเสมอ</b> (ยกเว้นเลือกสถานะ "ปิดแล้ว")
            </div>
          </form>

          <div class="d-flex align-items-center gap-2 flex-wrap mb-3">
            <span class="fp-chip fp-chip--period"><i class="bx bx-car"></i> {{ count($rows) }} คัน</span>
            <span class="fp-chip fp-chip--total ms-auto">
              <i class="bx bx-money"></i> รวมดอกเบี้ยที่ต้องจ่าย : {{ number_format($grandTotal, 2) }} ฿
            </span>
          </div>

          <div class="table-responsive">
            <table class="table table-bordered fp-table align-middle mb-0">
              <thead>
                <tr>
                  <th rowspan="2" class="fp-th-no">No.</th>
                  <th rowspan="2">รุ่นหลัก</th>
                  <th rowspan="2">รุ่นย่อย</th>
                  <th rowspan="2">VIN Number</th>
                  <th rowspan="2">Billing date</th>
                  <th rowspan="2">ปี</th>
                  @if ($showOption)
                    <th rowspan="2">Option</th>
                  @endif
                  <th rowspan="2">สี</th>
                  @if ($showInterior)
                    <th rowspan="2">สีภายใน</th>
                  @endif
                  <th rowspan="2">เลขเครื่อง</th>
                  <th rowspan="2">J Number</th>
                  <th rowspan="2" class="text-end">ราคาทุน</th>
                  <th rowspan="2" style="min-width:160px;">วันที่ปิด FP</th>
                  <th rowspan="2">สถานะ</th>
                  <th colspan="6" class="text-center fp-th-calc">การคิดดอกเบี้ย (แยกตามงวด)</th>
                  <th rowspan="2" class="text-end">รวมดอกเบี้ย</th>
                </tr>
                <tr>
                  <th class="fp-th-calc">งวด (ช่วงวันที่)</th>
                  <th class="fp-th-calc text-center">จำนวนวัน</th>
                  <th class="fp-th-calc text-end">MOR</th>
                  <th class="fp-th-calc text-end">MLR</th>
                  <th class="fp-th-calc text-end">Rate</th>
                  <th class="fp-th-calc text-end">ดอกที่ต้องจ่าย</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($rows as $i => $r)
                  @php $rowspan = max(1, count($r['segments'])); @endphp

                  @if ($r['isClosed'] && count($r['segments']))
                    @foreach ($r['segments'] as $si => $seg)
                      <tr>
                        @if ($si === 0)
                          <td rowspan="{{ $rowspan }}">{{ $i + 1 }}</td>
                          <td rowspan="{{ $rowspan }}">{{ $r['modelName'] }}</td>
                          <td rowspan="{{ $rowspan }}">{{ $r['subModelName'] }}</td>
                          <td rowspan="{{ $rowspan }}">{{ $r['vin'] }}</td>
                          <td rowspan="{{ $rowspan }}">{{ $r['billingText'] }}</td>
                          <td rowspan="{{ $rowspan }}">{{ $r['year'] }}</td>
                          @if ($showOption)
                            <td rowspan="{{ $rowspan }}">{{ $r['option'] }}</td>
                          @endif
                          <td rowspan="{{ $rowspan }}">{{ $r['color'] }}</td>
                          @if ($showInterior)
                            <td rowspan="{{ $rowspan }}">{{ $r['interior'] }}</td>
                          @endif
                          <td rowspan="{{ $rowspan }}">{{ $r['engine'] }}</td>
                          <td rowspan="{{ $rowspan }}">{{ $r['jNumber'] }}</td>
                          <td rowspan="{{ $rowspan }}" class="text-end">{{ number_format($r['cost'], 2) }}</td>
                          <td rowspan="{{ $rowspan }}">
                            <input type="date" class="form-control form-control-sm fp-close-input"
                              data-id="{{ $r['id'] }}" value="{{ $r['closeDate'] }}">
                          </td>
                          <td rowspan="{{ $rowspan }}">
                            <span class="fp-status fp-status--closed"><i class="bx bx-check-circle"></i> ปิดแล้ว</span>
                          </td>
                        @endif

                        {{-- segment cells --}}
                        <td class="fp-cell-calc">{{ $seg['startText'] }} – {{ $seg['endText'] }}</td>
                        <td class="fp-cell-calc text-center">{{ $seg['days'] }}</td>
                        <td class="fp-cell-calc text-end">{{ number_format($seg['mor'], 2) }}</td>
                        <td class="fp-cell-calc text-end">{{ number_format($seg['mlr'], 2) }}</td>
                        <td class="fp-cell-calc text-end fw-semibold">{{ number_format($seg['rate'], 2) }}</td>
                        <td class="fp-cell-calc text-end">{{ number_format($seg['interest'], 2) }}</td>

                        @if ($si === 0)
                          <td rowspan="{{ $rowspan }}" class="text-end fw-bold fp-total-cell">
                            {{ number_format($r['totalInterest'], 2) }}
                          </td>
                        @endif
                      </tr>
                    @endforeach
                  @else
                    {{-- ยังไม่ปิด FP — เว้นว่างส่วนคิดดอกเบี้ย --}}
                    <tr>
                      <td>{{ $i + 1 }}</td>
                      <td>{{ $r['modelName'] }}</td>
                      <td>{{ $r['subModelName'] }}</td>
                      <td>{{ $r['vin'] }}</td>
                      <td>{{ $r['billingText'] }}</td>
                      <td>{{ $r['year'] }}</td>
                      @if ($showOption)
                        <td>{{ $r['option'] }}</td>
                      @endif
                      <td>{{ $r['color'] }}</td>
                      @if ($showInterior)
                        <td>{{ $r['interior'] }}</td>
                      @endif
                      <td>{{ $r['engine'] }}</td>
                      <td>{{ $r['jNumber'] }}</td>
                      <td class="text-end">{{ number_format($r['cost'], 2) }}</td>
                      <td>
                        <input type="date" class="form-control form-control-sm fp-close-input"
                          data-id="{{ $r['id'] }}" value="{{ $r['closeDate'] }}">
                      </td>
                      <td>
                        <span class="fp-status fp-status--pending"><i class="bx bx-time"></i> รอปิด FP</span>
                      </td>
                      <td class="fp-cell-calc text-muted text-center" colspan="6">—</td>
                      <td class="text-end text-muted">—</td>
                    </tr>
                  @endif
                @empty
                  <tr>
                    <td colspan="{{ 20 + ($showOption ? 1 : 0) + ($showInterior ? 1 : 0) }}" class="text-center text-muted py-4">
                      ไม่มีรายการ FP (car_order ที่ประเภทการจ่าย = FP Tisco)
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <div class="text-muted small mt-2">
            <i class="bx bx-info-circle"></i>
            ดอกเบี้ย = ราคาทุน × (Rate ÷ 100) × (จำนวนวัน ÷ 365) &nbsp;•&nbsp; Rate = MOR − MLR &nbsp;•&nbsp;
            งวดข้ามเดือนตัดที่วันที่ 15/16 (MOR/Rate แต่ละงวดอาจต่างกัน)
          </div>

        </div>
      </div>
    </div>
  </div>

  <div id="fpLoadingOverlay" style="display:none;">
    <div class="ct-loading-box">
      <div class="spinner-border text-primary" role="status" style="width:1.4rem;height:1.4rem;"></div>
      <span>กำลังโหลด...</span>
    </div>
  </div>

  <style>
    .fp-list .fp-filter {
      display: flex; align-items: center; justify-content: space-between;
      flex-wrap: wrap; gap: .75rem;
      background: #f7f7fb; border: 1px solid #ececf4;
      border-radius: .75rem; padding: .7rem 1rem;
    }
    .fp-list .fp-chip {
      display: inline-flex; align-items: center; gap: .4rem;
      font-weight: 600; font-size: .9rem; padding: .4rem .8rem;
      border-radius: 2rem; line-height: 1;
    }
    .fp-list .fp-chip--brand  { color: #5a3ff0; background: #ece8fe; }
    .fp-list .fp-chip--period { color: #475569; background: #e7edf5; }
    .fp-list .fp-chip--total  { color: #047857; background: #d1fae5; }

    .fp-list .fp-table { font-size: .85rem; white-space: nowrap; }
    .fp-list .fp-table thead th {
      background: #f4f5fb; color: #4b4b6a; font-weight: 700;
      vertical-align: middle; text-align: center;
    }
    .fp-list .fp-table th.fp-th-calc { background: #eef2ff; color: #4338ca; }
    .fp-list .fp-table td.fp-cell-calc { background: #fafbff; }
    .fp-list .fp-table .fp-th-no { width: 46px; }
    .fp-list .fp-table td, .fp-list .fp-table th { padding: .5rem .6rem; }

    .fp-list .fp-status {
      display: inline-flex; align-items: center; gap: .3rem;
      font-weight: 600; font-size: .8rem; padding: .28rem .6rem; border-radius: 2rem;
    }
    .fp-list .fp-status--closed  { color: #047857; background: #d1fae5; }
    .fp-list .fp-status--pending { color: #b45309; background: #fef3c7; }

    .fp-list .fp-total-cell { background: #f0fdf4; color: #047857; }
    .fp-list .fp-close-input { min-width: 150px; }
  </style>
@endsection

@section('page-script')
  <script>
    $(function () {
      const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

      window.addEventListener('pageshow', function () {
        $('#fpLoadingOverlay').css('display', 'none');
      });

      // เปลี่ยนฟิลเตอร์ (สถานะ/เดือน) -> โหลดหน้าใหม่
      $(document).on('change', '#status, #month', function () {
        $('#fpLoadingOverlay').css('display', 'flex');
        document.getElementById('fpFilterForm').submit();
      });

      // แก้ "วันที่ปิด FP" -> บันทึกแล้วรีโหลดหน้า (คำนวณดอกใหม่)
      $(document).on('change', '.fp-close-input', function () {
        const id = $(this).data('id');
        const val = $(this).val();
        $('#fpLoadingOverlay').css('display', 'flex');

        $.ajax({
          url: `{{ url('floor-plan/fp') }}/${id}/close-date`,
          method: 'POST',
          data: { _method: 'PUT', _token: csrf, fp_close_date: val },
          success: function () { location.reload(); },
          error: function (xhr) {
            $('#fpLoadingOverlay').css('display', 'none');
            let msg = 'เกิดข้อผิดพลาด';
            if (xhr.status === 422 && xhr.responseJSON?.errors) {
              msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
            } else if (xhr.status === 403) {
              msg = 'ไม่มีสิทธิ์แก้ไข';
            }
            Swal.fire({ icon: 'error', title: 'ไม่สำเร็จ', text: msg });
          },
        });
      });
    });
  </script>
@endsection
