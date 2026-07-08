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

// จัดรูปแบบ comma ให้ช่องเงิน
$(document).on('input', '.money-input', function () {
  let val = this.value.replace(/,/g, '');
  if (val === '' || isNaN(val)) return;
  this.value = Number(val).toLocaleString();
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
  });
});

$(document).on('click', '.btnStorePlace', function (e) {
  e.preventDefault();
  submitSourceForm($(this), $('.inputPlace'), placeTable);
});

$(document).on('click', '.btnEditPlace', function () {
  const id = $(this).data('id');
  $.get('/source/place/edit/' + id, function (html) {
    $('.editPlaceModal').html(html);
    const $modal = $('.editPlace');
    $modal.modal('show');
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

// รีเซ็ตฟอร์มกลับสู่โหมด "เพิ่มงวดใหม่"
function resetClearForm() {
  const $body = $('#clearItemsBody');
  $('#clearEditId').val('');
  $('#clearDate').val('');
  $('#clearForm').removeAttr('data-editing-total');
  const $first = $body.find('.clear-item-row').first();
  $body.find('.clear-item-row').not(':first').remove();
  $first.find('.clear-type').attr('name', 'items[0][type]').val('');
  $first.find('.clear-amount').attr('name', 'items[0][amount]').val('');
  $body.attr('data-next-index', '1');
  $('#clearFormTitle').text('เพิ่มใบเคลียร์ (งวดใหม่)');
  $('.btnSaveClearLabel').text('บันทึกใบเคลียร์');
  $('.btnCancelEditClear').addClass('d-none');
  recalcClearTotal();
}

// โหลดเนื้อหา modal ใหม่ (รายการงวด + สรุปงบ) โดยไม่ปิด modal
function reloadClearInner(id) {
  return $.get('/source/place/' + id + '/clear', function (html) {
    $('#clearModalInner').html($(html).find('#clearModalInner').html());
    recalcClearTotal();
  });
}

// เปิด modal เคลียร์
$(document).on('click', '.btnClearPlace', function () {
  const id = $(this).data('id');
  $.get('/source/place/' + id + '/clear', function (html) {
    $('.clearPlaceModal').html(html);
    $('.clearPlace').modal('show');
    recalcClearTotal();
  });
});

// เพิ่มรายการ (clone แถวแรก)
$(document).on('click', '.btnAddClearItem', function () {
  const $body = $('#clearItemsBody');
  let idx = parseInt($body.attr('data-next-index') || $body.find('.clear-item-row').length, 10);
  const $row = $body.find('.clear-item-row').first().clone();
  $row.find('.clear-type').attr('name', 'items[' + idx + '][type]').val('');
  $row.find('.clear-amount').attr('name', 'items[' + idx + '][amount]').val('');
  $body.append($row);
  $body.attr('data-next-index', idx + 1);
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
  recalcClearTotal();
});

$(document).on('input', '.clear-amount', recalcClearTotal);

// แก้ไขใบเคลียร์ (งวด) — เติมข้อมูลลงฟอร์ม
$(document).on('click', '.btnEditClear', function () {
  const clearId = $(this).data('clear-id');
  const clearDate = $(this).data('clear-date') || '';
  let items;
  try { items = JSON.parse($(this).attr('data-items') || '[]'); } catch (e) { items = []; }
  if (!items.length) items = [{ type: '', amount: '' }];

  const fmt = (n) => Number(n).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const $body = $('#clearItemsBody');
  const $tmpl = $body.find('.clear-item-row').first().clone();
  $body.empty();

  let editingTotal = 0;
  items.forEach(function (it, i) {
    const $row = $tmpl.clone();
    $row.find('.clear-type').attr('name', 'items[' + i + '][type]').val(it.type || '');
    const amt = it.amount === '' || it.amount === null || it.amount === undefined ? '' : Number(it.amount);
    $row.find('.clear-amount').attr('name', 'items[' + i + '][amount]').val(amt === '' ? '' : fmt(amt));
    editingTotal += parseFloat(amt) || 0;
    $body.append($row);
  });
  $body.attr('data-next-index', items.length);

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
      resetClearForm();
      reloadClearInner(id);
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
