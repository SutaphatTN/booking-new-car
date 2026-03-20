$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});

//view : table finance
let licenseTable;

$(document).ready(function () {
  if ($.fn.DataTable.isDataTable('.licenseTable')) {
    $('.licenseTable').DataTable().destroy();
  }

  licenseTable = $('.licenseTable').DataTable({
    ajax: '/license/list',
    columns: [
      { data: 'No' },
      { data: 'red' },
      { data: 'FullName' },
      { data: 'sale' },
      { data: 'date' },
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

// blur focus viewLicense
$(document).on('hide.bs.modal', '.viewLicense', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

//view-more license
$(document).on('click', '.btnViewLicense', function () {
  const id = $(this).data('id');

  $.get('/license/' + id + '/view-more', function (html) {
    $('.viewMoreLicenseModel').html(html);
    $('.viewLicense').modal('show');
  });
});

// blur focus editLicense
$(document).on('hide.bs.modal', '.editLicense', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

//edit : license
$(document).on('click', '.btnEditLicense', function () {
  const id = $(this).data('id');
  const $btn = $(this);
  const form = $btn.closest('form')[0];

  $.get('/license/' + id + '/edit', function (html) {
    $('.editLicenseModel').html(html);
    const $modal = $('.editLicense');

    $modal.modal('show');

    $modal
      .find('.btnUpdateLicense')
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

            licenseTable.ajax.reload(null, false);
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

//finance approve
$(document).on('click', '.btnApproveFinance', function () {
  let id = $(this).data('id');

  Swal.fire({
    title: 'ยืนยันการจ่ายเงินจริง?',
    text: 'กดแล้วจะไม่สามารถย้อนกลับได้',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'ใช่, ยืนยัน',
    cancelButtonText: 'ยกเลิก'
  }).then(result => {
    if (result.isConfirmed) {
      $.ajax({
        url: '/license/approve-finance',
        type: 'POST',
        data: { id: id },
        success: function () {
          Swal.fire({
            icon: 'success',
            title: 'อนุมัติเรียบร้อย',
            timer: 1500,
            showConfirmButton: true
          });

          licenseTable.ajax.reload(null, false);
        },
        error: function () {
          Swal.fire({
            icon: 'error',
            title: 'เกิดข้อผิดพลาด'
          });
        }
      });
    }
  });
});

$(document).on('click', '.btnExportLicenseAll', function () {
  $.get('/license/view-export-license', function (html) {
    $('.viewExportLicenseAllModel').html(html);
    $('.viewExportLicAll').modal('show');
  });
});