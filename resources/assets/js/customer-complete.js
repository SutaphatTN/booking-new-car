// ตรวจ/กรอกข้อมูลลูกค้าให้ครบ (เลขบัตร/เบอร์โทร/ที่อยู่) ก่อนทำการจอง — modal: purchase-order/_complete-customer-modal
// ใช้ร่วมกัน 2 หน้า : เพิ่มการจอง (purchase-order.js) และคำขออนุมัติเกินงบ ตอนกด "สร้างการจอง" (pre-approval.js)
$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});

// window.poCustomerComplete = true/false (เช็คตอนเลือกลูกค้า), guard ตอนกดบันทึกใช้ค่านี้
$(document).ready(function () {
  const $modal = $('#modalCompleteCustomerPO');
  if (!$modal.length) return;

  let poCustomerId = null;
  // opts ของรอบที่เปิด modal อยู่ — onComplete ถูกเรียกเมื่อข้อมูลครบ (ครบอยู่แล้ว / บันทึกจน modal ครบ / รวมลูกค้าแล้วครบ)
  let poPendingOpts = {};

  function fmtPhonePO(v) {
    const d = v.replace(/\D/g, '').substring(0, 10);
    const p = [];
    if (d.length > 0) p.push(d.substring(0, 3));
    if (d.length > 3) p.push(d.substring(3, 7));
    if (d.length > 7) p.push(d.substring(7, 10));
    return p.join('-');
  }
  // ล้างเลขบัตร/พาสปอร์ตให้เหลือแต่ตัวเลข-ตัวอักษร ตรงกับ Customer::normalizeIdNumber() ฝั่ง server
  function normalizeIdPO(v) {
    return String(v || '')
      .replace(/[^A-Za-z0-9]/g, '')
      .toUpperCase();
  }

  function fmtIDPO(v) {
    // พาสปอร์ตต่างชาติ (มีตัวอักษร) ห้ามฟอร์แมต — ดู formatIDCard ใน customer.js
    if (/[A-Za-z]/.test(v)) {
      return v
        .replace(/[^A-Za-z0-9]/g, '')
        .toUpperCase()
        .substring(0, 17);
    }

    const d = v.replace(/\D/g, '').substring(0, 13);
    const p = [];
    if (d.length > 0) p.push(d.substring(0, 1));
    if (d.length > 1) p.push(d.substring(1, 5));
    if (d.length > 5) p.push(d.substring(5, 10));
    if (d.length > 10) p.push(d.substring(10, 12));
    if (d.length > 12) p.push(d.substring(12, 13));
    return p.join('-');
  }
  $('#ccpo_phone').on('input', function () {
    this.value = fmtPhonePO(this.value);
  });
  $('#ccpo_id_number').on('input', function () {
    this.value = fmtIDPO(this.value);
  });

  // ─── Thailand cascade (scoped to this modal) ───
  function ccpoLoadProvinces(preselect) {
    return $.get('/api/thailand/provinces').then(function (data) {
      const $sel = $('#ccpo_province').empty().append('<option value="">— เลือกจังหวัด —</option>');
      data.forEach(p => $sel.append(`<option value="${p}">${p}</option>`));
      if (preselect) $sel.val(preselect);
    });
  }
  function ccpoLoadDistricts(province, preselect) {
    const $sel = $('#ccpo_district').empty().append('<option value="">— เลือกอำเภอ —</option>').prop('disabled', true);
    if (!province) return $.Deferred().resolve().promise();
    return $.get('/api/thailand/districts', { province }).then(function (data) {
      data.forEach(d => $sel.append(`<option value="${d}">${d}</option>`));
      $sel.prop('disabled', false);
      if (preselect) $sel.val(preselect);
    });
  }
  function ccpoLoadTambons(province, district, preselect) {
    const $sel = $('#ccpo_subdistrict')
      .empty()
      .append('<option value="">— เลือกตำบล —</option>')
      .prop('disabled', true);
    $('#ccpo_postal_code').val('');
    $('#ccpo_post_id').val('');
    if (!province || !district) return $.Deferred().resolve().promise();
    return $.get('/api/thailand/tambons', { province, district }).then(function (data) {
      data.forEach(t =>
        $sel.append(
          `<option value="${t.Tambon_pro}" data-postal="${t.Postcode_pro}" data-post-id="${t.id}">${t.Tambon_pro}</option>`
        )
      );
      $sel.prop('disabled', false);
      if (preselect) {
        $sel.val(preselect);
        const opt = $sel.find('option:selected');
        $('#ccpo_postal_code').val(opt.data('postal') || '');
        $('#ccpo_post_id').val(opt.data('post-id') || '');
      }
    });
  }

  $('#ccpo_province').on('change', function () {
    ccpoLoadDistricts(this.value, '');
    $('#ccpo_subdistrict').empty().append('<option value="">— เลือกตำบล —</option>').prop('disabled', true);
    $('#ccpo_postal_code').val('');
    $('#ccpo_post_id').val('');
  });
  $('#ccpo_district').on('change', function () {
    ccpoLoadTambons($('#ccpo_province').val(), this.value, '');
  });
  $('#ccpo_subdistrict').on('change', function () {
    const opt = $(this).find('option:selected');
    $('#ccpo_postal_code').val(opt.data('postal') || '');
    $('#ccpo_post_id').val(opt.data('post-id') || '');
  });

  // เลขบัตรเดิมของลูกค้ารายนี้ (normalize แล้ว) — ใช้ยกเว้นการตรวจเมื่อเซลไม่ได้แก้ช่องนี้
  let _ccpoOriginalId = '';

  function openCompleteModal(profile) {
    _ccpoOriginalId = normalizeIdPO(profile.id_number || '');
    $('#ccpo_prefix').val(profile.prefix_id || '');
    $('#ccpo_first_name').val(profile.first_name || '');
    $('#ccpo_last_name').val(profile.last_name || '');
    $('#ccpo_original_name').val(profile.original_name || '');
    $('#ccpo_id_number').val(profile.id_number ? fmtIDPO(profile.id_number) : '');
    $('#ccpo_phone').val(profile.mobile ? fmtPhonePO(profile.mobile) : '');

    const $list = $('#ccpo_missing').empty();
    (profile.missing || []).forEach(m => $list.append(`<span class="badge bg-label-warning me-1">${m}</span>`));

    const a = profile.address || {};
    $('#ccpo_house_number').val(a.house_number || '');
    $('#ccpo_group').val(a.group || '');
    $('#ccpo_village').val(a.village || '');
    $('#ccpo_alley').val(a.alley || '');
    $('#ccpo_road').val(a.road || '');
    $('#ccpo_postal_code').val(a.postal_code || '');
    $('#ccpo_post_id').val(a.post_id || '');

    ccpoLoadProvinces(a.province || '').then(function () {
      if (!a.province) return;
      ccpoLoadDistricts(a.province, a.district || '').then(function () {
        if (a.district) ccpoLoadTambons(a.province, a.district, a.subdistrict || '');
      });
    });

    $modal.modal('show');
  }

  // ตรวจความครบของลูกค้า — incomplete จะเปิด modal ให้กรอก (เว้นแต่ opts.silent)
  window.poEnsureCustomerComplete = function (customerId, opts) {
    opts = opts || {};
    poCustomerId = customerId;
    poPendingOpts = opts;
    // คำขออนุมัติเกินงบล่วงหน้า: ไม่บังคับข้อมูลลูกค้า (ลองขอก่อนได้) — ไปดักตอน "สร้างการจอง" แทน
    if (document.getElementById('isPreApproval')?.value === '1') {
      window.poCustomerComplete = true;
      return;
    }
    if (!customerId) {
      window.poCustomerComplete = false;
      return;
    }
    $.get('/api/purchase-order/customer-profile', { customer_id: customerId }).done(function (res) {
      if (res.complete) {
        window.poCustomerComplete = true;
        if (opts.onComplete) opts.onComplete();
      } else {
        window.poCustomerComplete = false;
        if (!opts.silent) openCompleteModal(res);
      }
    });
  };

  // ข้อมูลที่กรอกในโมดัล — ใช้ทั้งตอนบันทึกปกติและตอนรวมลูกค้าซ้ำ (ส่งชุดเดียวกัน)
  function ccpoPayload() {
    return {
      customer_id: poCustomerId,
      PrefixName: $('#ccpo_prefix').val() || null,
      FirstName: $('#ccpo_first_name').val().trim(),
      LastName: $('#ccpo_last_name').val().trim(),
      IDNumber: $('#ccpo_id_number').val().trim(),
      Mobilephone1: $('#ccpo_phone').val().trim(),
      house_number: $('#ccpo_house_number').val().trim(),
      group: $('#ccpo_group').val(),
      village: $('#ccpo_village').val(),
      alley: $('#ccpo_alley').val(),
      road: $('#ccpo_road').val(),
      province: $('#ccpo_province').val(),
      district: $('#ccpo_district').val(),
      subdistrict: $('#ccpo_subdistrict').val(),
      postal_code: $('#ccpo_postal_code').val(),
      post_id: $('#ccpo_post_id').val() || null
    };
  }

  // อัปเดตชื่อ/เลขบัตร/เบอร์ ที่แสดงบนหน้าจอง ถ้าเป็นลูกค้าคนที่เลือกอยู่
  function applyCustomerDisplay(res, customerId) {
    if (String($('#CusID').val()) !== String(customerId)) return;

    const setDisplay = (id, val) => {
      const el = document.getElementById(id);
      if (!el) return;
      el.textContent = val || '—';
      el.classList.toggle('empty', !val);
    };
    if (res.name) {
      $('#customerName').val(res.name);
      setDisplay('customerName-display', res.name);
    }
    setDisplay('customerID-display', res.id_number);
    setDisplay('customerPhone-display', res.mobile);
  }

  const esc = s =>
    $('<div>')
      .text(s == null ? '' : s)
      .html();

  // การ์ดบอกว่าเลขบัตร/เบอร์ที่กรอกไปชนกับลูกค้ารายไหน
  function ownerCardHtml(owner) {
    const chips = [];
    if (!owner.same_brand)
      chips.push('<span class="badge bg-label-secondary">แบรนด์ ' + esc(owner.brand_name) + '</span>');
    if (owner.branch) chips.push('<span class="badge bg-label-secondary">สาขา ' + esc(owner.branch) + '</span>');
    if (owner.has_tracking) chips.push('<span class="badge bg-label-info">มีการติดตามเปิดอยู่</span>');
    if (owner.has_booking) chips.push('<span class="badge bg-label-primary">มีใบจอง</span>');
    if (owner.deleted) chips.push('<span class="badge bg-label-danger">ถูกลบแล้ว</span>');

    return (
      '<div class="text-start p-3 rounded" style="background:#f8fafc;border:1px solid #e2e8f0;">' +
      '<div class="fw-bold mb-1">' +
      esc(owner.name || '(ไม่มีชื่อ)') +
      '</div>' +
      '<div class="small text-muted">เลขบัตร ' +
      esc(owner.id_number || '-') +
      '</div>' +
      '<div class="small text-muted">เบอร์ ' +
      esc(owner.mobile || '-') +
      (owner.mobile2 ? ' , ' + esc(owner.mobile2) : '') +
      '</div>' +
      (owner.created_at ? '<div class="small text-muted">เพิ่มเมื่อ ' + esc(owner.created_at) + '</div>' : '') +
      (chips.length ? '<div class="mt-2 d-flex flex-wrap gap-1">' + chips.join('') + '</div>' : '') +
      '</div>'
    );
  }

  // เลขบัตรชน = เป็นคนเดียวกัน → เสนอให้รวมข้อมูลไปที่ลูกค้าเดิม แทนที่จะปิดประตู
  function handleIdTaken(owner) {
    if (owner.deleted) {
      Swal.fire({
        icon: 'warning',
        title: 'เลขบัตรนี้ถูกใช้อยู่แล้ว',
        html:
          ownerCardHtml(owner) +
          '<div class="small text-muted mt-2 text-start">ลูกค้ารายนี้ถูกลบไปแล้ว จึงรวมข้อมูลอัตโนมัติไม่ได้ — กรุณาแจ้งแอดมิน</div>',
        confirmButtonText: 'ตกลง'
      });
      return;
    }

    Swal.fire({
      icon: 'question',
      title: 'เลขบัตรนี้มีลูกค้าอยู่แล้ว',
      html:
        ownerCardHtml(owner) +
        '<div class="small text-muted mt-2 text-start">ถ้าเป็นคนเดียวกัน ระบบจะย้ายการติดตาม/ใบจอง/ที่อยู่ มาไว้ที่ลูกค้ารายนี้ ' +
        'เก็บเบอร์ที่กรอกเป็นเบอร์สำรอง แล้วลบข้อมูลที่ซ้ำออก</div>',
      showCancelButton: true,
      confirmButtonText: 'ใช่ เป็นคนเดียวกัน — รวมข้อมูล',
      cancelButtonText: 'ไม่ใช่ ขอแก้เลขบัตร',
      confirmButtonColor: '#0ea5e9'
    }).then(function (r) {
      if (r.isConfirmed) mergeIntoCustomer(owner);
    });
  }

  function mergeIntoCustomer(owner) {
    $.ajax({
      url: '/api/purchase-order/merge-customer',
      type: 'POST',
      data: Object.assign(ccpoPayload(), { target_customer_id: owner.id }),
      success: function (res) {
        if (!res.success) {
          Swal.fire({ icon: 'error', title: 'รวมข้อมูลไม่สำเร็จ', text: res.message || '' });
          return;
        }

        // ใบจอง/การติดตาม ถูกย้ายมาที่ลูกค้าปลายทางแล้ว — หน้าจองต้องชี้ไปคนใหม่ด้วย
        poCustomerId = res.customer_id;
        $('#CusID').val(res.customer_id);
        applyCustomerDisplay(res, res.customer_id);

        window.poCustomerComplete = !!res.complete;
        $modal.modal('hide');

        const notes = (res.notes || []).map(n => '<li>' + esc(n) + '</li>').join('');
        Swal.fire({
          icon: 'success',
          title: 'รวมข้อมูลลูกค้าแล้ว',
          html:
            '<div class="text-start">ใช้ข้อมูลของ <b>' +
            esc(res.name) +
            '</b> เป็นผู้ซื้อ' +
            (notes ? '<ul class="small text-muted mt-2 mb-0">' + notes + '</ul>' : '') +
            (res.complete
              ? ''
              : '<div class="small text-danger mt-2">ข้อมูลยังไม่ครบ: ' +
                esc((res.missing || []).join(', ')) +
                '</div>') +
            '</div>'
        }).then(function () {
          // ยังขาดข้อมูลอยู่ → เปิดโมดัลให้กรอกต่อกับลูกค้าคนใหม่ (คง callback เดิมไว้)
          if (!res.complete) window.poEnsureCustomerComplete(res.customer_id, poPendingOpts);
          else if (poPendingOpts.onComplete) poPendingOpts.onComplete();
        });
      },
      error: function (xhr) {
        Swal.fire({ icon: 'error', title: 'รวมข้อมูลไม่สำเร็จ', text: xhr.responseJSON?.message || 'กรุณาลองใหม่' });
      }
    });
  }

  $('#btnSaveCompleteCustomerPO').on('click', function () {
    const firstName = $('#ccpo_first_name').val().trim();
    const idNumber = $('#ccpo_id_number').val().trim();
    const phone = $('#ccpo_phone').val().trim();
    const house = $('#ccpo_house_number').val().trim();
    const province = $('#ccpo_province').val();
    const district = $('#ccpo_district').val();
    const subdistrict = $('#ccpo_subdistrict').val();

    if (!firstName) {
      Swal.fire({ icon: 'warning', title: 'กรอกชื่อ', text: 'กรุณากรอกชื่อลูกค้า' });
      return;
    }
    // กติกาเดียวกับ Customer::idNumberError() ฝั่ง server — แก้ที่นั่นแล้วต้องแก้ที่นี่ด้วย
    // รวมถึงข้อยกเว้น "ค่าเดิมไม่ตรวจ" ไม่งั้นฝั่งนี้จะเด้งก่อนที่ server จะได้ยกเว้นให้
    const idClean = normalizeIdPO(idNumber);
    const idUnchanged = idClean !== '' && idClean === _ccpoOriginalId;

    if (idUnchanged) {
      // ข้ามการตรวจ — ลูกค้าเก่าที่เลขเพี้ยนมาแต่เดิมยังแก้ที่อยู่/เบอร์ได้
    } else if (/^\d{13}$/.test(idClean)) {
      // หลักตรวจสอบบัตรไทย : 12 หลักแรก x น้ำหนัก 13..2 แล้ว (11 - ผลรวม % 11) % 10 ต้องเท่าหลักสุดท้าย
      let sum = 0;
      for (let i = 0; i < 12; i++) sum += Number(idClean[i]) * (13 - i);

      if ((11 - (sum % 11)) % 10 !== Number(idClean[12])) {
        Swal.fire({
          icon: 'warning',
          title: 'เลขบัตรไม่ถูกต้อง',
          text: 'เลขบัตรประชาชนไม่ถูกต้อง — กรุณาตรวจสอบเลขกับบัตรอีกครั้ง'
        });
        return;
      }
    } else if (!/^(?=.*[A-Z])[A-Z0-9]{6,17}$/.test(idClean)) {
      Swal.fire({
        icon: 'warning',
        title: 'เลขบัตรไม่ถูกต้อง',
        text: 'กรอกเลขบัตรประชาชนให้ครบ 13 หลัก หรือกรอกเลขพาสปอร์ต (ตัวอักษรผสมตัวเลข 6-17 ตัว)'
      });
      return;
    }
    if (phone.replace(/\D/g, '').length < 9) {
      Swal.fire({ icon: 'warning', title: 'เบอร์โทรไม่ถูกต้อง', text: 'กรุณากรอกเบอร์โทรศัพท์ให้ถูกต้อง' });
      return;
    }
    if (!house || !province || !district || !subdistrict) {
      Swal.fire({ icon: 'warning', title: 'กรอกที่อยู่ให้ครบ', text: 'เลขที่ จังหวัด อำเภอ และตำบล จำเป็นต้องกรอก' });
      return;
    }

    const $btn = $(this).prop('disabled', true);
    $.ajax({
      url: '/api/purchase-order/customer-profile',
      type: 'POST',
      data: ccpoPayload(),
      success: function (res) {
        if (!res.success) {
          Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: res.message || 'ไม่สามารถบันทึกได้' });
          return;
        }
        window.poCustomerComplete = true;

        applyCustomerDisplay(res, poCustomerId);

        $modal.modal('hide');

        if (poPendingOpts.onComplete) {
          poPendingOpts.onComplete();
          return;
        }
        Swal.fire({ icon: 'success', title: 'บันทึกข้อมูลลูกค้าแล้ว', timer: 1500, showConfirmButton: true });
      },
      error: function (xhr) {
        const res = xhr.responseJSON || {};

        // ค่าที่กรอกไปชนกับลูกค้ารายอื่น — ชี้ให้เห็นว่าชนกับใคร (เลขบัตรชน = เสนอรวมข้อมูลให้ด้วย)
        if (res.code === 'id_taken' && res.owner) {
          handleIdTaken(res.owner);
          return;
        }
        if (res.code === 'phone_taken' && res.owner) {
          Swal.fire({
            icon: 'warning',
            title: 'เบอร์นี้มีลูกค้าอยู่แล้ว',
            html:
              ownerCardHtml(res.owner) +
              '<div class="small text-muted mt-2 text-start">เบอร์โทรห้ามซ้ำกัน — กรุณาแก้เบอร์ หรือถ้าเป็นคนเดียวกัน ให้กรอกเลขบัตรของลูกค้ารายนี้เพื่อรวมข้อมูล</div>',
            confirmButtonText: 'ตกลง'
          });
          return;
        }

        Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: res.message || 'เกิดข้อผิดพลาด กรุณาลองใหม่' });
      },
      complete: function () {
        $btn.prop('disabled', false);
      }
    });
  });

  // path A: ลูกค้า prefill มาจากหน้าการติดตาม → เช็คตอนโหลดหน้า
  const prefillCus = $('#CusID').val();
  if (prefillCus) {
    window.poEnsureCustomerComplete(prefillCus, {});
  } else {
    window.poCustomerComplete = false;
  }
});
