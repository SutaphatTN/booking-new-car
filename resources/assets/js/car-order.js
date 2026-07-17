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
    ajax: {
      url: '/car-order/list',
      data: function (d) {
        d.model_id = $('#filter_model').val();
        d.sub_model_id = $('#filter_subModel').val();
      }
    },
    columns: [
      { data: 'No' },
      { data: 'car' },
      { data: 'vin_number' },
      { data: 'j_number' },
      { data: 'date' },
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
        next: 'ถัดไป',
        previous: 'ก่อนหน้า'
      }
    }
  });

  carOrderTable.on('draw', function () {
    $('[data-bs-toggle="tooltip"]').tooltip();
  });

  // คุม loader overlay เอง
  carOrderTable.on('preXhr.dt', function () {
    $('#carOrderLoadingOverlay').css('display', 'flex');
  });
  carOrderTable.on('xhr.dt', function () {
    $('#carOrderLoadingOverlay').css('display', 'none');
  });

  $('[data-bs-toggle="tooltip"]').tooltip();
});

//search filter car
$(document).on('change', '#filter_model', function () {
  const modelId = $(this).val();
  const $sub = $('#filter_subModel');

  $sub.prop('disabled', true).empty().append('<option value="">-- ทั้งหมด --</option>');

  if (!modelId) {
    carOrderTable.ajax.reload();
    return;
  }

  $.ajax({
    url: '/api/car-order/sub-model',
    data: { model_id: modelId },
    success: function (data) {
      if (data.length) {
        data.forEach(sub => {
          let text = sub.detail ? `${sub.detail} - ${sub.name}` : sub.name;

          $sub.append(`<option value="${sub.id}">${text}</option>`);
        });
        $sub.prop('disabled', false);
      } else {
        $sub.append('<option value="">-- ไม่มีรุ่นย่อย --</option>');
      }
    }
  });

  carOrderTable.ajax.reload();
});

//ปุ่มค้นหา
$(document).on('change', '#filter_subModel', function () {
  carOrderTable.ajax.reload();
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

  $modal.find('#fieldOnWeb, #fieldInvoice, #fieldStock').addClass('d-none');

  if (statusName === 'Onweb') {
    $modal.find('#fieldOnWeb').removeClass('d-none');
  } else if (statusName === 'Invoice') {
    $modal.find('#fieldInvoice').removeClass('d-none');
  } else if (statusName === 'Stock') {
    $modal.find('#fieldStock').removeClass('d-none');
  }
}

// วันที่จ่าย FP โชว์เมื่อประเภทการจ่าย = FP Tisco (ค้างไว้ทุกสถานะ)
function toggleFpField($modal) {
  const isFp = $modal.find('#payment_type').val() === 'fp_tisco';
  $modal.find('#fieldFp').toggleClass('d-none', !isFp);
}

// FP Tisco + สถานะ Invoice/Stock → ต้องกรอกวันที่จ่าย FP (คืน false = ไม่ผ่าน)
function validateFpDateRequired($modal) {
  const isFp = $modal.find('#payment_type').val() === 'fp_tisco';
  const statusName = $modal.find('#order_status option:selected').data('name');

  if (isFp && (statusName === 'Invoice' || statusName === 'Stock') && !$modal.find('#fp_date').val()) {
    toggleFpField($modal); // ให้ช่องโผล่แน่ ๆ
    Swal.fire({
      icon: 'warning',
      title: 'กรุณากรอกวันที่จ่าย FP',
      text: 'ประเภทการจ่ายเป็น FP Tisco และสถานะเป็น ' + statusName + ' ต้องระบุวันที่จ่าย FP ก่อนบันทึก'
    }).then(() => $modal.find('#fp_date').focus());
    return false;
  }
  return true;
}

$(document).on('change', '#order_status', function () {
  const $modal = $(this).closest('.modal');
  toggleOrderStatusFields($modal);
});

$(document).on('change', '#payment_type', function () {
  const $modal = $(this).closest('.modal');
  toggleFpField($modal);
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
      toggleFpField($modal);
    }, 50);

    $modal
      .find('.btnUpdateCarOrder')
      .off('click')
      .on('click', function (e) {
        e.preventDefault();

        // FP Tisco + สถานะ Invoice/Stock ต้องกรอกวันที่จ่าย FP ก่อน
        if (!validateFpDateRequired($modal)) return;

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
      { data: 'model' },
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

//input : หมายเหตุ Tooltip
document.addEventListener('shown.bs.modal', function (event) {
  const modal = event.target;

  const tooltipTriggerList = modal.querySelectorAll('[data-bs-toggle="tooltip"]');
  tooltipTriggerList.forEach(el => {
    new bootstrap.Tooltip(el);
  });
});

//input : when select purchase type to Test Drive
function toggleTestDriveFields($modal) {
  const selected = $modal.find('#purchase_type').val();

  $modal.find('.fieldTestDrive').addClass('d-none');

  if (selected == '1') {
    $modal.find('.fieldTestDrive').removeClass('d-none');
  }
}

$(document).on('change', '#purchase_type', function () {
  const $modal = $(this).closest('.modal');
  toggleTestDriveFields($modal);

  $('#modelError').addClass('d-none').text('');
});

//input : hide select customer
function togglePurchaseFields($modal) {
  const selected = $modal.find('#type').val();
  const isWaiting = selected === 'stock' || selected === 'auction';

  $modal.find('#fieldPurchase').addClass('d-none');
  $modal.find('#fieldCountOrder').addClass('d-none');
  $modal.find('#count_order').removeAttr('required');

  // ปรับ col ของแหล่งที่มา และประเภทการซื้อ
  $modal.find('#wrapPurchaseSource').toggleClass('col-md-5', !isWaiting).toggleClass('col-md-3', isWaiting);
  $modal.find('#wrapPurchaseType').toggleClass('col-md-4', !isWaiting).toggleClass('col-md-3', isWaiting);

  if (selected === 'customer') {
    $modal.find('#fieldPurchase').removeClass('d-none');
  } else if (isWaiting) {
    $modal.find('#fieldCountOrder').removeClass('d-none');
    $modal.find('#count_order').attr('required', true);
  }
}

$(document).on('change', '#type', function () {
  const $modal = $(this).closest('.modal');
  togglePurchaseFields($modal);

  $('#modelError').addClass('d-none').text('');

  $('#model_id').val('');
  $('#subModel_id').empty().append('<option value="">-- เลือกรุ่นรถย่อย --</option>').prop('disabled', true);

  toggleCarSelectByType();
});

$(document).on('shown.bs.modal', '.modal', function () {
  const $modal = $(this);
  togglePurchaseFields($modal);
  toggleTestDriveFields($modal);
});

function loadModelByCustomer(saleCarId) {
  $('#modelError').addClass('d-none').text('');

  $.get(
    '/api/car-order/models-by-customer',
    {
      salecar_id: saleCarId
    },
    function (res) {
      if (!res.success) {
        $('#model_id').prop('disabled', true);
        $('#modelError').removeClass('d-none').text(res.message);
        return;
      }

      res.data.forEach(m => {
        $('#model_id').append(`<option value="${m.id}">${m.Name_TH}</option>`);
      });

      $('#model_id').prop('disabled', false);
    }
  );
}

//input : search purchase customer
$(document).on('hidden.bs.modal', '#modalSearchPurchaseCus', function () {
  if (!$('.inputCarOrder').hasClass('show')) {
    setTimeout(() => {
      $('.inputCarOrder').modal('show');
    }, 200);
  }
});

$(document).ready(function () {
  // กด Enter ใน input
  $(document).on('keypress', '#purchaseCus', function (e) {
    if (e.which === 13) {
      e.preventDefault();
      $(this).siblings('.btnPurchaseCus').trigger('click');
    }
  });

  // คลิกปุ่มค้นหา
  $(document).on('click', '.btnPurchaseCus', function () {
    const keyword = $('#purchaseCus').val();
    if (!keyword.trim()) return;

    $('.inputCarOrder').modal('hide');

    setTimeout(() => {
      searchPurchaseCus(keyword);
    }, 300);
  });

  function searchPurchaseCus(keyword) {
    if (!keyword.trim()) return;

    $.ajax({
      url: '/purchase-order/search',
      type: 'GET',
      data: { keyword },
      success: function (res) {
        const $tableBody = $('#tableSelectPurchaseCus tbody');
        $tableBody.empty();

        if (!res.length) {
          $tableBody.append(`
          <tr>
            <td colspan="7" class="text-center">ไม่พบข้อมูลการจองของลูกค้า</td>
          </tr>
        `);
        } else {
          res.forEach(c => {
            const fullName = `${c.customer?.prefix?.Name_TH ?? ''}${c.customer?.FirstName ?? ''} ${c.customer?.LastName ?? ''}`;

            $tableBody.append(`
            <tr>
              <td>${fullName}</td>
              <td>${c.model?.Name_TH ?? '-'}</td>
              <td>${c.sub_model?.name ?? ''} ${c.sub_model?.detail ?? ''}</td>
              <td>${c.option ?? '-'}</td>
              <td>${c.Color ?? '-'}</td>
              <td>${c.Year ?? '-'}</td>
              <td>
                <button
                  class="btn btn-sm btn-primary btnSelectPurchaseCus"
                  data-id="${c.id}"
                  data-name="${fullName}">
                  เลือก
                </button>
              </td>
            </tr>
          `);
          });
        }

        $('#modalSearchPurchaseCus').modal('show');
      }
    });
  }

  $(document).on('click', '.btnSelectPurchaseCus', function () {
    const data = $(this).data();

    $('#purchaseCusName').val(data.name);
    $('#salecar_id').val(data.id);

    $('#purchaseCus').val('');

    $('#modalSearchPurchaseCus').modal('hide');

    setTimeout(() => {
      $('.inputCarOrder').modal('show');
    }, 300);

    $('#model_id').empty().append('<option value="">-- เลือกรุ่นรถหลัก --</option>').prop('disabled', true);

    $('#subModel_id').empty().append('<option value="">-- เลือกรุ่นรถย่อย --</option>').prop('disabled', true);

    $('#modelError').addClass('d-none').text('');

    loadModelByCustomer(data.id);
  });
});

//input : get sub mode
$(document).on('change', '#model_id', function () {
  const modelId = $('#model_id').val();
  const $subModel = $('#subModel_id');

  $subModel.prop('disabled', true).empty().append('<option value="">-- เลือกรุ่นรถย่อย --</option>');
  $('#interior_color').prop('disabled', true).empty().append('<option value="">-- เลือกสี --</option>');

  if (!modelId) return;

  const $interiorColor = $('#interior_color');
  if ($interiorColor.length) {
    $.get('/api/interior-color', { model_id: modelId }, function (data) {
      if (data.length) {
        data.forEach(c => $interiorColor.append(`<option value="${c.id}">${c.name}</option>`));
        $interiorColor.prop('disabled', false);
      }
    });
  }

  $.ajax({
    url: '/api/car-order/sub-model',
    data: {
      model_id: modelId
    },
    success: function (data) {
      if (data.length) {
        data.forEach(sub => {
          let text = sub.detail ? `${sub.detail} - ${sub.name}` : sub.name;

          $subModel.append(`<option value="${sub.id}">${text}</option>`);
        });
        $subModel.prop('disabled', false);
      } else {
        $subModel.append('<option value="">-- ไม่มีรุ่นย่อย --</option>');
      }
    }
  });
});

function toggleCarSelectByType() {
  const type = $('#type').val();
  const saleCarId = $('#salecar_id').val();

  if (type === 'customer') {
    $('#model_id').prop('disabled', !saleCarId);
    $('#subModel_id').prop('disabled', true);
  } else {
    $('#model_id').prop('disabled', false);
    $('#subModel_id').prop('disabled', false);
  }
}

$(document).on('change', '#type', toggleCarSelectByType);
$(document).on('change', '#salecar_id', toggleCarSelectByType);
$(document).on('shown.bs.modal', '.inputCarOrder', toggleCarSelectByType);

//input : get color
$(document).on('change', '#subModel_id', function () {
  const subModelId = $(this).val();
  const $color = $('#gwm_color');

  $color.prop('disabled', true).empty().append('<option value="">-- เลือกสี --</option>');

  if (!subModelId) return;

  $.ajax({
    url: '/api/car-order/color',
    data: {
      sub_model_id: subModelId
    },
    success: function (data) {
      if (data.length) {
        data.forEach(color => {
          $color.append(`<option value="${color.id}">${color.name}</option>`);
        });
        $color.prop('disabled', false);
      } else {
        $color.append('<option value="">-- รุ่นนี้ไม่มีตัวเลือกสี --</option>');
      }
    }
  });
});

//input : save car order
$(document).on('click', '.btnStoreCarOrder', function (e) {
  e.preventDefault();

  const $btn = $(this);
  const form = $btn.closest('form')[0];
  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }

  const type = $(form).find('#type').val();
  const isWaiting = type === 'stock' || type === 'auction';
  const url = isWaiting ? '/car-order/store-waiting' : $(form).attr('action');
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
      toggleFpField($modal);
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

// blur focus editWaitingOrder
$(document).on('hide.bs.modal', '.editWaitingOrder', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

//edit waiting (pending page)
$(document).on('click', '.btnEditWaiting', function () {
  const id = $(this).data('id');
  const $btn = $(this);

  $.get('/car-order/edit-waiting/' + id, function (html) {
    $('.editWaitingOrderModal').html(html);
    const $modal = $('.editWaitingOrder');
    $modal.modal('show');

    $modal
      .find('.btnUpdateWaitingOrder')
      .off('click')
      .on('click', function (e) {
        e.preventDefault();

        const form = $modal.find('form')[0];
        if (!form.checkValidity()) {
          form.reportValidity();
          return;
        }

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
              didOpen: () => Swal.showLoading(),
              allowOutsideClick: false
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

//delete waiting (pending page)
$(document).on('click', '.btnDeleteWaiting', function () {
  const id = $(this).data('id');

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
        url: '/car-order/destroy-waiting/' + id,
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
            Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: 'ไม่สามารถลบข้อมูลได้' });
          }
        },
        error: function (xhr) {
          Swal.fire({
            icon: 'error',
            title: 'เกิดข้อผิดพลาด',
            text: xhr.responseJSON?.message || 'ไม่สามารถลบข้อมูลได้'
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
      {
        data: null,
        orderable: false,
        searchable: false,
        className: 'text-center',
        render: function (row) {
          return `<input type="checkbox" class="form-check-input rowChk" data-id="${row.id}" data-type="${row.row_type}">`;
        }
      },
      { data: 'No' },
      { data: 'date' },
      { data: 'type' },
      { data: 'model_id' },
      { data: 'subModel_id' },
      { data: 'color' },
      { data: 'stock', className: 'text-center' },
      { data: 'order_qty', className: 'text-center' },
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
        next: 'ถัดไป',
        previous: 'ก่อนหน้า'
      }
    }
  });
});

// ===== ขออนุมัติที่เลือก / อนุมัติที่เลือก (process page) =====

// เลือกทั้งหมด
$(document).on('change', '#processChkAll', function () {
  $('.processOrderTable tbody .rowChk').prop('checked', this.checked);
});

// ถ้าติ๊กไม่ครบ ให้ยกเลิก checkbox หัวตาราง
$(document).on('change', '.processOrderTable tbody .rowChk', function () {
  const total = $('.processOrderTable tbody .rowChk').length;
  const checked = $('.processOrderTable tbody .rowChk:checked').length;
  $('#processChkAll').prop('checked', total > 0 && total === checked);
});

// เก็บรายการที่เลือก แยกตาม type
function getSelectedProcessRows() {
  const orderIds = [];
  const waitingIds = [];
  $('.processOrderTable tbody .rowChk:checked').each(function () {
    const id = $(this).data('id');
    if ($(this).data('type') === 'waiting') waitingIds.push(id);
    else orderIds.push(id);
  });
  return { orderIds, waitingIds, total: orderIds.length + waitingIds.length };
}

// เปิด modal ขออนุมัติ
$(document).on('click', '.btnRequestApproval', function () {
  const sel = getSelectedProcessRows();
  if (sel.total === 0) {
    Swal.fire({ icon: 'warning', title: 'กรุณาเลือกรายการอย่างน้อย 1 รายการ', confirmButtonText: 'ตกลง' });
    return;
  }
  $('#processApproverCount').text(sel.total);
  $('#process_approver_id').val('');
  $('#processApproverModal').modal('show');
});

// ยืนยันส่งคำขออนุมัติ
$(document).on('click', '.btnConfirmRequestApproval', function () {
  const approverId = $('#process_approver_id').val();
  if (!approverId) {
    Swal.fire({ icon: 'warning', title: 'กรุณาเลือกผู้อนุมัติ', confirmButtonText: 'ตกลง' });
    return;
  }
  const sel = getSelectedProcessRows();
  if (sel.total === 0) return;

  $.ajax({
    url: '/car-order/process/request-approval',
    type: 'POST',
    data: {
      _token: $('meta[name="csrf-token"]').attr('content'),
      approver_id: approverId,
      order_ids: sel.orderIds,
      waiting_ids: sel.waitingIds
    },
    beforeSend: function () {
      $('#processApproverModal').modal('hide');
      Swal.fire({ title: 'กำลังส่งคำขอ...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    },
    success: function (res) {
      Swal.fire({ icon: 'success', title: 'สำเร็จ!', text: res.message, timer: 2200, showConfirmButton: true });
      $('#processChkAll').prop('checked', false);
      processOrderTable.ajax.reload(null, false);
    },
    error: function (xhr) {
      Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด!', text: xhr.responseJSON?.message || 'ไม่สามารถส่งคำขอได้' });
    }
  });
});

// อนุมัติที่เลือก — order ลูกค้า + waiting (อนุมัติตามจำนวนที่สั่ง) ถ้ารับไม่ครบค่อยกดรายแถวกรอกจำนวนเอง
$(document).on('click', '.btnBulkApprove', function () {
  const sel = getSelectedProcessRows();
  if (sel.total === 0) {
    Swal.fire({ icon: 'warning', title: 'กรุณาเลือกรายการที่จะอนุมัติ', confirmButtonText: 'ตกลง' });
    return;
  }

  const waitingNote = sel.waitingIds.length > 0
    ? `<br><small class="text-muted">* รายการ stock/auction จะอนุมัติตามจำนวนที่สั่ง — หากรับไม่ครบ ให้กดอนุมัติทีละรายการแล้วระบุจำนวนเอง</small>`
    : '';

  Swal.fire({
    title: 'ยืนยันการอนุมัติ?',
    html: `อนุมัติตามจำนวนที่สั่ง ทั้งหมด <strong>${sel.total}</strong> รายการ${waitingNote}`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#6c5ffc',
    cancelButtonColor: '#d33',
    confirmButtonText: 'อนุมัติ',
    cancelButtonText: 'ยกเลิก'
  }).then(result => {
    if (!result.isConfirmed) return;

    $.ajax({
      url: '/car-order/process/bulk-approve',
      type: 'POST',
      data: {
        _token: $('meta[name="csrf-token"]').attr('content'),
        order_ids: sel.orderIds,
        waiting_ids: sel.waitingIds
      },
      beforeSend: function () {
        Swal.fire({ title: 'กำลังอนุมัติ...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
      },
      success: function (res) {
        Swal.fire({ icon: 'success', title: 'สำเร็จ!', text: res.message, timer: 2000, showConfirmButton: true });
        $('#processChkAll').prop('checked', false);
        processOrderTable.ajax.reload(null, false);
      },
      error: function (xhr) {
        Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด!', text: xhr.responseJSON?.message || 'ไม่สามารถอนุมัติได้' });
      }
    });
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

// ถามเมื่อไม่อนุมัติ
function askRejectReason(id) {
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
}

// ไม่อนุมัติ
$(document).on('click', '.btnRejectProcess', function () {
  const id = $(this).data('id');
  const hasSaleCar = $(this).data('has-salecar') == 1;

  if (hasSaleCar) {
    Swal.fire({
      icon: 'warning',
      title: 'รถคันนี้ผูกกับลูกค้าอยู่',
      text: 'หากไม่อนุมัติ ระบบจะยกเลิกการผูกรถ ต้องการดำเนินการต่อหรือไม่?',
      showCancelButton: true,
      confirmButtonText: 'ใช่ ดำเนินการต่อ',
      cancelButtonText: 'ยกเลิก',
      confirmButtonColor: '#6c5ffc',
      cancelButtonColor: '#d33'
    }).then(result => {
      if (result.isConfirmed) {
        askRejectReason(id);
      }
    });
  } else {
    askRejectReason(id);
  }
});

// blur focus viewWaitingOrder
$(document).on('hide.bs.modal', '.viewWaitingOrder', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

// ดูรายละเอียด waiting (stock/auction)
$(document).on('click', '.btnViewWaiting', function () {
  const id = $(this).data('id');

  $.get('/car-order/view-waiting/' + id, function (html) {
    $('.viewWaitingOrderModal').html(html);
    $('.viewWaitingOrder').modal('show');
  });
});

// อนุมัติ waiting (stock/auction)
$(document).on('click', '.btnApproveWaiting', function () {
  const id = $(this).data('id');
  const countOrder = $(this).data('count-order');

  Swal.fire({
    title: 'อนุมัติคำขอสั่งรถ',
    html: `
      <p class="mb-3 text-center">จำนวนที่สั่ง : <strong>${countOrder} คัน</strong></p>
      <div class="d-flex justify-content-center align-items-center gap-2">
        <label class="fw-semibold mb-0">สั่งจริง (คัน) :</label>
        <input type="number" id="swal-received" 
          class="form-control w-auto text-center" 
          style="max-width: 120px;"
          value="${countOrder}" min="0">
      </div>
    `,
    showCancelButton: true,
    confirmButtonColor: '#6c5ffc',
    cancelButtonColor: '#d33',
    confirmButtonText: 'ยืนยัน',
    cancelButtonText: 'ยกเลิก',
    preConfirm: () => {
      const val = parseInt($('#swal-received').val());
      if (isNaN(val) || val < 0) {
        Swal.showValidationMessage('กรุณากรอกจำนวนที่ถูกต้อง');
        return false;
      }
      return val;
    }
  }).then(result => {
    if (!result.isConfirmed) return;

    const formData = new FormData();
    formData.append('received_order', result.value);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    $.ajax({
      url: '/car-order/approve-waiting/' + id,
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
  });
});

// ไม่อนุมัติ waiting (stock/auction)
$(document).on('click', '.btnRejectWaiting', function () {
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
    if (!result.isConfirmed) return;

    const formData = new FormData();
    formData.append('reason', result.value);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    $.ajax({
      url: '/car-order/reject-waiting/' + id,
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      beforeSend: function () {
        Swal.fire({
          title: 'กำลังดำเนินการ...',
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
          text: xhr.responseJSON?.message || 'ไม่สามารถดำเนินการได้'
        });
      }
    });
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

// edit approve waiting car-order
// blur focus editApproveWaitingOrder
$(document).on('hide.bs.modal', '.editApproveWaitingOrder', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

// แก้ไขวันที่ waiting approve
$(document).on('click', '.btnApproveWaitingEdit', function () {
  const id = $(this).data('id');
  const $btn = $(this);

  $.get('/car-order/edit-approve-waiting/' + id, function (html) {
    $('.editApproveWaitingOrderModal').html(html);
    const $modal = $('.editApproveWaitingOrder');
    $modal.modal('show');

    $modal
      .find('.btnUpdateApproveWaitingOrder')
      .off('click')
      .on('click', function (e) {
        e.preventDefault();

        const form = $modal.find('form')[0];
        if (!form.checkValidity()) {
          form.reportValidity();
          return;
        }

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
              didOpen: () => Swal.showLoading(),
              allowOutsideClick: false
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
      toggleFpField($modal);
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

// รับทราบรายการไม่อนุมัติ (soft delete ออกจากหน้าผลการอนุมัติ)
function acknowledgeReject(url) {
  Swal.fire({
    title: 'รับทราบรายการนี้?',
    text: 'รายการที่ไม่อนุมัติจะถูกนำออกจากหน้านี้',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#6c5ffc',
    cancelButtonColor: '#d33',
    confirmButtonText: 'รับทราบ',
    cancelButtonText: 'ยกเลิก'
  }).then(result => {
    if (!result.isConfirmed) return;

    $.ajax({
      url: url,
      type: 'POST',
      data: {
        _method: 'DELETE',
        _token: $('meta[name="csrf-token"]').attr('content')
      },
      beforeSend: function () {
        $('.editApproveOrder, .editApproveWaitingOrder').modal('hide');
        Swal.fire({
          title: 'กำลังดำเนินการ...',
          allowOutsideClick: false,
          didOpen: () => Swal.showLoading()
        });
      },
      success: function (res) {
        Swal.fire({ icon: 'success', title: 'สำเร็จ!', text: res.message, timer: 2000, showConfirmButton: true });
        approveOrderTable.ajax.reload(null, false);
      },
      error: function (xhr) {
        Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด!', text: xhr.responseJSON?.message || 'ไม่สามารถดำเนินการได้' });
      }
    });
  });
}

$(document).on('click', '.btnAcknowledgeReject', function () {
  acknowledgeReject('/car-order/acknowledge-reject/' + $(this).data('id'));
});

$(document).on('click', '.btnAcknowledgeRejectWaiting', function () {
  acknowledgeReject('/car-order/acknowledge-reject-waiting/' + $(this).data('id'));
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

// คำนวณค่า WS จากราคาทุน (DNP) ถอด VAT — ดอกลอย 9%/ปี ตามจำนวนวันของเดือนปัจจุบัน ปัดเป็นหลักร้อย
// เช่น 1548 → 1500, 1559 → 1600 (ไม่ได้ดึงค่า ws จากตารางราคารถแล้ว)
function calcOrderWs(dnp) {
  const val = parseFloat((String(dnp ?? '')).replace(/,/g, '')) || 0;
  if (!val) return '';

  const dnpExVat = val - (val * 7) / 107;
  const now = new Date();
  const daysInMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate();
  const ws = ((dnpExVat * 0.09) / 365) * daysInMonth;
  const wsRounded = Math.round(ws / 100) * 100;

  return wsRounded.toLocaleString();
}

// price list car : โหลด type_color และ year จาก TbPricelistCar
function clearPricelistFields() {
  $('#pricelist_color').prop('disabled', true).empty().append('<option value="">-- เลือก --</option>');
  $('#pricelist_year').prop('disabled', true).empty().append('<option value="">-- เลือกปี --</option>');
  $('#option').val('');
  $('#car_DNP').val('');
  $('#car_MSRP').val('');
  $('#RI').val('');
  $('#WS').val('');
}

function loadPricelistData() {
  const subModelId = $('#subModel_id').val();
  const year = $('#pricelist_year').val();
  const typeColor = $('#pricelist_color').val() || '';

  if (!subModelId || !year) return;

  $.get('/api/car-order/pricelist-data', { sub_model_id: subModelId, year: year, color: typeColor }, function (data) {
    if (data) {
      $('#option').val(data.option ?? '');
      $('#car_DNP').val(data.dnp ? Number(data.dnp).toLocaleString() : '');
      $('#car_MSRP').val(data.msrp ? Number(data.msrp).toLocaleString() : '');
      $('#RI').val(data.ri ? Number(data.ri).toLocaleString() : '');
      $('#WS').val(calcOrderWs(data.dnp));
    } else {
      $('#option').val('');
      $('#car_DNP').val('');
      $('#car_MSRP').val('');
      $('#RI').val('');
      $('#WS').val('');
    }
  });
}

$(document).on('change', '#subModel_id', function () {
  clearPricelistFields();

  const subModelId = $(this).val();
  if (!subModelId) return;

  $.get('/api/car-order/pricelist-options', { sub_model_id: subModelId }, function (res) {
    if (!res.data || !res.data.length) return;

    if (res.type === 'color_year') {
      const colors = [...new Set(res.data.map(r => r.color))];
      const $colorSel = $('#pricelist_color');
      $colorSel.empty().append('<option value="">-- เลือก --</option>');
      colors.forEach(c => $colorSel.append(`<option value="${c}">${c}</option>`));
      $colorSel.prop('disabled', false).data('pricelistRows', res.data);
    } else {
      const $yearSel = $('#pricelist_year');
      $yearSel.empty().append('<option value="">-- เลือกปี --</option>');
      res.data.forEach(r => $yearSel.append(`<option value="${r.year}">${r.year}</option>`));
      $yearSel.prop('disabled', false);
    }
  });
});

$(document).on('change', '#pricelist_color', function () {
  const selectedColor = $(this).val();
  const rows = $(this).data('pricelistRows') || [];
  const $yearSel = $('#pricelist_year');

  $yearSel.prop('disabled', true).empty().append('<option value="">-- เลือกปี --</option>');
  $('#option').val('');
  $('#car_DNP').val('');
  $('#car_MSRP').val('');
  $('#RI').val('');
  $('#WS').val('');

  if (!selectedColor) return;

  const years = [...new Set(rows.filter(r => r.color === selectedColor).map(r => r.year))];
  years.forEach(y => $yearSel.append(`<option value="${y}">${y}</option>`));
  $yearSel.prop('disabled', false);
});

$(document).on('change', '#pricelist_year', function () {
  loadPricelistData();
});

//view report car order stock
document.addEventListener('DOMContentLoaded', function () {
  const modalEl = document.querySelector('.viewExportCarOrderStock');
  if (!modalEl) return;

  const modal = new bootstrap.Modal(modalEl);
  modal.show();

  // ปิด modal แล้วกลับหน้าก่อนหน้า
  modalEl.addEventListener('hidden.bs.modal', function () {
    window.history.back();
  });
});