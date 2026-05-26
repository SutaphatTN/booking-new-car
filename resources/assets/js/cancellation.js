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

function fileCardStyle(name) {
  const ext = (name.split('.').pop() || '').toLowerCase();
  if (ext === 'pdf') return { bg: '#ef4444', label: 'PDF' };
  if (['xlsx', 'xls', 'csv'].includes(ext)) return { bg: '#16a34a', label: ext.toUpperCase() };
  if (['doc', 'docx'].includes(ext)) return { bg: '#2563eb', label: ext.toUpperCase() };
  if (['ppt', 'pptx'].includes(ext)) return { bg: '#ea580c', label: ext.toUpperCase() };
  if (['zip', 'rar', '7z'].includes(ext)) return { bg: '#7c3aed', label: ext.toUpperCase() };
  return { bg: '#64748b', label: ext ? ext.toUpperCase() : 'FILE' };
}

function attachPreviewHtml(attachment, salecarId) {
  const url = typeof attachment === 'object' ? attachment.url : attachment;
  const name = typeof attachment === 'object' ? attachment.name : null;
  const proxy = attachProxy(url, salecarId, name);
  const uid = Math.random().toString(36).slice(2, 8);
  const ext = name ? name.split('.').pop().toLowerCase() : null;
  const imgExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
  const isFile = ext && !imgExts.includes(ext);

  if (isFile) {
    const st = fileCardStyle(name);
    return `
    <div class="d-inline-block m-1" style="width:80px;vertical-align:top;">
      <a href="${proxy}" target="_blank" class="d-flex flex-column align-items-center justify-content-center rounded text-white text-decoration-none" style="width:80px;height:80px;background:${st.bg};">
        <i class="bx bx-file" style="font-size:1.8rem;"></i>
        <span class="badge bg-white mt-1" style="font-size:.6rem;color:${st.bg};font-weight:700;">${st.label}</span>
      </a>
      <div class="text-truncate text-center text-dark mt-1" style="font-size:.7rem;max-width:80px;" title="${name}">${name}</div>
    </div>`;
  }
  return `
  <div class="d-inline-block m-1" style="width:80px;vertical-align:top;">
    <a href="${proxy}" target="_blank" id="imgw-${uid}" style="display:block;">
      <img src="${proxy}" class="rounded border" style="width:80px;height:80px;object-fit:cover;cursor:pointer;"
        onerror="document.getElementById('imgw-${uid}').style.display='none';document.getElementById('filew-${uid}').style.display='flex';">
    </a>
    <a href="${proxy}" target="_blank" id="filew-${uid}" class="text-decoration-none"
      style="display:none;width:80px;height:80px;border-radius:0.375rem;background:#64748b;flex-direction:column;align-items:center;justify-content:center;color:white;">
      <i class="bx bx-file" style="font-size:1.8rem;"></i>
    </a>
  </div>`;
}

function renderWithdrawAttachments(attachments, salecarId) {
  currentAttachments = attachments || [];
  const $list = $('#withdrawAttachmentList');
  $list.empty();

  const visible = currentAttachments.filter(att => {
    const u = typeof att === 'object' ? att.url : att;
    return !stagedDeletes.includes(u);
  });

  if (visible.length === 0) {
    // $list.html('<p class="text-muted small">ยังไม่มีไฟล์แนบ</p>');
    return;
  }

  visible.forEach(function (att) {
    const url = typeof att === 'object' ? att.url : att;
    const name = typeof att === 'object' ? att.name : null;
    const proxy = attachProxy(url, salecarId, name);
    const uid = Math.random().toString(36).slice(2, 8);
    const ext = name ? name.split('.').pop().toLowerCase() : null;
    const imgExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
    const isFile = ext && !imgExts.includes(ext);

    let cardHtml;
    if (isFile) {
      const st = fileCardStyle(name);
      cardHtml = `
        <a href="${proxy}" target="_blank" class="d-flex flex-column align-items-center justify-content-center rounded text-white text-decoration-none" style="width:80px;height:80px;background:${st.bg};">
          <i class="bx bx-file" style="font-size:1.8rem;"></i>
          <span class="badge bg-white mt-1" style="font-size:.6rem;color:${st.bg};font-weight:700;">${st.label}</span>
        </a>
        <div class="text-truncate text-center text-dark mt-1" style="font-size:.7rem;max-width:80px;" title="${name}">${name}</div>`;
    } else {
      cardHtml = `
        <a href="${proxy}" target="_blank" id="imgw-${uid}" style="display:block;">
          <img src="${proxy}" class="rounded border" style="width:80px;height:80px;object-fit:cover;cursor:pointer;"
            onerror="document.getElementById('imgw-${uid}').style.display='none';document.getElementById('filew-${uid}').style.display='flex';">
        </a>
        <a href="${proxy}" target="_blank" id="filew-${uid}" class="text-decoration-none"
          style="display:none;width:80px;height:80px;border-radius:0.375rem;background:#64748b;flex-direction:column;align-items:center;justify-content:center;color:white;">
          <i class="bx bx-file" style="font-size:1.8rem;"></i>
        </a>`;
    }

    const $item = $(
      `<div class="position-relative d-inline-block m-1" style="width:80px;vertical-align:top;">
        ${cardHtml}
        <button type="button" class="btn btn-danger btn-stage-delete position-absolute top-0 end-0" 
                style="font-size:.8rem;line-height:1;padding:2px 5px;" title="ลบ" data-url="${url}">
          <i class="bx bx-x"></i>
        </button>
      </div>`
    );
    $item.find('.btn-stage-delete').on('click', function () {
      stagedDeletes.push($(this).data('url'));
      renderWithdrawAttachments(currentAttachments, salecarId);
    });
    $list.append($item);
  });
}

function renderFilePreviews(input, $preview) {
  $preview.empty();
  Array.from(input.files).forEach(function (file, idx) {
    const isImg = /image/i.test(file.type);
    const objUrl = isImg ? URL.createObjectURL(file) : null;
    const st = isImg ? null : fileCardStyle(file.name);
    const $item = $(
      `<div class="position-relative d-inline-block m-1" style="width:80px;vertical-align:top;">
        ${
          isImg
            ? `<img src="${objUrl}" class="rounded border" style="width:80px;height:80px;object-fit:cover;">`
            : `<div class="d-flex flex-column align-items-center justify-content-center rounded text-white" style="width:80px;height:80px;background:${st.bg};">
               <i class="bx bx-file" style="font-size:1.8rem;"></i>
               <span class="badge bg-white mt-1" style="font-size:.6rem;color:${st.bg};font-weight:700;">${st.label}</span>
             </div>
             <div class="text-truncate text-center text-dark mt-1" style="font-size:.7rem;max-width:80px;">${file.name}</div>`
        }
        <button type="button" class="btn btn-danger btn-remove-new-file position-absolute top-0 end-0" style="font-size:.8rem;line-height:1;padding:2px 5px;" title="ลบ"><i class="bx bx-x"></i></button>
      </div>`
    );
    $item.find('.btn-remove-new-file').on('click', function () {
      const dt = new DataTransfer();
      Array.from(input.files).forEach(function (f, i) {
        if (i !== idx) dt.items.add(f);
      });
      input.files = dt.files;
      renderFilePreviews(input, $preview);
    });
    $preview.append($item);
  });
}

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
