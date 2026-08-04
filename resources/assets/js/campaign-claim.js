$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});

//view : table campaign claim
let campaignClaimTable;

//view : column filter "สรุปผลการตรวจสอบ"
// null = ไม่กรอง / array = เลือกเฉพาะสถานะที่ติ๊ก ('none' = ยังไม่ตรวจสอบ)
let claimStatusColFilter = null;

// ใช้ได้เฉพาะตอนฟิลเตอร์ด้านบนเป็น "ทั้งหมด" — สถานะอื่นทุกแถวเป็นสถานะเดียวกันอยู่แล้ว กรองซ้ำไม่มีความหมาย
function claimStatusColUsable() {
  return $('#claimStatusFilter').val() === 'all';
}

function refreshClaimStatusColBtn() {
  const usable = claimStatusColUsable();
  $('#claimStatusColBtn')
    .prop('disabled', !usable)
    .css({ opacity: usable ? '' : 0.35, cursor: usable ? '' : 'not-allowed' })
    .attr('title', usable
      ? 'กรองสรุปผลการตรวจสอบ'
      : 'เลือกสถานะ "ทั้งหมด" ด้านบนก่อน จึงจะกรองคอลัมน์นี้ได้');

  if (!usable) {
    $('#claimStatusColDropdown').removeClass('show');
    $('#claimStatusColBtn').removeClass('active filtered');
  }
}

function resetClaimStatusColFilter() {
  claimStatusColFilter = null;
  $('.claim-status-chk').prop('checked', true);
  $('#claimStatusChkAll').prop({ indeterminate: false, checked: true });
  $('#claimStatusColBtn').removeClass('filtered active');
}

function syncClaimStatusChkAll() {
  const $items = $('.claim-status-chk:visible');
  const checked = $items.filter(':checked').length;
  const $all = $('#claimStatusChkAll');
  if ($items.length === 0 || checked === 0) {
    $all.prop({ indeterminate: false, checked: false });
  } else if (checked === $items.length) {
    $all.prop({ indeterminate: false, checked: true });
  } else {
    $all.prop({ indeterminate: true, checked: false });
  }
}

$(document).ready(function () {
  if ($.fn.DataTable.isDataTable('.campaignClaimTable')) {
    $('.campaignClaimTable').DataTable().destroy();
  }

  campaignClaimTable = $('.campaignClaimTable').DataTable({
    serverSide: true,
    processing: false,
    ajax: {
      url: '/campaign/claim/list',
      data: function (d) {
        d.status_filter = $('#claimStatusFilter').val();
        d.delivery_month = $('#claimMonthFilter').val(); // '' = ทุกเดือน
        if (claimStatusColFilter !== null) {
          d.status_ids = JSON.stringify(claimStatusColFilter);
        }
      }
    },
    columns: [
      { data: 'No', orderable: false },
      { data: 'customer', orderable: false },
      { data: 'saleName', orderable: false },
      // { data: 'model', orderable: false },
      { data: 'vin_number', orderable: false },
      { data: 'campaignType', orderable: false },
      { data: 'delivery_date', orderable: false, searchable: false },
      // { data: 'used', orderable: false, searchable: false, className: 'text-end' },
      // { data: 'claim_amount', orderable: false, searchable: false, className: 'text-end' },
      // { data: 'diff', orderable: false, searchable: false, className: 'text-end' },
      // { data: 'received_date', orderable: false, searchable: false },
      // searchable: false เพราะช่องค้นหายิงไปกรองที่ฝั่ง server (ลูกค้า/ฝ่ายขาย/VIN/ประเภท) ไม่รวมสถานะ
      { data: 'status', orderable: false, searchable: false, className: 'text-center' },
      // { data: 'note', orderable: false, searchable: false },
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
      paginate: {
        next: 'ถัดไป',
        previous: 'ก่อนหน้า'
      }
    }
  });

  //filter : reload on status change — เปลี่ยนสถานะด้านบนแล้วล้างฟิลเตอร์คอลัมน์เสมอ
  $(document).on('change', '#claimStatusFilter', function () {
    resetClaimStatusColFilter();
    refreshClaimStatusColBtn();
    campaignClaimTable.ajax.reload();
  });

  //filter : เดือนส่งมอบ — คนละแกนกับสถานะ ไม่ต้องล้างฟิลเตอร์คอลัมน์
  $(document).on('change', '#claimMonthFilter', function () {
    campaignClaimTable.ajax.reload();
  });

  refreshClaimStatusColBtn();

  //column filter : เปิด/ปิด dropdown — จัดตำแหน่งด้วยพิกัด fixed ให้พ้น overflow ของตาราง
  $(document).on('click', '#claimStatusColBtn', function (e) {
    e.stopPropagation();
    if (!claimStatusColUsable()) {
      return;
    }

    const $dd = $('#claimStatusColDropdown');
    if ($dd.hasClass('show')) {
      $dd.removeClass('show');
      $(this).removeClass('active');
      return;
    }

    const rect = this.getBoundingClientRect();
    $dd.css({ top: rect.bottom + 4 + 'px', left: rect.left + 'px' }).addClass('show');
    $(this).addClass('active');
    $('#claimStatusColSearch').val('').trigger('input').focus();
  });

  //column filter : ปิดเมื่อคลิกนอกกล่อง
  $(document).on('click', function (e) {
    if (!$(e.target).closest('#claimStatusColDropdown, #claimStatusColBtn').length) {
      $('#claimStatusColDropdown').removeClass('show');
      $('#claimStatusColBtn').removeClass('active');
    }
  });

  $(document).on('change', '#claimStatusChkAll', function () {
    $('.claim-status-chk:visible').prop('checked', $(this).is(':checked'));
  });

  $(document).on('change', '.claim-status-chk', function () {
    syncClaimStatusChkAll();
  });

  //column filter : ค้นหาในกล่อง
  $(document).on('input', '#claimStatusColSearch', function () {
    const q = $(this).val().toLowerCase();
    $('#claimStatusColList .col-filter-item:not(.col-filter-all)').each(function () {
      const label = $(this).find('label').text().toLowerCase();
      $(this).toggle(!q || label.includes(q));
    });
    syncClaimStatusChkAll();
  });

  //column filter : ตกลง
  $(document).on('click', '#claimStatusColApply', function () {
    const $all = $('.claim-status-chk');
    const checked = $all.filter(':checked').map(function () {
      return this.value;
    }).get();

    // ติ๊กครบ = ไม่กรอง (เหมือนฟิลเตอร์คอลัมน์หน้าอื่น)
    claimStatusColFilter = checked.length === $all.length ? null : checked;
    $('#claimStatusColBtn').toggleClass('filtered', claimStatusColFilter !== null);

    campaignClaimTable.ajax.reload(null, false);
    $('#claimStatusColDropdown').removeClass('show');
    $('#claimStatusColBtn').removeClass('active');
  });

  //column filter : ล้าง
  $(document).on('click', '#claimStatusColClear', function () {
    resetClaimStatusColFilter();
    campaignClaimTable.ajax.reload(null, false);
    $('#claimStatusColDropdown').removeClass('show');
  });

  campaignClaimTable.on('preXhr.dt', function () {
    $('#campaignClaimLoadingOverlay').css('display', 'flex');
  });
  campaignClaimTable.on('xhr.dt', function (e, settings, json) {
    $('#campaignClaimLoadingOverlay').css('display', 'none');

    // ป้ายเตือน "ไม่มีวันส่งมอบ" — นับจากชุดที่กำลังแสดง (ผ่านฟิลเตอร์/ค้นหาแล้ว)
    const n = (json && json.nullDeliveryCount) || 0;
    $('#claimNullDeliveryCount').text(n);
    $('#claimNullDeliveryBadge').toggle(n > 0);
  });
  // ยิงไม่สำเร็จ (timeout/500) → กันตัวโหลดค้างเต็มจอจนกดอะไรไม่ได้
  campaignClaimTable.on('error.dt', function () {
    $('#campaignClaimLoadingOverlay').css('display', 'none');
  });
});

// กดปุ่ม back กลับมา หน้าถูกกู้จาก bfcache → ตัวโหลดค้างสถานะเดิม ต้องซ่อนเอง
// เช็ค e.persisted เพราะ pageshow ยิงตอนเปิดหน้าปกติด้วย ซึ่งตอนนั้น ajax แรกยังโหลดอยู่
window.addEventListener('pageshow', function (e) {
  if (e.persisted) {
    $('#campaignClaimLoadingOverlay').css('display', 'none');
  }
});

//css : format number on money input
$(document).on('input', '.money-input', function () {
  let value = this.value.replace(/,/g, '');
  if (value === '' || isNaN(value)) {
    this.value = '';
    updateClaimDiff();
    return;
  }
  this.value = parseFloat(value).toLocaleString();
  updateClaimDiff();
});

$(document).on('blur', '.money-input', function () {
  let value = this.value.replace(/,/g, '');
  if (value && !isNaN(value)) {
    this.value = parseFloat(value).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }
});

//calculate diff = ยอดแคมเปญที่ใช้ - ยอดรับเคลม
function updateClaimDiff() {
  const used = parseFloat($('#claim_used').data('raw')) || 0;
  const claim = parseFloat(($('#claim_amount').val() || '').replace(/,/g, '')) || 0;
  const diff = used - claim;

  $('#claim_diff').val(diff.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
}

// blur focus editClaim
$(document).on('hide.bs.modal', '.editClaim', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

//edit : load modal
$(document).on('click', '.btnEditClaim', function () {
  const id = $(this).data('id');

  $.get('/campaign/claim/' + id + '/edit', function (html) {
    $('.editClaimModal').html(html);
    const $modal = $('.editClaim');

    $modal.modal('show');

    setTimeout(updateClaimDiff, 100);

    $modal
      .find('.btnUpdateClaim')
      .off('click')
      .on('click', function (e) {
        e.preventDefault();

        const $btn = $(this);
        const form = $modal.find('form')[0];
        const formData = new FormData(form);

        $.ajax({
          url: form.action,
          type: 'POST',
          data: formData,
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
            Swal.fire({
              icon: 'success',
              title: 'สำเร็จ!',
              text: res.message,
              timer: 2000,
              showConfirmButton: true
            });

            campaignClaimTable.ajax.reload(null, false);
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
