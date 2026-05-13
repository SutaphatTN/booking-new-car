$.ajaxSetup({
  headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

// ── Tracking table column filter ──
let ctTrackingTable;
let ctSaleFilterActive = null, ctAllSaleNames = [];
let ctStatusFilterActive = null, ctAllStatusNames = [];
let ctLastDateFilterActive = null, ctAllLastDates = [];
let ctNextDateFilterActive = null, ctAllNextDates = [];

const ctDmyToKey = d => { const p = d.split('-'); return `${p[2]}-${p[1]}-${p[0]}`; };

$.fn.dataTable.ext.search.push(function (settings, data) {
  if (settings.nTable.id !== 'trackingTable') return true;
  const saleOk = ctSaleFilterActive === null
    || (ctSaleFilterActive.length > 0 && ctSaleFilterActive.includes(data[3] || ''));
  const lastDateOk = ctLastDateFilterActive === null
    || (ctLastDateFilterActive.length > 0 && ctLastDateFilterActive.includes(data[4] || ''));
  const nextDateOk = ctNextDateFilterActive === null
    || (ctNextDateFilterActive.length > 0 && ctNextDateFilterActive.includes(data[5] || ''));
  const statusOk = ctStatusFilterActive === null
    || (ctStatusFilterActive.length > 0 && ctStatusFilterActive.includes(data[6] || ''));
  return saleOk && lastDateOk && nextDateOk && statusOk;
});

function ctRefreshNames() {
  const saleSeen = new Set(), statusSeen = new Set();
  const lastDateSeen = new Set(), nextDateSeen = new Set();
  ctTrackingTable.rows().data().each(function (row) {
    if (row.sale) saleSeen.add(row.sale);
    if (row.status) statusSeen.add(row.status);
    if (row.last_date && row.last_date !== '-') lastDateSeen.add(row.last_date);
    if (row.next_date && row.next_date !== '-') nextDateSeen.add(row.next_date);
  });
  ctAllSaleNames = Array.from(saleSeen).sort((a, b) => a.localeCompare(b, 'th'));
  ctAllStatusNames = Array.from(statusSeen).sort((a, b) => a.localeCompare(b, 'th'));
  ctAllLastDates = Array.from(lastDateSeen).sort((a, b) => ctDmyToKey(a).localeCompare(ctDmyToKey(b)));
  ctAllNextDates = Array.from(nextDateSeen).sort((a, b) => ctDmyToKey(a).localeCompare(ctDmyToKey(b)));
}

function ctBuildList($list, allNames, activeFilter, chkClass, idPfx) {
  $list.empty();
  const allSelected = activeFilter === null;
  $list.append(
    `<div class="col-filter-item col-filter-all">
      <input type="checkbox" id="${idPfx}ChkAll" ${allSelected ? 'checked' : ''}>
      <label for="${idPfx}ChkAll">(เลือกทั้งหมด)</label>
    </div>`
  );
  allNames.forEach(function (name, i) {
    const chk = allSelected || (activeFilter !== null && activeFilter.includes(name)) ? 'checked' : '';
    $list.append(
      `<div class="col-filter-item">
        <input type="checkbox" class="${chkClass}" id="${idPfx}${i}" value="${name}" ${chk}>
        <label for="${idPfx}${i}">${name}</label>
      </div>`
    );
  });
  ctSyncAll(idPfx + 'ChkAll', chkClass);
}

function ctSyncAll(allId, itemClass) {
  const $items = $('.' + itemClass + ':visible');
  const total = $items.length, checked = $items.filter(':checked').length;
  const $all = $('#' + allId);
  if (total === 0 || checked === 0) $all.prop({ indeterminate: false, checked: false });
  else if (checked === total) $all.prop({ indeterminate: false, checked: true });
  else $all.prop({ indeterminate: true, checked: false });
}

// list
$(document).ready(function () {
  if (!$('#trackingTable').length) return;

  ctTrackingTable = $('#trackingTable').DataTable({
    ajax: { url: '/customer-tracking/list' },
    columns: [
      { data: 'No' },
      { data: 'FullName', orderable: false },
      { data: 'model', orderable: false },
      { data: 'sale', orderable: false },
      { data: 'last_date', orderable: false },
      {
        data: 'next_date',
        orderable: true,
        render: function (data, type, row) {
          if (type === 'sort') return row.next_date_sort;
          return data;
        }
      },
      { data: 'status', orderable: false },
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
    order: [[5, 'asc']],
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
        .each(function (cell, i) { cell.innerHTML = i + 1; });
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

  ctTrackingTable.on('init.dt', function () {
    ctRefreshNames();
    ctBuildList($('#ctSaleFilterList'), ctAllSaleNames, ctSaleFilterActive, 'ct-sale-chk', 'ctSale');
    ctBuildList($('#ctLastDateFilterList'), ctAllLastDates, ctLastDateFilterActive, 'ct-last-date-chk', 'ctLastDate');
    ctBuildList($('#ctNextDateFilterList'), ctAllNextDates, ctNextDateFilterActive, 'ct-next-date-chk', 'ctNextDate');
    ctBuildList($('#ctStatusFilterList'), ctAllStatusNames, ctStatusFilterActive, 'ct-status-chk', 'ctStatus');
  });

  $('#filterDecision').on('change', function () {
    const val = $(this).val();
    ctTrackingTable.column(8).search(val === '' ? '' : '^' + val + '$', true, false).draw();
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
          Swal.fire({ icon: 'success', title: 'สำเร็จ', text: 'ลบรายการเรียบร้อยแล้ว', timer: 1500, showConfirmButton: false });
          ctTrackingTable.ajax.reload();
        },
        error: function (xhr) {
          const msg = xhr.responseJSON?.message ?? 'ไม่สามารถลบข้อมูลได้';
          Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: msg });
        }
      });
    });
  });

  // ── Filter button toggles ──
  const ctAllDropdowns = '#ctSaleFilterDropdown,#ctLastDateFilterDropdown,#ctNextDateFilterDropdown,#ctStatusFilterDropdown';
  const ctAllBtns      = '#ctSaleFilterBtn,#ctLastDateFilterBtn,#ctNextDateFilterBtn,#ctStatusFilterBtn';

  function ctOpenDropdown($dd, btn, buildFn) {
    $(ctAllDropdowns).not($dd).removeClass('show');
    $(ctAllBtns).not(btn).removeClass('active');
    const rect = btn.getBoundingClientRect();
    $dd.css({ top: (rect.bottom + 4) + 'px', left: rect.left + 'px' }).addClass('show');
    $(btn).addClass('active');
    buildFn();
  }

  $('#ctSaleFilterBtn').on('click', function (e) {
    e.stopPropagation();
    const $dd = $('#ctSaleFilterDropdown');
    if ($dd.hasClass('show')) { $dd.removeClass('show'); $(this).removeClass('active'); return; }
    ctOpenDropdown($dd, this, () => {
      ctBuildList($('#ctSaleFilterList'), ctAllSaleNames, ctSaleFilterActive, 'ct-sale-chk', 'ctSale');
      $('#ctSaleFilterSearch').val('').trigger('input').focus();
    });
  });

  $('#ctLastDateFilterBtn').on('click', function (e) {
    e.stopPropagation();
    const $dd = $('#ctLastDateFilterDropdown');
    if ($dd.hasClass('show')) { $dd.removeClass('show'); $(this).removeClass('active'); return; }
    ctOpenDropdown($dd, this, () => {
      ctBuildList($('#ctLastDateFilterList'), ctAllLastDates, ctLastDateFilterActive, 'ct-last-date-chk', 'ctLastDate');
      $('#ctLastDateFilterSearch').val('').trigger('input').focus();
    });
  });

  $('#ctNextDateFilterBtn').on('click', function (e) {
    e.stopPropagation();
    const $dd = $('#ctNextDateFilterDropdown');
    if ($dd.hasClass('show')) { $dd.removeClass('show'); $(this).removeClass('active'); return; }
    ctOpenDropdown($dd, this, () => {
      ctBuildList($('#ctNextDateFilterList'), ctAllNextDates, ctNextDateFilterActive, 'ct-next-date-chk', 'ctNextDate');
      $('#ctNextDateFilterSearch').val('').trigger('input').focus();
    });
  });

  $('#ctStatusFilterBtn').on('click', function (e) {
    e.stopPropagation();
    const $dd = $('#ctStatusFilterDropdown');
    if ($dd.hasClass('show')) { $dd.removeClass('show'); $(this).removeClass('active'); return; }
    ctOpenDropdown($dd, this, () => {
      ctBuildList($('#ctStatusFilterList'), ctAllStatusNames, ctStatusFilterActive, 'ct-status-chk', 'ctStatus');
      $('#ctStatusFilterSearch').val('').trigger('input').focus();
    });
  });

  $(document).on('click.ctFilter', function (e) {
    if (!$(e.target).closest(ctAllDropdowns + ',' + ctAllBtns).length) {
      $(ctAllDropdowns).removeClass('show');
      $(ctAllBtns).removeClass('active');
    }
  });

  // Select all
  $(document).on('change', '#ctSaleChkAll',     function () { $('.ct-sale-chk:visible').prop('checked', $(this).is(':checked')); });
  $(document).on('change', '#ctLastDateChkAll', function () { $('.ct-last-date-chk:visible').prop('checked', $(this).is(':checked')); });
  $(document).on('change', '#ctNextDateChkAll', function () { $('.ct-next-date-chk:visible').prop('checked', $(this).is(':checked')); });
  $(document).on('change', '#ctStatusChkAll',   function () { $('.ct-status-chk:visible').prop('checked', $(this).is(':checked')); });

  // Individual → sync header
  $(document).on('change', '.ct-sale-chk',      function () { ctSyncAll('ctSaleChkAll',     'ct-sale-chk'); });
  $(document).on('change', '.ct-last-date-chk', function () { ctSyncAll('ctLastDateChkAll', 'ct-last-date-chk'); });
  $(document).on('change', '.ct-next-date-chk', function () { ctSyncAll('ctNextDateChkAll', 'ct-next-date-chk'); });
  $(document).on('change', '.ct-status-chk',    function () { ctSyncAll('ctStatusChkAll',   'ct-status-chk'); });

  // Search within dropdown
  $(document).on('input', '#ctSaleFilterSearch', function () {
    const q = $(this).val().toLowerCase();
    $('#ctSaleFilterList .col-filter-item:not(.col-filter-all)').each(function () {
      $(this).toggle(!q || $(this).find('.ct-sale-chk').val().toLowerCase().includes(q));
    });
    ctSyncAll('ctSaleChkAll', 'ct-sale-chk');
  });
  $(document).on('input', '#ctLastDateFilterSearch', function () {
    const q = $(this).val().toLowerCase();
    $('#ctLastDateFilterList .col-filter-item:not(.col-filter-all)').each(function () {
      $(this).toggle(!q || $(this).find('.ct-last-date-chk').val().toLowerCase().includes(q));
    });
    ctSyncAll('ctLastDateChkAll', 'ct-last-date-chk');
  });
  $(document).on('input', '#ctNextDateFilterSearch', function () {
    const q = $(this).val().toLowerCase();
    $('#ctNextDateFilterList .col-filter-item:not(.col-filter-all)').each(function () {
      $(this).toggle(!q || $(this).find('.ct-next-date-chk').val().toLowerCase().includes(q));
    });
    ctSyncAll('ctNextDateChkAll', 'ct-next-date-chk');
  });
  $(document).on('input', '#ctStatusFilterSearch', function () {
    const q = $(this).val().toLowerCase();
    $('#ctStatusFilterList .col-filter-item:not(.col-filter-all)').each(function () {
      $(this).toggle(!q || $(this).find('.ct-status-chk').val().toLowerCase().includes(q));
    });
    ctSyncAll('ctStatusChkAll', 'ct-status-chk');
  });

  // Apply
  $(document).on('click', '#ctLastDateFilterApply', function () {
    const $items = $('.ct-last-date-chk');
    const checked = [];
    $items.filter(':checked').each(function () { checked.push($(this).val()); });
    ctLastDateFilterActive = checked.length === $items.length ? null : checked;
    $('#ctLastDateFilterBtn').toggleClass('filtered', ctLastDateFilterActive !== null);
    ctTrackingTable.draw();
    $('#ctLastDateFilterDropdown').removeClass('show'); $('#ctLastDateFilterBtn').removeClass('active');
  });
  $(document).on('click', '#ctNextDateFilterApply', function () {
    const $items = $('.ct-next-date-chk');
    const checked = [];
    $items.filter(':checked').each(function () { checked.push($(this).val()); });
    ctNextDateFilterActive = checked.length === $items.length ? null : checked;
    $('#ctNextDateFilterBtn').toggleClass('filtered', ctNextDateFilterActive !== null);
    ctTrackingTable.draw();
    $('#ctNextDateFilterDropdown').removeClass('show'); $('#ctNextDateFilterBtn').removeClass('active');
  });
  $(document).on('click', '#ctSaleFilterApply', function () {
    const $items = $('.ct-sale-chk');
    const checked = [];
    $items.filter(':checked').each(function () { checked.push($(this).val()); });
    ctSaleFilterActive = checked.length === $items.length ? null : checked;
    $('#ctSaleFilterBtn').toggleClass('filtered', ctSaleFilterActive !== null);
    ctTrackingTable.draw();
    $('#ctSaleFilterDropdown').removeClass('show'); $('#ctSaleFilterBtn').removeClass('active');
  });
  $(document).on('click', '#ctStatusFilterApply', function () {
    const $items = $('.ct-status-chk');
    const checked = [];
    $items.filter(':checked').each(function () { checked.push($(this).val()); });
    ctStatusFilterActive = checked.length === $items.length ? null : checked;
    $('#ctStatusFilterBtn').toggleClass('filtered', ctStatusFilterActive !== null);
    ctTrackingTable.draw();
    $('#ctStatusFilterDropdown').removeClass('show'); $('#ctStatusFilterBtn').removeClass('active');
  });

  // Clear
  $(document).on('click', '#ctLastDateFilterClear', function () {
    ctLastDateFilterActive = null;
    $('.ct-last-date-chk').prop('checked', true);
    $('#ctLastDateChkAll').prop({ indeterminate: false, checked: true });
    $('#ctLastDateFilterBtn').removeClass('filtered active');
    ctTrackingTable.draw();
    $('#ctLastDateFilterDropdown').removeClass('show');
  });
  $(document).on('click', '#ctNextDateFilterClear', function () {
    ctNextDateFilterActive = null;
    $('.ct-next-date-chk').prop('checked', true);
    $('#ctNextDateChkAll').prop({ indeterminate: false, checked: true });
    $('#ctNextDateFilterBtn').removeClass('filtered active');
    ctTrackingTable.draw();
    $('#ctNextDateFilterDropdown').removeClass('show');
  });
  $(document).on('click', '#ctSaleFilterClear', function () {
    ctSaleFilterActive = null;
    $('.ct-sale-chk').prop('checked', true);
    $('#ctSaleChkAll').prop({ indeterminate: false, checked: true });
    $('#ctSaleFilterBtn').removeClass('filtered active');
    ctTrackingTable.draw();
    $('#ctSaleFilterDropdown').removeClass('show');
  });
  $(document).on('click', '#ctStatusFilterClear', function () {
    ctStatusFilterActive = null;
    $('.ct-status-chk').prop('checked', true);
    $('#ctStatusChkAll').prop({ indeterminate: false, checked: true });
    $('#ctStatusFilterBtn').removeClass('filtered active');
    ctTrackingTable.draw();
    $('#ctStatusFilterDropdown').removeClass('show');
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
    $.get('/customers/search', { keyword, check_salecar: 1, check_tracking: 1 }, function (res) {
      $tableBody.empty();
      if (!res.length) {
        $tableBody.append('<tr><td colspan="4" class="text-center">ไม่พบข้อมูลลูกค้า</td></tr>');
      } else {
        res.forEach(c => {
          let actionBtn;
          if (c.has_active_salecar) {
            actionBtn = `<span class="badge bg-secondary">มีการจองแล้ว</span>`;
          } else if (c.has_active_tracking) {
            actionBtn = `<span class="badge bg-warning">มีการติดตามแล้ว</span>`;
          } else {
            actionBtn = `<button class="btn btn-sm btn-success btnSelectCustomer"
                data-id="${c.id}"
                data-name="${(c.PrefixNameTH ?? '') + ' ' + (c.FirstName ?? '') + ' ' + (c.LastName ?? '')}"
                data-mobile="${c.formatted_mobile ?? ''}"
                data-idnumber="${c.formatted_id_number ?? ''}">
                เลือก
              </button>`;
          }
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
    setTimeout(() => {
      document.activeElement.blur();
      $('body').trigger('focus');
    }, 1);
  });

  // การเพิ่มข้อมูลลูกค้า แบบไม่ลัด
  function formatPhone(value) {
    const digits = value.replace(/\D/g, '').substring(0, 10);
    const parts = [];
    if (digits.length > 0) parts.push(digits.substring(0, 3));
    if (digits.length > 3) parts.push(digits.substring(3, 7));
    if (digits.length > 7) parts.push(digits.substring(7, 10));
    return parts.join('-');
  }

  function formatIDCard(value) {
    const digits = value.replace(/\D/g, '').substring(0, 13);
    const parts = [];
    if (digits.length > 0) parts.push(digits.substring(0, 1));
    if (digits.length > 1) parts.push(digits.substring(1, 5));
    if (digits.length > 5) parts.push(digits.substring(5, 10));
    if (digits.length > 10) parts.push(digits.substring(10, 12));
    if (digits.length > 12) parts.push(digits.substring(12, 13));
    return parts.join('-');
  }

  $('#qc_phone').on('input', function () {
    this.value = formatPhone(this.value);
  });

  $('#qc_id_number').on('input', function () {
    this.value = formatIDCard(this.value);
  });

  function openAddCustomerModal() {
    // reset form
    $('#qc_prefix').val('');
    $('#qc_first_name').val('');
    $('#qc_last_name').val('');
    $('#qc_phone').val('');
    $('#qc_id_number').val('');
    $('#qc_line_id').val('');
    $('#qc_facebook').val('');
    $modal.modal('hide');
    $('#modalAddCustomer').modal('show');
  }

  function closeAddCustomerModal() {
    $('#modalAddCustomer').modal('hide');
    $modal.modal('show');
  }

  $('#btnOpenAddCustomer').on('click', openAddCustomerModal);
  $('#btnCloseAddCustomer, #btnCancelAddCustomer').on('click', closeAddCustomerModal);

  $(document).on('hide.bs.modal', '#modalAddCustomer', function () {
    setTimeout(() => {
      document.activeElement.blur();
      $('body').trigger('focus');
    }, 1);
  });

  $('#btnSaveQuickCustomer').on('click', function () {
    const prefix = $('#qc_prefix').val();
    const first = $('#qc_first_name').val().trim();
    const last = $('#qc_last_name').val().trim();
    const phone = $('#qc_phone').val().trim();
    const idNumber = $('#qc_id_number').val().trim();
    const lineId = $('#qc_line_id').val().trim();
    const facebook = $('#qc_facebook').val().trim();

    if (!first || !phone) {
      Swal.fire({
        icon: 'warning',
        title: 'กรุณากรอกข้อมูลให้ครบ',
        text: 'ชื่อ และเบอร์โทร จำเป็นต้องกรอก'
      });
      return;
    }

    const $btn = $(this).prop('disabled', true).text('กำลังบันทึก...');

    $.ajax({
      url: '/customer-tracking/quick-store-customer',
      type: 'POST',
      data: { PrefixName: prefix || null, FirstName: first, LastName: last || null, Mobilephone1: phone, IDNumber: idNumber || null, LineID: lineId || null, FacebookName: facebook || null },
      success: function (res) {
        if (!res.success) {
          Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: res.message ?? 'ไม่สามารถบันทึกได้' });
          return;
        }

        // ปิด modal เพิ่มลูกค้า และ modal ค้นหา แล้วเลือกลูกค้าที่เพิ่งเพิ่ม
        $('#modalAddCustomer').modal('hide');

        $('#CusID').val(res.id);
        const setDisplay = (id, val) => {
          const el = document.getElementById(id);
          if (!el) return;
          el.textContent = val || '—';
          el.classList.toggle('empty', !val);
        };
        setDisplay('customerName-display', res.name);
        setDisplay('customerID-display', res.id_number);
        setDisplay('customerPhone-display', res.mobile);

        $('#customerSearch').val('');

        Swal.fire({ icon: 'success', title: 'เพิ่มลูกค้าสำเร็จ', timer: 1500, showConfirmButton: true });
      },
      error: function (xhr) {
        const msg = xhr.responseJSON?.message ?? 'เกิดข้อผิดพลาด กรุณาลองใหม่';
        Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: msg });
      },
      complete: function () {
        $btn.prop('disabled', false).html('<i class="bx bx-save me-1"></i> บันทึก');
      }
    });
  });

  // --- Car Cascades ---
  $('#model_id').on('change', function () {
    const modelId = $(this).val();
    const $sub = $('#sub_model_id');

    $sub.prop('disabled', true).empty().append('<option value="">— เลือกรุ่นรถย่อย —</option>');
    resetTrackingCarFields();
    $('#interior_color_id').prop('disabled', true).empty().append('<option value="">— เลือกสี —</option>');

    if (!modelId) return;

    const $interiorColor = $('#interior_color_id');
    if ($interiorColor.length) {
      $.get('/api/interior-color', { model_id: modelId }, function (data) {
        if (data.length) {
          data.forEach(c => $interiorColor.append(`<option value="${c.id}">${c.name}</option>`));
          $interiorColor.prop('disabled', false);
        }
      });
    }

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
      cancelButtonText: 'ไม่ใช่'
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
            showConfirmButton: true
          });
          setTimeout(() => {
            window.location.href = '/customer-tracking';
          }, 1500);
        },
        error: function () {
          Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: 'ไม่สามารถยกเลิกการติดตามได้' });
        }
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
