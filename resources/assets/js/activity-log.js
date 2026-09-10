'use strict';

$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});

/* ============================================================
 * ประวัติการแก้ไข (activity_logs) — หน้ารวมของ admin
 * serverSide เพราะตารางโตเรื่อย ๆ ตามการใช้งานจริง
 * ============================================================ */

let activityLogTable;

const dtLang = {
  lengthMenu: 'แสดง _MENU_ แถว',
  zeroRecords: 'ไม่พบข้อมูล',
  info: 'แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ',
  infoEmpty: 'ไม่มีข้อมูล',
  infoFiltered: '(กรองจากทั้งหมด _MAX_ รายการ)',
  search: 'ค้นหา:',
  processing: 'กำลังโหลด...',
  paginate: { next: 'ถัดไป', previous: 'ก่อนหน้า' }
};

$(document).ready(function () {
  if ($.fn.DataTable.isDataTable('.activityLogTable')) {
    $('.activityLogTable').DataTable().destroy();
  }

  activityLogTable = $('.activityLogTable').DataTable({
    serverSide: true,
    processing: false,   // ใช้ overlay #activityLogLoadingOverlay แทน (ของ DataTables ทับหัวตาราง)
    searchDelay: 500,
    ajax: {
      url: '/activity-log/list',
      data: function (d) {
        d.filter_subject = $('#logSubject').val() || '';
        d.filter_event = $('#logEvent').val() || '';
        d.filter_user = $('#logUser').val() || '';
        d.filter_brand = $('#logBrand').val() || '';
        d.date_from = $('#logDateFrom').val() || '';
        d.date_to = $('#logDateTo').val() || '';
      }
    },
    columns: [
      { data: 'at', className: 'text-nowrap' },
      { data: 'by' },
      { data: 'subject', className: 'text-center' },
      { data: 'item' },
      { data: 'event', className: 'text-center' },
      { data: 'changes' },
      { data: 'brand' }
    ],
    ordering: false,
    pageLength: 25,
    lengthMenu: [10, 25, 50, 100],
    autoWidth: false,
    language: dtLang
  });

  activityLogTable.on('preXhr.dt', () => $('#activityLogLoadingOverlay').css('display', 'flex'));
  activityLogTable.on('xhr.dt', () => $('#activityLogLoadingOverlay').css('display', 'none'));
});

// ตัวกรองทุกตัวโหลดตารางใหม่ (serverSide — กรองที่ฝั่ง server ทั้งหมด)
$(document).on('change', '#logSubject, #logEvent, #logUser, #logBrand, #logDateFrom, #logDateTo', function () {
  if (activityLogTable) activityLogTable.ajax.reload();
});

$(document).on('click', '.btnLogReset', function () {
  // ประเภทกลับไปที่ค่าตั้งต้น ไม่ใช่ค่าว่าง — หน้านี้ต้องมีประเภทที่เลือกอยู่เสมอ
  $('#logSubject').val($(this).data('default-subject'));
  $('#logEvent, #logUser, #logBrand').val('');
  $('#logDateFrom, #logDateTo').val('');
  if (activityLogTable) activityLogTable.ajax.reload();
});

// ดูรายละเอียดเต็ม (แถวที่ตารางย่อไว้เพราะเปลี่ยนหลายฟิลด์)
$(document).on('click', '.btnLogDetail', function () {
  const id = $(this).data('id');

  $.get('/activity-log/' + id, function (html) {
    $('.logDetailModal').html(html);
    $('.logDetail').modal('show');
  });
});

// blur focus กัน aria-hidden warning ตอนปิด modal
$(document).on('hide.bs.modal', '.logDetail', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});
