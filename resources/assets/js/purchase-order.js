$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});

//view

//view : table
let purchaseTable;

$(document).ready(function () {
  if ($.fn.DataTable.isDataTable('#purchaseTable')) {
    $('#purchaseTable').DataTable().destroy();
  }

  purchaseTable = $('#purchaseTable').DataTable({
    ajax: '/purchase-order/list',
    columns: [
      { data: 'No' },
      { data: 'FullName' },
      { data: 'IDNumber' },
      { data: 'Mobilephone' },
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

//view : delete
$(document).on('click', '.btnDeleteSale', function () {
  let id = $(this).data('id');

  Swal.fire({
    title: 'คุณแน่ใจหรือไม่?',
    text: 'คุณต้องการลบข้อมูลการจองของลูกค้าคนนี้ใช่หรือไม่?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#6c5ffc',
    cancelButtonColor: '#d33',
    confirmButtonText: 'ใช่, ลบเลย!',
    cancelButtonText: 'ยกเลิก'
  }).then(result => {
    if (result.isConfirmed) {
      $.ajax({
        url: '/purchase-order/' + id,
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
            purchaseTable.ajax.reload(null, false);
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

//view more

//view more : modal
$(document).on('click', '.btnViewSale', function () {
  const id = $(this).data('id');

  $.get('/purchase-order/' + id + '/view-more', function (html) {
    $('#viewMore').html(html);
    $('.viewPurchase').modal('show');
  });
});

//input

//input : open form turn car
document.addEventListener('DOMContentLoaded', function () {
  const yesRadio = document.getElementById('turnCarYes');
  const noRadio = document.getElementById('turnCarNo');
  const turnCarFields = document.getElementById('turnCarFields');

  function toggleTurnCarFields() {
    if (yesRadio.checked) {
      turnCarFields.style.display = 'flex';
    } else {
      turnCarFields.style.display = 'none';
    }
  }

  yesRadio.addEventListener('change', toggleTurnCarFields);
  noRadio.addEventListener('change', toggleTurnCarFields);

  toggleTurnCarFields();
});

//input : search customer
$(document).ready(function () {
  const $searchInput = $('#customerSearch');
  const $modal = $('#modalSearchCustomer');
  const $tableBody = $('#tableSelectCustomer tbody');

  $searchInput.on('keypress', function (e) {
    if (e.which === 13) {
      e.preventDefault();
      searchCustomer($(this).val());
    }
  });

  $('.btnSearchCustomer').on('click', function () {
    searchCustomer($searchInput.val());
  });

  function searchCustomer(keyword) {
    if (!keyword.trim()) return;

    $.ajax({
      url: '/customers/search',
      type: 'GET',
      data: { keyword },
      success: function (res) {
        $tableBody.empty();

        if (res.length === 0) {
          $tableBody.append(`<tr><td colspan="4" class="text-center">ไม่พบข้อมูลลูกค้า</td></tr>`);
        } else {
          res.forEach(c => {
            $tableBody.append(`
              <tr>
                <td>${c.PrefixNameTH ?? '-'} ${c.FirstName} ${c.LastName}</td>
                <td>${c.Mobilephone1 ?? '-'}</td>
                <td>${c.IDNumber ?? '-'}</td>
                <td>
                  <button class="btn btn-sm btn-primary btnSelectCustomer"
                    data-id="${c.id}"
                    data-name="${c.PrefixNameTH ?? '-'} ${c.FirstName} ${c.LastName}"
                    data-mobile="${c.Mobilephone1 ?? ''}"
                    data-idnumber="${c.IDNumber ?? ''}">
                    เลือก
                  </button>
                </td>
              </tr>
            `);
          });
        }

        $modal.modal('show');
      },
      error: function (xhr) {
        console.error('Search error:', xhr);
      }
    });
  }

  $(document).on('click', '.btnSelectCustomer', function () {
    const data = $(this).data();

    $('#customerName').val(data.name);
    $('#customerPhone').val(data.mobile);
    $('#customerID').val(data.idnumber);
    $('#CusID').val(data.id);

    $modal.modal('hide');

    $searchInput.val('');
  });
});

//input : save purchase
document.addEventListener('DOMContentLoaded', function () {
  $(document).on('click', '.btnSavePurchase', function (e) {
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
          window.location.href = '/purchase-order';
        }, 1000);
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
        $btn.prop('disabled', false).text('บันทึก');
      }
    });
  });
});

//edit

//edit : next/previous button
document.addEventListener('DOMContentLoaded', function () {
  //Next
  document.getElementById('nextDate').addEventListener('click', function (e) {
    e.preventDefault();
    const nextTabTrigger = document.querySelector('[data-bs-target="#tab-date"]');
    const nextTab = new bootstrap.Tab(nextTabTrigger);
    nextTab.show();
  });

  document.getElementById('nextAccessory').addEventListener('click', function (e) {
    e.preventDefault();
    const nextTabTrigger = document.querySelector('[data-bs-target="#tab-accessory-gift"]');
    const nextTab = new bootstrap.Tab(nextTabTrigger);
    nextTab.show();
  });

  document.getElementById('nextApproved').addEventListener('click', function (e) {
    e.preventDefault();
    const nextTabTrigger = document.querySelector('[data-bs-target="#tab-approved"]');
    const nextTab = new bootstrap.Tab(nextTabTrigger);
    nextTab.show();
  });

  document.getElementById('nextPrice').addEventListener('click', function (e) {
    e.preventDefault();
    const nextTabTrigger = document.querySelector('[data-bs-target="#tab-price"]');
    const nextTab = new bootstrap.Tab(nextTabTrigger);
    nextTab.show();
  });

  //Previous
  document.getElementById('prevDetail').addEventListener('click', function (e) {
    e.preventDefault();
    const prevTabTrigger = document.querySelector('[data-bs-target="#tab-detail"]');
    const prevTab = new bootstrap.Tab(prevTabTrigger);
    prevTab.show();
  });

  document.getElementById('prevPrice').addEventListener('click', function (e) {
    e.preventDefault();
    const prevTabTrigger = document.querySelector('[data-bs-target="#tab-price"]');
    const prevTab = new bootstrap.Tab(prevTabTrigger);
    prevTab.show();
  });

  document.getElementById('prevDate').addEventListener('click', function (e) {
    e.preventDefault();
    const prevTabTrigger = document.querySelector('[data-bs-target="#tab-date"]');
    const prevTab = new bootstrap.Tab(prevTabTrigger);
    prevTab.show();
  });

  document.getElementById('prevAccessory').addEventListener('click', function (e) {
    e.preventDefault();
    const prevTabTrigger = document.querySelector('[data-bs-target="#tab-accessory-gift"]');
    const prevTab = new bootstrap.Tab(prevTabTrigger);
    prevTab.show();
  });

  document.getElementById('prevApproved').addEventListener('click', function (e) {
    e.preventDefault();
    const prevTabTrigger = document.querySelector('[data-bs-target="#tab-approved"]');
    const prevTab = new bootstrap.Tab(prevTabTrigger);
    prevTab.show();
  });
});

document.addEventListener('DOMContentLoaded', function () {
  const radios = document.querySelectorAll('input[name="paymentCondition"]');
  const sections = {
    creditCheck: document.getElementById('creditFields'),
    cashCheck: document.getElementById('cashFields'),
    moneyCheck: document.getElementById('moneyFields'),
    financeCheck: document.getElementById('financeFields')
  };

  function toggleSection() {
    for (const key in sections) {
      sections[key].style.display = 'none';
    }
    const selected = document.querySelector('input[name="paymentCondition"]:checked');
    if (selected) {
      const section = sections[selected.id];
      if (section) section.style.display = 'block';
    }
  }

  radios.forEach(radio => radio.addEventListener('change', toggleSection));

  toggleSection();
});

//edit : modal remove accessory
$(document).on('click', '.btnDeleteRow', function () {
  $(this).closest('.accessory-row').remove();
});

//edit : modal remove gift
$(document).on('click', '.btnDeleteRow', function () {
  $(this).closest('.gift-row').remove();
});

//edit : redPlateOption
document.addEventListener('DOMContentLoaded', function () {
  const acceptRadio = document.getElementById('acceptRedPlate');
  const rejectRadio = document.getElementById('rejectRedPlate');
  const costInput = document.getElementById('redPlateCost');
  const provinceSelect = document.getElementById('province');

  function toggleRedPlateFields(enable) {
    costInput.disabled = !enable;
    provinceSelect.disabled = !enable;
    if (!enable) {
      costInput.value = '';
      provinceSelect.value = '';
    }
  }

  acceptRadio.addEventListener('change', () => toggleRedPlateFields(true));
  rejectRadio.addEventListener('change', () => toggleRedPlateFields(false));
});

//edit : accessory and gift
$(document).ready(function () {
  /**
   * Generic function สำหรับทั้ง accessory และ gift
   * @param {Object} options
   *   options = {
   *     type: 'accessory' | 'gift',
   *     btnOpen: '.btnAccessory' | '.btnGift',
   *     btnSearch: '.btnAccessorySearch' | '.btnGiftSearch',
   *     searchInput: '#accessorySearch' | '#giftSearch',
   *     modal: '.viewAccessory' | '.viewGift',
   *     tableBody: '#tableAccessoryResult tbody' | '#tableGiftResult tbody',
   *     mainTable: '#accessoryTablePrice tbody' | '#giftTable tbody',
   *     hiddenIds: '#accessory_ids' | '#gift_ids',
   *     hiddenTotal: '#total_accessory_used' | '#total_gift_used',
   *     totalDisplay: '#total-price' | '#total-price-gift',
   *     noDataRowId: '#no-data-row' | '#no-data-gift',
   *     deleteBtnClass: '.btn-delete-accessory' | '.btn-delete-gift'
   *   }
   */

  // =========================
  // ฟังก์ชันดึง selected IDs
  // =========================
  function getSelectedAccessoryIds() {
    const ids = [];
    $('#accessoryTablePrice tbody tr[data-id]').each(function () {
      ids.push($(this).data('id'));
    });
    return ids;
  }

  function getSelectedGiftIds() {
    const ids = [];
    $('#giftTable tbody tr[data-id]').each(function () {
      ids.push($(this).data('id'));
    });
    return ids;
  }

  // =========================
  // ฟังก์ชัน update grand totals
  // =========================
  function updateGrandTotals() {
    const giftTotal = parseFloat($('#total-price-gift').text().replace(/,/g, '')) || 0;
    const accessoryTotal = parseFloat($('#total-price').text().replace(/,/g, '')) || 0;

    $('#TotalAccessoryGift').val(accessoryTotal);
    $('#TotalAccessoryExtra').val(giftTotal);
  }

  // =========================
  // ฟังก์ชัน init accessory & gift
  // =========================
  function initAccessoryGift(options) {
    const $searchInput = $(options.searchInput);
    const $modal = $(options.modal);
    const $tableBody = $(options.tableBody);
    const $mainTable = $(options.mainTable);

    // เปิด modal
    $(options.btnOpen).on('click', function () {
      $modal.modal('show');
      const CarModelID = $('#CarModelID').val();
      $searchInput.val('');
      $tableBody.empty();

      if (CarModelID) {
        searchItem('');
      } else {
        $tableBody.append(`<tr><td colspan="5" class="text-center text-danger">กรุณาเลือกรุ่นรถก่อน</td></tr>`);
      }
    });

    // กด Enter ค้นหา
    $searchInput.on('keypress', function (e) {
      if (e.which === 13) {
        e.preventDefault();
        searchItem($searchInput.val());
      }
    });

    // ปุ่ม search
    $(options.btnSearch).on('click', function () {
      searchItem($searchInput.val());
    });

    // ฟังก์ชัน search item
    function searchItem(keyword = '') {
      const selectedAccessoryIds = getSelectedAccessoryIds();
      const selectedGiftIds = getSelectedGiftIds();
      const excludeIds = [...selectedAccessoryIds, ...selectedGiftIds];

      const CarModelID = $('#CarModelID').val();
      if (!CarModelID) return;

      $.ajax({
        url: '/accessory/search',
        type: 'GET',
        data: { keyword, CarModelID, exclude_ids: excludeIds },
        success: function (res) {
          $tableBody.empty();

          if (res.length === 0) {
            $tableBody.append(`<tr><td colspan="5" class="text-center">ไม่พบข้อมูล</td></tr>`);
            return;
          }

          res.forEach(a => {
            const costCell = a.accessoryCost
              ? `<input type="radio" name="priceType_${a.id}" value="cost"
                  data-id="${a.id}" data-source="${a.AccessorySource}"
                  data-detail="${a.AccessoryDetail}" data-price="${a.accessoryCost}"
                  data-com="${a.AccessoryComCost ?? 0}">
                <span class="ms-1">${a.accessoryCost}</span>`
              : `<span>-</span>`;

            const promoCell = a.AccessoryPromoPrice
              ? `<input type="radio" name="priceType_${a.id}" value="promo"
                  data-id="${a.id}" data-source="${a.AccessorySource}"
                  data-detail="${a.AccessoryDetail}" data-price="${a.AccessoryPromoPrice}"
                  data-com="${a.AccessoryComPromo ?? 0}">
                <span class="ms-1">${a.AccessoryPromoPrice}</span>`
              : `<span>-</span>`;

            const saleCell = a.AccessorySalePrice
              ? `<input type="radio" name="priceType_${a.id}" value="sale"
                  data-id="${a.id}" data-source="${a.AccessorySource}"
                  data-detail="${a.AccessoryDetail}" data-price="${a.AccessorySalePrice}"
                  data-com="${a.AccessoryComSale ?? 0}">
                <span class="ms-1">${a.AccessorySalePrice}</span>`
              : `<span>-</span>`;

            $tableBody.append(`
              <tr>
                <td class="text-center">${a.AccessorySource ?? '-'}</td>
                <td>${a.AccessoryDetail ?? '-'}</td>
                <td class="text-center">${costCell}</td>
                <td class="text-center">${promoCell}</td>
                <td class="text-center">${saleCell}</td>
              </tr>
            `);
          });
        }
      });
    }

    // เรียงลำดับ
    function reindexRows() {
      $mainTable.find(`tr:not(${options.noDataRowId})`).each(function (index) {
        $(this)
          .find('td:first')
          .text(index + 1);
      });
    }

    // รวมยอดรวม
    function updateTotal() {
      let total = 0;
      $mainTable.find(`tr:not(${options.noDataRowId})`).each(function () {
        const price = parseFloat($(this).data('price')) || 0;
        total += price;
      });
      $(options.totalDisplay).text(total.toLocaleString());
      updateGrandTotals();
    }

    // ซ่อน/โชว์แถวว่าง
    function checkEmptyTable() {
      const rows = $mainTable.find(`tr:not(${options.noDataRowId})`).length;
      if (rows === 0 && $mainTable.find(options.noDataRowId).length === 0) {
        $mainTable.append(`
          <tr id="${options.noDataRowId.replace('#', '')}">
            <td colspan="7" class="text-center">ยังไม่มีข้อมูล</td>
          </tr>
        `);
      }
    }

    // ลบแถว
    $(document).on('click', options.deleteBtnClass, function () {
      $(this).closest('tr').remove();
      reindexRows();
      updateTotal();
      updateHiddenInputs();
    });

    // update hidden input
    function updateHiddenInputs() {
      const ids = [];
      $mainTable.find(`tr:not(${options.noDataRowId})`).each(function () {
        const id = $(this).data('id');
        if (id) ids.push(id);
      });

      $(options.hiddenIds).val(ids.join(','));
      const total = parseFloat($(options.totalDisplay).text().replace(/,/g, '')) || 0;
      $(options.hiddenTotal).val(total).trigger('change');

      checkEmptyTable();
    }

    // บันทึก modal
    $modal.find('button.save-item, #btnSaveAccessory, #btnSaveGift').on('click', function () {
      const selected = $tableBody.find('input[type="radio"]:checked');
      if (selected.length === 0) {
        Swal.fire({ icon: 'warning', title: 'กรุณาเลือกราคาอย่างน้อยหนึ่งรายการ', confirmButtonText: 'ตกลง' });
        return;
      }

      const existingSources = [];
      $mainTable.find(`tr:not(${options.noDataRowId})`).each(function () {
        const source = $(this).find('td').eq(1).text().trim();
        if (source) existingSources.push(source);
      });

      let isDuplicate = false;
      selected.each(function () {
        const source = $(this).data('source');
        if (existingSources.includes(source)) isDuplicate = true;
      });

      if (isDuplicate) {
        Swal.fire({ icon: 'warning', title: 'คุณได้เพิ่มข้อมูลนี้ไปแล้ว', confirmButtonText: 'ตกลง' });
        $searchInput.val('');
        $tableBody.empty();
        return;
      }

      selected.each(function () {
        const $radio = $(this);
        const source = $radio.data('source');
        const detail = $radio.data('detail');
        const type = $radio.val();
        const price = $radio.data('price') || '-';
        const typeLabel = { cost: 'ราคาทุน', promo: 'ราคาพิเศษ', sale: 'ราคาขาย' }[type];
        const com = $radio.data('com') ?? '-';

        $mainTable.find(options.noDataRowId).remove();
        $mainTable.append(`
        <tr data-id="${$radio.data('id')}" data-price="${price}" data-com="${com}">
          <td></td>
          <td>${source}</td>
          <td>${detail}</td>
          <td>${typeLabel}</td>
          <td>${price}</td>
          <td>${com}</td>
          <td>
            <button type="button" class="btn btn-sm btn-danger ${options.deleteBtnClass.replace('.', '')}">
              <i class="bx bx-trash"></i>
            </button>
          </td>
        </tr>
      `);
      });

      reindexRows();
      updateTotal();
      updateHiddenInputs();

      Swal.fire({
        icon: 'success',
        title: 'เพิ่มข้อมูลเรียบร้อย',
        showConfirmButton: true,
        timer: 1200
      });

      searchItem($searchInput.val());
    });

    // expose updateTotal และ updateHiddenInputs
    return { updateTotal, updateHiddenInputs };
  }

  // =========================
  // เรียก init ทั้ง 2 ตาราง
  // =========================
  const accessoryFuncs = initAccessoryGift({
    type: 'accessory',
    btnOpen: '.btnAccessory',
    btnSearch: '.btnAccessorySearch',
    searchInput: '#accessorySearch',
    modal: '.viewAccessory',
    tableBody: '#tableAccessoryResult tbody',
    mainTable: '#accessoryTablePrice tbody',
    hiddenIds: '#accessory_ids',
    hiddenTotal: '#total_accessory_used',
    totalDisplay: '#total-price',
    noDataRowId: '#no-data-row',
    deleteBtnClass: '.btn-delete-accessory'
  });

  const giftFuncs = initAccessoryGift({
    type: 'gift',
    btnOpen: '.btnGift',
    btnSearch: '.btnGiftSearch',
    searchInput: '#giftSearch',
    modal: '.viewGift',
    tableBody: '#tableGiftResult tbody',
    mainTable: '#giftTable tbody',
    hiddenIds: '#gift_ids',
    hiddenTotal: '#total_gift_used',
    totalDisplay: '#total-price-gift',
    noDataRowId: '#no-data-gift',
    deleteBtnClass: '.btn-delete-gift'
  });

  // =========================
  // เมื่อเปลี่ยน CarModel
  // =========================
  $('#CarModelID').on('change', function () {
    $('#accessoryTablePrice tbody').html(
      `<tr id="no-data-row"><td colspan="7" class="text-center">ยังไม่มีข้อมูล</td></tr>`
    );
    $('#accessory_ids').val('');
    $('#total_accessory_used').val(0);
    $('#total-price').text('0');

    $('#giftTable tbody').html(`<tr id="no-data-gift"><td colspan="7" class="text-center">ยังไม่มีข้อมูล</td></tr>`);
    $('#gift_ids').val('');
    $('#total_gift_used').val(0);
    $('#total-price-gift').text('0');

    updateGrandTotals();
  });

  // =========================
  // ฟังก์ชันคำนวณยอดรวมตอนโหลดหน้า edit
  // =========================
  function initGrandTotalOnLoad() {
    // accessory
    let accessoryTotal = 0;
    $('#accessoryTablePrice tbody tr:not(#no-data-row)').each(function () {
      accessoryTotal += parseFloat($(this).data('price')) || 0;
    });
    $('#total-price').text(
      accessoryTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    );
    $('#total_accessory_used').val(accessoryTotal);

    // gift
    let giftTotal = 0;
    $('#giftTable tbody tr:not(#no-data-gift)').each(function () {
      giftTotal += parseFloat($(this).data('price')) || 0;
    });
    $('#total-price-gift').text(
      giftTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    );
    $('#total_gift_used').val(giftTotal);

    // update hidden field
    updateGrandTotals();
  }

  // =========================
  // เรียกตอนโหลดหน้า
  // =========================
  initGrandTotalOnLoad();

  // =========================
  // บันทึกข้อมูล
  // =========================
  $(document).on('click', '.btnUpdatePurchase', function (e) {
    e.preventDefault();

    const $btn = $(this);
    const $form = $('form');
    const actionUrl = $form.attr('action');
    const formData = new FormData($form[0]);

    const accessoriesGift = [];
    $('#accessoryTablePrice tbody tr:not(#no-data-row)').each(function () {
      accessoriesGift.push({
        id: $(this).data('id'),
        price_type: $(this).find('td').eq(3).text().trim(),
        price: parseFloat($(this).data('price')),
        commission: parseFloat($(this).data('com')),
        type: 'gift'
      });
    });

    const accessoriesExtra = [];
    $('#giftTable tbody tr:not(#no-data-gift)').each(function () {
      accessoriesExtra.push({
        id: $(this).data('id'),
        price_type: $(this).find('td').eq(3).text().trim(),
        price: parseFloat($(this).data('price')),
        commission: parseFloat($(this).data('com')),
        type: 'extra'
      });
    });

    const accessories = [...accessoriesGift, ...accessoriesExtra];
    formData.append('accessories', JSON.stringify(accessories));

    $.ajax({
      url: actionUrl,
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
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
          showConfirmButton: true,
          timer: 2000
        });
        setTimeout(() => {
          window.location.href = '/purchase-order';
        }, 1000);
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
        $btn.prop('disabled', false).text('บันทึก');
      }
    });
  });
});

//edit : campaign
$(document).ready(function () {
  // เปิดใช้งาน select2
  $('#CampaignID').select2({
    placeholder: 'เลือกแคมเปญ',
    width: '100%'
  });

  //ยอดรวม
  function calcTotalCampaign() {
    let total = 0;
    $('#CampaignID option:selected').each(function () {
      const cash = parseFloat($(this).data('cashsupport')) || 0;
      total += cash;
    });
    $('#TotalSaleCampaign').val(total.toFixed(2));
  }

  // คำนวณเมื่อเปลี่ยนค่า
  $('#CampaignID').on('change', calcTotalCampaign);

  // เรียกคำนวณตอนเปิดหน้า (กรณีมี selected อยู่แล้ว)
  calcTotalCampaign();

  // --- เมื่อเปลี่ยนรุ่นรถ ---
  $('#CarModelID').on('change', function () {
    const carModelID = $(this).val();
    $('#CampaignID').empty().trigger('change');

    if (!carModelID) return;

    $.ajax({
      url: '/purchase-order/get-campaign',
      type: 'GET',
      data: { CarModelID: carModelID },
      success: function (res) {
        if (res.length === 0) {
          $('#CampaignID').append(new Option('ไม่มีแคมเปญ', '', false, false));
          $('#CampaignID').trigger('change');
          return;
        }

        res.forEach(c => {
          const option = new Option(`${c.SubCampaignType} - ${c.CashSupport} บาท`, c.id, false, false);
          $(option).attr('data-cashsupport', c.CashSupport);
          $('#CampaignID').append(option);
        });

        $('#CampaignID').trigger('change');
      }
    });
  });
});

//edit : auto value -> บวกหัว (90%), ราคาสุทธิบวกหัว
document.addEventListener('DOMContentLoaded', function () {
  const salePriceInput = document.getElementById('CarSalePrice'); // ราคาเงินสด
  const markupInput = document.getElementById('MarkupPrice'); // บวกหัว
  const markup90Input = document.querySelector('input[name="Markup90"]'); // ช่องบวกหัว(90%)
  const finalPriceInput = document.getElementById('CarSalePriceFinal'); // ราคาขายสุทธิ

  //เงินดาวน์
  const downPaymentInput = document.getElementById('DownPayment'); // เงินดาวน์
  const downPaymentPercentInput = document.getElementById('DownPaymentPercentage'); // %

  //ราคารถ
  function calculateCarPrice() {
    const salePrice = parseFloat(salePriceInput.value.replace(/,/g, '')) || 0;
    const markup = parseFloat(markupInput.value.replace(/,/g, '')) || 0;

    // คำนวณบวกหัว (90%)
    const markup90 = markup * 0.9;

    // ราคาขายสุทธิ = ราคาเงินสด + บวกหัว
    const finalPrice = salePrice + markup;

    // ใส่ค่าในช่อง
    markup90Input.value = markup90.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    finalPriceInput.value = finalPrice.toLocaleString(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });

    downPaymentInput.value = '';
    downPaymentPercentInput.value = '';

    calculateRemaining();

    return { markup90, finalPrice };
  }

  //เงินดาวน์
  function calculateDownPayment() {
    const finalPrice = parseFloat(finalPriceInput.value.replace(/,/g, '')) || 0;
    const downPayment = parseFloat(downPaymentInput.value.replace(/,/g, '')) || 0;
    const downPercent = parseFloat(downPaymentPercentInput.value.replace(/,/g, '')) || 0;

    if (document.activeElement === downPaymentInput) {
      // กรอกเงิน
      const percent = finalPrice > 0 ? (downPayment / finalPrice) * 100 : 0;
      downPaymentPercentInput.value = percent.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    } else if (document.activeElement === downPaymentPercentInput) {
      // กรอก %
      const dp = (finalPrice * downPercent) / 100;
      downPaymentInput.value = dp.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

      $(downPaymentInput).trigger('input');
    }

    if (document.activeElement === downPaymentInput) {
      calculateRemaining();
    }
  }

  //ราคาขาย
  salePriceInput.addEventListener('input', calculateCarPrice);
  markupInput.addEventListener('input', calculateCarPrice);

  //ดาวน์
  downPaymentInput.addEventListener('input', calculateDownPayment);
  downPaymentPercentInput.addEventListener('input', calculateDownPayment);
});

//edit : total payment delivery + clone value รุ่นรถ สี เงินจอง + ยอดที่เหลือ
$(document).ready(function () {
  function calculateTotalPaymentAtDelivery() {
    const downPayment = parseFloat($('#DownPayment').val().replace(/,/g, '')) || 0;
    const downDiscount = parseFloat($('#DownPaymentDiscount').val().replace(/,/g, '')) || 0;
    const accessoryTotal = parseFloat($('#total_accessory_used').val().replace(/,/g, '')) || 0;
    const additionFromCustomer = parseFloat($('#AdditionFromCustomer').val().replace(/,/g, '')) || 0;
    const tradeinAddition = parseFloat($('#TradeinAddition').val().replace(/,/g, '')) || 0;
    const cashDeposit = parseFloat($('#summaryCashDeposit').val().replace(/,/g, '')) || 0;

    const total = (downPayment + additionFromCustomer) - (downDiscount + tradeinAddition + cashDeposit);

    $('#TotalPaymentatDeliveryDisplay').text(
      total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    );

    $('#TotalPaymentatDelivery').val(total);

    calculateRemaining();
  }

  function updateSummary() {
    const carModel = $('#CarModelID option:selected').text();
    const color = $('#Color').val();
    const cashDeposit = parseFloat($('#CashDeposit').val()) || 0;

    $('#summaryCarModel').val(carModel);
    $('#summaryColor').val(color);
    $('#summaryCashDeposit').val(cashDeposit.toLocaleString(undefined, { minimumFractionDigits: 2 }));

    calculateTotalPaymentAtDelivery();
  }

  function calculateRemaining() {
    const finalPrice = parseFloat($('#CarSalePriceFinal').val().replace(/,/g, '')) || 0;
    const downPayment = parseFloat($('#DownPayment').val().replace(/,/g, '')) || 0;

    const remaining = finalPrice - downPayment;

    // แสดงยอดที่เหลือ
    $('#RemainingAmountDisplay').text(
      remaining.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    );

    // เก็บค่าลง hidden input เพื่อบันทึกเข้าฐาน
    $('#TotalCashSupportUsed').val(remaining);
  }

  $('#CarModelID, #Color, #CashDeposit').on('input change', updateSummary);
  $('#DownPayment, #DownPaymentDiscount, #AdditionFromCustomer, #TradeinAddition, #total_accessory_used').on(
    'input change',
    calculateTotalPaymentAtDelivery
  );
  $('#CarSalePriceFinal, #DownPayment').on('input change', calculateRemaining);

  updateSummary();
});
