$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});

//view : table
let commissionTable;

$(document).ready(function () {
  if ($.fn.DataTable.isDataTable('.commissionTable')) {
    $('.commissionTable').DataTable().destroy();
  }

  commissionTable = $('.commissionTable').DataTable({
    ajax: {
      url: '/purchase-order/list-Commission',
      data: function (d) {
        d.month = $('#commissionMonth').val();
      }
    },
    columns: [
      { data: 'No' },
      { data: 'name' },
      { data: 'total_car' },
      { data: 'com' },
      {
        data: null,
        orderable: false,
        searchable: false,
        className: 'text-center',
        render: function (data, type, row) {
          if (!row.DT_RowData || !row.DT_RowData.saleid) return '';
          return (
            '<button type="button" class="btn btn-sm btn-primary btnCommissionDetail">' +
            '<i class="bx bx-edit me-1"></i> รายละเอียด / กรอกค่าคอม' +
            '</button>'
          );
        }
      }
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

  // ── ตัวโหลดข้อมูล (โชว์ตอนดึงข้อมูล เช่น เปลี่ยนเดือน) ──
  commissionTable.on('preXhr.dt', function () {
    $('#commissionLoadingOverlay').css('display', 'flex');
  });
  commissionTable.on('xhr.dt', function () {
    $('#commissionLoadingOverlay').css('display', 'none');
  });
});

// ── เป้ายอดขายต่อเดือน (คอมตัวรถรายคัน) ──
function loadMonthlyTarget() {
  if (!$('#monthlyTarget').length) return; // brand 3 ไม่มีช่องเป้า
  const month = $('#commissionMonth').val();
  $.get('/purchase-order/commission-target', { month: month }, function (res) {
    $('#monthlyTarget').val(res.target ?? '');
    const $st = $('#targetStatus');
    if (res.target) {
      const cls = res.achieved ? 'text-success' : 'text-danger';
      const txt = res.achieved ? 'บรรลุเป้า 120% ✓' : 'ยังไม่บรรลุ 120%';
      $st.html(
        '<span class="' + cls + '">ยอดขาย ' + res.brand_count + '/' + res.threshold + ' คัน — ' + txt + '</span>'
      );
    } else {
      $st.html('<span class="text-muted">ยังไม่ตั้งเป้า (ยอดขาย ' + res.brand_count + ' คัน)</span>');
    }
  });
}

$(document).on('click', '#btnSaveTarget', function () {
  const $btn = $(this);
  $btn.prop('disabled', true);
  $.post('/purchase-order/commission-target', {
    month: $('#commissionMonth').val(),
    target: $('#monthlyTarget').val() || 0
  }, function () {
    if (commissionTable) commissionTable.ajax.reload(null, false);
    loadMonthlyTarget();
    if (window.Swal) {
      Swal.fire({ icon: 'success', title: 'บันทึกเป้าแล้ว', timer: 1200, showConfirmButton: false });
    }
  })
    .fail(function () {
      if (window.Swal) Swal.fire({ icon: 'error', title: 'บันทึกไม่สำเร็จ' });
      else alert('บันทึกไม่สำเร็จ');
    })
    .always(function () { $btn.prop('disabled', false); });
});

$(document).ready(loadMonthlyTarget);

// reload when month changes
$(document).on('change', '#commissionMonth', function () {
  if (commissionTable) {
    commissionTable.ajax.reload();
  }
  loadMonthlyTarget();
});

// click action button -> open detail modal (customer list + monthly extra commission)
$(document).on('click', '.btnCommissionDetail', function () {
  const saleId = $(this).closest('tr').data('saleid');
  if (!saleId) return;

  const month = $('#commissionMonth').val();
  $.get('/purchase-order/commission-sale-detail/' + saleId, { month: month }, function (html) {
    $('.commissionDetailModel').html(html);
    $('.commissionDetail').modal('show');
  });
});

// live recompute net commission in the detail modal (brand-aware)
function recomputeCommissionNet() {
  const $display = $('#netCommissionDisplay');
  if (!$display.length) return;

  const num = id => parseFloat($('#' + id).val()) || 0;
  const base = parseFloat($display.data('base')) || 0;
  const brand = parseInt($display.data('brand'), 10) || 0;
  const ssi = parseFloat($display.data('ssi')) || 0; // คอม SSI (คิดสดจาก server) รวมเข้ายอด
  const car = parseFloat($display.data('car')) || 0; // คอมตัวรถรายคัน (คิดสดจาก server)
  const held = parseFloat($display.data('held')) || 0; // คอมกั๊ก brand 1 = (ยกมา) − (กั๊กเดือนนี้)

  let net;
  if (brand === 1 || brand === 3) {
    // วินัยไม่ผ่าน → หัก 15% จากรวมค่าคอมรถ ; ไม่มี lead/clip
    const failed = $('input[name="discipline_failed"]:checked').val() === '1';
    net = (failed ? base * 0.85 : base) - num('deduct_absence');
  } else {
    net = base + num('com_discipline') + num('com_lead') + num('com_clip') - num('deduct_absence');
  }
  net += ssi + car + held;

  $display.text(net.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ฿');
}

$(document).on('input', '#commissionMonthlyForm input[type="number"]', recomputeCommissionNet);
$(document).on('change', '#commissionMonthlyForm input[name="discipline_failed"]', recomputeCommissionNet);

// แก้ "คอมอื่นๆ" ต่อคัน → คิดรวมค่าคอมรถต่อแถว + ยอดรวม + net สด
function recomputeCarsTable() {
  let base = 0;
  $('.car-special-input').each(function () {
    const rowbase = parseFloat($(this).data('rowbase')) || 0;
    const special = parseFloat($(this).val()) || 0;
    const rowTotal = rowbase + special;
    $(this).closest('tr').find('.car-row-total')
      .text(rowTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    base += rowTotal;
  });
  $('#carsBaseTotal').text(base.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
  $('#netCommissionDisplay').data('base', base);
  recomputeCommissionNet();
}
$(document).on('input', '.car-special-input', recomputeCarsTable);

// save monthly extra commission (+ คอมอื่นๆ ต่อคัน)
$(document).on('submit', '#commissionMonthlyForm', function (e) {
  e.preventDefault();
  const $btn = $('#btnSaveCommissionMonthly');
  $btn.prop('disabled', true);

  const payload = $(this).serializeArray();
  $('.car-special-input').each(function () {
    payload.push({ name: 'car_special[' + $(this).data('id') + ']', value: $(this).val() || 0 });
  });

  $.post('/purchase-order/commission-monthly', $.param(payload), function () {
    $('.commissionDetail').modal('hide');
    if (commissionTable) {
      commissionTable.ajax.reload(null, false);
    }
    if (window.Swal) {
      Swal.fire({ icon: 'success', title: 'บันทึกสำเร็จ', timer: 1400, showConfirmButton: false });
    }
  })
    .fail(function () {
      if (window.Swal) {
        Swal.fire({ icon: 'error', title: 'บันทึกไม่สำเร็จ', text: 'กรุณาลองใหม่อีกครั้ง' });
      } else {
        alert('บันทึกไม่สำเร็จ');
      }
    })
    .always(function () {
      $btn.prop('disabled', false);
    });
});

// clear detail modal DOM after close (กัน backdrop ค้าง / focus)
$(document).on('hidden.bs.modal', '.commissionDetail', function () {
  $('.commissionDetailModel').empty();
});

//view report
$(document).on('hide.bs.modal', '.viewExportCom', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

$(document).on('click', '.btnViewExportCom', function () {
  $.get('/purchase-order/view-export-commission', function (html) {
    $('.viewExportComModel').html(html);
    $('.viewExportCom').modal('show');
  });
});

//view report gp
document.addEventListener('DOMContentLoaded', function () {
  const modalEl = document.querySelector('.viewExportGP');
  if (!modalEl) return; // กัน error

  const modal = new bootstrap.Modal(modalEl);
  modal.show();

  // ปิด modal แล้วกลับหน้าก่อนหน้า
  modalEl.addEventListener('hidden.bs.modal', function () {
    window.history.back();
  });
});
