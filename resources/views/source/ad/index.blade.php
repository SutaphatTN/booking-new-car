@extends('layouts/contentNavbarLayout')
@section('title', 'ชื่อแอด')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card tbl-card">

      {{-- ── Card header ── --}}
      <div class="po-card-header d-flex align-items-center gap-3">
        <div class="po-hd-icon">
          <i class="bx bx-bullseye fs-4 text-white"></i>
        </div>
        <div>
          <div class="text-white fw-bold mf-hd-title">ชื่อแอด</div>
          <div class="text-white mf-hd-sub">คลิปที่ยิงแอด (สำหรับตัวเลือกในหน้าเพิ่มการติดตาม)</div>
        </div>
      </div>

      <div class="card-body pt-3">

        {{-- ── แถบเครื่องมือ : ฟิลเตอร์ + ปุ่มเพิ่ม ── --}}
        <div class="po-filter-bar d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
          <div class="d-flex align-items-center gap-2">
            <label class="po-label mb-0" for="adStatusFilter"><i class="bx bx-filter-alt me-1"></i> สถานะ</label>
            <select id="adStatusFilter" class="form-select form-select-sm" style="width:auto;">
              <option value="active" selected>กำลังใช้งาน</option>
              <option value="archived">เก็บแล้ว</option>
            </select>
          </div>
          <button class="btn btn-secondary btn-sm btnOpenAdModal">
            <i class="bx bx-plus me-1"></i> เพิ่มแอด
          </button>
        </div>

        <div class="table-responsive">
          <table class="table table-bordered tbl-table tbl-styled adTable w-100">
            <thead>
              <tr>
                <th class="tbl-th-no">No.</th>
                <th style="width:200px;">ชื่อแอด</th>
                <th>URL</th>
                <th style="width:120px;">สถานะ</th>
                <th style="width:120px;">จัดการ</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>

{{-- ── Modal เพิ่ม/แก้ไขแอด ── --}}
<div class="modal fade" id="adModal" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--input">

      {{-- Header --}}
      <div class="modal-header mf-header mf-header--input px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-bullseye fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title" id="adModalTitle">เพิ่มแอด</h6>
            <small class="text-white mf-hd-sub">คลิปที่ยิงแอด</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">
        <form id="adForm" autocomplete="off">
          <input type="hidden" id="ad_id" name="id">

          {{-- Section : ข้อมูลแอด --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon indigo">
                <i class="bx bx-purchase-tag"></i>
              </div>
              <span class="mf-section-title">ข้อมูลแอด</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-12">
                  <label for="ad_name" class="mf-label form-label">
                    <i class="bx bx-purchase-tag"></i> ชื่อแอด <span class="text-danger">*</span>
                  </label>
                  <input id="ad_name" type="text" class="form-control" name="name" placeholder="ชื่อคลิป/แอด..." maxlength="255" required>
                </div>

                <div class="col-12">
                  <label for="ad_url" class="mf-label form-label">
                    <i class="bx bx-link"></i> URL <span class="text-secondary">(ถ้ามี)</span>
                  </label>
                  <input id="ad_url" type="text" class="form-control" name="url" placeholder="https://..." maxlength="2000">
                </div>

              </div>
            </div>
          </div>

          {{-- Actions --}}
          <div class="d-flex justify-content-end gap-2 pt-1">
            <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
              <i class="bx bx-x me-1"></i>ยกเลิก
            </button>
            <button type="submit" class="btn btn-primary px-5" id="btnSaveAd">
              <i class="bx bx-save me-1"></i>บันทึก
            </button>
          </div>

        </form>
      </div>

    </div>
  </div>
</div>
@endsection

@section('page-script')
<script>
  (function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': csrf } });

    const adModal = new bootstrap.Modal(document.getElementById('adModal'));

    const adTable = $('.adTable').DataTable({
      ajax: {
        url: '{{ route('ad.list') }}',
        data: function (d) {
          d.status = $('#adStatusFilter').val();
        },
      },
      columns: [
        { data: 'No', className: 'text-center' },
        { data: 'name' },
        { data: 'url' },
        { data: 'status', className: 'text-center' },
        { data: 'Action', orderable: false, searchable: false, className: 'text-center' },
      ],
      ordering: false,
      pageLength: 10,
      language: {
        lengthMenu: 'แสดง _MENU_ แถว',
        zeroRecords: 'ไม่พบข้อมูล',
        info: 'แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ',
        infoEmpty: 'ไม่มีข้อมูล',
        search: 'ค้นหา:',
        paginate: { next: 'ถัดไป', previous: 'ก่อนหน้า' },
      },
    });

    // เปลี่ยนฟิลเตอร์ → โหลดตารางใหม่
    $('#adStatusFilter').on('change', function () {
      adTable.ajax.reload();
    });

    // เปิด modal โหมด "เพิ่ม"
    $('.btnOpenAdModal').on('click', function () {
      $('#adForm')[0].reset();
      $('#ad_id').val('');
      $('#adModalTitle').text('เพิ่มแอด');
      adModal.show();
    });

    // เปิด modal โหมด "แก้ไข" (ดึงข้อมูลมา prefill)
    $('.adTable tbody').on('click', '.btnEditAd', function () {
      const id = $(this).data('id');
      $.get('{{ url('marketing/ad') }}/' + id + '/edit', function (ad) {
        $('#ad_id').val(ad.id);
        $('#ad_name').val(ad.name);
        $('#ad_url').val(ad.url || '');
        $('#adModalTitle').text('แก้ไขแอด');
        adModal.show();
      }).fail(function () {
        Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: 'ไม่พบข้อมูลแอด' });
      });
    });

    // บันทึก (เพิ่ม/แก้ไข)
    $('#adForm').on('submit', function (e) {
      e.preventDefault();
      const id = $('#ad_id').val();
      const name = $('#ad_name').val().trim();
      const url = $('#ad_url').val().trim();
      if (!name) return;

      const isEdit = !!id;
      $.ajax({
        url: isEdit ? ('{{ url('marketing/ad') }}/' + id) : '{{ route('ad.store') }}',
        method: 'POST',
        data: { name, url, _method: isEdit ? 'PUT' : 'POST' },
        success: function (res) {
          adModal.hide();
          // เพิ่มใหม่จะเป็น "กำลังใช้งาน" เสมอ → สลับฟิลเตอร์ให้เห็นรายการที่เพิ่งเพิ่ม
          if (!isEdit) $('#adStatusFilter').val('active');
          adTable.ajax.reload(null, false);
          Swal.fire({ icon: 'success', title: 'สำเร็จ', text: res.message, timer: 1200, showConfirmButton: false });
        },
        error: function (xhr) {
          Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: xhr.responseJSON?.message || 'กรุณาติดต่อแอดมิน' });
        },
      });
    });

    // เก็บ / นำกลับมาแสดง
    function toggleAd(id, url, confirmText) {
      Swal.fire({
        title: 'ยืนยัน',
        text: confirmText,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'ตกลง',
        cancelButtonText: 'ยกเลิก',
      }).then(function (result) {
        if (!result.isConfirmed) return;
        $.ajax({
          url: url,
          method: 'PATCH',
          success: function (res) {
            adTable.ajax.reload(null, false);
            Swal.fire({ icon: 'success', title: 'สำเร็จ', text: res.message, timer: 1200, showConfirmButton: false });
          },
          error: function (xhr) {
            Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: xhr.responseJSON?.message || 'กรุณาติดต่อแอดมิน' });
          },
        });
      });
    }

    $('.adTable tbody').on('click', '.btnArchiveAd', function () {
      toggleAd($(this).data('id'), '{{ url('marketing/ad') }}/' + $(this).data('id') + '/archive', 'เก็บแอดนี้? จะไม่แสดงเป็นตัวเลือกในหน้าเพิ่มการติดตาม');
    });

    $('.adTable tbody').on('click', '.btnRestoreAd', function () {
      toggleAd($(this).data('id'), '{{ url('marketing/ad') }}/' + $(this).data('id') + '/restore', 'นำแอดนี้กลับมาแสดงเป็นตัวเลือก?');
    });
  })();
</script>
@endsection
