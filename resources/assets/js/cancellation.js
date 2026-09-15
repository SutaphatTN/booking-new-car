// การ์ดไฟล์แนบ/พรีวิว + ปุ่มลบบนการ์ด — ตัวกลางตัวเดียวของทั้งระบบ
import { fileCardHtml, renderFileCards, renderFilePreviews } from './file-cards';

$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});

//view : table
let cancellationTable;

$(document).ready(function () {
  cancellationTable = $('#cancellationTable').DataTable({
    ajax: '/purchase-order/list-cancellation',
    columns: [
      { data: 'No' },
      { data: 'FullName', orderable: false },
      { data: 'model', orderable: false },
      { data: 'CancelGCIPDate', orderable: false },
      { data: 'Action', orderable: false, searchable: false }
    ],
    paging: true,
    lengthChange: true,
    searching: true,
    ordering: true,
    info: true,
    pageLength: 10,
    autoWidth: false,
    language: {
      lengthMenu: 'แสดง _MENU_ แถว',
      zeroRecords: 'ไม่พบข้อมูล',
      info: 'แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ',
      infoEmpty: 'ไม่มีข้อมูล',
      search: 'ค้นหา:',
      paginate: {
        next: 'ถัดไป',
        previous: 'ก่อนหน้า'
      }
    }
  });
});

// ยืนยันการคืนเงิน
$(document).on('click', '.btnConfirmWithdraw', function () {
  const id = $(this).data('id');

  Swal.fire({
    icon: 'question',
    title: 'ยืนยันการคืนเงิน?',
    text: 'เมื่อยืนยันแล้ว รายการนี้จะถูกนำออกจากหน้าถอนจอง',
    showCancelButton: true,
    confirmButtonText: 'ยืนยัน',
    cancelButtonText: 'ยกเลิก',
    confirmButtonColor: '#6c5ffc',
    cancelButtonColor: '#d33'
  }).then(function (result) {
    if (!result.isConfirmed) return;

    $.ajax({
      url: '/purchase-order/cancellation/' + id + '/confirm-withdraw',
      type: 'POST',
      success: function (res) {
        if (res.success) {
          Swal.fire({ icon: 'success', title: 'สำเร็จ', text: res.message, timer: 2000, showConfirmButton: true });
          cancellationTable.ajax.reload(null, false);
        } else {
          Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: res.message });
        }
      },
      error: function () {
        Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: 'ไม่สามารถบันทึกข้อมูลได้' });
      }
    });
  });
});

$(document).on('hide.bs.modal', '#cancellationViewModal', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

//view modal
let currentViewId = null;

$(document).on('click', '.btnViewCancellation', function () {
  currentViewId = $(this).data('id');

  $.get('/purchase-order/cancellation-data/' + currentViewId, function (res) {
    $('#viewFullName').text(res.FullName);
    $('#viewModel').text(res.model);
    $('#viewCancelDate').text(res.CancelGCIPDate ? new Date(res.CancelGCIPDate).toLocaleDateString('th-TH') : '-');
    $('#viewRefundDate').text(res.RefundDate ? new Date(res.RefundDate).toLocaleDateString('th-TH') : '-');
    $('#viewRefundMotorDate').text(
      res.RefundMotorDate ? new Date(res.RefundMotorDate).toLocaleDateString('th-TH') : '-'
    );

    const attachments = res.withdraw_attachments || [];
    if (attachments.length > 0) {
      const $list = $('#viewWithdrawAttachList').empty();
      attachments.forEach(function (url) {
        $list.append(attachPreviewHtml(url, currentViewId));
      });
      $('#viewWithdrawAttachSection').show();
    } else {
      $('#viewWithdrawAttachSection').hide();
    }

    $('#cancellationViewModal').modal('show');
  });
});

// $('#btnSaveRefundDate').on('click', function () {
//   if (!currentViewId) return;

//   $.ajax({
//     url: '/purchase-order/cancellation/' + currentViewId + '/refund',
//     type: 'PUT',
//     data: { refund_date: $('#viewRefundDate').val() },
//     success: function (res) {
//       if (res.success) {
//         $('#cancellationViewModal').modal('hide');
//         Swal.fire({ icon: 'success', title: 'สำเร็จ', text: res.message, timer: 2000, showConfirmButton: true });
//         cancellationTable.ajax.reload(null, false);
//       } else {
//         Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: res.message });
//       }
//     },
//     error: function () {
//       Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: 'ไม่สามารถบันทึกข้อมูลได้' });
//     }
//   });
// });

//edit modal รูปบบไฟล์มีสี
let currentEditId = null;
let currentAttachments = [];
let stagedDeletes = [];

function attachProxy(url, salecarId, name) {
  const base = `/purchase-order/cancellation/${salecarId}/proxy`;
  const path = name ? `${base}/${encodeURIComponent(name)}` : base;
  return `${path}?url=${encodeURIComponent(url)}`;
}

/** การ์ดไฟล์ 1 ใบสำหรับโมดัล "ดูข้อมูล" (อ่านอย่างเดียว ไม่มีปุ่มลบ) */
function attachPreviewHtml(attachment, salecarId) {
  const url = typeof attachment === 'object' ? attachment.url : attachment;
  const name = typeof attachment === 'object' ? attachment.name : null;

  return fileCardHtml({ url: url, href: attachProxy(url, salecarId, name), name: name }, { readonly: true });
}

/**
 * รายการไฟล์ในโมดัลแก้ไข — ลบแบบ "เอาออกจากจอ" (stage) ไฟล์หายจริงตอนกดบันทึก
 * ปุ่มลบเป็นของ file-cards.js ; ที่นี่ดัก 'fc:removed' เพื่อจำ url ที่ถูกเอาออกไว้ใน stagedDeletes
 */
function renderWithdrawAttachments(attachments, salecarId) {
  currentAttachments = attachments || [];

  const visible = currentAttachments
    .filter(att => !stagedDeletes.includes(typeof att === 'object' ? att.url : att))
    .map(att => {
      const url = typeof att === 'object' ? att.url : att;
      const name = typeof att === 'object' ? att.name : null;
      return { url: url, href: attachProxy(url, salecarId, name), name: name };
    });

  renderFileCards($('#withdrawAttachmentList'), visible, { stage: true });
}

$(document).on('fc:removed', '#withdrawAttachmentList', function (e, info) {
  stagedDeletes.push(info.url);
});

$('#withdrawAttachmentInput').on('change', function () {
  renderFilePreviews(this, $('#newFilePreview'));
});

$(document).on('click', '.btnEditCancellation', function () {
  currentEditId = $(this).data('id');
  stagedDeletes = [];
  currentAttachments = [];
  $('#withdrawAttachmentInput').val('');
  $('#newFilePreview').empty();

  $.get('/purchase-order/cancellation-data/' + currentEditId, function (res) {
    $('#editCancelDate').val(res.CancelGCIPDate ?? '');
    $('#editRefundDate').val(res.RefundDate ?? '');
    $('#editRefundMotorDate').val(res.RefundMotorDate ?? '');
    renderWithdrawAttachments(res.withdraw_attachments, currentEditId);
    $('#cancellationEditModal').modal('show');
  });
});

$(document).on('hide.bs.modal', '#cancellationEditModal', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

function deleteStagedFiles() {
  if (stagedDeletes.length === 0) return $.when();

  const def = $.Deferred();
  const deletes = stagedDeletes.map(function (url) {
    return $.ajax({
      url: '/purchase-order/cancellation/' + currentEditId + '/withdraw-attachment',
      type: 'DELETE',
      data: { url: url }
    });
  });

  $.when
    .apply($, deletes)
    .done(function () {
      def.resolve();
    })
    .fail(function () {
      def.reject();
    });

  return def.promise();
}

function saveDates() {
  return $.ajax({
    url: '/purchase-order/cancellation/' + currentEditId,
    type: 'PUT',
    data: {
      cancel_gcip_date: $('#editCancelDate').val(),
      refund_date: $('#editRefundDate').val(),
      refund_motor_date: $('#editRefundMotorDate').val()
    }
  });
}

function uploadWithdrawFiles() {
  const files = $('#withdrawAttachmentInput')[0].files;
  if (files.length === 0) return $.when();

  const formData = new FormData();
  Array.from(files).forEach(function (file) {
    formData.append('attachments[]', file);
  });
  formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

  return $.ajax({
    url: '/purchase-order/cancellation/' + currentEditId + '/withdraw-attachment',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false
  });
}

$('#btnSaveEdit').on('click', function () {
  if (!currentEditId) return;

  const $btn = $(this);
  $btn.prop('disabled', true);

  $('#cancellationEditModal').modal('hide');
  Swal.fire({
    title: 'กำลังบันทึกข้อมูล...',
    text: 'กรุณารอสักครู่',
    allowOutsideClick: false,
    didOpen: () => Swal.showLoading()
  });

  $.when(saveDates(), deleteStagedFiles(), uploadWithdrawFiles())
    .done(function (datesRes, _deleteRes, uploadRes) {
      const datesOk = datesRes && datesRes[0]?.success !== false;
      const uploadOk = !uploadRes || uploadRes[0]?.success !== false;

      if (datesOk && uploadOk) {
        stagedDeletes = [];
        currentAttachments = [];
        $('#withdrawAttachmentInput').val('');
        $('#newFilePreview').empty();
        Swal.fire({
          icon: 'success',
          title: 'สำเร็จ',
          text: 'บันทึกข้อมูลเรียบร้อยแล้ว',
          timer: 2000,
          showConfirmButton: true
        });
        cancellationTable.ajax.reload(null, false);
      } else {
        Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: 'ไม่สามารถบันทึกข้อมูลได้' });
      }
    })
    .fail(function (xhr) {
      Swal.fire({
        icon: 'error',
        title: 'เกิดข้อผิดพลาด',
        text: xhr.responseJSON?.message ?? 'ไม่สามารถบันทึกข้อมูลได้'
      });
    })
    .always(function () {
      $btn.prop('disabled', false);
    });
});
