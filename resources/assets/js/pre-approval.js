// pre-approval : ขออนุมัติเกินงบล่วงหน้า
$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});

let preApprovalTable;

$(document).ready(function () {
  if ($.fn.DataTable.isDataTable('#preApprovalTable')) {
    $('#preApprovalTable').DataTable().destroy();
  }

  preApprovalTable = $('#preApprovalTable').DataTable({
    ajax: '/pre-approval/list',
    columns: [
      { data: 'No' },
      { data: 'customer' },
      { data: 'model' },
      { data: 'sale' },
      { data: 'requested' },
      { data: 'status' },
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
      paginate: { next: 'ถัดไป', previous: 'ก่อนหน้า' }
    }
  });
});

// กด "สร้างการจอง" → แถวเดิมเข้าระบบจอง พร้อมลายเซ็นอนุมัติ
$(document).on('click', '.btnConvertBooking', function () {
  const id = $(this).data('id');

  Swal.fire({
    title: 'สร้างการจอง?',
    text: 'รายการนี้จะถูกส่งเข้าระบบจอง โดยใช้วันที่วันนี้เป็นวันที่จอง (แก้ไขได้ภายหลัง)',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#6c5ffc',
    cancelButtonColor: '#d33',
    confirmButtonText: 'ใช่, สร้างการจอง',
    cancelButtonText: 'ยกเลิก'
  }).then(result => {
    if (!result.isConfirmed) return;

    $.ajax({
      url: '/pre-approval/' + id + '/convert',
      type: 'POST',
      beforeSend: function () {
        Swal.fire({
          title: 'กำลังดำเนินการ...',
          allowOutsideClick: false,
          didOpen: () => Swal.showLoading()
        });
      },
      success: function (res) {
        Swal.fire({
          icon: 'success',
          title: 'สำเร็จ!',
          text: res.message,
          timer: 2000,
          showConfirmButton: true
        });
        preApprovalTable.ajax.reload(null, false);
      },
      error: function (xhr) {
        Swal.fire({
          icon: 'error',
          title: 'เกิดข้อผิดพลาด',
          text: xhr.responseJSON?.message || 'ไม่สามารถสร้างการจองได้'
        });
      }
    });
  });
});

// ลบคำขอ (ลบได้เฉพาะที่ยังไม่ถูกสร้างเป็นการจอง)
$(document).on('click', '.btnDeletePreApproval', function () {
  const id = $(this).data('id');

  Swal.fire({
    title: 'คุณแน่ใจหรือไม่?',
    text: 'ต้องการลบคำขออนุมัติเกินงบรายการนี้ใช่หรือไม่?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#6c5ffc',
    cancelButtonColor: '#d33',
    confirmButtonText: 'ใช่, ลบเลย!',
    cancelButtonText: 'ยกเลิก'
  }).then(result => {
    if (!result.isConfirmed) return;

    $.ajax({
      url: '/pre-approval/' + id,
      type: 'DELETE',
      success: function (res) {
        Swal.fire({
          icon: 'success',
          title: 'สำเร็จ',
          text: res.message,
          timer: 2000,
          showConfirmButton: true
        });
        preApprovalTable.ajax.reload(null, false);
      },
      error: function (xhr) {
        Swal.fire({
          icon: 'error',
          title: 'เกิดข้อผิดพลาด',
          text: xhr.responseJSON?.message || 'ไม่สามารถลบข้อมูลได้'
        });
      }
    });
  });
});
