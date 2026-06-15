$.ajaxSetup({
  headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

// ============================================================
//  LIST PAGE
// ============================================================
if ($('.gwmIncentiveTable').length) {
  let gwmIncentiveTable;

  function getFilterParams() {
    return {
      month: $('#filterMonth').val() || window.gwmIncentiveCurrentMonth,
      year: $('#filterYear').val() || window.gwmIncentiveCurrentYear
    };
  }

  $(document).ready(function () {
    if ($.fn.DataTable.isDataTable('.gwmIncentiveTable')) {
      $('.gwmIncentiveTable').DataTable().destroy();
    }

    gwmIncentiveTable = $('.gwmIncentiveTable').DataTable({
      ajax: {
        url: '/gwm-incentive/list',
        data: function (d) {
          const f = getFilterParams();
          d.month = f.month;
          d.year = f.year;
        }
      },
      columns: [
        { data: 'No' },
        { data: 'car' },
        { data: 'fixed' },
        { data: 'lt70' },
        { data: 'gte70_lte85' },
        { data: 'gt85_lte100' },
        { data: 'gt100_lte120' },
        { data: 'gte120' },
        { data: 'max_val' },
        { data: 'monthly_target' },
        { data: 'Action', orderable: false, searchable: false }
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
  });

  // Filter
  $(document).on('click', '#btnFilterIncentive', function () {
    gwmIncentiveTable.ajax.reload();
  });

  // Top "เพิ่ม" button → navigate to create page
  $(document).on('click', '.btnInputGwmIncentive', function () {
    const f = getFilterParams();
    window.location.href = '/gwm-incentive/create?month=' + f.month + '&year=' + f.year;
  });

  // Row "เพิ่ม" button → navigate with pre-filled sub_id
  $(document).on('click', '.btnAddGwmIncentive', function () {
    const subId = $(this).data('sub-id');
    const month = $(this).data('month');
    const year = $(this).data('year');
    window.location.href = '/gwm-incentive/create?sub_id=' + subId + '&month=' + month + '&year=' + year;
  });

  // blur focus
  $(document).on('hide.bs.modal', '.editGwmIncentive', function () {
    setTimeout(() => {
      document.activeElement.blur();
      $('body').trigger('focus');
    }, 1);
  });

  // EDIT modal
  $(document).on('click', '.btnEditGwmIncentive', function () {
    const id = $(this).data('id');
    const $btn = $(this);

    $.get('/gwm-incentive/' + id + '/edit', function (html) {
      $('.editGwmIncentiveModal').html(html);
      const $modal = $('.editGwmIncentive');
      $modal.modal('show');

      $modal
        .find('.btnUpdateGwmIncentive')
        .off('click')
        .on('click', function (e) {
          e.preventDefault();
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
              Swal.fire({ icon: 'success', title: 'สำเร็จ!', text: res.message, timer: 2000, showConfirmButton: true });
              gwmIncentiveTable.ajax.reload(null, false);
            },
            error: function (xhr) {
              $modal.modal('hide');
              Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด!',
                text: xhr.responseJSON?.message || 'ไม่สามารถบันทึกข้อมูลได้'
              });
            },
            complete: function () {
              $btn.prop('disabled', false);
            }
          });
        });
    });
  });

  // DELETE
  $(document).on('click', '.btnDeleteGwmIncentive', function () {
    const id = $(this).data('id');
    Swal.fire({
      title: 'คุณแน่ใจหรือไม่?',
      text: 'ต้องการลบข้อมูลนี้ใช่หรือไม่?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#6c5ffc',
      cancelButtonColor: '#d33',
      confirmButtonText: 'ใช่, ลบเลย!',
      cancelButtonText: 'ยกเลิก'
    }).then(result => {
      if (!result.isConfirmed) return;
      $.ajax({
        url: '/gwm-incentive/' + id,
        type: 'DELETE',
        success: function (res) {
          if (res.success) {
            Swal.fire({ icon: 'success', title: 'สำเร็จ', text: res.message, timer: 2000, showConfirmButton: true });
            gwmIncentiveTable.ajax.reload(null, false);
          }
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
}

// ============================================================
//  CREATE PAGE
// ============================================================
if ($('.gwm-create-page').length) {
  let selectedSubId = window.gwmCreatePreSubId || null;
  const fieldNames = [
    'fixed',
    'lt70',
    'gte70_lte85',
    'gt85_lte100',
    'gt100_lte120',
    'gte120',
    'max_val',
    'monthly_target'
  ];

  function getCreatePeriod() {
    return {
      month: $('#createMonth').val(),
      year: $('#createYear').val()
    };
  }

  function resetFields() {
    fieldNames.forEach(f => $('#field_' + f).val('0'));
    $('#formStatusMsg').text('');
  }

  function loadExisting(subId) {
    const p = getCreatePeriod();
    $.get(window.gwmIncentiveCheckUrl, { sub_id: subId, month: p.month, year: p.year }, function (res) {
      if (res.data) {
        // มีข้อมูลอยู่แล้ว → pre-fill และแจ้ง
        fieldNames.forEach(f => $('#field_' + f).val(res.data[f] ?? 0));
        $('#formStatusMsg').html(
          '<span class="text-warning"><i class="bx bx-info-circle me-1"></i>มีข้อมูลเดือนนี้แล้ว — บันทึกจะ<strong>อัปเดต</strong>ข้อมูลเดิม</span>'
        );
        // เก็บ id ไว้เพื่อ update
        $('#gwmCreateForm').data('existing-id', res.data.id);
      } else {
        resetFields();
        $('#gwmCreateForm').removeData('existing-id');
      }
    });
  }

  function selectSubmodel(subId, subName) {
    selectedSubId = subId;

    // highlight
    $('.gwm-sub-item').removeClass('active');
    $('.gwm-sub-item[data-sub-id="' + subId + '"]').addClass('active');

    // update form
    $('#formSubId').val(subId);
    $('#formCarTitle').text(subName);
    $('#formFields').show();
    $('#formPlaceholder').hide();

    syncPeriodToForm();
    loadExisting(subId);
  }

  function syncPeriodToForm() {
    const p = getCreatePeriod();
    $('#formMonth').val(p.month);
    $('#formYear').val(p.year);
  }

  // click submodel item
  $(document).on('click', '.gwm-sub-item', function () {
    selectSubmodel($(this).data('sub-id'), $(this).data('sub-name'));
  });

  // period change → reload existing data for selected sub
  $(document).on('change', '#createMonth, #createYear', function () {
    syncPeriodToForm();
    if (selectedSubId) loadExisting(selectedSubId);
  });

  // search filter
  $(document).on('input', '#searchSubmodel', function () {
    const q = $(this).val().toLowerCase();
    $('.gwm-sub-item').each(function () {
      const name = $(this).data('sub-name').toLowerCase();
      $(this).toggle(name.includes(q));
    });
    // hide model group header if all children hidden
    $('.gwm-model-group').each(function () {
      const anyVisible = $(this).nextUntil('.gwm-model-group', '.gwm-sub-item').filter(':visible').length > 0;
      $(this).toggle(anyVisible);
    });
  });

  // Reset
  $(document).on('click', '#btnResetFields', function () {
    resetFields();
    $('#gwmCreateForm').removeData('existing-id');
  });

  // Save
  $(document).on('click', '#btnSaveIncentive', function () {
    if (!selectedSubId) {
      Swal.fire({ icon: 'warning', title: 'แจ้งเตือน', text: 'กรุณาเลือกรุ่นรถก่อน' });
      return;
    }

    const existingId = $('#gwmCreateForm').data('existing-id');
    const $btn = $(this);

    let url = window.gwmIncentiveStoreUrl;
    let method = 'POST';

    // ถ้ามีข้อมูลเดิม → update แทน
    if (existingId) {
      url = '/gwm-incentive/' + existingId;
      method = 'POST'; // Laravel ใช้ _method=PUT
      if (!$('#gwmCreateForm').find('input[name="_method"]').length) {
        $('#gwmCreateForm').append('<input type="hidden" name="_method" value="PUT">');
      }
    } else {
      $('#gwmCreateForm').find('input[name="_method"]').remove();
    }

    const formData = new FormData($('#gwmCreateForm')[0]);

    Swal.fire({ title: 'กำลังบันทึก...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    $btn.prop('disabled', true);

    $.ajax({
      url: url,
      type: method,
      data: formData,
      processData: false,
      contentType: false,
      success: function (res) {
        Swal.fire({ icon: 'success', title: 'สำเร็จ', text: res.message, timer: 2000, showConfirmButton: true });
        // อัปเดต existing id ใน case ที่ยังอยู่หน้าเดิม
        if (res.id) $('#gwmCreateForm').data('existing-id', res.id);
        loadExisting(selectedSubId);
      },
      error: function (xhr) {
        Swal.fire({
          icon: 'error',
          title: 'เกิดข้อผิดพลาด',
          text: xhr.responseJSON?.message || 'ไม่สามารถบันทึกข้อมูลได้'
        });
      },
      complete: function () {
        $btn.prop('disabled', false);
      }
    });
  });

  // Auto-select ถ้ามี preSubId
  $(document).ready(function () {
    if (window.gwmCreatePreSubId) {
      const $target = $('.gwm-sub-item[data-sub-id="' + window.gwmCreatePreSubId + '"]');
      if ($target.length) {
        selectSubmodel(window.gwmCreatePreSubId, $target.data('sub-name'));
        $target[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    }
  });

  // Auto-select only (no KPI load here)
}

// ============================================================
//  VIEW PAGE (inline editable table + KPI)
// ============================================================
if ($('.gwm-view-page').length) {
  function saveRow($row) {
    if ($row.data('saving')) return;
    $row.data('saving', true);

    const data = {
      _token: $('meta[name="csrf-token"]').attr('content'),
      subcarmodel_id: $row.data('sub-id'),
      month: $row.data('month'),
      year: $row.data('year')
    };

    $row.find('.gwm-row-input').each(function () {
      data[$(this).attr('name')] = $(this).val();
    });

    $row.removeClass('row-saved row-error');

    $.ajax({
      url: window.gwmIncentiveUpsertUrl,
      type: 'POST',
      data: data,
      success: function () {
        $row.addClass('row-saved');
        setTimeout(() => $row.removeClass('row-saved'), 1500);
      },
      error: function (xhr) {
        $row.addClass('row-error');
        setTimeout(() => $row.removeClass('row-error'), 1500);
        Swal.fire({
          icon: 'error',
          title: 'เกิดข้อผิดพลาด',
          text: xhr.responseJSON?.message || 'ไม่สามารถบันทึกข้อมูลได้'
        });
      },
      complete: function () {
        $row.data('saving', false);
      }
    });
  }

  // Auto-save เมื่อ focus ออกจาก row (debounce เพื่อรอกรณี tab ระหว่าง input ใน row เดียวกัน)
  $(document).on('blur', '.gwm-row-input', function () {
    const $row = $(this).closest('tr.gwm-row');
    clearTimeout($row.data('blur-timer'));
    $row.data(
      'blur-timer',
      setTimeout(function () {
        if ($row.find(':focus').length === 0) {
          saveRow($row);
        }
      }, 150)
    );
  });

  // Save KPI
  $(document).on('click', '#btnSaveKpi', function () {
    const $btn = $(this);
    const formData = new FormData($('#gwmKpiForm')[0]);

    Swal.fire({ title: 'กำลังบันทึก KPI...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    $btn.prop('disabled', true);

    $.ajax({
      url: window.gwmKpiStoreUrl,
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function (res) {
        Swal.fire({ icon: 'success', title: 'สำเร็จ', text: res.message, timer: 1500, showConfirmButton: true });
      },
      error: function (xhr) {
        Swal.fire({
          icon: 'error',
          title: 'เกิดข้อผิดพลาด',
          text: xhr.responseJSON?.message || 'ไม่สามารถบันทึก KPI ได้'
        });
      },
      complete: function () {
        $btn.prop('disabled', false);
      }
    });
  });
}
