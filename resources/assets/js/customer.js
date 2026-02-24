$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});

//view

//view : table
let customerTable;

$(document).ready(function () {
  if ($.fn.DataTable.isDataTable('#customerTable')) {
    $('#customerTable').DataTable().destroy();
  }

  customerTable = $('#customerTable').DataTable({
    ajax: '/customer/list',
    columns: [
      { data: 'No' },
      { data: 'FullName' },
      { data: 'IDNumber' },
      {
        data: 'Mobilephone',
        render: function (data, type, row) {
          if (type === 'display') {
            return data;
          }
          if (type === 'filter') {
            return row.MobilephoneRaw;
          }
          return data;
        }
      },
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

// blur focus viewCust
$(document).on('hide.bs.modal', '.viewCust', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

//view : modal view-more
$(document).on('click', '.btnViewCust', function () {
  const id = $(this).data('id');

  $.get('/customer/' + id + '/view-more', function (html) {
    $('#viewMore').html(html);
    $('.viewCust').modal('show');
  });
});

// blur focus editCust
$(document).on('hide.bs.modal', '.editCust', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

//view : modal edit
$(document).on('click', '.btnEditCust', function () {
  const id = $(this).data('id');
  const $btn = $(this);
  const form = $btn.closest('form')[0];

  $.get('/customer/' + id + '/edit', function (html) {
    $('#editCust').html(html);
    const $modal = $('.editCust');

    $modal.modal('show');

    $modal.on('shown.bs.modal', function () {
      bindCustomerFormEvents(this);
    });

    $modal
      .find('#btnUpdateCustomer')
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

            customerTable.ajax.reload(null, false);
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

//view : delete customer
$(document).on('click', '.btnDeleteCust', function () {
  let id = $(this).data('id');

  Swal.fire({
    title: 'คุณแน่ใจหรือไม่?',
    text: 'คุณต้องการลบรายชื่อลูกค้าคนนี้ใช่หรือไม่?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#6c5ffc',
    cancelButtonColor: '#d33',
    confirmButtonText: 'ใช่, ลบเลย!',
    cancelButtonText: 'ยกเลิก'
  }).then(result => {
    if (result.isConfirmed) {
      $.ajax({
        url: '/customer/' + id,
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

            customerTable.ajax.reload(null, false);
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

//view more and edit
//view more and edit : format id number, mobile, address sync
function bindCustomerFormEvents(container = document) {
  const idInput = container.querySelector('#IDNumber');
  const phone1 = container.querySelector('#Mobilephone1');
  const phone2 = container.querySelector('#Mobilephone2');

  if (idInput) {
    idInput.addEventListener('input', e => (e.target.value = formatIDCard(e.target.value)));
  }
  if (phone1) {
    phone1.addEventListener('input', e => (e.target.value = formatPhone(e.target.value)));
  }
  if (phone2) {
    phone2.addEventListener('input', e => (e.target.value = formatPhone(e.target.value)));
  }

  // postal_code
  function allowOnlyNumbers(input) {
    input.addEventListener('input', e => {
      e.target.value = e.target.value.replace(/\D/g, '');
    });
  }

  const postalCurrent = container.querySelector('[name="current_postal_code"]');
  const postalDoc = container.querySelector('[name="doc_postal_code"]');

  if (postalCurrent) allowOnlyNumbers(postalCurrent);
  if (postalDoc) allowOnlyNumbers(postalDoc);

  const checkbox = container.querySelector('#sameAsCurrent');
  if (checkbox) {
    checkbox.addEventListener('change', function () {
      const fields = [
        'house_number',
        'group',
        'village',
        'alley',
        'road',
        'subdistrict',
        'district',
        'province',
        'postal_code'
      ];

      fields.forEach(field => {
        const currentField = container.querySelector(`[name="current_${field}"]`);
        const docField = container.querySelector(`[name="doc_${field}"]`);

        if (currentField && docField) {
          if (checkbox.checked) {
            docField.value = currentField.value;
            docField.readOnly = true;
          } else {
            docField.readOnly = false;
            docField.value = '';
          }
        }
      });
    });

    const currentFields = container.querySelectorAll('[name^="current_"]');
    currentFields.forEach(input => {
      input.addEventListener('input', () => {
        if (checkbox.checked) {
          const targetName = input.name.replace('current_', 'doc_');
          const docField = container.querySelector(`[name="${targetName}"]`);
          if (docField) docField.value = input.value;
        }
      });
    });

    if (checkbox.checked) {
      checkbox.dispatchEvent(new Event('change'));
    }
  }
}

//view more and edit : format id number
function formatIDCard(value) {
  const digits = value.replace(/\D/g, '').substring(0, 13);
  const parts = [];
  if (digits.length > 0) parts.push(digits.substring(0, 1));
  if (digits.length > 1) parts.push(digits.substring(1, 5));
  if (digits.length > 5) parts.push(digits.substring(5, 10));
  if (digits.length > 10) parts.push(digits.substring(10, 12));
  if (digits.length > 12) parts.push(digits.substring(12, 13));
  return parts.join('-');
}

//view more and edit : format phone
function formatPhone(value) {
  const digits = value.replace(/\D/g, '').substring(0, 10);
  const parts = [];
  if (digits.length > 0) parts.push(digits.substring(0, 3));
  if (digits.length > 3) parts.push(digits.substring(3, 7));
  if (digits.length > 7) parts.push(digits.substring(7, 10));
  return parts.join('-');
}

document.addEventListener('shown.bs.modal', e => {
  bindCustomerFormEvents(e.target);
});

//input + view more
document.addEventListener('DOMContentLoaded', function () {
  //view more and edit : call
  bindCustomerFormEvents(document);

  // input : save customer
  $(document).on('click', '.btnSaveCustomer', function (e) {
    e.preventDefault();

    const $btn = $(this);
    const form = $('#customerInputForm')[0];

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
          title: 'สำเร็จ',
          text: res.message,
          timer: 2000,
          showConfirmButton: true
        });

        setTimeout(() => {
          window.location.href = '/customer';
        }, 1000);
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
        $btn.prop('disabled', false).text('บันทึก');
      }
    });
  });
});
