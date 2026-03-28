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
      { data: 'No', orderable: false },
      { data: 'FullName', orderable: false },
      { data: 'model' },
      { data: 'CancelGCIPDate' },
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
      paginate: {
        first: '',
        last: '',
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
        const ext = url.split('.').pop().split('?')[0].toLowerCase();
        const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext);
        const preview = isImage
          ? `<a href="${url}" target="_blank"><img src="${url}" class="img-fluid rounded border" style="max-height:120px;object-fit:cover;width:100%;"></a>`
          : `<a href="${url}" target="_blank" class="btn btn-outline-secondary btn-sm w-100"><i class="bx bx-file me-1"></i> ดูไฟล์</a>`;
        $list.append(`<div class="col-md-3 col-sm-6 text-center">${preview}</div>`);
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
//         Swal.fire({ icon: 'success', title: 'สำเร็จ', text: res.message, timer: 2000, showConfirmButton: false });
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

//edit modal
let currentEditId = null;

function renderWithdrawAttachments(attachments) {
  const $list = $('#withdrawAttachmentList');
  $list.empty();

  if (!attachments || attachments.length === 0) {
    $list.html('<p class="text-muted small">ยังไม่มีไฟล์แนบ</p>');
    return;
  }

  attachments.forEach(function (url) {
    const ext = url.split('.').pop().split('?')[0].toLowerCase();
    const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext);
    const preview = isImage
      ? `<a href="${url}" target="_blank"><img src="${url}" class="img-fluid rounded border" style="max-height:120px;object-fit:cover;width:100%;"></a>`
      : `<a href="${url}" target="_blank" class="btn btn-outline-secondary btn-sm w-100"><i class="bx bx-file me-1"></i> ดูไฟล์</a>`;

    $list.append(`
      <div class="col-md-3 col-sm-6 text-center position-relative withdraw-attachment-item">
        ${preview}
        <button type="button" class="btn btn-danger btn-sm mt-1 w-100 btnDeleteWithdrawAttachment" data-url="${url}">
          <i class="bx bx-trash me-1"></i> ลบ
        </button>
      </div>
    `);
  });
}

$(document).on('click', '.btnEditCancellation', function () {
  currentEditId = $(this).data('id');
  $('#withdrawAttachmentInput').val('');

  $.get('/purchase-order/cancellation-data/' + currentEditId, function (res) {
    $('#editCancelDate').val(res.CancelGCIPDate ?? '');
    $('#editRefundDate').val(res.RefundDate ?? '');
    $('#editRefundMotorDate').val(res.RefundMotorDate ?? '');
    renderWithdrawAttachments(res.withdraw_attachments);
    $('#cancellationEditModal').modal('show');
  });
});

// ลบไฟล์แนบ
$(document).on('click', '.btnDeleteWithdrawAttachment', function () {
  const url = $(this).data('url');
  if (!currentEditId) return;

  $('#cancellationEditModal').modal('hide');

  Swal.fire({
    icon: 'warning',
    title: 'ยืนยันการลบ',
    text: 'ต้องการลบไฟล์นี้ออกจากระบบ?',
    showCancelButton: true,
    confirmButtonColor: '#6c5ffc',
    cancelButtonColor: '#d33',
    confirmButtonText: 'ใช่, ลบเลย!',
    cancelButtonText: 'ยกเลิก'
  }).then(function (result) {
    if (!result.isConfirmed) return;

    $.ajax({
      url: '/purchase-order/cancellation/' + currentEditId + '/withdraw-attachment',
      type: 'DELETE',
      data: { url: url },
      success: function (res) {
        if (res.success) {
          renderWithdrawAttachments(res.attachments);

          // ✅ เพิ่ม SweetAlert ตรงนี้
          Swal.fire({
            icon: 'success',
            title: 'สำเร็จ',
            text: 'ลบไฟล์เรียบร้อยแล้ว',
            timer: 2000,
            showConfirmButton: true
          });

          $('#cancellationEditModal').modal('show');
        } else {
          Swal.fire({
            icon: 'error',
            title: 'เกิดข้อผิดพลาด',
            text: res.message
          });
        }
      },

      error: function (xhr) {
        Swal.fire({
          icon: 'error',
          title: 'เกิดข้อผิดพลาด',
          text: xhr.responseJSON?.message ?? 'ไม่สามารถลบไฟล์ได้'
        });
      }
    });
  });
});

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

  $.when(saveDates(), uploadWithdrawFiles())
    .done(function (datesRes, uploadRes) {
      const datesOk = datesRes && datesRes[0]?.success !== false;
      const uploadOk = !uploadRes || uploadRes[0]?.success !== false;

      if (datesOk && uploadOk) {
        if (uploadRes && uploadRes[0]?.attachments) {
          $('#withdrawAttachmentInput').val('');
          renderWithdrawAttachments(uploadRes[0].attachments);
        }
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
