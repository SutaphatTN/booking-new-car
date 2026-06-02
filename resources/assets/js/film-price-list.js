$.ajaxSetup({
  headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

// ── DataTable ──────────────────────────────────────────────
let filmPriceTable;

$(document).ready(function () {
  if ($.fn.DataTable.isDataTable('.filmPriceTable')) {
    $('.filmPriceTable').DataTable().destroy();
  }

  filmPriceTable = $('.filmPriceTable').DataTable({
    ajax: '/film-price-list/list',
    columns: [
      { data: 'No' },
      { data: 'model' },
      { data: 'film_brand' },
      { data: 'position', className: 'text-center' },
      { data: 'shade' },
      { data: 'sqft', className: 'text-end' },
      { data: 'price', className: 'text-end' },
      { data: 'commission', className: 'text-end' },
      { data: 'Action', orderable: false, searchable: false },
    ],
    paging: true,
    lengthChange: true,
    searching: true,
    ordering: false,
    info: true,
    pageLength: 25,
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

// ── Position toggle (input modal) ─────────────────────────
$(document).on('change', '#fp_position', function () {
  togglePositionFields($(this).val(), '#fp_body_shades', '#fp_sunroof_shades', '#fp_body_shade', '#fp_sunroof_shade');
});

$(document).on('change', '#ep_position', function () {
  togglePositionFields($(this).val(), '#ep_body_shades', '#ep_sunroof_shades', '#ep_body_shade', '#ep_sunroof_shade');
});

function togglePositionFields(pos, bodyDiv, sunroofDiv, bodySelect, sunroofSelect) {
  if (pos === 'รอบคัน') {
    $(bodyDiv).removeClass('d-none');
    $(sunroofDiv).addClass('d-none');
    $(bodySelect).attr('required', true);
    $(sunroofSelect).removeAttr('required').val('');
  } else if (pos === 'sunroof') {
    $(bodyDiv).addClass('d-none');
    $(sunroofDiv).removeClass('d-none');
    $(sunroofSelect).attr('required', true);
    $(bodySelect).removeAttr('required').val('');
  } else {
    $(bodyDiv).addClass('d-none');
    $(sunroofDiv).addClass('d-none');
  }
}

// ── Auto Calculate ─────────────────────────────────────────
function calcFilmPrice(filmBrandId, sqft, priceField, commissionField) {
  if (!filmBrandId || !sqft) {
    Swal.fire({ icon: 'warning', title: 'แจ้งเตือน', text: 'กรุณาเลือกยี่ห้อฟิล์มและระบุจำนวน ตร.ฟุตก่อน' });
    return;
  }

  $.get('/film-price-list/calculate', { film_brand_id: filmBrandId, sqft: sqft }, function (res) {
    if (res.success) {
      $(priceField).val(parseFloat(res.price).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
      $(commissionField).val(parseFloat(res.commission).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
      Swal.fire({
        icon: 'success', title: 'คำนวณสำเร็จ',
        html: `ต้นทุน/ตร.ฟุต: <b>${res.detail.cost_per_sqft}</b><br>` +
              `ต้นทุนฟิล์ม: <b>${res.detail.film_cost.toLocaleString()}</b><br>` +
              `GP: <b>${res.detail.gp_pct}%</b> | Commission: <b>${res.detail.commission_pct}%</b>`,
        timer: 3000, showConfirmButton: false
      });
    } else {
      Swal.fire({ icon: 'warning', title: 'แจ้งเตือน', text: res.message });
    }
  }).fail(function () {
    Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: 'กรุณาติดต่อแอดมิน' });
  });
}

$(document).on('click', '.btnCalcFilmPrice', function () {
  calcFilmPrice($('#fp_film_brand_id').val(), $('#fp_sqft').val(), '#fp_price', '#fp_commission');
});

$(document).on('click', '.btnCalcFilmPriceEdit', function () {
  calcFilmPrice($('#ep_film_brand_id').val(), $('#ep_sqft').val(), '#ep_price', '#ep_commission');
});

// ── money-input format ─────────────────────────────────────
$(document).on('input', '.money-input', function () {
  let value = this.value.replace(/,/g, '');
  if (value === '' || isNaN(value)) { this.value = ''; return; }
  this.value = parseFloat(value).toLocaleString();
});

$(document).on('blur', '.money-input', function () {
  let value = this.value.replace(/,/g, '');
  if (value && !isNaN(value)) {
    this.value = parseFloat(value).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }
});

// ── Modal helpers ──────────────────────────────────────────
$(document).on('hide.bs.modal', '.inputFilmPrice', function () {
  setTimeout(() => { document.activeElement.blur(); $('body').trigger('focus'); }, 1);
});
$(document).on('hide.bs.modal', '.editFilmPrice', function () {
  setTimeout(() => { document.activeElement.blur(); $('body').trigger('focus'); }, 1);
});

// ── Open Input Modal ───────────────────────────────────────
$(document).on('click', '.btnInputFilmPrice', function () {
  $.get('/film-price-list/create', function (html) {
    $('.inputFilmPriceModal').html(html);
    $('.inputFilmPrice').modal('show');
  });
});

// ── Store ──────────────────────────────────────────────────
$(document).on('click', '.btnStoreFilmPrice', function () {
  const form = document.getElementById('formInputFilmPrice');
  if (!form.checkValidity()) { form.reportValidity(); return; }

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
        Swal.fire({ icon: 'success', title: 'สำเร็จ', text: res.message, timer: 1500, showConfirmButton: false });
        $('.inputFilmPrice').modal('hide');
        filmPriceTable.ajax.reload();
      } else {
        Swal.fire({ icon: 'warning', title: 'แจ้งเตือน', text: res.message });
      }
    },
    error: function () {
      Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: 'กรุณาติดต่อแอดมิน' });
    },
    complete: function () {
      $btn.prop('disabled', false).html('<i class="bx bx-save me-1"></i>บันทึก');
    }
  });
});

// ── Open Edit Modal ────────────────────────────────────────
$(document).on('click', '.btnEditFilmPrice', function () {
  const id = $(this).data('id');
  $.get('/film-price-list/' + id + '/edit', function (html) {
    $('.editFilmPriceModal').html(html);
    $('.editFilmPrice').modal('show');
  });
});

// ── Update ─────────────────────────────────────────────────
$(document).on('click', '.btnUpdateFilmPrice', function () {
  const form = document.getElementById('formEditFilmPrice');
  if (!form.checkValidity()) { form.reportValidity(); return; }

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
        Swal.fire({ icon: 'success', title: 'สำเร็จ', text: res.message, timer: 1500, showConfirmButton: false });
        $('.editFilmPrice').modal('hide');
        filmPriceTable.ajax.reload();
      } else {
        Swal.fire({ icon: 'warning', title: 'แจ้งเตือน', text: res.message });
      }
    },
    error: function () {
      Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: 'กรุณาติดต่อแอดมิน' });
    },
    complete: function () {
      $btn.prop('disabled', false).html('<i class="bx bx-save me-1"></i>บันทึก');
    }
  });
});

// ── Delete ─────────────────────────────────────────────────
$(document).on('click', '.btnDeleteFilmPrice', function () {
  const id = $(this).data('id');

  Swal.fire({
    title: 'ยืนยันการลบ?',
    text: 'ข้อมูลจะถูกลบออกจากระบบ',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'ลบ',
    cancelButtonText: 'ยกเลิก'
  }).then(function (result) {
    if (result.isConfirmed) {
      $.ajax({
        url: '/film-price-list/' + id,
        type: 'POST',
        data: { _method: 'DELETE' },
        success: function (res) {
          if (res.success) {
            Swal.fire({ icon: 'success', title: 'ลบสำเร็จ', text: res.message, timer: 1500, showConfirmButton: false });
            filmPriceTable.ajax.reload();
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
