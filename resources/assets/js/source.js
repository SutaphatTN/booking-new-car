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
    ajax: '/source/place/list',
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

function recalcClearTotal() {
  let total = 0;
  $('#clearItemsBody .clear-amount').each(function () {
    const v = parseFloat(($(this).val() || '').replace(/,/g, '')) || 0;
    total += v;
  });
  $('#clearTotal').val(total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
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

// บันทึกการเคลียร์
$(document).on('click', '.btnSaveClear', function () {
  const $btn = $(this);
  const form = document.getElementById('clearForm');
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
      Swal.fire({ title: 'กำลังบันทึก...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
      $btn.prop('disabled', true);
    },
    success: function (res) {
      Swal.fire({ icon: 'success', title: 'สำเร็จ', text: res.message, timer: 2000, showConfirmButton: true });
      $('.clearPlace').modal('hide');
      placeTable.ajax.reload(null, false);
    },
    error: function (xhr) {
      Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: xhr.responseJSON?.message || 'ไม่สามารถส่งเคลียร์ได้' });
    },
    complete: function () {
      $btn.prop('disabled', false);
    }
  });
});

// อนุมัติการจ่าย (บัญชี) — ต้องระบุวันที่จ่ายก่อน
$(document).on('click', '.btnApproveClearPay', function () {
  const $btn = $(this);
  const id = $btn.data('id');
  const payDate = $('#clearPayDate').val();

  if (!payDate) {
    Swal.fire({ icon: 'warning', title: 'กรุณาระบุวันที่จ่ายก่อน' });
    $('#clearPayDate').focus();
    return;
  }

  Swal.fire({
    title: 'ยืนยันอนุมัติการจ่าย?',
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
      data: { pay_date: $('#clearPayDate').val() },
      beforeSend: function () {
        Swal.fire({ title: 'กำลังบันทึก...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
        $btn.prop('disabled', true);
      },
      success: function (res) {
        Swal.fire({ icon: 'success', title: 'สำเร็จ', text: res.message, timer: 2000, showConfirmButton: true });
        $('.clearPlace').modal('hide');
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
