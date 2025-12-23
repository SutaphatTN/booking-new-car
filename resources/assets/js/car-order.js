$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});

//view : table car-order
let carOrderTable;

$(document).ready(function () {
  if ($.fn.DataTable.isDataTable('.carOrderTable')) {
    $('.carOrderTable').DataTable().destroy();
  }

  carOrderTable = $('.carOrderTable').DataTable({
    ajax: '/car-order/list',
    columns: [
      { data: 'No' },
      { data: 'date' },
      { data: 'car' },
      { data: 'vin_number' },
      { data: 'j_number' },
      { data: 'status' },
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

// blur focus viewCarOrder
$(document).on('hide.bs.modal', '.viewCarOrder', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

//view-more car-order
$(document).on('click', '.btnViewCarOrder', function () {
  const id = $(this).data('id');

  $.get('/car-order/' + id + '/view-more', function (html) {
    $('.viewMoreCarOrder').html(html);
    $('.viewCarOrder').modal('show');
  });
});

//css : format number
$(document).ready(function () {
  $('.money-input').each(function () {
    let value = $(this).val();
    if (value && !isNaN(value.replace(/,/g, ''))) {
      $(this).val(
        parseFloat(value.replace(/,/g, '')).toLocaleString(undefined, {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        })
      );
    }
  });
});

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

//hide select
function toggleOrderStatusFields($modal) {
  const selected = $modal.find('#order_status option:selected');
  const statusName = selected.data('name');

  $modal.find('#fieldInvoice, #fieldStock').addClass('d-none');

  if (statusName === 'Invoice') {
    $modal.find('#fieldInvoice').removeClass('d-none');
  } else if (statusName === 'Stock') {
    $modal.find('#fieldStock').removeClass('d-none');
  }
}

$(document).on('change', '#order_status', function () {
  const $modal = $(this).closest('.modal');
  toggleOrderStatusFields($modal);
});

// blur focus editCarOrder
$(document).on('hide.bs.modal', '.editCarOrder', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

//edit : car-order
$(document).on('click', '.btnEditCarOrder', function () {
  const id = $(this).data('id');
  const $btn = $(this);
  const form = $btn.closest('form')[0];

  $.get('/car-order/' + id + '/edit', function (html) {
    $('.editCarOrderModal').html(html);
    const $modal = $('.editCarOrder');

    $modal.modal('show');

    setTimeout(() => {
      toggleOrderStatusFields($modal);
    }, 50);

    $modal
      .find('.btnUpdateCarOrder')
      .off('click')
      .on('click', function (e) {
        e.preventDefault();

        const form = $modal.find('form')[0];
        const formData = new FormData(form);

        $.ajax({
          url: form.action,
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,

          beforeSend: function () {
            $modal.modal('hide');

            Swal.fire({
              title: 'กำลังบันทึกข้อมูล...',
              text: 'กรุณารอสักครู่',
              allowOutsideClick: false,
              didOpen: () => {
                Swal.showLoading();
              }
            });
            $btn.prop('disabled', true);
          },
          success: function (res) {
            Swal.fire({
              icon: 'success',
              title: 'สำเร็จ!',
              text: res.message,
              timer: 2000,
              showConfirmButton: true
            });

            carOrderTable.ajax.reload(null, false);
          },
          error: function (xhr) {
            $modal.modal('hide');
            Swal.fire({
              icon: 'error',
              title: 'เกิดข้อผิดพลาด!',
              text: xhr.responseJSON?.message || 'ไม่สามารถบันทึกข้อมูลได้'
            });
          },
          complete: function () {
            $btn.prop('disabled', false);
          }
        });
      });
  });
});

//delete car-order
$(document).on('click', '.btnDeleteCarOrder', function () {
  let id = $(this).data('id');

  Swal.fire({
    title: 'คุณแน่ใจหรือไม่?',
    text: 'คุณต้องการลบข้อมูลนี้ใช่หรือไม่?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#6c5ffc',
    cancelButtonColor: '#d33',
    confirmButtonText: 'ใช่, ลบเลย!',
    cancelButtonText: 'ยกเลิก'
  }).then(result => {
    if (result.isConfirmed) {
      $.ajax({
        url: '/car-order/' + id,
        type: 'DELETE',

        success: function (res) {
          if (res.success) {
            Swal.fire({
              icon: 'success',
              title: 'สำเร็จ',
              text: res.message,
              timer: 2000,
              showConfirmButton: true
            });

            carOrderTable.ajax.reload(null, false);
          } else {
            Swal.fire({
              icon: 'error',
              title: 'เกิดข้อผิดพลาด',
              text: 'ไม่สามารถลบข้อมูลได้'
            });
          }
        },
        error: function (xhr) {
          let errMsg = 'ไม่สามารถลบข้อมูลได้';
          if (xhr.responseJSON && xhr.responseJSON.message) {
            errMsg = xhr.responseJSON.message;
          }
          Swal.fire({
            icon: 'error',
            title: 'เกิดข้อผิดพลาด',
            text: errMsg
          });
        }
      });
    }
  });
});

// history
// view history car-order
let historyCarOrderTable;

$(document).ready(function () {
  if ($.fn.DataTable.isDataTable('.historyCarOrderTable')) {
    $('.historyCarOrderTable').DataTable().destroy();
  }

  historyCarOrderTable = $('.historyCarOrderTable').DataTable({
    ajax: '/car-order/history/list',
    columns: [
      { data: 'No' },
      { data: 'full_name' },
      { data: 'order_code' },
      { data: 'model' },
      { data: 'subModel' },
      { data: 'booking' },
      { data: 'status' }
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

//pending
// view pending car-order
let pendingOrderTable;

$(document).ready(function () {
  if ($.fn.DataTable.isDataTable('.pendingOrderTable')) {
    $('.pendingOrderTable').DataTable().destroy();
  }

  pendingOrderTable = $('.pendingOrderTable').DataTable({
    ajax: '/car-order/pending/list',
    columns: [
      { data: 'No' },
      { data: 'order_code' },
      { data: 'date' },
      { data: 'type' },
      { data: 'model_id' },
      { data: 'subModel_id' },
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

// blur focus inputCarOrder
$(document).on('hide.bs.modal', '.inputCarOrder', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

//input : modal car-order
$(document).on('click', '.btnInputCarOrder', function () {
  $.get('/car-order/create', function (html) {
    $('.inputCarOrderModal').html(html);
    $('.inputCarOrder').modal('show');
  });
});

//input : save cam
$(document).on('click', '.btnStoreCarOrder', function (e) {
  e.preventDefault();

  const $btn = $(this);
  const form = $btn.closest('form')[0];
  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }

  const url = $(form).attr('action');
  const formData = new FormData(form);

  $.ajax({
    url: url,
    type: 'POST',
    data: formData,
    contentType: false,
    processData: false,
    beforeSend: function () {
      $('.inputCarOrder').modal('hide');

      Swal.fire({
        title: 'กำลังบันทึกข้อมูล...',
        text: 'กรุณารอสักครู่',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
      });
      $btn.prop('disabled', true);
    },
    success: function (res) {
      Swal.fire({
        icon: 'success',
        title: 'สำเร็จ',
        text: res.message,
        timer: 2000,
        showConfirmButton: true
      });

      pendingOrderTable.ajax.reload(null, false);
    },
    error: function (xhr) {
      let errMsg = 'ไม่สามารถบันทึกข้อมูลได้';
      if (xhr.responseJSON && xhr.responseJSON.message) {
        errMsg = xhr.responseJSON.message;
      }
      Swal.fire({
        icon: 'error',
        title: 'เกิดข้อผิดพลาด',
        text: errMsg
      });
    },
    complete: function () {
      $btn.prop('disabled', false);
    }
  });
});

//input : get sub model
$(document).on('change', '#model_id', function () {
  const modelId = $(this).val();
  const $subModelSelect = $('#subModel_id');

  $subModelSelect.empty().append('<option value="">-- เลือกรุ่นรถย่อย --</option>');

  if (!modelId) return;

  $.ajax({
    url: '/api/car-order/sub-model/' + modelId,
    type: 'GET',
    success: function (data) {
      console.log('data:', data);
      if (data.length > 0) {
        data.forEach(function (sub) {
          $subModelSelect.append(`<option value="${sub.id}">${sub.name}</option>`);
        });
      } else {
        $subModelSelect.append('<option value="">-- ไม่มีรุ่นย่อย --</option>');
      }
    },
    error: function () {
      alert('เกิดข้อผิดพลาดในการโหลดข้อมูลรุ่นย่อย');
    }
  });
});

// blur focus editPendingOrder
$(document).on('hide.bs.modal', '.editPendingOrder', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

//edit pending car-order
$(document).on('click', '.btnPendingOrder', function () {
  const id = $(this).data('id');
  const $btn = $(this);
  const form = $btn.closest('form')[0];

  $.get('/car-order/edit-pending/' + id, function (html) {
    $('.editPendingOrderModal').html(html);
    const $modal = $('.editPendingOrder');

    $modal.modal('show');

    setTimeout(() => {
      toggleOrderStatusFields($modal);
    }, 50);

    $modal
      .find('.btnUpdatePendingOrder')
      .off('click')
      .on('click', function (e) {
        e.preventDefault();

        const form = $modal.find('form')[0];
        const formData = new FormData(form);

        $.ajax({
          url: form.action,
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,

          beforeSend: function () {
            $modal.modal('hide');

            Swal.fire({
              title: 'กำลังบันทึกข้อมูล...',
              text: 'กรุณารอสักครู่',
              allowOutsideClick: false,
              didOpen: () => {
                Swal.showLoading();
              }
            });
            $btn.prop('disabled', true);
          },
          success: function (res) {
            Swal.fire({
              icon: 'success',
              title: 'สำเร็จ!',
              text: res.message,
              timer: 2000,
              showConfirmButton: true
            });

            pendingOrderTable.ajax.reload(null, false);
          },
          error: function (xhr) {
            $modal.modal('hide');
            Swal.fire({
              icon: 'error',
              title: 'เกิดข้อผิดพลาด!',
              text: xhr.responseJSON?.message || 'ไม่สามารถบันทึกข้อมูลได้'
            });
          },
          complete: function () {
            $btn.prop('disabled', false);
          }
        });
      });
  });
});

//delete pending car-order
$(document).on('click', '.btnDeletePendingOrder', function () {
  let id = $(this).data('id');

  Swal.fire({
    title: 'คุณแน่ใจหรือไม่?',
    text: 'คุณต้องการลบข้อมูลนี้ใช่หรือไม่?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#6c5ffc',
    cancelButtonColor: '#d33',
    confirmButtonText: 'ใช่, ลบเลย!',
    cancelButtonText: 'ยกเลิก'
  }).then(result => {
    if (result.isConfirmed) {
      $.ajax({
        url: '/car-order/destroy-pending/' + id,
        type: 'DELETE',

        success: function (res) {
          if (res.success) {
            Swal.fire({
              icon: 'success',
              title: 'สำเร็จ',
              text: res.message,
              timer: 2000,
              showConfirmButton: true
            });

            pendingOrderTable.ajax.reload(null, false);
          } else {
            Swal.fire({
              icon: 'error',
              title: 'เกิดข้อผิดพลาด',
              text: 'ไม่สามารถลบข้อมูลได้'
            });
          }
        },
        error: function (xhr) {
          let errMsg = 'ไม่สามารถลบข้อมูลได้';
          if (xhr.responseJSON && xhr.responseJSON.message) {
            errMsg = xhr.responseJSON.message;
          }
          Swal.fire({
            icon: 'error',
            title: 'เกิดข้อผิดพลาด',
            text: errMsg
          });
        }
      });
    }
  });
});

//process
// view process car-order
let processOrderTable;

$(document).ready(function () {
  if ($.fn.DataTable.isDataTable('.processOrderTable')) {
    $('.processOrderTable').DataTable().destroy();
  }

  processOrderTable = $('.processOrderTable').DataTable({
    ajax: '/car-order/process/list',
    columns: [
      { data: 'No' },
      { data: 'date' },
      { data: 'type' },
      { data: 'model_id' },
      { data: 'subModel_id' },
      { data: 'color' },
      { data: 'cost' },
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

// edit process car-order old

// $(document).ready(function () {
//   // --- ตรวจสอบ openId จาก div ---
//   const openId = $('#openIdHolder').data('open-id');
//   if (openId) {
//     openProcessModal(openId);
//   }

//   // --- ฟังก์ชันเดิม กดปุ่มในตาราง ---
//   $(document).on('click', '.btnProcessCarOrder', function () {
//     const id = $(this).data('id');
//     openProcessModal(id);
//   });

//   // --- ฟังก์ชันเปิด modal ---
//   function openProcessModal(id) {
//     $.get('/car-order/edit-process/' + id, function (html) {
//       $('.editProcessOrderModal').html(html);
//       const $modal = $('.editProcessOrder');

//       $modal.modal('show');

//       const $form = $modal.find('#processOrderForm');

//       // ปุ่มอนุมัติ
//       $modal.find('.btnApproverOrder').on('click', function () {
//         $('#action_status').val('approve');
//         $modal.modal('hide');
//         Swal.fire({
//           title: 'ยืนยันการอนุมัติ?',
//           text: 'คุณแน่ใจใช่หรือไม่ว่าต้องการอนุมัติคำขอนี้',
//           icon: 'question',
//           showCancelButton: true,
//           confirmButtonColor: '#6c5ffc',
//           cancelButtonColor: '#d33',
//           confirmButtonText: 'ใช่, อนุมัติ',
//           cancelButtonText: 'ยกเลิก'
//         }).then(result => {
//           if (result.isConfirmed) {
//             submitProcessForm($form, $modal);
//           }
//         });
//       });

//       // ปุ่มไม่อนุมัติ
//       $modal.find('.btnRejectOrder').on('click', function () {
//         $('#action_status').val('reject');
//         $modal.modal('hide');
//         Swal.fire({
//           title: 'ระบุเหตุผลที่ไม่อนุมัติ',
//           input: 'textarea',
//           inputPlaceholder: 'กรอกเหตุผลที่ไม่อนุมัติ...',
//           inputAttributes: { 'aria-label': 'เหตุผลที่ไม่อนุมัติ' },
//           showCancelButton: true,
//           confirmButtonColor: '#6c5ffc',
//           cancelButtonColor: '#d33',
//           confirmButtonText: 'บันทึก',
//           cancelButtonText: 'ยกเลิก',
//           preConfirm: reason => {
//             if (!reason || reason.trim() === '') {
//               Swal.showValidationMessage('กรุณากรอกเหตุผลก่อนบันทึก');
//               return false;
//             }
//             $('#reason').val(reason);
//           }
//         }).then(result => {
//           if (result.isConfirmed) {
//             submitProcessForm($form, $modal);
//           }
//         });
//       });
//     });
//   }

//   // --- ฟังก์ชัน submit form ---
//   function submitProcessForm($form, $modal) {
//     const formData = new FormData($form[0]);

//     $.ajax({
//       url: $form.attr('action'),
//       type: 'POST',
//       data: formData,
//       processData: false,
//       contentType: false,
//       beforeSend: function () {
//         $modal.modal('hide');
//         Swal.fire({
//           title: 'กำลังบันทึกข้อมูล...',
//           text: 'กรุณารอสักครู่',
//           didOpen: () => Swal.showLoading(),
//           allowOutsideClick: false
//         });
//       },
//       success: function (res) {
//         Swal.fire({
//           icon: 'success',
//           title: 'สำเร็จ!',
//           text: res.message,
//           timer: 2000,
//           showConfirmButton: true
//         });

//         processOrderTable.ajax.reload(null, false);
//       },
//       error: function (xhr) {
//         Swal.fire({
//           icon: 'error',
//           title: 'เกิดข้อผิดพลาด!',
//           text: xhr.responseJSON?.message || 'ไม่สามารถบันทึกข้อมูลได้'
//         });
//       }
//     });
//   }
// });

// blur focus editProcessOrder
$(document).on('hide.bs.modal', '.editProcessOrder', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

// edit process car-order
$(document).on('click', '.btnProcessCarOrder', function () {
  const id = $(this).data('id');
  console.log('Process ID =', id);

  $.get('/car-order/edit-process/' + id, function (html) {
    $('.editProcessOrderModal').html(html);
    $('.editProcessOrder').modal('show');
  });
});

// อนุมัติ
$(document).on('click', '.btnApproveProcess', function () {
  const id = $(this).data('id');

  Swal.fire({
    title: 'ยืนยันการอนุมัติ?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#6c5ffc',
    cancelButtonColor: '#d33',
    confirmButtonText: 'ใช่, อนุมัติ',
    cancelButtonText: 'ยกเลิก'
  }).then(result => {
    if (result.isConfirmed) {
      submitProcessDirect(id, 'approve', null);
    }
  });
});

// ไม่อนุมัติ
$(document).on('click', '.btnRejectProcess', function () {
  const id = $(this).data('id');

  Swal.fire({
    title: 'ระบุเหตุผลที่ไม่อนุมัติ',
    input: 'textarea',
    inputPlaceholder: 'กรอกเหตุผลที่ไม่อนุมัติ...',
    showCancelButton: true,
    confirmButtonColor: '#6c5ffc',
    cancelButtonColor: '#d33',
    confirmButtonText: 'บันทึก',
    cancelButtonText: 'ยกเลิก',
    preConfirm: reason => {
      if (!reason || reason.trim() === '') {
        Swal.showValidationMessage('กรุณากรอกเหตุผลก่อนบันทึก');
        return false;
      }
      return reason;
    }
  }).then(result => {
    if (result.isConfirmed) {
      submitProcessDirect(id, 'reject', result.value);
    }
  });
});

function submitProcessDirect(id, action, reason) {
  const formData = new FormData();
  formData.append('action_status', action);
  formData.append('reason', reason ?? '');
  formData.append('_method', 'PUT');
  formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

  $.ajax({
    url: '/car-order/update-process/' + id,
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    beforeSend: function () {
      Swal.fire({
        title: 'กำลังบันทึกข้อมูล...',
        didOpen: () => Swal.showLoading(),
        allowOutsideClick: false
      });
    },
    success: function (res) {
      Swal.fire({
        icon: 'success',
        title: 'สำเร็จ!',
        text: res.message,
        timer: 2000,
        showConfirmButton: true
      });

      processOrderTable.ajax.reload(null, false);
    },
    error: function (xhr) {
      Swal.fire({
        icon: 'error',
        title: 'เกิดข้อผิดพลาด!',
        text: xhr.responseJSON?.message || 'ไม่สามารถบันทึกข้อมูลได้'
      });
    }
  });
}

// view approve car-order
let approveOrderTable;

$(document).ready(function () {
  if ($.fn.DataTable.isDataTable('.approveOrderTable')) {
    $('.approveOrderTable').DataTable().destroy();
  }

  approveOrderTable = $('.approveOrderTable').DataTable({
    ajax: '/car-order/approve/list',
    columns: [
      { data: 'No' },
      { data: 'date' },
      { data: 'type' },
      { data: 'model_id' },
      { data: 'subModel_id' },
      { data: 'color' },
      { data: 'cost' },
      { data: 'status' },
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

// blur focus editApproveOrder
$(document).on('hide.bs.modal', '.editApproveOrder', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

// edit approve car-order
$(document).on('click', '.btnApproveCarOrder', function () {
  const id = $(this).data('id');
  const $btn = $(this);
  const form = $btn.closest('form')[0];

  $.get('/car-order/edit-approve/' + id, function (html) {
    $('.editApproveOrderModal').html(html);
    const $modal = $('.editApproveOrder');

    $modal.modal('show');

    setTimeout(() => {
      toggleOrderStatusFields($modal);
    }, 50);

    $modal
      .find('.btnUpdateApproveOrder')
      .off('click')
      .on('click', function (e) {
        e.preventDefault();

        const form = $modal.find('form')[0];
        const formData = new FormData(form);

        $.ajax({
          url: form.action,
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,

          beforeSend: function () {
            $modal.modal('hide');

            Swal.fire({
              title: 'กำลังบันทึกข้อมูล...',
              text: 'กรุณารอสักครู่',
              allowOutsideClick: false,
              didOpen: () => {
                Swal.showLoading();
              }
            });
            $btn.prop('disabled', true);
          },
          success: function (res) {
            Swal.fire({
              icon: 'success',
              title: 'สำเร็จ!',
              text: res.message,
              timer: 2000,
              showConfirmButton: true
            });

            approveOrderTable.ajax.reload(null, false);
          },
          error: function (xhr) {
            $modal.modal('hide');
            Swal.fire({
              icon: 'error',
              title: 'เกิดข้อผิดพลาด!',
              text: xhr.responseJSON?.message || 'ไม่สามารถบันทึกข้อมูลได้'
            });
          },
          complete: function () {
            $btn.prop('disabled', false);
          }
        });
      });
  });
});

// email
// document.addEventListener('DOMContentLoaded', function () {
//   const orderId = window.location.pathname.split('/').pop();

//   $.get('/car-order/edit-process/' + orderId, function (html) {
//     $('#modalContainer').html(html);

//     const $modal = $('.editProcessOrder');

//     $modal.modal('show');
//   });
// });
