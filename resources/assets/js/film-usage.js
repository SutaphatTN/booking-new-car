$.ajaxSetup({
  headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

// ── DataTable (list page) ──────────────────────────────────
if ($('.filmUsageTable').length) {
  $(document).ready(function () {
    const table = $('.filmUsageTable').DataTable({
      ajax: {
        url: '/film-usage/list',
        data: function (d) {
          d.month = $('#filmUsageMonth').val();
        }
      },
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
        paginate: { next: 'ถัดไป', previous: 'ก่อนหน้า' }
      }
    });

    // คุม loader overlay เอง (ทั้งตอนโหลดครั้งแรกและตอนเปลี่ยนเดือน)
    table.on('preXhr.dt', function () {
      $('#filmUsageLoadingOverlay').css('display', 'flex');
    });
    table.on('xhr.dt', function () {
      $('#filmUsageLoadingOverlay').css('display', 'none');
    });

    // เปลี่ยนเดือน → โหลดข้อมูลใหม่ตามวันที่สั่งงาน
    $('#filmUsageMonth').on('change', function () {
      table.ajax.reload();
    });

    // ปุ่มรายงาน → ดาวน์โหลด Excel ประวัติการใช้งานตามเดือนที่เลือก
    $('#btnFilmUsageReport').on('click', function () {
      const month = $('#filmUsageMonth').val();
      window.location.href = '/film-usage/report-export?month=' + encodeURIComponent(month || '');
    });
  });

  // ── ดูข้อมูล (view-more modal) ──────────────────────────────
  $(document).on('click', '.btnViewFilmUsage', function () {
    const id = $(this).data('id');
    $.get('/film-usage/' + id + '/view-more', function (html) {
      $('.viewMoreFilmUsageModal').html(html);
      $('.viewFilmUsage').modal('show');
    }).fail(function () {
      Swal.fire({ icon: 'error', text: 'โหลดข้อมูลไม่สำเร็จ' });
    });
  });

  $(document).on('hide.bs.modal', '.viewFilmUsage', function () {
    setTimeout(() => { document.activeElement.blur(); $('body').trigger('focus'); }, 1);
  });

  $(document).on('click', '.btnDeleteFilmUsage', function () {
    const id = $(this).data('id');
    Swal.fire({
      title: 'คุณแน่ใจหรือไม่?',
      text: 'คุณต้องการลบข้อมูลนี้ใช่หรือไม่?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#6c5ffc',
      cancelButtonColor: '#d33',
      confirmButtonText: 'ใช่, ลบเลย!',
      cancelButtonText: 'ยกเลิก'
    }).then(function (result) {
      if (!result.isConfirmed) return;
      $.ajax({
        url: '/film-usage/' + id,
        type: 'POST',
        data: { _method: 'DELETE' },
        success: function (res) {
          if (res.success) {
            Swal.fire({ icon: 'success', title: 'ลบสำเร็จ', timer: 1500, showConfirmButton: true });
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

  // ตำแหน่ง → ฟิลด์ ตร.ฟุต ในหน้าราคาฟิล์ม
  const POSITION_SQFT_FIELD = {
    บานหน้า: 'sqft_windshield',
    รอบคัน: 'sqft_around',
    บานหลัง: 'sqft_rear',
    กระจกประตูคู่หน้า: 'sqft_door_front',
    'กระจกประตูคู่หลัง 1': 'sqft_door_rear1',
    'กระจกประตูคู่หลัง 2': 'sqft_door_rear2',
    กระจกหูช้าง: 'sqft_quarter',
    'แพ็กเกจ 3 บาน': 'sqft_3window',
    ซันรูฟ: 'sqft_sunroof'
  };

  const BP_PRICES = {
    บานหน้า: { sqft: 16, price: 3000, multiPane: false },
    บานหลัง: { sqft: 9, price: 3000, multiPane: false },
    กระจกประตูคู่หน้า: { sqft: 6, price: 1200, multiPane: true },
    'กระจกประตูคู่หลัง 1': { sqft: 6, price: 1200, multiPane: true },
    'กระจกประตูคู่หลัง 2': { sqft: 6, price: 1200, multiPane: true },
    กระจกหูช้าง: { sqft: 3, price: 1000, multiPane: true },
    ซันรูฟ: { sqft: 24, price: 3000, multiPane: false }
  };

  function getBpMultiplier() {
    const code = $('#fu_film_brand_id option:selected').data('code') || 'MX';
    return { MX: 1.0, CB: 1.8, BY: 2.0 }[code] || 1.0;
  }

  function fillBpRowPrice($row, position) {
    const config = BP_PRICES[position];
    if (!config) return;
    const mult = getBpMultiplier();
    const panes = parseInt($row.find('.bpPaneBtn.active').data('panes') || 1);
    $row.find('.rowSqft').val((config.sqft * panes).toFixed(2));
    $row.find('.rowPrice').val(formatMoney(config.price * mult * panes));
    rebuildAllocations($row);
    recalcTotals();
  }

  let fuNewCustomer = false;

  // ราคารวม/ค่าคอมรวมของแพ็กเกจฐาน (front_body/advanced) ที่ไม่มีราคารายตำแหน่ง
  let fpPackagePrice = null,
    fpPackageCommission = null;

  function resetNewCustomerBtn() {
    $('#btnNewCustomer')
      .html('<i class="bx bx-user-plus me-1"></i> ลูกค้าใหม่')
      .removeClass('btn-danger')
      .addClass('btn-outline-primary');
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
    resetExtraToggles();
    $('input[name="package"]').prop('checked', false);
    $('#pkgSunroofWrap, #pkg3windowWrap').addClass('d-none');
    $('.bpPosCheck').prop('checked', false);
    // รีเซ็ตแหล่งที่มาลูกค้า/ประกัน เมื่อสลับประเภท
    $('#fu_source_bp').val('self');
    $('#bpInsuranceWrap').addClass('d-none');
    $('#fu_insurance_bp').val('');
    $('#vinSearchStatus').addClass('d-none');
    $('#vinSuggestList').addClass('d-none').empty();
    if (fuNewCustomer) {
      fuNewCustomer = false;
      resetNewCustomerBtn();
      if (isGeneral) $('#generalVinSection').removeClass('d-none');
    }
  });

  // ── BP: แหล่งที่มาลูกค้า → แสดง/ซ่อน ประกัน ───────────────────
  $(document).on('change', '[name="customer_source"]', function () {
    const isInsurance = $(this).val() === 'insurance';
    $('#bpInsuranceWrap').toggleClass('d-none', !isInsurance);
    if (!isInsurance) $('#fu_insurance_bp').val('');
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
          $list
            .html('<li class="list-group-item text-muted text-center py-2 small">ไม่พบข้อมูล</li>')
            .removeClass('d-none');
          return;
        }

        results.forEach(function (r) {
          const encoded = encodeURIComponent(JSON.stringify(r));
          const li = $('<li>')
            .addClass('list-group-item list-group-item-action d-flex flex-column gap-0 py-2 px-3 vinSuggestItem')
            .css('cursor', 'pointer')
            .attr('data-encoded', encoded);
          li.append($('<div>').addClass('fw-semibold text-uppercase').text(r.vin));
          li.append(
            $('<div>')
              .addClass('text-muted small')
              .html((r.customer_name || '-') + ' &nbsp;|&nbsp; <span class="text-primary">' + r.model_name + '</span>')
          );
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
    $('#fu_customer_name').val(r.customer_name);
    $('#fu_sale_person').val(r.sale_person);
    $('#fu_customer_name_display').text(r.customer_name || '—');
    $('#fu_sale_person_display').text(r.sale_person || '—');
    $('#fu_model_display').text(r.model_name || '—');
    $('#generalInfoFields').removeClass('d-none');
    $('#vinSuggestList').addClass('d-none').empty();
    showVinStatus('success', '<i class="bx bx-check-circle me-1"></i>พบข้อมูล: ' + r.model_name);
    refreshStandalonePackages();
    refreshCurrentPackageRows();
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
      $(this).html('<i class="bx bx-x me-1"></i> ยกเลิก').removeClass('btn-outline-primary').addClass('btn-danger');
      $('#fu_vin, #fu_car_order_id, #fu_salecar_id, #fu_model_id').val('');
      $('#fu_customer_name, #fu_sale_person').val('');
      $('#fu_customer_name_display, #fu_sale_person_display, #fu_model_display').text('—');
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

    resetExtraToggles();

    let positions = [];
    if (pkg === 'full') positions = ['รอบคัน+บานหน้า'];
    if (pkg === 'front_body') positions = ['บานหน้า', 'รอบคัน'];
    if (pkg === 'advanced')
      positions = ['บานหน้า', 'บานหลัง', 'กระจกประตูคู่หน้า', 'กระจกประตูคู่หลัง 1', 'กระจกหูช้าง'];

    positions.forEach(pos => addRow(pos));

    if (pkg === 'full') autoFillFromPriceList(false);
    if (pkg === 'front_body' || pkg === 'advanced') autoFillPositionSqft();
    if (pkg === 'full' || pkg === 'front_body' || pkg === 'advanced') checkSunroofForCurrentModel();
    if (pkg === 'advanced') checkAdvancedExtrasForCurrentModel();

    // แพ็กเกจเดี่ยว (ติดเฉพาะตำแหน่งนั้น) — มีราคาของตัวเองในหน้าราคาฟิล์ม
    if (pkg === 'sunroof') {
      addRow('ซันรูฟ');
      fillAddonRow('ซันรูฟ', 'sqft_sunroof', 'price_sunroof', 'commission_sunroof');
    }
    if (pkg === 'window3') {
      addRow('แพ็กเกจ 3 บาน');
      fillAddonRow('แพ็กเกจ 3 บาน', 'sqft_3window', 'price_3window', 'commission_3window');
      checkSunroofForCurrentModel(); // ให้เพิ่มซันรูฟได้ ถ้ามีข้อมูล
    }
  });

  $('#addSunroof').on('change', function () {
    if ($(this).is(':checked')) {
      addRow('ซันรูฟ');
      autoFillFromPriceList(true);
    } else {
      removeRowByPosition('ซันรูฟ');
    }
  });

  $('#addDoorRear2').on('change', function () {
    if ($(this).is(':checked')) {
      addRow('กระจกประตูคู่หลัง 2');
      autoFillPositionSqft('กระจกประตูคู่หลัง 2');
    } else {
      removeRowByPosition('กระจกประตูคู่หลัง 2');
    }
  });

  $('#add3window').on('change', function () {
    if ($(this).is(':checked')) {
      addRow('แพ็กเกจ 3 บาน');
      fillAddonRow('แพ็กเกจ 3 บาน', 'sqft_3window', 'price_3window', 'commission_3window');
    } else {
      removeRowByPosition('แพ็กเกจ 3 บาน');
    }
  });

  // เติม ตร.ฟุต + ราคา + ค่าคอม ของตำแหน่งเสริมที่มีราคาแยก (เช่น 3 บาน)
  function fillAddonRow(position, sqftField, priceField, comField) {
    const modelId = fuNewCustomer ? $('#fu_model_id_new').val() : $('#fu_model_id').val();
    const filmBrandId = $('#fu_film_brand_id').val();
    if (!modelId || !filmBrandId) return;

    $.get('/film-usage/price-list-lookup', { model_id: modelId, film_brand_id: filmBrandId }, function (res) {
      if (!res.found) return;
      const $row = $(`#positionRows tr[data-position="${position}"]`);
      if (!$row.length) return;
      if (res[sqftField] != null && res[sqftField] !== '')
        $row.find('.rowSqft').val(parseFloat(res[sqftField]).toFixed(2));
      if (res[priceField] != null && res[priceField] !== '') $row.find('.rowPrice').val(formatMoney(res[priceField]));
      if (res[comField] != null && res[comField] !== '') $row.find('.rowCommission').val(formatMoney(res[comField]));
      recalcTotals();
    });
  }

  // ── ซ่อน/รีเซ็ตปุ่มเพิ่มตำแหน่งเสริม ──────────────────────
  function resetExtraToggles() {
    $('#sunroofToggleRow').addClass('d-none');
    $('#doorRear2ToggleRow').addClass('d-none');
    $('#window3ToggleRow').addClass('d-none');
    $('#addSunroof, #addDoorRear2, #add3window').prop('checked', false);
  }

  // ── แสดงปุ่มประตูคู่หลัง 2 / 3 บาน ถ้ามีข้อมูล (ขั้นสูง) ────
  function checkAdvancedExtrasForCurrentModel() {
    const modelId = fuNewCustomer ? $('#fu_model_id_new').val() : $('#fu_model_id').val();
    const filmBrandId = $('#fu_film_brand_id').val();
    if (!modelId || !filmBrandId) return;

    $.get('/film-usage/price-list-lookup', { model_id: modelId, film_brand_id: filmBrandId }, function (res) {
      // กระจกประตูคู่หลัง 2: ถ้ามีข้อมูล เพิ่มลงรายละเอียดอัตโนมัติ (ไม่ต้องกดปุ่ม)
      // หมายเหตุ: ปุ่ม toggle #doorRear2ToggleRow + handler ยังเก็บไว้ เผื่อใช้ภายหลัง
      if (res.found && res.has_door_rear2 && !$('#positionRows tr[data-position="กระจกประตูคู่หลัง 2"]').length) {
        addRow('กระจกประตูคู่หลัง 2');
        const $r2 = $('#positionRows tr[data-position="กระจกประตูคู่หลัง 2"]');
        const $r1 = $('#positionRows tr[data-position="กระจกประตูคู่หลัง 1"]');
        if ($r1.length) $r1.after($r2); // วางต่อจากประตูคู่หลัง 1
        if (res.sqft_door_rear2 != null && res.sqft_door_rear2 !== '') {
          $r2.find('.rowSqft').val(parseFloat(res.sqft_door_rear2).toFixed(2));
        }
        recalcTotals();
      }
      $('#doorRear2ToggleRow').addClass('d-none'); // เพิ่มอัตโนมัติแล้ว ไม่ต้องโชว์ปุ่ม

      // แพ็กเกจ 3 บาน: ไม่ต้องโชว์ปุ่มในขั้นสูง (มีเป็นแพ็กเกจเดี่ยวแล้ว) — เก็บ handler ไว้เผื่อใช้
      $('#window3ToggleRow').addClass('d-none');
    });
  }

  // ── แสดงแพ็กเกจเดี่ยว ซันรูฟ / 3 บาน ถ้ามีข้อมูลในหน้าราคาฟิล์ม ──
  function refreshStandalonePackages() {
    const modelId = fuNewCustomer ? $('#fu_model_id_new').val() : $('#fu_model_id').val();
    const filmBrandId = $('#fu_film_brand_id').val();
    if (!modelId || !filmBrandId) {
      $('#pkgSunroofWrap, #pkg3windowWrap').addClass('d-none');
      return;
    }

    $.get('/film-usage/price-list-lookup', { model_id: modelId, film_brand_id: filmBrandId }, function (res) {
      const showSun = !!(res.found && res.has_sunroof);
      const show3 = !!(res.found && res.has_3window);
      $('#pkgSunroofWrap').toggleClass('d-none', !showSun);
      $('#pkg3windowWrap').toggleClass('d-none', !show3);

      // ถ้าแพ็กเกจเดี่ยวที่เลือกอยู่ไม่มีข้อมูลแล้ว ให้ยกเลิกการเลือก
      const cur = $('input[name="package"]:checked').val();
      if ((cur === 'sunroof' && !showSun) || (cur === 'window3' && !show3)) {
        $('input[name="package"]').prop('checked', false);
        clearRows();
        resetExtraToggles();
      }
    });
  }

  // ── BP: Checkbox → generate rows + auto-fill price ───────
  $(document).on('change', '.bpPosCheck', function () {
    const pos = $(this).val();
    if ($(this).is(':checked')) {
      addRow(pos);
      const $row = $(`#positionRows tr[data-position="${pos}"]`);
      if (BP_PRICES[pos]?.multiPane) {
        $row
          .find('td:first')
          .append(
            '<div class="btn-group btn-group-sm fu-pane-group mt-1">' +
              '<button type="button" class="btn fu-pane-btn active bpPaneBtn" data-position="' +
              pos +
              '" data-panes="1"><i class="bx bx-square me-1"></i>1 บาน</button>' +
              '<button type="button" class="btn fu-pane-btn bpPaneBtn" data-position="' +
              pos +
              '" data-panes="2"><i class="bx bx-grid-small me-1"></i>2 บาน</button>' +
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
    const $row = $(this).closest('tr');
    fillBpRowPrice($row, $row.data('position'));
  });

  // ── Stock allocation (ตัดจากหลายม้วนถ้าคงเหลือไม่พอ) ──────────
  // ช่องเลือก stock 1 แถว
  function allocRowHtml(placeholder) {
    return `
      <div class="input-group input-group-sm stockAllocRow mb-1">
        <select class="form-select form-select-sm rowStock"><option value="">${placeholder || '— เลือก Stock —'}</option></select>
        <span class="input-group-text stockPortion px-2 d-none" title="ตัดจากม้วนนี้ (ตร.ฟุต)"></span>
      </div>`;
  }

  // โครงช่อง Stock No. (ใช้ทั้ง ทั่วไป และ BP)
  function stockCellHtml() {
    return `
      <div class="stockAllocWrap">${allocRowHtml('— เลือก Shade ก่อน —')}</div>
      <div class="stockShortWarn small text-danger mt-1 d-none">
        <i class="bx bx-error-circle"></i> สต็อกไม่พอ ขาดอีก <span class="shortAmt"></span> ตร.ฟุต
      </div>`;
  }

  function stockRemaining(s) {
    return parseFloat(s.initial_qty) - parseFloat(s.used_qty);
  }

  function buildStockOptions(stocks, excludeIds) {
    if (!stocks || !stocks.length) return '<option value="">ไม่มีสต็อก</option>';
    let opts = '<option value="">— เลือก Stock —</option>';
    stocks.forEach(function (s) {
      const remaining = stockRemaining(s);
      const dis = excludeIds.indexOf(String(s.id)) !== -1 ? 'disabled' : '';
      opts += `<option value="${s.id}" data-stock-no="${s.stock_no}" data-remaining="${remaining.toFixed(2)}" ${dis}>${s.stock_no} (คงเหลือ ${remaining.toFixed(2)} ตร.ฟุต)</option>`;
    });
    return opts;
  }

  function selectedAllocIds($tr) {
    const ids = [];
    $tr.find('.stockAllocRow .rowStock').each(function () {
      const v = $(this).val();
      if (v) ids.push(v);
    });
    return ids;
  }

  // คำนวณการตัดจากแต่ละม้วน (ม้วนแรกใช้คงเหลือทั้งหมด ที่เหลือไปม้วนถัดไป) แล้ววาดช่องเลือกใหม่
  function rebuildAllocations($tr) {
    const stocks = $tr.data('stocks');
    if (stocks === undefined) return; // ยังไม่ได้เลือก shade / โหลด stock
    const needed = parseFloat($tr.find('.rowSqft').val()) || 0;
    const $wrap = $tr.find('.stockAllocWrap');
    const selectedIds = selectedAllocIds($tr);

    let covered = 0;
    const allocs = selectedIds.map(function (id) {
      const s = stocks.find(function (x) {
        return String(x.id) === String(id);
      });
      const remaining = s ? stockRemaining(s) : 0;
      const leftover = Math.max(0, needed - covered);
      const portion = needed > 0 ? Math.min(remaining, leftover) : 0;
      covered += portion;
      return { id: id, portion: portion };
    });

    const moreAvail = stocks.some(function (s) {
      return selectedIds.indexOf(String(s.id)) === -1 && stockRemaining(s) > 0;
    });
    const needMore = needed > 0 && covered < needed - 0.001 && moreAvail;

    $wrap.empty();
    allocs.forEach(function (a) {
      const $row = $(allocRowHtml());
      const others = selectedIds.filter(function (id) {
        return id !== a.id;
      });
      $row.find('.rowStock').html(buildStockOptions(stocks, others)).val(a.id);
      $row
        .find('.stockPortion')
        .attr('data-portion', a.portion.toFixed(2))
        .removeClass('d-none')
        .text(a.portion.toFixed(2));
      $wrap.append($row);
    });
    // ช่องว่างให้เลือกม้วนถัดไป (ยังไม่ครบ) หรือช่องแรก (ยังไม่ได้เลือกอะไร)
    if (needMore || allocs.length === 0) {
      const $row = $(allocRowHtml());
      $row.find('.rowStock').html(buildStockOptions(stocks, selectedIds));
      $wrap.append($row);
    }

    const short = needed - covered;
    const $warn = $tr.find('.stockShortWarn');
    if (needed > 0 && short > 0.001 && !moreAvail && allocs.length > 0) {
      $warn.removeClass('d-none').find('.shortAmt').text(short.toFixed(2));
    } else {
      $warn.addClass('d-none');
    }
  }

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
        <td>${stockCellHtml()}</td>
        <td>
          <input type="number" class="form-control form-control-sm text-end rowSqft"
            data-idx="${idx}" step="0.01" min="0" placeholder="0.00">
        </td>
        <td>
          <input type="text" class="form-control form-control-sm text-end money-input rowPrice"
            data-idx="${idx}" placeholder="0.00" autocomplete="off">
        </td>
        <td>
          <input type="text" class="form-control form-control-sm text-end money-input rowCommission"
            data-idx="${idx}" placeholder="0.00" autocomplete="off">
        </td>
      </tr>`;
    $('#positionRows').append(row);
  }

  function removeRowByPosition(position) {
    $(`#positionRows tr[data-position="${position}"]`).remove();
    if ($('#positionRows tr').length === 0) addNoRowMsg();
    recalcTotals();
  }

  // ── BP: เพิ่มแถวว่าง พร้อม dropdown เลือกตำแหน่ง + ปุ่มลบ ──────
  function addBpRow() {
    $('#noRowMsg').remove();
    const idx = Date.now() + Math.random();
    const shadeOpts = SHADES.map(s => `<option value="${s}">${s}</option>`).join('');
    const posOpts = Object.keys(BP_PRICES)
      .map(p => `<option value="${p}">${p}</option>`)
      .join('');

    const row = `
      <tr data-position="" data-idx="${idx}">
        <td>
          <div class="d-flex gap-1">
            <select class="form-select form-select-sm bpPosSelect" data-idx="${idx}">
              <option value="">— เลือกตำแหน่ง —</option>
              ${posOpts}
            </select>
            <button type="button" class="btn btn-sm btn-outline-danger bpRemoveRow" title="ลบ">
              <i class="bx bx-trash"></i>
            </button>
          </div>
          <input type="hidden" name="items[${idx}][position]" class="bpPosHidden">
          <div class="bpPaneContainer"></div>
        </td>
        <td>
          <select class="form-select form-select-sm rowShade" data-idx="${idx}" name="items[${idx}][shade]">
            <option value="">—</option>
            ${shadeOpts}
          </select>
        </td>
        <td>${stockCellHtml()}</td>
        <td>
          <input type="number" class="form-control form-control-sm text-end rowSqft"
            data-idx="${idx}" step="0.01" min="0" placeholder="0.00">
        </td>
        <td>
          <input type="text" class="form-control form-control-sm text-end money-input rowPrice"
            data-idx="${idx}" placeholder="0.00" autocomplete="off">
        </td>
        <td>
          <input type="text" class="form-control form-control-sm text-end money-input rowCommission"
            data-idx="${idx}" placeholder="0.00" autocomplete="off">
        </td>
      </tr>`;
    $('#positionRows').append(row);
  }

  // ── BP: ปุ่มเพิ่มแพ็กเกจ ────────────────────────────────────
  $(document).on('click', '#btnAddBpRow', function () {
    addBpRow();
  });

  // ── BP: เลือกตำแหน่งในแถว → เติมราคา/ตร.ฟุต + ปุ่ม 1/2 บาน ───
  $(document).on('change', '.bpPosSelect', function () {
    const $row = $(this).closest('tr');
    const pos = $(this).val();
    $row.attr('data-position', pos);
    $row.find('.bpPosHidden').val(pos);

    const $pane = $row.find('.bpPaneContainer').empty();
    if (pos && BP_PRICES[pos] && BP_PRICES[pos].multiPane) {
      $pane.html(
        '<div class="btn-group btn-group-sm fu-pane-group mt-1">' +
          '<button type="button" class="btn fu-pane-btn active bpPaneBtn" data-panes="1"><i class="bx bx-square me-1"></i>1 บาน</button>' +
          '<button type="button" class="btn fu-pane-btn bpPaneBtn" data-panes="2"><i class="bx bx-grid-small me-1"></i>2 บาน</button>' +
          '</div>'
      );
    }

    if (pos) fillBpRowPrice($row, pos);
    else recalcTotals();
  });

  // ── BP: ปุ่มลบแถว ───────────────────────────────────────────
  $(document).on('click', '.bpRemoveRow', function () {
    $(this).closest('tr').remove();
    if ($('#positionRows tr').length === 0) addNoRowMsg();
    recalcTotals();
  });

  function clearRows() {
    $('#positionRows').empty();
    fpPackagePrice = null;
    fpPackageCommission = null;
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
    refreshStandalonePackages();
    refreshCurrentPackageRows();
  });

  // ── ลูกค้าใหม่: model change → recheck sunroof ────────────────
  $(document).on('change', '#fu_model_id_new', function () {
    refreshStandalonePackages();
    refreshCurrentPackageRows();
  });

  // ── ดึงข้อมูลใหม่ของแพ็กเกจที่เลือกอยู่ (เมื่อเปลี่ยนรุ่น/ยี่ห้อ) ──
  function refreshCurrentPackageRows() {
    const pkg = $('input[name="package"]:checked').val();
    if (pkg === 'full' || pkg === 'front_body' || pkg === 'advanced') {
      resetExtraToggles();
      removeRowByPosition('ซันรูฟ');
      removeRowByPosition('กระจกประตูคู่หลัง 2');
      removeRowByPosition('แพ็กเกจ 3 บาน');
      if (pkg === 'front_body' || pkg === 'advanced') autoFillPositionSqft();
      checkSunroofForCurrentModel();
      if (pkg === 'advanced') checkAdvancedExtrasForCurrentModel();
    }
    if (pkg === 'sunroof') fillAddonRow('ซันรูฟ', 'sqft_sunroof', 'price_sunroof', 'commission_sunroof');
    if (pkg === 'window3') {
      resetExtraToggles();
      removeRowByPosition('ซันรูฟ');
      fillAddonRow('แพ็กเกจ 3 บาน', 'sqft_3window', 'price_3window', 'commission_3window');
      checkSunroofForCurrentModel();
    }
  }

  function checkSunroofForCurrentModel() {
    const modelId = fuNewCustomer ? $('#fu_model_id_new').val() : $('#fu_model_id').val();
    const filmBrandId = $('#fu_film_brand_id').val();
    if (!modelId || !filmBrandId) return;

    $.get('/film-usage/price-list-lookup', { model_id: modelId, film_brand_id: filmBrandId }, function (res) {
      $('#sunroofToggleRow').toggleClass('d-none', !(res.found && res.has_sunroof));
    });
  }

  // ── Shade change → load stocks ────────────────────────────
  $(document).on('change', '.rowShade', function () {
    const $tr = $(this).closest('tr');
    const shade = $(this).val();
    const filmBrand = $('#fu_film_brand_id').val();
    const $wrap = $tr.find('.stockAllocWrap');

    $tr.removeData('stocks');
    $tr.find('.stockShortWarn').addClass('d-none');

    if (!shade || !filmBrand) {
      $wrap.html(allocRowHtml('— เลือก Shade ก่อน —'));
      return;
    }

    $wrap.html(allocRowHtml('กำลังโหลด...'));

    $.get('/film-usage/stock-search', { film_brand_id: filmBrand, shade }, function (stocks) {
      $tr.data('stocks', stocks || []);
      rebuildAllocations($tr);
    }).fail(function () {
      $wrap.html(allocRowHtml('เกิดข้อผิดพลาด'));
    });
  });

  // ── Stock select / จำนวน ตร.ฟุต เปลี่ยน → คำนวณการตัดจากม้วนใหม่ ──
  $(document).on('change', '.rowStock', function () {
    rebuildAllocations($(this).closest('tr'));
  });
  $(document).on('change', '.rowSqft', function () {
    rebuildAllocations($(this).closest('tr'));
  });

  // ── Auto-fill from price list ─────────────────────────────
  function autoFillFromPriceList(sunroofOnly) {
    const modelId = fuNewCustomer ? $('#fu_model_id_new').val() : $('#fu_model_id').val();
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

  // ── Auto-fill ตร.ฟุต รายตำแหน่ง (front_body / advanced) ─────
  function autoFillPositionSqft(onlyPosition) {
    const modelId = fuNewCustomer ? $('#fu_model_id_new').val() : $('#fu_model_id').val();
    const filmBrandId = $('#fu_film_brand_id').val();
    if (!modelId || !filmBrandId) return;

    $.get('/film-usage/price-list-lookup', { model_id: modelId, film_brand_id: filmBrandId }, function (res) {
      if (!res.found) {
        if (!onlyPosition) {
          fpPackagePrice = null;
          fpPackageCommission = null;
          recalcTotals();
        }
        return;
      }
      $('#positionRows tr[data-position]').each(function () {
        const pos = $(this).data('position');
        if (onlyPosition && pos !== onlyPosition) return;
        const field = POSITION_SQFT_FIELD[pos];
        if (field && res[field] != null && res[field] !== '') {
          $(this).find('.rowSqft').val(parseFloat(res[field]).toFixed(2));
        }
      });
      // เก็บราคารวม/ค่าคอมรวมของแพ็กเกจฐาน (เฉพาะตอนดึงทั้งแพ็กเกจ ไม่ใช่ตอนเพิ่มตำแหน่งเสริม)
      if (!onlyPosition) {
        fpPackagePrice = res.price != null && res.price !== '' ? parseFloat(res.price) : null;
        fpPackageCommission = res.commission != null && res.commission !== '' ? parseFloat(res.commission) : null;
      }
      recalcTotals();
    });
  }

  // ── Totals ─────────────────────────────────────────────────
  $(document).on('input change', '.rowSqft, .rowPrice, .rowCommission', recalcTotals);

  function recalcTotals() {
    let totalSqft = 0,
      totalPrice = 0,
      totalCom = 0;
    $('#positionRows tr[data-position]').each(function () {
      totalSqft += parseFloat($(this).find('.rowSqft').val()) || 0;
      totalPrice += parseMoney($(this).find('.rowPrice').val());
      totalCom += parseMoney($(this).find('.rowCommission').val());
    });
    // บวกราคารวมของแพ็กเกจฐาน (front_body/advanced) ที่ไม่ได้กระจายลงรายแถว
    totalPrice += fpPackagePrice || 0;
    totalCom += fpPackageCommission || 0;
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
    const type = $('input[name="type"]:checked').val();
    const date = $('#fu_order_date').val();
    const filmBrand = $('#fu_film_brand_id').val();

    if (!date) {
      Swal.fire({ icon: 'warning', text: 'กรุณาระบุวันที่สั่งงาน' });
      return;
    }
    if (!filmBrand) {
      Swal.fire({ icon: 'warning', text: 'กรุณาเลือกยี่ห้อฟิล์ม' });
      return;
    }

    // คำนวณการตัดสต็อกให้เป็นปัจจุบันก่อนตรวจ
    $('#positionRows tr[data-position]').each(function () {
      rebuildAllocations($(this));
    });

    // ── ตรวจสอบ: ทุกตำแหน่งต้องเลือก Stock ให้ครบตามจำนวน ตร.ฟุต ──
    const shortList = [];
    $('#positionRows tr[data-position]').each(function () {
      const $tr = $(this);
      const needed = parseFloat($tr.find('.rowSqft').val()) || 0;
      if (needed <= 0) return;
      const stocks = $tr.data('stocks') || [];
      let covered = 0;
      $tr.find('.stockAllocRow .rowStock').each(function () {
        const id = $(this).val();
        if (!id) return;
        const s = stocks.find(function (x) {
          return String(x.id) === String(id);
        });
        const remaining = s ? parseFloat(s.initial_qty) - parseFloat(s.used_qty) : 0;
        covered += Math.min(remaining, Math.max(0, needed - covered));
      });
      if (covered < needed - 0.001) {
        const pos = $tr.attr('data-position') || $tr.find('td:first').text().trim();
        shortList.push({ pos: pos || '(ยังไม่เลือกตำแหน่ง)', needed: needed, covered: covered });
      }
    });

    if (shortList.length) {
      const html =
        'กรุณาเลือก Stock No. ให้ครบตามจำนวน ตร.ฟุต:<br><br>' +
        shortList
          .map(function (p) {
            return (
              '• <b>' +
              p.pos +
              '</b> — ต้องการ ' +
              p.needed.toFixed(2) +
              ' / เลือกแล้ว ' +
              p.covered.toFixed(2) +
              ' (ขาด ' +
              (p.needed - p.covered).toFixed(2) +
              ' ตร.ฟุต)'
            );
          })
          .join('<br>');
      Swal.fire({ icon: 'warning', title: 'เลือก Stock ไม่ครบ', html: html });
      return;
    }

    const rows = [];
    $('#positionRows tr[data-position]').each(function () {
      const $tr = $(this);
      const position = $tr.attr('data-position');
      const shade = $tr.find('.rowShade').val();
      const totalSqft = $tr.find('.rowSqft').val();
      const price = $tr.find('.rowPrice').val();
      const commission = $tr.find('.rowCommission').val();

      // รวบรวมม้วนที่เลือก (1 ตำแหน่งอาจตัดจากหลายม้วน)
      const allocs = [];
      $tr.find('.stockAllocRow').each(function () {
        const $sel = $(this).find('.rowStock');
        const id = $sel.val();
        if (!id) return;
        allocs.push({
          film_stock_id: id,
          stock_no: $sel.find(':selected').data('stock-no') || '',
          sqft_used: $(this).find('.stockPortion').attr('data-portion') || '0'
        });
      });

      if (!allocs.length) {
        // ยังไม่เลือกม้วน — ส่งเป็นรายการเดียว (ไม่ตัดสต็อก)
        rows.push({ position, shade, film_stock_id: '', stock_no: '', sqft_used: totalSqft, price, commission });
      } else {
        // ราคา/ค่าคอม ผูกกับม้วนแรกของตำแหน่ง, ม้วนถัดไปเก็บเฉพาะจำนวนที่ตัด
        allocs.forEach(function (a, i) {
          rows.push({
            position,
            shade,
            film_stock_id: a.film_stock_id,
            stock_no: a.stock_no,
            sqft_used: a.sqft_used,
            price: i === 0 ? price : '',
            commission: i === 0 ? commission : ''
          });
        });
      }
    });

    if (!rows.length) {
      Swal.fire({ icon: 'warning', text: 'กรุณาเลือกตำแหน่งอย่างน้อย 1 รายการ' });
      return;
    }

    // แพ็กเกจฐาน (front_body/advanced) ไม่มีราคารายตำแหน่ง — แนบราคารวม/ค่าคอมรวมไว้ที่แถวฐานแถวแรก
    // เพื่อให้ยอดรวมในรายงาน (sum ของ items) ถูกต้อง โดยไม่กระจายตัวเลขปลอมลงทุกแถว
    if (fpPackagePrice != null && rows.length) {
      if (!parseMoney(rows[0].price)) rows[0].price = String(fpPackagePrice);
      if (!parseMoney(rows[0].commission)) rows[0].commission = String(fpPackageCommission || 0);
    }

    // Build payload
    const payload = {
      _token: $('meta[name="csrf-token"]').attr('content'),
      type,
      order_date: date,
      film_brand_id: filmBrand,
      items: rows
    };

    if (type === 'general') {
      if (fuNewCustomer) {
        const newVin = $('#fu_vin_new').val().trim().toUpperCase();
        const newCustName = $('#fu_customer_name_new').val().trim();
        if (!newVin) {
          Swal.fire({ icon: 'warning', text: 'กรุณาระบุเลข VIN' });
          return;
        }
        if (!newCustName) {
          Swal.fire({ icon: 'warning', text: 'กรุณาระบุชื่อ-สกุลลูกค้า' });
          return;
        }
        payload.vin = newVin;
        payload.customer_name = newCustName;
        payload.sale_person = $('#fu_sale_person_new').val().trim();
        payload.model_id = $('#fu_model_id_new').val();
      } else {
        if (!$('#fu_vin').val() || !$('#fu_car_order_id').val()) {
          Swal.fire({ icon: 'warning', text: 'กรุณาค้นหาและเลือกเลข VIN ก่อนบันทึก' });
          return;
        }
        payload.vin = $('#fu_vin').val();
        payload.car_order_id = $('#fu_car_order_id').val();
        payload.salecar_id = $('#fu_salecar_id').val();
        payload.model_id = $('#fu_model_id').val();
        payload.customer_name = $('#fu_customer_name').val();
        payload.sale_person = $('#fu_sale_person').val();
      }
    } else {
      const custName = $('#fu_customer_name_bp').val().trim();
      const carBrand = $('#fu_car_brand_bp').val();
      const custSource = $('#fu_source_bp').val();

      if (!custName) {
        Swal.fire({ icon: 'warning', text: 'กรุณาระบุชื่อ-สกุลลูกค้า' });
        return;
      }
      if (!carBrand) {
        Swal.fire({ icon: 'warning', text: 'กรุณาเลือกยี่ห้อรถ' });
        return;
      }

      payload.vin = $('input[name="vin_bp"]').val();
      payload.customer_name = custName;
      payload.car_brand = carBrand;
      payload.car_model = $('#fu_car_model_bp').val().trim();
      payload.car_year = $('#fu_car_year_bp').val().trim();
      payload.customer_source = custSource;

      if (custSource === 'insurance') {
        const ins = $('#fu_insurance_bp').val();
        if (!ins) {
          Swal.fire({ icon: 'warning', text: 'กรุณาเลือกประกัน' });
          return;
        }
        payload.insurance_company = ins;
      }
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
          Swal.fire({ icon: 'success', title: 'บันทึกสำเร็จ', timer: 1500, showConfirmButton: true }).then(() => {
            window.location.href = '/film-usage';
          });
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
