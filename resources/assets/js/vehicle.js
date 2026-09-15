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
      { data: 'FullName', orderable: false },
      { data: 'vin', orderable: false },
      // ป้ายแดง/ป้ายขาว 2 บรรทัดในคอลัมน์เดียว — ค้นหาได้ด้วย (เลขป้ายอยู่ในข้อความ)
      { data: 'plates', orderable: false },
      { data: 'province', orderable: false },
      { data: 'withdrawn_cost', orderable: false },
      { data: 'receipt_total', orderable: false },
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

  // คุม loader overlay เอง
  vehicleTable.on('preXhr.dt', function () {
    $('#vehicleLoadingOverlay').css('display', 'flex');
  });
  vehicleTable.on('xhr.dt', function () {
    $('#vehicleLoadingOverlay').css('display', 'none');
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


// หน้าแก้ไข : คิด "รวมเบิก / รวมเคลียร์" ใหม่ทันทีที่พิมพ์ (ตรวจ + ช่อง + ใบเสร็จ + อื่นๆ)
// ฝั่ง server คิดซ้ำตอนบันทึกอยู่แล้ว ตรงนี้แค่ให้เห็นยอดทันทีไม่ต้องเดา
// ผูกที่ document เพราะโมดัลแก้ไขโหลดมาด้วย ajax
function recalcVehicleEdit($scope, prefix) {
  const num = cls => parseFloat(($scope.find('.' + prefix + cls).val() || '0').replace(/,/g, '')) || 0;
  const total = num('-check') + num('-channel') + num('-bill') + num('-other');

  $scope
    .find('.' + prefix + '-total')
    .val(total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

  // มียอดอื่นๆ ต้องมีหมายเหตุ — ขึ้นกรอบแดงไว้ก่อน ฝั่ง server ดักซ้ำตอนกดบันทึก
  const $note = $scope.find('.' + prefix + '-other-note');
  $note.toggleClass('is-invalid', num('-other') > 0 && !String($note.val() || '').trim());
}

$(document).on(
  'input',
  '.veh-wd-check, .veh-wd-channel, .veh-wd-bill, .veh-wd-other, .veh-wd-other-note',
  function () {
    recalcVehicleEdit($(this).closest('form'), 'veh-wd');
  }
);

$(document).on(
  'input',
  '.veh-rc-check, .veh-rc-channel, .veh-rc-bill, .veh-rc-other, .veh-rc-other-note',
  function () {
    recalcVehicleEdit($(this).closest('form'), 'veh-rc');
  }
);

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
  let missingNote = 0;

  $('.checkItem:checked').each(function () {
    let row = $(this).closest('tr');

    let check = row.find('.withdrawal-check').val().replace(/,/g, '');
    let channel = row.find('.withdrawal-channel').val().replace(/,/g, '');
    let receipt = row.find('.withdrawal-bill').val().replace(/,/g, '');
    let other = row.find('.withdrawal-other').val().replace(/,/g, '');
    let otherNote = (row.find('.withdrawal-other-note').val() || '').trim();
    let total = row.find('.withdrawal-total').val().replace(/,/g, '');

    // มียอด "อื่นๆ" ต้องมีหมายเหตุกำกับเสมอ — ไม่งั้นทีหลังไม่มีใครรู้ว่าเงินก้อนนี้ค่าอะไร
    if (parseFloat(other) > 0 && !otherNote) {
      missingNote++;
      return;
    }

    let isComplete = check >= 0 && channel >= 0 && receipt > 0 && total >= 0;

    if (!isComplete) return;

    items.push({
      id: $(this).val(),
      check: check,
      channel: channel,
      receipt: receipt,
      other: other,
      other_note: otherNote,
      total: total
    });
  });

  if (missingNote > 0) {
    $('.viewWithdrawal')
      .one('hidden.bs.modal', function () {
        Swal.fire({
          icon: 'warning',
          title: 'กรุณากรอกหมายเหตุ',
          text: 'มี ' + missingNote + ' รายการที่ใส่ยอด "อื่นๆ" ไว้แต่ยังไม่ได้ระบุหมายเหตุ'
        }).then(() => {
          $('.viewWithdrawal').modal('show');
        });
      })
      .modal('hide');
    return;
  }


  if (items.length === 0) {
    // แถวที่กรอกครบแล้วแต่ยังไม่ได้ติ๊ก — บอกให้ตรงจุด ดีกว่าขึ้น "กรุณาเลือกข้อมูล" ลอย ๆ
    // (เคสที่เจอบ่อย : กรอกเสร็จแล้วกดส่งเบิกเลย โดยไม่รู้ว่าติ๊กยังไม่ได้ติ๊ก/หลุดไป)
    let ready = $('#tab-withdrawal .checkItem:not(:disabled):not(:checked)').length;

    $('.viewWithdrawal')
      .one('hidden.bs.modal', function () {
        Swal.fire({
          icon: 'warning',
          title: ready > 0 ? 'ยังไม่ได้ติ๊กเลือกรายการ' : 'กรุณาเลือกข้อมูล',
          text: ready > 0 ? 'มี ' + ready + ' รายการที่กรอกข้อมูลครบแล้ว แต่ยังไม่ได้ติ๊กเลือกหน้ารายการ' : ''
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

//เช็คกรอกข้อมูลครบ — มียอด "อื่นๆ" ต้องมีหมายเหตุด้วย ถึงจะติ๊กเลือกได้
// จำไว้ด้วยว่าก่อนหน้านี้ผู้ใช้ติ๊กไว้ไหม : ระหว่างพิมพ์ "อื่นๆ" แถวจะไม่ครบชั่วคราว (ยังไม่ได้พิมพ์หมายเหตุ)
// ถ้าปล่อยให้ติ๊กหลุดแล้วไม่คืนให้ ผู้ใช้ที่กรอกเสร็จแล้วกดส่งเบิกเลยจะเสียรายการนั้นไปเงียบ ๆ
function checkWithdrawalRow(row) {
  let check = row.find('.withdrawal-check').val().replace(/,/g, '');
  let channel = row.find('.withdrawal-channel').val().replace(/,/g, '');
  let receipt = row.find('.withdrawal-bill').val().replace(/,/g, '');
  let other = parseFloat(row.find('.withdrawal-other').val().replace(/,/g, '')) || 0;
  let otherNote = (row.find('.withdrawal-other-note').val() || '').trim();
  let total = row.find('.withdrawal-total').val().replace(/,/g, '');

  let otherOk = other <= 0 || otherNote !== '';
  let isComplete = check >= 0 && check !== '' && channel >= 0 && receipt > 0 && total >= 0 && otherOk;

  // ย้ำด้วยกรอบแดงตรงช่องหมายเหตุ จะได้รู้ว่าติ๊กไม่ได้เพราะอะไร
  row.find('.withdrawal-other-note').toggleClass('is-invalid', other > 0 && otherNote === '');

  let checkbox = row.find('.checkItem');

  if (isComplete) {
    checkbox.prop('disabled', false).attr('title', '');
    // คืนติ๊กที่หลุดไปตอนแถวยังกรอกไม่ครบ
    if (checkbox.data('wasChecked')) {
      checkbox.prop('checked', true).removeData('wasChecked');
    }
  } else {
    if (checkbox.is(':checked')) {
      checkbox.data('wasChecked', true);
    }
    // โชว์ช่องติ๊กไว้เหมือนเดิมแต่กดไม่ได้ (ของเดิมซ่อนทิ้ง ทำให้ดูเหมือนช่องหายไปเฉย ๆ)
    checkbox
      .prop('checked', false)
      .prop('disabled', true)
      .attr('title', other > 0 && otherNote === '' ? 'ใส่ยอด "อื่นๆ" แล้วต้องกรอกหมายเหตุก่อน' : 'กรอกข้อมูลให้ครบก่อน');
  }

  checkbox.show();
}

//คำนวณรวม ส่งเบิก — "อื่นๆ" ถูกบวกเข้ายอดรวมด้วย
$(document).on('input', '.calc-input', function () {
  let row = $(this).closest('tr');

  let check = parseFloat(row.find('.withdrawal-check').val().replace(/,/g, '')) || 0;
  let channel = parseFloat(row.find('.withdrawal-channel').val().replace(/,/g, '')) || 0;
  let receipt = parseFloat(row.find('.withdrawal-bill').val().replace(/,/g, '')) || 0;
  let other = parseFloat(row.find('.withdrawal-other').val().replace(/,/g, '')) || 0;

  let total = check + channel + receipt + other;

  row
    .find('.withdrawal-total')
    .val(total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

  checkWithdrawalRow(row);
});

// พิมพ์หมายเหตุแล้วต้องเช็คแถวใหม่ (ติ๊กได้/ไม่ได้ ขึ้นกับว่ามีหมายเหตุครบหรือยัง)
$(document).on('input', '.withdrawal-other-note', function () {
  checkWithdrawalRow($(this).closest('tr'));
});

$(document).on('change', '#checkAll', function () {
  let isChecked = this.checked;

  // เลือกได้เฉพาะแถวที่กรอกครบ (ช่องติ๊กไม่ถูก disable) — เช็คจาก :disabled ไม่ใช่ :visible
  // เพราะตอนนี้แถวที่ยังไม่ครบจะโชว์ช่องติ๊กไว้แบบกดไม่ได้ ไม่ได้ซ่อนทิ้งเหมือนเดิม
  $('#tab-withdrawal .checkItem').each(function () {
    checkWithdrawalRow($(this).closest('tr'));

    if (!$(this).is(':disabled')) {
      $(this).prop('checked', isChecked);
    } else {
      $(this).prop('checked', false);
    }
  });
});

$(document).on('shown.bs.modal', '.viewWithdrawal', function () {
  // ตั้งสถานะช่องติ๊กของทุกแถวตอนเปิดโมดัล — แถวที่ยังกรอกไม่ครบจะโชว์แต่กดไม่ได้
  $('#tab-withdrawal tbody tr').each(function () {
    checkWithdrawalRow($(this));
  });

  $('#tab-clear tbody tr').each(function () {
    checkClearRow($(this));
  });
});

//clear
$(document).on('click', '.btnConfirmClear', function () {
  let items = [];
  let missingNote = 0;

  $('.checkItemClear:checked').each(function () {
    let row = $(this).closest('tr');

    let check = row.find('.receipt-check').val().replace(/,/g, '');
    let channel = row.find('.receipt-channel').val().replace(/,/g, '');
    let receipt = row.find('.receipt-bill').val().replace(/,/g, '');
    let other = row.find('.receipt-other').val().replace(/,/g, '');
    let otherNote = (row.find('.receipt-other-note').val() || '').trim();
    let total = row.find('.receipt-total').val().replace(/,/g, '');

    // มียอด "อื่นๆ" ต้องมีหมายเหตุกำกับเสมอ (กติกาเดียวกับฝั่งส่งเบิก)
    if (parseFloat(other) > 0 && !otherNote) {
      missingNote++;
      return;
    }

    let isComplete = check >= 0 && channel >= 0 && receipt > 0 && total >= 0;

    if (!isComplete) return;

    items.push({
      id: $(this).val(),
      check: check,
      channel: channel,
      receipt: receipt,
      other: other,
      other_note: otherNote,
      total: total
    });
  });

  if (missingNote > 0) {
    $('.viewWithdrawal')
      .one('hidden.bs.modal', function () {
        Swal.fire({
          icon: 'warning',
          title: 'กรุณากรอกหมายเหตุ',
          text: 'มี ' + missingNote + ' รายการที่ใส่ยอด "อื่นๆ" ไว้แต่ยังไม่ได้ระบุหมายเหตุ'
        }).then(() => {
          $('.viewWithdrawal').modal('show');
        });
      })
      .modal('hide');
    return;
  }


  if (items.length === 0) {
    let ready = $('#tab-clear .checkItemClear:not(:disabled):not(:checked)').length;

    $('.viewWithdrawal')
      .one('hidden.bs.modal', function () {
        Swal.fire({
          icon: 'warning',
          title: ready > 0 ? 'ยังไม่ได้ติ๊กเลือกรายการ' : 'กรุณาเลือกข้อมูล',
          text: ready > 0 ? 'มี ' + ready + ' รายการที่กรอกข้อมูลครบแล้ว แต่ยังไม่ได้ติ๊กเลือกหน้ารายการ' : ''
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

//เช็คครบไหม — กติกาและการคืนติ๊กเหมือนฝั่งส่งเบิก (ดู checkWithdrawalRow)
function checkClearRow(row) {
  let check = row.find('.receipt-check').val().replace(/,/g, '');
  let channel = row.find('.receipt-channel').val().replace(/,/g, '');
  let receipt = row.find('.receipt-bill').val().replace(/,/g, '');
  let other = parseFloat(row.find('.receipt-other').val().replace(/,/g, '')) || 0;
  let otherNote = (row.find('.receipt-other-note').val() || '').trim();
  let total = row.find('.receipt-total').val().replace(/,/g, '');

  let otherOk = other <= 0 || otherNote !== '';
  let isComplete = check >= 0 && check !== '' && channel >= 0 && receipt > 0 && total >= 0 && otherOk;

  row.find('.receipt-other-note').toggleClass('is-invalid', other > 0 && otherNote === '');

  let checkbox = row.find('.checkItemClear');

  if (isComplete) {
    checkbox.prop('disabled', false).attr('title', '');
    if (checkbox.data('wasChecked')) {
      checkbox.prop('checked', true).removeData('wasChecked');
    }
  } else {
    if (checkbox.is(':checked')) {
      checkbox.data('wasChecked', true);
    }
    checkbox
      .prop('checked', false)
      .prop('disabled', true)
      .attr('title', other > 0 && otherNote === '' ? 'ใส่ยอด "อื่นๆ" แล้วต้องกรอกหมายเหตุก่อน' : 'กรอกข้อมูลให้ครบก่อน');
  }

  checkbox.show();
}

//คำนวณรวม ส่งเคลียร์ — "อื่นๆ" ถูกบวกเข้ายอดรวมด้วย
$(document).on('input', '#tab-clear .calc-clear', function () {
  let row = $(this).closest('tr');

  let check = parseFloat(row.find('.receipt-check').val().replace(/,/g, '')) || 0;
  let channel = parseFloat(row.find('.receipt-channel').val().replace(/,/g, '')) || 0;
  let receipt = parseFloat(row.find('.receipt-bill').val().replace(/,/g, '')) || 0;
  let other = parseFloat(row.find('.receipt-other').val().replace(/,/g, '')) || 0;

  let total = check + channel + receipt + other;

  row
    .find('.receipt-total')
    .val(total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

  checkClearRow(row);
});

$(document).on('input', '#tab-clear .receipt-other-note', function () {
  checkClearRow($(this).closest('tr'));
});

$(document).on('change', '#checkAllClear', function () {
  let isChecked = this.checked;

  $('#tab-clear .checkItemClear').each(function () {
    checkClearRow($(this).closest('tr'));

    if (!$(this).is(':disabled')) {
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
