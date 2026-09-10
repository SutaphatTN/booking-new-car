'use strict';

$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});

/* ============================================================
 * การตลาด > เงินเคลม (Form B)
 * รายการยิงจากสถานที่ — แก้ได้เฉพาะข้อมูลฝั่งเคลมใน modal
 * ============================================================ */

let claimTable;

const dtLang = {
  lengthMenu: 'แสดง _MENU_ แถว',
  zeroRecords: 'ไม่พบข้อมูล',
  info: 'แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ',
  infoEmpty: 'ไม่มีข้อมูล',
  search: 'ค้นหา:',
  paginate: { next: 'ถัดไป', previous: 'ก่อนหน้า' }
};

$(document).ready(function () {
  if ($.fn.DataTable.isDataTable('.claimTable')) {
    $('.claimTable').DataTable().destroy();
  }

  claimTable = $('.claimTable').DataTable({
    ajax: {
      url: '/source/claim/list',
      data: function (d) {
        d.state = $('#claimStateFilter').val() || 'month';
        d.month = $('#claimFilterMonth').val() || '';
      }
    },
    columns: [
      { data: 'No' },
      { data: 'location' },
      { data: 'las_number' },
      { data: 'date_range' },
      { data: 'form_b', className: 'text-end' },
      { data: 'Action', orderable: false, searchable: false }
    ],
    ordering: false,
    pageLength: 10,
    autoWidth: false,
    language: dtLang
  });

  // คุม loader overlay เอง — เลือก "ทั้งหมด" แล้วข้อมูลย้อนหลังอาจเยอะ
  claimTable.on('preXhr.dt', () => $('#claimLoadingOverlay').css('display', 'flex'));
  claimTable.on('xhr.dt', () => $('#claimLoadingOverlay').css('display', 'none'));
});

$(document).on('change', '#claimStateFilter', function () {
  $('#claimFilterMonth').toggleClass('d-none', $(this).val() === 'all');
  if (claimTable) claimTable.ajax.reload();
});

$(document).on('change', '#claimFilterMonth', function () {
  if (claimTable) claimTable.ajax.reload();
});

// ออกรายงาน Excel ตามตัวกรองที่ค้างอยู่บนหน้าจอ
$(document).on('click', '.btnClaimExport', function () {
  const state = $('#claimStateFilter').val() || 'month';
  const month = $('#claimFilterMonth').val() || '';

  if (state === 'month' && !month) {
    Swal.fire({ icon: 'warning', title: 'กรุณาเลือกเดือน' });
    return;
  }

  window.location = '/source/claim/export?state=' + state + '&month=' + encodeURIComponent(month);
});

/* ---------- ช่องเงิน: ตัวเลข + จุดทศนิยม แล้วใส่ comma คั่นหลักพัน ---------- */
$(document).on('input', '.claim-money', function () {
  let clean = this.value.replace(/[^\d.]/g, '');

  const firstDot = clean.indexOf('.');
  if (firstDot !== -1) {
    clean = clean.slice(0, firstDot + 1) + clean.slice(firstDot + 1).replace(/\./g, '');
  }

  if (clean === '' || clean === '.') {
    this.value = clean;
  } else {
    const [intPart, decPart] = clean.split('.');
    let out = Number(intPart).toLocaleString('en-US');
    if (clean.indexOf('.') !== -1) out += '.' + (decPart ?? '');
    this.value = out;
  }

  recalcClaim();
});

$(document).on('paste', '.claim-money', function () {
  const el = this;
  setTimeout(() => $(el).trigger('input'), 0);
});

function toNumber($el) {
  const raw = ($el.val() || '').replace(/,/g, '').trim();
  if (raw === '' || raw === '.') return null;
  const n = parseFloat(raw);
  return isNaN(n) ? null : n;
}

function fmt(n) {
  return n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

/** Form B (%) คำนวณทันทีตอนพิมพ์ Form B และ Diff = Form B (%) − ยอดเงินจริงในบัญชี */
function recalcClaim() {
  const $formB = $('#claim_form_b');
  if (!$formB.length) return;

  const share = parseFloat($formB.data('share')) || 0.5;
  const formB = toNumber($formB);
  const half = formB === null ? null : Math.round(formB * share * 100) / 100;

  $('#claim_form_b_half').val(half === null ? '' : fmt(half));

  const actual = toNumber($('#claim_actual_amount'));
  $('#claim_diff').val(half === null || actual === null ? '' : fmt(half - actual));
}

/* ---------- แก้ไข ---------- */
$(document).on('click', '.btnEditClaim', function () {
  const id = $(this).data('id');

  $.get('/source/claim/' + id + '/edit', function (html) {
    $('.editClaimModal').html(html);
    const $modal = $('.editClaim');
    $modal.modal('show');
    recalcClaim();

    $modal.find('.btnUpdateClaim').off('click').on('click', function (e) {
      e.preventDefault();
      submitClaimForm($(this), $modal);
    });
  });
});

function submitClaimForm($btn, $modal) {
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
      Swal.fire({
        title: 'กำลังบันทึกข้อมูล...',
        text: 'กรุณารอสักครู่',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
      });
      $btn.prop('disabled', true);
    },
    success: function (res) {
      Swal.fire({ icon: 'success', title: 'สำเร็จ', text: res.message, timer: 2000, showConfirmButton: true });
      claimTable.ajax.reload(null, false);
    },
    error: function (xhr) {
      $modal.modal('hide');
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
}

// blur focus กัน aria-hidden warning ตอนปิด modal
$(document).on('hide.bs.modal', '.editClaim', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});
