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
    ajax: '/purchase-order/list-Commission',
    columns: [
      { data: 'No' }, 
      { data: 'name' }, 
      { data: 'total_car' }, 
      { data: 'com' }
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
