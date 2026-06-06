$.ajaxSetup({
  headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

// ── Tracking table column filter ──
let ctTrackingTable;
// active filters store raw YYYY-MM-DD for dates, name strings for sale/source/status
let ctSaleFilterActive = null;
let ctSourceFilterActive = null;
let ctStatusFilterActive = null;
let ctLastDateFilterActive = null; // YYYY-MM-DD
let ctNextDateFilterActive = null; // YYYY-MM-DD

const ctRawToDmy = d => {
  const p = d.split('-');
  return `${p[2]}-${p[1]}-${p[0]}`;
};

function ctGetCurrentFilterParams(exclude) {
  const params = {};
  const decisionVal = $('#filterDecision').val();
  if (decisionVal) params.decision_id = decisionVal;
  if (exclude !== 'sale' && ctSaleFilterActive !== null) params.sale_filter = JSON.stringify(ctSaleFilterActive);
  if (exclude !== 'source' && ctSourceFilterActive !== null)
    params.source_filter = JSON.stringify(ctSourceFilterActive);
  if (exclude !== 'status' && ctStatusFilterActive !== null)
    params.status_filter = JSON.stringify(ctStatusFilterActive);
  if (exclude !== 'last_date' && ctLastDateFilterActive !== null)
    params.last_date_filter = JSON.stringify(ctLastDateFilterActive);
  if (exclude !== 'next_date' && ctNextDateFilterActive !== null)
    params.next_date_filter = JSON.stringify(ctNextDateFilterActive);
  return params;
}

const ctFilterCache = {};

function ctLoadFilterOptions(params, callback) {
  const cacheKey = JSON.stringify(params);
  const cached = ctFilterCache[cacheKey];

  if (cached !== undefined) {
    callback(cached);
    $.get('/customer-tracking/filter-options', params, function (res) {
      ctFilterCache[cacheKey] = res;
    });
    return;
  }

  $.get('/customer-tracking/filter-options', params, function (res) {
    ctFilterCache[cacheKey] = res;
    callback(res);
  });
}

function ctBuildList($list, allNames, activeFilter, chkClass, idPfx, labelFn) {
  $list.empty();
  const allSelected = activeFilter === null;
  const getLabel = labelFn || (n => n);
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
        <label for="${idPfx}${i}">${getLabel(name)}</label>
      </div>`
    );
  });
  ctSyncAll(idPfx + 'ChkAll', chkClass);
}

function ctSyncAll(allId, itemClass) {
  const $items = $('.' + itemClass + ':visible');
  const total = $items.length,
    checked = $items.filter(':checked').length;
  const $all = $('#' + allId);
  if (total === 0 || checked === 0) $all.prop({ indeterminate: false, checked: false });
  else if (checked === total) $all.prop({ indeterminate: false, checked: true });
  else $all.prop({ indeterminate: true, checked: false });
}

// list
$(document).ready(function () {
  if (!$('#trackingTable').length) return;

  ctTrackingTable = $('#trackingTable').DataTable({
    serverSide: true,
    processing: false,
    ajax: {
      url: '/customer-tracking/list',
      data: function (d) {
        d.decision_id = $('#filterDecision').val();
        if (ctSaleFilterActive !== null) d.sale_filter = JSON.stringify(ctSaleFilterActive);
        if (ctSourceFilterActive !== null) d.source_filter = JSON.stringify(ctSourceFilterActive);
        if (ctStatusFilterActive !== null) d.status_filter = JSON.stringify(ctStatusFilterActive);
        if (ctLastDateFilterActive !== null) d.last_date_filter = JSON.stringify(ctLastDateFilterActive);
        if (ctNextDateFilterActive !== null) d.next_date_filter = JSON.stringify(ctNextDateFilterActive);
      }
    },
    columns: [
      { data: 'No', orderable: false },
      { data: 'FullName', orderable: false },
      { data: 'contact_info', orderable: false },
      { data: 'model', orderable: false },
      { data: 'sale', orderable: false },
      { data: 'source', orderable: false },
      { data: 'last_date', orderable: false },
      { data: 'next_date', orderable: false },
      { data: 'status', orderable: false },
      {
        data: 'id',
        orderable: false,
        searchable: false,
        render: function (id) {
          return `
            <div class="d-flex justify-content-center gap-1">
              <a href="/customer-tracking/${id}" class="btn btn-icon btn-info text-white" title="ดูรายละเอียด">
                <i class="bx bx-show"></i>
              </a>
              <button class="btn btn-icon btn-warning text-white btnEndTracking" data-id="${id}" title="จบการติดตาม">
                <i class="bx bx-flag"></i>
              </button>
              <button class="btn btn-icon btn-danger text-white btnDeleteTracking" data-id="${id}" title="ลบ">
                <i class="bx bx-trash"></i>
              </button>
            </div>`;
        }
      }
    ],
    paging: true,
    searching: true,
    ordering: false,
    info: true,
    pageLength: 10,
    autoWidth: false,
    language: {
      search: 'ค้นหา:',
      lengthMenu: 'แสดง _MENU_ รายการ',
      info: 'แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ',
      paginate: { previous: 'ก่อนหน้า', next: 'ถัดไป' },
      emptyTable: 'ไม่มีข้อมูล',
      zeroRecords: 'ไม่พบข้อมูล',
      processing: ''
    }
  });

  ctTrackingTable.on('init.dt', function () {
    ctLoadFilterOptions({}, function () {});
  });

  ctTrackingTable.on('preXhr.dt', function () {
    $('#ctLoadingOverlay').css('display', 'flex');
  });
  ctTrackingTable.on('xhr.dt', function () {
    $('#ctLoadingOverlay').css('display', 'none');
  });

  $('#filterDecision').on('change', function () {
    $('#filterDecisionMobile').val($(this).val());
    ctTrackingTable.ajax.reload(null, false);
  });

  $('#filterDecisionMobile').on('change', function () {
    $('#filterDecision').val($(this).val());
    ctTrackingTable.ajax.reload(null, false);
  });

  // จบการติดตาม
  $(document).on('click', '.btnEndTracking', function () {
    const id = $(this).data('id');
    Swal.fire({
      title: 'จบการติดตาม?',
      html: 'ต้องการจบการติดตามลูกค้ารายนี้ใช่หรือไม่?<br><small class="text-muted">รายการนี้จะไม่แสดงในหน้ารายการติดตามอีกต่อไป</small>',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#f59e0b',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'ยืนยัน จบการติดตาม',
      cancelButtonText: 'ยกเลิก'
    }).then(result => {
      if (!result.isConfirmed) return;
      $.ajax({
        url: `/customer-tracking/${id}/cancel`,
        type: 'POST',
        success: function () {
          Swal.fire({ icon: 'success', title: 'จบการติดตามเรียบร้อยแล้ว', timer: 1500, showConfirmButton: true });
          ctTrackingTable.ajax.reload();
        },
        error: function (xhr) {
          const msg = xhr.responseJSON?.message ?? 'ไม่สามารถจบการติดตามได้';
          Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: msg });
        }
      });
    });
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
            showConfirmButton: true
          });
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
  const ctAllDropdowns =
    '#ctSaleFilterDropdown,#ctSourceFilterDropdown,#ctLastDateFilterDropdown,#ctNextDateFilterDropdown,#ctStatusFilterDropdown';
  const ctAllBtns = '#ctSaleFilterBtn,#ctSourceFilterBtn,#ctLastDateFilterBtn,#ctNextDateFilterBtn,#ctStatusFilterBtn';

  function ctOpenDropdown($dd, btn, buildFn) {
    $(ctAllDropdowns).not($dd).removeClass('show');
    $(ctAllBtns).not(btn).removeClass('active');
    const rect = btn.getBoundingClientRect();
    $dd.css({ top: rect.bottom + 4 + 'px', left: rect.left + 'px' }).addClass('show');
    $(btn).addClass('active');
    buildFn();
  }

  $('#ctSaleFilterBtn').on('click', function (e) {
    e.stopPropagation();
    const $dd = $('#ctSaleFilterDropdown');
    if ($dd.hasClass('show')) {
      $dd.removeClass('show');
      $(this).removeClass('active');
      return;
    }
    const btn = this;
    ctLoadFilterOptions(ctGetCurrentFilterParams('sale'), function (res) {
      ctOpenDropdown($dd, btn, () => {
        ctBuildList($('#ctSaleFilterList'), res.sales || [], ctSaleFilterActive, 'ct-sale-chk', 'ctSale');
        $('#ctSaleFilterSearch').val('').trigger('input').focus();
      });
    });
  });

  $('#ctSourceFilterBtn').on('click', function (e) {
    e.stopPropagation();
    const $dd = $('#ctSourceFilterDropdown');
    if ($dd.hasClass('show')) {
      $dd.removeClass('show');
      $(this).removeClass('active');
      return;
    }
    const btn = this;
    ctLoadFilterOptions(ctGetCurrentFilterParams('source'), function (res) {
      ctOpenDropdown($dd, btn, () => {
        ctBuildList($('#ctSourceFilterList'), res.sources || [], ctSourceFilterActive, 'ct-source-chk', 'ctSource');
        $('#ctSourceFilterSearch').val('').trigger('input').focus();
      });
    });
  });

  $('#ctLastDateFilterBtn').on('click', function (e) {
    e.stopPropagation();
    const $dd = $('#ctLastDateFilterDropdown');
    if ($dd.hasClass('show')) {
      $dd.removeClass('show');
      $(this).removeClass('active');
      return;
    }
    const btn = this;
    ctLoadFilterOptions(ctGetCurrentFilterParams('last_date'), function (res) {
      ctOpenDropdown($dd, btn, () => {
        ctBuildList(
          $('#ctLastDateFilterList'),
          res.lastDates || [],
          ctLastDateFilterActive,
          'ct-last-date-chk',
          'ctLastDate',
          ctRawToDmy
        );
        $('#ctLastDateFilterSearch').val('').trigger('input').focus();
      });
    });
  });

  $('#ctNextDateFilterBtn').on('click', function (e) {
    e.stopPropagation();
    const $dd = $('#ctNextDateFilterDropdown');
    if ($dd.hasClass('show')) {
      $dd.removeClass('show');
      $(this).removeClass('active');
      return;
    }
    const btn = this;
    ctLoadFilterOptions(ctGetCurrentFilterParams('next_date'), function (res) {
      ctOpenDropdown($dd, btn, () => {
        ctBuildList(
          $('#ctNextDateFilterList'),
          res.nextDates || [],
          ctNextDateFilterActive,
          'ct-next-date-chk',
          'ctNextDate',
          ctRawToDmy
        );
        $('#ctNextDateFilterSearch').val('').trigger('input').focus();
      });
    });
  });

  $('#ctStatusFilterBtn').on('click', function (e) {
    e.stopPropagation();
    const $dd = $('#ctStatusFilterDropdown');
    if ($dd.hasClass('show')) {
      $dd.removeClass('show');
      $(this).removeClass('active');
      return;
    }
    const btn = this;
    ctLoadFilterOptions(ctGetCurrentFilterParams('status'), function (res) {
      ctOpenDropdown($dd, btn, () => {
        ctBuildList($('#ctStatusFilterList'), res.decisions || [], ctStatusFilterActive, 'ct-status-chk', 'ctStatus');
        $('#ctStatusFilterSearch').val('').trigger('input').focus();
      });
    });
  });

  $(document).on('click.ctFilter', function (e) {
    if (!$(e.target).closest(ctAllDropdowns + ',' + ctAllBtns).length) {
      $(ctAllDropdowns).removeClass('show');
      $(ctAllBtns).removeClass('active');
    }
  });

  // Select all
  $(document).on('change', '#ctSaleChkAll', function () {
    $('.ct-sale-chk:visible').prop('checked', $(this).is(':checked'));
  });
  $(document).on('change', '#ctSourceChkAll', function () {
    $('.ct-source-chk:visible').prop('checked', $(this).is(':checked'));
  });
  $(document).on('change', '#ctLastDateChkAll', function () {
    $('.ct-last-date-chk:visible').prop('checked', $(this).is(':checked'));
  });
  $(document).on('change', '#ctNextDateChkAll', function () {
    $('.ct-next-date-chk:visible').prop('checked', $(this).is(':checked'));
  });
  $(document).on('change', '#ctStatusChkAll', function () {
    $('.ct-status-chk:visible').prop('checked', $(this).is(':checked'));
  });

  // Individual → sync header
  $(document).on('change', '.ct-sale-chk', function () {
    ctSyncAll('ctSaleChkAll', 'ct-sale-chk');
  });
  $(document).on('change', '.ct-source-chk', function () {
    ctSyncAll('ctSourceChkAll', 'ct-source-chk');
  });
  $(document).on('change', '.ct-last-date-chk', function () {
    ctSyncAll('ctLastDateChkAll', 'ct-last-date-chk');
  });
  $(document).on('change', '.ct-next-date-chk', function () {
    ctSyncAll('ctNextDateChkAll', 'ct-next-date-chk');
  });
  $(document).on('change', '.ct-status-chk', function () {
    ctSyncAll('ctStatusChkAll', 'ct-status-chk');
  });

  // Search within dropdown
  $(document).on('input', '#ctSaleFilterSearch', function () {
    const q = $(this).val().toLowerCase();
    $('#ctSaleFilterList .col-filter-item:not(.col-filter-all)').each(function () {
      $(this).toggle(!q || $(this).find('.ct-sale-chk').val().toLowerCase().includes(q));
    });
    ctSyncAll('ctSaleChkAll', 'ct-sale-chk');
  });
  $(document).on('input', '#ctSourceFilterSearch', function () {
    const q = $(this).val().toLowerCase();
    $('#ctSourceFilterList .col-filter-item:not(.col-filter-all)').each(function () {
      $(this).toggle(!q || $(this).find('.ct-source-chk').val().toLowerCase().includes(q));
    });
    ctSyncAll('ctSourceChkAll', 'ct-source-chk');
  });
  $(document).on('input', '#ctLastDateFilterSearch', function () {
    const q = $(this).val().toLowerCase();
    $('#ctLastDateFilterList .col-filter-item:not(.col-filter-all)').each(function () {
      $(this).toggle(!q || $(this).find('label').text().toLowerCase().includes(q));
    });
    ctSyncAll('ctLastDateChkAll', 'ct-last-date-chk');
  });
  $(document).on('input', '#ctNextDateFilterSearch', function () {
    const q = $(this).val().toLowerCase();
    $('#ctNextDateFilterList .col-filter-item:not(.col-filter-all)').each(function () {
      $(this).toggle(!q || $(this).find('label').text().toLowerCase().includes(q));
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
    $items.filter(':checked').each(function () {
      checked.push($(this).val());
    });
    ctLastDateFilterActive = checked.length === $items.length ? null : checked;
    $('#ctLastDateFilterBtn').toggleClass('filtered', ctLastDateFilterActive !== null);
    ctTrackingTable.ajax.reload(null, false);
    $('#ctLastDateFilterDropdown').removeClass('show');
    $('#ctLastDateFilterBtn').removeClass('active');
  });
  $(document).on('click', '#ctNextDateFilterApply', function () {
    const $items = $('.ct-next-date-chk');
    const checked = [];
    $items.filter(':checked').each(function () {
      checked.push($(this).val());
    });
    ctNextDateFilterActive = checked.length === $items.length ? null : checked;
    $('#ctNextDateFilterBtn').toggleClass('filtered', ctNextDateFilterActive !== null);
    ctTrackingTable.ajax.reload(null, false);
    $('#ctNextDateFilterDropdown').removeClass('show');
    $('#ctNextDateFilterBtn').removeClass('active');
  });
  $(document).on('click', '#ctSaleFilterApply', function () {
    const $items = $('.ct-sale-chk');
    const checked = [];
    $items.filter(':checked').each(function () {
      checked.push($(this).val());
    });
    ctSaleFilterActive = checked.length === $items.length ? null : checked;
    $('#ctSaleFilterBtn').toggleClass('filtered', ctSaleFilterActive !== null);
    ctTrackingTable.ajax.reload(null, false);
    $('#ctSaleFilterDropdown').removeClass('show');
    $('#ctSaleFilterBtn').removeClass('active');
  });
  $(document).on('click', '#ctSourceFilterApply', function () {
    const $items = $('.ct-source-chk');
    const checked = [];
    $items.filter(':checked').each(function () {
      checked.push($(this).val());
    });
    ctSourceFilterActive = checked.length === $items.length ? null : checked;
    $('#ctSourceFilterBtn').toggleClass('filtered', ctSourceFilterActive !== null);
    ctTrackingTable.ajax.reload(null, false);
    $('#ctSourceFilterDropdown').removeClass('show');
    $('#ctSourceFilterBtn').removeClass('active');
  });
  $(document).on('click', '#ctStatusFilterApply', function () {
    const $items = $('.ct-status-chk');
    const checked = [];
    $items.filter(':checked').each(function () {
      checked.push($(this).val());
    });
    ctStatusFilterActive = checked.length === $items.length ? null : checked;
    $('#ctStatusFilterBtn').toggleClass('filtered', ctStatusFilterActive !== null);
    ctTrackingTable.ajax.reload(null, false);
    $('#ctStatusFilterDropdown').removeClass('show');
    $('#ctStatusFilterBtn').removeClass('active');
  });

  // Clear
  $(document).on('click', '#ctLastDateFilterClear', function () {
    ctLastDateFilterActive = null;
    $('.ct-last-date-chk').prop('checked', true);
    $('#ctLastDateChkAll').prop({ indeterminate: false, checked: true });
    $('#ctLastDateFilterBtn').removeClass('filtered active');
    ctTrackingTable.ajax.reload(null, false);
    $('#ctLastDateFilterDropdown').removeClass('show');
  });
  $(document).on('click', '#ctNextDateFilterClear', function () {
    ctNextDateFilterActive = null;
    $('.ct-next-date-chk').prop('checked', true);
    $('#ctNextDateChkAll').prop({ indeterminate: false, checked: true });
    $('#ctNextDateFilterBtn').removeClass('filtered active');
    ctTrackingTable.ajax.reload(null, false);
    $('#ctNextDateFilterDropdown').removeClass('show');
  });
  $(document).on('click', '#ctSaleFilterClear', function () {
    ctSaleFilterActive = null;
    $('.ct-sale-chk').prop('checked', true);
    $('#ctSaleChkAll').prop({ indeterminate: false, checked: true });
    $('#ctSaleFilterBtn').removeClass('filtered active');
    ctTrackingTable.ajax.reload(null, false);
    $('#ctSaleFilterDropdown').removeClass('show');
  });
  $(document).on('click', '#ctSourceFilterClear', function () {
    ctSourceFilterActive = null;
    $('.ct-source-chk').prop('checked', true);
    $('#ctSourceChkAll').prop({ indeterminate: false, checked: true });
    $('#ctSourceFilterBtn').removeClass('filtered active');
    ctTrackingTable.ajax.reload(null, false);
    $('#ctSourceFilterDropdown').removeClass('show');
  });
  $(document).on('click', '#ctStatusFilterClear', function () {
    ctStatusFilterActive = null;
    $('.ct-status-chk').prop('checked', true);
    $('#ctStatusChkAll').prop({ indeterminate: false, checked: true });
    $('#ctStatusFilterBtn').removeClass('filtered active');
    ctTrackingTable.ajax.reload(null, false);
    $('#ctStatusFilterDropdown').removeClass('show');
  });

  $('#btnExportDaily').on('click', function () {
    const date = $('#reportDailyDate').val();
    if (!date) {
      Swal.fire({ icon: 'warning', title: 'กรุณาเลือกวันที่', timer: 1500, showConfirmButton: true });
      return;
    }
    window.location.href = `/customer-tracking/export-daily?date=${date}`;
  });

  $('#btnExportByDate').on('click', function () {
    const dateFrom = $('#reportDateFrom').val();
    const dateTo = $('#reportDateTo').val();
    if (!dateFrom || !dateTo) {
      Swal.fire({ icon: 'warning', title: 'กรุณาเลือกวันที่', timer: 1500, showConfirmButton: true });
      return;
    }
    if (dateFrom > dateTo) {
      Swal.fire({
        icon: 'warning',
        title: 'วันที่เริ่มต้นต้องไม่มากกว่าวันที่สิ้นสุด',
        timer: 2000,
        showConfirmButton: true
      });
      return;
    }
    window.location.href = `/customer-tracking/export-by-date?date_from=${dateFrom}&date_to=${dateTo}`;
  });

  $('#btnExportOverdue').on('click', function () {
    const month = $('#reportOverdueMonth').val();
    if (!month) {
      Swal.fire({ icon: 'warning', title: 'กรุณาเลือกเดือน', timer: 1500, showConfirmButton: true });
      return;
    }
    window.location.href = `/customer-tracking/export-overdue?month=${month}`;
  });
});

// INPUT FORM — phone formatting + car cascades
$(document).ready(function () {
  if (!$('#ct_phone').length) return;

  function formatPhone(value) {
    const digits = value.replace(/\D/g, '').substring(0, 10);
    const parts = [];
    if (digits.length > 0) parts.push(digits.substring(0, 3));
    if (digits.length > 3) parts.push(digits.substring(3, 7));
    if (digits.length > 7) parts.push(digits.substring(7, 10));
    return parts.join('-');
  }

  $('#ct_phone').on('input', function () {
    this.value = formatPhone(this.value);
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
  function submitTrackingForm($btn, form) {
    const url = $(form).attr('action');
    const formData = new FormData(form);

    $btn.prop('disabled', true);
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
          didOpen: () => Swal.showLoading()
        });
      },
      success: function (res) {
        Swal.fire({ icon: 'success', title: 'สำเร็จ', text: res.message, timer: 2000, showConfirmButton: true });
        setTimeout(() => {
          window.location.href = '/customer-tracking';
        }, 1000);
      },
      error: function (xhr) {
        Swal.fire({
          icon: 'error',
          title: 'เกิดข้อผิดพลาด',
          text: xhr.responseJSON?.message ?? 'ไม่สามารถบันทึกข้อมูลได้'
        });
      },
      complete: function () {
        $btn.prop('disabled', false);
      }
    });
  }

  $(document).on('click', '.btnSaveTracking', function (e) {
    e.preventDefault();

    const $btn = $(this);
    const form = $btn.closest('form')[0];

    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    const phone = $('#ct_phone').val().trim();
    const lineId = $('#ct_line_id').val().trim();
    const facebook = $('#ct_facebook').val().trim();
    const firstName = $('#ct_first_name').val().trim();
    const prefix = $('#ct_prefix').val() || null;
    const last = $('#ct_last_name').val().trim() || null;

    function createNewCustomer() {
      $.ajax({
        url: '/customer-tracking/quick-store-customer',
        type: 'POST',
        data: {
          PrefixName: prefix,
          FirstName: firstName,
          LastName: last,
          Mobilephone1: phone || null,
          LineID: lineId || null,
          FacebookName: facebook || null
        },
        success: function (r) {
          if (!r.success) {
            Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: r.message ?? 'ไม่สามารถบันทึกลูกค้าได้' });
            return;
          }
          $('#CusID').val(r.id);
          submitTrackingForm($btn, form);
        },
        error: function (xhr) {
          Swal.fire({
            icon: 'error',
            title: 'เกิดข้อผิดพลาด',
            text: xhr.responseJSON?.message ?? 'ไม่สามารถบันทึกลูกค้าได้'
          });
        }
      });
    }

    // เช็คแบบ chain: ถ้าไม่ซ้ำค่อยไปเช็คตัวถัดไป จนสุดท้ายถึง onCreate
    function handleCheckResult(res, labelHtml, onNotFound) {
      if (res.has_booking) {
        Swal.fire({
          icon: 'error',
          title: 'ไม่สามารถเพิ่มการติดตามได้',
          html: `<p><b>${res.name}</b> มีข้อมูลการจองอยู่แล้ว ไม่สามารถเพิ่มการติดตามได้</p>`,
          confirmButtonText: 'ตกลง',
          confirmButtonColor: '#6c5ffc'
        });
        return;
      }

      if (res.has_tracking) {
        Swal.fire({
          icon: 'warning',
          title: 'ลูกค้ามีการติดตามอยู่แล้ว',
          html: `<p><b>${res.name}</b> มีการติดตามในระบบอยู่แล้ว</p>`,
          showCancelButton: true,
          confirmButtonText: 'ไปยังหน้าการติดตาม',
          cancelButtonText: 'ยกเลิก',
          confirmButtonColor: '#6c5ffc',
          cancelButtonColor: '#6c757d'
        }).then(result => {
          if (result.isConfirmed) window.location.href = '/customer-tracking/' + res.tracking_id;
        });
        return;
      }

      if (res.found) {
        Swal.fire({
          icon: 'question',
          title: 'พบข้อมูลในฐานข้อมูล',
          html: `<p>${labelHtml} มีข้อมูลในฐานข้อมูลแล้ว</p>
                 <p>ชื่อ: <b>${res.name}</b></p>
                 <p>ต้องการเพิ่มการติดตามให้ลูกค้าคนนี้ไหม?</p>`,
          showCancelButton: true,
          confirmButtonText: 'ใช่, เพิ่มการติดตาม',
          cancelButtonText: 'ยกเลิก',
          confirmButtonColor: '#6c5ffc',
          cancelButtonColor: '#6c757d'
        }).then(result => {
          if (!result.isConfirmed) return;
          $('#CusID').val(res.customer_id);
          submitTrackingForm($btn, form);
        });
        return;
      }

      onNotFound();
    }

    function runChecks(checks, idx, onCreate) {
      if (idx >= checks.length) {
        onCreate();
        return;
      }
      const { params, labelHtml } = checks[idx];
      $.get('/customer-tracking/check-phone', params)
        .done(res => handleCheckResult(res, labelHtml, () => runChecks(checks, idx + 1, onCreate)))
        .fail(() =>
          Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: 'ไม่สามารถตรวจสอบข้อมูลได้ กรุณาลองใหม่' })
        );
    }

    if (!phone && !lineId && !facebook) {
      $('#ct_phone, #ct_line_id, #ct_facebook').addClass('is-invalid');
      $('#contactRequiredHint').addClass('text-danger fw-semibold').removeClass('text-muted');
      Swal.fire({
        icon: 'warning',
        title: 'กรุณากรอกข้อมูลติดต่อ',
        text: 'ต้องระบุ เบอร์โทร, LineID หรือ Facebook อย่างน้อย 1 ช่อง',
        confirmButtonText: 'ตกลง',
        confirmButtonColor: '#6c5ffc'
      });
      return;
    }

    $('#ct_phone, #ct_line_id, #ct_facebook').removeClass('is-invalid');
    $('#contactRequiredHint').removeClass('text-danger fw-semibold').addClass('text-muted');

    const checks = [];
    if (phone) checks.push({ params: { phone }, labelHtml: `เบอร์ : <b>${phone}</b>` });
    if (lineId) checks.push({ params: { field: 'line_id', value: lineId }, labelHtml: `Line ID : <b>${lineId}</b>` });
    if (facebook)
      checks.push({ params: { field: 'facebook', value: facebook }, labelHtml: `Facebook : <b>${facebook}</b>` });

    Swal.fire({ title: 'กำลังตรวจสอบข้อมูล...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
    runChecks(checks, 0, createNewCustomer);
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

// GRADE TAB
$(document).ready(function () {
  if (!$('.gs-select').length) return;

  const BADGE_IDS = {
    delivery_timeline_scoring: 'score_delivery',
    test_drive_scoring: 'score_testdrive',
    occupation_scoring: 'score_occupation',
    revenue_scoring: 'score_revenue',
    model_interest_scoring: 'score_model',
    purchase_type_scoring: 'score_purchase',
    engagement_scoring: 'score_engagement'
  };

  function gradeInfo(total) {
    if (total >= 80) return { letter: 'A', color: '#10b981' };
    if (total >= 60) return { letter: 'B', color: '#6c5ffc' };
    if (total >= 40) return { letter: 'C', color: '#f59e0b' };
    return { letter: 'D', color: '#ef4444' };
  }

  function updateGradeDisplay() {
    let total = 0;
    let hasAny = false;

    $('.gs-select').each(function () {
      const field = $(this).data('field');
      const val = $(this).val();
      const $badge = $('#' + BADGE_IDS[field] + ' .gs-score-val');

      if (val) {
        const score = parseInt($(this).find(':selected').data('score') ?? 0);
        total += score;
        hasAny = true;
        $badge
          .text(score + ' คะแนน')
          .removeClass('bg-label-secondary bg-label-primary bg-label-danger')
          .addClass(score > 0 ? 'bg-label-primary' : 'bg-label-danger');
      } else {
        $badge.text('—').removeClass('bg-label-primary bg-label-danger').addClass('bg-label-secondary');
      }
    });

    const $letter = $('#gradeLetter');
    const $total = $('#gradeTotal');
    const $progress = $('#gradeProgress');

    if (!hasAny) {
      $letter.text('—').css('color', '#9ca3af');
      $total.text('—').css('color', '#374151');
      $progress.css({ width: '0%', background: '#d1d5db' });
    } else {
      const g = gradeInfo(total);
      $letter.text(g.letter).css('color', g.color);
      $total.text(total + ' / 100').css('color', g.color);
      $progress.css({ width: total + '%', background: g.color });
    }
  }

  $(document).on('change', '.gs-select', updateGradeDisplay);
  updateGradeDisplay();

  $('#btnSaveTestDrive').on('click', function () {
    const $btn = $(this);
    const trackingId = $btn.data('tracking-id');

    $btn.prop('disabled', true);
    $.ajax({
      url: `/customer-tracking/${trackingId}/test-drive`,
      type: 'POST',
      data: {
        test_drive_date: $('#td_date').val() || null,
        test_drive_note: $('#td_note').val() || null
      },
      success: function () {
        Swal.fire({ icon: 'success', title: 'บันทึกสำเร็จ', timer: 1500, showConfirmButton: true });
      },
      error: function () {
        Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: 'ไม่สามารถบันทึกได้' });
      },
      complete: function () {
        $btn.prop('disabled', false);
      }
    });
  });

  $('#btnSaveGrade').on('click', function () {
    const $btn = $(this);
    const trackingId = $btn.data('tracking-id');
    const data = {};
    $('.gs-select').each(function () {
      data[$(this).data('field')] = $(this).val() || null;
    });

    $btn.prop('disabled', true);
    $.ajax({
      url: `/customer-tracking/${trackingId}/grade`,
      type: 'POST',
      data: data,
      success: function () {
        Swal.fire({ icon: 'success', title: 'บันทึกเกรดสำเร็จ', timer: 1500, showConfirmButton: true });
      },
      error: function () {
        Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: 'ไม่สามารถบันทึกได้' });
      },
      complete: function () {
        $btn.prop('disabled', false);
      }
    });
  });
});
