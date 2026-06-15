$.ajaxSetup({
  headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

// ── DataTable ──────────────────────────────────────────────
let filmStockTable;

$(document).ready(function () {
  if ($.fn.DataTable.isDataTable('.filmStockTable')) {
    $('.filmStockTable').DataTable().destroy();
  }

  filmStockTable = $('.filmStockTable').DataTable({
    ajax: '/stock-film/list',
    columns: [
      { data: 'No' },
      { data: 'stock_no' },
      { data: 'part_no' },
      // { data: 'brand_group' },
      { data: 'film_brand' },
      { data: 'shade', className: 'text-center' },
      // { data: 'withdrawal_date' },
      // { data: 'initial_qty', className: 'text-end' },
      // { data: 'used_qty', className: 'text-end' },
      { data: 'remaining_qty', className: 'text-end' },
      { data: 'status', className: 'text-center', orderable: false, searchable: false },
      // { data: 'inspection_date' },
      // { data: 'inspection_qty', className: 'text-end' },
      // { data: 'inspection_diff', className: 'text-end', orderable: false },
      // { data: 'inspection_result', className: 'text-center', orderable: false, searchable: false },
      { data: 'Action', orderable: false, searchable: false }
    ],
    paging: true,
    lengthChange: true,
    searching: true,
    ordering: false,
    info: true,
    pageLength: 10,
    autoWidth: false,
    language: {
      lengthMenu: 'แสดง _MENU_ แถว',
      zeroRecords: 'ไม่พบข้อมูล',
      info: 'แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ',
      infoEmpty: 'ไม่มีข้อมูล',
      search: 'ค้นหา:',
      paginate: { next: 'ถัดไป', previous: 'ก่อนหน้า' }
    }
  });
});

// ── Preview Stock No. ──────────────────────────────────────
function fetchStockNoPreview() {
  const filmBrandId = $('#inp_film_brand_id').val();
  const shade = $('#inp_shade').val();
  const date = $('#inp_withdrawal_date').val();

  if (!filmBrandId || !shade || !date) {
    $('#preview_stock_no').val('');
    $('#stock_no_warning').addClass('d-none');
    return;
  }

  $.get('/stock-film/preview-stock-no', { film_brand_id: filmBrandId, shade, withdrawal_date: date }, function (res) {
    $('#preview_stock_no').val(res.stock_no);
    if (res.exists) {
      $('#stock_no_warning').removeClass('d-none');
    } else {
      $('#stock_no_warning').addClass('d-none');
    }
  });
}

$(document).on('change', '#inp_film_brand_id, #inp_shade, #inp_withdrawal_date', fetchStockNoPreview);

// ── Modal helpers ──────────────────────────────────────────
$(document).on('hide.bs.modal', '.viewFilm', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});
$(document).on('hide.bs.modal', '.inputFilm', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});
$(document).on('hide.bs.modal', '.editFilm', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

// ── Open View-More Modal ───────────────────────────────────
$(document).on('click', '.btnViewFilm', function () {
  const id = $(this).data('id');
  $.get('/stock-film/' + id + '/view-more', function (html) {
    $('.viewMoreFilmModal').html(html);
    $('.viewFilm').modal('show');
  });
});

// ── Open Input Modal ───────────────────────────────────────
$(document).on('click', '.btnInputFilm', function () {
  $.get('/stock-film/create', function (html) {
    $('.inputFilmModal').html(html);
    $('.inputFilm').modal('show');
  });
});

// ── Store ──────────────────────────────────────────────────
$(document).on('click', '.btnStoreFilm', function () {
  const form = document.getElementById('formInputFilm');
  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }

  const warning = $('#stock_no_warning');
  if (!warning.hasClass('d-none')) {
    Swal.fire({ icon: 'warning', title: 'แจ้งเตือน', text: 'Stock No. นี้มีอยู่แล้วในระบบ กรุณาเปลี่ยนวันที่เบิก' });
    return;
  }

  const $btn = $(this);
  $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>กำลังบันทึก...');

  $.ajax({
    url: $(form).attr('action'),
    type: 'POST',
    data: new FormData(form),
    contentType: false,
    processData: false,
    success: function (res) {
      if (res.success) {
        Swal.fire({ icon: 'success', title: 'สำเร็จ', text: res.message, timer: 1500, showConfirmButton: true });
        $('.inputFilm').modal('hide');
        filmStockTable.ajax.reload();
      } else {
        Swal.fire({ icon: 'warning', title: 'แจ้งเตือน', text: res.message });
      }
    },
    error: function (xhr) {
      const msg = xhr.responseJSON?.message || 'เกิดข้อผิดพลาด';
      Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: msg });
    },
    complete: function () {
      $btn.prop('disabled', false).html('<i class="bx bx-save me-1"></i> บันทึก');
    }
  });
});

// ── Open Edit Modal ────────────────────────────────────────
$(document).on('click', '.btnEditFilm', function () {
  const id = $(this).data('id');
  $.get('/stock-film/' + id + '/edit', function (html) {
    $('.editFilmModal').html(html);
    $('.editFilm').modal('show');
  });
});

// ── Update ─────────────────────────────────────────────────
$(document).on('click', '.btnUpdateFilm', function () {
  const form = document.getElementById('formEditFilm');
  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }

  const $btn = $(this);
  $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>กำลังบันทึก...');

  $.ajax({
    url: $(form).attr('action'),
    type: 'POST',
    data: new FormData(form),
    contentType: false,
    processData: false,
    success: function (res) {
      if (res.success) {
        Swal.fire({ icon: 'success', title: 'สำเร็จ', text: res.message, timer: 1500, showConfirmButton: true });
        $('.editFilm').modal('hide');
        filmStockTable.ajax.reload();
      } else {
        Swal.fire({ icon: 'warning', title: 'แจ้งเตือน', text: res.message });
      }
    },
    error: function () {
      Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: 'กรุณาติดต่อแอดมิน' });
    },
    complete: function () {
      $btn.prop('disabled', false).html('<i class="bx bx-save me-1"></i> บันทึก');
    }
  });
});

// ── Audit complete (admin/audit) ───────────────────────────
$(document).on('click', '.btnAuditComplete', function () {
  const id = $(this).data('id');

  Swal.fire({
    title: 'ยืนยันตรวจสอบเสร็จสิ้น?',
    text: 'รายการนี้จะถูกซ่อนออกจากหน้าข้อมูลฟิล์ม',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#198754',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'เสร็จสิ้น',
    cancelButtonText: 'ยกเลิก'
  }).then(function (result) {
    if (!result.isConfirmed) return;
    $.ajax({
      url: '/stock-film/' + id + '/audit-complete',
      type: 'POST',
      success: function (res) {
        if (res.success) {
          Swal.fire({ icon: 'success', title: 'สำเร็จ', text: res.message, timer: 1500, showConfirmButton: true });
          filmStockTable.ajax.reload();
        } else {
          Swal.fire({ icon: 'warning', title: 'แจ้งเตือน', text: res.message });
        }
      },
      error: function (xhr) {
        Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: xhr.responseJSON?.message || 'กรุณาติดต่อแอดมิน' });
      }
    });
  });
});

// ── Delete ─────────────────────────────────────────────────
$(document).on('click', '.btnDeleteFilm', function () {
  const id = $(this).data('id');

  Swal.fire({
    title: 'คุณแน่ใจหรือไม่?',
    text: 'คุณต้องการลบข้อมูลนี้ใช่หรือไม่?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#6c5ffc',
    cancelButtonColor: '#d33',
    confirmButtonText: 'ใช่, ลบเลย!',
    cancelButtonText: 'ยกเลิก'
  }).then(function (result) {
    if (result.isConfirmed) {
      $.ajax({
        url: '/stock-film/' + id,
        type: 'POST',
        data: { _method: 'DELETE' },
        success: function (res) {
          if (res.success) {
            Swal.fire({ icon: 'success', title: 'ลบสำเร็จ', text: res.message, timer: 1500, showConfirmButton: true });
            filmStockTable.ajax.reload();
          } else {
            Swal.fire({ icon: 'warning', title: 'แจ้งเตือน', text: res.message });
          }
        },
        error: function () {
          Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: 'กรุณาติดต่อแอดมิน' });
        }
      });
    }
  });
});
