$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});

//view : table
let commissionTable;

$(document).ready(function () {
  if ($.fn.DataTable.isDataTable('.commissionTable')) {
    $('.commissionTable').DataTable().destroy();
  }

  commissionTable = $('.commissionTable').DataTable({
    ajax: {
      url: '/purchase-order/list-Commission',
      data: function (d) {
        d.month = $('#commissionMonth').val();
      }
    },
    columns: [
      { data: 'No' },
      { data: 'name' },
      { data: 'total_car' },
      { data: 'com' },
      {
        data: null,
        orderable: false,
        searchable: false,
        className: 'text-center',
        render: function (data, type, row) {
          if (!row.DT_RowData || !row.DT_RowData.saleid) return '';
          return (
            '<button type="button" class="btn btn-sm btn-primary btnCommissionDetail">' +
            '<i class="bx bx-edit me-1"></i> รายละเอียด / กรอกค่าคอม' +
            '</button>'
          );
        }
      }
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
        next: 'ถัดไป',
        previous: 'ก่อนหน้า'
      }
    }
  });

  // ── ตัวโหลดข้อมูล (โชว์ตอนดึงข้อมูล เช่น เปลี่ยนเดือน) ──
  commissionTable.on('preXhr.dt', function () {
    $('#commissionLoadingOverlay').css('display', 'flex');
  });
  commissionTable.on('xhr.dt', function () {
    $('#commissionLoadingOverlay').css('display', 'none');
  });
});

// ── เป้ายอดขายต่อเดือน (คอมตัวรถรายคัน) ──
function loadMonthlyTarget() {
  if (!$('#monthlyTarget').length) return; // brand 3 ไม่มีช่องเป้า
  const month = $('#commissionMonth').val();
  $.get('/purchase-order/commission-target', { month: month }, function (res) {
    $('#monthlyTarget').val(res.target ?? '');
    const $st = $('#targetStatus');
    if (res.target) {
      const cls = res.achieved ? 'text-success' : 'text-danger';
      const txt = res.achieved ? 'บรรลุเป้า 120% ✓' : 'ยังไม่บรรลุ 120%';
      $st.html(
        '<span class="' + cls + '">ยอดขาย ' + res.brand_count + '/' + res.threshold + ' คัน — ' + txt + '</span>'
      );
    } else {
      $st.html('<span class="text-muted">ยังไม่ตั้งเป้า (ยอดขาย ' + res.brand_count + ' คัน)</span>');
    }
  });
}

$(document).on('click', '#btnSaveTarget', function () {
  const $btn = $(this);
  $btn.prop('disabled', true);
  $.post(
    '/purchase-order/commission-target',
    {
      month: $('#commissionMonth').val(),
      target: $('#monthlyTarget').val() || 0
    },
    function () {
      if (commissionTable) commissionTable.ajax.reload(null, false);
      loadMonthlyTarget();
      if (window.Swal) {
        Swal.fire({ icon: 'success', title: 'บันทึกเป้าแล้ว', timer: 1200, showConfirmButton: false });
      }
    }
  )
    .fail(function () {
      if (window.Swal) Swal.fire({ icon: 'error', title: 'บันทึกไม่สำเร็จ' });
      else alert('บันทึกไม่สำเร็จ');
    })
    .always(function () {
      $btn.prop('disabled', false);
    });
});

$(document).ready(loadMonthlyTarget);

// reload when month changes
$(document).on('change', '#commissionMonth', function () {
  if (commissionTable) {
    commissionTable.ajax.reload();
  }
  loadMonthlyTarget();
});

// click action button -> open detail modal (customer list + monthly extra commission)
$(document).on('click', '.btnCommissionDetail', function () {
  const saleId = $(this).closest('tr').data('saleid');
  if (!saleId) return;

  const month = $('#commissionMonth').val();
  const $btn = $(this);
  if ($btn.prop('disabled')) return; // กันกดรัว ๆ ยิงซ้ำระหว่างรอ

  $btn.prop('disabled', true);
  $('#commissionLoadingOverlay').css('display', 'flex');

  $.get('/purchase-order/commission-sale-detail/' + saleId, { month: month }, function (html) {
    $('.commissionDetailModel').html(html);
    $('.commissionDetail').modal('show');
  })
    .fail(function () {
      if (window.Swal) Swal.fire({ icon: 'error', title: 'โหลดรายละเอียดไม่สำเร็จ' });
      else alert('โหลดรายละเอียดไม่สำเร็จ');
    })
    .always(function () {
      $('#commissionLoadingOverlay').css('display', 'none');
      $btn.prop('disabled', false);
    });
});

// live recompute net commission in the detail modal (brand-aware)
function recomputeCommissionNet() {
  const $display = $('#netCommissionDisplay');
  if (!$display.length) return;

  const num = id => parseMoney($('#' + id).val());
  const base = parseFloat($display.data('base')) || 0;
  const brand = parseInt($display.data('brand'), 10) || 0;
  const ssi = parseFloat($display.data('ssi')) || 0; // คอม SSI (คิดสดจาก server) รวมเข้ายอด
  const car = parseFloat($display.data('car')) || 0; // คอมตัวรถรายคัน (คิดสดจาก server)
  const held = parseFloat($display.data('held')) || 0; // คอมกั๊ก brand 1 = (ยกมา) − (กั๊กเดือนนี้)

  let net;
  // กลุ่ม [1,3,4] ต้องตรงกับ SaleCommissionMonthly::computeNet และ $isBrand13 ในหน้า blade
  if (brand === 1 || brand === 3 || brand === 4) {
    // วินัยไม่ผ่าน → หัก 15% จากรวมค่าคอมรถ ; ไม่มี lead/clip
    const failed = $('input[name="discipline_failed"]:checked').val() === '1';
    net = (failed ? base * 0.85 : base) - num('deduct_absence');
  } else {
    net = base + num('com_discipline') + num('com_lead') + num('com_clip') - num('deduct_absence');
  }
  net -= num('deduct_other'); // หักอื่นๆ ใช้ทุก brand (ตรงกับ SaleCommissionMonthly::computeNet)
  // คอมประดับยนต์ (หน้าร้าน) ใช้ทุก brand — บวกนอกฐาน จึงไม่โดนหัก 15% ตอนวินัยไม่ผ่าน
  net += num('com_accessory_sold');
  // โบนัส budget ที่เหลือ × 30% (brand 2) — recomputeCarsTable คิดสดไว้ให้แล้ว
  net += parseFloat($('#budgetWalletBox').data('bonus')) || 0;
  net += ssi + car + held;

  $display.text(net.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ฿');
}

// ช่องค่าคอมรายเดือน (วินัย/lead/clip/ขาดลา) : ใส่ comma + คิด net สด
$(document).on('input', '.cmoney', function () {
  formatMoneyInput(this);
  recomputeCommissionNet();
});
$(document).on('blur', '.cmoney', function () {
  if (this.value.trim() === '') return;
  this.value = parseMoney(this.value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
});
$(document).on('change', '#commissionMonthlyForm input[name="discipline_failed"]', recomputeCommissionNet);

// อ่านตัวเลขจากช่องที่มี comma
function parseMoney(v) {
  return parseFloat(String(v == null ? '' : v).replace(/,/g, '')) || 0;
}

// ใส่ comma ระหว่างพิมพ์ (คงเครื่องหมายลบ + จุดทศนิยม ≤ 2 ตำแหน่ง)
function formatMoneyInput(el) {
  let raw = el.value.replace(/,/g, '');
  if (raw === '' || raw === '-') return;
  const neg = raw.trim().charAt(0) === '-';
  raw = raw.replace(/[^0-9.]/g, '');
  const hasDot = raw.indexOf('.') !== -1;
  const parts = raw.split('.');
  const intFmt = parts[0] ? parseInt(parts[0], 10).toLocaleString('en-US') : '';
  let out = intFmt;
  if (hasDot) out = (intFmt || '0') + '.' + (parts[1] ? parts[1].slice(0, 2) : '');
  el.value = (neg ? '-' : '') + out;
}

// กันกรอก "budget หัก" เกิน budget ที่มี (รวมทุกคัน ≤ ยกมา)
function clampBudgetInput(el) {
  const carried = parseMoney($('#budgetWalletBox').data('carried'));
  let val = parseMoney(el.value);
  if (val < 0) val = 0;
  let otherUsed = 0;
  $('.car-budget-input').each(function () {
    if (this !== el) otherUsed += parseMoney(this.value);
  });
  const maxForThis = Math.max(0, carried - otherUsed);
  if (val > maxForThis) {
    el.value = String(maxForThis);
  }
}

// แก้ "คอมอื่นๆ" / "budget หัก" ต่อคัน → คิดรวมค่าคอมรถต่อแถว + ยอดรวม + budget คงเหลือ + net สด
function recomputeCarsTable() {
  const fmt = n => n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  // base = รวมค่าคอมรถ (ไม่รวมคอมตัวรถ) — ยอดสุทธิด้านล่างบวกคอมตัวรถแยกจาก data-car อยู่แล้ว
  let base = 0;
  let budgetUsed = 0;
  let carTotal = 0;
  let posTotal = 0;
  let negTotal = 0;
  let netTotal = 0;
  let specialTotal = 0; // รวมช่อง "คอมอื่นๆ" (แก้สดได้ จึงรวมที่นี่ ไม่ใช่ฝั่ง blade)
  $('.car-special-input').each(function () {
    const $row = $(this).closest('tr');
    const rowbase = parseFloat($(this).data('rowbase')) || 0;
    const rowcar = parseFloat($(this).data('rowcar')) || 0; // คอมตัวรถของคันนี้ (คงที่)
    const rowneg = parseFloat($(this).data('rowneg')) || 0; // หักเกินงบ (คงที่, ติดลบหรือ 0)
    const special = parseMoney($(this).val());
    // budget หัก : ไปหักกระเป๋า budget ยกมาอย่างเดียว ไม่เข้าคอมของคันนี้ (ตรงกับ Salecar::effectiveCommissionSale)
    const budget = parseMoney($row.find('.car-budget-input').val());
    const rowTotal = rowbase + special; // = รวมค่าคอมรถของคันนี้ (มียอดติดลบรวมอยู่แล้ว)
    const rowNet = rowTotal + rowcar; // คอมสุทธิ = รวมค่าคอมรถ + คอมตัวรถ
    const rowPos = rowNet - rowneg; // รวมเงินได้ = คอมสุทธิ − (ยอดติดลบ)
    $row.find('.car-row-positive').text(fmt(rowPos));
    $row.find('.car-row-total').text(fmt(rowNet));
    base += rowTotal;
    budgetUsed += budget;
    carTotal += rowcar;
    posTotal += rowPos;
    negTotal += rowneg;
    netTotal += rowNet;
    specialTotal += special;
  });
  $('#carsSpecialTotal').text(fmt(specialTotal));
  $('#carsBudgetTotal').text(fmt(budgetUsed));
  $('#carsCarTotal').text(fmt(carTotal));
  $('#carsPositiveTotal').text(fmt(posTotal));
  $('#carsNegativeTotal').text(fmt(negTotal));
  // คอมสุทธิรวม : หักยอดที่กั๊ก/พักไว้ (ยังไม่จ่ายรอบนี้) ออก — ค่าคงที่จาก server
  const withheld = parseFloat($('#carsNetTotal').closest('td').data('withheld')) || 0;
  $('#carsNetTotal').text(fmt(netTotal - withheld));
  $('#netCommissionDisplay').data('base', base);

  // budget ยกมา (brand 2): อัปเดต ใช้ไป / คงเหลือ สด
  const $wallet = $('#budgetWalletBox');
  if ($wallet.length) {
    const carried = parseMoney($wallet.data('carried'));
    const remaining = carried - budgetUsed;
    $('#budgetUsedDisplay').text(fmt(budgetUsed));
    $('#budgetRemainingDisplay').text(fmt(remaining));
    // budget ที่เหลือ × 30% คืนเซลล์ → เก็บไว้ให้ recomputeCommissionNet บวกเข้ายอดสุทธิ
    // (ยิ่งหัก budget เยอะ โบนัสยิ่งลด — ต้องขยับสดตอนพิมพ์ ไม่งั้นยอดไม่ตรงกับที่ server จะบันทึก)
    const bonus = Math.max(0, remaining) * (parseFloat($wallet.data('bonusRate')) || 0);
    $wallet.data('bonus', bonus);
    $('#budgetBonusDisplay').text(fmt(bonus));
  }
  recomputeCommissionNet();
}

// คอมอื่นๆ : ใส่ comma + คิดใหม่
$(document).on('input', '.car-special-input', function () {
  formatMoneyInput(this);
  recomputeCarsTable();
});
// budget หัก : กันเกิน budget ที่มี → ใส่ comma → คิดใหม่
$(document).on('input', '.car-budget-input', function () {
  clampBudgetInput(this);
  formatMoneyInput(this);
  recomputeCarsTable();
});
// ออกจากช่อง → เติมทศนิยม 2 ตำแหน่ง
$(document).on('blur', '.car-special-input, .car-budget-input', function () {
  if (this.value.trim() === '') return;
  this.value = parseMoney(this.value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
});
// เปิด modal → จัด comma ค่าที่ server ส่งมา + คิดยอดครั้งแรก
$(document).on('shown.bs.modal', '.commissionDetail', function () {
  $('.car-special-input, .car-budget-input, .cmoney').each(function () {
    if (this.value.trim() !== '') {
      this.value = parseMoney(this.value).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    }
  });
  recomputeCarsTable();
});

// save monthly extra commission (+ คอมอื่นๆ ต่อคัน)
$(document).on('submit', '#commissionMonthlyForm', function (e) {
  e.preventDefault();
  const $btn = $('#btnSaveCommissionMonthly');
  $btn.prop('disabled', true);

  // strip comma ช่องค่าคอมรายเดือน (backend validate numeric — "3,500.00" จะไม่ผ่านแล้วได้ 422)
  // เพิ่มช่องเงินใหม่ตรงนี้ทุกครั้ง ไม่งั้นค่าที่มีหลักพันขึ้นไปจะบันทึกไม่ผ่าน (ต่ำกว่าพันไม่มี comma เลยรอด)
  const moneyFields = [
    'com_discipline',
    'deduct_absence',
    'deduct_other',
    'com_lead',
    'com_clip',
    'com_accessory_sold'
  ];
  const payload = $(this)
    .serializeArray()
    .map(f => (moneyFields.includes(f.name) ? { name: f.name, value: parseMoney(f.value) } : f));
  $('.car-special-input').each(function () {
    payload.push({ name: 'car_special[' + $(this).data('id') + ']', value: parseMoney($(this).val()) });
  });
  $('.car-budget-input').each(function () {
    payload.push({ name: 'car_budget_deduct[' + $(this).data('id') + ']', value: parseMoney($(this).val()) });
  });

  // ── เช็คให้ครบก่อนยิง (server เช็คซ้ำอีกชั้น ตรงนี้แค่ให้รู้เร็ว ไม่ต้องรอ 422) ──
  const warn = msg => {
    if (window.Swal) Swal.fire({ icon: 'warning', title: 'กรอกข้อมูลไม่ครบ', text: msg });
    else alert(msg);
    $btn.prop('disabled', false);
  };

  if (parseMoney($('#deduct_other').val()) > 0 && !String($('#deduct_other_note').val() || '').trim()) {
    warn('กรอก "หักอื่นๆ" แล้วต้องระบุ "หมายเหตุหักอื่นๆ" ว่าหักค่าอะไรด้วย');
    $('#deduct_other_note').trigger('focus');
    return;
  }

  const receiptInput = document.getElementById('accessory_receipt');
  const hasNewReceipt = receiptInput && receiptInput.files && receiptInput.files.length > 0;
  const hasOldReceipt = $('#existingReceipts [data-url]').length > 0;
  if (parseMoney($('#com_accessory_sold').val()) > 0 && !hasNewReceipt && !hasOldReceipt) {
    warn('กรอก "คอมประดับยนต์ (หน้าร้าน)" แล้วต้องแนบใบเสร็จด้วย (รูปภาพหรือ PDF)');
    if (receiptInput) receiptInput.focus();
    return;
  }

  // ต้องส่งเป็น FormData เพราะมีไฟล์แนบ (urlencoded ส่งไฟล์ไม่ได้)
  const fd = new FormData();
  payload.forEach(f => fd.append(f.name, f.value));
  // ใบเสร็จเดิมที่ยังอยู่ในหน้าจอ = ตัวที่ให้เก็บไว้ ; ที่กดลบไปแล้วจะไม่ถูกส่ง → server ตัดออกให้
  $('#existingReceipts [data-url]').each(function () {
    fd.append('receipt_keep[]', $(this).data('url'));
  });
  if (hasNewReceipt) {
    Array.from(receiptInput.files).forEach(f => fd.append('accessory_receipt[]', f));
  }

  $.ajax({
    url: '/purchase-order/commission-monthly',
    type: 'POST',
    data: fd,
    processData: false,
    contentType: false
  })
    .done(function () {
      $('.commissionDetail').modal('hide');
      if (commissionTable) {
        commissionTable.ajax.reload(null, false);
      }
      if (window.Swal) {
        Swal.fire({ icon: 'success', title: 'บันทึกสำเร็จ', timer: 1400, showConfirmButton: false });
      }
    })
    .fail(function (xhr) {
      // โชว์สาเหตุจริงจาก server (422 = ข้อมูลไม่ผ่าน validate, 403 = ไม่มีสิทธิ์)
      // ไม่งั้นขึ้นแต่ "กรุณาลองใหม่อีกครั้ง" แล้วหาสาเหตุไม่ได้เลย
      const res = xhr.responseJSON || {};
      const detail = res.errors
        ? Object.values(res.errors).flat().join('\n')
        : res.message || 'กรุณาลองใหม่อีกครั้ง';
      if (window.Swal) {
        Swal.fire({ icon: 'error', title: 'บันทึกไม่สำเร็จ', text: detail });
      } else {
        alert('บันทึกไม่สำเร็จ\n' + detail);
      }
    })
    .always(function () {
      $btn.prop('disabled', false);
    });
});

// ── ใบเสร็จประดับยนต์ ──
// ข้อความ "ยังไม่ได้แนบใบเสร็จ" โผล่เมื่อไม่มีไฟล์เลย (ทั้งของเดิมและที่เพิ่งเลือก)
function toggleReceiptEmpty() {
  const total = $('#existingReceipts [data-url]').length + $('#newReceiptPreview .receipt-item').length;
  $('#receiptEmpty').toggleClass('d-none', total > 0);
}

// ลบของเดิม = เอาออกจากหน้าจอเฉย ๆ มีผลจริงตอนกดบันทึก (ตัวที่เหลือถูกส่งไปเป็น receipt_keep[])
$(document).on('click', '.btn-remove-receipt', function () {
  $(this).closest('[data-url]').remove();
  toggleReceiptEmpty();
});

// ── พรีวิวไฟล์ที่เพิ่งเลือก + ปุ่มลบทีละไฟล์ ──
function renderReceiptPreview(input) {
  const $preview = $('#newReceiptPreview').empty();
  Array.from(input.files).forEach(function (file, idx) {
    const isImg = /image/i.test(file.type);
    const thumb = isImg
      ? `<img src="${URL.createObjectURL(file)}">`
      : `<span class="receipt-doc"><i class="bx bxs-file-pdf"></i><span>PDF</span></span>`;

    const $item = $(
      `<div class="receipt-item" title="${file.name}">
         ${thumb}
         <div class="receipt-name">${file.name}</div>
         <button type="button" class="receipt-x btn-remove-new-receipt" title="ลบ"><i class="bx bx-x"></i></button>
       </div>`
    );
    // ลบไฟล์ออกจาก input จริง ๆ (FileList แก้ตรง ๆ ไม่ได้ ต้องสร้าง DataTransfer ใหม่)
    $item.find('.btn-remove-new-receipt').on('click', function () {
      const dt = new DataTransfer();
      Array.from(input.files).forEach((f, i) => {
        if (i !== idx) dt.items.add(f);
      });
      input.files = dt.files;
      renderReceiptPreview(input);
    });
    $preview.append($item);
  });
  toggleReceiptEmpty();
}

$(document).on('change', '#accessory_receipt', function () {
  renderReceiptPreview(this);
});

// clear detail modal DOM after close (กัน backdrop ค้าง / focus)
$(document).on('hidden.bs.modal', '.commissionDetail', function () {
  $('.commissionDetailModel').empty();
});

//view report
$(document).on('hide.bs.modal', '.viewExportCom', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

$(document).on('click', '.btnViewExportCom', function () {
  $.get('/purchase-order/view-export-commission', function (html) {
    $('.viewExportComModel').html(html);
    $('.viewExportCom').modal('show');
  });
});

//view report gp
document.addEventListener('DOMContentLoaded', function () {
  const modalEl = document.querySelector('.viewExportGP');
  if (!modalEl) return; // กัน error

  const modal = new bootstrap.Modal(modalEl);
  modal.show();

  // ปิด modal แล้วกลับหน้าก่อนหน้า
  modalEl.addEventListener('hidden.bs.modal', function () {
    window.history.back();
  });
});
