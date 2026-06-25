@extends('layouts/contentNavbarLayout')
@section('title', 'ตั้งค่า GP')

@php
  $isAdmin = auth()->user()->role === 'admin';
@endphp

@section('content')
  <div class="row">
    <div class="col-12">
      <div class="card tbl-card">

        {{-- ── Card header ── --}}
        <div class="po-card-header d-flex align-items-center gap-3">
          <div class="po-hd-icon">
            <i class="bx bx-cog fs-4 text-white"></i>
          </div>
          <div>
            <div class="text-white fw-bold mf-hd-title">ตั้งค่า GP</div>
            <div class="text-white mf-hd-sub">GP Setting</div>
          </div>
        </div>

        <div class="card-body pt-3">

          {{-- ── ฟิลเตอร์เดือน ── --}}
          <form method="GET" action="{{ route('purchase-order.gp-setting') }}">
            <div class="po-filter-bar d-flex align-items-center gap-2 flex-wrap justify-content-end">
              <i class="bx bx-filter-alt text-muted"></i>
              <span class="fw-semibold text-muted tbl-filter-text">กรองข้อมูล :</span>
              <label for="month" class="mb-0 text-muted">เดือน (ตามวันที่ส่งมอบ CK)</label>
              <input type="month" id="month" name="month" class="form-control form-control-sm"
                style="max-width:190px;" value="{{ $month }}">
            </div>
          </form>

          {{-- <div class="alert alert-info py-2 small mb-3">
            <i class="bx bx-info-circle"></i>
            เว้นว่าง "ราคาทุน GP" = ใช้ค่าเดิม (DNP ÷ 1.07) / เว้นว่าง "คอมขาย" = 4500.
            ถ้ากรอก "ราคาทุน GP" ระบบจะใช้ค่านี้ + ค่าอุปกรณ์ตกแต่งแทน
            @unless ($isAdmin)
              <span class="text-danger fw-bold">— สิทธิ์ audit: ดูได้อย่างเดียว</span>
            @endunless
          </div> --}}

          <div class="table-responsive">
            <table class="table table-bordered tbl-table tbl-styled" id="gpSettingTable">
              <thead>
                <tr>
                  <th class="tbl-th-no">No.</th>
                  <th>วันที่ส่งมอบ CK</th>
                  <th>ชื่อ - นามสกุล</th>
                  <th>รุ่นรถ</th>
                  <th>VIN Number</th>
                  <th class="tbl-th-action" style="width:120px;">Action</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($rows as $i => $r)
                  @php
                    $customerName = trim(
                      ($r->customer->prefix->Name_TH ?? '') . ' ' .
                      ($r->customer->FirstName ?? '') . ' ' .
                      ($r->customer->LastName ?? '')
                    );
                    $customerName = $customerName !== '' ? $customerName : '-';
                    $model = $r->carOrder->model->Name_TH ?? '-';
                    $code  = $r->carOrder->order_code ?? '-';
                    $vin   = $r->carOrder->vin_number ?? '-';
                  @endphp
                  <tr data-id="{{ $r->id }}"
                    data-customer="{{ $customerName }}"
                    data-model="{{ $model }}"
                    data-code="{{ $code }}"
                    data-vin="{{ $vin }}"
                    data-date="{{ $r->format_ck_date ?? '-' }}"
                    data-dnp="{{ $r->carOrder->car_DNP ?? '' }}"
                    data-msrp="{{ $r->carOrder->car_MSRP ?? '' }}"
                    data-ri="{{ $r->carOrder->RI ?? '' }}"
                    data-ws="{{ $r->carOrder->WS ?? '' }}"
                    data-cost="{{ $r->gp_cost_price_override ?? '' }}"
                    data-acc="{{ $r->gp_accessory_cost ?? '' }}"
                    data-com="{{ $r->gp_commission_sale ?? '' }}">
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r->format_ck_date ?? '-' }}</td>
                    <td>{{ $customerName }}</td>
                    <td>{{ $model }}</td>
                    <td>{{ $vin }}</td>
                    <td class="text-center">
                      <button type="button" class="btn btn-icon btn-warning text-white btn-edit-gp"
                        data-bs-toggle="modal" data-bs-target="#gpEditModal"
                        title="แก้ไข">
                        <i class="bx bx-edit"></i>
                      </button>
                    </td>
                  </tr>
                @empty
                @endforelse
              </tbody>
            </table>
          </div>

        </div>
      </div>
    </div>
  </div>

  {{-- ── Edit Modal ── --}}
  <div class="modal fade" id="gpEditModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
      <div class="modal-content border-0 shadow mf-content mf-content--edit">

        <div class="modal-header mf-header mf-header--edit px-4">
          <div class="d-flex align-items-center gap-3">
            <div class="mf-hd-icon">
              <i class="bx bx-cog fs-5 text-white"></i>
            </div>
            <div>
              <h6 class="mb-0 fw-bold text-white mf-hd-title">ตั้งค่า GP รายคัน</h6>
              <small class="text-white mf-hd-sub">GP Setting</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body mf-body">
          <input type="hidden" id="m_id">

          {{-- Section 1 : ข้อมูลลูกค้า/รถ (อ้างอิง) --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon sky">
                <i class="bx bx-user"></i>
              </div>
              <span class="mf-section-title">ข้อมูลลูกค้า / รถ</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">
                <div class="col-md-6">
                  <div class="mf-label"><i class="bx bx-user-circle ci-sky"></i> ชื่อ - นามสกุล</div>
                  <div class="mf-val" id="m_customer">-</div>
                </div>
                <div class="col-md-6">
                  <div class="mf-label"><i class="bx bx-car ci-sky"></i> รุ่นรถ</div>
                  <div class="mf-val" id="m_model">-</div>
                </div>
                <div class="col-md-3">
                  <div class="mf-label"><i class="bx bx-barcode ci-sky"></i> VIN Number</div>
                  <div class="mf-val" id="m_vin">-</div>
                </div>
                <div class="col-md-3">
                  <div class="mf-label"><i class="bx bx-calendar-check ci-sky"></i> วันที่ส่งมอบ</div>
                  <div class="mf-val" id="m_date">-</div>
                </div>

                <div class="col-md-3">
                  <label for="m_car_DNP" class="mf-label form-label"><i class="bx bx-purchase-tag ci-sky"></i> ราคาทุน</label>
                  <input type="text" inputmode="decimal" class="form-control text-end gp-num" id="m_car_DNP" {{ $isAdmin ? '' : 'readonly' }}>
                </div>
                <div class="col-md-3">
                  <label for="m_car_MSRP" class="mf-label form-label"><i class="bx bx-tag ci-sky"></i> ราคาขาย</label>
                  <input type="text" inputmode="decimal" class="form-control text-end gp-num" id="m_car_MSRP" {{ $isAdmin ? '' : 'readonly' }}>
                </div>
                <div class="col-md-3">
                  <label for="m_RI" class="mf-label form-label"><i class="bx bx-trending-up ci-sky"></i> RI</label>
                  <input type="text" inputmode="decimal" class="form-control text-end gp-num" id="m_RI">
                </div>
                <div class="col-md-3">
                  <label for="m_WS" class="mf-label form-label"><i class="bx bx-store ci-sky"></i> WS</label>
                  <input type="text" inputmode="decimal" class="form-control text-end gp-num" id="m_WS">
                </div>
              </div>
            </div>
          </div>

          {{-- Section 2 : ข้อมูลรถ (car_order) --}}
          {{-- <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon amber">
                <i class="bx bx-purchase-tag"></i>
              </div>
              <span class="mf-section-title">ข้อมูลรถ (car_order)</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">
                <div class="col-md-6">
                  <label for="m_car_DNP" class="mf-label form-label">ราคาทุน (DNP)</label>
                  <input type="number" step="0.01" min="0" class="form-control text-end" id="m_car_DNP" {{ $isAdmin ? '' : 'readonly' }}>
                </div>
                <div class="col-md-6">
                  <label for="m_car_MSRP" class="mf-label form-label">ราคาขาย (MSRP)</label>
                  <input type="number" step="0.01" min="0" class="form-control text-end" id="m_car_MSRP" {{ $isAdmin ? '' : 'readonly' }}>
                </div>
                <div class="col-md-6">
                  <label for="m_RI" class="mf-label form-label">RI</label>
                  <input type="number" step="0.01" class="form-control text-end" id="m_RI" {{ $isAdmin ? '' : 'readonly' }}>
                </div>
                <div class="col-md-6">
                  <label for="m_WS" class="mf-label form-label">WS</label>
                  <input type="number" step="0.01" class="form-control text-end" id="m_WS" {{ $isAdmin ? '' : 'readonly' }}>
                </div>
              </div>
            </div>
          </div> --}}

          {{-- Section 3 : ค่า GP (กรอกเอง) --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon amber">
                <i class="bx bx-calculator"></i>
              </div>
              <span class="mf-section-title">ค่า GP (กรอกเอง)</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">
                <div class="col-md-4">
                  <label for="m_gp_cost" class="mf-label form-label"><i class="bx bx-coin ci-amber"></i> ราคาทุนตาม DMS</label>
                  <input type="text" inputmode="decimal" class="form-control text-end gp-num" id="m_gp_cost">
                </div>
                <div class="col-md-4">
                  <label for="m_gp_acc" class="mf-label form-label"><i class="bx bx-wrench ci-amber"></i> ค่าอุปกรณ์ตกแต่ง</label>
                  <input type="text" inputmode="decimal" class="form-control text-end gp-num" id="m_gp_acc">
                </div>
                <div class="col-md-4">
                  <label for="m_gp_com" class="mf-label form-label"><i class="bx bx-dollar-circle ci-amber"></i> คอมขาย</label>
                  <input type="text" inputmode="decimal" class="form-control text-end gp-num" id="m_gp_com">
                </div>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2 pt-1">
            <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
              <i class="bx bx-x me-1"></i>ปิด
            </button>
              <button type="button" class="btn btn-primary px-5" id="btnSaveGp">
                <i class="bx bx-save me-1"></i>บันทึก
              </button>
          </div>

        </div>
      </div>
    </div>
  </div>

  <div id="gpLoadingOverlay">
    <div class="ct-loading-box">
      <div class="spinner-border text-primary" role="status" style="width:1.4rem;height:1.4rem;"></div>
      <span>กำลังโหลด...</span>
    </div>
  </div>
@endsection

@section('page-script')
  <script>
    // โชว์ loader ตอนเปลี่ยนเดือน (form submit โหลดหน้าใหม่)
    $(document).on('change', '#month', function () {
      $('#gpLoadingOverlay').css('display', 'flex');
      this.form.submit();
    });

    // กันค้าง: ถ้ากดย้อนกลับแล้วหน้าโดนกู้จาก bfcache ให้ซ่อน overlay
    window.addEventListener('pageshow', function () {
      $('#gpLoadingOverlay').css('display', 'none');
    });

    $(function () {
      const updateUrl = "{{ url('purchase-order/gp-setting') }}";
      const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      const isAdmin = @json($isAdmin);
      let $activeRow = null;

      // ── helper: ใส่/ถอด comma สำหรับช่องตัวเลข ──
      const fmtNum = v => {
        if (v === null || v === undefined || v === '') return '';
        const n = Number(String(v).replace(/,/g, ''));
        return isNaN(n) ? '' : n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      };
      const unfmtNum = sel => {
        const v = String($(sel).val() ?? '').replace(/,/g, '').trim();
        return v === '' ? null : v;
      };

      // จัด comma + ทศนิยม แบบเรียลไทม์ + อนุญาตเฉพาะตัวเลข
      const formatGpInput = (input) => {
        const oldValue = input.value;
        const oldPos = input.selectionStart ?? oldValue.length;
        // นับจำนวนตัวเลข/จุด ก่อน cursor ไว้คืนตำแหน่งหลังใส่ comma
        const digitsBefore = oldValue.slice(0, oldPos).replace(/[^0-9.]/g, '').length;

        // เก็บเฉพาะตัวเลขกับจุด และให้มีจุดเดียว
        let raw = oldValue.replace(/[^0-9.]/g, '');
        const firstDot = raw.indexOf('.');
        if (firstDot !== -1) {
          raw = raw.slice(0, firstDot + 1) + raw.slice(firstDot + 1).replace(/\./g, '');
        }

        let [intPart = '', decPart] = raw.split('.');
        intPart = intPart.replace(/^0+(?=\d)/, ''); // ตัดศูนย์นำหน้า
        if (decPart !== undefined) decPart = decPart.slice(0, 2); // ทศนิยมไม่เกิน 2 ตำแหน่ง

        let result = intPart === '' ? '' : Number(intPart).toLocaleString('en-US');
        if (raw.indexOf('.') !== -1) {
          if (result === '') result = '0';
          result += '.' + (decPart ?? '');
        }

        input.value = result;

        // คืนตำแหน่ง cursor โดยนับตัวเลข/จุดให้ครบเท่าเดิม
        let newPos = 0, counted = 0;
        while (newPos < result.length && counted < digitsBefore) {
          if (/[0-9.]/.test(result[newPos])) counted++;
          newPos++;
        }
        input.setSelectionRange(newPos, newPos);
      };

      $('#gpEditModal').on('input', '.gp-num', function () {
        formatGpInput(this);
      });

      // ออกจากช่อง -> เติมทศนิยมให้ครบ 2 ตำแหน่ง (เช่น 833,644 -> 833,644.00)
      $('#gpEditModal').on('blur', '.gp-num', function () {
        this.value = fmtNum(this.value);
      });

      // DataTables — แสดง 10 รายการต่อหน้า
      if ($.fn.DataTable.isDataTable('#gpSettingTable')) {
        $('#gpSettingTable').DataTable().destroy();
      }
      $('#gpSettingTable').DataTable({
        pageLength: 10,
        lengthChange: false,
        order: [],
        columnDefs: [{ orderable: false, targets: [0, 5] }],
        language: {
          search: 'ค้นหา:',
          info: 'แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ',
          infoEmpty: 'ไม่มีข้อมูล',
          infoFiltered: '(กรองจาก _MAX_ รายการ)',
          zeroRecords: 'ไม่พบรายการที่ค้นหา',
          emptyTable: 'ไม่พบรายการในเดือนนี้',
          paginate: { next: 'ถัดไป', previous: 'ก่อนหน้า' }
        }
      });

      // เปิด modal -> เติมข้อมูลจากแถว
      $('#gpEditModal').on('show.bs.modal', function (e) {
        const $row = $(e.relatedTarget).closest('tr');
        $activeRow = $row;
        const d = $row.data();

        $('#m_id').val($row.data('id'));
        $('#m_customer').text(d.customer);
        $('#m_model').text(d.model);
        $('#m_vin').text(d.vin);
        $('#m_code').text(d.code);
        $('#m_date').text(d.date);

        $('#m_car_DNP').val(fmtNum(d.dnp));
        $('#m_car_MSRP').val(fmtNum(d.msrp));
        $('#m_RI').val(fmtNum(d.ri));
        $('#m_WS').val(fmtNum(d.ws));
        $('#m_gp_cost').val(fmtNum(d.cost));
        $('#m_gp_acc').val(fmtNum(d.acc));
        // คอมขาย: ถ้ายังไม่มีค่า แสดง default 3500
        const comVal = (d.com === '' || d.com === null || d.com === undefined) ? 3500 : d.com;
        $('#m_gp_com').val(fmtNum(comVal));
      });

      $('#btnSaveGp').on('click', function () {
        const $btn = $(this);
        const id = $('#m_id').val();

        const payload = {
          _method: 'PUT',
          _token: csrf,
          gp_cost_price_override: unfmtNum('#m_gp_cost'),
          gp_accessory_cost: unfmtNum('#m_gp_acc'),
          gp_commission_sale: unfmtNum('#m_gp_com'),
          car_DNP: unfmtNum('#m_car_DNP'),
          car_MSRP: unfmtNum('#m_car_MSRP'),
          RI: unfmtNum('#m_RI'),
          WS: unfmtNum('#m_WS'),
        };

        $btn.prop('disabled', true);

        $.ajax({
          url: `${updateUrl}/${id}`,
          method: 'POST',
          data: payload,
          success: function (res) {
            // อัปเดต data attributes ของแถว เพื่อให้เปิด modal รอบหน้าได้ค่าล่าสุด
            if ($activeRow) {
              $activeRow.data('cost', payload.gp_cost_price_override ?? '')
                .data('acc', payload.gp_accessory_cost ?? '')
                .data('com', payload.gp_commission_sale ?? '')
                .data('dnp', payload.car_DNP ?? '')
                .data('msrp', payload.car_MSRP ?? '')
                .data('ri', payload.RI ?? '')
                .data('ws', payload.WS ?? '');
            }
            bootstrap.Modal.getInstance(document.getElementById('gpEditModal')).hide();
            Swal.fire({ icon: 'success', title: res.message ?? 'บันทึกเรียบร้อยแล้ว', timer: 1200, showConfirmButton: true });
          },
          error: function (xhr) {
            let msg = 'เกิดข้อผิดพลาด';
            if (xhr.status === 422 && xhr.responseJSON?.errors) {
              msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
            } else if (xhr.status === 403) {
              msg = 'ไม่มีสิทธิ์แก้ไข';
            }
            Swal.fire({ icon: 'error', title: 'ไม่สำเร็จ', text: msg });
          },
          complete: function () {
            $btn.prop('disabled', false);
          },
        });
      });

      // blur focus กัน aria-hidden warning ตอนปิด modal
      $(document).on('hide.bs.modal', '#gpEditModal', function () {
        setTimeout(() => {
          document.activeElement.blur();
          $('body').trigger('focus');
        }, 1);
      });
    });
  </script>
@endsection
