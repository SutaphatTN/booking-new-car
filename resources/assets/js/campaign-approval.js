$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});

// ── ตารางอนุมัติแคมเปญ CK ──
let ckApprovalTable;
let modelFilterActive = null; // null = ทุกรุ่น, array = เฉพาะ model_id ที่เลือก
let cachedCkModels = [];      // [{id, name}]

function fmtMoney(n) {
  return Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

$(document).ready(function () {
  if ($.fn.DataTable.isDataTable('.ckApprovalTable')) {
    $('.ckApprovalTable').DataTable().destroy();
  }

  ckApprovalTable = $('.ckApprovalTable').DataTable({
    serverSide: true,
    processing: false,
    ajax: {
      url: '/campaign/ck-approval/list',
      data: function (d) {
        d.period = $('#ckPeriod').val();
        if (modelFilterActive !== null) {
          d.model_filter = JSON.stringify(modelFilterActive);
        }
      }
    },
    columns: [
      { data: 'No', orderable: false },
      { data: 'model', orderable: false },
      { data: 'name', orderable: false },
      { data: 'type', orderable: false },
      { data: 'amount', orderable: false },
      { data: 'status', orderable: false, searchable: false },
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
      processing: '',
      paginate: { next: 'ถัดไป', previous: 'ก่อนหน้า' }
    }
  });

  // ให้ campaign.js (แก้ไข/จัดเก็บ/ลบ) reload ตารางนี้ได้หลังบันทึก
  window.ckApprovalTable = ckApprovalTable;

  ckApprovalTable.on('preXhr.dt', function () {
    $('#ckApprovalLoadingOverlay').css('display', 'flex');
  });
  ckApprovalTable.on('draw.dt xhr.dt', function () {
    $('#ckApprovalLoadingOverlay').css('display', 'none');
  });

  // เปลี่ยนเดือน → reload ตาราง
  $('#ckPeriod').on('change', function () {
    if (!$(this).val()) return;
    ckApprovalTable.ajax.reload();
  });

  // ── ตัวกรองคอลัมน์ รุ่นรถ ──
  populateModelFilterList();

  // เปิด/ปิด dropdown — วางตำแหน่ง fixed หนี overflow ของตาราง
  $('#modelFilterBtn').on('click', function (e) {
    e.stopPropagation();
    const $dd = $('#modelFilterDropdown');
    if ($dd.hasClass('show')) {
      $dd.removeClass('show');
      $(this).removeClass('active');
      return;
    }
    const rect = this.getBoundingClientRect();
    $dd.css({ top: rect.bottom + 4 + 'px', left: rect.left + 'px' });
    $dd.addClass('show');
    $(this).addClass('active');
    buildModelFilterList();
    $('#modelFilterSearch').val('').trigger('input').focus();
  });

  // ปิดเมื่อคลิกนอก dropdown
  $(document).on('click.modelFilter', function (e) {
    if (!$(e.target).closest('#modelFilterDropdown, #modelFilterBtn').length) {
      $('#modelFilterDropdown').removeClass('show');
      $('#modelFilterBtn').removeClass('active');
    }
  });

  // เลือกทั้งหมด
  $(document).on('change', '#modelChkAll', function () {
    $('.model-chk-item:visible').prop('checked', $(this).is(':checked'));
  });

  // เลือกทีละรายการ → sync หัว
  $(document).on('change', '.model-chk-item', function () {
    syncModelSelectAll();
  });

  // ค้นหาใน dropdown
  $(document).on('input', '#modelFilterSearch', function () {
    const q = $(this).val().toLowerCase();
    $('#modelFilterList .col-filter-item:not(.col-filter-all)').each(function () {
      const label = $(this).find('label').text().toLowerCase();
      $(this).toggle(!q || label.includes(q));
    });
    syncModelSelectAll();
  });

  // ตกลง
  $(document).on('click', '#modelFilterApply', function () {
    const $all = $('.model-chk-item');
    const checked = [];
    $all.filter(':checked').each(function () { checked.push($(this).val()); });
    const isAll = checked.length === $all.length;
    modelFilterActive = isAll ? null : checked;
    $('#modelFilterBtn').toggleClass('filtered', modelFilterActive !== null);
    ckApprovalTable.ajax.reload(null, false);
    $('#modelFilterDropdown').removeClass('show');
    $('#modelFilterBtn').removeClass('active');
  });

  // ล้าง
  $(document).on('click', '#modelFilterClear', function () {
    modelFilterActive = null;
    $('.model-chk-item').prop('checked', true);
    $('#modelChkAll').prop({ indeterminate: false, checked: true });
    $('#modelFilterBtn').removeClass('filtered active');
    ckApprovalTable.ajax.reload(null, false);
    $('#modelFilterDropdown').removeClass('show');
  });
});

function populateModelFilterList() {
  $.get('/campaign/ck-approval/model-options', function (models) {
    cachedCkModels = models || [];
    buildModelFilterList();
  });
}

function buildModelFilterList() {
  const $list = $('#modelFilterList').empty();
  const allSelected = modelFilterActive === null;
  $list.append(
    `<div class="col-filter-item col-filter-all">
      <input type="checkbox" id="modelChkAll" ${allSelected ? 'checked' : ''}>
      <label for="modelChkAll">(เลือกทั้งหมด)</label>
    </div>`
  );
  cachedCkModels.forEach(function (m, i) {
    const checked = allSelected || (modelFilterActive !== null && modelFilterActive.includes(String(m.id))) ? 'checked' : '';
    $list.append(
      `<div class="col-filter-item">
        <input type="checkbox" class="model-chk-item" id="modelChk${i}" value="${m.id}" ${checked}>
        <label for="modelChk${i}">${m.name}</label>
      </div>`
    );
  });
  syncModelSelectAll();
}

function syncModelSelectAll() {
  const $items = $('.model-chk-item:visible');
  const total = $items.length;
  const checked = $items.filter(':checked').length;
  const $all = $('#modelChkAll');
  if (total === 0 || checked === 0) {
    $all.prop({ indeterminate: false, checked: false });
  } else if (checked === total) {
    $all.prop({ indeterminate: false, checked: true });
  } else {
    $all.prop({ indeterminate: true, checked: false });
  }
}

// ══ Modal ขออนุมัติ ══
const approvalModalEl = document.getElementById('approvalModal');
const approvalModal = approvalModalEl ? new bootstrap.Modal(approvalModalEl) : null;

// เปิด modal → โหลดรายการที่ยังไม่อนุมัติของเดือนนั้น
$(document).on('click', '#btnOpenApproval', function () {
  const period = $('#ckPeriod').val();
  if (!period) return;
  $('#approvalModalPeriod').text(period);

  $('#approvalModalBody').html(
    '<tr><td colspan="5" class="text-center py-4"><span class="spinner-border spinner-border-sm text-primary"></span> กำลังโหลด...</td></tr>'
  );
  $('#approvalEmpty').addClass('d-none');
  $('#approvalSearch').val('');
  $('#approvalSelectAll').prop({ checked: false, indeterminate: false });
  approvalModal.show();

  $.get('/campaign/ck-approval/pending-list', { period: period }, function (res) {
    const rows = res.data || [];
    if (!rows.length) {
      $('#approvalModalBody').html('');
      $('#approvalEmpty').removeClass('d-none');
      updateApprovalTotals();
      return;
    }

    const statusClass = { 'ยังไม่ขอ': 'bg-secondary', 'รออนุมัติ': 'bg-warning', 'ส่งกลับแก้ไข': 'bg-danger' };

    // จัดกลุ่มตาม "รุ่นหลัก" (model_id) — ตามลำดับที่ backend ส่งมา (เรียงตามรุ่นหลักแล้ว)
    const groups = {}, order = [];
    rows.forEach(r => {
      const key = r.model_id ?? 0;
      if (!groups[key]) { groups[key] = []; order.push(key); }
      groups[key].push(r);
    });

    let html = '';
    order.forEach(mid => {
      const items = groups[mid];
      const mainName = items[0].model_main || '-';
      const groupTotal = items.reduce((s, r) => s + (parseFloat(r.amount) || 0), 0);

      // แถวหัวรุ่นหลัก — ติ๊กเพื่อเลือกทุกแคมเปญใต้รุ่นนี้ + ปุ่มย่อ/ขยาย
      html += `
        <tr class="ap-group table-light" data-model-id="${mid}">
          <td class="text-center">
            <input type="checkbox" class="form-check-input ap-group-chk" data-model-id="${mid}">
          </td>
          <td colspan="2">
            <strong class="ap-group-title" style="cursor:pointer;">${mainName}</strong>
            <span class="text-muted small">(${items.length} รายการ)</span>
          </td>
          <td class="text-end text-muted small">${fmtMoney(groupTotal)}</td>
          <td class="text-center">
            <button type="button" class="btn btn-sm btn-link p-0 ap-group-toggle" title="ย่อ/ขยาย">
              <i class="bx bx-chevron-down"></i>
            </button>
          </td>
        </tr>`;

      // แถวแคมเปญย่อย (รุ่นย่อย)
      items.forEach(r => {
        html += `
          <tr class="ap-row" data-model-id="${mid}" data-search="${(r.model + ' ' + r.name + ' ' + r.type).toLowerCase()}">
            <td class="text-center">
              <input type="checkbox" class="form-check-input ap-item" value="${r.id}" data-model-id="${mid}" data-amount="${r.amount}">
            </td>
            <td class="ps-4 text-muted">${r.sub || '-'}</td>
            <td>${r.name}</td>
            <td class="text-end">${fmtMoney(r.amount)}</td>
            <td><span class="badge ${statusClass[r.status] || 'bg-secondary'}">${r.status}</span></td>
          </tr>`;
      });
    });

    $('#approvalModalBody').html(html);
    updateApprovalTotals();
  }).fail(function () {
    $('#approvalModalBody').html('<tr><td colspan="5" class="text-center text-danger py-4">โหลดข้อมูลไม่สำเร็จ</td></tr>');
  });
});

// การ "เลือก" อิงจากแถวที่ตรงคำค้น (ไม่ผูกกับการย่อ) ; การ "แสดง" อิงคำค้น + สถานะย่อ
function apSearchQ() { return $('#approvalSearch').val().toLowerCase().trim(); }
function apRowMatches($row, q) { return !q || String($row.data('search')).indexOf(q) !== -1; }

// แถวย่อย (ap-item) ของรุ่นที่ตรงคำค้น — ใช้กับการติ๊กเลือก
function apGroupItems(mid) {
  const q = apSearchQ();
  return $(`#approvalModalBody .ap-row[data-model-id="${mid}"]`)
    .filter(function () { return apRowMatches($(this), q); })
    .find('.ap-item');
}
function apAllItems() {
  const q = apSearchQ();
  return $('#approvalModalBody .ap-row')
    .filter(function () { return apRowMatches($(this), q); })
    .find('.ap-item');
}

// ปรับการแสดงผล: ค้นหา → โชว์ทุก match (มองข้ามการย่อ) ; ไม่ค้นหา → โชว์เฉพาะกลุ่มที่ไม่ย่อ
function refreshApprovalVisibility() {
  const q = apSearchQ();
  $('#approvalModalBody .ap-group').each(function () {
    const $g = $(this);
    const mid = $g.data('model-id');
    const collapsed = $g.hasClass('collapsed');
    let anyMatch = false;
    $(`#approvalModalBody .ap-row[data-model-id="${mid}"]`).each(function () {
      const match = apRowMatches($(this), q);
      if (match) anyMatch = true;
      $(this).toggle(match && (q ? true : !collapsed));
    });
    $g.toggle(q ? anyMatch : true);
  });
  syncAllGroupChk();
  syncApprovalSelectAll();
}

// ค้นหาในโมดัล (client-side)
$(document).on('input', '#approvalSearch', refreshApprovalVisibility);

// ── ปุ่มย่อ/ขยาย รายรุ่นหลัก (ปุ่มลูกศร หรือคลิกชื่อรุ่น) ──
$(document).on('click', '.ap-group-toggle, .ap-group-title', function () {
  const $g = $(this).closest('.ap-group');
  $g.toggleClass('collapsed');
  const collapsed = $g.hasClass('collapsed');
  $g.find('.ap-group-toggle i').attr('class', collapsed ? 'bx bx-chevron-right' : 'bx bx-chevron-down');
  refreshApprovalVisibility();
});

// ── ย่อ/ขยายทั้งหมด ──
$(document).on('click', '#approvalToggleAll', function () {
  const collapseAll = $(this).data('collapsed') !== true;
  $('#approvalModalBody .ap-group').toggleClass('collapsed', collapseAll);
  $('#approvalModalBody .ap-group-toggle i').attr('class', collapseAll ? 'bx bx-chevron-right' : 'bx bx-chevron-down');
  $(this).data('collapsed', collapseAll)
    .html(collapseAll ? '<i class="bx bx-expand-vertical me-1"></i>ขยายทั้งหมด' : '<i class="bx bx-collapse-vertical me-1"></i>ย่อทั้งหมด');
  refreshApprovalVisibility();
});

// เลือกทั้งหมด (ทุกแถวที่ตรงคำค้น)
$(document).on('change', '#approvalSelectAll', function () {
  apAllItems().prop('checked', $(this).is(':checked'));
  syncAllGroupChk();
  updateApprovalTotals();
});

// ── ติ๊กหัว "รุ่นหลัก" → เลือก/ยกเลิกทุกแคมเปญใต้รุ่นนั้น (ที่ตรงคำค้น แม้ย่ออยู่) ──
$(document).on('change', '.ap-group-chk', function () {
  const mid = $(this).data('model-id');
  apGroupItems(mid).prop('checked', $(this).is(':checked'));
  $(this).prop('indeterminate', false);
  syncApprovalSelectAll();
  updateApprovalTotals();
});

$(document).on('change', '.ap-item', function () {
  syncGroupChk($(this).data('model-id'));
  syncApprovalSelectAll();
  updateApprovalTotals();
});

// ซิงก์สถานะติ๊กหัวรุ่นจากแถวย่อยที่ตรงคำค้น (ไม่ขึ้นกับการย่อ)
function syncGroupChk(mid) {
  const $items = apGroupItems(mid);
  const total = $items.length;
  const checked = $items.filter(':checked').length;
  $(`.ap-group-chk[data-model-id="${mid}"]`)
    .prop({ indeterminate: checked > 0 && checked < total, checked: total > 0 && checked === total });
}

function syncAllGroupChk() {
  $('#approvalModalBody .ap-group-chk').each(function () {
    syncGroupChk($(this).data('model-id'));
  });
}

function syncApprovalSelectAll() {
  const $items = apAllItems();
  const total = $items.length;
  const checked = $items.filter(':checked').length;
  $('#approvalSelectAll').prop({ indeterminate: checked > 0 && checked < total, checked: total > 0 && checked === total });
}

function updateApprovalTotals() {
  let count = 0, total = 0;
  $('.ap-item:checked').each(function () {
    count++;
    total += parseFloat($(this).data('amount')) || 0;
  });
  $('#approvalSelCount').text(count);
  $('#approvalSubmitCount').text(count);
  $('#approvalSelTotal').text(fmtMoney(total));
  $('#btnSubmitApproval').prop('disabled', count === 0);
}

// ส่งขออนุมัติ
$(document).on('click', '#btnSubmitApproval', function () {
  const ids = $('.ap-item:checked').map(function () { return $(this).val(); }).get();
  const period = $('#ckPeriod').val();
  if (!ids.length || !period) return;

  $.ajax({
    url: '/campaign/ck-approval/request',
    type: 'POST',
    data: { campaign_ids: ids, period_ym: period },
    beforeSend: function () {
      approvalModal.hide();
      Swal.fire({
        title: 'กำลังส่งคำขออนุมัติ...',
        text: 'กรุณารอสักครู่',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
      });
    },
    success: function (res) {
      Swal.fire({
        icon: 'success',
        title: 'สำเร็จ',
        text: res.message,
        timer: 3000,
        showConfirmButton: true
      });
      ckApprovalTable.ajax.reload(null, false);
    },
    error: function (xhr) {
      Swal.fire({
        icon: 'error',
        title: 'ไม่สำเร็จ',
        text: xhr.responseJSON?.message || 'เกิดข้อผิดพลาด กรุณาลองใหม่'
      });
    }
  });
});
