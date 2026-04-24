$.ajaxSetup({
  headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

// list
$(document).ready(function () {
  if (!$('#trackingTable').length) return;

  const table = $('#trackingTable').DataTable({
    ajax: { url: '/customer-tracking/list' },
    columns: [
      { data: 'No', orderable: false, searchable: false },
      { data: 'FullName' },
      { data: 'model' },
      { data: 'sale' },
      { data: 'detail', orderable: false },
      {
        data: 'id',
        orderable: false,
        searchable: false,
        render: function (id) {
          return `
            <div class="d-flex justify-content-center gap-1">
              <a href="/customer-tracking/${id}" class="btn btn-icon btn-info text-white">
                <i class="bx bx-show"></i>
              </a>
              <button class="btn btn-icon btn-danger text-white btnDeleteTracking" data-id="${id}">
                <i class="bx bx-trash"></i>
              </button>
            </div>`;
        }
      },
      { data: 'decision_id', visible: false, searchable: true }
    ],
    paging: true,
    searching: true,
    ordering: true,
    info: true,
    pageLength: 10,
    autoWidth: false,
    drawCallback: function () {
      this.api()
        .column(0, { search: 'applied', order: 'applied' })
        .nodes()
        .each(function (cell, i) {
          cell.innerHTML = i + 1;
        });
    },
    language: {
      search: 'ค้นหา:',
      lengthMenu: 'แสดง _MENU_ รายการ',
      info: 'แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ',
      paginate: { previous: 'ก่อนหน้า', next: 'ถัดไป' },
      emptyTable: 'ไม่มีข้อมูล',
      zeroRecords: 'ไม่พบข้อมูล'
    }
  });

  $('#filterDecision').on('change', function () {
    const val = $(this).val();
    table
      .column(6)
      .search(val === '' ? '' : '^' + val + '$', true, false)
      .draw();
  });

  // ลบ
  $(document).on('click', '.btnDeleteTracking', function () {
    const id = $(this).data('id');
    Swal.fire({
      title: 'คุณแน่ใจหรือไม่?',
      text: 'ต้องการลบรายการนี้ออกจากการติดตามใช่หรือไม่?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#6c5ffc',
      cancelButtonColor: '#d33',
      confirmButtonText: 'ใช่, ลบเลย!',
      cancelButtonText: 'ยกเลิก'
    }).then(result => {
      if (!result.isConfirmed) return;
      $.ajax({
        url: `/customer-tracking/${id}`,
        type: 'DELETE',
        success: function () {
          Swal.fire({
            icon: 'success',
            title: 'สำเร็จ',
            text: 'ลบรายการเรียบร้อยแล้ว',
            timer: 1500,
            showConfirmButton: false
          });
          table.ajax.reload();
        },
        error: function (xhr) {
          const msg = xhr.responseJSON?.message ?? 'ไม่สามารถลบข้อมูลได้';
          Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: msg });
        }
      });
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
    $.get('/customers/search', { keyword, check_salecar: 1 }, function (res) {
      $tableBody.empty();
      if (!res.length) {
        $tableBody.append('<tr><td colspan="4" class="text-center">ไม่พบข้อมูลลูกค้า</td></tr>');
      } else {
        res.forEach(c => {
          const actionBtn = c.has_active_salecar
            ? `<span class="badge bg-secondary">มีการจองแล้ว</span>`
            : `<button class="btn btn-sm btn-primary btnSelectCustomer"
                data-id="${c.id}"
                data-name="${(c.PrefixNameTH ?? '') + ' ' + (c.FirstName ?? '') + ' ' + (c.LastName ?? '')}"
                data-mobile="${c.formatted_mobile ?? ''}"
                data-idnumber="${c.formatted_id_number ?? ''}">
                เลือก
              </button>`;
          $tableBody.append(`
            <tr>
              <td>${c.PrefixNameTH ?? ''} ${c.FirstName ?? ''} ${c.LastName ?? ''}</td>
              <td>${c.formatted_mobile ?? '-'}</td>
              <td>${c.formatted_id_number ?? '-'}</td>
              <td>${actionBtn}</td>
            </tr>`);
        });
      }
      $modal.modal('show');
    });
  }

  $('#customerSearch').on('keypress', function (e) {
    if (e.which === 13) {
      e.preventDefault();
      searchCustomer($(this).val());
    }
  });

  $('.btnSearchCustomer').on('click', function () {
    searchCustomer($('#customerSearch').val());
  });

  $(document).on('click', '.btnSelectCustomer', function () {
    const d = $(this).data();

    $.get('/customer-tracking/check-duplicate', { customer_id: d.id }, function (res) {
      if (res.exists) {
        Swal.fire({
          icon: 'warning',
          title: 'มีข้อมูลการติดตามอยู่แล้ว',
          text: `${d.name} มีข้อมูลการติดตามอยู่ในระบบแล้ว ไม่สามารถเพิ่มซ้ำได้`,
          confirmButtonText: 'ตกลง'
        });
        return;
      }

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
  });

  $(document).on('hide.bs.modal', '#modalSearchCustomer', function () {
    setTimeout(() => {
      document.activeElement.blur();
      $('body').trigger('focus');
    }, 1);
  });

  // --- Car Cascades ---
  $('#model_id').on('change', function () {
    const modelId = $(this).val();
    const $sub = $('#sub_model_id');

    $sub.prop('disabled', true).empty().append('<option value="">— เลือกรุ่นรถย่อย —</option>');
    resetTrackingCarFields();

    if (!modelId) return;

    $.get('/api/purchase-order/sub-model/' + modelId, function (res) {
      if (res.length > 0) {
        res.forEach(s => {
          let text = s.detail ? `${s.detail} - ${s.name}` : s.name;
          $sub.append(`<option value="${s.id}">${text}</option>`);
        });
        $sub.prop('disabled', false);
      } else {
        $sub.append('<option value="">— ไม่มีรุ่นย่อย —</option>');
      }
    });
  });

  function resetTrackingCarFields() {
    $('#year').prop('disabled', true).empty().append('<option value="">— เลือกปี —</option>');
    $('#color_id').prop('disabled', true).empty().append('<option value="">— เลือกสี —</option>');
    $('#pricelist_color').prop('disabled', true).empty().append('<option value="">— เลือก —</option>');
    $('#option').val('');
  }

  $('#sub_model_id').on('change', function () {
    const subModelId = $(this).val();
    resetTrackingCarFields();

    if (!subModelId) return;

    // Brand 2 (GWM) และ Others: โหลดสีจาก color_id
    const $color = $('#color_id');
    if ($color.length) {
      $.get('/api/car-order/color', { sub_model_id: subModelId }, function (data) {
        if (data && data.length) {
          data.forEach(c => $color.append(`<option value="${c.id}">${c.name}</option>`));
          $color.prop('disabled', false);
        }
      });
    }

    // โหลดปี หรือ ประเภทสี (Brand 1 Mitsubishi)
    $.get('/api/car-order/pricelist-options', { sub_model_id: subModelId }, function (res) {
      if (!res.data || !res.data.length) return;

      if (res.type === 'color_year') {
        // Brand 1: แสดง pricelist_color ก่อน ปีจะโหลดหลังเลือกสี
        const colors = [...new Set(res.data.map(r => r.color))];
        const $colorSel = $('#pricelist_color');
        $colorSel.empty().append('<option value="">— เลือก —</option>');
        colors.forEach(c => $colorSel.append(`<option value="${c}">${c}</option>`));
        $colorSel.prop('disabled', false).data('pricelistRows', res.data);
      } else {
        // Brand 2, 3: แสดง year เลย
        res.data.forEach(r => $('#year').append(`<option value="${r.year}">${r.year}</option>`));
        $('#year').prop('disabled', false);
      }
    });
  });

  // Brand 1 (Mitsubishi): เลือกประเภทสีแล้วโหลดปี
  $('#pricelist_color').on('change', function () {
    const selectedColor = $(this).val();
    const rows = $(this).data('pricelistRows') || [];
    const $yearSel = $('#year');

    $yearSel.prop('disabled', true).empty().append('<option value="">— เลือกปี —</option>');
    $('#option').val('');
    if (!selectedColor) return;

    const filtered = rows.filter(r => r.color === selectedColor);
    const years = [...new Set(filtered.map(r => r.year))];
    years.forEach(y => $yearSel.append(`<option value="${y}">${y}</option>`));
    $yearSel.prop('disabled', false);
  });

  // Brand 1 (Mitsubishi): เลือกปีแล้วโหลด option
  $('#year').on('change', function () {
    const $option = $('#option');
    if (!$option.length) return;

    const subModelId = $('#sub_model_id').val();
    const year = $(this).val();
    const color = $('#pricelist_color').val() || '';

    $option.val('');
    if (!subModelId || !year) return;

    $.get('/api/car-order/pricelist-data', { sub_model_id: subModelId, year: year, color: color }, function (data) {
      if (data) $option.val(data.option ?? '');
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
          didOpen: () => {
            Swal.showLoading();
          }
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
        setTimeout(() => {
          window.location.href = '/customer-tracking';
        }, 1000);
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
  // เปิด modal + ตั้งค่า entry_type + เปลี่ยน title
  $(document).on('click', '.btnOpenAddDetail', function () {
    const entryType = $(this).data('entry-type');
    $('#add_entry_type').val(entryType);

    if (entryType === 'sale') {
      $('#modalAddDetailTitle').text('เพิ่มบันทึกเซลล์');
      $('#modalAddDetailSub').text('Sale Tracking Detail');
      $('#modalAddDetailIcon').attr('class', 'bx bx-notepad fs-5 text-white');
    } else {
      $('#modalAddDetailTitle').text('เพิ่มบันทึกผู้จัดการ');
      $('#modalAddDetailSub').text('Manager Tracking Detail');
      $('#modalAddDetailIcon').attr('class', 'bx bx-briefcase fs-5 text-white');
    }

    $('#add_contact_date').val(new Date().toISOString().split('T')[0]);
    $('#add_decision_id').val('');
    $('#add_comment_sale').val('');
    $('#addContactYes').prop('checked', true);

    $('#modalAddDetail').modal('show');
  });

  $('#btnSaveDetail').on('click', function () {
    const trackingId = $(this).data('tracking-id');
    const contactDate = $('#add_contact_date').val();
    const contactStatus = $('input[name="add_contact_status"]:checked').val();
    const decisionId = $('#add_decision_id').val();
    const commentSale = $('#add_comment_sale').val();
    const entryType = $('#add_entry_type').val();

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
        entry_type: entryType
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

// VIEW-MORE — ยกเลิกการติดตาม
$(document).ready(function () {
  $('#btnCancelTracking').on('click', function () {
    const trackingId = $(this).data('id');

    Swal.fire({
      title: 'ยกเลิกการติดตาม?',
      html: 'ต้องการยกเลิกการติดตามลูกค้ารายนี้ใช่หรือไม่?<br><small class="text-muted">รายการนี้จะไม่แสดงในหน้ารายการติดตามอีกต่อไป</small>',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#6c5ffc',
      cancelButtonColor: '#d33',
      confirmButtonText: 'ยืนยัน',
      cancelButtonText: 'ไม่ใช่',
    }).then(result => {
      if (!result.isConfirmed) return;

      $.ajax({
        url: `/customer-tracking/${trackingId}/cancel`,
        type: 'POST',
        success: function () {
          Swal.fire({
            icon: 'success',
            title: 'ยกเลิกการติดตามเรียบร้อยแล้ว',
            timer: 1500,
            showConfirmButton: true,
          });
          setTimeout(() => {
            window.location.href = '/customer-tracking';
          }, 1500);
        },
        error: function () {
          Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: 'ไม่สามารถยกเลิกการติดตามได้' });
        },
      });
    });
  });
});

// VIEW-MORE — แก้ไขบันทึกผู้จัดการ (รวม continue tracking)
$(document).ready(function () {
  $(document).on('click', '.btnEditDetail', function () {
    const d = $(this).data();

    $('#edit_detail_id').val(d.id);
    $('#edit_contact_date_display').text(d.contactDate);
    $('#edit_decision_display').text(d.decision);
    $('#edit_comment_sale').val(d.comment);
    $('#edit_continue_decision_id').val('');

    if (parseInt(d.contactStatus) === 1) {
      $('#editContactYes').prop('checked', true);
    } else {
      $('#editContactNo').prop('checked', true);
    }

    // แสดงส่วน "ติดตามต่อ" เฉพาะ entry ที่เป็น checkpoint
    if (parseInt(d.isCheckpoint) === 1) {
      $('#editContinueSection').show();
    } else {
      $('#editContinueSection').hide();
    }

    $('#modalEditDetail').modal('show');
  });

  $('#edit_continue_decision_id').on('change', function () {
    const val = parseInt($(this).val());
    if (val === 1 || val === 2) {
      $('#editContinueDateWrapper').hide();
      $('#editContinueAutoHint').show();
    } else if ($(this).val()) {
      $('#editContinueDateWrapper').show();
      $('#edit_continue_date').val(new Date().toISOString().split('T')[0]);
      $('#editContinueAutoHint').hide();
    } else {
      $('#editContinueDateWrapper').hide();
      $('#editContinueAutoHint').hide();
    }
  });

  $('#btnSaveEditDetail').on('click', function () {
    const detailId = $('#edit_detail_id').val();
    const contactStatus = $('input[name="edit_contact_status"]:checked').val();
    const commentSale = $('#edit_comment_sale').val();
    const continueDecisionId = $('#edit_continue_decision_id').val();
    const continueDate = $('#edit_continue_date').val();

    if (continueDecisionId && ![1, 2].includes(parseInt(continueDecisionId)) && !continueDate) {
      Swal.fire({ icon: 'warning', title: 'กรุณาระบุวันที่ติดตาม', confirmButtonText: 'ตกลง' });
      return;
    }

    $.ajax({
      url: `/customer-tracking/detail/${detailId}`,
      type: 'PUT',
      data: { contact_status: contactStatus, comment_sale: commentSale },
      success: function () {
        if (continueDecisionId) {
          $.ajax({
            url: `/customer-tracking/detail/${detailId}/continue`,
            type: 'POST',
            data: { decision_id: continueDecisionId, contact_date: continueDate },
            success: function () {
              location.reload();
            },
            error: function () {
              Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                text: 'บันทึกแล้วแต่ไม่สามารถสร้างรายการติดตามต่อได้'
              });
            }
          });
        } else {
          location.reload();
        }
      },
      error: function () {
        alert('เกิดข้อผิดพลาด ไม่สามารถบันทึกได้');
      }
    });
  });
});

// blur focus viewCust
$(document).on('hide.bs.modal', '#modalEditDetail', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});