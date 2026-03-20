$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});

//view : table vehicleTable
let vehicleTable;

$(document).ready(function () {
  if ($.fn.DataTable.isDataTable('.vehicleTable')) {
    $('.vehicleTable').DataTable().destroy();
  }

  vehicleTable = $('.vehicleTable').DataTable({
    ajax: {
      url: '/vehicle/list',
      data: function (d) {
        d.status = $('#withdrawalStatusFilter').val();
      }
    },
    columns: [
      { data: 'No' },
      { data: 'FullName' },
      { data: 'vin' },
      { data: 'province' },
      { data: 'withdrawn_cost' },
      { data: 'receipt_total' },
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

$('#withdrawalStatusFilter').on('change', function () {
  vehicleTable.ajax.reload();
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

// blur focus viewVehicle
$(document).on('hide.bs.modal', '.viewVehicle', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

//view-more vehicle
$(document).on('click', '.btnViewVehicle', function () {
  const id = $(this).data('id');

  $.get('/vehicle/' + id + '/view-more', function (html) {
    $('.viewMoreVehicleModel').html(html);
    $('.viewVehicle').modal('show');
  });
});

// update vehicle
$(document).on('blur', '.input-vehicle', function () {
  let val = $(this).val().replace(/,/g, '');
  let SaleID = $(this).data('sale-id');
  let type = $(this).data('type');

  if ($(this).data('old') == val) return;
  $(this).data('old', val);

  let data = { SaleID };

  if (type === 'withdrawal') {
    data.withdrawal_total = val;
  } else {
    data.receipt_total = val;
  }

  $.ajax({
    url: '/vehicle/update-vehicle',
    method: 'POST',
    data: data
  });
});

// blur focus editVehicle
$(document).on('hide.bs.modal', '.editVehicle', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

//edit : vehicle
$(document).on('click', '.btnEditVehicle', function () {
  const id = $(this).data('id');
  const $btn = $(this);
  const form = $btn.closest('form')[0];

  $.get('/vehicle/' + id + '/edit', function (html) {
    $('.editVehicleModel').html(html);
    const $modal = $('.editVehicle');

    $modal.modal('show');

    $modal
      .find('.btnUpdateVehicle')
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

            vehicleTable.ajax.reload(null, false);
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

//withdrawal pending
// blur focus viewWithdrawal
$(document).on('hide.bs.modal', '.viewWithdrawal', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

//view-more withdrawal-pending
$(document).on('click', '.btnViewWithdrawal', function () {
  $.get('/vehicle/withdrawal-pending', function (html) {
    $('.viewWithdrawalModel').html(html);
    $('.viewWithdrawal').modal('show');
  });
});

$(document).on('click', '.btnConfirmWithdrawal', function () {
  let items = [];

  $('.checkItem:checked').each(function () {
    let row = $(this).closest('tr');

    let check = row.find('.withdrawal-check').val().replace(/,/g, '');
    let channel = row.find('.withdrawal-channel').val().replace(/,/g, '');
    let receipt = row.find('.withdrawal-bill').val().replace(/,/g, '');
    let total = row.find('.withdrawal-total').val().replace(/,/g, '');

    let isComplete = check > 0 && channel > 0 && receipt > 0 && total > 0;

    if (!isComplete) return;

    items.push({
      id: $(this).val(),
      check: check,
      channel: channel,
      receipt: receipt,
      total: total
    });
  });

  if (items.length === 0) {
    $('.viewWithdrawal')
      .one('hidden.bs.modal', function () {
        Swal.fire({
          icon: 'warning',
          title: 'กรุณาเลือกข้อมูล'
        }).then(() => {
          $('.viewWithdrawal').modal('show');
        });
      })
      .modal('hide');
    return;
  }

  $('.viewWithdrawal').modal('hide');

  setTimeout(() => {
    Swal.fire({
      title: 'ยืนยันการส่งเบิก',
      text: 'คุณต้องการส่งเบิกใช่ไหม?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#6c5ffc',
      cancelButtonColor: '#d33',
      confirmButtonText: 'ใช่, ส่งเบิก',
      cancelButtonText: 'ยกเลิก'
    }).then(result => {
      if (result.isConfirmed) {
        Swal.fire({
          title: 'กำลังดำเนินการ...',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        $.post('/vehicle/confirm-withdrawal', { items: items }, function (res) {
          Swal.fire({
            icon: 'success',
            title: 'สำเร็จ',
            text: 'ส่งเบิกเรียบร้อย',
            timer: 1500,
            showConfirmButton: true
          });

          vehicleTable.ajax.reload();

          let ids = items.map(i => i.id);
          window.open('/vehicle/export-pdf?ids=' + ids.join(','), '_blank');
        });
      } else {
        $('.viewWithdrawal').modal('show');
      }
    });
  }, 300);
});

//เช็คกรอกข้อมูลครบ
function checkWithdrawalRow(row) {
  let check = row.find('.withdrawal-check').val().replace(/,/g, '');
  let channel = row.find('.withdrawal-channel').val().replace(/,/g, '');
  let receipt = row.find('.withdrawal-bill').val().replace(/,/g, '');
  let total = row.find('.withdrawal-total').val().replace(/,/g, '');

  let isComplete = check > 0 && channel > 0 && receipt > 0 && total > 0;

  let checkbox = row.find('.checkItem');

  if (isComplete) {
    checkbox.show().prop('disabled', false);
  } else {
    checkbox.prop('checked', false).prop('disabled', true).hide();
  }
}

//คำนวณรวม ส่งเบิก
$(document).on('input', '.calc-input', function () {
  let row = $(this).closest('tr');

  let check = parseFloat(row.find('.withdrawal-check').val().replace(/,/g, '')) || 0;
  let channel = parseFloat(row.find('.withdrawal-channel').val().replace(/,/g, '')) || 0;
  let receipt = parseFloat(row.find('.withdrawal-bill').val().replace(/,/g, '')) || 0;

  let total = check + channel + receipt;

  row
    .find('.withdrawal-total')
    .val(total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

  checkWithdrawalRow(row);
});

$(document).on('change', '#checkAll', function () {
  let isChecked = this.checked;

  $('.checkItem').each(function () {
    let row = $(this).closest('tr');

    checkWithdrawalRow(row);

    if ($(this).is(':visible')) {
      $(this).prop('checked', isChecked);
    } else {
      $(this).prop('checked', false);
    }
  });
});

$(document).on('shown.bs.modal', '.viewWithdrawal', function () {
  // withdrawal
  $('#tab-withdrawal .checkItem').hide();
  $('#tab-withdrawal tbody tr').each(function () {
    checkWithdrawalRow($(this));
  });

  // clear
  $('#tab-clear .checkItemClear').hide();
  $('#tab-clear tbody tr').each(function () {
    checkClearRow($(this));
  });
});

//clear
$(document).on('click', '.btnConfirmClear', function () {
  let items = [];

  $('.checkItemClear:checked').each(function () {
    let row = $(this).closest('tr');

    let check = row.find('.receipt-check').val().replace(/,/g, '');
    let channel = row.find('.receipt-channel').val().replace(/,/g, '');
    let receipt = row.find('.receipt-bill').val().replace(/,/g, '');
    let total = row.find('.receipt-total').val().replace(/,/g, '');

    let isComplete = check > 0 && channel > 0 && receipt > 0 && total > 0;

    if (!isComplete) return;

    items.push({
      id: $(this).val(),
      check: check,
      channel: channel,
      receipt: receipt,
      total: total
    });
  });

  if (items.length === 0) {
    $('.viewWithdrawal')
      .one('hidden.bs.modal', function () {
        Swal.fire({
          icon: 'warning',
          title: 'กรุณาเลือกข้อมูล'
        }).then(() => {
          $('.viewWithdrawal').modal('show');
        });
      })
      .modal('hide');

    return;
  }

  $('.viewWithdrawal').modal('hide');

  setTimeout(() => {
    Swal.fire({
      title: 'ยืนยันการส่งเคลียร์',
      text: 'คุณต้องการส่งเคลียร์ใช่ไหม?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#6c5ffc',
      cancelButtonColor: '#d33',
      confirmButtonText: 'ใช่, ส่งเคลียร์',
      cancelButtonText: 'ยกเลิก'
    }).then(result => {
      if (result.isConfirmed) {
        Swal.fire({
          title: 'กำลังดำเนินการ...',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        $.post('/vehicle/confirm-clear', { items: items }, function (res) {
          Swal.fire({
            icon: 'success',
            title: 'สำเร็จ',
            text: 'ส่งเคลียร์เรียบร้อย',
            timer: 1500,
            showConfirmButton: true
          });

          vehicleTable.ajax.reload();

          window.open('/vehicle/export-clear-pdf?ids=' + items.map(i => i.id).join(','), '_blank');
        });
      } else {
        $('.viewWithdrawal').modal('show');
      }
    });
  }, 300);
});

//เช็คครบไหม
function checkClearRow(row) {
  let check = row.find('.receipt-check').val().replace(/,/g, '');
  let channel = row.find('.receipt-channel').val().replace(/,/g, '');
  let receipt = row.find('.receipt-bill').val().replace(/,/g, '');
  let total = row.find('.receipt-total').val().replace(/,/g, '');

  let isComplete = check > 0 && channel > 0 && receipt > 0 && total > 0;

  let checkbox = row.find('.checkItemClear');

  if (isComplete) {
    checkbox.show().prop('disabled', false);
  } else {
    checkbox.prop('checked', false).prop('disabled', true).hide();
  }
}

//คำนวณรวม ส่งเคลียร์
$(document).on('input', '#tab-clear .calc-clear', function () {
  let row = $(this).closest('tr');

  let check = parseFloat(row.find('.receipt-check').val().replace(/,/g, '')) || 0;
  let channel = parseFloat(row.find('.receipt-channel').val().replace(/,/g, '')) || 0;
  let receipt = parseFloat(row.find('.receipt-bill').val().replace(/,/g, '')) || 0;

  let total = check + channel + receipt;

  row
    .find('.receipt-total')
    .val(total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

  checkClearRow(row);
});

$(document).on('change', '#checkAllClear', function () {
  let isChecked = this.checked;

  $('#tab-clear .checkItemClear').each(function () {
    let row = $(this).closest('tr');

    checkClearRow(row);

    if ($(this).is(':visible')) {
      $(this).prop('checked', isChecked);
    } else {
      $(this).prop('checked', false);
    }
  });
});

//export
// blur focus viewExportVH
$(document).on('hide.bs.modal', '.viewExportVH', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

$(document).on('click', '.btnViewExportVehicle', function () {
  $.get('/vehicle/view-export-vehicle', function (html) {
    $('.viewExportVehicleModel').html(html);
    $('.viewExportVH').modal('show');
  });
});