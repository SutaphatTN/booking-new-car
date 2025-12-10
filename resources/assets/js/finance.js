$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});

//view : table finance
let financeTable;

$(document).ready(function () {
  if ($.fn.DataTable.isDataTable('.financeTable')) {
    $('.financeTable').DataTable().destroy();
  }

  financeTable = $('.financeTable').DataTable({
    ajax: '/finance/list',
    columns: [
      { data: 'No' },
      { data: 'name' },
      { data: 'tax' },
      { data: 'year' },
      { data: 'update' },
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

//input : modal fin
$(document).on('click', '.btnInputFin', function () {
  $.get('/finance/create', function (html) {
    $('.inputFinModal').html(html);
    $('.inputFin').modal('show');
  });
});

//input : save fin
$(document).on('click', '.btnStoreFinance', function (e) {
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
      $('.inputFin').modal('hide');

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
      financeTable.ajax.reload(null, false);
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

//edit : fin
$(document).on('click', '.btnEditFin', function () {
  const id = $(this).data('id');
  const $btn = $(this);
  const form = $btn.closest('form')[0];

  $.get('/finance/' + id + '/edit', function (html) {
    $('.editFinModal').html(html);
    const $modal = $('.editFin');

    $modal.modal('show');

    $modal
      .find('.btnUpdateFinance')
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

            financeTable.ajax.reload(null, false);
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

//delete fin
$(document).on('click', '.btnDeleteFin', function () {
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
        url: '/finance/' + id,
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
            financeTable.ajax.reload(null, false);
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

//finance extra com

//view : table finance extra com
let financeExtraComTable;

$(document).ready(function () {
  if ($.fn.DataTable.isDataTable('.financeExtraComTable')) {
    $('.financeExtraComTable').DataTable().destroy();
  }

  financeExtraComTable = $('.financeExtraComTable').DataTable({
    ajax: '/finance/extra-com/list',
    columns: [
      { data: 'No' },
      { data: 'financeID' },
      { data: 'model_id' },
      { data: 'com' },
      { data: 'update' },
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

//css : format number
function parseNumber(val) {
  if (val === null || val === undefined || val === '') {
    return null;
  }
  return parseFloat(val.toString().replace(/,/g, '')) || null;
}

function formatMoney(val) {
  if (val === null || val === '') return '';
  return Number(val).toLocaleString(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
}

function formatAllMoneyInputs() {
  $('.money-input').each(function () {
    let v = parseNumber($(this).val());
    $(this).val(formatMoney(v));
  });
}

$(document).on('input', '.money-input', function () {
  let val = this.value.replace(/,/g, '');
  if (val === '' || isNaN(val)) return;

  this.value = Number(val).toLocaleString();
});

$(document).on('blur', '.money-input', function () {
  let val = parseNumber(this.value);
  this.value = formatMoney(val);
});

//input : modal fin
$(document).on('click', '.btnInputFinExtraCom', function () {
  $.get('/finance/create-extra-com', function (html) {
    $('.inputFinExtraComModal').html(html);
    $('.inputFinExtraCom').modal('show');
  });
});

//input : save fin
$(document).on('click', '.btnStoreFinanceExtraCom', function (e) {
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
      $('.inputFinExtraCom').modal('hide');

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
      financeExtraComTable.ajax.reload(null, false);
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

//edit : fin extra com
$(document).on('click', '.btnEditFinExtraCom', function () {
  const id = $(this).data('id');
  const $btn = $(this);
  const form = $btn.closest('form')[0];

  $.get('/finance/edit-extra-com/' + id, function (html) {
    $('.editFinExtraComModal').html(html);
    const $modal = $('.editFinExtraCom');

    $modal.modal('show');

    $modal
      .find('.btnUpdateFinanceExtraCom')
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

            financeExtraComTable.ajax.reload(null, false);
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

//delete fin Extra Com
$(document).on('click', '.btnDeleteFinExtraCom', function () {
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
        url: '/finance/destroy-extra-com/' + id,
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
            financeExtraComTable.ajax.reload(null, false);
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

// confirm finance
//view : confirm finance table
let confirmFNTable;

$(document).ready(function () {
  if ($.fn.DataTable.isDataTable('.confirmFNTable')) {
    $('.confirmFNTable').DataTable().destroy();
  }

  confirmFNTable = $('.confirmFNTable').DataTable({
    ajax: '/purchase-order/list-fn',
    columns: [
      { data: 'No' },
      { data: 'FullName' },
      { data: 'model' },
      { data: 'subModel' },
      { data: 'po' },
      { data: 'Action' }
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

//view-more fin confirm
$(document).on('click', '.btnViewFNConfirm', function () {
  const id = $(this).data('id');

  $.get('/purchase-order/' + id + '/view-more', function (html) {
    $('.viewMoreFinConfirmModal').html(html);
    $('.viewFinConfirm').modal('show');
  });
});

//edit : fin confirm
$(document).on('click', '.btnEditFNConfirm', function () {
  const id = $(this).data('id');
  const $btn = $(this);
  const form = $btn.closest('form')[0];

  $.get('/purchase-order/edit-fn/' + id, function (html) {
    $('.editFinConfirmModal').html(html);
    const $modal = $('.editFinConfirm');

    formatAllMoneyInputs();
    calculateComFin();
    bindTotalEvents();
    calculateTotal();

    $modal.modal('show');

    $modal
      .find('.btnUpdateFinanceConfirm')
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

            confirmFNTable.ajax.reload(null, false);
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

function calculateComFin() {
  let netPrice = parseFloat(document.getElementById('net_price').value || 0);
  let down = parseFloat(document.getElementById('down').value || 0);
  let alp = parseFloat(document.getElementById('alp').value || 0);
  let interest = parseFloat(document.getElementById('interest').value || 0) / 100;
  let type_com = parseFloat(document.getElementById('type_com').value || 0) / 100;
  let period = parseFloat(document.getElementById('period').value || 0);
  let tax = parseFloat(document.getElementById('tax').value || 0) / 100;

  let base = netPrice - down + alp;
  let per = type_com * interest * (period / 12);

  let com = (base * per) / 1.07;

  let comFin = com * 1.07 - com * tax;

  $('#com_fin').val(formatMoney(comFin));
}

function calculateTotal() {
  let excellent = parseFloat($('#excellent').val() || 0);
  let advance = parseFloat($('#advance_installment').val() || 0);
  let comFin = parseFloat($('#com_fin').val() || 0);
  let comExtra = parseFloat($('#com_extra').val() || 0);
  let comKickback = parseFloat($('#com_kickback').val() || 0);
  let comSubsidy = parseFloat($('#com_subsidy').val() || 0);

  let total = excellent - advance + comFin + comExtra + comKickback + comSubsidy;

  $('#total').val(formatMoney(total));
}

function bindTotalEvents() {
  $('#excellent, #advance_installment, #com_fin, #com_extra, #com_kickback, #com_subsidy')
    .off('input')
    .on('input', function () {
      calculateTotal();
    });
}
