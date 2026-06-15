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

// ── Brand rows (input modal) ───────────────────────────────
function fpBrandRowHtml(idx) {
  const brands = window.fpFilmBrands || [];
  const opts = brands.map(fb => `<option value="${fb.id}">${fb.name}</option>`).join('');
  const sunHide = $('#fp_has_sunroof').is(':checked') ? '' : 'd-none';
  const win3Hide = $('#fp_has_3window').is(':checked') ? '' : 'd-none';

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
      <td class="col-3window ${win3Hide}">
        <div class="input-group input-group-sm">
          <span class="input-group-text ig-indigo">฿</span>
          <input type="text" name="brands[${idx}][price_3window]"
            class="form-control text-end money-input" placeholder="0.00" autocomplete="off">
        </div>
      </td>
      <td class="col-3window ${win3Hide}">
        <div class="input-group input-group-sm">
          <span class="input-group-text ig-indigo">฿</span>
          <input type="text" name="brands[${idx}][commission_3window]"
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

// ── Toggles (input modal) ──────────────────────────────────
$(document).on('change', '#fp_has_door_rear2', function () {
  const on = $(this).is(':checked');
  $('#fp_door_rear2_fields').toggleClass('d-none', !on);
  if (!on) $('#fp_door_rear2_fields input').val('');
});

$(document).on('change', '#fp_has_sunroof', function () {
  const on = $(this).is(':checked');
  $('#fp_sunroof_fields').toggleClass('d-none', !on);
  $('#fpBrandTable .col-sunroof').toggleClass('d-none', !on);
  if (!on) {
    $('#fp_sqft_sunroof').val('');
    $('#fpBrandRows input[name*="[price_sunroof]"], #fpBrandRows input[name*="[commission_sunroof]"]').val('');
  }
});

$(document).on('change', '#fp_has_3window', function () {
  const on = $(this).is(':checked');
  $('#fp_3window_fields').toggleClass('d-none', !on);
  $('#fpBrandTable .col-3window').toggleClass('d-none', !on);
  if (!on) {
    $('#fp_3window_fields input').val('');
    $('#fpBrandRows input[name*="[price_3window]"], #fpBrandRows input[name*="[commission_3window]"]').val('');
  }
});

// ── Brand rows (edit modal) ────────────────────────────────
function epBrandRowHtml(idx) {
  const brands = window.fpFilmBrands || [];
  const opts = brands.map(fb => `<option value="${fb.id}">${fb.name}</option>`).join('');
  const sunHide = $('#ep_has_sunroof').is(':checked') ? '' : 'd-none';
  const win3Hide = $('#ep_has_3window').is(':checked') ? '' : 'd-none';

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
      <td class="col-3window ${win3Hide}">
        <div class="input-group input-group-sm">
          <span class="input-group-text ig-indigo">฿</span>
          <input type="text" name="brands[${idx}][price_3window]"
            class="form-control text-end money-input" placeholder="0.00" autocomplete="off">
        </div>
      </td>
      <td class="col-3window ${win3Hide}">
        <div class="input-group input-group-sm">
          <span class="input-group-text ig-indigo">฿</span>
          <input type="text" name="brands[${idx}][commission_3window]"
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
  const idx = window.epNextIdx !== undefined ? window.epNextIdx++ : Date.now();
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

// ── Toggles (edit modal) ───────────────────────────────────
$(document).on('change', '#ep_has_door_rear2', function () {
  const on = $(this).is(':checked');
  $('#ep_door_rear2_fields').toggleClass('d-none', !on);
  if (!on) $('#ep_door_rear2_fields input').val('');
});

$(document).on('change', '#ep_has_sunroof', function () {
  const on = $(this).is(':checked');
  $('#ep_sunroof_fields').toggleClass('d-none', !on);
  $('#epBrandTable .col-sunroof').toggleClass('d-none', !on);
  if (!on) {
    $('#ep_sqft_sunroof').val('');
    $('#epBrandRows input[name*="[price_sunroof]"], #epBrandRows input[name*="[commission_sunroof]"]').val('');
  }
});

$(document).on('change', '#ep_has_3window', function () {
  const on = $(this).is(':checked');
  $('#ep_3window_fields').toggleClass('d-none', !on);
  $('#epBrandTable .col-3window').toggleClass('d-none', !on);
  if (!on) {
    $('#ep_3window_fields input').val('');
    $('#epBrandRows input[name*="[price_3window]"], #epBrandRows input[name*="[commission_3window]"]').val('');
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
      $(priceField).val(
        parseFloat(res.price).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
      );
      $(commissionField).val(
        parseFloat(res.commission).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
      );
      Swal.fire({
        icon: 'success',
        title: 'คำนวณสำเร็จ',
        html:
          `ต้นทุน/ตร.ฟุต: <b>${res.detail.cost_per_sqft}</b><br>` +
          `ต้นทุนฟิล์ม: <b>${res.detail.film_cost.toLocaleString()}</b><br>` +
          `GP: <b>${res.detail.gp_pct}%</b> | Commission: <b>${res.detail.commission_pct}%</b>`,
        timer: 3000,
        showConfirmButton: true
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
  if (value === '' || isNaN(value)) {
    this.value = '';
    return;
  }
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
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});
$(document).on('hide.bs.modal', '.editFilmPrice', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

// ── Open Settings Modal ────────────────────────────────────
$(document).on('click', '.btnFilmSettings', function () {
  $.get('/film-settings/modal', function (html) {
    $('.filmSettingsModal').html(html);
    $('.filmSettings').modal('show');
  });
});

$(document).on('hide.bs.modal', '.filmSettings', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
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
  const sqft = $('#fp_sqft').val();

  if (!modelId) {
    Swal.fire({ icon: 'warning', text: 'กรุณาเลือกรุ่นรถ' });
    return;
  }
  if (!sqft || parseFloat(sqft) <= 0) {
    Swal.fire({ icon: 'warning', text: 'กรุณาระบุจำนวน ตร.ฟุต' });
    return;
  }

  const brands = [];
  let allValid = true;

  const hasDoorRear2 = $('#fp_has_door_rear2').is(':checked');
  const hasSunroof = $('#fp_has_sunroof').is(':checked');
  const has3window = $('#fp_has_3window').is(':checked');

  $('#fpBrandRows tr[data-idx]').each(function () {
    const idx = $(this).data('idx');
    const brandId = $(this).find('.fpBrandSel').val();
    if (!brandId) {
      allValid = false;
      return false;
    }
    brands.push({
      film_brand_id: brandId,
      price: $(this).find(`input[name="brands[${idx}][price]"]`).val(),
      commission: $(this).find(`input[name="brands[${idx}][commission]"]`).val(),
      price_sunroof: $(this).find(`input[name="brands[${idx}][price_sunroof]"]`).val(),
      commission_sunroof: $(this).find(`input[name="brands[${idx}][commission_sunroof]"]`).val(),
      price_3window: $(this).find(`input[name="brands[${idx}][price_3window]"]`).val(),
      commission_3window: $(this).find(`input[name="brands[${idx}][commission_3window]"]`).val()
    });
  });

  if (!allValid) {
    Swal.fire({ icon: 'warning', text: 'กรุณาเลือกยี่ห้อฟิล์มให้ครบทุกแถว' });
    return;
  }
  if (!brands.length) {
    Swal.fire({ icon: 'warning', text: 'กรุณาเพิ่มยี่ห้อฟิล์มอย่างน้อย 1 รายการ' });
    return;
  }

  const payload = {
    model_id: modelId,
    sqft: sqft,
    sqft_windshield: $('input[name="sqft_windshield"]').val(),
    sqft_rear: $('input[name="sqft_rear"]').val(),
    sqft_door_front: $('input[name="sqft_door_front"]').val(),
    sqft_door_rear1: $('input[name="sqft_door_rear1"]').val(),
    sqft_quarter: $('input[name="sqft_quarter"]').val(),
    sqft_around: $('input[name="sqft_around"]').val(),
    has_door_rear2: hasDoorRear2 ? 1 : 0,
    sqft_door_rear2: hasDoorRear2 ? $('input[name="sqft_door_rear2"]').val() : null,
    has_sunroof: hasSunroof ? 1 : 0,
    sqft_sunroof: hasSunroof ? $('#fp_sqft_sunroof').val() : null,
    has_3window: has3window ? 1 : 0,
    sqft_3window: has3window ? $('input[name="sqft_3window"]').val() : null,
    brands
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
        Swal.fire({ icon: 'success', title: 'สำเร็จ', text: res.message, timer: 1500, showConfirmButton: true });
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
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

// ── Update (by model) ──────────────────────────────────────
$(document).on('click', '.btnUpdateFilmPriceModel', function () {
  const modelId = $('#ep_model_id').val();
  const sqft = $('#ep_sqft').val();

  if (!sqft || parseFloat(sqft) <= 0) {
    Swal.fire({ icon: 'warning', text: 'กรุณาระบุจำนวน ตร.ฟุต' });
    return;
  }

  const brands = [];
  let allValid = true;

  const hasDoorRear2 = $('#ep_has_door_rear2').is(':checked');
  const hasSunroof = $('#ep_has_sunroof').is(':checked');
  const has3window = $('#ep_has_3window').is(':checked');

  $('#epBrandRows tr[data-idx]').each(function () {
    const idx = $(this).data('idx');
    const brandId = $(this).find('.epBrandSel').val();
    if (!brandId) {
      allValid = false;
      return false;
    }
    brands.push({
      film_brand_id: brandId,
      price: $(this).find(`input[name="brands[${idx}][price]"]`).val(),
      commission: $(this).find(`input[name="brands[${idx}][commission]"]`).val(),
      price_sunroof: $(this).find(`input[name="brands[${idx}][price_sunroof]"]`).val(),
      commission_sunroof: $(this).find(`input[name="brands[${idx}][commission_sunroof]"]`).val(),
      price_3window: $(this).find(`input[name="brands[${idx}][price_3window]"]`).val(),
      commission_3window: $(this).find(`input[name="brands[${idx}][commission_3window]"]`).val()
    });
  });

  if (!allValid) {
    Swal.fire({ icon: 'warning', text: 'กรุณาเลือกยี่ห้อฟิล์มให้ครบทุกแถว' });
    return;
  }
  if (!brands.length) {
    Swal.fire({ icon: 'warning', text: 'กรุณาเพิ่มยี่ห้อฟิล์มอย่างน้อย 1 รายการ' });
    return;
  }

  const payload = {
    sqft,
    sqft_windshield: $('input[name="sqft_windshield"]').val(),
    sqft_rear: $('input[name="sqft_rear"]').val(),
    sqft_door_front: $('input[name="sqft_door_front"]').val(),
    sqft_door_rear1: $('input[name="sqft_door_rear1"]').val(),
    sqft_quarter: $('input[name="sqft_quarter"]').val(),
    sqft_around: $('input[name="sqft_around"]').val(),
    has_door_rear2: hasDoorRear2 ? 1 : 0,
    sqft_door_rear2: hasDoorRear2 ? $('input[name="sqft_door_rear2"]').val() : null,
    has_sunroof: hasSunroof ? 1 : 0,
    sqft_sunroof: hasSunroof ? $('#ep_sqft_sunroof').val() : null,
    has_3window: has3window ? 1 : 0,
    sqft_3window: has3window ? $('input[name="sqft_3window"]').val() : null,
    brands
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
        Swal.fire({ icon: 'success', title: 'สำเร็จ', text: res.message, timer: 1500, showConfirmButton: true });
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
    title: 'คุณแน่ใจหรือไม่?',
    text: 'คุณต้องการลบข้อมูลนี้ใช่หรือไม่?',
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
            Swal.fire({ icon: 'success', title: 'ลบสำเร็จ', text: res.message, timer: 1500, showConfirmButton: true });
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
