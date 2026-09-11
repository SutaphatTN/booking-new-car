@extends('layouts/contentNavbarLayout')
@section('title', 'SSI หลังส่งมอบ')

@section('page-script')
  <script>
    $(document).ready(function() {

      const table = $('#ssiTable').DataTable({
        ajax: {
          url: '{{ route('ssi.list') }}',
          dataSrc: 'data',
          data: function(d) {
            // ติ๊ก "รายการที่ปิดงานแล้ว" → ขอรายการที่ถูกซ่อนแทน (admin เท่านั้น)
            d.show_done = $('#ssiShowDone').is(':checked') ? 1 : 0;
          },
        },
        columns: [{
            data: 'No'
          },
          {
            data: 'FullName',
            orderable: false
          },
          {
            data: 'Phone',
            orderable: false
          },
          {
            data: 'model',
            orderable: false
          },
          {
            data: 'DeliveryDate',
            orderable: false
          },
          {
            data: null,
            orderable: false,
            className: 'text-center',
            render: function(data, type, row) {
              const resolvedTag = row.has_resolved
                ? `<div class="text-success" style="font-size:.72rem;margin-top:2px;">
                     <i class="bx bx-check-circle"></i> แก้ไขปัญหาแล้ว
                   </div>`
                : '';

              let html;
              if (!row.ssi_answered) {
                // ยังไม่กรอกคะแนนเลย
                html = '<span class="badge bg-label-secondary">ยังไม่ประเมิน</span>';
              } else if (!row.ssi_complete) {
                // กรอกมาบางส่วน ยังไม่ครบ — ยังไม่สรุปคะแนน
                html = `<span class="badge bg-label-warning text-warning">กรอกไม่ครบ</span>
                        <div class="text-muted" style="font-size:.72rem;margin-top:2px;">
                          ${row.ssi_answered}/${row.ssi_total} ข้อ
                        </div>`;
              } else {
                // กรอกครบ → คะแนนรวมจริง
                const low = row.ssi_score < 90;
                html = `<span class="badge ${low ? 'bg-label-danger' : 'bg-label-success'}">${row.ssi_score}%</span>`;
                if (low) {
                  html += `<div class="text-danger" style="font-size:.72rem;margin-top:2px;">
                             <i class="bx bx-error-circle"></i> SSI &lt; 90%
                           </div>`;
                }
              }
              const doneTag = row.completed_at
                ? `<div class="text-muted" style="font-size:.72rem;margin-top:2px;">
                     <i class="bx bx-check-double"></i> ปิดงาน ${row.completed_at}
                   </div>`
                : '';

              return html + resolvedTag + doneTag;
            },
          },
          {
            data: null,
            orderable: false,
            className: 'text-center',
            render: function(data, type, row) {
              // รายการที่ปิดงานแล้ว (ซ่อนอยู่) → ดึงกลับมาแก้ได้อย่างเดียว
              if (row.is_done) {
                return `
                <div class="d-flex gap-1 justify-content-center">
                  <button class="btn btn-icon btn-secondary text-white btn-ssi-reopen"
                      data-id="${row.salecar_id}" data-name="${row.FullName}"
                      title="ดึงกลับมาแก้ไข">
                    <i class="bx bx-undo"></i>
                  </button>
                </div>`;
              }

              const completeBtn = row.can_complete
                ? `<button class="btn btn-icon btn-success text-white btn-ssi-complete"
                       data-id="${row.salecar_id}" title="ตรวจสอบเสร็จแล้ว">
                     <i class="bx bx-check-double"></i>
                   </button>`
                : `<button class="btn btn-icon btn-success text-white" disabled
                       style="opacity:.4;cursor:not-allowed;"
                       title="SSI ต่ำกว่า 90% และยังไม่มีวันที่แก้ไขปัญหา จึงยังปิดงานไม่ได้">
                     <i class="bx bx-check-double"></i>
                   </button>`;
              return `
                <div class="d-flex gap-1 justify-content-center">
                  <a href="/ssi/${row.salecar_id}/edit"
                     class="btn btn-icon btn-warning text-white"
                     title="แก้ไข / บันทึก SSI">
                    <i class="bx bx-edit"></i>
                  </a>
                  ${completeBtn}
                </div>`;
            },
          },
        ],
        language: {
          lengthMenu: 'แสดง _MENU_ แถว',
          zeroRecords: 'ไม่พบข้อมูล',
          info: 'แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ',
          infoEmpty: 'ไม่มีข้อมูล',
          search: 'ค้นหา:',
          paginate: {
            next: 'ถัดไป',
            previous: 'ก่อนหน้า'
          },
        },
        pageLength: 10,
        order: [
          [0, 'asc']
        ],
      });

      $('#btnExportSsi').on('click', function() {
        const dateFrom = $('#ssiDateFrom').val();
        const dateTo = $('#ssiDateTo').val();
        if (!dateFrom || !dateTo) {
          Swal.fire({
            icon: 'warning',
            title: 'กรุณาเลือกวันที่',
            timer: 1500,
            showConfirmButton: true
          });
          return;
        }
        if (dateFrom > dateTo) {
          Swal.fire({
            icon: 'warning',
            title: 'วันที่เริ่มต้นต้องไม่เกินวันที่สิ้นสุด',
            timer: 1800,
            showConfirmButton: true
          });
          return;
        }
        window.location.href = '{{ route('ssi.export') }}?date_from=' + dateFrom + '&date_to=' + dateTo;
      });

      // ── สลับดูรายการที่ปิดงานแล้ว (admin) ──
      $('#ssiShowDone').on('change', function() {
        table.ajax.reload();
      });

      // ── ดึงงานที่ปิดแล้วกลับมาแก้ (admin) ──
      $('#ssiTable').on('click', '.btn-ssi-reopen', function() {
        const salecarId = $(this).data('id');
        const name = $(this).data('name');

        Swal.fire({
          icon: 'question',
          title: 'ดึงกลับมาแก้ไข',
          html: `<p>ดึงรายการของ <b>${name}</b> กลับไปที่รายการ SSI ใช่ไหม?</p>
                 <p class="text-muted small mb-0"><i class="bx bx-info-circle me-1"></i>รายการจะกลับมาแก้ไขได้ จนกว่าจะกดเสร็จสิ้นอีกครั้ง</p>`,
          showCancelButton: true,
          confirmButtonText: 'ใช่, ดึงกลับ',
          cancelButtonText: 'ยกเลิก',
          confirmButtonColor: '#6c5ffc',
          cancelButtonColor: '#d33',
        }).then(result => {
          if (!result.isConfirmed) return;

          $.ajax({
            url: `/ssi/${salecarId}/reopen`,
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res) {
              Swal.fire({
                icon: 'success',
                title: 'สำเร็จ',
                text: res.message,
                timer: 1500,
                showConfirmButton: true
              });
              table.ajax.reload(null, false);
            },
            error: function(xhr) {
              Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                text: xhr.responseJSON?.message ?? 'ไม่สามารถดึงรายการกลับได้'
              });
            }
          });
        });
      });

      $('#ssiTable').on('click', '.btn-ssi-complete', function() {
        const salecarId = $(this).data('id');

        Swal.fire({
          icon: 'question',
          title: 'ยืนยันการตรวจสอบเสร็จสิ้น',
          html: `<p>คุณกรอกข้อมูล SSI เรียบร้อยแล้วใช่ไหม?</p>
                 <p class="text-danger small mb-0"><i class="bx bx-info-circle me-1"></i>หลังจากยืนยัน รายการนี้จะไม่แสดงในหน้านี้อีกต่อไป</p>`,
          showCancelButton: true,
          confirmButtonText: 'ใช่, เสร็จสิ้นแล้ว',
          cancelButtonText: 'ยกเลิก',
          confirmButtonColor: '#6c5ffc',
          cancelButtonColor: '#d33',
        }).then(result => {
          if (!result.isConfirmed) return;

          $.ajax({
            url: `/ssi/${salecarId}/complete`,
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res) {
              Swal.fire({
                icon: 'success',
                title: 'เสร็จสิ้น',
                text: res.message,
                timer: 1500,
                showConfirmButton: true
              });
              table.ajax.reload(null, false);
            },
            error: function(xhr) {
              Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                text: xhr.responseJSON?.message ?? 'ไม่สามารถบันทึกได้'
              });
            }
          });
        });
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
            <i class="bx bx-star fs-4 text-white"></i>
          </div>
          <div>
            <div class="text-white fw-bold mf-hd-title">SSI หลังส่งมอบ</div>
            <div class="text-white mf-hd-sub">Customer Satisfaction Survey</div>
          </div>
        </div>

        <div class="card-body pt-3">

          {{-- ── Action bar ── --}}
          <div class="po-filter-bar d-flex align-items-center gap-2 justify-content-end">
            @if ($canReopen)
              {{-- admin: เปิดดูรายการที่ปิดงานแล้ว เพื่อดึงกลับมาแก้ --}}
              <div class="form-check form-switch me-auto mb-0">
                <input class="form-check-input" type="checkbox" id="ssiShowDone">
                <label class="form-check-label small text-muted" for="ssiShowDone">
                  รายการที่ปิดงานแล้ว
                </label>
              </div>
            @endif
            <span class="text-muted small">วันส่งมอบ</span>
            <input type="date" id="ssiDateFrom" class="form-control form-control-sm"
              value="{{ now()->format('Y-m-d') }}" style="width:155px;" data-no-icon>
            <span class="text-muted small">ถึง</span>
            <input type="date" id="ssiDateTo" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}"
              style="width:155px;" data-no-icon>
            <button type="button" id="btnExportSsi" class="btn btn-warning btn-sm">
              <i class="bx bx-file me-1"></i> รายงาน
            </button>
          </div>

          <div class="table-responsive">
            <table class="table table-bordered tbl-table tbl-styled" id="ssiTable">
              <thead>
                <tr>
                  <th class="tbl-th-no">No.</th>
                  <th>ชื่อ - นามสกุลลูกค้า</th>
                  <th>เบอร์โทร</th>
                  <th>รุ่นรถ</th>
                  <th class="text-center">วันที่ส่งมอบ</th>
                  <th class="text-center" style="width:110px;">ผล SSI</th>
                  <th class="tbl-th-action" style="width:100px;">Action</th>
                </tr>
              </thead>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>

@endsection
