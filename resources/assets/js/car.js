$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});

//view : table model-car
let carTable;

$(document).ready(function () {
  if ($.fn.DataTable.isDataTable('.carTable')) {
    $('.carTable').DataTable().destroy();
  }

  carTable = $('.carTable').DataTable({
    ajax: '/model-car/list',
    columns: [
      { data: 'No' },
      { data: 'Name_TH' },
      { data: 'Name_EN' },
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

//input : modal model-car
$(document).on('click', '.btnInputCar', function () {
  $.get('/model-car/create', function (html) {
    $('.inputCarModal').html(html);
    $('.inputCar').modal('show');
  });
});

//input : save model-car
$(document).on('click', '.btnStoreCar', function (e) {
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
      $('.inputCar').modal('hide');

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
      carTable.ajax.reload(null, false);
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

//edit : model-car
$(document).on('click', '.btnEditCar', function () {
  const id = $(this).data('id');
  const $btn = $(this);
  const form = $btn.closest('form')[0];

  $.get('/model-car/' + id + '/edit', function (html) {
    $('.editCarModal').html(html);
    const $modal = $('.editCar');

    $modal.modal('show');

    $modal
      .find('.btnUpdateCar')
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
              showConfirmButton: false
            });

            carTable.ajax.reload(null, false);
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

//delete model-car
$(document).on('click', '.btnDeleteCar', function () {
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
        url: '/model-car/' + id,
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
            carTable.ajax.reload(null, false);
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

// =====================================

//view : table sub-model-car
let subCarTable;

$(document).ready(function () {
  if ($.fn.DataTable.isDataTable('.subCarTable')) {
    $('.subCarTable').DataTable().destroy();
  }

  subCarTable = $('.subCarTable').DataTable({
    ajax: '/sub-model-car/list',
    columns: [
      { data: 'No' },
      { data: 'model_id' },
      { data: 'code' },
      { data: 'name' },
      { data: 'active', orderable: false, searchable: false },
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

//view : toggle sub car
$(document).on('change', '.status-sub-car', function () {
  const $checkbox = $(this);
  const id = $(this).data('id');
  const isChecked = $(this).is(':checked');
  const status = isChecked ? 'active' : 'inactive';

  $.ajax({
    url: '/sub-model-car/status-sub-car',
    type: 'POST',
    data: {
      id: id,
      status: status
    },
    success: function (res) {
      if (res.success) {
        console.log('✅', res.message);
      } else {
        Swal.fire({
          icon: 'warning',
          title: 'แจ้งเตือน',
          text: res.message
        });
        $checkbox.prop('checked', !isChecked);
      }
    },
    error: function (xhr) {
      let errMsg = 'เกิดข้อผิดพลาดในการอัปเดตสถานะ';
      Swal.fire({
        icon: 'error',
        title: 'เกิดข้อผิดพลาด',
        text: errMsg
      });
      $checkbox.prop('checked', !isChecked);
    }
  });
});

//view-more sub-car
$(document).on('click', '.btnViewSubCar', function () {
  const id = $(this).data('id');

  $.get('/sub-model-car/' + id + '/view-more', function (html) {
    $('.viewMoreSubCarModal').html(html);
    $('.viewSubCar').modal('show');
  });
});

//input : modal sub-model-car
$(document).on('click', '.btnInputSubCar', function () {
  $.get('/sub-model-car/create', function (html) {
    $('.inputSubCarModal').html(html);
    $('.inputSubCar').modal('show');
  });
});

//input : save sub-model-car
$(document).on('click', '.btnStoreSubCar', function (e) {
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
      $('.inputSubCar').modal('hide');

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
      subCarTable.ajax.reload(null, false);
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

//edit : sub-model-car
$(document).on('click', '.btnEditSubCar', function () {
  const id = $(this).data('id');
  const $btn = $(this);
  const form = $btn.closest('form')[0];

  $.get('/sub-model-car/' + id + '/edit', function (html) {
    $('.editSubCarModal').html(html);
    const $modal = $('.editSubCar');

    $modal.modal('show');

    $modal
      .find('.btnUpdateSubCar')
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
              showConfirmButton: false
            });

            subCarTable.ajax.reload(null, false);
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

//delete sub-model-car
$(document).on('click', '.btnDeleteSubCar', function () {
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
        url: '/sub-model-car/' + id,
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
            subCarTable.ajax.reload(null, false);
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
