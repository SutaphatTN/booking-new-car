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

function safeTextNumber(selector) {
  const el = document.querySelector(selector);
  if (!el) return 0;
  const text = el.textContent?.trim();
  if (!text) return 0;
  return parseFloat(text.replace(/,/g, '')) || 0;
}

//use css
$(document).ready(function () {
  $('.money-input').each(function () {
    let value = $(this).val();
    if (value === null || value === undefined || value === '') return;

    const num = parseFloat(value.toString().replace(/,/g, ''));
    if (isNaN(num)) return;

    $(this).val(
      num.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      })
    );
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
        d.con_status = $('#filterStatus').val();
      }
    },
    columns: [
      { data: 'No', orderable: false },
      { data: 'FullName', orderable: false },
      { data: 'model' },
      { data: 'order' },
      { data: 'statusSale' },
      { data: 'Action', orderable: false, searchable: false }
    ],
    paging: true,
    lengthChange: true,
    searching: true,
    ordering: true,
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

  purchaseTable.on('order.dt', function () {
    var order = purchaseTable.order();
    $('#purchaseTable thead th .sort-icon')
      .removeClass('bx-up-arrow-alt bx-down-arrow-alt text-primary')
      .addClass('bx-sort-alt-2');
    if (order.length) {
      var colIdx = order[0][0];
      var dir = order[0][1];
      var $icon = $($('#purchaseTable thead th').get(colIdx)).find('.sort-icon');
      $icon
        .removeClass('bx-sort-alt-2')
        .addClass(dir === 'asc' ? 'bx-up-arrow-alt' : 'bx-down-arrow-alt')
        .addClass('text-primary');
    }
  });

  $('#filterStatus').on('change', function () {
    purchaseTable.ajax.reload();
  });
});

//view : delete
function askCancelDate(id) {
  let today = new Date().toISOString().split('T')[0];

  Swal.fire({
    title: 'ระบุวันที่ยกเลิก',
    html: `<input type="date" id="swal-cancel-date" value="${today}" max="${today}"
             style="border:1px solid #d9d9d9; border-radius:6px; padding:8px 12px; font-size:1rem; outline:none; width:35%;">`,
    showCancelButton: true,
    confirmButtonColor: '#6c5ffc',
    cancelButtonColor: '#d33',
    confirmButtonText: 'ยืนยัน',
    cancelButtonText: 'ยกเลิก',
    preConfirm: () => {
      const cancelDate = document.getElementById('swal-cancel-date').value;
      if (!cancelDate) {
        Swal.showValidationMessage('กรุณาระบุวันที่ยกเลิก');
        return false;
      }
      return cancelDate;
    }
  }).then(result => {
    if (result.isConfirmed) {
      $.ajax({
        url: '/purchase-order/' + id,
        type: 'DELETE',
        data: { cancel_gcip_date: result.value },

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
}

$(document).on('click', '.btnDeleteSale', function () {
  let id = $(this).data('id');
  askCancelDate(id);
});

//view more

// blur focus viewPurchase
$(document).on('hide.bs.modal', '.viewPurchase', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

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
    if (!turnCarFields) return;
    turnCarFields.style.display = yesRadio.checked ? 'block' : 'none';
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

// blur focus modalSearchCustomer
$(document).on('hide.bs.modal', '#modalSearchCustomer', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
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
                <td>${c.PrefixNameTH ?? ''} ${c.FirstName ?? ''} ${c.LastName ?? ''}</td>
                <td>${c.formatted_mobile ?? '-'}</td>
                <td>${c.formatted_id_number ?? '-'}</td>
                <td>
                  <button class="btn btn-sm btn-primary btnSelectCustomer"
                    data-id="${c.id}"
                    data-name="${c.PrefixNameTH ?? ''} ${c.FirstName ?? ''} ${c.LastName ?? ''}"
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

      // Update display divs
      const setDisplay = (id, val) => {
        const el = document.getElementById(id);
        if (!el) return;
        el.textContent = val || '—';
        el.classList.toggle('empty', !val);
      };
      if (nameInput === '#customerName') {
        setDisplay('customerName-display', data.name);
        setDisplay('customerID-display', data.idnumber);
        setDisplay('customerPhone-display', data.mobile);
      }
      if (nameInput === '#customerNameRef') {
        setDisplay('customerNameRef-display', data.name);
        setDisplay('customerIDRef-display', data.idnumber);
        setDisplay('customerPhoneRef-display', data.mobile);
      }

      $modal.modal('hide');
      $search.val('');
    }
  });
}

//input : radio payment reservation
document.addEventListener('DOMContentLoaded', function () {
  const radios = document.querySelectorAll('input[name="reservationCondition"]');
  const cashSection = document.getElementById('cashSection');
  const bankSection = document.getElementById('bankSection');
  const checkSection = document.getElementById('checkSection');
  const creditSection = document.getElementById('creditSection');

  if (!radios.length || !bankSection || !checkSection || !creditSection) return;

  const allSections = [cashSection, creditSection, checkSection, bankSection].filter(Boolean);

  function toggleSection() {
    allSections.forEach(s => {
      s.style.display = 'none';
      s.querySelectorAll('input').forEach(i => (i.disabled = true));
    });

    const selected = document.querySelector('input[name="reservationCondition"]:checked');
    if (!selected) return;

    let active = null;
    if (selected.value === 'cash') active = cashSection;
    if (selected.value === 'credit') active = creditSection;
    if (selected.value === 'check') active = checkSection;
    if (selected.value === 'transfer') active = bankSection;

    if (active) {
      active.style.display = 'block';
      active.querySelectorAll('input').forEach(i => (i.disabled = false));
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
  clearPricelistFields();
  $('#gwm_color').prop('disabled', true).empty().append('<option value="">-- เลือกสี --</option>');

  if (!modelId) return;

  $.ajax({
    url: '/api/purchase-order/sub-model/' + modelId,
    type: 'GET',
    success: function (data) {
      // console.log('data:', data);
      if (data.length > 0) {
        data.forEach(function (sub) {
          let text = sub.detail ? `${sub.detail} - ${sub.name}` : sub.name;

          $subModelSelect.append(`<option value="${sub.id}">${text}</option>`);
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

//input : price list car เลือก submodel แล้วโหลด color,year จาก TbPricelistCar
document.addEventListener('DOMContentLoaded', function () {
  const $colorSel = $('#pricelist_color');
  if ($colorSel.length) {
    const rows = $colorSel.data('pricelist-rows');
    if (rows && rows.length) $colorSel.data('pricelistRows', rows);
  }
});

function clearPricelistFields() {
  $('#pricelist_color').prop('disabled', true).empty().append('<option value="">-- เลือก --</option>');
  $('#pricelist_year').prop('disabled', true).empty().append('<option value="">-- เลือกปี --</option>');
  $('#option').val('');
  $('#car_DNP').val('');
  $('#car_MSRP').val('');
  $('#RI').val('');
  $('#price_sub').val('');
}

function loadPricelistData() {
  const subModelId = $('#subModel_id').val();
  const year = $('#pricelist_year').val();
  const color = $('#pricelist_color').val() || '';

  if (!subModelId || !year) return;

  $.get('/api/car-order/pricelist-data', { sub_model_id: subModelId, year: year, color: color }, function (data) {
    if (data) {
      $('#option').val(data.option ?? '');
      $('#car_DNP').val(data.dnp ? Number(data.dnp).toLocaleString() : '');
      $('#car_MSRP').val(data.msrp ? Number(data.msrp).toLocaleString() : '');
      $('#RI').val(data.ri ? Number(data.ri).toLocaleString() : '');
      $('#price_sub').val(data.msrp ? Number(data.msrp).toLocaleString() : '');
    } else {
      $('#option').val('');
      $('#car_DNP').val('');
      $('#car_MSRP').val('');
      $('#RI').val('');
      $('#price_sub').val('');
    }

    // trigger recalculation after price_sub is set
    if (typeof calculateCarPrice === 'function') calculateCarPrice();
    if (typeof calculateBalance === 'function') calculateBalance();
  });
}

$(document).on('change', '#subModel_id', function () {
  clearPricelistFields();

  const subModelId = $(this).val();
  if (!subModelId) return;

  // brand 2: get color
  const $gwmColor = $('#gwm_color');
  if ($gwmColor.length) {
    $gwmColor.prop('disabled', true).empty().append('<option value="">-- เลือกสี --</option>');
    $.ajax({
      url: '/api/car-order/color',
      data: { sub_model_id: subModelId },
      success: function (data) {
        if (data.length) {
          data.forEach(color => {
            $gwmColor.append(`<option value="${color.id}">${color.name}</option>`);
          });
          $gwmColor.prop('disabled', false);
        } else {
          $gwmColor.append('<option value="">-- รุ่นนี้ไม่มีตัวเลือกสี --</option>');
        }
      }
    });
  }

  $.get('/api/car-order/pricelist-options', { sub_model_id: subModelId }, function (res) {
    if (!res.data || !res.data.length) return;

    if (res.type === 'color_year') {
      // brand 1: แสดง color ก่อน ปีจะโหลดหลังเลือกสี
      const colors = [...new Set(res.data.map(r => r.color))];
      const $colorSel = $('#pricelist_color');
      $colorSel.empty().append('<option value="">-- เลือก --</option>');
      colors.forEach(c => $colorSel.append(`<option value="${c}">${c}</option>`));
      $colorSel.prop('disabled', false).data('pricelistRows', res.data);
    } else {
      // brand 2,3: แสดง year เลย
      const $yearSel = $('#pricelist_year');
      $yearSel.empty().append('<option value="">-- เลือกปี --</option>');
      res.data.forEach(r => $yearSel.append(`<option value="${r.year}">${r.year}</option>`));
      $yearSel.prop('disabled', false);
    }
  });
});

$(document).on('change', '#pricelist_color', function () {
  const selectedColor = $(this).val();
  const rows = $(this).data('pricelistRows') || [];
  const $yearSel = $('#pricelist_year');

  $yearSel.prop('disabled', true).empty().append('<option value="">-- เลือกปี --</option>');
  $('#option').val('');
  $('#car_DNP').val('');
  $('#car_MSRP').val('');
  $('#RI').val('');

  if (!selectedColor) return;

  const years = [...new Set(rows.filter(r => r.color === selectedColor).map(r => r.year))];
  years.forEach(y => $yearSel.append(`<option value="${y}">${y}</option>`));
  $yearSel.prop('disabled', false);
});

$(document).on('change', '#pricelist_year', function () {
  loadPricelistData();
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

    if (!$('input[name="reservationCondition"]:checked').val()) {
      const $err = $('#payTypeError');
      const $group = $('#payTypeGroup');
      $err.show();
      $group.addClass('is-invalid-group');
      $group[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
      $('input[name="reservationCondition"]').one('change', function () {
        $err.hide();
        $group.removeClass('is-invalid-group');
      });
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

// blur focus modalSearchCarOrder
$(document).on('hide.bs.modal', '#modalSearchCarOrder', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

//edit : search car order id
$(document).ready(function () {
  const $searchInput = $('#carOrderSearch');
  const $modal = $('#modalSearchCarOrder');
  const $tableBody = $('#tableSelectCarOrder tbody');
  const userBrand = $('#user_brand').val();

  $searchInput.on('keypress', function (e) {
    if (e.which === 13) {
      e.preventDefault();
      triggerCarOrderSearch();
    }
  });

  $('.btnSearchCarOrder').on('click', function () {
    triggerCarOrderSearch();
  });

  function triggerCarOrderSearch() {
    const keyword = $searchInput.val().trim();
    if (keyword) {
      searchCarOrder(keyword);
    } else {
      searchCarOrderByFilter();
    }
  }

  function searchCarOrderByFilter() {
    const isBrand2 = $('#gwm_color').length > 0;
    const data = {
      model_id: $('#model_id').val() || '',
      sub_model_id: $('#subModel_id').val() || '',
      option: $('#option').val() || '',
      year: $('#pricelist_year').val() || $('#Year').val() || ''
    };
    if (isBrand2) {
      data.color_id = $('#gwm_color').val() || '';
      data.interior_color_id = $('#interior_color').val() || '';
    } else {
      data.color_text = $('#Color').val() || '';
    }
    doCarOrderSearch(data);
  }

  function searchCarOrder(keyword) {
    doCarOrderSearch({ keyword });
  }

  function doCarOrderSearch(data) {
    $.ajax({
      url: '/car-order/search',
      type: 'GET',
      data: data,
      success: function (res) {
        $tableBody.empty();

        if (res.length === 0) {
          $tableBody.append(`<tr><td colspan="7" class="text-center">ไม่พบข้อมูล Car Oder</td></tr>`);
        } else {
          res.forEach(c => {
            let dynamicColumn = '';

            if (userBrand == 2) {
              dynamicColumn = `<td>${c.format_order_stock_date ?? '-'}</td>`;
            } else {
              dynamicColumn = `<td>${c.option ?? '-'}</td>`;
            }

            $tableBody.append(`
              <tr>
              <td>${c.order_code ?? '-'}</td>
                <td>
                  <div>
                    ${c.sub_model?.name ?? '-'}<br>
                      ${c.sub_model?.detail ?? ''}
                  </div>
                </td>
                <td>${c.vin_number ?? '-'}</td>
                ${dynamicColumn}
                <td>
                  ${c.display_color ?? '-'}
                  ${c.display_interior_color ? '<br><small class="text-muted">สีภายใน: ' + c.display_interior_color + '</small>' : ''}
                </td>
                <td>${c.year ?? '-'}</td>
                <td>${c.order_status?.name}</td>
                <td>
                  <button class="btn btn-sm btn-primary btnSelectCarOder"
                    data-id="${c.id}"
                    data-code="${c.order_code}"
                    data-model="${c.model?.Name_TH}"
                    data-sub="${c.sub_model?.name ?? ''}"
                    data-sub-detail="${c.sub_model?.detail ?? ''}"
                    data-vin="${c.vin_number ?? ''}"
                    data-option="${c.option ?? ''}"
                    data-color="${c.display_color ?? ''}"
                    data-interior="${c.display_interior_color ?? ''}"
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
    $('#carOrderSubModel').val(data.sub + (data.subDetail ? ' - ' + data.subDetail : ''));
    $('#carOrderVin').val(data.vin);
    $('#carOrderOption').val(data.option);
    $('#carOrderColor').val(data.color);
    $('#carOrderInterior').val(data.interior);
    $('#carOrderYear').val(data.year);
    $('#carOrderCost').val(formatNumber(data.cost));
    $('#carOrderSale').val(formatNumber(data.sale));
    $('#CarOrderID').val(data.id);

    $modal.modal('hide');

    $searchInput.val('');

    updateSummary();
  });
});

//edit : ยกเลิกการผูกรถ
$(document).on('click', '#btnCancelCarOrder', function () {
  const saleId = $(this).data('sale-id');
  const carOrderId = $(this).data('carorder-id');

  Swal.fire({
    title: 'ยืนยันการยกเลิกการผูกรถ',
    // text: 'รถคันนี้จะ',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'ยืนยัน',
    cancelButtonText: 'ยกเลิก',
    confirmButtonColor: '#6c5ffc',
    cancelButtonColor: '#d33'
  }).then(result => {
    if (result.isConfirmed) {
      cancelCarOrder(saleId, carOrderId);
    }
  });
});

function cancelCarOrder(saleId, carOrderId) {
  $.ajax({
    url: `/purchase-order/${saleId}/cancel-car-order`,
    type: 'POST',
    data: {
      car_order_id: carOrderId
    },
    success: function (res) {
      Swal.fire('สำเร็จ', res.message, 'success');

      clearCarOrderUI();
    },
    error: function () {
      Swal.fire('ผิดพลาด', 'ไม่สามารถยกเลิกการผูกรถได้', 'error');
    }
  });
}

function clearCarOrderUI() {
  $('#CarOrderID').val('');

  $('#carOrderCode').val('');
  $('#carOrderModel').val('');
  $('#carOrderSubModel').val('');
  $('#carOrderVin').val('');
  $('#carOrderOption').val('');
  $('#carOrderColor').val('');
  $('#carOrderYear').val('');
  $('#carOrderCost').val('');
  $('#carOrderSale').val('');

  // ซ่อนปุ่ม
  $('#btnCancelCarOrder').remove();

  updateSummary();
}

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

// blur focus viewGift
$(document).on('hide.bs.modal', '.viewGift', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

// blur focus viewExtra
$(document).on('hide.bs.modal', '.viewExtra', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
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

      const model_id = $('#model_id').val();

      if (!model_id) {
        $tableBody.append(`<tr><td colspan="5" class="text-center text-danger">กรุณาเลือกรุ่นรถหลักก่อน</td></tr>`);
        return;
      }

      $(options.modelInput).val(model_id);
      searchItem('');
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

      const model_id = $('#model_id').val();
      if (!model_id) return;

      $.ajax({
        url: '/accessory/search',
        type: 'GET',
        data: { keyword, model_id, exclude_ids: excludeIds },
        success: function (res) {
          $tableBody.empty();

          if (res.length === 0) {
            $tableBody.append(`<tr><td colspan="5" class="text-center">ไม่พบข้อมูล</td></tr>`);
            return;
          }

          res.forEach(a => {
            const costCell =
              a.accessoryCost !== null && a.accessoryCost !== undefined
                ? `<input type="radio" name="priceType_${a.id}" value="cost"
                  data-id="${a.id}" data-source="${a.AccessorySource}"
                  data-detail="${a.AccessoryDetail}" data-price="${a.accessoryCost ?? ''}">
                <span class="ms-1">${formatNumber(a.accessoryCost)}</span>`
                : `<span>-</span>`;

            const promoCell =
              a.AccessoryPromoPrice !== null && a.AccessoryPromoPrice !== undefined
                ? `<input type="radio" name="priceType_${a.id}" value="promo"
                  data-id="${a.id}" data-source="${a.AccessorySource}"
                  data-detail="${a.AccessoryDetail}" data-price="${a.AccessoryPromoPrice ?? ''}">
                <span class="ms-1">${formatNumber(a.AccessoryPromoPrice)}</span>`
                : `<span>-</span>`;

            const saleCell =
              a.AccessorySalePrice !== null && a.AccessorySalePrice !== undefined
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
      calculateCommissionSale();
      calculateBalanceCampaign();
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

  // เมื่อเปลี่ยนรุ่นรถ
  $('#model_id').on('change', function () {
    resetGiftAndExtraTable();
  });

  function resetGiftAndExtraTable() {
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
  }

  // ฟังก์ชันคำนวณยอดรวมตอนโหลดหน้า edit
  function initGrandTotalOnLoad() {
    // gift
    let giftTotal = 0;
    let giftComTotal = 0;

    $('#giftTablePrice tbody tr:not(#no-data-row)').each(function () {
      giftTotal += parseFloat($(this).data('price')) || 0;
      giftComTotal += parseFloat($(this).data('com')) || 0;
    });

    $('#total-price-gift').text(
      `${giftTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} ` +
        `(${giftComTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })})`
    );

    $('#total_gift_used').val(giftTotal);
    $('#total_gift_com').val(giftComTotal);

    // extra
    let extraTotal = 0;
    let extraComTotal = 0;

    $('#extraTable tbody tr:not(#no-data-extra)').each(function () {
      extraTotal += parseFloat($(this).data('price')) || 0;
      extraComTotal += parseFloat($(this).data('com')) || 0;
    });

    $('#total-price-extra').text(
      `${extraTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} ` +
        `(${extraComTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })})`
    );

    $('#total_extra_used').val(extraTotal);
    $('#total_extra_com').val(extraComTotal);

    updateGrandTotals();
    calculateCommissionSale();
  }

  // เรียกตอนโหลดหน้า
  initGrandTotalOnLoad();

  // บันทึกข้อมูล
  $(document).on('click', '#btnUpdatePurchase', function (e) {
    e.preventDefault();

    const $btn = $(this);
    const $form = $('#purchaseForm');
    const actionUrl = $form.attr('action');
    const formData = new FormData($form[0]);
    formData.append('action_type', $('#action_type').val());

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

// ปุ่มขออนุมัติ (ยอดปกติ)
$(document).on('click', '#btnRequestNormal', function () {
  $('#action_type').val('request_normal');
  $('#btnUpdatePurchase').trigger('click');
});

// ปุ่มขออนุมัติเกินงบ
$(document).on('click', '#btnRequestOverBudget', function () {
  const level = this.dataset.level || 'manager';
  const previewModalEl = document.getElementById('previewPurchase');
  const previewModal = bootstrap.Modal.getInstance(previewModalEl);

  if (!previewModal) return;
  previewModal.hide();

  previewModalEl.addEventListener(
    'hidden.bs.modal',
    function () {
      Swal.fire({
        title: 'กรอกเหตุผลที่เกินงบ',
        input: 'textarea',
        inputLabel: 'เหตุผล',
        inputPlaceholder: 'กรุณาระบุสาเหตุที่ต้องขออนุมัติเกินงบ...',
        inputAttributes: {
          'aria-label': 'กรอกเหตุผล'
        },
        showCancelButton: true,
        confirmButtonColor: '#6c5ffc',
        cancelButtonColor: '#d33',
        confirmButtonText: 'บันทึก',
        cancelButtonText: 'ยกเลิก',
        allowOutsideClick: false,
        inputValidator: value => {
          if (!value) {
            return 'กรุณากรอกเหตุผลก่อนดำเนินการ';
          }
        }
      }).then(result => {
        if (result.isConfirmed) {
          $('#reason_campaign').val(result.value);
          $('#action_type').val(level === 'gm' ? 'request_gm' : 'request_over');
          $('#btnUpdatePurchase').trigger('click');
        } else {
          previewModal.show();
        }
      });
    },
    { once: true }
  );
});

//edit : campaign
$(document).ready(function () {
  $('#CampaignID').select2({
    placeholder: 'เลือกแคมเปญ',
    width: '100%'
  });

  // ป้ายแดง — searchable select
  if ($('#red_license').length) {
    $('#red_license').select2({
      placeholder: 'พิมพ์เลขป้าย...',
      allowClear: true,
      width: '100%'
    });
  }

  // ล้างค่ายอดรวม
  function clearCampaignSelection() {
    $('#CampaignID').val(null).empty().prop('disabled', true).trigger('change.select2');

    $('#TotalSaleCampaign').val('0.00');
    calculateBalanceCampaign?.();
  }

  // ล้างแคมเปญที่เลือก
  function resetCampaign(message = '') {
    clearCampaignSelection();

    if (message) {
      showCampaignWarning(message);
    }
  }

  // format money
  function formatMoney(num) {
    if (!num || isNaN(num)) return '0.00';
    return Number(num).toLocaleString(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }

  // แจ้งเตือนเลือกฟิลด์ไม่ครบ ไม่แสดงแคมเปญ
  function showCampaignWarning(message) {
    $('#campaignWarning').removeClass('d-none').text(message);
  }

  function hideCampaignWarning() {
    $('#campaignWarning').addClass('d-none').text('');
  }

  function loadCampaign() {
    const model_id = $('#model_id').val();
    const subModel_id = $('#subModel_id').val();
    const year = $('#pricelist_year').val() || $('#Year').val();

    if (!model_id) {
      showCampaignWarning('กรุณาเลือกรถรุ่นหลักก่อน');
      return;
    }

    if (!subModel_id) {
      showCampaignWarning('กรุณาเลือกรถรุ่นย่อยก่อน');
      return;
    }

    if (!year) {
      showCampaignWarning('กรุณาระบุปีรถก่อน');
      return;
    }

    hideCampaignWarning();
    $('#CampaignID').prop('disabled', false);

    $.ajax({
      url: '/purchase-order/get-campaign',
      type: 'GET',
      data: {
        subModel_id: subModel_id,
        year: year
      },
      success: function (res) {
        $('#CampaignID').empty();

        if (!res || res.length === 0) {
          resetCampaign('ไม่มีแคมเปญสำหรับรุ่นและปีนี้');
          return;
        }

        res.forEach(c => {
          const appellation = c.appellation?.name ?? c.camName_id;
          const typeName = c.type?.name ?? '-';
          const option = new Option(
            `(${typeName}) ${appellation} - ${formatMoney(c.cashSupport_final)} บาท`,
            c.id,
            false,
            false
          );

          $(option).attr('data-cash-support-final', c.cashSupport_final);
          $('#CampaignID').append(option);
        });

        $('#CampaignID').trigger('change.select2');
      },
      error: function () {
        resetCampaign('เกิดข้อผิดพลาดในการโหลดแคมเปญ');
      }
    });
  }

  // รวมค่าแคมเปญ
  function calcTotalCampaign() {
    let total = 0;

    $('#CampaignID option:selected').each(function () {
      const cash = parseFloat($(this).data('cashSupportFinal')) || 0;
      total += cash;
    });

    $('#TotalSaleCampaign').val(formatMoney(total));
    calculateBalanceCampaign?.();
  }

  $('#CampaignID').on('select2:select select2:unselect', function () {
    calcTotalCampaign();
  });

  $('#model_id').on('change', function () {
    clearCampaignSelection();
    loadCampaign();
  });

  $('#subModel_id').on('change', function () {
    clearCampaignSelection();
    loadCampaign();
  });

  $('#Year, #pricelist_year').on('keyup change', function () {
    clearCampaignSelection();
    loadCampaign();
  });

  const isEditPage = $('#CampaignID option:selected').length > 0;

  if (isEditPage) {
    calcTotalCampaign();
  } else if ($('#subModel_id').val() && ($('#pricelist_year').val() || $('#Year').val())) {
    loadCampaign();
  } else {
    resetCampaign();
  }
});

//edit : auto value -> บวกหัว (90%), ราคาสุทธิบวกหัว
let salePriceInput;
let markupInput;
let markup90Input;
let finalPriceInput;
let discountInput;

let downPaymentInput;
let downPaymentPercentInput;
let totalPaymentAtDeliveryInput;
let totalPaymentAtDeliveryCarInput;

let isInitialLoad = true;

function getNumber(el) {
  if (!el || !el.value) return 0;
  return parseFloat(el.value.replace(/,/g, '')) || 0;
}

function calculateCarPrice(e) {
  const salePrice = getNumber(salePriceInput);
  const markup = getNumber(markupInput);
  const discount = getNumber(discountInput);

  // บวกหัว 90%
  const markup90 = markup * 0.9;

  if (markup90Input) {
    markup90Input.value = markup90.toLocaleString(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }

  // ราคารถสุทธิรวมบวกหัว
  const finalPrice = salePrice + markup - discount;

  if (finalPriceInput) {
    finalPriceInput.value = finalPrice.toLocaleString(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }

  // ล้างค่าเมื่อเปลี่ยน
  if (
    !isInitialLoad &&
    (document.activeElement === salePriceInput ||
      document.activeElement === markupInput ||
      document.activeElement === discountInput)
  ) {
    downPaymentInput.value = '';
    downPaymentPercentInput.value = '';

    if (totalPaymentAtDeliveryInput) {
      totalPaymentAtDeliveryInput.value = '';
    }

    if (totalPaymentAtDeliveryCarInput) {
      totalPaymentAtDeliveryCarInput.value = '';
    }
  }

  calculateRemaining?.();
  calculateBalanceCampaign?.();

  return { markup, finalPrice, discount };
}

// เงินดาวน์ และ %
function calculateDownPayment() {
  const finalPrice = getNumber(finalPriceInput);
  const downPayment = getNumber(downPaymentInput);
  const downPercent = getNumber(downPaymentPercentInput);

  if (document.activeElement === downPaymentInput) {
    const percent = finalPrice > 0 ? (downPayment / finalPrice) * 100 : 0;
    downPaymentPercentInput.value = percent.toLocaleString(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }

  if (document.activeElement === downPaymentPercentInput) {
    const dp = (finalPrice * downPercent) / 100;
    downPaymentInput.value = dp.toLocaleString(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });

    if (window.jQuery) {
      $(downPaymentInput).trigger('input');
    }
  }

  if (document.activeElement === downPaymentInput) {
    if (typeof calculateRemaining === 'function') {
      calculateRemaining();
    }
  }
}

// INIT (DOM READY)
document.addEventListener('DOMContentLoaded', function () {
  salePriceInput = document.getElementById('price_sub');
  markupInput = document.getElementById('MarkupPrice');
  markup90Input = document.querySelector('input[name="Markup90"]');
  finalPriceInput = document.getElementById('CarSalePriceFinal');
  discountInput = document.getElementById('discount');

  downPaymentInput = document.getElementById('DownPayment');
  downPaymentPercentInput = document.getElementById('DownPaymentPercentage');
  totalPaymentAtDeliveryInput = document.getElementById('TotalPaymentatDelivery');
  totalPaymentAtDeliveryCarInput = document.getElementById('TotalPaymentatDeliveryCar');

  if (salePriceInput) salePriceInput.addEventListener('input', calculateCarPrice);
  if (markupInput) markupInput.addEventListener('input', calculateCarPrice);
  if (discountInput) discountInput.addEventListener('input', calculateCarPrice);

  if (downPaymentInput) downPaymentInput.addEventListener('input', calculateDownPayment);
  if (downPaymentPercentInput) downPaymentPercentInput.addEventListener('input', calculateDownPayment);

  isInitialLoad = false;

  if (typeof calculateRemaining === 'function') {
    calculateRemaining();
  }
});

//edit : total payment delivery ค่าใช้จ่ายวันออกรถ
function calculateTotalPaymentAtDelivery() {
  const downPayment = safeNumber('#DownPayment');
  const downDiscount = safeNumber('#DownPaymentDiscount');
  const giftTotal = safeNumber('#total_gift_used');
  const ExtraTotal = safeNumber('#total_extra_used');
  const turnCost = safeNumber('#cost_turn');
  const cashDeposit = safeNumber('#CashDeposit');
  const otherCostFi = safeNumber('#other_cost_fi');
  const vatExtra = safeNumber('#AccessoryExtraVat');

  const total = downPayment + ExtraTotal + otherCostFi + vatExtra - (downDiscount + turnCost + cashDeposit);

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

// edit : ยอดรวมการชำระเงินแต่ละครั้ง
function calculatePaymentTotal() {
  let total = 0;
  document.querySelectorAll('input[name="payment_cost[]"]').forEach(input => {
    const value = parseFloat((input.value || '0').replace(/,/g, ''));
    if (!isNaN(value)) total += value;
  });
  return total;
}

//edit : ยอดคงเหลือ
function calculateBalance() {
  const carSale = safeNumber('#price_sub');
  const ExtraTotal = safeNumber('#total_extra_used');
  const turnCost = safeNumber('#cost_turn');
  const cashDeposit = safeNumber('#CashDeposit');
  const discount = safeNumber('#PaymentDiscount');
  const otherCost = safeNumber('#other_cost');
  const paymentTotal = calculatePaymentTotal();

  const total = carSale + ExtraTotal + otherCost - (turnCost + cashDeposit + discount + paymentTotal);

  $('.balance-display').val(
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
  const comturn = safeNumber('#com_turn');
  const reserve = safeNumber('#CashDeposit');
  const sale = safeNumber('#price_sub');

  $('#summarySubCarModel').val(model);
  $('#summaryTurn').val(turn.toLocaleString(undefined, { minimumFractionDigits: 2 }));
  $('#summaryComTurn').val(comturn.toLocaleString(undefined, { minimumFractionDigits: 2 }));
  $('#summaryCashDeposit').val(reserve.toLocaleString(undefined, { minimumFractionDigits: 2 }));
  $('#summaryCarSale').val(sale.toLocaleString(undefined, { minimumFractionDigits: 2 }));

  calculateTotalPaymentAtDelivery();
  calculateBalance();
  calculateInstallment();
  calculateCarPrice();
}

// edit : ค่างวด (กรณีไม่มี ALP)
function calculateInstallment() {
  const financeAmount = safeNumber('#balanceFinanceDisplay');
  const interestRate = Number($('#remaining_interest').val());
  const periodMonths = Number($('#remaining_period').val());

  if (!financeAmount || !periodMonths || periodMonths <= 0) {
    return;
  }

  const years = periodMonths / 12;
  const totalInterest = (financeAmount * interestRate * years) / 100;
  const totalWithInterest = financeAmount + totalInterest;

  const monthlyPayment = totalWithInterest / periodMonths;
  const interPayment = Math.ceil(monthlyPayment);

  $('#remaining_alp').val(
    Number(interPayment).toLocaleString(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    })
  );
}

//edit : com C
function calculateCommission() {
  const isApproved = $('#ApprovalSignature').is(':checked') || $('#GMApprovalSignature').is(':checked');

  let interest = parseFloat($('#remaining_interest').val()) || 0;
  const typeCom = $('#remaining_type_com').val();
  let commission = 0;

  const comNumber = typeCom ? parseInt(typeCom.replace('C', '')) : 0;

  if (isApproved) {
    commission = 0;
  } else {
    if (interest >= 3 && comNumber >= 10) {
      commission = 500;
    } else {
      commission = 0;
    }
  }

  calculateCommissionSale();

  $('#remaining_total_com').val(
    commission.toLocaleString(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    })
  );
}

//edit : com sale
function calculateCommissionSale() {
  let balanceCam = safeNumber('#balanceCampaign');
  const giftCom = safeNumber('#total_gift_com');
  const extraCom = safeNumber('#total_extra_com');
  const fiCom = safeNumber('#remaining_total_com');
  const turnCom = safeNumber('#com_turn');
  const comSpecial = safeNumber('#CommissionSpecial');

  const selectedModel = $('#model_id option:selected');
  const perBudget = parseFloat(selectedModel.data('perbudget')) || 0;

  if (balanceCam >= 0) {
    balanceCam = Math.min(balanceCam, 2500);
  } else {
    balanceCam = balanceCam * 2 * (perBudget / 100);
  }

  const totalCommission = balanceCam + giftCom + extraCom + fiCom + turnCom + comSpecial;

  const formatted = totalCommission.toLocaleString(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });

  const totalComAcc = giftCom + extraCom;

  $('#ComGiftDisplay').val(
    totalComAcc.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
  );

  $('#TotalbalanceCampaign').val(
    balanceCam.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
  );

  $('#ComInterestDisplay').val(fiCom.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

  $('#CommissionSaleDisplay').val(formatted);

  $('#CommissionSale').val(totalCommission.toFixed(2));
}

// edit : cal balance cam
function calculateBalanceCampaign() {
  const paymentType = document.querySelector('#payment_mode')?.value || '-';

  const totalCampaign = safeNumber('#TotalSaleCampaign');
  const markup90 = safeNumber('#Markup90');
  const kickback = safeNumber('#kickback');

  const downPay = safeNumber('#DownPaymentDiscount');
  const disC = safeNumber('#discount');
  const gift = safeTextNumber('#total-price-gift');
  const refA = safeNumber('#ReferrerAmount');
  const vatGift = safeNumber('#AccessoryGiftVat');

  const payDis = safeNumber('#PaymentDiscount');

  const totalCam90 = totalCampaign + markup90 + kickback;
  const totalUseFinance = downPay + gift + refA + vatGift + disC;
  const totalUseNon = payDis + gift + refA;

  let balance = 0;

  if (paymentType === 'finance') {
    balance = (totalCam90 - totalUseFinance) / 2;
  } else {
    balance = (totalCampaign - totalUseNon) / 2;
  }

  const balanceCampaignInput = document.getElementById('balanceCampaign');
  if (balanceCampaignInput) {
    balanceCampaignInput.value = formatNumber(balance);
  }

  calculateCommissionSale();
}

//edit : event update
$(document).on('input', 'input[name="payment_cost[]"]', calculateBalance);

$(document).on(
  'input change',
  '#payment_type, #TotalSaleCampaign, #Markup90, #kickback, #DownPaymentDiscount, #total-price-gift, #PaymentDiscount, #ReferrerAmount, #AccessoryGiftVat, #discount',
  calculateBalanceCampaign
);

$(document).on(
  'input change',
  '#remaining_total_com, #com_turn, #CommissionSpecial, #model_id',
  calculateCommissionSale
);

$(document).ready(function () {
  $('#carOrderSubModel, #cost_turn, #com_turn, #CashDeposit, #price_sub').on('input change', updateSummary);
  $(
    '#DownPayment, #DownPaymentDiscount, #cost_turn, #total_gift_used, #total_extra_used, #CashDeposit, #other_cost_fi, #AccessoryExtraVat'
  ).on('input change', calculateTotalPaymentAtDelivery);
  $('#CarSalePriceFinal, #DownPayment').on('input change', calculateRemaining);
  $('#remaining_interest, #remaining_period').on('input change', calculateInstallment);
  $('#price_sub, #total_extra_used, #cost_turn, #CashDeposit, #PaymentDiscount, #other_cost').on(
    'input change',
    calculateBalance
  );
  $('#remaining_interest, #remaining_type_com, #ApprovalSignature, #GMApprovalSignature').on(
    'input change',
    calculateCommission
  );

  $('#ApprovalSignature, #GMApprovalSignature').on('change', calculateCommission);

  calculateBalanceCampaign();
  calculateCommissionSale();
  calculateCommission();

  updateSummary();
});

//edit : radio reservation payment
$(document).ready(function () {
  $('#bankReservation, #checkReservation, #creditReservation, #danuReservation').hide();

  const selected = $('input[name="reservationCondition"]:checked').val();
  if (selected) {
    showRemaining(selected);
  }

  $('input[name="reservationCondition"]').change(function () {
    const type = $(this).val();
    showRemaining(type);
  });

  function showRemaining(type) {
    $('#bankReservation, #creditReservation, #checkReservation, #danuReservation').hide();

    if (type === 'credit') $('#creditReservation').show();
    else if (type === 'check') $('#checkReservation').show();
    else if (type === 'transfer') $('#bankReservation').show();
    else if (type === 'cash') $('#danuReservation').show();
  }
});

//edit : radio payment remaining
$(document).ready(function () {
  $('#financeSection1, #financeSection2, #bankRemain, #checkRemain, #creditRemain, #nonFinanceSelect').hide();

  function updateTab2Label(mode) {
    if (mode === 'finance') {
      $('#sumTab2Label').text('ข้อมูลไฟแนนซ์');
    } else {
      $('#sumTab2Label').text('ข้อมูลการจ่ายเงิน');
    }
  }

  function showCorrectSection() {
    const mode = $('#payment_mode').val();
    const type = $('#remainingConditionSelect').val();

    $('#financeSection1, #financeSection2, #bankRemain, #checkRemain, #creditRemain, #nonFinanceSelect').hide();

    if (mode === 'finance') {
      $('#financeSection1').show();
      $('#financeSection2').show();
      $('#remainingCondition').val('finance');
    } else if (mode === 'non-finance') {
      $('#nonFinanceSelect').show();
      $('#remainingCondition').val(type || '');

      if (type === 'credit') $('#creditRemain').show();
      if (type === 'check') $('#checkRemain').show();
      if (type === 'transfer') $('#bankRemain').show();
    } else {
      $('#remainingCondition').val('');
    }

    updateTab2Label(mode);
    updateProvince();
    updateRemainingDate();
    calculateBalanceCampaign();
  }

  // province
  function updateProvince() {
    const mode = $('#payment_mode').val();
    let province = '';

    if (mode === 'finance') {
      province = $('#RegistrationProvince_finance').val();
    } else {
      province = $('#RegistrationProvince_cash').val();
    }

    $('#RegistrationProvince').val(province);
  }

  $('#RegistrationProvince_finance').on('change', updateProvince);
  $('#RegistrationProvince_cash').on('change', updateProvince);

  // วันที่จ่ายเงิน
  function updateRemainingDate() {
    const mode = $('#payment_mode').val();
    let dateValue = '';

    if (mode === 'finance') {
      dateValue = $('#remaining_date_finance').val();
    } else {
      dateValue = $('#remaining_date_cash').val();
    }

    $('#remaining_date').val(dateValue); // hidden input
  }

  $('#remaining_date_finance').on('change', updateRemainingDate);
  $('#remaining_date_cash').on('change', updateRemainingDate);

  // เงินสด
  function togglePaymentSection() {
    const mode = $('#payment_mode').val();

    if (mode === 'non-finance') {
      $('#paymentSection').show();
    } else {
      $('#paymentSection').hide();
    }
  }

  togglePaymentSection();

  $('#payment_mode').on('change', togglePaymentSection);

  showCorrectSection();

  $('#payment_mode').on('change', showCorrectSection);

  $('#remainingConditionSelect').on('change', function () {
    $('#remainingCondition').val($(this).val());
    showCorrectSection();
  });

  $('#purchaseForm').on('submit', function () {
    showCorrectSection();
    updateProvince();
    updateRemainingDate();
  });
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

//edit : เลือกไฟแนนซ์ แล้วแสดงปีตาม max year
// function renderPeriods(maxYear, selectedPeriod = null) {
//   const $period = $('#remaining_period');

//   if (!$period.length) return;

//   $period.empty();
//   $period.append('<option value="">-- เลือกงวด --</option>');

//   if (!maxYear || isNaN(maxYear)) {
//     $period.prop('disabled', true);
//     return;
//   }

//   $period.prop('disabled', false);

//   const maxMonth = maxYear * 12;

//   for (let m = 12; m <= maxMonth; m += 12) {
//     $period.append(`<option value="${m}">${m} งวด</option>`);
//   }

//   if (selectedPeriod && selectedPeriod <= maxMonth) {
//     $period.val(String(selectedPeriod)).trigger('change');
//   }
// }

// เมื่อเลือกไฟแนนซ์ใหม่ / โหลดหน้า: ทำให้เลือกงวดตรงกับค่าที่มีใน DB
// $(document).ready(function () {
//   $('#remaining_finance').on('change', function () {
//     const maxYear = Number($(this).find('option:selected').data('max-year')) || 0;
//     renderPeriods(maxYear);
//   });

//   const finance = $('#remaining_finance option:selected');
//   const maxYear = Number(finance.data('max-year')) || 0;
//   const selectedPeriod = Number($('#remaining_period').data('selected')) || 0;

//   if (maxYear) {
//     renderPeriods(maxYear, selectedPeriod);
//   }
// });

//edit preview : all campaign
function getSelectedCampaignText() {
  const campaignSelect = document.getElementById('CampaignID');
  if (!campaignSelect) return '-';

  const selectedOptions = Array.from(campaignSelect.selectedOptions || []);
  if (selectedOptions.length === 0) return '-';

  return selectedOptions.map(opt => opt.textContent.trim()).join(' + ');
}

// blur focus previewPurchase
$(document).on('hide.bs.modal', '#previewPurchase', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

//edit : previewPurchase
document.addEventListener('DOMContentLoaded', function () {
  const modalElement = document.getElementById('previewPurchase');
  if (!modalElement) return;

  const modal = new bootstrap.Modal(modalElement);
  const content = document.getElementById('previewPurchaseContent');

  const btnPreviewCar = document.getElementById('btnPreviewCar');
  const btnPreviewMore = document.getElementById('btnPreviewMore');

  // check role
  const userRole = document.getElementById('userRole')?.value || '';
  const hasApproval = document.getElementById('hasApproval').value === '1';

  //check brand
  const userBrand = document.getElementById('userBrand')?.value || '';

  // button
  const btnSave = document.getElementById('btnUpdatePurchase');
  const btnRequestNormal = document.getElementById('btnRequestNormal');
  const btnRequestOverBudget = document.getElementById('btnRequestOverBudget');

  const approvalRequested = document.getElementById('approvalRequested')?.value === '1';
  const approvalType = document.getElementById('approvalType')?.value || '';

  function handlePreview() {
    function formatThaiDate(inputId, type = 'date') {
      const value = document.getElementById(inputId)?.value || '-';
      if (!value || value === '-') return '-';

      if (type === 'month') {
        const [year, month] = value.split('-');
        return `${month}/${parseInt(year) + 543}`;
      }

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
    const customerSale = document.getElementById('sale_name')?.value || '-';
    let BookingDate = formatThaiDate('BookingDate');

    //ข้อมูลการขาย
    const model = document.querySelector('#model_id option:checked')?.textContent || '-';
    const subModel = document.querySelector('#subModel_id option:checked')?.textContent || '-';
    const vinNumber = document.getElementById('carOrderVin')?.value || '-';
    const option = document.getElementById('option')?.value || '-';

    //color
    let color = '-';

    const colorInput = document.getElementById('Color');
    if (colorInput) {
      color = colorInput.value || '-';
    }

    const colorSelect = document.getElementById('gwm_color');
    if (colorSelect) {
      const selectedOption = colorSelect.options[colorSelect.selectedIndex];
      color = selectedOption?.text || '-';
    }

    let interiorColorHtml = '';

    const interiorSelect = document.getElementById('interior_color');
    if (interiorSelect) {
      const selectedOption = interiorSelect.options[interiorSelect.selectedIndex];
      const interiorColor = selectedOption?.text || '-';

      interiorColorHtml = `<div class="mf-info-row"><span class="mf-info-label">สีภายใน</span><span class="mf-info-val">${interiorColor}</span></div>`;
    }

    //option
    let optionHtml = '';

    if (userBrand != 2) {
      optionHtml = `<div class="mf-info-row"><span class="mf-info-label">Option</span><span class="mf-info-val">${option}</span></div>`;
    }

    const carSale = document.getElementById('price_sub')?.value || '-';
    const extraTotal = document.querySelector('#total-price-extra')?.textContent || '-';
    const giftTotal = Number(document.querySelector('#total_gift_used')?.value || 0).toLocaleString(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
    const cashDeposit = document.getElementById('CashDeposit')?.value || '-';
    const summaryExtraTotal = Number(document.querySelector('#total_extra_used')?.value || 0).toLocaleString(
      undefined,
      {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }
    );
    const turn = document.getElementById('cost_turn')?.value || '-';

    // รูปแบบการชำระเงิน
    const paymentType = document.querySelector('#payment_mode')?.value || '-';

    //if
    // เงินดาวน์
    const downPayment = document.getElementById('DownPayment')?.value || '-';
    const downPaymentPercentage = document.getElementById('DownPaymentPercentage')?.value || '-';
    const downPaymentDiscount = document.getElementById('DownPaymentDiscount')?.value || '-';
    const otherCostFi = document.getElementById('other_cost_fi')?.value || '-';
    const reasonOtherCostFi = document.getElementById('reason_other_cost_fi')?.value || '-';
    const vatExtra = document.getElementById('AccessoryExtraVat')?.value || '-';
    const discount = document.getElementById('discount')?.value || '-';

    // วันออกรถ
    const poNumber = document.getElementById('remaining_po_number')?.value || '-';
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
    const kickback = parseFloat(document.getElementById('kickback')?.value.replace(/,/g, '') || 0);
    const totalcam90 = totalCampaign + markup90 + kickback;

    //หาค่า ยอดรวมรายการที่ใช้
    const downPay = parseFloat(document.getElementById('DownPaymentDiscount')?.value.replace(/,/g, '') || 0);
    const disC = parseFloat(document.getElementById('discount')?.value.replace(/,/g, '') || 0);
    const gift = parseFloat(document.querySelector('#total-price-gift')?.textContent.replace(/,/g, '') || 0);
    const refA = parseFloat(document.getElementById('ReferrerAmount')?.value.replace(/,/g, '') || 0);
    const vatGift = parseFloat(document.getElementById('AccessoryGiftVat')?.value.replace(/,/g, '') || 0);

    //แนะนำ
    const customerIDRef = document.getElementById('customerIDRef')?.value || '-';
    const ReferrerAmount = document.getElementById('ReferrerAmount')?.value || '-';

    const totalUseFinance = downPay + gift + refA + vatGift + disC;

    //หาค่า คงเหลือ
    const totalBalanceFinance = totalcam90 - totalUseFinance;
    const totalBalanceFinance2 = totalBalanceFinance / 2;
    // const totalBalanceFinance2 = Math.max(totalBalanceFinance / 2, 0); ให้เป็น 0 ถ้าติดลบ

    //else
    const paymentDiscount = document.getElementById('PaymentDiscount')?.value || '0';
    const otherCost = document.getElementById('other_cost')?.value || '-';
    const reasonOtherCost = document.getElementById('reason_other_cost')?.value || '-';
    const balanceValue = parseFloat(document.getElementById('balance')?.value.replace(/,/g, '') || 0);

    // ฟอร์แมตเป็นเลขไทย มี comma และ 2 ทศนิยม
    const balanceDisplay = balanceValue.toLocaleString(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });

    const payDis = parseFloat(document.getElementById('PaymentDiscount')?.value.replace(/,/g, '') || 0);

    //หาค่า ยอดรวมรายการที่ใช้
    const totalUse = payDis + gift + refA;

    //หาค่า คงเหลือ
    const totalBalance = totalCampaign - totalUse;
    const totalBalance2 = totalBalance / 2;
    // const totalBalance2 = Math.max(totalBalance / 2, 0); ให้เป็น 0 ถ้าติดลบ

    let price = '-';
    let discountHtml = '';
    let campaignHtml = '';

    // ยอดรวม campaign แบ่งครึ่ง
    // const balanceCampaignInput = document.getElementById('balanceCampaign');
    // if (balanceCampaignInput) {
    //   if (paymentType === 'finance') {
    //     balanceCampaignInput.value = totalBalanceFinance2.toLocaleString('th-TH', { minimumFractionDigits: 2 });
    //   } else {
    //     balanceCampaignInput.value = totalBalance2.toLocaleString('th-TH', { minimumFractionDigits: 2 });
    //   }
    // }

    const balanceCampaignValue = safeNumber('#balanceCampaign');
    const balanceCampaignForDisplay = Math.max(balanceCampaignValue, 0);

    const balanceCampaignDisplay = balanceCampaignForDisplay.toLocaleString(undefined, {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });

    if (paymentType === 'finance') {
      price = document.getElementById('CarSalePriceFinal')?.value || '-';

      discountHtml = `
          <div class="mf-info-row"><span class="mf-info-label">เงินดาวน์</span><span class="mf-info-val">${downPayment} บาท</span></div>
          <div class="mf-info-row"><span class="mf-info-label">เปอร์เซ็นต์เงินดาวน์</span><span class="mf-info-val">${downPaymentPercentage} %</span></div>
          <div class="mf-info-row"><span class="mf-info-label">ส่วนลดเงินดาวน์</span><span class="mf-info-val">${downPaymentDiscount} บาท</span></div>
          <div class="mf-info-row"><span class="mf-info-label">ส่วนลดราคารถ</span><span class="mf-info-val">${discount} บาท</span></div>
          <div class="mf-info-row"><span class="mf-info-label">ค่าใช้จ่ายอื่นๆ</span><span class="mf-info-val">${otherCostFi} บาท</span></div>
          <div class="mf-info-row"><span class="mf-info-label">หมายเหตุ ค่าใช้จ่ายอื่นๆ</span><span class="mf-info-val">${reasonOtherCostFi}</span></div>
          <div class="mf-info-row"><span class="mf-info-label">Vat ซื้อเพิ่ม</span><span class="mf-info-val">${vatExtra} บาท</span></div>
          <p class="mf-sub-heading mt-2">วันออกรถ</p>
          <div class="mf-info-row"><span class="mf-info-label">สรุปค่าใช้จ่ายวันออกรถ</span><span class="mf-info-val">${TotalPaymentatDeliveryCar} บาท</span></div>
          <div class="mf-info-row"><span class="mf-info-label">Po Number</span><span class="mf-info-val">${poNumber}</span></div>
          <div class="mf-info-row"><span class="mf-info-label">ไฟแนนซ์</span><span class="mf-info-val">${financeCompany}</span></div>
          <div class="mf-info-row"><span class="mf-info-label">ยอดจัดไฟแนนซ์</span><span class="mf-info-val">${balanceFinanceDisplay} บาท</span></div>
          <div class="mf-info-row"><span class="mf-info-label">ดอกเบี้ย</span><span class="mf-info-val">${interest} %</span></div>
          <div class="mf-info-row"><span class="mf-info-label">งวดผ่อน</span><span class="mf-info-val">${period}</span></div>
          <div class="mf-info-row"><span class="mf-info-label">ค่างวด (กรณีไม่มี ALP)</span><span class="mf-info-val">${alp} บาท</span></div>
          <div class="mf-info-row"><span class="mf-info-label">ค่างวด (รวม ALP)</span><span class="mf-info-val">${includingAlp} บาท</span></div>
          <div class="mf-info-row"><span class="mf-info-label">ยอดเงิน ALP ที่หักจากใบเสร็จดาวน์</span><span class="mf-info-val">${totalAlp} บาท</span></div>
          <div class="mf-info-row"><span class="mf-info-label">ดอกเบี้ยคอม</span><span class="mf-info-val">${typeCom}</span></div>
          <div class="mf-info-row"><span class="mf-info-label">ยอดเงินค่าคอม</span><span class="mf-info-val">${totalCom} บาท</span></div>
      `;

      campaignHtml = `
          <div class="mf-info-row"><span class="mf-info-label">รวมงบแคมเปญ</span><span class="mf-info-val">${totalCampaign.toLocaleString('th-TH', { minimumFractionDigits: 2 })} บาท</span></div>
          <div class="mf-info-row"><span class="mf-info-label">บวกหัว (90%)</span><span class="mf-info-val">${markup90.toLocaleString('th-TH', { minimumFractionDigits: 2 })} บาท</span></div>
          <div class="mf-info-row"><span class="mf-info-label">Kick Back</span><span class="mf-info-val">${kickback.toLocaleString('th-TH', { minimumFractionDigits: 2 })} บาท</span></div>
          <div class="mf-info-row"><span class="mf-info-label">ยอดรวมแคมเปญ (รวมบวกหัว 90%)</span><span class="mf-info-val">${totalcam90.toLocaleString('th-TH', { minimumFractionDigits: 2 })} บาท</span></div>
          <div class="mf-info-row"><span class="mf-info-label">ส่วนลดเงินดาวน์</span><span class="mf-info-val">${downPaymentDiscount} บาท</span></div>
          <div class="mf-info-row"><span class="mf-info-label">ส่วนลดราคารถ</span><span class="mf-info-val">${discount} บาท</span></div>
          <div class="mf-info-row"><span class="mf-info-label">ส่วนต่างของแถม</span><span class="mf-info-val">${giftTotal} บาท</span></div>
          <div class="mf-info-row"><span class="mf-info-label">ค่าแนะนำ</span><span class="mf-info-val">${ReferrerAmount} บาท</span></div>
          <div class="mf-info-row"><span class="mf-info-label">Vat ของแถม</span><span class="mf-info-val">${vatGift.toLocaleString('th-TH', { minimumFractionDigits: 2 })} บาท</span></div>
          <div class="mf-info-row"><span class="mf-info-label">ยอดรวมรายการที่ใช้</span><span class="mf-info-val">${totalUseFinance.toLocaleString('th-TH', { minimumFractionDigits: 2 })} บาท</span></div>
          <div class="mf-info-row"><span class="mf-info-label">คงเหลือ</span><span class="mf-info-val">${totalBalanceFinance.toLocaleString('th-TH', { minimumFractionDigits: 2 })} บาท</span></div>
          <div class="mf-info-row"><span class="mf-info-label">คงเหลือ (แบ่ง 2 ส่วน)</span><span class="mf-info-val">${balanceCampaignDisplay} บาท</span></div>
      `;
    } else {
      price = document.getElementById('price_sub')?.value || '-';

      discountHtml = `
          <div class="mf-info-row"><span class="mf-info-label">ส่วนลด</span><span class="mf-info-val">${paymentDiscount} บาท</span></div>
          <div class="mf-info-row"><span class="mf-info-label">ค่าใช้จ่ายอื่นๆ</span><span class="mf-info-val">${otherCost} บาท</span></div>
          <div class="mf-info-row"><span class="mf-info-label">หมายเหตุ ค่าใช้จ่ายอื่นๆ</span><span class="mf-info-val">${reasonOtherCost}</span></div>
          <div class="mf-info-row"><span class="mf-info-label">คงเหลือ</span><span class="mf-info-val">${balanceDisplay} บาท</span></div>
      `;

      campaignHtml = `
          <div class="mf-info-row"><span class="mf-info-label">รวมงบแคมเปญ</span><span class="mf-info-val">${totalCampaign.toLocaleString('th-TH', { minimumFractionDigits: 2 })} บาท</span></div>
          <div class="mf-info-row"><span class="mf-info-label">ส่วนลด</span><span class="mf-info-val">${paymentDiscount} บาท</span></div>
          <div class="mf-info-row"><span class="mf-info-label">ส่วนต่างของแถม</span><span class="mf-info-val">${giftTotal} บาท</span></div>
          <div class="mf-info-row"><span class="mf-info-label">ค่าแนะนำ</span><span class="mf-info-val">${ReferrerAmount} บาท</span></div>
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
            <span>${balanceCampaignDisplay} บาท</span>
          </div>
      `;
    }

    //campaign
    // ดึงชื่อแคมเปญที่เลือกไว้
    // const campaignSelect = document.getElementById('CampaignID');
    // let campaignList = [];

    // if (campaignSelect) {
    //   const selectedOptions = Array.from(campaignSelect.selectedOptions);
    //   campaignList = selectedOptions.map(opt => opt.textContent.trim());
    // }
    const campaignText = getSelectedCampaignText();

    // รวมชื่อทั้งหมดคั่นด้วย " + "
    // const campaignText = campaignList.length > 0 ? campaignList.join(' + ') : '-';

    //ของแถม
    const giftRows = document.querySelectorAll('#giftTablePrice tbody tr');
    let giftHtml = '';
    let totalGift = 0;
    let totalGiftCom = 0;

    if (giftRows.length > 0 && !document.getElementById('no-data-row')) {
      giftHtml += `<table class="table table-sm table-bordered">
      <thead class="table-light">
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

        const parts = priceComText.split('(');

        price = parseFloat(parts[0].replace(/,/g, '').trim()) || 0;

        if (parts[1]) {
          com = parseFloat(parts[1].replace(')', '').replace(/,/g, '').trim()) || 0;
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
      extraHtml += `<table class="table table-sm table-bordered">
      <thead class="table-light">
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

        const parts = priceComText.split('(');

        price = parseFloat(parts[0].replace(/,/g, '').trim()) || 0;

        if (parts[1]) {
          com = parseFloat(parts[1].replace(')', '').replace(/,/g, '').trim()) || 0;
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

    const AdminSignature = document.querySelector('#AdminSignature').checked ? 'เช็ครายการเรียบร้อยแล้ว' : '-';
    let AdminCheckedDate = formatThaiDate('AdminCheckedDate');
    const CheckerID = document.querySelector('#CheckerID').checked ? 'เช็ครายการเรียบร้อยแล้ว' : '-';
    let CheckerCheckedDate = formatThaiDate('CheckerCheckedDate');
    const SMSignature = document.querySelector('#SMSignature').checked ? 'อนุมัติเรียบร้อยแล้ว' : '-';
    let SMCheckedDate = formatThaiDate('SMCheckedDate');

    const ApprovalSignature = document.querySelector('#ApprovalSignature').checked ? 'อนุมัติเรียบร้อยแล้ว' : '-';
    let ApprovalSignatureDate = formatThaiDate('ApprovalSignatureDate');
    const GMApprovalSignature = document.querySelector('#GMApprovalSignature').checked ? 'อนุมัติเรียบร้อยแล้ว' : '-';
    let GMApprovalSignatureDate = formatThaiDate('GMApprovalSignatureDate');
    let DeliveryEstimateDate = formatThaiDate('DeliveryEstimateDate', 'month');

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

    let dateAppHtml = '';
    if (userRole === 'admin' || userRole === 'audit' || userRole === 'manager' || userRole === 'md') {
      dateAppHtml = `
          <div class="mf-info-row"><span class="mf-info-label">วันที่ส่งมอบของบริษัท</span><span class="mf-info-val">${DeliveryInDMSDate}</span></div>
          <div class="mf-info-row"><span class="mf-info-label">วันที่ส่งมอบของฝ่ายขาย</span><span class="mf-info-val">${DeliveryInCKDate}</span></div>
          <div class="mf-info-row"><span class="mf-info-label">ประมาณการส่งมอบ</span><span class="mf-info-val">${DeliveryEstimateDate}</span></div>
          <p class="mf-sub-heading mt-2">ผู้อนุมัติ</p>
          <div class="mf-info-row"><span class="mf-info-label">ผู้เช็ครายการ (แอดมินขาย)</span><span class="mf-info-val">${AdminSignature}</span></div>
          <div class="mf-info-row"><span class="mf-info-label">วันที่แอดมินเช็ครายการ</span><span class="mf-info-val">${AdminCheckedDate}</span></div>
          <div class="mf-info-row"><span class="mf-info-label">ผู้ตรวจสอบรายการ (IA)</span><span class="mf-info-val">${CheckerID}</span></div>
          <div class="mf-info-row"><span class="mf-info-label">วันที่ฝ่ายตรวจสอบเช็ครายการ</span><span class="mf-info-val">${CheckerCheckedDate}</span></div>
          <div class="mf-info-row"><span class="mf-info-label">ผู้จัดการ อนุมัติการขาย</span><span class="mf-info-val">${SMSignature}</span></div>
          <div class="mf-info-row"><span class="mf-info-label">วันที่ผู้จัดการขายอนุมัติ</span><span class="mf-info-val">${SMCheckedDate}</span></div>
          <div class="mf-info-row"><span class="mf-info-label">ผู้จัดการ อนุมัติกรณีงบเกิน</span><span class="mf-info-val">${ApprovalSignature}</span></div>
          <div class="mf-info-row"><span class="mf-info-label">วันที่ผู้จัดการอนุมัติการขาย</span><span class="mf-info-val">${ApprovalSignatureDate}</span></div>
          <div class="mf-info-row"><span class="mf-info-label">GM อนุมัติกรณีงบเกิน (N)</span><span class="mf-info-val">${GMApprovalSignature}</span></div>
          <div class="mf-info-row"><span class="mf-info-label">วันที่ GM อนุมัติกรณีงบเกิน</span><span class="mf-info-val">${GMApprovalSignatureDate}</span></div>
          <p class="mf-sub-heading mt-2">สถานะ</p>
          <div class="mf-info-row"><span class="mf-info-label">สถานะ</span><span class="mf-info-val">${con_status}</span></div>
    `;
    }

    const html = `
      <div class="row g-3">

        <!-- ฝั่งซ้าย -->
        <div class="col-md-6">

          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon sky"><i class="bx bx-user"></i></div>
              <span class="mf-section-title">ข้อมูลลูกค้า</span>
            </div>
            <div class="mf-section-body">
              <div class="mf-info-row"><span class="mf-info-label">วันที่จอง</span><span class="mf-info-val">${BookingDate}</span></div>
              <div class="mf-info-row"><span class="mf-info-label">ชื่อลูกค้า</span><span class="mf-info-val">${customerName}</span></div>
              <div class="mf-info-row"><span class="mf-info-label">ชื่อฝ่ายขาย</span><span class="mf-info-val">${customerSale}</span></div>
              <div class="mf-info-row"><span class="mf-info-label">ที่อยู่ปัจจุบัน</span><span class="mf-info-val">${currentAddress}</span></div>
              <div class="mf-info-row"><span class="mf-info-label">ที่อยู่สำหรับส่งเอกสาร</span><span class="mf-info-val">${documentAddress}</span></div>
              <div class="mf-info-row"><span class="mf-info-label">เบอร์มือถือ</span><span class="mf-info-val">${customerMobile}</span></div>
            </div>
          </div>

          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon indigo"><i class="bx bx-car"></i></div>
              <span class="mf-section-title">ข้อมูลการขาย</span>
            </div>
            <div class="mf-section-body">
              <div class="mf-info-row"><span class="mf-info-label">รุ่นรถหลัก</span><span class="mf-info-val">${model}</span></div>
              <div class="mf-info-row"><span class="mf-info-label">รุ่นรถย่อย</span><span class="mf-info-val">${subModel}</span></div>
              <div class="mf-info-row"><span class="mf-info-label">Vin-Number</span><span class="mf-info-val">${vinNumber}</span></div>
              ${optionHtml}
              <div class="mf-info-row"><span class="mf-info-label">สี</span><span class="mf-info-val">${color}</span></div>
              ${interiorColorHtml}
              <div class="mf-info-row"><span class="mf-info-label">ราคา</span><span class="mf-info-val">${price} บาท</span></div>
              <div class="mf-info-row"><span class="mf-info-label">เงินจอง</span><span class="mf-info-val">${cashDeposit} บาท</span></div>
              <div class="mf-info-row"><span class="mf-info-label">รถเทิร์น</span><span class="mf-info-val">${turn} บาท</span></div>
              <div class="mf-info-row"><span class="mf-info-label">ลูกค้าจ่ายเพิ่ม</span><span class="mf-info-val">${summaryExtraTotal} บาท</span></div>
              ${discountHtml}
            </div>
          </div>

          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon emerald"><i class="bx bx-map-pin"></i></div>
              <span class="mf-section-title">จังหวัดที่ขึ้นทะเบียน</span>
            </div>
            <div class="mf-section-body">
              <div class="mf-info-row"><span class="mf-info-label">จังหวัดที่ขึ้นทะเบียน</span><span class="mf-info-val">${RegistrationProvince}</span></div>
            </div>
          </div>

          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon pink"><i class="bx bx-user-plus"></i></div>
              <span class="mf-section-title">แนะนำ</span>
            </div>
            <div class="mf-section-body">
              <div class="mf-info-row"><span class="mf-info-label">ผู้แนะนำ</span><span class="mf-info-val">${customerIDRef}</span></div>
              <div class="mf-info-row"><span class="mf-info-label">ยอดเงินค่าแนะนำ</span><span class="mf-info-val">${ReferrerAmount} บาท</span></div>
            </div>
          </div>

          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon amber"><i class="bx bx-purchase-tag"></i></div>
              <span class="mf-section-title">แคมเปญ</span>
            </div>
            <div class="mf-section-body">
              <div class="mf-info-row"><span class="mf-info-label">ข้อมูลแคมเปญ</span><span class="mf-info-val">${campaignText}</span></div>
              ${campaignHtml}
            </div>
          </div>

        </div>

        <!-- ฝั่งขวา -->
        <div class="col-md-6">

          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon emerald"><i class="bx bx-gift"></i></div>
              <span class="mf-section-title">รายละเอียดอุปกรณ์ตกแต่ง (แถม)</span>
            </div>
            <div class="mf-section-body">
              <div class="table-responsive text-nowrap">${giftHtml}</div>
            </div>
          </div>

          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon sky"><i class="bx bx-plus-circle"></i></div>
              <span class="mf-section-title">รายการซื้อเพิ่ม</span>
            </div>
            <div class="mf-section-body">
              <div class="table-responsive text-nowrap">${extraHtml}</div>
            </div>
          </div>

          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon rose"><i class="bx bx-calendar-check"></i></div>
              <span class="mf-section-title">ข้อมูลวันส่งมอบ</span>
            </div>
            <div class="mf-section-body">
              <div class="mf-info-row"><span class="mf-info-label">วันที่ส่งเอกสารสรุปการขาย</span><span class="mf-info-val">${KeyInDate}</span></div>
              <div class="mf-info-row"><span class="mf-info-label">วันส่งมอบจริง (วันที่แจ้งประกัน)</span><span class="mf-info-val">${DeliveryDate}</span></div>
              ${dateAppHtml}
            </div>
          </div>

        </div>
      </div>
    `;

    const raw = document.getElementById('balanceCampaign')?.value || '';
    const balanceCam = parseFloat(raw.replace(/,/g, '')) || 0;
    const selectedModel = document.querySelector('#model_id option:checked');
    const budget = selectedModel?.dataset.overbudget || '-';

    btnSave.classList.add('d-none');
    btnRequestNormal.classList.add('d-none');
    btnRequestOverBudget.classList.add('d-none');

    if (userRole !== 'sale') {
      btnSave.classList.remove('d-none');
      content.innerHTML = html;
      modal.show();
      return;
    }

    // sale เคยขออนุมัติแล้ว แสดงแค่ปิด
    if (approvalRequested || hasApproval) {
      content.innerHTML = html;
      modal.show();
      return;
    }

    // sale ต้องเห็นปุ่ม บันทึก ก่อนขออนุมัติ
    btnSave.classList.remove('d-none');

    if (balanceCam >= 0) {
      btnRequestNormal.classList.remove('d-none');
    } else {
      const overAmount = Math.abs(balanceCam);
      if (overAmount <= budget) {
        btnRequestOverBudget.classList.remove('d-none');
        btnRequestOverBudget.dataset.level = 'manager';
        btnRequestOverBudget.textContent = 'ขอ ผู้จัดการ อนุมัติเกินงบ';
      } else {
        btnRequestOverBudget.classList.remove('d-none');
        btnRequestOverBudget.dataset.level = 'gm';
        btnRequestOverBudget.textContent = 'ขอ GM อนุมัติเกินงบ';
      }
    }

    content.innerHTML = html;
    modal.show();
  }

  btnPreviewCar?.addEventListener('click', handlePreview);
  btnPreviewMore?.addEventListener('click', handlePreview);
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
  $('#filterModel').on('change', function () {
    const modelId = $(this).val();

    $('#filterSubModel').prop('disabled', true).html('<option value="">-- เลือกรุ่นรถย่อย --</option>');

    if (!modelId) return;

    $.get('/api/purchase-order/sub-model/' + modelId, function (res) {
      $('#filterSubModel').prop('disabled', false);

      res.forEach(item => {
        $('#filterSubModel').append(`<option value="${item.id}">${item.name}</option>`);
      });
    });
  });

  bookingTable = $('.bookingTable').DataTable({
    ajax: {
      url: '/purchase-order/list-booking',
      data: function (d) {
        d.model_id = $('#filterModel').val();
        d.sub_model_id = $('#filterSubModel').val();
        d.status_id = $('#filterStatus').val();
        d.booking_start = $('#filterBookingStart').val();
        d.booking_end = $('#filterBookingEnd').val();
      }
    },
    columns: [
      { data: 'No' },
      { data: 'model' },
      { data: 'subModel' },
      { data: 'option' },
      { data: 'order' },
      { data: 'FullName' },
      { data: 'sale' },
      { data: 'date' },
      { data: 'daysBind' },
      { data: 'status' }
    ],
    paging: true,
    searching: true,
    ordering: false,
    pageLength: 10,
    language: {
      lengthMenu: 'แสดง _MENU_ แถว',
      zeroRecords: 'ไม่พบข้อมูล',
      info: 'แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ',
      infoEmpty: 'ไม่มีข้อมูล',
      paginate: { next: 'ถัดไป', previous: 'ก่อนหน้า' }
    }
  });

  $('#btnSearch').on('click', function () {
    bookingTable.ajax.reload();
  });
});

// history
let historyFinalTable;

$(document).ready(function () {
  if ($.fn.DataTable.isDataTable('.historyFinalTable')) {
    $('.historyFinalTable').DataTable().destroy();
  }

  historyFinalTable = $('.historyFinalTable').DataTable({
    ajax: '/purchase-order/list-history',
    columns: [
      { data: 'No' },
      { data: 'FullName' },
      { data: 'code' },
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
        next: 'ถัดไป',
        previous: 'ก่อนหน้า'
      }
    }
  });
});

// blur focus viewPurchaseHistory
$(document).on('hide.bs.modal', '.viewPurchaseHistory', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

//view more history : modal
$(document).on('click', '.btnViewHistory', function () {
  const id = $(this).data('id');

  $.get('/purchase-order/view-more-history/' + id, function (html) {
    $('.viewMoreHistory').html(html);
    $('.viewPurchaseHistory').modal('show');
  });
});

//history ส่งมอบ แสดง campaign
function updateViewMoreCampaign() {
  const el = document.getElementById('viewMoreCampaignText');
  if (!el) return;

  const text = el.dataset.campaign || '-';
  el.textContent = text;
}

// add payment for cash money
document.addEventListener('DOMContentLoaded', function () {
  const container = document.getElementById('paymentContainer');
  const btnAdd = document.getElementById('btnAddPayment');

  if (!container || !btnAdd) {
    return;
  }

  btnAdd.addEventListener('click', function () {
    const newRow = `
      <div class="payment-row row g-2 align-items-end mt-2 pt-2 border-top">
        <input type="hidden" name="payment_id[]" value="">
        <div class="col-md-3">
          <label class="po-label"><i class="bx bx-credit-card me-1"></i>ประเภท</label>
          <select name="payment_type[]" class="form-select">
            <option value="">-- เลือกประเภท --</option>
            <option value="cash">เงินสด</option>
            <option value="transfer">เงินโอน</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="po-label"><i class="bx bx-money me-1"></i>จำนวนเงิน</label>
          <div class="money-wrap">
            <input type="text" name="payment_cost[]" class="form-control text-end money-input">
            <span class="money-suffix">฿</span>
          </div>
        </div>
        <div class="col-md-4">
          <label class="po-label"><i class="bx bx-calendar me-1"></i>วันที่จ่ายเงิน</label>
          <input type="date" name="payment_date[]" class="form-control">
        </div>
        <div class="col-md-1 d-flex align-items-end">
          <button type="button" class="btn btn-outline-danger btnRemove" title="ลบ">
            <i class="bx bx-trash"></i>
          </button>
        </div>
      </div>
    `;

    container.insertAdjacentHTML('beforeend', newRow);
    calculateBalance();
  });

  // ลบแถว
  container.addEventListener('click', function (e) {
    if (e.target.closest('.btnRemove')) {
      const row = e.target.closest('.payment-row');
      const id = row.querySelector('input[name="payment_id[]"]').value;

      if (id) {
        // ถ้าแถวนี้มี ID เก็บไว้ก่อน
        let deleted = document.getElementById('deletedPayments');
        deleted.value += id + ',';
      }

      row.remove();
      calculateBalance();
    }
  });
});

//view report saleCar
document.addEventListener('DOMContentLoaded', function () {
  const modalEl = document.querySelector('.viewExportSaleCar');
  if (!modalEl) return;

  const modal = new bootstrap.Modal(modalEl);
  modal.show();

  // ปิด modal แล้วกลับหน้าก่อนหน้า
  modalEl.addEventListener('hidden.bs.modal', function () {
    window.history.back();
  });
});

//view report gwm stock
document.addEventListener('DOMContentLoaded', function () {
  const modalEl = document.querySelector('.viewExportGwmStock');
  if (!modalEl) return;

  const modal = new bootstrap.Modal(modalEl);
  modal.show();

  // ปิด modal แล้วกลับหน้าก่อนหน้า
  modalEl.addEventListener('hidden.bs.modal', function () {
    window.history.back();
  });
});

// pre-fill จาก customer tracking
document.addEventListener('DOMContentLoaded', function () {
  const el = document.getElementById('prefillData');
  if (!el) return;

  const d = el.dataset;

  if (d.saleId && $('#SaleID').length) {
    $('#SaleID').val(d.saleId);
  }

  // Brand 2: interior_color โหลดจาก server-side → set ตรงได้เลย
  if (d.interiorColorId && $('#interior_color').length) {
    $('#interior_color').val(d.interiorColorId);
  }

  const modelId = d.modelId;
  if (!modelId) return;

  $('#model_id').val(modelId).trigger('change');

  const subModelId = d.subModelId;
  const year = d.year;
  const colorId = d.colorId;
  const pricelistColor = d.pricelistColor;
  const colorText = d.colorText;

  if (!subModelId) return;

  const waitForSubModel = setInterval(function () {
    const $opt = $(`#subModel_id option[value="${subModelId}"]`);
    if (!$opt.length) return;

    clearInterval(waitForSubModel);
    $('#subModel_id').val(subModelId).trigger('change');

    // ── Brand 1 (Mitsubishi): cascade คือ pricelist_color → pricelist_year ──
    if ($('#pricelist_color').length) {
      if (!pricelistColor) return; // ไม่มีข้อมูล pricelist_color → user เลือกเอง

      let attempts = 0;
      const waitForColorOpts = setInterval(function () {
        attempts++;
        if ($('#pricelist_color option').length > 1) {
          clearInterval(waitForColorOpts);
          $('#pricelist_color').val(pricelistColor).trigger('change');

          if (!year) return;
          let yAttempts = 0;
          const waitForYear1 = setInterval(function () {
            yAttempts++;
            const $yOpt = $(`#pricelist_year option[value="${year}"]`);
            if ($yOpt.length) {
              clearInterval(waitForYear1);
              $('#pricelist_year').val(year).trigger('change');
              if (colorText && $('#Color').length) $('#Color').val(colorText);
            }
            if (yAttempts > 30) clearInterval(waitForYear1);
          }, 100);
        }
        if (attempts > 30) clearInterval(waitForColorOpts);
      }, 100);
      return;
    }

    // ── Brand 2/3: cascade คือ pricelist_year (+ gwm_color พร้อมกัน) ──
    if (!year) return;
    const waitForYear = setInterval(function () {
      const $yearOpt = $(`#pricelist_year option[value="${year}"]`);
      if (!$yearOpt.length) return;

      clearInterval(waitForYear);
      $('#pricelist_year').val(year).trigger('change');

      if (!colorId) return;
      let colorAttempts = 0;
      const waitForColor = setInterval(function () {
        colorAttempts++;
        const $colorOpt = $(`#gwm_color option[value="${colorId}"]`);
        if ($colorOpt.length) {
          clearInterval(waitForColor);
          $('#gwm_color').val(colorId);
          return;
        }
        if (colorAttempts > 30) clearInterval(waitForColor);
      }, 100);
    }, 100);
  }, 100);
});

//view report monthly delivery
document.addEventListener('DOMContentLoaded', function () {
  const modalEl = document.querySelector('.viewExportMonthlyDelivery');
  if (!modalEl) return;

  const modal = new bootstrap.Modal(modalEl);
  modal.show();

  // ปิด modal แล้วกลับหน้าก่อนหน้า
  modalEl.addEventListener('hidden.bs.modal', function () {
    window.history.back();
  });
});
