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