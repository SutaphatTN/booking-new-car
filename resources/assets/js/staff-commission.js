$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});

let staffCommissionTable;

$(document).ready(function () {
  if ($.fn.DataTable.isDataTable('.staffCommissionTable')) {
    $('.staffCommissionTable').DataTable().destroy();
  }

  staffCommissionTable = $('.staffCommissionTable').DataTable({
    ajax: {
      url: '/staff-commission/list',
      data: function (d) {
        d.month = $('#staffCommissionMonth').val();
      }
    },
    columns: [
      { data: 'No' },
      { data: 'name' },
      { data: 'detail' },
      { data: 'carTotal', className: 'text-end' },
      { data: 'extra', className: 'text-end' },
      { data: 'total', className: 'text-end fw-bold' },
      {
        data: null,
        orderable: false,
        searchable: false,
        className: 'text-center',
        render: function (data, type, row) {
          if (!row.DT_RowData || !row.DT_RowData.uid) return '';
          return (
            '<button type="button" class="btn btn-sm btn-primary btnStaffCommissionDetail">' +
            '<i class="bx bx-detail me-1"></i> รายละเอียด' +
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
    pageLength: 25,
    autoWidth: false,
    language: {
      lengthMenu: 'แสดง _MENU_ แถว',
      zeroRecords: 'ไม่พบข้อมูล',
      info: 'แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ',
      infoEmpty: 'ไม่มีข้อมูล',
      search: 'ค้นหา:',
      paginate: { next: 'ถัดไป', previous: 'ก่อนหน้า' }
    }
  });

  staffCommissionTable.on('preXhr.dt', function () {
    $('#staffCommissionLoadingOverlay').css('display', 'flex');
  });
  staffCommissionTable.on('xhr.dt', function () {
    $('#staffCommissionLoadingOverlay').css('display', 'none');
  });
});

// เปลี่ยนเดือน → โหลดใหม่
$(document).on('change', '#staffCommissionMonth', function () {
  if (staffCommissionTable) staffCommissionTable.ajax.reload();
});

// เปิดรายละเอียดรายคน
$(document).on('click', '.btnStaffCommissionDetail', function () {
  const uid = $(this).closest('tr').data('uid');
  if (!uid) return;

  const $btn = $(this);
  if ($btn.prop('disabled')) return; // กันกดรัว ๆ ยิงซ้ำระหว่างรอ

  $btn.prop('disabled', true);
  $('#staffCommissionLoadingOverlay').css('display', 'flex');

  $.get('/staff-commission/detail/' + uid, { month: $('#staffCommissionMonth').val() }, function (html) {
    $('.staffCommissionDetailModel').html(html);
    $('.staffCommissionDetail').modal('show');
  })
    .fail(function (xhr) {
      const msg = xhr.status === 403 ? 'ไม่มีสิทธิ์ดูของคนนี้' : 'โหลดรายละเอียดไม่สำเร็จ';
      if (window.Swal) Swal.fire({ icon: 'error', title: msg });
      else alert(msg);
    })
    .always(function () {
      $('#staffCommissionLoadingOverlay').css('display', 'none');
      $btn.prop('disabled', false);
    });
});

// อ่านตัวเลขที่มี comma
function parseStaffMoney(v) {
  return parseFloat(String(v == null ? '' : v).replace(/,/g, '')) || 0;
}

// คิดยอดสุทธิสด : คอมตามยอดขาย (คงที่จาก server) + ช่องที่กรอกเอง
function recomputeStaffNet() {
  const $display = $('#staffNetDisplay');
  if (!$display.length) return;

  let total = parseFloat($display.data('car')) || 0;

  $('#staffCommissionForm .smoney').each(function () {
    total += parseStaffMoney($(this).val());
  });
  // ช่องติ๊ก : ยอดอยู่ใน label ฝั่ง server แล้ว อ่านจาก data ที่ฝังไว้กับ checkbox
  $('#staffCommissionForm input[type="checkbox"][name^="extras"]').each(function () {
    if (this.checked) total += parseFloat($(this).data('amount')) || 0;
  });

  $display.text(total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ฿');
}

$(document).on('input', '#staffCommissionForm .smoney', function () {
  recomputeStaffNet();
});
$(document).on('change', '#staffCommissionForm input[type="checkbox"][name^="extras"]', recomputeStaffNet);
$(document).on('blur', '#staffCommissionForm .smoney', function () {
  if (this.value.trim() === '') return;
  this.value = parseStaffMoney(this.value).toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
});

// บันทึก
$(document).on('submit', '#staffCommissionForm', function (e) {
  e.preventDefault();
  const $btn = $('#btnSaveStaffCommission');
  $btn.prop('disabled', true);

  // ตัด comma ก่อนส่ง (backend validate เป็นตัวเลข)
  const payload = $(this)
    .serializeArray()
    .map(f => (/^extras\[/.test(f.name) ? { name: f.name, value: parseStaffMoney(f.value) || f.value } : f));

  // checkbox ที่ไม่ติ๊กจะไม่ถูกส่ง → ส่ง 0 ไปเองจะได้ล้างค่าเดิมได้
  $('#staffCommissionForm input[type="checkbox"][name^="extras"]').each(function () {
    if (!this.checked) payload.push({ name: this.name, value: 0 });
  });

  $.post('/staff-commission/save', $.param(payload), function () {
    $('.staffCommissionDetail').modal('hide');
    if (staffCommissionTable) staffCommissionTable.ajax.reload(null, false);
    if (window.Swal) {
      Swal.fire({ icon: 'success', title: 'บันทึกสำเร็จ', timer: 1400, showConfirmButton: true });
    }
  })
    .fail(function (xhr) {
      const res = xhr.responseJSON || {};
      const detail = res.errors ? Object.values(res.errors).flat().join('\n') : res.message || 'กรุณาลองใหม่อีกครั้ง';
      if (window.Swal) Swal.fire({ icon: 'error', title: 'บันทึกไม่สำเร็จ', text: detail });
      else alert('บันทึกไม่สำเร็จ\n' + detail);
    })
    .always(function () {
      $btn.prop('disabled', false);
    });
});

// เคลียร์ DOM หลังปิด modal (กัน backdrop ค้าง)
$(document).on('hidden.bs.modal', '.staffCommissionDetail', function () {
  $('.staffCommissionDetailModel').empty();
});

// ── รายงาน Excel ────────────────────────────────────────────────
$(document).on('click', '.btnViewExportStaffCom', function () {
  $.get('/staff-commission/report', function (html) {
    $('.viewExportStaffComModel').html(html);
    $('.viewExportStaffCom').modal('show');
    // เปิดมาให้ตรงกับเดือนที่กำลังดูอยู่ในตาราง
    $('#staffReportMonth').val($('#staffCommissionMonth').val());
  });
});

$(document).on('hide.bs.modal', '.viewExportStaffCom', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});
