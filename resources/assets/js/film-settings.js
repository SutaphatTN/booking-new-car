$.ajaxSetup({
  headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

// ── Helpers ────────────────────────────────────────────────
function stripCommas(val) {
  return parseFloat((val + '').replace(/,/g, '')) || 0;
}

function formatDec(val) {
  const n = parseFloat((val + '').replace(/,/g, ''));
  if (isNaN(n)) return '';
  return n.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// ── money-input-dec: comma while typing ───────────────────
$(document).on('input', '.money-input-dec', function () {
  let raw = this.value.replace(/[^0-9.]/g, '');
  // allow only one dot
  const parts = raw.split('.');
  if (parts.length > 2) raw = parts[0] + '.' + parts.slice(1).join('');
  // add commas to integer part
  const intPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  this.value = parts.length === 2 ? intPart + '.' + parts[1] : intPart;
});

$(document).on('blur', '.money-input-dec', function () {
  const n = stripCommas(this.value);
  if (!isNaN(n) && this.value !== '') {
    this.value = formatDec(n);
  }
});

// ── Live calc final cost & per sqft ───────────────────────
function recalcRow(id) {
  const roll     = stripCommas($('#cost_roll_' + id).val());
  const discount = stripCommas($('#cost_discount_display_' + id).val()); // positive
  const rollSize = stripCommas($('#gs_roll_size').val());
  const final    = roll - discount; // roll minus discount

  // update hidden field (negative discount)
  $('#cost_discount_' + id).val(discount > 0 ? -discount : 0);

  $('#final_cost_' + id).text(final.toLocaleString('th-TH', { minimumFractionDigits: 2 }));
  $('#per_sqft_' + id).text(rollSize > 0
    ? (final / rollSize).toLocaleString('th-TH', { minimumFractionDigits: 2 })
    : '-'
  );
}

$(document).on('input blur', '.cost-roll, .cost-discount-display', function () {
  recalcRow($(this).data('id'));
});

$(document).on('input blur', '#gs_roll_size', function () {
  $('.cost-roll').each(function () { recalcRow($(this).data('id')); });
});

// ── Save Global Settings ───────────────────────────────────
$(document).on('click', '.btnSaveGlobal', function () {
  const form = document.getElementById('formGlobal');
  if (!form.checkValidity()) { form.reportValidity(); return; }

  const $btn = $(this);
  $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>กำลังบันทึก...');

  const data = {
    _token:         $('meta[name="csrf-token"]').attr('content'),
    roll_size:      stripCommas($('#gs_roll_size').val()),
    waste_pct:      $('#gs_waste_pct').val(),
    gp_pct:         $('#gs_gp_pct').val(),
    commission_pct: $('#gs_commission_pct').val(),
  };

  $.post('/film-settings/global', data, function (res) {
    if (res.success) {
      Swal.fire({ icon: 'success', title: 'สำเร็จ', text: res.message, timer: 1500, showConfirmButton: false });
    } else {
      Swal.fire({ icon: 'warning', title: 'แจ้งเตือน', text: res.message });
    }
  }).fail(function () {
    Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: 'กรุณาติดต่อแอดมิน' });
  }).always(function () {
    $btn.prop('disabled', false).html('<i class="bx bx-save me-1"></i> บันทึก Global Settings');
  });
});

// ── Save Film Costs ────────────────────────────────────────
$(document).on('click', '.btnSaveCosts', function () {
  // sync all hidden discount fields before serialize
  $('.cost-discount-display').each(function () {
    const id = $(this).data('id');
    const discount = stripCommas($(this).val());
    $('#cost_discount_' + id).val(discount > 0 ? -discount : 0);
  });

  // strip commas from roll_price before serialize
  const data = { _token: $('meta[name="csrf-token"]').attr('content') };
  $('.cost-roll').each(function () {
    const id = $(this).data('id');
    data['costs[' + id + '][roll_price]'] = stripCommas($(this).val());
    data['costs[' + id + '][discount]']   = stripCommas($('#cost_discount_' + id).val());
  });

  const $btn = $(this);
  $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>กำลังบันทึก...');

  $.post('/film-settings/costs', data, function (res) {
    if (res.success) {
      Swal.fire({ icon: 'success', title: 'สำเร็จ', text: res.message, timer: 1500, showConfirmButton: false });
    } else {
      Swal.fire({ icon: 'warning', title: 'แจ้งเตือน', text: res.message });
    }
  }).fail(function () {
    Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: 'กรุณาติดต่อแอดมิน' });
  }).always(function () {
    $btn.prop('disabled', false).html('<i class="bx bx-save me-1"></i> บันทึกต้นทุนฟิล์ม');
  });
});
