@extends('layouts/contentNavbarLayout')
@section('title', 'แจ้งจำหน่าย')

@php
  $showOption   = $brand == 1;   // option เฉพาะ brand 1
  $showInterior = $brand == 2;   // สีภายใน เฉพาะ brand 2
@endphp

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card tbl-card">

      {{-- ── Card header ── --}}
      <div class="po-card-header d-flex align-items-center gap-3">
        <div class="po-hd-icon">
          <i class="bx bx-purchase-tag fs-4 text-white"></i>
        </div>
        <div>
          <div class="text-white fw-bold mf-hd-title">แจ้งจำหน่าย</div>
          <div class="text-white mf-hd-sub">Floor Plan — แจ้งจำหน่าย</div>
        </div>
      </div>

      <div class="card-body pt-3">

        {{-- ── แถบเครื่องมือ : ฟิลเตอร์ สถานะ + เดือน (วันที่รับ) ── --}}
        <form method="GET" action="{{ route('floor-plan.dispose') }}" id="dpFilterForm">
          <div class="po-filter-bar d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
            <div class="d-flex align-items-center gap-3 flex-wrap">
              <div class="d-flex align-items-center gap-2">
                <label class="po-label mb-0" for="status"><i class="bx bx-filter-alt me-1"></i> สถานะ</label>
                <select id="status" name="status" class="form-select form-select-sm" style="width:auto;">
                  <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>ยังไม่เบิก</option>
                  <option value="withdrawn" {{ $status === 'withdrawn' ? 'selected' : '' }}>เบิกแล้ว</option>
                </select>
              </div>
              <div class="d-flex align-items-center gap-2">
                <label class="po-label mb-0" for="month"><i class="bx bx-calendar me-1"></i> เดือน (วันที่รับ)</label>
                <input type="month" id="month" name="month" class="form-control form-control-sm"
                  style="width:auto;" value="{{ $month }}">
                @if ($month)
                  <a href="{{ route('floor-plan.dispose', ['status' => $status]) }}"
                     class="btn btn-sm btn-outline-secondary" title="ล้างเดือน">
                    <i class="bx bx-x"></i>
                  </a>
                @endif
              </div>
            </div>
            <a href="{{ route('floor-plan.dispose.export', ['month' => $month]) }}"
               class="btn btn-success btn-sm" title="ออกรายงานตามเดือนของวันที่รับ (ทุกสถานะ)">
              <i class="bx bx-download me-1"></i> ออกรายงาน Excel
            </a>
          </div>
        </form>
        <div class="text-muted small mb-3">
          <i class="bx bx-info-circle"></i>
          "ยังไม่เบิก" = ยังไม่มีวันที่ ทบ.เบิก &nbsp;•&nbsp; ตัวกรองเดือนใช้เป็นรายงานตาม "วันที่รับ"
          &nbsp;•&nbsp; <b>แสดงรถทุกคัน</b> รถที่ยังไม่มีใบจองจะไม่มีชื่อลูกค้า
        </div>

        <div class="table-responsive">
          <table class="table table-bordered tbl-table tbl-styled dpTable w-100" id="dpTable">
            <thead>
              <tr>
                <th class="tbl-th-no">No.</th>
                <th>VIN Number</th>
                <th>เลขเครื่อง</th>
                <th>J Number</th>
                <th style="width:130px;">วันที่ปิด FP</th>
                <th style="width:100px;">Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($rows as $i => $r)
                <tr>
                  <td class="text-center">{{ $i + 1 }}</td>
                  <td>{{ $r['vin'] }}</td>
                  <td>{{ $r['engine'] }}</td>
                  <td>{{ $r['jNumber'] }}</td>
                  <td class="text-center">{{ $r['fpCloseText'] }}</td>
                  <td class="text-center">
                    <button type="button" class="btn btn-sm btn-icon btn-warning text-white dp-edit-btn"
                      data-id="{{ $r['id'] }}"
                      data-set="{{ $r['disposeSet'] }}"
                      data-received="{{ $r['received'] }}"
                      data-withdraw="{{ $r['withdraw'] }}"
                      data-note="{{ $r['note'] }}"
                      data-vin="{{ $r['vin'] }}"
                      data-engine="{{ $r['engine'] }}"
                      data-model="{{ $r['modelName'] }}"
                      data-submodel="{{ $r['subModelName'] }}"
                      data-year="{{ $r['year'] }}"
                      data-color="{{ $r['color'] }}"
                      data-option="{{ $r['option'] }}"
                      data-interior="{{ $r['interior'] }}"
                      data-cost="{{ number_format($r['cost'], 2) }}"
                      data-jnumber="{{ $r['jNumber'] }}"
                      data-fpclose="{{ $r['fpCloseText'] }}"
                      title="แก้ไข">
                      <i class="bx bx-edit"></i>
                    </button>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>

{{-- ── Modal แก้ไขแจ้งจำหน่าย ── --}}
<div class="modal fade" id="dpModal" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--edit">

      {{-- Header --}}
      <div class="modal-header mf-header mf-header--edit px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-purchase-tag fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">แก้ไขแจ้งจำหน่าย</h6>
            <small class="text-white mf-hd-sub">Floor Plan — แจ้งจำหน่าย</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">
        <form id="dpForm" autocomplete="off">
          <input type="hidden" id="dp_id">

          {{-- Section : ข้อมูลรถ (อ่านอย่างเดียว) --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon amber">
                <i class="bx bx-car"></i>
              </div>
              <span class="mf-section-title">ข้อมูลรถ</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">
                <div class="col-md-4"><span class="dp-info-label">VIN Number</span><div id="dp_vin" class="dp-info-val">-</div></div>
                <div class="col-md-4"><span class="dp-info-label">เลขเครื่อง</span><div id="dp_engine" class="dp-info-val">-</div></div>
                <div class="col-md-4"><span class="dp-info-label">J Number</span><div id="dp_jnumber" class="dp-info-val">-</div></div>
                <div class="col-md-4"><span class="dp-info-label">รุ่นหลัก</span><div id="dp_model" class="dp-info-val">-</div></div>
                <div class="col-md-4"><span class="dp-info-label">รุ่นย่อย</span><div id="dp_submodel" class="dp-info-val">-</div></div>
                <div class="col-md-4"><span class="dp-info-label">ปี</span><div id="dp_year" class="dp-info-val">-</div></div>
                <div class="col-md-4"><span class="dp-info-label">สี</span><div id="dp_color" class="dp-info-val">-</div></div>
                @if ($showOption)
                  <div class="col-md-4"><span class="dp-info-label">Option</span><div id="dp_option" class="dp-info-val">-</div></div>
                @endif
                @if ($showInterior)
                  <div class="col-md-4"><span class="dp-info-label">สีภายใน</span><div id="dp_interior" class="dp-info-val">-</div></div>
                @endif
                <div class="col-md-4"><span class="dp-info-label">ราคาทุน</span><div id="dp_cost" class="dp-info-val">-</div></div>
                <div class="col-md-4"><span class="dp-info-label">วันที่ปิด FP</span><div id="dp_fpclose" class="dp-info-val">-</div></div>
              </div>
            </div>
          </div>

          {{-- Section : ข้อมูลแจ้งจำหน่าย (แก้ไขได้) --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon amber">
                <i class="bx bx-purchase-tag"></i>
              </div>
              <span class="mf-section-title">ข้อมูลแจ้งจำหน่าย</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">
                <div class="col-md-4">
                  <label for="dp_set" class="mf-label form-label"><i class="bx bx-list-ul"></i> ชุดแจ้งจำหน่าย</label>
                  <select id="dp_set" class="form-select">
                    <option value="">— เลือก —</option>
                    @foreach ($disposeSets as $key => $label)
                      <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-4">
                  <label for="dp_received" class="mf-label form-label"><i class="bx bx-calendar-check"></i> วันที่รับ</label>
                  <input type="date" id="dp_received" class="form-control">
                </div>
                <div class="col-md-4">
                  <label for="dp_withdraw" class="mf-label form-label"><i class="bx bx-calendar-event"></i> วันที่ ทบ.เบิก</label>
                  <input type="date" id="dp_withdraw" class="form-control">
                </div>
                <div class="col-md-12">
                  <label for="dp_note" class="mf-label form-label"><i class="bx bx-note"></i> หมายเหตุ</label>
                  <textarea id="dp_note" class="form-control" rows="2" maxlength="1000"></textarea>
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

<div id="dpLoadingOverlay" style="display:none;">
  <div class="ct-loading-box">
    <div class="spinner-border text-primary" role="status" style="width:1.4rem;height:1.4rem;"></div>
    <span>กำลังโหลด...</span>
  </div>
</div>

<style>
  #dpModal .dp-info-label { font-size: .78rem; color: #8a8aa3; display: block; }
  #dpModal .dp-info-val { font-weight: 600; color: #3a3a55; }

  /* ตัวโหลด — สไตล์เดียวกับหน้าอื่น (มาตรฐาน tables.css) ไม่เบลอพื้นหลัง
     (id นี้ไม่ได้อยู่ใน list ของ tables.css จึงต้องประกาศเอง แต่ให้ค่าตรงกัน) */
  #dpLoadingOverlay {
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
    const dpModal = new bootstrap.Modal(document.getElementById('dpModal'));

    window.addEventListener('pageshow', function () {
      $('#dpLoadingOverlay').css('display', 'none');
    });

    // DataTable client-side 10 แถว/หน้า
    $('#dpTable').DataTable({
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
      $('#dpLoadingOverlay').css('display', 'flex');
      document.getElementById('dpFilterForm').submit();
    });

    // เปิด modal แก้ไข
    $(document).on('click', '.dp-edit-btn', function () {
      const $b = $(this);
      $('#dp_id').val($b.data('id'));

      // ข้อมูลรถ (อ่านอย่างเดียว)
      $('#dp_vin').text($b.data('vin') || '-');
      $('#dp_engine').text($b.data('engine') || '-');
      $('#dp_jnumber').text($b.data('jnumber') || '-');
      $('#dp_model').text($b.data('model') || '-');
      $('#dp_submodel').text($b.data('submodel') || '-');
      $('#dp_year').text($b.data('year') || '-');
      $('#dp_color').text($b.data('color') || '-');
      $('#dp_option').text($b.data('option') || '-');
      $('#dp_interior').text($b.data('interior') || '-');
      $('#dp_cost').text($b.data('cost') || '-');
      $('#dp_fpclose').text($b.data('fpclose') || '-');

      // ฟิลด์แก้ไขได้
      $('#dp_set').val($b.data('set') || '');
      $('#dp_received').val($b.data('received') || '');
      $('#dp_withdraw').val($b.data('withdraw') || '');
      $('#dp_note').val($b.data('note') || '');

      dpModal.show();
    });

    // บันทึก
    $('#dpForm').on('submit', function (e) {
      e.preventDefault();
      const id = $('#dp_id').val();
      $('#dpLoadingOverlay').css('display', 'flex');

      $.ajax({
        url: `{{ url('floor-plan/dispose') }}/${id}`,
        method: 'POST',
        data: {
          _method: 'PUT',
          _token: csrf,
          dispose_set: $('#dp_set').val(),
          dispose_received_date: $('#dp_received').val(),
          dispose_reg_withdraw_date: $('#dp_withdraw').val(),
          dispose_note: $('#dp_note').val(),
        },
        success: function () { location.reload(); },
        error: function (xhr) {
          $('#dpLoadingOverlay').css('display', 'none');
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
