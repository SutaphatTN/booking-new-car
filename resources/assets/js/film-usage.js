$.ajaxSetup({
  headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

// ── DataTable (list page) ──────────────────────────────────
if ($('.filmUsageTable').length) {
  $(document).ready(function () {
    $('.filmUsageTable').DataTable({
      ajax: '/film-usage/list',
      columns: [
        { data: 'No' },
        { data: 'type', className: 'text-center', orderable: false },
        { data: 'order_date' },
        { data: 'vin' },
        { data: 'customer' },
        { data: 'model' },
        { data: 'film_brand' },
        { data: 'total_sqft', className: 'text-end' },
        // { data: 'total_price', className: 'text-end' },
        { data: 'Action', orderable: false, searchable: false },
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

  $(document).on('click', '.btnDeleteFilmUsage', function () {
    const id = $(this).data('id');
    Swal.fire({
      title: 'ยืนยันการลบ?', icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33', cancelButtonColor: '#6c757d',
      confirmButtonText: 'ลบ', cancelButtonText: 'ยกเลิก'
    }).then(function (result) {
      if (!result.isConfirmed) return;
      $.ajax({
        url: '/film-usage/' + id,
        type: 'POST',
        data: { _method: 'DELETE' },
        success: function (res) {
          if (res.success) {
            Swal.fire({ icon: 'success', title: 'ลบสำเร็จ', timer: 1500, showConfirmButton: false });
            $('.filmUsageTable').DataTable().ajax.reload();
          } else {
            Swal.fire({ icon: 'warning', text: res.message });
          }
        }
      });
    });
  });
}

// ════════════════════════════════════════════════════════════
// Create form logic
// ════════════════════════════════════════════════════════════
if ($('#formFilmUsage').length) {

  const SHADES = ['40', '60', '80'];

  const BP_PRICES = {
    'บานหน้า':              { sqft: 16, price: 3000, multiPane: false },
    'บานหลัง':              { sqft: 9,  price: 3000, multiPane: false },
    'กระจกประตูคู่หน้า':   { sqft: 6,  price: 1200, multiPane: true  },
    'กระจกประตูคู่หลัง 1': { sqft: 6,  price: 1200, multiPane: true  },
    'กระจกประตูคู่หลัง 2': { sqft: 6,  price: 1200, multiPane: true  },
    'กระจกหูช้าง':         { sqft: 3,  price: 1000, multiPane: true  },
    'ซันรูฟ':              { sqft: 24, price: 3000, multiPane: false },
  };

  function getBpMultiplier() {
    const code = $('#fu_film_brand_id option:selected').data('code') || 'MX';
    return ({ MX: 1.0, CB: 1.8, BY: 2.0 })[code] || 1.0;
  }

  function fillBpRowPrice($row, position) {
    const config = BP_PRICES[position];
    if (!config) return;
    const mult  = getBpMultiplier();
    const panes = parseInt($row.find('.bpPaneBtn.active').data('panes') || 1);
    $row.find('.rowSqft').val((config.sqft * panes).toFixed(2));
    $row.find('.rowPrice').val(formatMoney(config.price * mult * panes));
    recalcTotals();
  }

  let fuNewCustomer = false;

  function resetNewCustomerBtn() {
    $('#btnNewCustomer').html('<i class="bx bx-user-plus me-1"></i> ลูกค้าใหม่')
      .removeClass('btn-primary').addClass('btn-outline-primary');
  }

  // ── Type toggle ───────────────────────────────────────────
  $('input[name="type"]').on('change', function () {
    const isGeneral = $(this).val() === 'general';
    $('#generalVinSection').toggleClass('d-none', !isGeneral);
    $('#bpVinSection').toggleClass('d-none', isGeneral);
    $('#generalInfoFields').addClass('d-none');
    $('#newCustomerFields').addClass('d-none');
    $('#bpInfoFields').toggleClass('d-none', isGeneral);
    $('#generalPackageSection').toggleClass('d-none', !isGeneral);
    $('#bpPositionSection').toggleClass('d-none', isGeneral);
    $('#btnNewCustomerWrap').toggleClass('d-none', !isGeneral);
    clearRows();
    $('input[name="package"]').prop('checked', false);
    $('.bpPosCheck').prop('checked', false);
    $('#vinSearchStatus').addClass('d-none');
    $('#vinSuggestList').addClass('d-none').empty();
    if (fuNewCustomer) {
      fuNewCustomer = false;
      resetNewCustomerBtn();
      if (isGeneral) $('#generalVinSection').removeClass('d-none');
    }
  });

  // ── VIN Autocomplete (list-group pattern) ────────────────────
  let vinDebounce = null;

  $(document).on('input', '#fu_vin', function () {
    const q = $(this).val().trim().toUpperCase();
    $(this).val(q);
    clearTimeout(vinDebounce);
    $('#vinSuggestList').addClass('d-none').empty();
    $('#vinSearchStatus').addClass('d-none');

    $('#fu_car_order_id, #fu_salecar_id, #fu_model_id').val('');
    $('#fu_customer_name, #fu_sale_person').val('');
    $('#generalInfoFields').addClass('d-none');

    if (q.length < 3) return;

    vinDebounce = setTimeout(function () {
      $('#vinSearchSpinner').removeClass('d-none');
      $.get('/film-usage/vin-suggest', { q }, function (results) {
        $('#vinSearchSpinner').addClass('d-none');
        const $list = $('#vinSuggestList').empty();

        if (!results.length) {
          $list.html('<li class="list-group-item text-muted text-center py-2 small">ไม่พบข้อมูล</li>').removeClass('d-none');
          return;
        }

        results.forEach(function (r) {
          const encoded = encodeURIComponent(JSON.stringify(r));
          const li = $('<li>')
            .addClass('list-group-item list-group-item-action d-flex flex-column gap-0 py-2 px-3 vinSuggestItem')
            .css('cursor', 'pointer')
            .attr('data-encoded', encoded);
          li.append($('<div>').addClass('fw-semibold text-uppercase').text(r.vin));
          li.append($('<div>').addClass('text-muted small').html(
            (r.customer_name || '-') + ' &nbsp;|&nbsp; <span class="text-primary">' + r.model_name + '</span>'
          ));
          $list.append(li);
        });
        $list.removeClass('d-none');
      }).fail(function () {
        $('#vinSearchSpinner').addClass('d-none');
      });
    }, 300);
  });

  $(document).on('click', '.vinSuggestItem', function () {
    const r = JSON.parse(decodeURIComponent($(this).data('encoded')));
    $('#fu_vin').val(r.vin);
    $('#fu_car_order_id').val(r.car_order_id);
    $('#fu_salecar_id').val(r.salecar_id);
    $('#fu_model_id').val(r.model_id);
    $('#fu_customer_name_display, #fu_customer_name').val(r.customer_name);
    $('#fu_sale_person_display, #fu_sale_person').val(r.sale_person);
    $('#fu_model_display').val(r.model_name);
    $('#generalInfoFields').removeClass('d-none');
    $('#vinSuggestList').addClass('d-none').empty();
    showVinStatus('success', '<i class="bx bx-check-circle me-1"></i>พบข้อมูล: ' + r.model_name);
  });

  $('#fu_vin').on('focus', function () {
    if ($('#vinSuggestList li').length) $('#vinSuggestList').removeClass('d-none');
  });

  $(document).on('click', function (e) {
    if (!$(e.target).closest('#fu_vin, #vinSuggestList').length) {
      $('#vinSuggestList').addClass('d-none');
    }
  });

  function showVinStatus(type, html) {
    $('#vinSearchStatus')
      .removeClass('d-none text-success text-danger')
      .addClass('text-' + type)
      .html(html);
  }

  // ── ลูกค้าใหม่ button ─────────────────────────────────────
  $(document).on('click', '#btnNewCustomer', function () {
    fuNewCustomer = !fuNewCustomer;
    if (fuNewCustomer) {
      $('#generalVinSection').addClass('d-none');
      $('#vinSearchStatus').addClass('d-none');
      $('#vinSuggestList').addClass('d-none').empty();
      $('#generalInfoFields').addClass('d-none');
      $('#newCustomerFields').removeClass('d-none');
      $(this).html('<i class="bx bx-x me-1"></i> ยกเลิก')
        .removeClass('btn-outline-primary').addClass('btn-primary');
      $('#fu_vin, #fu_car_order_id, #fu_salecar_id, #fu_model_id').val('');
      $('#fu_customer_name, #fu_sale_person, #fu_customer_name_display, #fu_sale_person_display, #fu_model_display').val('');
    } else {
      $('#generalVinSection').removeClass('d-none');
      $('#newCustomerFields').addClass('d-none');
      $('#fu_vin_new, #fu_customer_name_new, #fu_sale_person_new').val('');
      $('#fu_model_id_new').val('');
      resetNewCustomerBtn();
    }
  });

  // ── General: Package selection → generate rows ─────────────
  $('input[name="package"]').on('change', function () {
    const pkg = $(this).val();
    clearRows();

    $('#sunroofToggleRow').addClass('d-none');
    $('#addSunroof').prop('checked', false);

    let positions = [];
    if (pkg === 'full')        positions = ['รอบคัน+บานหน้า'];
    if (pkg === 'front_body')  positions = ['บานหน้า', 'รอบคัน'];
    if (pkg === 'advanced')    positions = ['บานหน้า', 'บานหลัง', 'กระจกประตูคู่หน้า', 'กระจกประตูคู่หลัง 1', 'กระจกประตูคู่หลัง 2', 'กระจกหูช้าง'];

    positions.forEach(pos => addRow(pos));

    if (pkg === 'full') autoFillFromPriceList(false);
    if (pkg === 'full' || pkg === 'front_body' || pkg === 'advanced') checkSunroofForCurrentModel();
  });

  $('#addSunroof').on('change', function () {
    if ($(this).is(':checked')) {
      addRow('ซันรูฟ');
      autoFillFromPriceList(true);
    } else {
      removeRowByPosition('ซันรูฟ');
    }
  });

  // ── BP: Checkbox → generate rows + auto-fill price ───────
  $(document).on('change', '.bpPosCheck', function () {
    const pos = $(this).val();
    if ($(this).is(':checked')) {
      addRow(pos);
      const $row = $(`#positionRows tr[data-position="${pos}"]`);
      if (BP_PRICES[pos]?.multiPane) {
        $row.find('td:first').append(
          '<div class="btn-group btn-group-sm mt-1">' +
            '<button type="button" class="btn btn-outline-secondary active bpPaneBtn" data-position="' + pos + '" data-panes="1">1 บาน</button>' +
            '<button type="button" class="btn btn-outline-secondary bpPaneBtn" data-position="' + pos + '" data-panes="2">2 บาน</button>' +
          '</div>'
        );
      }
      fillBpRowPrice($row, pos);
    } else {
      removeRowByPosition(pos);
    }
  });

  // ── BP: Pane toggle ────────────────────────────────────────
  $(document).on('click', '.bpPaneBtn', function () {
    $(this).closest('.btn-group').find('.bpPaneBtn').removeClass('active');
    $(this).addClass('active');
    const pos  = $(this).data('position');
    fillBpRowPrice($(`#positionRows tr[data-position="${pos}"]`), pos);
  });

  // ── Row management ─────────────────────────────────────────
  function addRow(position) {
    $('#noRowMsg').remove();
    const idx = Date.now() + Math.random();
    const shadeOpts = SHADES.map(s => `<option value="${s}">${s}</option>`).join('');

    const row = `
      <tr data-position="${position}" data-idx="${idx}">
        <td class="align-middle fw-bold small">${position}</td>
        <td>
          <select class="form-select form-select-sm rowShade" data-idx="${idx}" name="items[${idx}][shade]">
            <option value="">—</option>
            ${shadeOpts}
          </select>
        </td>
        <td>
          <div class="input-group input-group-sm">
            <select class="form-select form-select-sm rowStock" data-idx="${idx}" name="items[${idx}][film_stock_id]">
              <option value="">— เลือก Shade ก่อน —</option>
            </select>
            <input type="hidden" name="items[${idx}][stock_no]" class="rowStockNo" data-idx="${idx}">
            <input type="hidden" name="items[${idx}][position]" value="${position}">
          </div>
        </td>
        <td>
          <input type="number" class="form-control form-control-sm text-end rowSqft"
            name="items[${idx}][sqft_used]" data-idx="${idx}" step="0.01" min="0" placeholder="0.00">
        </td>
        <td>
          <input type="text" class="form-control form-control-sm text-end money-input rowPrice"
            name="items[${idx}][price]" data-idx="${idx}" placeholder="0.00" autocomplete="off">
        </td>
        <td>
          <input type="text" class="form-control form-control-sm text-end money-input rowCommission"
            name="items[${idx}][commission]" data-idx="${idx}" placeholder="0.00" autocomplete="off">
        </td>
      </tr>`;
    $('#positionRows').append(row);
  }

  function removeRowByPosition(position) {
    $(`#positionRows tr[data-position="${position}"]`).remove();
    if ($('#positionRows tr').length === 0) addNoRowMsg();
    recalcTotals();
  }

  function clearRows() {
    $('#positionRows').empty();
    addNoRowMsg();
    recalcTotals();
  }

  function addNoRowMsg() {
    $('#positionRows').html(`
      <tr id="noRowMsg">
        <td colspan="6" class="text-center text-muted py-3">
          <i class="bx bx-info-circle me-1"></i> เลือกตำแหน่งก่อน
        </td>
      </tr>`);
  }

  // ── Film brand change → reload all rows + recheck sunroof ────────
  $(document).on('change', '#fu_film_brand_id', function () {
    const currentType = $('input[name="type"]:checked').val();
    if (currentType === 'bp') {
      $('#positionRows tr[data-position]').each(function () {
        fillBpRowPrice($(this), $(this).data('position'));
      });
      return;
    }
    $('.rowShade').each(function () {
      if ($(this).val()) $(this).trigger('change');
    });
    const pkg = $('input[name="package"]:checked').val();
    if (pkg === 'full' || pkg === 'front_body' || pkg === 'advanced') {
      $('#sunroofToggleRow').addClass('d-none');
      $('#addSunroof').prop('checked', false);
      removeRowByPosition('ซันรูฟ');
      checkSunroofForCurrentModel();
    }
  });

  // ── ลูกค้าใหม่: model change → recheck sunroof ────────────────
  $(document).on('change', '#fu_model_id_new', function () {
    const pkg = $('input[name="package"]:checked').val();
    if (pkg === 'full' || pkg === 'front_body' || pkg === 'advanced') {
      $('#sunroofToggleRow').addClass('d-none');
      $('#addSunroof').prop('checked', false);
      removeRowByPosition('ซันรูฟ');
      checkSunroofForCurrentModel();
    }
  });

  function checkSunroofForCurrentModel() {
    const modelId     = fuNewCustomer ? $('#fu_model_id_new').val() : $('#fu_model_id').val();
    const filmBrandId = $('#fu_film_brand_id').val();
    if (!modelId || !filmBrandId) return;

    $.get('/film-usage/price-list-lookup', { model_id: modelId, film_brand_id: filmBrandId }, function (res) {
      $('#sunroofToggleRow').toggleClass('d-none', !(res.found && res.has_sunroof));
    });
  }

  // ── Shade change → load stocks ────────────────────────────
  $(document).on('change', '.rowShade', function () {
    const idx        = $(this).data('idx');
    const shade      = $(this).val();
    const filmBrand  = $('#fu_film_brand_id').val();
    const $stockSel  = $(`.rowStock[data-idx="${idx}"]`);
    const $stockNo   = $(`.rowStockNo[data-idx="${idx}"]`);

    $stockSel.html('<option value="">กำลังโหลด...</option>');
    $stockNo.val('');

    if (!shade || !filmBrand) {
      $stockSel.html('<option value="">— เลือก Shade ก่อน —</option>');
      return;
    }

    $.get('/film-usage/stock-search', { film_brand_id: filmBrand, shade }, function (stocks) {
      if (!stocks.length) {
        $stockSel.html('<option value="">ไม่มีสต็อก</option>');
        return;
      }
      const opts = stocks.map(s => {
        const remaining = (parseFloat(s.initial_qty) - parseFloat(s.used_qty)).toFixed(2);
        return `<option value="${s.id}" data-stock-no="${s.stock_no}">${s.stock_no} (คงเหลือ ${remaining} ตร.ฟุต)</option>`;
      }).join('');
      $stockSel.html('<option value="">— เลือก Stock —</option>' + opts);
    }).fail(function () {
      $stockSel.html('<option value="">เกิดข้อผิดพลาด</option>');
    });
  });

  // ── Stock select → store stock_no ─────────────────────────
  $(document).on('change', '.rowStock', function () {
    const idx     = $(this).data('idx');
    const stockNo = $(this).find(':selected').data('stock-no') || '';
    $(`.rowStockNo[data-idx="${idx}"]`).val(stockNo);
  });

  // ── Auto-fill from price list ─────────────────────────────
  function autoFillFromPriceList(sunroofOnly) {
    const modelId     = $('#fu_model_id').val();
    const filmBrandId = $('#fu_film_brand_id').val();
    if (!modelId || !filmBrandId) return;

    $.get('/film-usage/price-list-lookup', { model_id: modelId, film_brand_id: filmBrandId }, function (res) {
      if (!res.found) return;

      if (!sunroofOnly) {
        const $mainRow = $('#positionRows tr[data-position="รอบคัน+บานหน้า"]');
        if ($mainRow.length) {
          $mainRow.find('.rowSqft').val(res.sqft);
          $mainRow.find('.rowPrice').val(formatMoney(res.price));
          $mainRow.find('.rowCommission').val(formatMoney(res.commission));
        }
      }

      if (sunroofOnly && res.has_sunroof) {
        const $sunRow = $('#positionRows tr[data-position="ซันรูฟ"]');
        if ($sunRow.length) {
          $sunRow.find('.rowSqft').val(res.sqft_sunroof);
          $sunRow.find('.rowPrice').val(formatMoney(res.price_sunroof));
          $sunRow.find('.rowCommission').val(formatMoney(res.commission_sunroof));
        }
      }
      recalcTotals();
    });
  }

  // ── Totals ─────────────────────────────────────────────────
  $(document).on('input change', '.rowSqft, .rowPrice, .rowCommission', recalcTotals);

  function recalcTotals() {
    let totalSqft = 0, totalPrice = 0, totalCom = 0;
    $('#positionRows tr[data-position]').each(function () {
      totalSqft  += parseFloat($(this).find('.rowSqft').val()) || 0;
      totalPrice += parseMoney($(this).find('.rowPrice').val());
      totalCom   += parseMoney($(this).find('.rowCommission').val());
    });
    $('#totalSqft').text(totalSqft > 0 ? totalSqft.toFixed(2) : '-');
    $('#totalPrice').text(totalPrice > 0 ? formatMoney(totalPrice) : '-');
    $('#totalCommission').text(totalCom > 0 ? formatMoney(totalCom) : '-');
  }

  // ── Money format helpers ───────────────────────────────────
  $(document).on('input', '.money-input', function () {
    let v = this.value.replace(/,/g, '');
    if (v === '' || isNaN(v)) return;
    const parts = v.split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    this.value = parts.join('.');
  });

  $(document).on('blur', '.money-input', function () {
    const v = parseMoney(this.value);
    if (v > 0) this.value = formatMoney(v);
    recalcTotals();
  });

  function parseMoney(val) {
    return parseFloat(String(val || '').replace(/,/g, '')) || 0;
  }

  function formatMoney(val) {
    return parseFloat(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  // ── Save ───────────────────────────────────────────────────
  $(document).on('click', '.btnSaveFilmUsage', function () {
    const type    = $('input[name="type"]:checked').val();
    const date    = $('#fu_order_date').val();
    const filmBrand = $('#fu_film_brand_id').val();

    if (!date) { Swal.fire({ icon: 'warning', text: 'กรุณาระบุวันที่สั่งงาน' }); return; }
    if (!filmBrand) { Swal.fire({ icon: 'warning', text: 'กรุณาเลือกยี่ห้อฟิล์ม' }); return; }

    const rows = [];
    $('#positionRows tr[data-position]').each(function () {
      rows.push({
        position:      $(this).find('input[name*="[position]"]').val(),
        shade:         $(this).find('.rowShade').val(),
        film_stock_id: $(this).find('.rowStock').val(),
        stock_no:      $(this).find('.rowStockNo').val(),
        sqft_used:     $(this).find('.rowSqft').val(),
        price:         $(this).find('.rowPrice').val(),
        commission:    $(this).find('.rowCommission').val(),
      });
    });

    if (!rows.length) { Swal.fire({ icon: 'warning', text: 'กรุณาเลือกตำแหน่งอย่างน้อย 1 รายการ' }); return; }

    // Build payload
    const payload = {
      _token:        $('meta[name="csrf-token"]').attr('content'),
      type,
      order_date:    date,
      film_brand_id: filmBrand,
      items:         rows,
    };

    if (type === 'general') {
      if (fuNewCustomer) {
        const newVin      = $('#fu_vin_new').val().trim().toUpperCase();
        const newCustName = $('#fu_customer_name_new').val().trim();
        if (!newVin) {
          Swal.fire({ icon: 'warning', text: 'กรุณาระบุเลข VIN' });
          return;
        }
        if (!newCustName) {
          Swal.fire({ icon: 'warning', text: 'กรุณาระบุชื่อ-สกุลลูกค้า' });
          return;
        }
        payload.vin           = newVin;
        payload.customer_name = newCustName;
        payload.sale_person   = $('#fu_sale_person_new').val().trim();
        payload.model_id      = $('#fu_model_id_new').val();
      } else {
        if (!$('#fu_vin').val() || !$('#fu_car_order_id').val()) {
          Swal.fire({ icon: 'warning', text: 'กรุณาค้นหาและเลือกเลข VIN ก่อนบันทึก' });
          return;
        }
        payload.vin           = $('#fu_vin').val();
        payload.car_order_id  = $('#fu_car_order_id').val();
        payload.salecar_id    = $('#fu_salecar_id').val();
        payload.model_id      = $('#fu_model_id').val();
        payload.customer_name = $('#fu_customer_name').val();
        payload.sale_person   = $('#fu_sale_person').val();
      }
    } else {
      payload.vin           = $('input[name="vin_bp"]').val();
      payload.model_id      = $('#fu_model_id_bp').val();
      payload.customer_name = $('#fu_customer_name_bp').val();
      payload.sale_person   = $('#fu_sale_person_bp').val();
    }

    const $btn = $(this);
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>กำลังบันทึก...');

    $.ajax({
      url: '/film-usage',
      type: 'POST',
      data: JSON.stringify(payload),
      contentType: 'application/json',
      success: function (res) {
        if (res.success) {
          Swal.fire({ icon: 'success', title: 'บันทึกสำเร็จ', timer: 1500, showConfirmButton: false })
            .then(() => { window.location.href = '/film-usage'; });
        } else {
          Swal.fire({ icon: 'warning', text: res.message });
        }
      },
      error: function (xhr) {
        Swal.fire({ icon: 'error', text: xhr.responseJSON?.message || 'เกิดข้อผิดพลาด' });
      },
      complete: function () {
        $btn.prop('disabled', false).html('<i class="bx bx-save me-1"></i> บันทึก');
      }
    });
  });

  // Init: show BP info section on load
  $('input[name="type"][value="general"]').trigger('change');
}
