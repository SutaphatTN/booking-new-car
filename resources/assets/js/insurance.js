'use strict';

$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});

/* ============================================================
 * ตั้งค่าประกัน (insurance) — เพิ่ม/แก้/ลบ เฉพาะ admin, role อื่นดูอย่างเดียว
 * ============================================================ */

let insuranceTable;

const dtLang = {
  lengthMenu: 'แสดง _MENU_ แถว',
  zeroRecords: 'ไม่พบข้อมูล',
  info: 'แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ',
  infoEmpty: 'ไม่มีข้อมูล',
  search: 'ค้นหา:',
  paginate: { next: 'ถัดไป', previous: 'ก่อนหน้า' }
};

$(document).ready(function () {
  if ($.fn.DataTable.isDataTable('.insuranceTable')) {
    $('.insuranceTable').DataTable().destroy();
  }
  insuranceTable = $('.insuranceTable').DataTable({
    ajax: '/insurance/list',
    columns: [
      { data: 'No' },
      { data: 'name' },
      { data: 'Action', orderable: false, searchable: false }
    ],
    ordering: false,
    pageLength: 10,
    autoWidth: false,
    language: dtLang
  });
});

/* ---------- helper: ajax submit ฟอร์มใน modal ---------- */
function submitInsuranceForm($btn, $modal) {
  const form = $modal.find('form')[0];
  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }

  $.ajax({
    url: form.action,
    type: 'POST',
    data: new FormData(form),
    processData: false,
    contentType: false,
    beforeSend: function () {
      $modal.modal('hide');
      Swal.fire({ title: 'กำลังบันทึก...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
      $btn.prop('disabled', true);
    },
    success: function (res) {
      Swal.fire({ icon: 'success', title: 'สำเร็จ', text: res.message, timer: 1500, showConfirmButton: true });
      insuranceTable.ajax.reload(null, false);
    },
    error: function (xhr) {
      Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: xhr.responseJSON?.message || 'ไม่สามารถบันทึกได้' });
    },
    complete: function () {
      $btn.prop('disabled', false);
    }
  });
}

/* ---------- เพิ่ม ---------- */
$(document).on('click', '.btnInputInsurance', function () {
  $.get('/insurance/create', function (html) {
    $('.inputInsuranceModal').html(html);
    $('.inputInsurance').modal('show');
  });
});

$(document).on('click', '.btnStoreInsurance', function (e) {
  e.preventDefault();
  submitInsuranceForm($(this), $('.inputInsurance'));
});

/* ---------- แก้ไข ---------- */
$(document).on('click', '.btnEditInsurance', function () {
  const id = $(this).data('id');
  $.get('/insurance/edit/' + id, function (html) {
    $('.editInsuranceModal').html(html);
    const $modal = $('.editInsurance');
    $modal.modal('show');
    $modal.find('.btnUpdateInsurance').off('click').on('click', function (e) {
      e.preventDefault();
      submitInsuranceForm($(this), $modal);
    });
  });
});

/* ---------- ลบ ---------- */
$(document).on('click', '.btnDeleteInsurance', function () {
  const id = $(this).data('id');
  Swal.fire({
    title: 'ยืนยันการลบ?',
    text: 'ประกันนี้จะถูกลบออกจากรายการ',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'ลบ',
    cancelButtonText: 'ยกเลิก',
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c5ffc'
  }).then(function (r) {
    if (!r.isConfirmed) return;
    $.ajax({
      url: '/insurance/destroy/' + id,
      type: 'DELETE',
      success: function (res) {
        Swal.fire({ icon: 'success', title: 'สำเร็จ', text: res.message, timer: 1500, showConfirmButton: true });
        insuranceTable.ajax.reload(null, false);
      },
      error: function (xhr) {
        Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: xhr.responseJSON?.message || 'ไม่สามารถลบได้' });
      }
    });
  });
});

// blur focus กัน aria-hidden warning ตอนปิด modal
$(document).on('hide.bs.modal', '.inputInsurance, .editInsurance', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});
