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
      { data: 'red', orderable: false },
      { data: 'owner', orderable: false },
      { data: 'status', orderable: false },
      { data: 'FullName', orderable: false },
      { data: 'sale', orderable: false },
      { data: 'date', orderable: false },
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
    confirmButtonColor: '#6c5ffc',
    cancelButtonColor: '#d33',
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

// ── เพิ่มป้ายแดง (admin เท่านั้น) ──
$(document).on('click', '.btnAddPlate', function () {
  const $modal = $('.addPlateModal');
  $modal.find('#addPlateNumber').val('');
  $modal.find('#addPlateBrand').val('');
  $modal.modal('show');
});

$(document).on('click', '.btnSaveAddPlate', function () {
  const $modal = $('.addPlateModal');
  const number = $modal.find('#addPlateNumber').val().trim();
  const brand = $modal.find('#addPlateBrand').val();

  if (!number || !brand) {
    Swal.fire({ icon: 'warning', title: 'กรุณากรอกเลขป้ายและเลือกแบรนด์' });
    return;
  }

  $.ajax({
    url: '/license',
    type: 'POST',
    data: { number: number, brand: brand },
    success: function (res) {
      $modal.modal('hide');
      Swal.fire({ icon: 'success', title: 'สำเร็จ!', text: res.message, timer: 2000, showConfirmButton: true });
      licenseTable.ajax.reload(null, false);
    },
    error: function (xhr) {
      Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: xhr.responseJSON?.message || 'ไม่สามารถบันทึกได้' });
    }
  });
});

// ── ยืมป้ายแดงข้ามแบรนด์ ──
$(document).on('click', '.btnBorrowPlate', function () {
  const $modal = $('.borrowPlateModal');
  $modal.find('#ownerBrand').val('');
  $modal.find('#borrowerBrand').val('');
  $modal.find('#borrowPlateId').html('<option value="">-- เลือกแบรนด์ก่อน --</option>').prop('disabled', true);
  $modal.find('#borrowNote').val('');
  $modal.modal('show');
});

// เลือกแบรนด์เจ้าของ -> โหลดป้ายว่างของแบรนด์นั้น
$(document).on('change', '.borrowPlateModal #ownerBrand', function () {
  const brand = $(this).val();
  const $plate = $('.borrowPlateModal #borrowPlateId');

  if (!brand) {
    $plate.html('<option value="">-- เลือกแบรนด์ก่อน --</option>').prop('disabled', true);
    return;
  }

  $plate.html('<option value="">กำลังโหลด...</option>').prop('disabled', true);

  $.get('/license/loan-options', { brand: brand }, function (res) {
    let options = '<option value="">-- เลือก --</option>';
    (res.data || []).forEach(p => {
      options += `<option value="${p.id}">${p.number}</option>`;
    });
    if (!res.data || !res.data.length) {
      options = '<option value="">ไม่มีป้ายว่างให้ยืม</option>';
    }
    $plate.html(options).prop('disabled', !res.data || !res.data.length);
  });
});

$(document).on('click', '.btnSaveBorrow', function () {
  const $modal = $('.borrowPlateModal');
  const plateId = $modal.find('#borrowPlateId').val();
  const borrowDate = $modal.find('#borrowDate').val();
  const borrowerBrand = $modal.find('#borrowerBrand').val();
  const userBrand = $modal.data('user-brand');

  if (!plateId || !borrowDate || (!userBrand && !borrowerBrand)) {
    Swal.fire({ icon: 'warning', title: 'กรุณากรอกข้อมูลให้ครบ' });
    return;
  }

  // แบรนด์เจ้าของกับแบรนด์ที่ยืมต้องไม่ซ้ำกัน (กรณี admin เลือกเอง)
  if (borrowerBrand && borrowerBrand === $modal.find('#ownerBrand').val()) {
    Swal.fire({ icon: 'warning', title: 'แบรนด์ที่ยืมซ้ำกับแบรนด์เจ้าของ' });
    return;
  }

  $.ajax({
    url: '/license/loan',
    type: 'POST',
    data: {
      license_plate_id: plateId,
      borrow_date: borrowDate,
      borrower_brand: borrowerBrand,
      note: $modal.find('#borrowNote').val()
    },
    success: function (res) {
      $modal.modal('hide');
      Swal.fire({ icon: 'success', title: 'สำเร็จ!', text: res.message, timer: 2000, showConfirmButton: true });
      licenseTable.ajax.reload(null, false);
    },
    error: function (xhr) {
      Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: xhr.responseJSON?.message || 'ไม่สามารถบันทึกได้' });
    }
  });
});

// คืนป้าย -> กรอกวันที่คืน แล้วเจ้าของนำกลับไปใช้ได้
$(document).on('click', '.btnReturnPlate', function () {
  const id = $(this).data('id');
  const number = $(this).data('number');
  const borrow = $(this).data('borrow');

  // ป้ายยังผูกกับงานขายที่ยังไม่ยืนยันจ่ายเงิน -> คืนไม่ได้
  if ($(this).data('inuse') == 1) {
    Swal.fire({
      icon: 'warning',
      title: 'ยังคืนป้าย ' + number + ' ไม่ได้',
      text: 'ป้ายยังผูกกับงานขายอยู่ ต้องกดยืนยันการจ่ายเงินจริง (ปุ่มติ๊กถูก) เพื่อปลดป้ายออกจากงานขายก่อน จึงจะคืนเจ้าของได้'
    });
    return;
  }

  Swal.fire({
    title: 'คืนป้าย ' + number,
    text: 'ยืมเมื่อ ' + borrow + ' — กรอกวันที่คืนจริง',
    input: 'date',
    inputValue: new Date().toISOString().slice(0, 10),
    showCancelButton: true,
    confirmButtonColor: '#6c5ffc',
    cancelButtonColor: '#d33',
    confirmButtonText: 'ยืนยันคืนป้าย',
    cancelButtonText: 'ยกเลิก',
    inputValidator: value => (!value ? 'กรุณากรอกวันที่คืน' : undefined)
  }).then(result => {
    if (result.isConfirmed) {
      $.ajax({
        url: '/license/loan/' + id + '/return',
        type: 'POST',
        data: { return_date: result.value },
        success: function (res) {
          Swal.fire({ icon: 'success', title: 'สำเร็จ!', text: res.message, timer: 2000, showConfirmButton: true });
          licenseTable.ajax.reload(null, false);
        },
        error: function (xhr) {
          Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: xhr.responseJSON?.message || 'ไม่สามารถคืนป้ายได้' });
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
