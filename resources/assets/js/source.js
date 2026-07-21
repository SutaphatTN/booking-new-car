'use strict';

$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});

/* ============================================================
 * ตั้งค่าแหล่งที่มา — แหล่งที่มาย่อย (sub-source) + สถานที่ (place)
 * ============================================================ */

let subSourceTable;
let placeTable;

const dtLang = {
  lengthMenu: 'แสดง _MENU_ แถว',
  zeroRecords: 'ไม่พบข้อมูล',
  info: 'แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ',
  infoEmpty: 'ไม่มีข้อมูล',
  search: 'ค้นหา:',
  paginate: { next: 'ถัดไป', previous: 'ก่อนหน้า' }
};

$(document).ready(function () {
  if ($.fn.DataTable.isDataTable('.subSourceTable')) {
    $('.subSourceTable').DataTable().destroy();
  }
  subSourceTable = $('.subSourceTable').DataTable({
    ajax: '/source/sub/list',
    columns: [
      { data: 'No' },
      { data: 'name' },
      { data: 'main_source' },
      { data: 'Action', orderable: false, searchable: false }
    ],
    ordering: false,
    pageLength: 10,
    autoWidth: false,
    language: dtLang
  });

  if ($.fn.DataTable.isDataTable('.placeTable')) {
    $('.placeTable').DataTable().destroy();
  }
  placeTable = $('.placeTable').DataTable({
    ajax: {
      url: '/source/place/list',
      data: function (d) {
        d.state = $('#placeStateFilter').val() || 'active';
        d.month = $('#placeFilterMonth').val() || '';
      }
    },
    columns: [
      { data: 'checkbox', orderable: false, searchable: false, className: 'text-center' },
      { data: 'No' },
      // { data: 'source' },
      { data: 'location' },
      { data: 'las_number' },
      { data: 'date_range' },
      // { data: 'expense_type' },
      { data: 'cost', className: 'text-end' },
      { data: 'target', className: 'text-end' },
      { data: 'status', orderable: false, searchable: false },
      { data: 'Action', orderable: false, searchable: false }
    ],
    ordering: false,
    pageLength: 10,
    autoWidth: false,
    language: dtLang
  });

  // คุม loader overlay เอง (โหลดครั้งแรก + เปลี่ยนฟิลเตอร์ — เผื่อข้อมูลปิดยอดเยอะ)
  placeTable.on('preXhr.dt', () => $('#placeLoadingOverlay').css('display', 'flex'));
  placeTable.on('xhr.dt', () => $('#placeLoadingOverlay').css('display', 'none'));
});

// กรองสถานะ (กำลังใช้งาน / ปิดยอดแล้ว / ทั้งหมด) — เดือนใช้เฉพาะตอนดูปิดยอด/ทั้งหมด
$(document).on('change', '#placeStateFilter', function () {
  $('#placeFilterMonth').toggleClass('d-none', $(this).val() === 'active');
  if (placeTable) placeTable.ajax.reload();
});
$(document).on('change', '#placeFilterMonth', function () {
  if (placeTable) placeTable.ajax.reload();
});

// ช่องเงิน: อนุญาตเฉพาะตัวเลข + จุดทศนิยม แล้วใส่ comma คั่นหลักพัน
$(document).on('input', '.money-input', function () {
  // ตัดทุกอย่างที่ไม่ใช่ตัวเลข/จุด ออกก่อน (พิมพ์ตัวอักษร/อักขระอื่นจะถูกลบทันที)
  let clean = this.value.replace(/[^\d.]/g, '');

  // เหลือจุดทศนิยมได้จุดเดียว จุดที่เกินมาถูกตัดทิ้ง
  const firstDot = clean.indexOf('.');
  if (firstDot !== -1) {
    clean = clean.slice(0, firstDot + 1) + clean.slice(firstDot + 1).replace(/\./g, '');
  }

  if (clean === '' || clean === '.') { this.value = clean; return; }

  // ใส่ comma เฉพาะส่วนจำนวนเต็ม คงส่วนทศนิยมที่กำลังพิมพ์ไว้ (ไม่ปัด/ไม่เติม 0)
  const [intPart, decPart] = clean.split('.');
  let out = Number(intPart).toLocaleString('en-US');
  if (clean.indexOf('.') !== -1) out += '.' + (decPart ?? '');
  this.value = out;
});

// กันวาง(paste) ข้อความที่มีตัวอักษรปน — ล้างให้เหลือเฉพาะตัวเลข/จุด
$(document).on('paste', '.money-input', function () {
  const el = this;
  setTimeout(() => $(el).trigger('input'), 0);
});

/* ---------- helper: ajax submit ฟอร์มใน modal ---------- */
function submitSourceForm($btn, $modal, table) {
  const form = $modal.find('form')[0];
  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }

  $.ajax({
    url: form.action,
    type: 'POST',
    data: new FormData(form),
    processData: false,
    contentType: false,
    beforeSend: function () {
      $modal.modal('hide');
      Swal.fire({
        title: 'กำลังบันทึกข้อมูล...',
        text: 'กรุณารอสักครู่',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
      });
      $btn.prop('disabled', true);
    },
    success: function (res) {
      Swal.fire({ icon: 'success', title: 'สำเร็จ', text: res.message, timer: 2000, showConfirmButton: true });
      table.ajax.reload(null, false);
    },
    error: function (xhr) {
      $modal.modal('hide');
      Swal.fire({
        icon: 'error',
        title: 'เกิดข้อผิดพลาด',
        text: xhr.responseJSON?.message || 'ไม่สามารถบันทึกข้อมูลได้'
      });
    },
    complete: function () {
      $btn.prop('disabled', false);
    }
  });
}

/* ===================== แหล่งที่มาย่อย ===================== */

$(document).on('click', '.btnInputSub', function () {
  $.get('/source/sub/create', function (html) {
    $('.inputSubModal').html(html);
    $('.inputSub').modal('show');
  });
});

$(document).on('click', '.btnStoreSub', function (e) {
  e.preventDefault();
  submitSourceForm($(this), $('.inputSub'), subSourceTable);
});

$(document).on('click', '.btnEditSub', function () {
  const id = $(this).data('id');
  $.get('/source/sub/edit/' + id, function (html) {
    $('.editSubModal').html(html);
    const $modal = $('.editSub');
    $modal.modal('show');
    $modal.find('.btnUpdateSub').off('click').on('click', function (e) {
      e.preventDefault();
      submitSourceForm($(this), $modal, subSourceTable);
    });
  });
});

/* ===================== สถานที่ ===================== */

$(document).on('click', '.btnInputPlace', function () {
  $.get('/source/place/create', function (html) {
    $('.inputPlaceModal').html(html);
    $('.inputPlace').modal('show');
    $('.inputPlace .budget-items-wrap').each(function () { recalcBudgetTotal($(this)); });
  });
});

$(document).on('click', '.btnStorePlace', function (e) {
  e.preventDefault();
  submitSourceForm($(this), $('.inputPlace'), placeTable);
});

/* ---------- แจกแจงประมาณค่าใช้จ่าย (หลายประเภท) ----------
   scope ด้วย .budget-items-wrap ไม่ใช่ id เพราะ modal เพิ่ม/แก้ไข อยู่ใน DOM พร้อมกัน */

// ไล่เลข name ใหม่ทุกแถว (PHP ต้องการ index ชัดเจน budget_items[i][...])
function renumberBudgetRows($wrap) {
  $wrap.find('.budget-item-row').each(function (i) {
    $(this).find('.budget-type').attr('name', 'budget_items[' + i + '][type]');
    $(this).find('.budget-amount').attr('name', 'budget_items[' + i + '][amount]');
  });
}

function recalcBudgetTotal($wrap) {
  let total = 0;
  $wrap.find('.budget-amount').each(function () {
    total += parseFloat(($(this).val() || '').replace(/,/g, '')) || 0;
  });
  $wrap.find('.budget-total').val(total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
}

$(document).on('click', '.btnAddBudgetItem', function () {
  const $wrap = $(this).closest('.budget-items-wrap');
  const $row = $wrap.find('.budget-item-row').first().clone();
  $row.find('.budget-type').val('');
  $row.find('.budget-amount').val('');
  $wrap.find('.budget-items-body').append($row);
  renumberBudgetRows($wrap);
});

// ลบรายการ (เหลืออย่างน้อย 1 แถว — แถวสุดท้ายแค่ล้างค่า)
$(document).on('click', '.btnRemoveBudgetItem', function () {
  const $wrap = $(this).closest('.budget-items-wrap');
  if ($wrap.find('.budget-item-row').length > 1) {
    $(this).closest('.budget-item-row').remove();
  } else {
    const $row = $(this).closest('.budget-item-row');
    $row.find('.budget-type').val('');
    $row.find('.budget-amount').val('');
  }
  renumberBudgetRows($wrap);
  recalcBudgetTotal($wrap);
});

$(document).on('input', '.budget-amount', function () {
  recalcBudgetTotal($(this).closest('.budget-items-wrap'));
});

$(document).on('click', '.btnEditPlace', function () {
  const id = $(this).data('id');
  $.get('/source/place/edit/' + id, function (html) {
    $('.editPlaceModal').html(html);
    const $modal = $('.editPlace');
    $modal.modal('show');
    $modal.find('.budget-items-wrap').each(function () { recalcBudgetTotal($(this)); });
    $modal.find('.btnUpdatePlace').off('click').on('click', function (e) {
      e.preventDefault();
      submitSourceForm($(this), $modal, placeTable);
    });
  });
});

/* ===================== ขออนุมัติเพิ่ม (topup) ===================== */

// เปิด modal ของบเพิ่ม (ปิด modal แก้ไขก่อน แล้วเปิด topup)
$(document).on('click', '.btnOpenTopup', function () {
  const $edit = $('.editPlace');
  $edit.one('hidden.bs.modal', function () {
    $('.topupPlace').modal('show');
  });
  $edit.modal('hide');
});

// ส่งคำขออนุมัติเพิ่ม
$(document).on('click', '.btnSubmitTopup', function () {
  const $btn = $(this);
  const form = document.getElementById('topupForm');
  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }
  $.ajax({
    url: form.action,
    type: 'POST',
    data: new FormData(form),
    processData: false,
    contentType: false,
    beforeSend: function () {
      $('.topupPlace').modal('hide');
      Swal.fire({ title: 'กำลังส่งคำขอ...', text: 'กรุณารอสักครู่', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
      $btn.prop('disabled', true);
    },
    success: function (res) {
      Swal.fire({ icon: 'success', title: 'สำเร็จ', text: res.message, timer: 2500, showConfirmButton: true });
      placeTable.ajax.reload(null, false);
    },
    error: function (xhr) {
      Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: xhr.responseJSON?.message || 'ไม่สามารถส่งคำขอได้' });
    },
    complete: function () {
      $btn.prop('disabled', false);
    }
  });
});

// blur focus กัน aria-hidden warning ตอนปิด modal (ครอบทั้ง add/edit ของ sub และ place)
$(document).on('hide.bs.modal', '.inputSub, .editSub, .inputPlace, .editPlace, .topupPlace', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

/* ===================== ขออนุมัติ (batch) ===================== */

// select all (เฉพาะ checkbox ที่มีในหน้า — สถานะ draft/rejected)
$(document).on('change', '#placeChkAll', function () {
  $('.place-chk').prop('checked', this.checked);
});

function selectedPlaceIds() {
  return $('.place-chk:checked').map(function () {
    return $(this).val();
  }).get();
}

$(document).on('click', '.btnRequestApproval', function () {
  const ids = selectedPlaceIds();
  if (!ids.length) {
    Swal.fire({ icon: 'info', title: 'ยังไม่ได้เลือก', text: 'กรุณาเลือกสถานที่ที่ต้องการขออนุมัติ (เฉพาะฉบับร่าง/ไม่อนุมัติ)' });
    return;
  }
  $('#approverCount').text(ids.length);
  $('#approver_id').val('');
  $('#approverModal').modal('show');
});

$(document).on('click', '.btnSubmitApproval', function () {
  const $btn = $(this);
  const ids = selectedPlaceIds();
  const approverId = $('#approver_id').val();
  const period = $('#approver_period').val();

  if (!period) {
    Swal.fire({ icon: 'warning', title: 'กรุณาเลือกประจำเดือน' });
    return;
  }
  if (!approverId) {
    Swal.fire({ icon: 'warning', title: 'กรุณาเลือกผู้อนุมัติ' });
    return;
  }

  $.ajax({
    url: '/source/request',
    type: 'POST',
    data: { place_ids: ids, approver_id: approverId, period: period },
    beforeSend: function () {
      $('#approverModal').modal('hide');
      Swal.fire({ title: 'กำลังส่งคำขอ...', text: 'กรุณารอสักครู่', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
      $btn.prop('disabled', true);
    },
    success: function (res) {
      Swal.fire({ icon: 'success', title: 'สำเร็จ', text: res.message, timer: 2500, showConfirmButton: true });
      $('#placeChkAll').prop('checked', false);
      placeTable.ajax.reload(null, false);
    },
    error: function (xhr) {
      Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: xhr.responseJSON?.message || 'ไม่สามารถส่งคำขอได้' });
    },
    complete: function () {
      $btn.prop('disabled', false);
    }
  });
});

// รายงานสรุปตามเดือน (PDF)
$(document).on('click', '.btnPlaceReport', function () {
  const m = $('#reportMonth').val();
  if (!m) {
    Swal.fire({ icon: 'warning', title: 'กรุณาเลือกเดือน' });
    return;
  }
  window.open('/source/place/report?period=' + m, '_blank');
});

/* ===================== เคลียร์ค่าใช้จ่าย ===================== */

// งบคงเหลือสำหรับ "ใบนี้" = งบรวม − ยอดใบอื่น (เคลียร์แล้วทั้งหมด − ใบที่กำลังแก้ไข)
// คืน null = ไม่ได้ตั้งงบ (ไม่จำกัด)
function getClearAllowed() {
  const $f = $('#clearForm');
  const rawB = $f.attr('data-budget');
  if (rawB === undefined || rawB === '') return null;
  const budget = parseFloat(String(rawB).replace(/,/g, ''));
  if (isNaN(budget)) return null;
  const cleared = parseFloat(String($f.attr('data-cleared') || '0').replace(/,/g, '')) || 0;
  const editingTotal = parseFloat(String($f.attr('data-editing-total') || '0').replace(/,/g, '')) || 0;
  return budget - (cleared - editingTotal);
}

function recalcClearTotal() {
  let total = 0;
  $('#clearItemsBody .clear-amount').each(function () {
    const v = parseFloat(($(this).val() || '').replace(/,/g, '')) || 0;
    total += v;
  });
  $('#clearTotal').val(total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

  // เตือนเมื่อยอดใบนี้เกินงบคงเหลือ
  const allowed = getClearAllowed();
  $('#clearTotal').toggleClass('is-invalid', allowed !== null && total > allowed + 0.01);
  return total;
}

const fmtMoney = (n) => Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

// สร้างแถวรายการจากแถวต้นแบบที่ซ่อนไว้ใน tbody
// ph = ข้อความ placeholder จาก server (ยอดที่ตั้งงบไว้ / ยอดคงเหลือของประเภทนั้น)
function makeClearRow(type, amount, ph) {
  const $row = $('#clearItemsBody .clear-item-tmpl').first().clone()
    .removeClass('clear-item-tmpl d-none').addClass('clear-item-row');
  $row.find('.clear-type, .clear-amount').prop('disabled', false);
  $row.find('.clear-type').val(type || '');
  $row.find('.clear-amount').val(amount === undefined || amount === null || amount === '' ? '' : amount);
  if (ph) $row.find('.clear-amount').attr('placeholder', ph);
  return $row;
}

// PHP ต้องการ index ต่อเนื่อง items[i][...] — ไล่เลขใหม่ทุกครั้งที่เพิ่ม/ลบแถว
function renumberClearRows() {
  $('#clearItemsBody .clear-item-row').each(function (i) {
    $(this).find('.clear-type').attr('name', 'items[' + i + '][type]');
    $(this).find('.clear-amount').attr('name', 'items[' + i + '][amount]');
  });
}

// แทนที่แถวทั้งหมดด้วยรายการที่ให้มา (ว่าง = แถวเปล่า 1 แถว)
function setClearRows(list) {
  const $body = $('#clearItemsBody');
  $body.find('.clear-item-row').remove();
  const rows = list && list.length ? list : [{}];
  rows.forEach((it) => $body.append(makeClearRow(it.type, it.amount, it.ph)));
  renumberClearRows();
  recalcClearTotal();
}

// เลื่อนไปที่งวดที่เพิ่งบันทึก + ไฮไลต์สั้น ๆ ให้เห็นว่าบันทึกเข้าไปแล้วจริง
// (ฟอร์มอยู่ล่างสุด งวดใหม่ไปโผล่ด้านบน ถ้าไม่เลื่อนให้จะไม่รู้ว่าบันทึกสำเร็จ)
function highlightClearCard(clearId) {
  const $cards = $('#clearModalInner .clr-card');
  if (!$cards.length) return;
  const $found = clearId ? $cards.filter('[data-clear-id="' + clearId + '"]') : $();
  const $card = $found.length ? $found : $cards.last();

  $card[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
  $card.removeClass('is-new');
  void $card[0].offsetWidth; // reflow — บังคับให้ animation เริ่มใหม่เมื่อบันทึกซ้ำการ์ดเดิม
  $card.addClass('is-new');
  setTimeout(() => $card.removeClass('is-new'), 2600);
}

// ประเภทที่ตั้งงบไว้ (จาก ประมาณค่าใช้จ่าย) — ใช้ตั้งต้นฟอร์มงวดใหม่ ยอดเว้นว่างให้กรอกเอง
function clearPrefill() {
  try { return JSON.parse($('#clearForm').attr('data-prefill') || '[]'); } catch (e) { return []; }
}

// รีเซ็ตฟอร์มกลับสู่โหมด "เพิ่มงวดใหม่"
function resetClearForm() {
  $('#clearEditId').val('');
  $('#clearDate').val('');
  $('#clearForm').removeAttr('data-editing-total');
  setClearRows(clearPrefill());
  $('#clearFormTitle').text('เพิ่มใบเคลียร์ (งวดใหม่)');
  $('.btnSaveClearLabel').text('บันทึกใบเคลียร์');
  $('.btnCancelEditClear').addClass('d-none');
}

// โหลดเนื้อหา modal ใหม่ (รายการงวด + สรุปงบ) โดยไม่ปิด modal
function reloadClearInner(id) {
  return $.get('/source/place/' + id + '/clear', function (html) {
    $('#clearModalInner').html($(html).find('#clearModalInner').html());
    resetClearForm();
  });
}

// เปิด modal เคลียร์
$(document).on('click', '.btnClearPlace', function () {
  const id = $(this).data('id');
  $.get('/source/place/' + id + '/clear', function (html) {
    $('.clearPlaceModal').html(html);
    $('.clearPlace').modal('show');
    resetClearForm();
  });
});

// เพิ่มรายการ (แถวเปล่า — เลือกประเภทอื่นนอกเหนือจากที่ตั้งงบไว้ได้)
$(document).on('click', '.btnAddClearItem', function () {
  $('#clearItemsBody').append(makeClearRow());
  renumberClearRows();
});

// ลบรายการ (เหลืออย่างน้อย 1 แถว)
$(document).on('click', '.btnRemoveClearItem', function () {
  const $body = $('#clearItemsBody');
  if ($body.find('.clear-item-row').length > 1) {
    $(this).closest('.clear-item-row').remove();
  } else {
    const $row = $(this).closest('.clear-item-row');
    $row.find('.clear-type').val('');
    $row.find('.clear-amount').val('');
  }
  renumberClearRows();
  recalcClearTotal();
});

$(document).on('input', '.clear-amount', recalcClearTotal);

// แก้ไขใบเคลียร์ (งวด) — เติมข้อมูลลงฟอร์ม
$(document).on('click', '.btnEditClear', function () {
  const clearId = $(this).data('clear-id');
  const clearDate = $(this).data('clear-date') || '';
  let items;
  try { items = JSON.parse($(this).attr('data-items') || '[]'); } catch (e) { items = []; }

  let editingTotal = 0;
  setClearRows(items.map(function (it) {
    const amt = it.amount === '' || it.amount === null || it.amount === undefined ? '' : Number(it.amount);
    editingTotal += parseFloat(amt) || 0;
    return { type: it.type || '', amount: amt === '' ? '' : fmtMoney(amt) };
  }));

  $('#clearEditId').val(clearId);
  $('#clearDate').val(clearDate);
  $('#clearForm').attr('data-editing-total', editingTotal);
  $('#clearFormTitle').text('แก้ไขใบเคลียร์');
  $('.btnSaveClearLabel').text('อัปเดตใบเคลียร์');
  $('.btnCancelEditClear').removeClass('d-none');
  recalcClearTotal();
  document.getElementById('clearForm').scrollIntoView({ behavior: 'smooth', block: 'center' });
});

// ยกเลิกแก้ไข → กลับสู่โหมดเพิ่มงวดใหม่
$(document).on('click', '.btnCancelEditClear', resetClearForm);

// ปิดยอด/จบงาน (บัญชี) — ปิด modal แล้วซ่อนออกจากรายการ
$(document).on('click', '.btnSettlePlace', function () {
  const id = $(this).data('id');
  Swal.fire({
    title: 'ปิดยอด/จบงานสถานที่นี้?',
    text: 'รายการจะถูกซ่อนออกจากรายการที่ต้องทำ (เปิดใหม่ได้ภายหลัง)',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'ปิดยอด',
    cancelButtonText: 'ยกเลิก',
    confirmButtonColor: '#212529',
    cancelButtonColor: '#d33',
  }).then(function (r) {
    if (!r.isConfirmed) return;
    $.ajax({
      url: '/source/place/' + id + '/settle',
      type: 'POST',
      beforeSend: () => { Swal.fire({ title: 'กำลังบันทึก...', allowOutsideClick: false, didOpen: () => Swal.showLoading() }); },
      success: function (res) {
        Swal.fire({ icon: 'success', title: 'สำเร็จ', text: res.message, timer: 1500, showConfirmButton: true });
        $('.clearPlace').modal('hide');
        placeTable.ajax.reload(null, false);
      },
      error: function (xhr) {
        Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: xhr.responseJSON?.message || 'ไม่สามารถปิดยอดได้' });
      }
    });
  });
});

// เปิดใหม่ (ยกเลิกปิดยอด)
$(document).on('click', '.btnReopenPlace', function () {
  const id = $(this).data('id');
  $.ajax({
    url: '/source/place/' + id + '/reopen',
    type: 'POST',
    beforeSend: () => { Swal.fire({ title: 'กำลังบันทึก...', allowOutsideClick: false, didOpen: () => Swal.showLoading() }); },
    success: function (res) {
      Swal.fire({ icon: 'success', title: 'สำเร็จ', text: res.message, timer: 1500, showConfirmButton: true });
      reloadClearInner(id);
      placeTable.ajax.reload(null, false);
    },
    error: function (xhr) {
      Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: xhr.responseJSON?.message || 'ไม่สามารถเปิดใหม่ได้' });
    }
  });
});

// ลบใบเคลียร์ (งวด)
$(document).on('click', '.btnDeleteClear', function () {
  const id = $(this).data('id');
  const clearId = $(this).data('clear-id');
  Swal.fire({
    title: 'ลบใบเคลียร์นี้?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'ลบ',
    cancelButtonText: 'ยกเลิก',
    confirmButtonColor: '#d33',
  }).then(function (r) {
    if (!r.isConfirmed) return;
    $.ajax({
      url: '/source/place/' + id + '/clear/' + clearId,
      type: 'POST',
      data: { _method: 'DELETE' },
      beforeSend: function () {
        Swal.fire({ title: 'กำลังลบ...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
      },
      success: function (res) {
        Swal.fire({ icon: 'success', title: 'สำเร็จ', text: res.message, timer: 1500, showConfirmButton: true });
        resetClearForm();
        reloadClearInner(id);
        placeTable.ajax.reload(null, false);
      },
      error: function (xhr) {
        Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: xhr.responseJSON?.message || 'ไม่สามารถลบได้' });
      }
    });
  });
});

// บันทึกการเคลียร์
$(document).on('click', '.btnSaveClear', function () {
  const $btn = $(this);
  const form = document.getElementById('clearForm');
  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }

  // ยอดใบนี้ต้องไม่เกินงบคงเหลือ (งบรวม − ใบอื่น)
  const allowed = getClearAllowed();
  const total = recalcClearTotal();
  if (allowed !== null && total > allowed + 0.01) {
    const fmt = (n) => n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    Swal.fire({
      icon: 'warning',
      title: 'ยอดเกินงบคงเหลือ',
      html: 'ยอดใบนี้ <b>' + fmt(total) + '</b> บาท เกินงบคงเหลือ <b>' + fmt(allowed) + '</b> บาท',
    });
    return;
  }
  const id = $btn.data('id');
  $.ajax({
    url: form.action,
    type: 'POST',
    data: new FormData(form),
    processData: false,
    contentType: false,
    beforeSend: function () {
      Swal.fire({ title: 'กำลังบันทึก...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
      $btn.prop('disabled', true);
    },
    success: function (res) {
      Swal.fire({ icon: 'success', title: 'สำเร็จ', text: res.message, timer: 1500, showConfirmButton: true });
      // อ่าน id ที่กำลังแก้ก่อน reset (reset จะล้างค่าทิ้ง) — ว่าง = เพิ่งเพิ่มงวดใหม่ ให้ไฮไลต์งวดล่าสุด
      const editedId = $('#clearEditId').val();
      resetClearForm();
      reloadClearInner(id).done(function () {
        highlightClearCard(editedId);
      });
      placeTable.ajax.reload(null, false);
    },
    error: function (xhr) {
      Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: xhr.responseJSON?.message || 'ไม่สามารถบันทึกได้' });
    },
    complete: function () {
      $btn.prop('disabled', false);
    }
  });
});

// อนุมัติการจ่ายรายงวด (บัญชี) — ต้องระบุวันที่จ่ายก่อน
$(document).on('click', '.btnApproveClearPay', function () {
  const $btn = $(this);
  const id = $btn.data('id');
  const clearId = $btn.data('clear-id');
  const $date = $btn.closest('.input-group').find('.clear-pay-date');
  const payDate = $date.val();

  if (!payDate) {
    Swal.fire({ icon: 'warning', title: 'กรุณาระบุวันที่จ่ายก่อน' });
    $date.focus();
    return;
  }

  Swal.fire({
    title: 'ยืนยันอนุมัติการจ่ายงวดนี้?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'อนุมัติ',
    cancelButtonText: 'ยกเลิก',
    confirmButtonColor: '#6c5ffc',
    cancelButtonColor: '#d33',
  }).then(function (r) {
    if (!r.isConfirmed) return;
    $.ajax({
      url: '/source/place/' + id + '/clear/approve-pay',
      type: 'POST',
      data: { pay_date: payDate, clear_id: clearId },
      beforeSend: function () {
        Swal.fire({ title: 'กำลังบันทึก...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        $btn.prop('disabled', true);
      },
      success: function (res) {
        Swal.fire({ icon: 'success', title: 'สำเร็จ', text: res.message, timer: 1500, showConfirmButton: true });
        reloadClearInner(id);
        placeTable.ajax.reload(null, false);
      },
      error: function (xhr) {
        Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: xhr.responseJSON?.message || 'ไม่สามารถอนุมัติได้' });
      },
      complete: function () {
        $btn.prop('disabled', false);
      }
    });
  });
});

$(document).on('click', '.btnDeletePlace', function () {
  const id = $(this).data('id');
  Swal.fire({
    title: 'คุณแน่ใจหรือไม่?',
    text: 'คุณต้องการลบสถานที่นี้ใช่หรือไม่?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#6c5ffc',
    cancelButtonColor: '#d33',
    confirmButtonText: 'ใช่, ลบเลย!',
    cancelButtonText: 'ยกเลิก'
  }).then(result => {
    if (!result.isConfirmed) return;
    $.ajax({
      url: '/source/place/destroy/' + id,
      type: 'DELETE',
      success: function (res) {
        Swal.fire({ icon: 'success', title: 'สำเร็จ', text: res.message, timer: 2000, showConfirmButton: true });
        placeTable.ajax.reload(null, false);
      },
      error: function (xhr) {
        Swal.fire({
          icon: 'error',
          title: 'เกิดข้อผิดพลาด',
          text: xhr.responseJSON?.message || 'ไม่สามารถลบข้อมูลได้'
        });
      }
    });
  });
});

$(document).on('click', '.btnDeleteSub', function () {
  const id = $(this).data('id');
  Swal.fire({
    title: 'คุณแน่ใจหรือไม่?',
    text: 'คุณต้องการลบแหล่งที่มาย่อยนี้ใช่หรือไม่?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#6c5ffc',
    cancelButtonColor: '#d33',
    confirmButtonText: 'ใช่, ลบเลย!',
    cancelButtonText: 'ยกเลิก'
  }).then(result => {
    if (!result.isConfirmed) return;
    $.ajax({
      url: '/source/sub/destroy/' + id,
      type: 'DELETE',
      success: function (res) {
        Swal.fire({ icon: 'success', title: 'สำเร็จ', text: res.message, timer: 2000, showConfirmButton: true });
        subSourceTable.ajax.reload(null, false);
      },
      error: function (xhr) {
        Swal.fire({
          icon: 'error',
          title: 'เกิดข้อผิดพลาด',
          text: xhr.responseJSON?.message || 'ไม่สามารถลบข้อมูลได้'
        });
      }
    });
  });
});
