$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});

//Accessory
//view : table accessory
let accessoryTable;

$(document).ready(function () {
  if ($.fn.DataTable.isDataTable('.accessoryTable')) {
    $('.accessoryTable').DataTable().destroy();
  }

  accessoryTable = $('.accessoryTable').DataTable({
    serverSide: true,
    processing: false,
    ajax: {
      url: '/accessory/list',
      data: function (d) {
        d.filter_model_id = $('#filterAccModel').val();
      }
    },
    columns: [
      { data: 'No', orderable: false },
      { data: 'accessoryPartner_id', orderable: false },
      { data: 'name', orderable: false },
      { data: 'model', orderable: false },
      { data: 'cost', orderable: false },
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
      processing: '',
      paginate: {
        next: 'ถัดไป',
        previous: 'ก่อนหน้า'
      }
    }
  });

  // ── ตัวกรอง : รุ่นรถ ──
  // ปุ่มล้าง + ไฮไลต์ select จะโผล่เฉพาะตอนที่เลือกรุ่นอยู่จริง
  function syncAccFilterUi() {
    const active = !!$('#filterAccModel').val();
    $('#filterAccModel').toggleClass('is-filtered', active);
    $('#btnClearAccFilter').toggleClass('is-hidden', !active);
  }

  $('#filterAccModel').on('change', function () {
    syncAccFilterUi();
    accessoryTable.ajax.reload();
  });

  $('#btnClearAccFilter').on('click', function () {
    $('#filterAccModel').val('');
    syncAccFilterUi();
    accessoryTable.ajax.reload();
  });

  accessoryTable.on('preXhr.dt', function () {
    $('#accessoryLoadingOverlay').css('display', 'flex');
  });
  accessoryTable.on('xhr.dt', function () {
    $('#accessoryLoadingOverlay').css('display', 'none');
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

// ใส่ comma หลักพันระหว่างพิมพ์ + พิมพ์จุดทศนิยมได้ (สูงสุด 2 ตำแหน่ง)
//
// ของเดิมใช้ parseFloat(value).toLocaleString() ซึ่ง "1500." → 1500 → "1,500"
// = จุดที่เพิ่งพิมพ์ถูกกินทิ้งทุกครั้ง เลยกรอกทศนิยมไม่ได้เลย
// ตัวใหม่จัดรูปแบบจาก string ตรง ๆ ไม่แปลงเป็นตัวเลขระหว่างพิมพ์ จุดจึงค้างอยู่ได้
$(document).on('input', '.money-input', function () {
  const before = this.value;
  const caret  = this.selectionStart;

  // นับตัวอักษรที่ "นับจริง" (เลข+จุด) ก่อน cursor ไว้ ใช้คืนตำแหน่ง cursor หลังใส่ comma
  const keptBefore = before.slice(0, caret).replace(/[^0-9.]/g, '').length;

  const parts   = before.replace(/[^0-9.]/g, '').split('.');
  // จุดได้ตัวเดียว ทศนิยมไม่เกิน 2 ตำแหน่ง (ตรงกับคอลัมน์ decimal(20,2) ใน DB)
  const decPart = parts.length > 1 ? parts.slice(1).join('').slice(0, 2) : null;
  const intPart = parts[0].replace(/^0+(?=\d)/, '').replace(/\B(?=(\d{3})+(?!\d))/g, ',');

  this.value = decPart !== null ? intPart + '.' + decPart : intPart;

  let seen = 0;
  let pos  = keptBefore === 0 ? 0 : this.value.length;
  for (let i = 0; i < this.value.length && keptBefore > 0; i++) {
    if (/[0-9.]/.test(this.value[i])) seen++;
    if (seen >= keptBefore) {
      pos = i + 1;
      break;
    }
  }
  this.setSelectionRange(pos, pos);
});

$(document).on('blur', '.money-input', function () {
  const value = this.value.replace(/,/g, '');
  if (value === '') return;

  const n = parseFloat(value);
  // ค่าที่ไม่เป็นตัวเลข (เช่นเหลือแค่ ".") ล้างทิ้ง ไม่งั้นส่งไป validate numeric ไม่ผ่าน
  this.value = isNaN(n)
    ? ''
    : n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
});

// ราคาทุนอะไหล่ต้อง > 0 (checkValidity ดัก required ได้ แต่ไม่ดัก 0)
// ยกเว้น 2 กรณี → บันทึก 0 ได้
//   1. ติ๊กสวิตช์ "ทุนอะไหล่เป็น 0 จริง" (is_zero_cost) — เช็คสดจาก checkbox ในฟอร์มเดียวกัน
//   2. แถวที่ค่าเดิมใน DB เป็น 0 อยู่แล้วแต่ยังไม่ติดธง (data-allow-zero จาก blade)
// เช็คก่อนยิง ajax เพื่อให้ modal ยังเปิดอยู่ ผู้ใช้ไม่เสียข้อมูลที่กรอกมา
function costSpareIsValid(form) {
  const $input = $(form).find('[name="cost_spare"]');
  const zeroConfirmed = $(form).find('input[type="checkbox"][name="is_zero_cost"]').is(':checked');
  const allowZero = zeroConfirmed || $input.data('allow-zero') == 1;
  const value = parseFloat(String($input.val()).replace(/,/g, ''));

  const ok = allowZero ? value >= 0 : value > 0;
  if (!ok) {
    Swal.fire({
      icon: 'error',
      title: 'ข้อมูลไม่ถูกต้อง',
      text: allowZero
        ? 'กรุณากรอกราคาทุนอะไหล่'
        : 'ราคาทุนอะไหล่ต้องมากกว่า 0 — ถ้าเป็น 0 จริง ให้เปิดสวิตช์ "ทุนอะไหล่เป็น 0 จริง"'
    }).then(() => $input.trigger('focus'));
    return false;
  }
  return true;
}

// ติ๊กสวิตช์ "ทุนอะไหล่เป็น 0 จริง" → เติม 0.00 ให้เลย / ติ๊กออก → ล้างค่า 0 ทิ้งเพื่อบังคับกรอกใหม่
// ใช้ delegated event เพราะโมดัลแก้ไขถูกโหลดเข้ามาทีหลังด้วย ajax
$(document).on('change', '.acc-zero-cost-toggle', function () {
  const $form = $(this).closest('form');
  const $input = $form.find('[name="cost_spare"]');
  const value = parseFloat(String($input.val()).replace(/,/g, ''));

  if (this.checked) {
    if (!$input.val() || value === 0) $input.val('0.00');
    $input.attr('placeholder', '0.00');
  } else {
    if (value === 0) $input.val('');
    $input.attr('placeholder', 'ต้องมากกว่า 0');
  }
});

//view : toggle
$(document).on('change', '.status-acc', function () {
  const $checkbox = $(this);
  const id = $(this).data('id');
  const isChecked = $(this).is(':checked');
  const status = isChecked ? 'active' : 'inactive';

  $.ajax({
    url: '/accessory/status-acc',
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

// blur focus viewAcc
$(document).on('hide.bs.modal', '.viewAcc', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

//view-more acc
$(document).on('click', '.btnViewAcc', function () {
  const id = $(this).data('id');

  $.get('/accessory/' + id + '/view-more', function (html) {
    $('.viewMoreAccModal').html(html);
    $('.viewAcc').modal('show');
  });
});

// blur focus inputAcc
$(document).on('hide.bs.modal', '.inputAcc', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

//input : modal acc
$(document).on('click', '.btnInputAcc', function () {
  $.get('/accessory/create', function (html) {
    $('.inputAccModal').html(html);
    $('.inputAcc').modal('show');
  });
});

//input : get sub model
$(document).on('change', '#model_id', function () {
  const modelId = $(this).val();
  const $subModelSelect = $('#subModel_id');

  $subModelSelect.empty().append('<option value="">-- เลือกรุ่นรถย่อย --</option>');

  if (!modelId) return;

  $.ajax({
    url: '/api/accessory/sub-model/' + modelId,
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

//input : save acc
$(document).on('click', '.btnStoreAccessory', function (e) {
  e.preventDefault();

  const $btn = $(this);
  const form = $btn.closest('form')[0];
  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }
  if (!costSpareIsValid(form)) {
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
    // ไม่ปิด modal ตอนยิง — ถ้าเจอรายการซ้ำ (422) ผู้ใช้จะได้ไม่เสียข้อมูลที่กรอกมา
    beforeSend: function () {
      Swal.fire({
        title: 'กำลังบันทึกข้อมูล...',
        text: 'กรุณารอสักครู่',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
      });
      $btn.prop('disabled', true);
    },
    success: function (res) {
      $('.inputAcc').modal('hide');

      Swal.fire({
        icon: 'success',
        title: 'สำเร็จ',
        text: res.message,
        timer: 2000,
        showConfirmButton: true
      });

      accessoryTable.ajax.reload(null, false);
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

// blur focus editAcc
$(document).on('hide.bs.modal', '.editAcc', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

//edit acc
$(document).on('click', '.btnEditAcc', function () {
  const id = $(this).data('id');
  const $btn = $(this);
  const form = $btn.closest('form')[0];

  $.get('/accessory/' + id + '/edit', function (html) {
    $('.editAccModal').html(html);
    const $modal = $('.editAcc');

    $modal.modal('show');

    $modal
      .find('.btnUpdateAccessory')
      .off('click')
      .on('click', function (e) {
        e.preventDefault();

        const form = $modal.find('form')[0];
        if (!costSpareIsValid(form)) {
          return;
        }

        const formData = new FormData(form);

        $.ajax({
          url: form.action,
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          // ไม่ปิด modal ตอนยิง — ถ้าเจอรายการซ้ำ (422) ผู้ใช้จะได้ไม่เสียข้อมูลที่กรอกมา
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
            $modal.modal('hide');

            Swal.fire({
              icon: 'success',
              title: 'สำเร็จ!',
              text: res.message,
              timer: 2000,
              showConfirmButton: true
            });

            accessoryTable.ajax.reload(null, false);
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

//delete acc
$(document).on('click', '.btnDeleteAcc', function () {
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
        url: '/accessory/' + id,
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

            accessoryTable.ajax.reload(null, false);
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

// =============================================

//partner

//view : table partner
let partnerTable;

$(document).ready(function () {
  if ($.fn.DataTable.isDataTable('.partnerTable')) {
    $('.partnerTable').DataTable().destroy();
  }

  partnerTable = $('.partnerTable').DataTable({
    ajax: '/accessory/partner/list',
    columns: [{ data: 'No' }, { data: 'name' }, { data: 'Action', orderable: false, searchable: false }],
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

// blur focus inputPart
$(document).on('hide.bs.modal', '.inputPart', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

//input partner
$(document).on('click', '.btnInputPart', function () {
  $.get('/accessory/create-partner', function (html) {
    $('.inputPartModal').html(html);
    $('.inputPart').modal('show');
  });
});

$(document).on('click', '.btnStorePartner', function (e) {
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
      $('.inputPart').modal('hide');

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

      partnerTable.ajax.reload(null, false);
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

// blur focus editPart
$(document).on('hide.bs.modal', '.editPart', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

//edit partner
$(document).on('click', '.btnEditPart', function () {
  const id = $(this).data('id');
  const $btn = $(this);
  const form = $btn.closest('form')[0];

  $.get('/accessory/edit-partner/' + id, function (html) {
    $('.editPartModal').html(html);
    const $modal = $('.editPart');

    $modal.modal('show');

    $modal
      .find('.btnUpdatePartner')
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

            partnerTable.ajax.reload(null, false);
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

//delete partner
$(document).on('click', '.btnDeletePart', function () {
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
        url: '/accessory/destroy-partner/' + id,
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

            partnerTable.ajax.reload(null, false);
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

//report accessory partner
document.addEventListener('DOMContentLoaded', function () {
  const modalEl = document.querySelector('.viewExportAccPart');
  if (!modalEl) return; // กัน error

  const modal = new bootstrap.Modal(modalEl);
  modal.show();

  // ปิด modal แล้วกลับหน้าก่อนหน้า
  modalEl.addEventListener('hidden.bs.modal', function () {
    window.history.back();
  });
});
