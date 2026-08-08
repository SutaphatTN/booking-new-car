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
      { data: 'initials' },
      { data: 'over_budget' },
      { data: 'money_min' },
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

// blur focus inputCar
$(document).on('hide.bs.modal', '.inputCar', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
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

// blur focus editCar
$(document).on('hide.bs.modal', '.editCar', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
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
              showConfirmButton: true
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
      { data: 'name' },
      { data: 'detail' },
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

// blur focus viewSubCar
$(document).on('hide.bs.modal', '.viewSubCar', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

//view-more sub-car
$(document).on('click', '.btnViewSubCar', function () {
  const id = $(this).data('id');

  $.get('/sub-model-car/' + id + '/view-more', function (html) {
    $('.viewMoreSubCarModal').html(html);
    $('.viewSubCar').modal('show');
  });
});

// blur focus inputSubCar
$(document).on('hide.bs.modal', '.inputSubCar', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

//input : modal sub-model-car
$(document).on('click', '.btnInputSubCar', function () {
  $.get(window.routeSubModelCreate, function (html) {
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

// blur focus editSubCar
$(document).on('hide.bs.modal', '.editSubCar', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

//edit : sub-model-car
$(document).on('click', '.btnEditSubCar', function () {
  const id = $(this).data('id');
  const $btn = $(this);
  const form = $btn.closest('form')[0];

  const url = window.routeSubModelEdit.replace(':id', id);

  $.get(url, function (html) {
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
              showConfirmButton: true
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

//view : table price list car
let pricelistCarTable;
const userBrand = parseInt($('#userBrand').val());
const hide = [2, 3].includes(userBrand);

$(document).ready(function () {
  if ($.fn.DataTable.isDataTable('.pricelistCarTable')) {
    $('.pricelistCarTable').DataTable().destroy();
  }

  pricelistCarTable = $('.pricelistCarTable').DataTable({
    ajax: '/pricelist-car/list',
    columns: [
      { data: 'No' },
      { data: 'car' },
      { data: 'option', visible: !hide },
      { data: 'year' },
      { data: 'color', visible: !hide },
      { data: 'dnp' },
      { data: 'msrp' },
      { data: 'dm', visible: !hide },
      { data: 'ri', visible: !hide },
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

// blur focus inputPricelistCar
$(document).on('hide.bs.modal', '.inputPricelistCar', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

//input : modal pricelist-car
$(document).on('click', '.btnInputPricelistCar', function () {
  $.get('/pricelist-car/create', function (html) {
    $('.inputPricelistCarModal').html(html);
    $('.inputPricelistCar').modal('show');
  });
});

//input : get subModel
$(document).on('change', '#pl_model_id', function () {
  const modelId = $(this).val();
  const $subSelect = $('#pl_subModel_id');

  $subSelect.prop('disabled', true).empty().append('<option value="">-- เลือกรุ่นรถย่อย --</option>');

  if (!modelId) {
    pricelistCarTable.ajax.reload();
    return;
  }

  $.get('/api/pricelist-car/sub-model/' + modelId, function (data) {
    $.each(data, function (i, item) {
      let text = item.detail ? `${item.detail} - ${item.name}` : item.name;

      $subSelect.append($('<option>', { value: item.id, text: text }));
    });
    $subSelect.prop('disabled', false);
  });
});

// คำนวณค่า WS อัตโนมัติจากราคาทุน (DNP) ถอด VAT — ดอกลอย 9%/ปี ตามจำนวนวันของเดือนปัจจุบัน (เติมเป็นค่าตั้งต้น แก้ไขเองได้)
function calcPricelistWs(dnpSelector, wsSelector) {
  const dnp = parseFloat(($(dnpSelector).val() || '').replace(/,/g, '')) || 0;
  const $ws = $(wsSelector);

  if (!dnp) {
    $ws.val('');
    return;
  }

  const dnpExVat = dnp - (dnp * 7) / 107;
  const now = new Date();
  const daysInMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate();
  const ws = ((dnpExVat * 0.09) / 365) * daysInMonth;
  // ปัดเป็นเลขเต็มหลักร้อย เช่น 1548 → 1500, 1559 → 1600
  const wsRounded = Math.round(ws / 100) * 100;

  $ws.val(wsRounded.toLocaleString());
}

// เติมค่า WS ให้อัตโนมัติเมื่อกรอก/แก้ราคาทุน (DNP) ทั้งหน้าเพิ่มและหน้าแก้ไข
$(document).on('input', '#pl_dnp', function () {
  calcPricelistWs('#pl_dnp', '#pl_ws');
});

$(document).on('input', '#edit_pl_dnp', function () {
  calcPricelistWs('#edit_pl_dnp', '#edit_pl_ws');
});

// escape ข้อความจากเซิร์ฟเวอร์ก่อนยัดลง Swal (ใช้ html เพื่อทำตารางเทียบค่า)
function escapeHtml(text) {
  return $('<div>').text(text == null ? '' : text).html();
}

// ถามยืนยันเมื่อข้อมูลราคารถซ้ำ — โชว์ค่าเดิมเทียบค่าใหม่ ให้เลือกว่าจะทับของเดิมไหม
function confirmOverwritePricelistCar(res, onConfirm, onCancel) {
  const info = res.info || {};
  const rows = (res.compare || [])
    .map(
      r => `<tr${r.changed ? ' style="background:#fff8e1"' : ''}>
              <td class="text-start">${escapeHtml(r.label)}</td>
              <td class="text-end text-muted">${escapeHtml(r.old)}</td>
              <td class="text-end fw-bold${r.changed ? ' text-danger' : ''}">${escapeHtml(r.new)}</td>
            </tr>`
    )
    .join('');

  Swal.fire({
    icon: 'warning',
    title: 'ข้อมูลนี้มีอยู่แล้ว',
    html: `
      <div class="text-start mb-2">
        <div>รุ่นหลัก : <b>${escapeHtml(info.model)}</b></div>
        <div>รุ่นย่อย : <b>${escapeHtml(info.subModel)}</b></div>
        <div>ปี : <b>${escapeHtml(info.year)}</b>${info.color ? ` &nbsp; ประเภทสี : <b>${escapeHtml(info.color)}</b>` : ''}</div>
      </div>
      <table class="table table-sm table-bordered mb-2">
        <thead><tr><th class="text-start">รายการ</th><th class="text-end">ค่าเดิม</th><th class="text-end">ค่าใหม่</th></tr></thead>
        <tbody>${rows}</tbody>
      </table>
      <div class="text-start small text-muted">กด "ทับข้อมูลเดิม" เพื่อแก้ไขรายการเดิมด้วยค่าใหม่ (ไม่สร้างรายการซ้ำ)</div>
    `,
    width: 600,
    showCancelButton: true,
    confirmButtonText: 'ทับข้อมูลเดิม',
    cancelButtonText: 'ยกเลิก',
    confirmButtonColor: '#6c5ffc',
    cancelButtonColor: '#d33'
  }).then(result => {
    if (result.isConfirmed) {
      onConfirm();
    } else {
      onCancel();
    }
  });
}

function submitPricelistCar($btn, form, confirmOverwrite) {
  const formData = new FormData(form);
  if (confirmOverwrite) {
    formData.append('confirm_overwrite', 1);
  }

  $.ajax({
    url: $(form).attr('action'),
    type: 'POST',
    data: formData,
    contentType: false,
    processData: false,
    beforeSend: function () {
      $('.inputPricelistCar').modal('hide');

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

      pricelistCarTable.ajax.reload(null, false);
    },
    error: function (xhr) {
      // ซ้ำกับข้อมูลเดิม — ถามก่อนว่าจะทับไหม ถ้ายกเลิกให้กลับไปที่ฟอร์มเดิม (ค่าที่กรอกไว้ยังอยู่)
      if (xhr.status === 409 && xhr.responseJSON && xhr.responseJSON.duplicate) {
        confirmOverwritePricelistCar(
          xhr.responseJSON,
          () => submitPricelistCar($btn, form, true),
          () => $('.inputPricelistCar').modal('show')
        );
        return;
      }

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
}

//input : save pricelist-car
$(document).on('click', '.btnStorePricelistCar', function (e) {
  e.preventDefault();

  const $btn = $(this);
  const form = $btn.closest('form')[0];
  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }

  submitPricelistCar($btn, form, false);
});

// blur focus editPricelistCar
$(document).on('hide.bs.modal', '.editPricelistCar', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

//edit : modal pricelist-car
$(document).on('click', '.btnEditPricelistCar', function () {
  const id = $(this).data('id');
  const $btn = $(this);

  $.get('/pricelist-car/' + id + '/edit', function (html) {
    $('.editPricelistCarModal').html(html);
    const $modal = $('.editPricelistCar');
    $modal.modal('show');

    //โหลดรุ่นรถย่อยเมื่อเปลี่ยนรุ่นหลัก
    $modal
      .find('#edit_pl_model_id')
      .off('change')
      .on('change', function () {
        const modelId = $(this).val();
        const $subSelect = $modal.find('#edit_pl_subModel_id');

        $subSelect.empty().append('<option value="">-- เลือกรุ่นรถย่อย --</option>').prop('disabled', true);

        if (!modelId) return;

        $.get('/api/pricelist-car/sub-model/' + modelId, function (data) {
          $.each(data, function (i, item) {
            let text = item.detail ? `${item.detail} - ${item.name}` : item.name;

            $subSelect.append($('<option>', { value: item.id, text: text }));
          });
          $subSelect.prop('disabled', false);
        });
      });

    $modal
      .find('.btnUpdatePricelistCar')
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
              text: 'กรุณารอสักครู่',
              allowOutsideClick: false,
              didOpen: () => Swal.showLoading()
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

            pricelistCarTable.ajax.reload(null, false);
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

//delete pricelist-car
$(document).on('click', '.btnDeletePricelistCar', function () {
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
        url: '/pricelist-car/' + id,
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

            pricelistCarTable.ajax.reload(null, false);
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
