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
      { data: 'sqft', className: 'text-end' },
      { data: 'Action', orderable: false, searchable: false },
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

// ── Brand rows (input modal) ───────────────────────────────
function fpBrandRowHtml(idx) {
  const brands  = window.fpFilmBrands || [];
  const opts    = brands.map(fb => `<option value="${fb.id}">${fb.name}</option>`).join('');
  const sunHide = $('#fp_has_sunroof').is(':checked') ? '' : 'd-none';

  return `
    <tr data-idx="${idx}">
      <td>
        <select name="brands[${idx}][film_brand_id]" class="form-select form-select-sm fpBrandSel">
          <option value="">— เลือกยี่ห้อ —</option>
          ${opts}
        </select>
      </td>
      <td>
        <div class="input-group input-group-sm">
          <span class="input-group-text ig-sky">฿</span>
          <input type="text" name="brands[${idx}][price]"
            class="form-control text-end money-input" placeholder="0.00" autocomplete="off">
        </div>
      </td>
      <td>
        <div class="input-group input-group-sm">
          <span class="input-group-text ig-sky">฿</span>
          <input type="text" name="brands[${idx}][commission]"
            class="form-control text-end money-input" placeholder="0.00" autocomplete="off">
        </div>
      </td>
      <td class="col-sunroof ${sunHide}">
        <div class="input-group input-group-sm">
          <span class="input-group-text ig-amber">฿</span>
          <input type="text" name="brands[${idx}][price_sunroof]"
            class="form-control text-end money-input" placeholder="0.00" autocomplete="off">
        </div>
      </td>
      <td class="col-sunroof ${sunHide}">
        <div class="input-group input-group-sm">
          <span class="input-group-text ig-amber">฿</span>
          <input type="text" name="brands[${idx}][commission_sunroof]"
            class="form-control text-end money-input" placeholder="0.00" autocomplete="off">
        </div>
      </td>
      <td class="text-center">
        <button type="button" class="btn btn-sm btn-outline-danger btnRemoveFpBrand">
          <i class="bx bx-trash"></i>
        </button>
      </td>
    </tr>`;
}

$(document).on('click', '.btnAddBrandRow', function () {
  $('#fpNoBrandMsg').remove();
  $('#fpBrandRows').append(fpBrandRowHtml(Date.now()));
});

$(document).on('click', '.btnRemoveFpBrand', function () {
  $(this).closest('tr').remove();
  if ($('#fpBrandRows tr').length === 0) {
    $('#fpBrandRows').html(`
      <tr id="fpNoBrandMsg">
        <td colspan="6" class="text-center text-muted py-3">
          <i class="bx bx-info-circle me-1"></i> กดปุ่ม "เพิ่มยี่ห้อ" เพื่อเพิ่มข้อมูล
        </td>
      </tr>`);
  }
});

// ── Sunroof toggle (input modal) ───────────────────────────
$(document).on('change', '#fp_has_sunroof', function () {
  const on = $(this).is(':checked');
  $('#fp_sunroof_fields').toggleClass('d-none', !on);
  $('#fpBrandTable .col-sunroof').toggleClass('d-none', !on);
  if (!on) {
    $('#fp_sqft_sunroof').val('');
    $('#fpBrandRows input[name*="[price_sunroof]"], #fpBrandRows input[name*="[commission_sunroof]"]').val('');
  }
});

// ── Brand rows (edit modal) ────────────────────────────────
function epBrandRowHtml(idx) {
  const brands  = window.fpFilmBrands || [];
  const opts    = brands.map(fb => `<option value="${fb.id}">${fb.name}</option>`).join('');
  const sunHide = $('#ep_has_sunroof').is(':checked') ? '' : 'd-none';

  return `
    <tr data-idx="${idx}">
      <td>
        <select name="brands[${idx}][film_brand_id]" class="form-select form-select-sm epBrandSel">
          <option value="">— เลือกยี่ห้อ —</option>
          ${opts}
        </select>
      </td>
      <td>
        <div class="input-group input-group-sm">
          <span class="input-group-text ig-sky">฿</span>
          <input type="text" name="brands[${idx}][price]"
            class="form-control text-end money-input" placeholder="0.00" autocomplete="off">
        </div>
      </td>
      <td>
        <div class="input-group input-group-sm">
          <span class="input-group-text ig-sky">฿</span>
          <input type="text" name="brands[${idx}][commission]"
            class="form-control text-end money-input" placeholder="0.00" autocomplete="off">
        </div>
      </td>
      <td class="col-sunroof ${sunHide}">
        <div class="input-group input-group-sm">
          <span class="input-group-text ig-amber">฿</span>
          <input type="text" name="brands[${idx}][price_sunroof]"
            class="form-control text-end money-input" placeholder="0.00" autocomplete="off">
        </div>
      </td>
      <td class="col-sunroof ${sunHide}">
        <div class="input-group input-group-sm">
          <span class="input-group-text ig-amber">฿</span>
          <input type="text" name="brands[${idx}][commission_sunroof]"
            class="form-control text-end money-input" placeholder="0.00" autocomplete="off">
        </div>
      </td>
      <td class="text-center">
        <button type="button" class="btn btn-sm btn-outline-danger btnRemoveEpBrand">
          <i class="bx bx-trash"></i>
        </button>
      </td>
    </tr>`;
}

$(document).on('click', '.btnAddEpBrandRow', function () {
  $('#epNoBrandMsg').remove();
  const idx = (window.epNextIdx !== undefined ? window.epNextIdx++ : Date.now());
  $('#epBrandRows').append(epBrandRowHtml(idx));
});

$(document).on('click', '.btnRemoveEpBrand', function () {
  $(this).closest('tr').remove();
  if ($('#epBrandRows tr').length === 0) {
    $('#epBrandRows').html(`
      <tr id="epNoBrandMsg">
        <td colspan="6" class="text-center text-muted py-3">
          <i class="bx bx-info-circle me-1"></i> กดปุ่ม "เพิ่มยี่ห้อ" เพื่อเพิ่มข้อมูล
        </td>
      </tr>`);
  }
});

// ── Sunroof toggle (edit modal) ────────────────────────────
$(document).on('change', '#ep_has_sunroof', function () {
  const on = $(this).is(':checked');
  $('#ep_sunroof_fields').toggleClass('d-none', !on);
  $('#epBrandTable .col-sunroof').toggleClass('d-none', !on);
  if (!on) {
    $('#ep_sqft_sunroof').val('');
    $('#epBrandRows input[name*="[price_sunroof]"], #epBrandRows input[name*="[commission_sunroof]"]').val('');
  }
});

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

// ── Store (multi-brand) ────────────────────────────────────
$(document).on('click', '.btnStoreFilmPrice', function () {
  const modelId = $('#fp_model_id').val();
  const sqft    = $('#fp_sqft').val();

  if (!modelId) { Swal.fire({ icon: 'warning', text: 'กรุณาเลือกรุ่นรถ' }); return; }
  if (!sqft || parseFloat(sqft) <= 0) { Swal.fire({ icon: 'warning', text: 'กรุณาระบุจำนวน ตร.ฟุต' }); return; }

  const brands = [];
  let allValid = true;

  $('#fpBrandRows tr[data-idx]').each(function () {
    const idx     = $(this).data('idx');
    const brandId = $(this).find('.fpBrandSel').val();
    if (!brandId) { allValid = false; return false; }
    brands.push({
      film_brand_id:      brandId,
      price:              $(this).find(`input[name="brands[${idx}][price]"]`).val(),
      commission:         $(this).find(`input[name="brands[${idx}][commission]"]`).val(),
      price_sunroof:      $(this).find(`input[name="brands[${idx}][price_sunroof]"]`).val(),
      commission_sunroof: $(this).find(`input[name="brands[${idx}][commission_sunroof]"]`).val(),
    });
  });

  if (!allValid) { Swal.fire({ icon: 'warning', text: 'กรุณาเลือกยี่ห้อฟิล์มให้ครบทุกแถว' }); return; }
  if (!brands.length) { Swal.fire({ icon: 'warning', text: 'กรุณาเพิ่มยี่ห้อฟิล์มอย่างน้อย 1 รายการ' }); return; }

  const hasSunroof = $('#fp_has_sunroof').is(':checked');

  const payload = {
    model_id:    modelId,
    sqft:        sqft,
    has_sunroof: hasSunroof ? 1 : 0,
    sqft_sunroof: hasSunroof ? $('#fp_sqft_sunroof').val() : null,
    brands,
  };

  const $btn = $(this);
  $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>กำลังบันทึก...');

  $.ajax({
    url: '/film-price-list',
    type: 'POST',
    data: JSON.stringify(payload),
    contentType: 'application/json',
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

// ── Open Edit Modal (by model) ─────────────────────────────
$(document).on('click', '.btnEditFilmPrice', function () {
  const modelId = $(this).data('model-id');
  $.get('/film-price-list/' + modelId + '/edit-model', function (html) {
    $('.editFilmPriceModal').html(html);
    $('.editFilmPriceModel').modal('show');
  });
});

$(document).on('hide.bs.modal', '.editFilmPriceModel', function () {
  setTimeout(() => { document.activeElement.blur(); $('body').trigger('focus'); }, 1);
});

// ── Update (by model) ──────────────────────────────────────
$(document).on('click', '.btnUpdateFilmPriceModel', function () {
  const modelId = $('#ep_model_id').val();
  const sqft    = $('#ep_sqft').val();

  if (!sqft || parseFloat(sqft) <= 0) { Swal.fire({ icon: 'warning', text: 'กรุณาระบุจำนวน ตร.ฟุต' }); return; }

  const brands = [];
  let allValid = true;

  $('#epBrandRows tr[data-idx]').each(function () {
    const idx     = $(this).data('idx');
    const brandId = $(this).find('.epBrandSel').val();
    if (!brandId) { allValid = false; return false; }
    brands.push({
      film_brand_id:      brandId,
      price:              $(this).find(`input[name="brands[${idx}][price]"]`).val(),
      commission:         $(this).find(`input[name="brands[${idx}][commission]"]`).val(),
      price_sunroof:      $(this).find(`input[name="brands[${idx}][price_sunroof]"]`).val(),
      commission_sunroof: $(this).find(`input[name="brands[${idx}][commission_sunroof]"]`).val(),
    });
  });

  if (!allValid) { Swal.fire({ icon: 'warning', text: 'กรุณาเลือกยี่ห้อฟิล์มให้ครบทุกแถว' }); return; }
  if (!brands.length) { Swal.fire({ icon: 'warning', text: 'กรุณาเพิ่มยี่ห้อฟิล์มอย่างน้อย 1 รายการ' }); return; }

  const hasSunroof = $('#ep_has_sunroof').is(':checked');

  const payload = {
    sqft,
    has_sunroof:  hasSunroof ? 1 : 0,
    sqft_sunroof: hasSunroof ? $('#ep_sqft_sunroof').val() : null,
    brands,
  };

  const $btn = $(this);
  $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>กำลังบันทึก...');

  $.ajax({
    url: '/film-price-list/' + modelId + '/update-model',
    type: 'POST',
    data: JSON.stringify(payload),
    contentType: 'application/json',
    success: function (res) {
      if (res.success) {
        Swal.fire({ icon: 'success', title: 'สำเร็จ', text: res.message, timer: 1500, showConfirmButton: false });
        $('.editFilmPriceModel').modal('hide');
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
