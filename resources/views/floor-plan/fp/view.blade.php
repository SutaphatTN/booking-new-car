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

        <div class="card-body pt-3">

          {{-- ── แถบเครื่องมือ : ฟิลเตอร์ สถานะ + เดือน (Billing) ── --}}
          <form method="GET" action="{{ route('floor-plan.fp') }}" id="fpFilterForm">
            <div class="po-filter-bar d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
              <div class="d-flex align-items-center gap-3 flex-wrap">
                <div class="d-flex align-items-center gap-2">
                  <label class="po-label mb-0" for="status"><i class="bx bx-filter-alt me-1"></i> สถานะ</label>
                  <select id="status" name="status" class="form-select form-select-sm" style="width:auto;">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>ทั้งหมด</option>
                    <option value="closed" {{ $status === 'closed' ? 'selected' : '' }}>ปิดแล้ว</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>รอปิด FP</option>
                  </select>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <label class="po-label mb-0" for="month"><i class="bx bx-calendar me-1"></i> เดือน (Billing)</label>
                  <input type="month" id="month" name="month" class="form-control form-control-sm"
                    style="width:auto;" value="{{ $month }}" {{ $status === 'pending' ? 'disabled' : '' }}>
                </div>
              </div>
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge bg-label-secondary"><i class="bx bx-calendar-event me-1"></i> งวด {{ $periodLabel }}</span>
                <span class="badge bg-label-success"><i class="bx bx-money me-1"></i> รวมดอกเบี้ย {{ number_format($grandTotal, 2) }} ฿</span>
                <a href="{{ route('floor-plan.fp.export', ['month' => $month, 'status' => $status]) }}"
                   class="btn btn-success btn-sm" title="ออกรายงานตามงวด Billing date ที่เลือก">
                  <i class="bx bx-download me-1"></i> ออกรายงาน Excel
                </a>
              </div>
            </div>
          </form>
          <div class="text-muted small mb-3">
            <i class="bx bx-info-circle"></i>
            กรอง "ปิดแล้ว" ตามงวด Billing date (รอบ 16–15) &nbsp;•&nbsp; <b>รอปิด FP แสดงเสมอ</b> (ยกเว้นเลือกสถานะ "ปิดแล้ว")
          </div>

          <div class="table-responsive">
            <table class="table table-bordered tbl-table tbl-styled fpTable w-100" id="fpTable">
              <thead>
                <tr>
                  <th class="tbl-th-no">No.</th>
                  <th>VIN Number</th>
                  <th>เลขเครื่อง</th>
                  <th>J Number</th>
                  <th>ราคาทุน</th>
                  <th>Billing date</th>
                  <th>สถานะ</th>
                  <th style="width:110px;">Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($rows as $i => $r)
                  <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $r['vin'] }}</td>
                    <td>{{ $r['engine'] }}</td>
                    <td>{{ $r['jNumber'] }}</td>
                    <td class="text-end">{{ number_format($r['cost'], 2) }}</td>
                    <td class="text-center">{{ $r['billingText'] }}</td>
                    <td class="text-center">
                      @if ($r['isClosed'])
                        <span class="badge bg-label-success">ปิดแล้ว</span>
                      @else
                        <span class="badge bg-label-warning">รอปิด FP</span>
                      @endif
                    </td>
                    <td class="text-center text-nowrap">
                      <button type="button" class="btn btn-sm btn-icon btn-info text-white fp-btn-view"
                        data-id="{{ $r['id'] }}" title="ดูข้อมูล">
                        <i class="bx bx-show"></i>
                      </button>
                      <button type="button" class="btn btn-sm btn-icon btn-warning text-white fp-btn-edit"
                        data-id="{{ $r['id'] }}"
                        data-vin="{{ $r['vin'] }}"
                        data-model="{{ $r['modelName'] }}"
                        data-billing="{{ $r['billingText'] }}"
                        data-close="{{ $r['closeDate'] }}"
                        title="แก้ไขวันที่ปิด FP">
                        <i class="bx bx-edit"></i>
                      </button>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                      ไม่มีรายการ FP (car_order ที่ประเภทการจ่าย = FP Tisco)
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

        </div>
      </div>
    </div>
  </div>

  {{-- ── Detail templates (ซ่อนไว้ ใช้ยัดเข้า modal ดูข้อมูล) ── --}}
  <div id="fpDetailTemplates" class="d-none">
    @foreach ($rows as $r)
      <div data-detail="{{ $r['id'] }}">
        {{-- .fp-detail-info = ส่วนที่ใช้ซ้ำใน modal แก้ไขด้วย (ข้อมูลรถ + ข้อมูลการเงิน) --}}
        <div class="fp-detail-info">
        {{-- ข้อมูลรถ --}}
        <div class="mf-section">
          <div class="mf-section-hd">
            <div class="mf-section-icon sky"><i class="bx bx-car"></i></div>
            <span class="mf-section-title">ข้อมูลรถ</span>
          </div>
          <div class="mf-section-body">
            <div class="row g-3">
              <div class="col-md-4"><span class="fp-info-label">รุ่นหลัก</span><div class="fp-info-val">{{ $r['modelName'] }}</div></div>
              <div class="col-md-4"><span class="fp-info-label">รุ่นย่อย</span><div class="fp-info-val">{{ $r['subModelName'] }}</div></div>
              <div class="col-md-4"><span class="fp-info-label">ปี</span><div class="fp-info-val">{{ $r['year'] }}</div></div>
              <div class="col-md-4"><span class="fp-info-label">VIN Number</span><div class="fp-info-val">{{ $r['vin'] }}</div></div>
              <div class="col-md-4"><span class="fp-info-label">เลขเครื่อง</span><div class="fp-info-val">{{ $r['engine'] }}</div></div>
              <div class="col-md-4"><span class="fp-info-label">J Number</span><div class="fp-info-val">{{ $r['jNumber'] }}</div></div>
              @if ($showOption)
                <div class="col-md-4"><span class="fp-info-label">Option</span><div class="fp-info-val">{{ $r['option'] }}</div></div>
              @endif
              <div class="col-md-4"><span class="fp-info-label">สี</span><div class="fp-info-val">{{ $r['color'] }}</div></div>
              @if ($showInterior)
                <div class="col-md-4"><span class="fp-info-label">สีภายใน</span><div class="fp-info-val">{{ $r['interior'] }}</div></div>
              @endif
              <div class="col-md-4"><span class="fp-info-label">ราคาทุน</span><div class="fp-info-val">{{ number_format($r['cost'], 2) }}</div></div>
              <div class="col-md-4"><span class="fp-info-label">Billing date</span><div class="fp-info-val">{{ $r['billingText'] }}</div></div>
              <div class="col-md-4"><span class="fp-info-label">วันที่ปิด FP</span><div class="fp-info-val">{{ $r['closeText'] }}</div></div>
              <div class="col-md-4">
                <span class="fp-info-label">สถานะ</span>
                <div class="fp-info-val">
                  @if ($r['isClosed'])
                    <span class="badge bg-label-success">ปิดแล้ว</span>
                  @else
                    <span class="badge bg-label-warning">รอปิด FP</span>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- ข้อมูลการเงิน (จากใบจอง) --}}
        <div class="mf-section">
          <div class="mf-section-hd">
            <div class="mf-section-icon sky"><i class="bx bx-buildings"></i></div>
            <span class="mf-section-title">ข้อมูลการเงิน</span>
          </div>
          <div class="mf-section-body">
            <div class="row g-3">
              <div class="col-md-4">
                <span class="fp-info-label">เงินดาวน์</span>
                <div class="fp-info-val">{{ $r['downPayment'] !== null ? number_format($r['downPayment'], 2) : '-' }}</div>
              </div>
              <div class="col-md-4">
                <span class="fp-info-label">ยอดจัดไฟแนนซ์</span>
                <div class="fp-info-val">{{ $r['balanceFinance'] !== null ? number_format($r['balanceFinance'], 2) : '-' }}</div>
              </div>
              <div class="col-md-4">
                <span class="fp-info-label">ชื่อไฟแนนซ์</span>
                <div class="fp-info-val">{{ $r['financeName'] }}</div>
              </div>
            </div>
          </div>
        </div>

        </div>{{-- /.fp-detail-info --}}

        {{-- การคิดดอกเบี้ย --}}
        <div class="mf-section">
          <div class="mf-section-hd">
            <div class="mf-section-icon sky"><i class="bx bx-calculator"></i></div>
            <span class="mf-section-title">การคิดดอกเบี้ย</span>
          </div>
          <div class="mf-section-body">
            @if ($r['isClosed'] && count($r['segments']))
              <div class="table-responsive">
                <table class="table table-bordered tbl-table tbl-styled w-100 mb-0">
                  <thead>
                    <tr>
                      <th>งวด (ช่วงวันที่)</th>
                      <th>จำนวนวัน</th>
                      <th>MOR</th>
                      <th>MLR</th>
                      <th>Rate</th>
                      <th>ดอกที่ต้องจ่าย</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($r['segments'] as $seg)
                      <tr>
                        <td class="text-center">{{ $seg['startText'] }} – {{ $seg['endText'] }}</td>
                        <td class="text-center">{{ $seg['days'] }}</td>
                        <td class="text-end">{{ number_format($seg['mor'], 2) }}</td>
                        <td class="text-end">{{ number_format($seg['mlr'], 2) }}</td>
                        <td class="text-end fw-semibold">{{ number_format($seg['rate'], 2) }}</td>
                        <td class="text-end">{{ number_format($seg['interest'], 2) }}</td>
                      </tr>
                    @endforeach
                    <tr>
                      <td colspan="5" class="text-end fw-bold">รวมดอกเบี้ยที่ต้องจ่าย</td>
                      <td class="text-end fw-bold text-success">{{ number_format($r['totalInterest'], 2) }} ฿</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            @else
              <div class="text-center text-muted py-3">
                <i class="bx bx-time-five fs-4 d-block mb-1"></i>
                ยังไม่ปิด FP — กรอกวันที่ปิด FP เพื่อคำนวณดอกเบี้ย
              </div>
            @endif
          </div>
        </div>
      </div>
    @endforeach
  </div>

  {{-- ── Modal : ดูข้อมูล ── --}}
  <div class="modal fade" id="fpDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content border-0 shadow mf-content mf-content--view">
        <div class="modal-header mf-header mf-header--view px-4">
          <div class="d-flex align-items-center gap-3">
            <div class="mf-hd-icon"><i class="bx bx-show fs-5 text-white"></i></div>
            <div>
              <h6 class="mb-0 fw-bold text-white mf-hd-title">รายละเอียด FP</h6>
              <small class="text-white mf-hd-sub">Floor Plan — FP Detail</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body mf-body" id="fpDetailBody"></div>
      </div>
    </div>
  </div>

  {{-- ── Modal : แก้ไขวันที่ปิด FP ── --}}
  <div class="modal fade" id="fpEditModal" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content border-0 shadow mf-content mf-content--edit">
        <div class="modal-header mf-header mf-header--edit px-4">
          <div class="d-flex align-items-center gap-3">
            <div class="mf-hd-icon"><i class="bx bx-edit fs-5 text-white"></i></div>
            <div>
              <h6 class="mb-0 fw-bold text-white mf-hd-title">แก้ไขวันที่ปิด FP</h6>
              <small class="text-white mf-hd-sub">Floor Plan — Edit FP Close Date</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body mf-body">
          <form id="fpEditForm" autocomplete="off">
            <input type="hidden" id="fpEditId">

            {{-- ข้อมูลรถ + ข้อมูลการเงิน (อ่านอย่างเดียว — ยัดจาก detail template ตัวเดียวกับ modal รายละเอียด) --}}
            <div id="fpEditInfoBody"></div>

            {{-- Section : วันที่ปิด FP (แก้ไขได้) --}}
            <div class="mf-section">
              <div class="mf-section-hd">
                <div class="mf-section-icon amber"><i class="bx bx-calendar-check"></i></div>
                <span class="mf-section-title">วันที่ปิด FP</span>
              </div>
              <div class="mf-section-body">
                <div class="row g-3">
                  <div class="col-md-4">
                    <label for="fpEditCloseDate" class="mf-label form-label"><i class="bx bx-calendar-check"></i> วันที่ปิด FP</label>
                    <input type="date" id="fpEditCloseDate" class="form-control">
                    <div class="form-text">เว้นว่าง = กลับเป็น "รอปิด FP" / ต้องไม่ก่อน Billing date</div>
                  </div>
                </div>
              </div>
            </div>

            {{-- Actions --}}
            <div class="d-flex justify-content-end gap-2 pt-1">
              <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
                <i class="bx bx-x me-1"></i>ยกเลิก
              </button>
              <button type="submit" class="btn btn-primary px-5">
                <i class="bx bx-save me-1"></i>บันทึก
              </button>
            </div>
          </form>
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
    #fpDetailModal .fp-info-label,
    #fpEditModal .fp-info-label { font-size: .78rem; color: #8a8aa3; display: block; }
    #fpDetailModal .fp-info-val,
    #fpEditModal .fp-info-val { font-weight: 600; color: #3a3a55; }

    /* ไอคอนหัวข้อ section ให้ล้อสีตามธีมของ modal (template เดียวใช้ทั้ง 2 modal)
       ดูข้อมูล = mf-header--view (ฟ้า) / แก้ไข = mf-header--edit (ส้ม) */
    #fpDetailModal .mf-section-icon { background: #e0f2fe; color: #0284c7; }
    #fpEditModal   .mf-section-icon { background: #fef3c7; color: #d97706; }

    /* ตัวโหลด — สไตล์เดียวกับหน้าอื่น (มาตรฐาน tables.css) ไม่เบลอพื้นหลัง
       (id นี้ไม่ได้อยู่ใน list ของ tables.css จึงต้องประกาศเอง แต่ให้ค่าตรงกัน) */
    #fpLoadingOverlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.35);
      z-index: 9999;
      align-items: center;
      justify-content: center;
    }
  </style>
@endsection

@section('page-script')
  <script>
    $(function () {
      const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      const fpDetailModal = new bootstrap.Modal(document.getElementById('fpDetailModal'));
      const fpEditModal = new bootstrap.Modal(document.getElementById('fpEditModal'));

      window.addEventListener('pageshow', function () {
        $('#fpLoadingOverlay').css('display', 'none');
      });

      // DataTable client-side 10 แถว/หน้า
      $('#fpTable').DataTable({
        ordering: false,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        columnDefs: [{ targets: -1, orderable: false, searchable: false }],
        language: {
          lengthMenu: 'แสดง _MENU_ แถว',
          zeroRecords: 'ไม่พบข้อมูล',
          info: 'แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ',
          infoEmpty: 'ไม่มีข้อมูล',
          infoFiltered: '(กรองจาก _MAX_ รายการ)',
          search: 'ค้นหา:',
          paginate: { next: 'ถัดไป', previous: 'ก่อนหน้า' },
        },
      });

      // เปลี่ยนฟิลเตอร์ (สถานะ/เดือน) -> โหลดหน้าใหม่
      $(document).on('change', '#status, #month', function () {
        $('#fpLoadingOverlay').css('display', 'flex');
        document.getElementById('fpFilterForm').submit();
      });

      // ดูข้อมูล -> ยัด detail template เข้า modal
      $(document).on('click', '.fp-btn-view', function () {
        const id = $(this).data('id');
        const html = $('#fpDetailTemplates [data-detail="' + id + '"]').html()
          || '<div class="text-muted text-center py-3">ไม่พบข้อมูล</div>';
        $('#fpDetailBody').html(html);
        fpDetailModal.show();
      });

      // แก้ไขวันที่ปิด FP
      $(document).on('click', '.fp-btn-edit', function () {
        const d = $(this).data();
        $('#fpEditId').val(d.id);
        // ใช้ template เดียวกับ modal รายละเอียด (เฉพาะข้อมูลรถ + ข้อมูลการเงิน)
        const info = $('#fpDetailTemplates [data-detail="' + d.id + '"] .fp-detail-info').html() || '';
        $('#fpEditInfoBody').html(info);
        $('#fpEditCloseDate').val(d.close || '');
        fpEditModal.show();
      });

      // บันทึกวันที่ปิด FP
      $('#fpEditForm').on('submit', function (e) {
        e.preventDefault();
        const id = $('#fpEditId').val();
        const val = $('#fpEditCloseDate').val();
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
