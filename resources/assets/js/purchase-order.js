$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});

//format
function formatNumber(num) {
  if (num === null || num === undefined || num === '') return '-';
  const n = parseFloat(num);
  if (isNaN(n)) return '-';
  return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

//safe number
function safeNumber(selector) {
  const el = $(selector);
  if (!el.length) return 0;
  const val = el.val();
  if (!val) return 0;
  return parseFloat(val.replace(/,/g, '')) || 0;
}

//use css
$(document).ready(function () {
  $('.money-input').each(function () {
    let value = $(this).val();
    if (value && !isNaN(value.replace(/,/g, ''))) {
      $(this).val(
        parseFloat(value.replace(/,/g, '')).toLocaleString(undefined, {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        })
      );
    }
  });
});

$(document).on('input', '.money-input', function () {
  let value = this.value.replace(/,/g, '');
  if (value === '' || isNaN(value)) {
    this.value = '';
    return;
  }
  this.value = parseFloat(value).toLocaleString();
});

$(document).on('blur', '.money-input', function () {
  let value = this.value.replace(/,/g, '');
  if (value && !isNaN(value)) {
    this.value = parseFloat(value).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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
    ajax: {
      url: '/purchase-order/list',
      data: function (d) {
        d.status = $('#filterStatus').val();
      }
    },
    columns: [
      { data: 'No' },
      { data: 'FullName' },
      { data: 'subSale' },
      { data: 'statusSale' },
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

  $('#filterStatus').on('change', function () {
    purchaseTable.ajax.reload();
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
document.addEventListener('DOMContentLoaded', () => {
  const yesRadio = document.getElementById('turnCarYes');
  if (!yesRadio) return;

  const noRadio = document.getElementById('turnCarNo');
  const turnCarFields = document.getElementById('turnCarFields');

  function toggleTurnCarFields() {
    turnCarFields.style.display = yesRadio.checked ? 'flex' : 'none';
  }

  yesRadio.addEventListener('change', toggleTurnCarFields);
  noRadio.addEventListener('change', toggleTurnCarFields);

  toggleTurnCarFields();
});

//input : search customer
$(document).ready(function () {
  setupCustomerSearch({
    searchInput: '#customerSearch',
    nameInput: '#customerName',
    phoneInput: '#customerPhone',
    idInput: '#customerID',
    hiddenId: '#CusID'
  });

  setupCustomerSearch({
    searchInput: '#customerSearchRef',
    nameInput: '#customerNameRef',
    phoneInput: '#customerPhoneRef',
    idInput: '#customerIDRef',
    hiddenId: '#ReferrerID'
  });
});

function setupCustomerSearch({ searchInput, nameInput, phoneInput, idInput, hiddenId }) {
  const $search = $(searchInput);
  const $modal = $('#modalSearchCustomer');
  const $tableBody = $('#tableSelectCustomer tbody');

  $search.on('keypress', function (e) {
    if (e.which === 13) {
      e.preventDefault();
      searchCustomer($(this).val());
    }
  });

  $search.siblings('.btnSearchCustomer').on('click', function () {
    searchCustomer($search.val());
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
                <td>${c.formatted_mobile ?? '-'}</td>
                <td>${c.formatted_id_number ?? '-'}</td>
                <td>
                  <button class="btn btn-sm btn-primary btnSelectCustomer"
                    data-id="${c.id}"
                    data-name="${c.PrefixNameTH ?? '-'} ${c.FirstName} ${c.LastName}"
                    data-mobile="${c.formatted_mobile ?? ''}"
                    data-idnumber="${c.formatted_id_number ?? ''}"
                    data-target="${searchInput}">
                    เลือก
                  </button>
                </td>
              </tr>
            `);
          });
        }

        $modal.modal('show');
      }
    });
  }

  $(document).on('click', '.btnSelectCustomer', function () {
    const data = $(this).data();

    if (data.target === searchInput) {
      $(nameInput).val(data.name);
      $(phoneInput).val(data.mobile);
      $(idInput).val(data.idnumber);
      $(hiddenId).val(data.id);

      $modal.modal('hide');
      $search.val('');
    }
  });
}

//input : radio payment reservation
document.addEventListener('DOMContentLoaded', function () {
  const radios = document.querySelectorAll('input[name="reservationCondition"]');
  const bankSection = document.getElementById('bankSection');
  const checkSection = document.getElementById('checkSection');
  const creditSection = document.getElementById('creditSection');

  if (!radios.length || !bankSection || !checkSection || !creditSection) return;

  function toggleSection() {
    bankSection.style.display = 'none';
    checkSection.style.display = 'none';
    creditSection.style.display = 'none';

    const selected = document.querySelector('input[name="reservationCondition"]:checked');
    if (!selected) return;

    if (selected.value === 'transfer') {
      bankSection.style.display = 'block';
    } else if (selected.value === 'check') {
      checkSection.style.display = 'block';
    } else if (selected.value === 'credit') {
      creditSection.style.display = 'block';
    }
  }

  radios.forEach(radio => radio.addEventListener('change', toggleSection));
  toggleSection();
});

//input : get sub model
$(document).on('change', '#model_id', function () {
  const modelId = $(this).val();
  const $subModelSelect = $('#subModel_id');

  $subModelSelect.empty().append('<option value="">-- เลือกรุ่นรถย่อย --</option>');

  if (!modelId) return;

  $.ajax({
    url: '/api/purchase-order/sub-model/' + modelId,
    type: 'GET',
    success: function (data) {
      console.log('data:', data);
      if (data.length > 0) {
        data.forEach(function (sub) {
          $subModelSelect.append(`<option value="${sub.id}">${sub.name}</option>`);
        });
      } else {
        $subModelSelect.append('<option value="">-- ไม่มีรุ่นย่อย --</option>');
      }
    },
    error: function () {
      alert('เกิดข้อผิดพลาดในการโหลดข้อมูลรุ่นย่อย');
    }
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
  function bindClick(id, callback) {
    const el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('click', function (e) {
      e.preventDefault();
      callback();
    });
  }

  // Next
  bindClick('nextCampaign', () => {
    const priceTabTrigger = document.querySelector('[data-bs-target="#tab-price"]');
    if (priceTabTrigger) new bootstrap.Tab(priceTabTrigger).show();

    setTimeout(() => {
      const campaignTabTrigger = document.querySelector('[data-bs-target="#tab-campaign"]');
      if (campaignTabTrigger) new bootstrap.Tab(campaignTabTrigger).show();
    }, 200);
  });

  bindClick('nextAccessory', () => {
    const trg = document.querySelector('[data-bs-target="#tab-accessory"]');
    if (trg) new bootstrap.Tab(trg).show();
  });

  bindClick('nextExtra', () => {
    const trg = document.querySelector('[data-bs-target="#tab-extra"]');
    if (trg) new bootstrap.Tab(trg).show();
  });

  bindClick('nextCar', () => {
    const trg = document.querySelector('[data-bs-target="#tab-car"]');
    if (trg) new bootstrap.Tab(trg).show();
  });

  bindClick('nextDate', () => {
    const trgMore = document.querySelector('[data-bs-target="#tab-more"]');
    if (trgMore) new bootstrap.Tab(trgMore).show();

    setTimeout(() => {
      const trgDate = document.querySelector('[data-bs-target="#tab-date"]');
      if (trgDate) new bootstrap.Tab(trgDate).show();
    }, 200);
  });

  bindClick('nextApproved', () => {
    const trg = document.querySelector('[data-bs-target="#tab-approved"]');
    if (trg) new bootstrap.Tab(trg).show();
  });

  //Previous

  bindClick('prevDetail', () => {
    const trg = document.querySelector('[data-bs-target="#tab-detail"]');
    if (trg) new bootstrap.Tab(trg).show();
  });

  bindClick('prevCampaign', () => {
    const trg = document.querySelector('[data-bs-target="#tab-campaign"]');
    if (trg) new bootstrap.Tab(trg).show();
  });

  bindClick('prevAccessory', () => {
    const trg = document.querySelector('[data-bs-target="#tab-accessory"]');
    if (trg) new bootstrap.Tab(trg).show();
  });

  bindClick('prevExtra', () => {
    const trg = document.querySelector('[data-bs-target="#tab-extra"]');
    if (trg) new bootstrap.Tab(trg).show();
  });

  bindClick('prevCar', () => {
    const price = document.querySelector('[data-bs-target="#tab-price"]');
    if (price) new bootstrap.Tab(price).show();

    setTimeout(() => {
      const car = document.querySelector('[data-bs-target="#tab-car"]');
      if (car) new bootstrap.Tab(car).show();
    }, 200);
  });

  bindClick('prevDate', () => {
    const trg = document.querySelector('[data-bs-target="#tab-date"]');
    if (trg) new bootstrap.Tab(trg).show();
  });
});

//edit : search car order id
$(document).ready(function () {
  const $searchInput = $('#carOrderSearch');
  const $modal = $('#modalSearchCarOrder');
  const $tableBody = $('#tableSelectCarOrder tbody');

  $searchInput.on('keypress', function (e) {
    if (e.which === 13) {
      e.preventDefault();
      searchCarOrder($(this).val());
    }
  });

  $('.btnSearchCarOrder').on('click', function () {
    searchCarOrder($searchInput.val());
  });

  function searchCarOrder(keyword) {
    if (!keyword.trim()) return;

    $.ajax({
      url: '/car-order/search',
      type: 'GET',
      data: { keyword },
      success: function (res) {
        $tableBody.empty();

        if (res.length === 0) {
          $tableBody.append(`<tr><td colspan="7" class="text-center">ไม่พบข้อมูล Car Oder</td></tr>`);
        } else {
          res.forEach(c => {
            $tableBody.append(`
              <tr>
              <td>${c.order_code ?? '-'}</td>
                <td>${c.sub_model?.name}</td>
                <td>${c.vin_number ?? '-'}</td>
                <td>${c.option ?? '-'}</td>
                <td>${c.color ?? '-'}</td>
                <td>${c.year ?? '-'}</td>
                <td>${c.order_status?.name}</td>
                <td>
                  <button class="btn btn-sm btn-primary btnSelectCarOder"
                    data-id="${c.id}"
                    data-code="${c.order_code}"
                    data-model="${c.model?.Name_TH}"
                    data-sub="${c.sub_model?.name}"
                    data-vin="${c.vin_number ?? ''}"
                    data-option="${c.option ?? ''}"
                    data-color="${c.color ?? ''}"
                    data-year="${c.year ?? ''}"
                    data-cost="${c.car_DNP ?? ''}"
                    data-sale="${c.car_MSRP ?? ''}">
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

  $(document).on('click', '.btnSelectCarOder', function () {
    const data = $(this).data();

    function formatNumber(num) {
      if (!num || isNaN(num)) return '';
      return Number(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    $('#carOrderCode').val(data.code);
    $('#carOrderModel').val(data.model);
    $('#carOrderSubModel').val(data.sub);
    $('#carOrderVin').val(data.vin);
    $('#carOrderOption').val(data.option);
    $('#carOrderColor').val(data.color);
    $('#carOrderYear').val(data.year);
    $('#carOrderCost').val(formatNumber(data.cost));
    $('#carOrderSale').val(formatNumber(data.sale));
    $('#CarOrderID').val(data.id);

    $modal.modal('hide');

    $searchInput.val('');

    updateSummary();
  });
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

  if (!acceptRadio || !rejectRadio || !costInput || !provinceSelect) return;

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
  // ฟังก์ชันดึง id ที่เลือก
  function getSelectedGiftIds() {
    const ids = [];
    $('#giftTablePrice tbody tr[data-id]').each(function () {
      ids.push($(this).data('id'));
    });
    return ids;
  }

  function getSelectedExtraIds() {
    const ids = [];
    $('#extraTable tbody tr[data-id]').each(function () {
      ids.push($(this).data('id'));
    });
    return ids;
  }

  // ฟังก์ชัน update grand totals
  function updateGrandTotals() {
    const extraTotal = parseFloat($('#total-price-extra').text().replace(/,/g, '')) || 0;
    const giftTotal = parseFloat($('#total-price-gift').text().replace(/,/g, '')) || 0;

    $('#TotalAccessoryGift').val(giftTotal);
    $('#TotalAccessoryExtra').val(extraTotal);

    $('#summaryExtraTotal').val(
      extraTotal.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    );
  }

  // ฟังก์ชัน init accessory & gift
  function initAccessoryGift(options) {
    const $searchInput = $(options.searchInput);
    const $modal = $(options.modal);
    const $tableBody = $(options.tableBody);
    const $mainTable = $(options.mainTable);

    $(options.btnOpen).on('click', function () {
      $modal.modal('show');
      $searchInput.val('');
      $tableBody.empty();

      const subModel_id = $('#subModel_id').val();
      console.log('ใช้ subModel_id ล่าสุด:', subModel_id);

      if (!subModel_id) {
        $tableBody.append(`<tr><td colspan="5" class="text-center text-danger">กรุณาเลือกรุ่นรถก่อน</td></tr>`);
      } else {
        $(options.subModelInput).val(subModel_id);
        searchItem('');
      }
    });

    $searchInput.on('keypress', function (e) {
      if (e.which === 13) {
        e.preventDefault();
        searchItem($searchInput.val());
      }
    });

    $(options.btnSearch).on('click', function () {
      searchItem($searchInput.val());
    });

    // ฟังก์ชัน search item
    function searchItem(keyword = '') {
      const selectedGiftIds = getSelectedGiftIds();
      const selectedExtraIds = getSelectedExtraIds();
      const excludeIds = [...selectedGiftIds, ...selectedExtraIds];

      const subModel_id = $('#subModel_id').val();
      if (!subModel_id) return;

      $.ajax({
        url: '/accessory/search',
        type: 'GET',
        data: { keyword, subModel_id, exclude_ids: excludeIds },
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
                  data-detail="${a.AccessoryDetail}" data-price="${a.accessoryCost ?? ''}">
                <span class="ms-1">${formatNumber(a.accessoryCost)}</span>`
              : `<span>-</span>`;

            const promoCell = a.AccessoryPromoPrice
              ? `<input type="radio" name="priceType_${a.id}" value="promo"
                  data-id="${a.id}" data-source="${a.AccessorySource}"
                  data-detail="${a.AccessoryDetail}" data-price="${a.AccessoryPromoPrice ?? ''}">
                <span class="ms-1">${formatNumber(a.AccessoryPromoPrice)}</span>`
              : `<span>-</span>`;

            const saleCell = a.AccessorySalePrice
              ? `<input type="radio" name="priceType_${a.id}" value="sale"
                  data-id="${a.id}" data-source="${a.AccessorySource}"
                  data-detail="${a.AccessoryDetail}" data-price="${a.AccessorySalePrice ?? ''}"
                  data-com="${a.AccessoryComSale ?? ''}">
                <span class="ms-1">${formatNumber(a.AccessorySalePrice) ?? ''}</span>`
              : `<span>-</span>`;

            $tableBody.append(`
              <tr>
                <td class="text-center">${a.AccessorySource ?? '-'}</td>
                <td>${a.AccessoryDetail ?? '-'}</td>
                <td class="text-center">${costCell}</td>
                <td class="text-center">${promoCell}</td>
                <td class="text-center">${saleCell} (${formatNumber(a.AccessoryComSale ?? '-')})</td>
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
      let totalCom = 0;

      $mainTable.find(`tr:not(${options.noDataRowId})`).each(function () {
        const price = parseFloat($(this).data('price')) || 0;
        const com = parseFloat($(this).data('com')) || 0;
        total += price;
        totalCom += com;
      });

      // แสดง ราคา (ค่าคอม)
      $(options.totalDisplay).text(
        `${total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} (` +
          `${totalCom.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })})`
      );

      $(options.hiddenTotal).val(total);
      $(options.hiddenCom).val(totalCom);

      updateGrandTotals();
    }

    // ซ่อน/โชว์แถวว่าง
    function checkEmptyTable() {
      const rows = $mainTable.find(`tr:not(${options.noDataRowId})`).length;
      if (rows === 0 && $mainTable.find(options.noDataRowId).length === 0) {
        $mainTable.append(`
          <tr id="${options.noDataRowId.replace('#', '')}">
            <td colspan="6" class="text-center">ยังไม่มีข้อมูล</td>
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
    $modal.find('button.save-item, #btnSaveGift, #btnSaveExtra').on('click', function () {
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
        const rawPrice = $radio.data('price');
        const rawCom = $radio.data('com');
        const price = formatNumber(rawPrice);
        const comDisplay = rawCom && parseFloat(rawCom) > 0 ? formatNumber(rawCom) : '-';

        const typeLabel = { cost: 'ราคาทุน', promo: 'ราคาพิเศษ', sale: 'ราคาขาย' }[type];

        $mainTable.find(options.noDataRowId).remove();
        $mainTable.append(`
        <tr data-id="${$radio.data('id')}" data-price="${rawPrice}" data-com="${rawCom}">
          <td></td>
          <td>${source}</td>
          <td>${detail}</td>
          <td>${typeLabel}</td>
          <td>${price} (${comDisplay})</td>
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

  // เรียก init ทั้ง 2 ตาราง
  const giftFuncs = initAccessoryGift({
    type: 'gift',
    btnOpen: '.btnGift',
    btnSearch: '.btnGiftSearch',
    searchInput: '#giftSearch',
    modal: '.viewGift',
    tableBody: '#tableGiftResult tbody',
    mainTable: '#giftTablePrice tbody',
    hiddenIds: '#gift_ids',
    hiddenTotal: '#total_gift_used',
    hiddenCom: '#total_gift_com',
    subModelInput: '#subModel_id_gift',
    totalDisplay: '#total-price-gift',
    noDataRowId: '#no-data-row',
    deleteBtnClass: '.btn-delete-gift'
  });

  const extraFuncs = initAccessoryGift({
    type: 'extra',
    btnOpen: '.btnExtra',
    btnSearch: '.btnExtraSearch',
    searchInput: '#extraSearch',
    modal: '.viewExtra',
    tableBody: '#tableExtraResult tbody',
    mainTable: '#extraTable tbody',
    hiddenIds: '#extra_ids',
    hiddenTotal: '#total_extra_used',
    hiddenCom: '#total_extra_com',
    subModelInput: '#subModel_id_extra',
    totalDisplay: '#total-price-extra',
    noDataRowId: '#no-data-extra',
    deleteBtnClass: '.btn-delete-extra'
  });

  // เมื่อเปลี่ยน CarModel
  $('#subModel_id').on('change', function () {
    $('#giftTablePrice tbody').html(
      `<tr id="no-data-row"><td colspan="6" class="text-center">ยังไม่มีข้อมูล</td></tr>`
    );
    $('#gift_ids').val('');
    $('#total_gift_used').val(0);
    $('#total-price-gift').text('0');

    $('#extraTable tbody').html(`<tr id="no-data-extra"><td colspan="6" class="text-center">ยังไม่มีข้อมูล</td></tr>`);
    $('#extra_ids').val('');
    $('#total_extra_used').val(0);
    $('#total-price-extra').text('0');

    updateGrandTotals();
  });

  // ฟังก์ชันคำนวณยอดรวมตอนโหลดหน้า edit
  function initGrandTotalOnLoad() {
    // accessory
    let giftTotal = 0;
    $('#giftTablePrice tbody tr:not(#no-data-row)').each(function () {
      giftTotal += parseFloat($(this).data('price')) || 0;
    });
    $('#total-price-gift').text(
      giftTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    );
    $('#total_gift_used').val(giftTotal);

    // gift
    let extraTotal = 0;
    $('#extraTable tbody tr:not(#no-data-extra)').each(function () {
      extraTotal += parseFloat($(this).data('price')) || 0;
    });
    $('#total-price-extra').text(
      extraTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    );
    $('#total_extra_used').val(extraTotal);

    // update hidden field
    updateGrandTotals();
  }

  // เรียกตอนโหลดหน้า
  initGrandTotalOnLoad();

  // บันทึกข้อมูล
  $(document).on('click', '.btnUpdatePurchase', function (e) {
    e.preventDefault();

    const $btn = $(this);
    const $form = $('#purchaseForm');
    const actionUrl = $form.attr('action');
    const formData = new FormData($form[0]);

    const accessoriesGift = [];
    $('#giftTablePrice tbody tr:not(#no-data-row)').each(function () {
      accessoriesGift.push({
        id: $(this).data('id'),
        price_type: $(this).find('td').eq(3).text().trim(),
        price: parseFloat($(this).data('price')),
        commission: parseFloat($(this).data('com')),
        type: 'gift'
      });
    });

    const accessoriesExtra = [];
    $('#extraTable tbody tr:not(#no-data-extra)').each(function () {
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
    $('#TotalSaleCampaign').val(
      total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    );
  }

  // คำนวณเมื่อเปลี่ยนค่า
  $('#CampaignID').on('change', calcTotalCampaign);

  // เรียกคำนวณตอนเปิดหน้า (กรณีมี selected อยู่แล้ว)
  calcTotalCampaign();

  // เมื่อเปลี่ยนรุ่นรถ
  $('#subModel_id').on('change', function () {
    const subModel_id = $(this).val();
    $('#CampaignID').empty().trigger('change');

    if (!subModel_id) return;

    $.ajax({
      url: '/purchase-order/get-campaign',
      type: 'GET',
      data: { subModel_id: subModel_id },
      success: function (res) {
        if (res.length === 0) {
          $('#CampaignID').append(new Option('ไม่มีแคมเปญ', '', false, false));
          $('#CampaignID').trigger('change');
          return;
        }

        res.forEach(c => {
          const option = new Option(`${c.name} - ${c.cashSupport} บาท`, c.id, false, false);
          $(option).attr('data-cashsupport', c.cashSupport);
          $('#CampaignID').append(option);
        });

        $('#CampaignID').trigger('change');
      }
    });
  });
});

//edit : auto value -> บวกหัว (90%), ราคาสุทธิบวกหัว
document.addEventListener('DOMContentLoaded', function () {
  const salePriceInput = document.getElementById('carOrderSale');
  const markupInput = document.getElementById('MarkupPrice');
  const markup90Input = document.querySelector('input[name="Markup90"]');
  const finalPriceInput = document.getElementById('CarSalePriceFinal');

  //เงินดาวน์
  const downPaymentInput = document.getElementById('DownPayment');
  const downPaymentPercentInput = document.getElementById('DownPaymentPercentage');

  //edit : ราคารถสุทธิรวมบวกหัว
  function calculateCarPrice() {
    const salePrice = parseFloat(salePriceInput.value.replace(/,/g, '')) || 0;
    const markup = parseFloat(markupInput.value.replace(/,/g, '')) || 0;

    const markup90 = markup * 0.9;
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

  // edit : บวกหัว 90%
  function calculateFinalFromManualMarkup90() {
    const salePrice = parseFloat(salePriceInput.value.replace(/,/g, '')) || 0;
    const markup90 = parseFloat(markup90Input.value.replace(/,/g, '')) || 0;

    const finalPrice = salePrice + markup90;

    finalPriceInput.value = finalPrice.toLocaleString(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });

    downPaymentInput.value = '';
    downPaymentPercentInput.value = '';
    calculateRemaining();
  }

  //edit : เงินดาวน์ และ %
  function calculateDownPayment() {
    const finalPrice = parseFloat(finalPriceInput.value.replace(/,/g, '')) || 0;
    const downPayment = parseFloat(downPaymentInput.value.replace(/,/g, '')) || 0;
    const downPercent = parseFloat(downPaymentPercentInput.value.replace(/,/g, '')) || 0;

    if (document.activeElement === downPaymentInput) {
      const percent = finalPrice > 0 ? (downPayment / finalPrice) * 100 : 0;
      downPaymentPercentInput.value = percent.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    } else if (document.activeElement === downPaymentPercentInput) {
      const dp = (finalPrice * downPercent) / 100;
      downPaymentInput.value = dp.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

      $(downPaymentInput).trigger('input');
    }

    if (document.activeElement === downPaymentInput) {
      calculateRemaining();
    }
  }

  //ราคาขาย
  if (salePriceInput) salePriceInput.addEventListener('input', calculateCarPrice);
  if (markupInput) markupInput.addEventListener('input', calculateCarPrice);
  if (markup90Input) markup90Input.addEventListener('input', calculateFinalFromManualMarkup90);

  //ดาวน์
  if (downPaymentInput) downPaymentInput.addEventListener('input', calculateDownPayment);
  if (downPaymentPercentInput) downPaymentPercentInput.addEventListener('input', calculateDownPayment);

  calculateRemaining();
});

//edit : total payment delivery ค่าใช้จ่ายวันออกรถ
function calculateTotalPaymentAtDelivery() {
  const downPayment = safeNumber('#DownPayment');
  const downDiscount = safeNumber('#DownPaymentDiscount');
  const giftTotal = safeNumber('#total_gift_used');
  const ExtraTotal = safeNumber('#summaryExtraTotal');
  const turnCost = safeNumber('#summaryTurn');
  const cashDeposit = safeNumber('#summaryCashDeposit');

  const total = downPayment + ExtraTotal - (downDiscount + turnCost + cashDeposit);

  $('#TotalPaymentatDeliveryCar').val(
    total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
  );

  $('#TotalPaymentatDelivery').val(total);

  calculateRemaining();
}

//edit : ยอดคงเหลือสำหรับจัดไฟแนนซ์
function calculateRemaining() {
  const finalPrice = safeNumber('#CarSalePriceFinal');
  const downPayment = safeNumber('#DownPayment');
  const remaining = finalPrice - downPayment;

  // แสดงยอดที่เหลือ
  $('#balanceFinanceDisplay').val(
    remaining.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
  );

  // เก็บค่าลง hidden input เพื่อบันทึกเข้าฐาน
  $('#remaining_cost').val(remaining);
  $('#balanceFinance').val(remaining);
}

//edit : ยอดคงเหลือ
function calculateBalance() {
  const carSale = safeNumber('#summaryCarSale');
  const ExtraTotal = safeNumber('#summaryExtraTotal');
  const turnCost = safeNumber('#summaryTurn');
  const cashDeposit = safeNumber('#summaryCashDeposit');
  const discount = safeNumber('#PaymentDiscount');

  const total = carSale + ExtraTotal - (turnCost + cashDeposit + discount);

  $('.balance-display:visible').val(
    total.toLocaleString(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    })
  );

  $('#balance').val(total);
}

//edit : clone value
function updateSummary() {
  const model = $('#carOrderSubModel').val() || '';
  const turn = safeNumber('#cost_turn');
  const reserve = safeNumber('#CashDeposit');
  const sale = safeNumber('#carOrderSale');

  $('#summarySubCarModel').val(model);
  $('#summaryTurn').val(turn.toLocaleString(undefined, { minimumFractionDigits: 2 }));
  $('#summaryCashDeposit').val(reserve.toLocaleString(undefined, { minimumFractionDigits: 2 }));
  $('#summaryCarSale').val(sale.toLocaleString(undefined, { minimumFractionDigits: 2 }));

  calculateTotalPaymentAtDelivery();
  calculateInstallment();
}

// edit : ค่างวด (กรณีไม่มี ALP)
function calculateInstallment() {
  const financeAmount = safeNumber('#balanceFinanceDisplay');
  const interestRate = Number($('#remaining_interest').val());
  const periodMonths = Number($('#remaining_period').val());

  if (!financeAmount || !interestRate || !periodMonths || periodMonths <= 0) {
    $('#remaining_alp').val('');
    return;
  }

  const years = periodMonths / 12;
  const totalInterest = (financeAmount * interestRate * years) / 100;
  const totalWithInterest = financeAmount + totalInterest;

  const monthlyPayment = totalWithInterest / periodMonths;

  $('#remaining_alp').val(
    Number(monthlyPayment).toLocaleString(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    })
  );
}

//edit : event update
$(document).ready(function () {
  $('#carOrderSubModel, #cost_turn, #reservation_cost, #carOrderSale').on('input change', updateSummary);
  $('#DownPayment, #DownPaymentDiscount, #summaryTurn, #total_gift_used, #summaryExtraTotal, #summaryCashDeposit').on(
    'input change',
    calculateTotalPaymentAtDelivery
  );
  $('#CarSalePriceFinal, #DownPayment').on('input change', calculateRemaining);
  $('#balanceFinanceDisplay, #remaining_interest, #remaining_period').on('input change', calculateInstallment);
  $('#summaryCarSale, #summaryExtraTotal, #summaryTurn, #summaryCashDeposit, #PaymentDiscount').on(
    'input change',
    calculateBalance
  );

  updateSummary();
});

//edit : radio reservation payment
$(document).ready(function () {
  $('#bankReservation, #checkReservation, #creditReservation').hide();

  const selected = $('input[name="reservationCondition"]:checked').val();
  if (selected) {
    showRemaining(selected);
  }

  $('input[name="reservationCondition"]').change(function () {
    const type = $(this).val();
    showRemaining(type);
  });

  function showRemaining(type) {
    $('#bankReservation, #creditReservation').hide();

    if (type === 'credit') $('#creditReservation').show();
    else if (type === 'check') $('#checkReservation').show();
    else if (type === 'transfer') $('#bankReservation').show();
  }
});

//edit : radio payment remaining
$(document).ready(function () {
  $('#financeRemain, #bankRemain, #checkRemain, #creditRemain, #nonFinanceSelect').hide();

  const mode = $('#payment_mode').val(); // ใช้ hidden field แทน
  const type = $('#remainingCondition').val(); // ประเภทไม่ผ่อน

  if (mode === 'finance') {
    $('#financeRemain').show();
  } else if (mode === 'non-finance') {
    $('#nonFinanceSelect').show();

    if (type === 'credit') $('#creditRemain').show();
    if (type === 'check') $('#checkRemain').show();
    if (type === 'transfer') $('#bankRemain').show();
  }
});

//edit : radio delivery payment
$(document).ready(function () {
  $('#bankDelivery, #checkDelivery, #creditDelivery').hide();

  const selected = $('input[name="deliveryCondition"]:checked').val();
  if (selected) {
    showRemaining(selected);
  }

  $('input[name="deliveryCondition"]').change(function () {
    const type = $(this).val();
    showRemaining(type);
  });

  function showRemaining(type) {
    $('#bankDelivery, #checkDelivery, #creditDelivery').hide();

    if (type === 'credit') $('#creditDelivery').show();
    else if (type === 'check') $('#checkDelivery').show();
    else if (type === 'transfer') $('#bankDelivery').show();
  }
});

//edit : previewPurchase
document.addEventListener('DOMContentLoaded', function () {
  const btnPreview = document.getElementById('btnPreview');
  const modalElement = document.getElementById('previewPurchase');
  const modal = new bootstrap.Modal(modalElement);
  const content = document.getElementById('previewPurchaseContent');

  btnPreview?.addEventListener('click', function () {
    function formatThaiDate(inputId) {
      const value = document.getElementById(inputId)?.value || '-';

      if (!value || value === '-') return '-';

      const date = new Date(value);
      return date.toLocaleDateString('th-TH', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
      });
    }

    // ข้อมูลลูกค้า
    const customerName = document.getElementById('CusFullName')?.value || '-';
    const currentAddress = document.getElementById('CusCurrentAddress')?.value || '-';
    const documentAddress = document.getElementById('CusDocumentAddress')?.value || '-';
    const customerMobile = document.getElementById('CusMobile')?.value || '-';
    let BookingDate = formatThaiDate('BookingDate');

    //ข้อมูลการขาย
    const model = document.getElementById('carOrderModel')?.value || '-';
    const subModel = document.getElementById('carOrderSubModel')?.value || '-';
    const option = document.getElementById('carOrderOption')?.value || '-';
    const color = document.getElementById('carOrderColor')?.value || '-';
    const carSale = document.getElementById('carOrderSale')?.value || '-';
    const extraTotal = document.querySelector('#total-price-extra')?.textContent || '-';
    const giftTotal = Number(document.querySelector('#total_gift_used')?.value || 0).toLocaleString(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
    const cashDeposit = document.getElementById('summaryCashDeposit')?.value || '-';
    const summaryExtraTotal = document.getElementById('summaryExtraTotal')?.value || '-';
    const turn = document.getElementById('summaryTurn')?.value || '-';

    // รูปแบบการชำระเงิน
    const paymentType = document.querySelector('#payment_mode')?.value || '-';

    //if
    // เงินดาวน์
    const downPayment = document.getElementById('DownPayment')?.value || '-';
    const downPaymentPercentage = document.getElementById('DownPaymentPercentage')?.value || '-';
    const downPaymentDiscount = document.getElementById('DownPaymentDiscount')?.value || '-';

    // วันออกรถ
    const TotalPaymentatDeliveryCar = document.getElementById('TotalPaymentatDeliveryCar')?.value || '-';
    const financeCompany = document.querySelector('#remaining_finance option:checked')?.textContent || '-';
    const balanceFinanceDisplay = document.getElementById('balanceFinanceDisplay')?.value || '-';
    const interest = document.getElementById('remaining_interest')?.value || '-';
    const period = document.querySelector('#remaining_period option:checked')?.textContent || '-';
    const alp = document.getElementById('remaining_alp')?.value || '-';
    const includingAlp = document.getElementById('remaining_including_alp')?.value || '-';
    const totalAlp = document.getElementById('remaining_total_alp')?.value || '-';
    const typeCom = document.querySelector('#remaining_type_com option:checked')?.textContent || '-';
    const totalCom = document.getElementById('remaining_total_com')?.value || '-';

    // const balanceCampaign = totalCampaign + markup90;
    // document.getElementById('balanceCampaign').value = balanceCampaign;

    // ยอดรวมแคมเปญ
    const totalCampaign = parseFloat(document.getElementById('TotalSaleCampaign')?.value.replace(/,/g, '') || 0);
    const markup90 = parseFloat(document.getElementById('Markup90')?.value.replace(/,/g, '') || 0);
    const totalcam90 = totalCampaign + markup90;

    //หาค่า ยอดรวมรายการที่ใช้
    const downPay = parseFloat(document.getElementById('DownPaymentDiscount')?.value.replace(/,/g, '') || 0);
    const gift = parseFloat(document.querySelector('#total-price-gift')?.textContent.replace(/,/g, '') || 0);
    const totalUseFinance = downPay + gift;

    //หาค่า คงเหลือ
    const totalBalanceFinance = totalcam90 - totalUseFinance;
    const totalBalanceFinance2 = Math.max(totalBalanceFinance / 2, 0);

    //else
    const paymentDiscount = document.getElementById('PaymentDiscount')?.value || '0';
    const balanceValue = parseFloat(document.getElementById('balance')?.value.replace(/,/g, '') || 0);

    // ฟอร์แมตเป็นเลขไทย/อังกฤษ มี comma และ 2 ทศนิยม
    const balanceDisplay = balanceValue.toLocaleString(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });

    const payDis = parseFloat(document.getElementById('PaymentDiscount')?.value.replace(/,/g, '') || 0);

    //หาค่า ยอดรวมรายการที่ใช้
    const totalUse = payDis + gift;

    //หาค่า คงเหลือ
    const totalBalance = totalCampaign - totalUse;
    const totalBalance2 = Math.max(totalBalance / 2, 0);

    let price = '-';
    let discountHtml = '';
    let campaignHtml = '';

    // ยอดรวม campaign แบ่งครึ่ง
    const balanceCampaignInput = document.getElementById('balanceCampaign');
    if (balanceCampaignInput) {
      if (paymentType === 'finance') {
        balanceCampaignInput.value = totalBalanceFinance2.toLocaleString('th-TH', { minimumFractionDigits: 2 });
      } else {
        balanceCampaignInput.value = totalBalance2.toLocaleString('th-TH', { minimumFractionDigits: 2 });
      }
    }

    if (paymentType === 'finance') {
      price = document.getElementById('CarSalePriceFinal')?.value || '-';

      discountHtml = `
          <div class="d-flex justify-content-between mb-2">
                <strong>เงินดาวน์ :</strong>
                <span>${downPayment} บาท</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>เปอร์เซ็นต์เงินดาวน์ :</strong>
            <span>${downPaymentPercentage} %</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>ส่วนลดเงินดาวน์ :</strong>
            <span>${downPaymentDiscount} บาท</span>
          </div>

          <h5 class="pb-2 mb-3"></h5>
          <div class="d-flex justify-content-between mb-2">
            <strong>สรุปค่าใช้จ่ายวันออกรถ :</strong>
            <span>${TotalPaymentatDeliveryCar} บาท</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>ไฟแนนซ์ :</strong>
            <span>${financeCompany}</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>ยอดจัดไฟแนนซ์ :</strong>
            <span>${balanceFinanceDisplay} บาท</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>ดอกเบี้ย :</strong>
            <span>${interest} %</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>งวดผ่อน :</strong>
            <span>${period}</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>ค่างวด (กรณีไม่มี ALP) :</strong>
            <span>${alp} บาท</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>ค่างวด (รวม ALP) :</strong>
            <span>${includingAlp} บาท</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>ยอดเงิน ALP ที่หักจากใบเสร็จดาวน์ :</strong>
            <span>${totalAlp} บาท</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>ดอกเบี้ยคอม :</strong>
            <span>${typeCom}</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>ยอดเงินค่าคอม :</strong>
            <span>${totalCom} บาท</span>
          </div>
      `;

      campaignHtml = `
          <div class="d-flex justify-content-between mb-2">
            <strong>รวมงบแคมเปญ :</strong>
            <span>${totalCampaign.toLocaleString('th-TH', { minimumFractionDigits: 2 })} บาท</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>บวกหัว (90%) :</strong>
            <span>${markup90.toLocaleString('th-TH', { minimumFractionDigits: 2 })} บาท</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>ยอดรวมแคมเปญ (รวมบวกหัว 90%) :</strong>
            <span>${totalcam90.toLocaleString('th-TH', { minimumFractionDigits: 2 })} บาท</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>ส่วนลดเงินดาวน์ :</strong>
            <span>${downPaymentDiscount} บาท</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>ส่วนต่างของแถม :</strong>
            <span>${giftTotal} บาท</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>ยอดรวมรายการที่ใช้ :</strong>
            <span>${totalUseFinance.toLocaleString('th-TH', { minimumFractionDigits: 2 })} บาท</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>คงเหลือ :</strong>
            <span>${totalBalanceFinance.toLocaleString('th-TH', { minimumFractionDigits: 2 })} บาท</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>คงเหลือ(แบ่ง 2 ส่วน) :</strong>
            <span>${totalBalanceFinance2.toLocaleString('th-TH', { minimumFractionDigits: 2 })} บาท</span>
          </div>
      `;
    } else {
      price = document.getElementById('summaryCarSale')?.value || '-';

      discountHtml = `
          <div class="d-flex justify-content-between mb-2">
            <strong>ส่วนลด :</strong>
            <span>${paymentDiscount}  บาท</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>คงเหลือ :</strong>
            <span>${balanceDisplay}  บาท</span>
          </div>
      `;

      campaignHtml = `
          <div class="d-flex justify-content-between mb-2">
            <strong>รวมงบแคมเปญ :</strong>
            <span>${totalCampaign.toLocaleString('th-TH', { minimumFractionDigits: 2 })} บาท</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>ส่วนลด :</strong>
            <span>${paymentDiscount} บาท</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>ส่วนต่างของแถม :</strong>
            <span>${giftTotal} บาท</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>ยอดรวมรายการที่ใช้ :</strong>
            <span>${totalUse.toLocaleString('th-TH', { minimumFractionDigits: 2 })} บาท</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>คงเหลือ :</strong>
            <span>${totalBalance.toLocaleString('th-TH', { minimumFractionDigits: 2 })} บาท</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>คงเหลือ(แบ่ง 2 ส่วน) :</strong>
            <span>${totalBalance2.toLocaleString('th-TH', { minimumFractionDigits: 2 })} บาท</span>
          </div>
      `;
    }

    const customerIDRef = document.getElementById('customerIDRef')?.value || '-';
    const ReferrerAmount = document.getElementById('ReferrerAmount')?.value || '-';

    //campaign
    // ดึงชื่อแคมเปญที่เลือกไว้
    const campaignSelect = document.getElementById('CampaignID');
    let campaignList = [];

    if (campaignSelect) {
      const selectedOptions = Array.from(campaignSelect.selectedOptions);
      campaignList = selectedOptions.map(opt => opt.textContent.trim());
    }

    // รวมชื่อทั้งหมดคั่นด้วย " + "
    const campaignText = campaignList.length > 0 ? campaignList.join(' + ') : '-';

    //ของแถม
    const giftRows = document.querySelectorAll('#giftTablePrice tbody tr');
    let giftHtml = '';
    let totalGift = 0;
    let totalGiftCom = 0;

    if (giftRows.length > 0 && !document.getElementById('no-data-row')) {
      giftHtml += `<table class="table table-bordered">
      <thead>
        <tr>
          <th>ลำดับ</th>
          <th>รายละเอียด</th>
          <th>รหัสสินค้า</th>
          <th>ราคา (ค่าคอม)</th>
        </tr>
      </thead>
      <tbody>`;

      giftRows.forEach((row, index) => {
        const id = row.dataset.id || '-';
        const code = row.querySelector('td:nth-child(2)')?.textContent.trim() || '-';
        const detail = row.querySelector('td:nth-child(3)')?.textContent.trim() || '-';
        const priceComText = row.querySelector('td:nth-child(5)')?.textContent.trim().replace(/,/g, '') || '0';
        const match = priceComText.match(/([\d,\.]+)\s*\(?([\d,\.]+)?\)?/);

        let price = 0;
        let com = 0;

        if (match) {
          price = parseFloat(match[1].replace(/,/g, '')) || 0;
          com = parseFloat(match[2]?.replace(/,/g, '') || '0') || 0;
        }

        totalGift += price;
        totalGiftCom += com;

        giftHtml += `<tr>
        <td>${index + 1}</td>
        <td>${detail}</td>
        <td>${code}</td>
        <td>${price.toLocaleString(undefined, { minimumFractionDigits: 2 })} 
            (${com.toLocaleString(undefined, { minimumFractionDigits: 2 })})
        </td>
      </tr>`;
      });

      giftHtml += `
      </tbody>
        <tfoot>
          <tr>
            <th colspan="3" class="text-end">ยอดรวมทั้งหมด</th>
            <th>${totalGift.toLocaleString(undefined, { minimumFractionDigits: 2 })} (${totalGiftCom.toLocaleString(undefined, { minimumFractionDigits: 2 })})</th>
          </tr>
        </tfoot>
      </table>`;
    } else {
      giftHtml = `<div class="text-center text-muted pb-2 mb-3">ยังไม่มีข้อมูลแถม</div>`;
    }

    //ซื้อเพิ่ม
    const extraRows = document.querySelectorAll('#extraTable tbody tr');
    let extraHtml = '';
    let totalExtra = 0;
    let totalExtraCom = 0;

    if (extraRows.length > 0 && !document.getElementById('no-data-extra')) {
      extraHtml += `<table class="table table-bordered">
      <thead>
        <tr>
          <th>ลำดับ</th>
          <th>รายละเอียด</th>
          <th>รหัสสินค้า</th>
          <th>ราคา (ค่าคอม)</th>
        </tr>
      </thead>
      <tbody>`;

      extraRows.forEach((row, index) => {
        const id = row.dataset.id || '-';
        const code = row.querySelector('td:nth-child(2)')?.textContent.trim() || '-';
        const detail = row.querySelector('td:nth-child(3)')?.textContent.trim() || '-';
        const priceComText = row.querySelector('td:nth-child(5)')?.textContent.trim().replace(/,/g, '') || '0';
        const match = priceComText.match(/([\d,\.]+)\s*\(?([\d,\.]+)?\)?/);

        let price = 0;
        let com = 0;

        if (match) {
          price = parseFloat(match[1].replace(/,/g, '')) || 0;
          com = parseFloat(match[2]?.replace(/,/g, '') || '0') || 0;
        }

        totalExtra += price;
        totalExtraCom += com;

        extraHtml += `<tr>
        <td>${index + 1}</td>
        <td>${detail}</td>
        <td>${code}</td>
        <td>${price.toLocaleString(undefined, { minimumFractionDigits: 2 })} 
            (${com.toLocaleString(undefined, { minimumFractionDigits: 2 })})
        </td>
      </tr>`;
      });

      extraHtml += `
      </tbody>
        <tfoot>
          <tr>
            <th colspan="3" class="text-end">ยอดรวมทั้งหมด</th>
            <th>${totalExtra.toLocaleString(undefined, { minimumFractionDigits: 2 })} (${totalExtraCom.toLocaleString(undefined, { minimumFractionDigits: 2 })})</th>
          </tr>
        </tfoot>
      </table>`;
    } else {
      extraHtml = `<div class="text-center text-muted pb-2 mb-3">ยังไม่มีข้อมูลซื้อเพิ่ม</div>`;
    }

    // วันส่งมอบ
    let KeyInDate = formatThaiDate('KeyInDate');
    let DeliveryDate = formatThaiDate('DeliveryDate');
    let DeliveryInDMSDate = formatThaiDate('DeliveryInDMSDate');
    let DeliveryInCKDate = formatThaiDate('DeliveryInCKDate');

    const AdminSignature = document.querySelector('#AdminSignature option:checked')?.textContent || '-';
    let AdminCheckedDate = formatThaiDate('AdminCheckedDate');
    const CheckerID = document.querySelector('#CheckerID option:checked')?.textContent || '-';
    let CheckerCheckedDate = formatThaiDate('CheckerCheckedDate');
    const SMSignature = document.querySelector('#SMSignature option:checked')?.textContent || '-';
    let SMCheckedDate = formatThaiDate('SMCheckedDate');

    const ApprovalSignature = document.querySelector('#ApprovalSignature option:checked')?.textContent || '-';
    const GMApprovalSignature = document.querySelector('#GMApprovalSignature option:checked')?.textContent || '-';

    // จังหวัดที่จดทะเบียน
    let RegistrationProvince = '-';

    let prov = document.querySelector('#RegistrationProvince_cash option:checked');
    if (prov && prov.value) {
      RegistrationProvince = prov.textContent.trim();
    }

    prov = document.querySelector('#RegistrationProvince_finance option:checked');
    if (prov && prov.value) {
      RegistrationProvince = prov.textContent.trim();
    }

    // สถานะ
    const con_status = document.querySelector('#con_status option:checked')?.textContent || '-';

    const html = `
      <div class="row">
        <!-- ฝั่งซ้าย -->
        <div class="col-md-6 border-end pe-3">
          <h5 class="border-bottom pb-2 mb-3">ข้อมูลลูกค้า</h5>
          <div class="d-flex justify-content-between mb-2">
            <strong>วันที่จอง:</strong>
            <span>${BookingDate}</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>ชื่อลูกค้า :</strong>
            <span>${customerName}</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>ที่อยู่ปัจจุบัน :</strong>
            <span style="width:60%; text-align:right;">${currentAddress}</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>ที่อยู่สำหรับส่งเอกสาร :</strong>
            <span style="width:60%; text-align:right;">${documentAddress}</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>เบอร์มือถือ :</strong>
            <span>${customerMobile}</span>
          </div>

          <h5 class="border-bottom pb-2 mb-3">ข้อมูลการขาย</h5>
          <div class="d-flex justify-content-between mb-2">
            <strong>รุ่นรถหลัก :</strong>
            <span>${model}</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>รุ่นรถย่อย :</strong>
            <span>${subModel}</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>แบบ :</strong>
            <span>${option}</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>สี :</strong>
            <span>${color}</span>
          </div>
          <!-- <div class="d-flex justify-content-between mb-2">
            <strong>ประเภทการชำระเงิน :</strong>
            <span>${paymentType}</span>
          </div> -->
          <div class="d-flex justify-content-between mb-2">
            <strong>ราคา :</strong>
            <span>${price} บาท</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>เงินจอง :</strong>
            <span>${cashDeposit} บาท</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>รถเทิร์น :</strong>
            <span>${turn} บาท</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>ลูกค้าจ่ายเพิ่ม :</strong>
            <span>${summaryExtraTotal} บาท</span>
          </div>
          ${discountHtml}

          <h5 class="border-bottom pb-2 mb-3">จังหวัดที่ขึ้นทะเบียน</h5>
          <div class="d-flex justify-content-between mb-2">
            <strong>จังหวัดที่ขึ้นทะเบียน :</strong>
            <span>${RegistrationProvince}</span>
          </div>
          
          <h5 class="border-bottom pb-2 mb-3">แนะนำ</h5>
          <div class="d-flex justify-content-between mb-2">
            <strong>ผู้แนะนำ :</strong>
            <span>${customerIDRef}</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>ยอดเงินค่าแนะนำ :</strong>
            <span>${ReferrerAmount} บาท</span>
          </div>

          <h5 class="border-bottom pb-2 mb-3">แคมเปญ</h5>
          <div class="d-flex justify-content-between mb-2">
            <strong>ข้อมูลแคมเปญ :</strong>
            <span style="width:60%; text-align:right;">${campaignText}</span>
          </div>
          ${campaignHtml}
        </div>

        <!-- ฝั่งขวา -->
        <div class="col-md-6 ps-3">
          <h5 class="border-bottom pb-2 mb-3">รายละเอียดอุปกรณ์ตกแต่ง (แถม)</h5>
          <div class="table-responsive text-nowrap">
            ${giftHtml}
          </div>

          <h5 class="border-bottom pb-2 mb-3">รายการซื้อเพิ่ม</h5>
          <div class="table-responsive text-nowrap">
            ${extraHtml}
          </div>

          <h5 class="border-bottom pb-2 mb-3">ข้อมูลวันส่งมอบ</h5>
          <div class="d-flex justify-content-between mb-2">
            <strong>วันที่ส่งเอกสารสรุปการขาย :</strong>
            <span>${KeyInDate}</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>วันส่งมอบจริง (วันที่แจ้งประกัน) :</strong>
            <span>${DeliveryDate}</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>วันที่ส่งมอบในระบบ DMS :</strong>
            <span>${DeliveryInDMSDate}</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>วันที่ส่งมอบตามยอดชูเกียรติ :</strong>
            <span>${DeliveryInCKDate}</span>
          </div>

          <h5 class="border-bottom pb-2 mb-3">ผู้อนุมัติ</h5>
          <div class="d-flex justify-content-between mb-2">
            <strong>ผู้เช็ครายการ (แอดมินขาย) :</strong>
            <span>${AdminSignature}</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>วันที่แอดมินเช็ครายการ :</strong>
            <span>${AdminCheckedDate}</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>ผู้ตรวจสอบรายการ (IA) :</strong>
            <span>${CheckerID}</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>วันที่ฝ่ายตรวจสอบเช็ครายการ :</strong>
            <span>${CheckerCheckedDate}</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>ผู้อนุมัติรายการ (ผู้จัดการขาย) :</strong>
            <span>${SMSignature}</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>วันที่ผู้จัดการขายอนุมัติ :</strong>
            <span>${SMCheckedDate}</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>ผู้อนุมัติการขายกรณีเกินจากงบ :</strong>
            <span>${ApprovalSignature}</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <strong>GM อนุมัติกรณีงบเกิน (N) :</strong>
            <span>${GMApprovalSignature}</span>
          </div>

          <h5 class="border-bottom pb-2 mb-3">สถานะ</h5>
          <div class="d-flex justify-content-between mb-2">
            <strong>สถานะ :</strong>
            <span>${con_status}</span>
          </div>

        </div>
      </div>
    `;

    content.innerHTML = html;
    modal.show();
  });
});

// PO
//view : po table
let poTable;

$(document).ready(function () {
  if ($.fn.DataTable.isDataTable('.poTable')) {
    $('.poTable').DataTable().destroy();
  }

  poTable = $('.poTable').DataTable({
    ajax: '/purchase-order/list-po',
    columns: [
      { data: 'No' },
      { data: 'FullName' },
      { data: 'model' },
      { data: 'subModel' },
      { data: 'po' },
      { data: 'date' }
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

// booking
//view : booking table
let bookingTable;

$(document).ready(function () {
  if ($.fn.DataTable.isDataTable('.bookingTable')) {
    $('.bookingTable').DataTable().destroy();
  }

  bookingTable = $('.bookingTable').DataTable({
    ajax: '/purchase-order/list-booking',
    columns: [
      { data: 'No' },
      { data: 'model' },
      { data: 'subModel' },
      { data: 'option' },
      { data: 'order' },
      { data: 'FullName' },
      { data: 'date' }
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