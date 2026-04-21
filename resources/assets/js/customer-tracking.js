$.ajaxSetup({
  headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

// list
$(document).ready(function () {
  if (!$('#trackingTable').length) return;

  const table = $('#trackingTable').DataTable({
    ajax: { url: '/customer-tracking/list' },
    columns: [
      { data: 'No', orderable: false },
      { data: 'FullName' },
      { data: 'model' },
      { data: 'sale' },
      { data: 'detail' },
      {
        data: 'id', orderable: false, searchable: false,
        render: function (id) {
          return `
            <div class="d-flex gap-1">
              <a href="/customer-tracking/${id}" class="btn btn-icon btn-info text-white">
                <i class="bx bx-show"></i>
              </a>
              <button class="btn btn-icon btn-danger text-white btnDeleteTracking" data-id="${id}">
                <i class="bx bx-trash"></i>
              </button>
            </div>`;
        }
      }
    ],
    paging: true,
    searching: true,
    ordering: true,
    info: true,
    pageLength: 10,
    autoWidth: false,
    language: {
      search: 'ค้นหา:',
      lengthMenu: 'แสดง _MENU_ รายการ',
      info: 'แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ',
      paginate: { previous: 'ก่อนหน้า', next: 'ถัดไป' },
      emptyTable: 'ไม่มีข้อมูล',
      zeroRecords: 'ไม่พบข้อมูล'
    }
  });

  // ลบ
  let deleteId = null;
  $(document).on('click', '.btnDeleteTracking', function () {
    deleteId = $(this).data('id');
    $('#modalConfirmDelete').modal('show');
  });

  $('#btnConfirmDelete').on('click', function () {
    if (!deleteId) return;
    $.ajax({
      url: `/customer-tracking/${deleteId}`,
      type: 'DELETE',
      success: function () {
        $('#modalConfirmDelete').modal('hide');
        table.ajax.reload();
        deleteId = null;
      },
      error: function () {
        alert('เกิดข้อผิดพลาด ไม่สามารถลบได้');
      }
    });
  });
});

// INPUT FORM — customer search + car cascades
$(document).ready(function () {
  if (!$('#customerSearch').length) return;

  // --- Customer Search ---
  const $modal = $('#modalSearchCustomer');
  const $tableBody = $('#tableSelectCustomer tbody');

  function searchCustomer(keyword) {
    if (!keyword.trim()) return;
    $.get('/customers/search', { keyword }, function (res) {
      $tableBody.empty();
      if (!res.length) {
        $tableBody.append('<tr><td colspan="4" class="text-center">ไม่พบข้อมูลลูกค้า</td></tr>');
      } else {
        res.forEach(c => {
          $tableBody.append(`
            <tr>
              <td>${c.PrefixNameTH ?? ''} ${c.FirstName ?? ''} ${c.LastName ?? ''}</td>
              <td>${c.formatted_mobile ?? '-'}</td>
              <td>${c.formatted_id_number ?? '-'}</td>
              <td>
                <button class="btn btn-sm btn-primary btnSelectCustomer"
                  data-id="${c.id}"
                  data-name="${(c.PrefixNameTH ?? '') + ' ' + (c.FirstName ?? '') + ' ' + (c.LastName ?? '')}"
                  data-mobile="${c.formatted_mobile ?? ''}"
                  data-idnumber="${c.formatted_id_number ?? ''}">
                  เลือก
                </button>
              </td>
            </tr>`);
        });
      }
      $modal.modal('show');
    });
  }

  $('#customerSearch').on('keypress', function (e) {
    if (e.which === 13) { e.preventDefault(); searchCustomer($(this).val()); }
  });

  $('.btnSearchCustomer').on('click', function () {
    searchCustomer($('#customerSearch').val());
  });

  $(document).on('click', '.btnSelectCustomer', function () {
    const d = $(this).data();
    $('#CusID').val(d.id);

    const setDisplay = (id, val) => {
      const el = document.getElementById(id);
      if (!el) return;
      el.textContent = val || '—';
      el.classList.toggle('empty', !val);
    };
    setDisplay('customerName-display', d.name);
    setDisplay('customerID-display', d.idnumber);
    setDisplay('customerPhone-display', d.mobile);

    $modal.modal('hide');
    $('#customerSearch').val('');
  });

  $(document).on('hide.bs.modal', '#modalSearchCustomer', function () {
    setTimeout(() => { document.activeElement.blur(); $('body').trigger('focus'); }, 1);
  });

  // --- Car Cascades ---
  $('#model_id').on('change', function () {
    const modelId = $(this).val();
    const $sub = $('#sub_model_id');
    const $year = $('#year');
    const $color = $('#color_id');

    $sub.prop('disabled', true).empty().append('<option value="">— เลือกรุ่นรถย่อย —</option>');
    $year.prop('disabled', true).empty().append('<option value="">— เลือกปี —</option>');
    $color.prop('disabled', true).empty().append('<option value="">— เลือกสี —</option>');

    if (!modelId) return;

    $.get('/api/purchase-order/sub-model/' + modelId, function (res) {
      res.forEach(s => $sub.append(`<option value="${s.id}">${s.name}</option>`));
      $sub.prop('disabled', false);
    });
  });

  $('#sub_model_id').on('change', function () {
    const subModelId = $(this).val();
    const $year = $('#year');
    const $color = $('#color_id');

    $year.prop('disabled', true).empty().append('<option value="">— เลือกปี —</option>');
    $color.prop('disabled', true).empty().append('<option value="">— เลือกสี —</option>');

    if (!subModelId) return;

    // โหลดสี
    $.get('/api/car-order/color', { sub_model_id: subModelId }, function (data) {
      if (data && data.length) {
        data.forEach(c => $color.append(`<option value="${c.id}">${c.name}</option>`));
        $color.prop('disabled', false);
      }
    });

    // โหลดปี
    $.get('/api/car-order/pricelist-options', { sub_model_id: subModelId }, function (res) {
      if (!res.data || !res.data.length) return;
      res.data.forEach(r => $year.append(`<option value="${r.year}">${r.year}</option>`));
      $year.prop('disabled', false);
    });
  });
});

// INPUT FORM — save tracking
document.addEventListener('DOMContentLoaded', function () {
  $(document).on('click', '.btnSaveTracking', function (e) {
    e.preventDefault();

    const $btn = $(this);
    const form = $btn.closest('form')[0];

    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    if (!$('#CusID').val()) {
      Swal.fire({
        icon: 'warning',
        title: 'ยังไม่ได้เลือกลูกค้า',
        text: 'กรุณาค้นหาและเลือกลูกค้าก่อนบันทึก',
        confirmButtonText: 'ตกลง'
      }).then(() => {
        $('#customerSearch').focus();
      });
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
        Swal.fire({
          title: 'กำลังบันทึกข้อมูล...',
          text: 'กรุณารอสักครู่',
          allowOutsideClick: false,
          didOpen: () => { Swal.showLoading(); }
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
        setTimeout(() => { window.location.href = '/customer-tracking'; }, 1000);
      },
      error: function (xhr) {
        let errMsg = 'ไม่สามารถบันทึกข้อมูลได้';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          errMsg = xhr.responseJSON.message;
        }
        Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: errMsg });
      },
      complete: function () {
        $btn.prop('disabled', false);
      }
    });
  });
});

// VIEW-MORE — เพิ่มการติดตาม (modal)
$(document).ready(function () {
  $('#btnSaveDetail').on('click', function () {
    const trackingId = $(this).data('tracking-id');
    const contactDate = $('#add_contact_date').val();
    const contactStatus = $('input[name="add_contact_status"]:checked').val();
    const decisionId = $('#add_decision_id').val();
    const commentSale = $('#add_comment_sale').val();

    if (!contactDate) {
      alert('กรุณาระบุวันที่ติดต่อ');
      return;
    }

    $.ajax({
      url: `/customer-tracking/${trackingId}/detail`,
      type: 'POST',
      data: {
        contact_date: contactDate,
        contact_status: contactStatus,
        decision_id: decisionId,
        comment_sale: commentSale,
      },
      success: function () {
        location.reload();
      },
      error: function () {
        alert('เกิดข้อผิดพลาด ไม่สามารถบันทึกได้');
      }
    });
  });
});
