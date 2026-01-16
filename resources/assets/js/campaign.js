$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});

//view : table campaign
let campaignTable;

$(document).ready(function () {
  if ($.fn.DataTable.isDataTable('.campaignTable')) {
    $('.campaignTable').DataTable().destroy();
  }

  campaignTable = $('.campaignTable').DataTable({
    ajax: '/campaign/list',
    columns: [
      { data: 'No' },
      { data: 'model_id' },
      { data: 'name' },
      { data: 'year' },
      { data: 'campaign_type' },
      { data: 'cashSupport_final' },
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

//view : toggle
$(document).on('change', '.status-cam', function () {
  const $checkbox = $(this);
  const id = $(this).data('id');
  const isChecked = $(this).is(':checked');
  const status = isChecked ? 'active' : 'inactive';

  $.ajax({
    url: '/campaign/status-cam',
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

// blur focus viewCam
$(document).on('hide.bs.modal', '.viewCam', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

//view-more cam
$(document).on('click', '.btnViewCam', function () {
  const id = $(this).data('id');

  $.get('/campaign/' + id + '/view-more', function (html) {
    $('.viewMoreCamModal').html(html);
    $('.viewCam').modal('show');
  });
});

// blur focus inputCam
$(document).on('hide.bs.modal', '.inputCam', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

//input : modal cam
$(document).on('click', '.btnInputCam', function () {
  $.get('/campaign/create', function (html) {
    $('.inputCamModal').html(html);
    $('.inputCam').modal('show');
  });
});

//input : save cam
$(document).on('click', '.btnStoreCampaign', function (e) {
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
      $('.inputCam').modal('hide');

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

      campaignTable.ajax.reload(null, false);
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
    url: '/api/campaign/sub-model/' + modelId,
    type: 'GET',
    success: function (data) {
      console.log('data:', data);
      if (data.length > 0) {
        data.forEach(function (sub) {
          $subModelSelect.append(`<option value="${sub.id}">${sub.detail} - ${sub.name}</option>`);
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

// blur focus editCam
$(document).on('hide.bs.modal', '.editCam', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

//edit : cam
$(document).on('click', '.btnEditCam', function () {
  const id = $(this).data('id');
  const $btn = $(this);
  const form = $btn.closest('form')[0];

  $.get('/campaign/' + id + '/edit', function (html) {
    $('.editCamModal').html(html);
    const $modal = $('.editCam');

    $modal.modal('show');

    $modal
      .find('.btnUpdateCampaign')
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

            campaignTable.ajax.reload(null, false);
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

//calculate
function updateCashSupportFinal() {
  let cashSupport = parseFloat($('#cashSupport').val().replace(/,/g, '')) || 0;
  let cashSupportDeduct = parseFloat($('#cashSupport_deduct').val().replace(/,/g, '')) || 0;

  let cashSupportFinal = cashSupport - cashSupportDeduct;

  $('#cashSupport_final').val(
    cashSupportFinal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
  );
}

$(document).on('input', '#cashSupport, #cashSupport_deduct', function () {
  updateCashSupportFinal();
});

//delete cam
$(document).on('click', '.btnDeleteCam', function () {
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
        url: '/campaign/' + id,
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

            campaignTable.ajax.reload(null, false);
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

// campaign Appellation
//view : table campaign appellation
let campaignAppellationTable;

$(document).ready(function () {
  if ($.fn.DataTable.isDataTable('.campaignAppellationTable')) {
    $('.campaignAppellationTable').DataTable().destroy();
  }

  campaignAppellationTable = $('.campaignAppellationTable').DataTable({
    ajax: '/campaign/appellation/list',
    columns: [
      { data: 'No' },
      { data: 'name' },
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

// blur focus inputCamAppellation
$(document).on('hide.bs.modal', '.inputCamAppellation', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

//input : modal cam
$(document).on('click', '.btnInputCamAppellation', function () {
  $.get('/campaign/create-appellation', function (html) {
    $('.inputCamAppellationModal').html(html);
    $('.inputCamAppellation').modal('show');
  });
});

//input : save cam
$(document).on('click', '.btnStoreCampaignAppellation', function (e) {
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
      $('.inputCamAppellation').modal('hide');

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

      campaignAppellationTable.ajax.reload(null, false);
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

// blur focus editCamAppellation
$(document).on('hide.bs.modal', '.editCamAppellation', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

//edit : cam
$(document).on('click', '.btnEditCamAppellation', function () {
  const id = $(this).data('id');
  const $btn = $(this);
  const form = $btn.closest('form')[0];

  $.get('/campaign/edit-appellation/' + id, function (html) {
    $('.editCamAppellationModal').html(html);
    const $modal = $('.editCamAppellation');

    $modal.modal('show');

    $modal
      .find('.btnUpdateCampaignAppellation')
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

            campaignAppellationTable.ajax.reload(null, false);
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

//delete cam Appellation
$(document).on('click', '.btnDeleteCamAppellation', function () {
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
        url: '/campaign/destroy-appellation/' + id,
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

            campaignAppellationTable.ajax.reload(null, false);
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