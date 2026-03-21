$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
  }
});

//view

//view : table
let customerTable;

$(document).ready(function () {
  if ($.fn.DataTable.isDataTable('#customerTable')) {
    $('#customerTable').DataTable().destroy();
  }

  customerTable = $('#customerTable').DataTable({
    ajax: '/customer/list',
    columns: [
      { data: 'No' },
      { data: 'FullName' },
      { data: 'IDNumber' },
      {
        data: 'Mobilephone',
        render: function (data, type, row) {
          if (type === 'display') {
            return data;
          }
          if (type === 'filter') {
            return row.MobilephoneRaw;
          }
          return data;
        }
      },
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

// blur focus viewCust
$(document).on('hide.bs.modal', '.viewCust', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

//view : modal view-more
$(document).on('click', '.btnViewCust', function () {
  const id = $(this).data('id');

  $.get('/customer/' + id + '/view-more', function (html) {
    $('#viewMore').html(html);
    $('.viewCust').modal('show');
  });
});

// blur focus editCust
$(document).on('hide.bs.modal', '.editCust', function () {
  setTimeout(() => {
    document.activeElement.blur();
    $('body').trigger('focus');
  }, 1);
});

//view : modal edit
$(document).on('click', '.btnEditCust', function () {
  const id = $(this).data('id');
  const $btn = $(this);
  const form = $btn.closest('form')[0];

  $.get('/customer/' + id + '/edit', function (html) {
    $('#editCust').html(html);
    const $modal = $('.editCust');

    $modal.modal('show');

    $modal.on('shown.bs.modal', function () {
      bindCustomerFormEvents(this);
    });

    $modal
      .find('#btnUpdateCustomer')
      .off('click')
      .on('click', function (e) {
        e.preventDefault();

        const form = $modal.find('form')[0];

        const disabledSelects = form.querySelectorAll('select[name$="_province"],select[name$="_district"],select[name$="_subdistrict"]');
        disabledSelects.forEach(el => el.disabled = false);
        const formData = new FormData(form);
        disabledSelects.forEach(el => { if (!el.value) el.disabled = true; });

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
              didOpen: () => {
                Swal.showLoading();
              }
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

            customerTable.ajax.reload(null, false);
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

//view : delete customer
$(document).on('click', '.btnDeleteCust', function () {
  let id = $(this).data('id');

  Swal.fire({
    title: 'คุณแน่ใจหรือไม่?',
    text: 'คุณต้องการลบรายชื่อลูกค้าคนนี้ใช่หรือไม่?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#6c5ffc',
    cancelButtonColor: '#d33',
    confirmButtonText: 'ใช่, ลบเลย!',
    cancelButtonText: 'ยกเลิก'
  }).then(result => {
    if (result.isConfirmed) {
      $.ajax({
        url: '/customer/' + id,
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

            customerTable.ajax.reload(null, false);
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

// ─── Thailand Address Dropdowns ──────────────────────────────────────────────

function thLoadProvinces(sel, preselect, afterLoad) {
  $.get('/api/thailand/provinces').done(function (data) {
    sel.innerHTML = '<option value="">-- เลือกจังหวัด --</option>';
    data.forEach(function (p) {
      const opt = document.createElement('option');
      opt.value = p;
      opt.textContent = p;
      sel.appendChild(opt);
    });
    if (preselect) sel.value = preselect;
    if (afterLoad) afterLoad();
  });
}

function thLoadDistricts(sel, province, preselect, afterLoad) {
  sel.innerHTML = '<option value="">-- เลือกอำเภอ --</option>';
  sel.disabled = true;
  if (!province) { if (afterLoad) afterLoad(); return; }
  $.get('/api/thailand/districts', { province: province }).done(function (data) {
    data.forEach(function (d) {
      const opt = document.createElement('option');
      opt.value = d;
      opt.textContent = d;
      sel.appendChild(opt);
    });
    sel.disabled = false;
    if (preselect) sel.value = preselect;
    if (afterLoad) afterLoad();
  });
}

function thLoadTambons(tambonSel, postalInput, postIdInput, province, district, preselect, afterLoad) {
  tambonSel.innerHTML = '<option value="">-- เลือกตำบล --</option>';
  tambonSel.disabled = true;
  if (postalInput) postalInput.value = '';
  if (postIdInput) postIdInput.value = '';
  if (!province || !district) { if (afterLoad) afterLoad(); return; }
  $.get('/api/thailand/tambons', { province: province, district: district }).done(function (data) {
    data.forEach(function (t) {
      const opt = document.createElement('option');
      opt.value = t.Tambon_pro;
      opt.textContent = t.Tambon_pro;
      opt.dataset.postal = t.Postcode_pro;
      opt.dataset.postId = t.id;
      tambonSel.appendChild(opt);
    });
    tambonSel.disabled = false;
    if (preselect) {
      tambonSel.value = preselect;
      const selected = tambonSel.options[tambonSel.selectedIndex];
      if (selected && selected.dataset.postal) {
        if (postalInput) postalInput.value = selected.dataset.postal;
        if (postIdInput) postIdInput.value = selected.dataset.postId;
      }
    }
    if (afterLoad) afterLoad();
  });
}

function initAddressSection(container, prefix) {
  const provinceSel = container.querySelector(`[name="${prefix}_province"]`);
  const districtSel = container.querySelector(`[name="${prefix}_district"]`);
  const tambonSel   = container.querySelector(`[name="${prefix}_subdistrict"]`);
  const postalInput = container.querySelector(`[name="${prefix}_postal_code"]`);
  const postIdInput = container.querySelector(`[name="${prefix}_post_id"]`);

  if (!provinceSel) return;

  // if options already pre-rendered server-side (edit form), skip AJAX initial load
  if (provinceSel.options.length > 1) {
    // just sync postal/post_id from the already-selected tambon option
    if (tambonSel && tambonSel.value) {
      const selectedOpt = tambonSel.options[tambonSel.selectedIndex];
      if (selectedOpt && selectedOpt.dataset.postal) {
        if (postalInput && !postalInput.value) postalInput.value = selectedOpt.dataset.postal;
        if (postIdInput && !postIdInput.value) postIdInput.value = selectedOpt.dataset.postId;
      }
    }
  } else {
    // create form: no pre-rendered options, load via AJAX
    const preProvince = provinceSel.dataset.value || '';
    const preDistrict = districtSel ? (districtSel.dataset.value || '') : '';
    const preTambon   = tambonSel   ? (tambonSel.dataset.value   || '') : '';

    thLoadProvinces(provinceSel, preProvince, function () {
      if (preProvince && districtSel) {
        thLoadDistricts(districtSel, preProvince, preDistrict, function () {
          if (preDistrict && tambonSel) {
            thLoadTambons(tambonSel, postalInput, postIdInput, preProvince, preDistrict, preTambon);
          }
        });
      }
    });
  }

  $(provinceSel).on('change', function () {
    const province = this.value;
    if (districtSel) thLoadDistricts(districtSel, province, '', null);
    if (tambonSel)   { tambonSel.innerHTML = '<option value="">-- เลือกตำบล --</option>'; tambonSel.disabled = true; }
    if (postalInput) postalInput.value = '';
    if (postIdInput) postIdInput.value = '';
  });

  if (districtSel) {
    $(districtSel).on('change', function () {
      const province = provinceSel.value;
      const district = this.value;
      if (tambonSel) thLoadTambons(tambonSel, postalInput, postIdInput, province, district, '', null);
    });
  }

  if (tambonSel) {
    $(tambonSel).on('change', function () {
      const selected = this.options[this.selectedIndex];
      if (selected && selected.dataset.postal) {
        if (postalInput) postalInput.value = selected.dataset.postal;
        if (postIdInput) postIdInput.value = selected.dataset.postId;
      } else {
        if (postalInput) postalInput.value = '';
        if (postIdInput) postIdInput.value = '';
      }
    });
  }
}

// sync doc dropdown from current values (used by sameAsCurrent checkbox)
function syncDocFromCurrent(container) {
  const province = container.querySelector('[name="current_province"]')?.value || '';
  const district = container.querySelector('[name="current_district"]')?.value || '';
  const tambon   = container.querySelector('[name="current_subdistrict"]')?.value || '';

  const docProvinceSel = container.querySelector('[name="doc_province"]');
  const docDistrictSel = container.querySelector('[name="doc_district"]');
  const docTambonSel   = container.querySelector('[name="doc_subdistrict"]');
  const docPostal      = container.querySelector('[name="doc_postal_code"]');
  const docPostId      = container.querySelector('[name="doc_post_id"]');

  // if doc selects already have the correct values pre-rendered, skip AJAX reload
  if (docProvinceSel && docProvinceSel.value === province &&
      docDistrictSel && docDistrictSel.value === district &&
      docTambonSel   && docTambonSel.value   === tambon) {
    if (tambon) {
      const selectedOpt = docTambonSel.options[docTambonSel.selectedIndex];
      if (selectedOpt && selectedOpt.dataset.postal) {
        if (docPostal && !docPostal.value) docPostal.value = selectedOpt.dataset.postal;
        if (docPostId && !docPostId.value) docPostId.value = selectedOpt.dataset.postId;
      }
    }
    return;
  }

  if (docProvinceSel) docProvinceSel.value = province;

  thLoadDistricts(docDistrictSel, province, district, function () {
    thLoadTambons(docTambonSel, docPostal, docPostId, province, district, tambon);
  });
}

//view more and edit : format id number, mobile, address sync
function bindCustomerFormEvents(container = document) {
  const idInput = container.querySelector('#IDNumber');
  const phone1 = container.querySelector('#Mobilephone1');
  const phone2 = container.querySelector('#Mobilephone2');

  if (idInput) {
    idInput.addEventListener('input', e => (e.target.value = formatIDCard(e.target.value)));
  }
  if (phone1) {
    phone1.addEventListener('input', e => (e.target.value = formatPhone(e.target.value)));
  }
  if (phone2) {
    phone2.addEventListener('input', e => (e.target.value = formatPhone(e.target.value)));
  }

  // init cascading dropdowns
  initAddressSection(container, 'current');
  initAddressSection(container, 'doc');

  const checkbox = container.querySelector('#sameAsCurrent');
  if (checkbox) {
    // flag to prevent uncheck loop when we programmatically set doc values
    let syncing = false;

    checkbox.addEventListener('change', function () {
      const simpleFields = ['house_number', 'group', 'village', 'alley', 'road'];

      if (checkbox.checked) {
        // copy simple fields (no lock — user can still edit to deviate)
        simpleFields.forEach(field => {
          const currentField = container.querySelector(`[name="current_${field}"]`);
          const docField = container.querySelector(`[name="doc_${field}"]`);
          if (currentField && docField) docField.value = currentField.value;
        });
        syncDocFromCurrent(container);
      } else {
        // clear doc address section
        simpleFields.forEach(field => {
          const docField = container.querySelector(`[name="doc_${field}"]`);
          if (docField) docField.value = '';
        });
        const docProvinceSel = container.querySelector('[name="doc_province"]');
        const docDistrictSel = container.querySelector('[name="doc_district"]');
        const docTambonSel   = container.querySelector('[name="doc_subdistrict"]');
        const docPostal      = container.querySelector('[name="doc_postal_code"]');
        const docPostId      = container.querySelector('[name="doc_post_id"]');
        if (docProvinceSel) docProvinceSel.value = '';
        if (docDistrictSel) { docDistrictSel.innerHTML = '<option value="">-- เลือกอำเภอ --</option>'; docDistrictSel.disabled = true; }
        if (docTambonSel)   { docTambonSel.innerHTML   = '<option value="">-- เลือกตำบล --</option>';  docTambonSel.disabled   = true; }
        if (docPostal) docPostal.value = '';
        if (docPostId) docPostId.value = '';
      }
    });

    // auto-uncheck if user edits any doc simple field
    ['house_number', 'group', 'village', 'alley', 'road'].forEach(field => {
      const docField = container.querySelector(`[name="doc_${field}"]`);
      if (docField) {
        docField.addEventListener('input', () => {
          if (checkbox.checked && !syncing) checkbox.checked = false;
        });
      }
    });

    // auto-uncheck if user changes any doc address dropdown
    ['province', 'district', 'subdistrict'].forEach(field => {
      const docSel = container.querySelector(`[name="doc_${field}"]`);
      if (docSel) {
        docSel.addEventListener('change', () => {
          if (checkbox.checked && !syncing) checkbox.checked = false;
        });
      }
    });

    // sync simple fields live when current changes (while checkbox is checked)
    ['house_number', 'group', 'village', 'alley', 'road'].forEach(field => {
      const currentField = container.querySelector(`[name="current_${field}"]`);
      if (currentField) {
        currentField.addEventListener('input', () => {
          if (checkbox.checked) {
            const docField = container.querySelector(`[name="doc_${field}"]`);
            if (docField) docField.value = currentField.value;
          }
        });
      }
    });

    // sync address dropdowns live when current changes (while checkbox is checked)
    ['province', 'district', 'subdistrict'].forEach(field => {
      const currentSel = container.querySelector(`[name="current_${field}"]`);
      if (currentSel) {
        currentSel.addEventListener('change', () => {
          if (checkbox.checked) {
            syncing = true;
            syncDocFromCurrent(container);
            // reset flag after async load finishes (give generous timeout)
            setTimeout(() => { syncing = false; }, 2000);
          }
        });
      }
    });

    if (checkbox.checked) {
      checkbox.dispatchEvent(new Event('change'));
    }
  }
}

//view more and edit : format id number
function formatIDCard(value) {
  const digits = value.replace(/\D/g, '').substring(0, 13);
  const parts = [];
  if (digits.length > 0) parts.push(digits.substring(0, 1));
  if (digits.length > 1) parts.push(digits.substring(1, 5));
  if (digits.length > 5) parts.push(digits.substring(5, 10));
  if (digits.length > 10) parts.push(digits.substring(10, 12));
  if (digits.length > 12) parts.push(digits.substring(12, 13));
  return parts.join('-');
}

//view more and edit : format phone
function formatPhone(value) {
  const digits = value.replace(/\D/g, '').substring(0, 10);
  const parts = [];
  if (digits.length > 0) parts.push(digits.substring(0, 3));
  if (digits.length > 3) parts.push(digits.substring(3, 7));
  if (digits.length > 7) parts.push(digits.substring(7, 10));
  return parts.join('-');
}

document.addEventListener('shown.bs.modal', e => {
  bindCustomerFormEvents(e.target);
});

//input + view more
document.addEventListener('DOMContentLoaded', function () {
  //view more and edit : call
  bindCustomerFormEvents(document);

  // input : save customer
  $(document).on('click', '.btnSaveCustomer', function (e) {
    e.preventDefault();

    const $btn = $(this);
    const form = $('#customerInputForm')[0];

    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    const url = $(form).attr('action');

    // enable disabled address selects so FormData includes their values
    const disabledSelects = form.querySelectorAll('select[name$="_province"],select[name$="_district"],select[name$="_subdistrict"]');
    disabledSelects.forEach(el => el.disabled = false);
    const formData = new FormData(form);
    disabledSelects.forEach(el => { if (!el.value) el.disabled = true; });

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
          window.location.href = '/customer';
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
