$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});

//view : table color_submodel
let ColorSubTable;

$(document).ready(function () {
  if ($.fn.DataTable.isDataTable('.ColorSubTable')) {
    $('.ColorSubTable').DataTable().destroy();
  }

  ColorSubTable = $('.ColorSubTable').DataTable({
    ajax: '/color/list',
    columns: [
      { data: 'No' },
      { data: 'model' },
      { data: 'submodel' },
      { data: 'color' },
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
        first: '',
        last: '',
        next: 'ถัดไป',
        previous: 'ก่อนหน้า'
      }
    }
  });
});

// blur focus inputColorSub
$(document).on('hide.bs.modal', '.inputColorSub', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

// select เลือกสี
$(document).on('shown.bs.modal', '.inputColorSub', function () {
  $('#color_id').select2({
    placeholder: 'เลือกสี',
    width: '100%',
    closeOnSelect: false,
    dropdownParent: $('.inputColorSub')
  });
});

//input : modal fin
$(document).on('click', '.btnInputColorSub', function () {
  $.get('/color/create', function (html) {
    $('.inputColorSubModal').html(html);
    $('.inputColorSub').modal('show');
  });
});

//input : save fin
$(document).on('click', '.btnStoreColorSub', function (e) {
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
      $('.inputColorSub').modal('hide');

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

      ColorSubTable.ajax.reload(null, false);
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

// blur focus editColorSub
// $(document).on('hide.bs.modal', '.editColorSub', function () {
//   setTimeout(() => {
//     document.activeElement.blur();
//     $('body').trigger('focus');
//   }, 1);
// });

//edit : fin
// $(document).on('click', '.btnEditColorSub', function () {
//   const id = $(this).data('id');
//   const $btn = $(this);
//   const form = $btn.closest('form')[0];

//   $.get('/color/' + id + '/edit', function (html) {
//     $('.editColorSubModal').html(html);
//     const $modal = $('.editColorSub');

//     $modal.modal('show');

//     $modal
//       .find('.btnUpdateColorSub')
//       .off('click')
//       .on('click', function (e) {
//         e.preventDefault();

//         const form = $modal.find('form')[0];
//         const formData = new FormData(form);

//         $.ajax({
//           url: form.action,
//           type: 'POST',
//           data: formData,
//           processData: false,
//           contentType: false,

//           beforeSend: function () {
//             $modal.modal('hide');

//             Swal.fire({
//               title: 'กำลังบันทึกข้อมูล...',
//               text: 'กรุณารอสักครู่',
//               allowOutsideClick: false,
//               didOpen: () => {
//                 Swal.showLoading();
//               }
//             });
//             $btn.prop('disabled', true);
//           },
//           success: function (res) {
//             Swal.fire({
//               icon: 'success',
//               title: 'สำเร็จ!',
//               text: res.message,
//               timer: 2000,
//               showConfirmButton: true
//             });

//             ColorSubTable.ajax.reload(null, false);
//           },
//           error: function (xhr) {
//             $modal.modal('hide');
//             Swal.fire({
//               icon: 'error',
//               title: 'เกิดข้อผิดพลาด!',
//               text: xhr.responseJSON?.message || 'ไม่สามารถบันทึกข้อมูลได้'
//             });
//           },
//           complete: function () {
//             $btn.prop('disabled', false);
//           }
//         });
//       });
//   });
// });

//delete fin
$(document).on('click', '.btnDeleteColorSub', function () {
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
        url: '/color/' + id,
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

            ColorSubTable.ajax.reload(null, false);
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

//input : get sub model
$(document).on('change', '#model_id', function () {
  const modelId = $(this).val();
  const $subModelSelect = $('#subcarmodel_id');

  $subModelSelect.empty().append('<option value="">-- เลือกรุ่นรถย่อย --</option>');

  if (!modelId) return;

  $.ajax({
    url: '/api/color/sub-model/' + modelId,
    type: 'GET',
    success: function (data) {
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
