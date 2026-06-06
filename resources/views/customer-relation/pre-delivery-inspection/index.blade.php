@extends('layouts/contentNavbarLayout')
@section('title', 'ตรวจรถก่อนส่งมอบ')

@section('page-script')
  <script>
    $(document).ready(function() {

      // DataTable
      const table = $('#preDeliveryTable').DataTable({
        ajax: {
          url: '{{ route('pre-delivery-inspection.list') }}',
          dataSrc: 'data',
        },
        columns: [{
            data: 'No',
            width: '50px',
            className: 'text-center'
          },
          {
            data: 'FullName',
            orderable: false
          },
          {
            data: 'sale_name',
            orderable: false
          },
          {
            data: 'model',
            orderable: false
          },
          {
            data: 'delivery_date',
            orderable: false
          },
          {
            data: 'status_badge'
          },
          {
            data: null,
            orderable: false,
            className: 'text-center',
            render: function(data, type, row) {
              return `
            <div class="d-flex gap-1 justify-content-center">
              <button class="btn btn-icon btn-info btn-view text-white"
                data-id="${row.salecar_id}"
                data-name="${row.FullName}"
                title="ดูข้อมูล">
                <i class="bx bx-show"></i>
            </button>
            <button class="btn btn-icon btn-warning btn-edit text-white"
                data-id="${row.salecar_id}"
                data-name="${row.FullName}"
                title="แก้ไข">
                <i class="bx bx-edit"></i>
              </button>
            </div>`;
            },
          },
        ],
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
        },
        pageLength: 10,
        order: [
          [0, 'asc']
        ],
      });

      // ── Open modal & load data ──
      $('#preDeliveryTable').on('click', '.btn-edit', function() {
        const salecarId = $(this).data('id');
        const name = $(this).data('name');

        resetModal();
        $('#modalTitle').text('แก้ไข - ' + name);
        $('#modalSalecarId').val(salecarId);
        $('#modalInspectionId').val('');

        $.get(`/pre-delivery-inspection/${salecarId}/data`, function(data) {
          if (!data) return;
          $('#modalInspectionId').val(data.id);

          // Radios
          if (data.accessories_complete !== null)
            $(`input[name="accessories_complete"][value="${data.accessories_complete ? 1 : 0}"]`).prop(
              'checked', true);
          if (data.exterior_clean !== null)
            $(`input[name="exterior_clean"][value="${data.exterior_clean ? 1 : 0}"]`).prop('checked', true);
          if (data.interior_clean !== null)
            $(`input[name="interior_clean"][value="${data.interior_clean ? 1 : 0}"]`).prop('checked', true);
          if (data.issues_resolved !== null)
            $(`input[name="issues_resolved"][value="${data.issues_resolved ? 1 : 0}"]`).prop('checked', true);

          // Text fields
          $('#accessories_incomplete_items').val(data.accessories_incomplete_items || '');
          $('#accessories_note').val(data.accessories_note || '');
          $('#exterior_incomplete_items').val(data.exterior_incomplete_items || '');
          $('#exterior_note').val(data.exterior_note || '');
          $('#interior_incomplete_items').val(data.interior_incomplete_items || '');
          $('#interior_note').val(data.interior_note || '');
          $('#issues_detail').val(data.issues_detail || '');
          $('#issues_reason').val(data.issues_reason || '');

          // Conditional visibility
          toggleAccessoriesRow();
          toggleExteriorRow();
          toggleInteriorRow();

          // Existing docs
          const $docList = $('#existingDocs').empty();
          (data.docs || []).forEach(function(f) {
            $docList.append(buildMediaItem(f, 'doc'));
          });

          // Existing photos
          const $photoList = $('#existingPhotos').empty();
          (data.photos || []).forEach(function(f) {
            $photoList.append(buildMediaItem(f, 'photo'));
          });
        });

        $('#modalInspection').modal('show');
      });

      // ── Conditional: อุปกรณ์ตกแต่ง ──
      function toggleAccessoriesRow() {
        const val = $('input[name="accessories_complete"]:checked').val();
        if (val === '0') {
          $('#rowAccessoriesIncomplete').slideDown(150);
        } else {
          $('#rowAccessoriesIncomplete').slideUp(150);
        }
      }

      function toggleExteriorRow() {
        const val = $('input[name="exterior_clean"]:checked').val();
        if (val === '0') {
          $('#rowExteriorIncomplete').slideDown(150);
        } else {
          $('#rowExteriorIncomplete').slideUp(150);
        }
      }

      function toggleInteriorRow() {
        const val = $('input[name="interior_clean"]:checked').val();
        if (val === '0') {
          $('#rowInteriorIncomplete').slideDown(150);
        } else {
          $('#rowInteriorIncomplete').slideUp(150);
        }
      }

      $('input[name="accessories_complete"]').on('change', toggleAccessoriesRow);
      $('input[name="exterior_clean"]').on('change', toggleExteriorRow);
      $('input[name="interior_clean"]').on('change', toggleInteriorRow);

      // ── File card style by extension ──
      function fileCardStyle(name) {
        const ext = (name.split('.').pop() || '').toLowerCase();
        if (ext === 'pdf') return {
          bg: '#ef4444',
          label: 'PDF'
        };
        if (['xlsx', 'xls', 'csv'].includes(ext)) return {
          bg: '#16a34a',
          label: ext.toUpperCase()
        };
        if (['doc', 'docx'].includes(ext)) return {
          bg: '#2563eb',
          label: ext.toUpperCase()
        };
        if (['ppt', 'pptx'].includes(ext)) return {
          bg: '#ea580c',
          label: ext.toUpperCase()
        };
        if (['zip', 'rar', '7z'].includes(ext)) return {
          bg: '#7c3aed',
          label: ext.toUpperCase()
        };
        return {
          bg: '#64748b',
          label: ext ? ext.toUpperCase() : 'FILE'
        };
      }

      // ── Build media item (existing uploaded files) ──
      function buildMediaItem(f, type) {
        const isImage = /\.(jpg|jpeg|png|gif|webp|bmp)$/i.test(f.name);
        const inspId = $('#modalInspectionId').val();
        const proxyUrl =
          `/pre-delivery-inspection/${inspId}/proxy/${encodeURIComponent(f.name)}?url=${encodeURIComponent(f.url)}`;
        if (isImage) {
          return `
        <div class="position-relative d-inline-block m-1" data-url="${f.url}" data-type="${type}">
          <img src="${proxyUrl}" class="rounded border" style="width:80px;height:80px;object-fit:cover;cursor:pointer;"
            onclick="window.open('${proxyUrl}','_blank')" title="${f.name}">
          <button type="button" class="btn btn-danger btn-delete-file position-absolute top-0 end-0" style="font-size:.8rem;line-height:1;padding:2px 5px;" title="ลบ">
            <i class="bx bx-x"></i>
          </button>
        </div>`;
        }
        const st = fileCardStyle(f.name);
        return `
        <div class="position-relative d-inline-block m-1" data-url="${f.url}" data-type="${type}" style="width:80px;">
          <a href="${proxyUrl}" target="_blank" class="d-block text-decoration-none" title="${f.name}">
            <div class="d-flex flex-column align-items-center justify-content-center rounded text-white" style="width:80px;height:80px;background:${st.bg};">
              <i class="bx bx-file" style="font-size:1.8rem;"></i>
              <span class="badge bg-white mt-1" style="font-size:.6rem;color:${st.bg};font-weight:700;">${st.label}</span>
            </div>
            <div class="text-truncate text-center text-dark mt-1" style="font-size:.7rem;max-width:80px;">${f.name}</div>
          </a>
          <button type="button" class="btn btn-danger btn-delete-file position-absolute top-0 end-0" style="font-size:.8rem;line-height:1;padding:2px 5px;" title="ลบ">
            <i class="bx bx-x"></i>
          </button>
        </div>`;
      }

      // ── Remove existing file (staged – actual delete happens on save) ──
      $(document).on('click', '.btn-delete-file', function() {
        $(this).closest('[data-url]').remove();
      });

      // ── Preview newly selected files (shared renderer with X-button) ──
      function renderFilePreviews(input, $preview) {
        $preview.empty();
        Array.from(input.files).forEach(function(file, idx) {
          const isImg = /image/i.test(file.type);
          const objUrl = isImg ? URL.createObjectURL(file) : null;
          const st = isImg ? null : fileCardStyle(file.name);
          const $item = $(
            `<div class="position-relative d-inline-block m-1" style="width:80px;vertical-align:top;">
              ${isImg
                ? `<img src="${objUrl}" class="rounded border" style="width:80px;height:80px;object-fit:cover;">`
                : `<div class="d-flex flex-column align-items-center justify-content-center rounded text-white" style="width:80px;height:80px;background:${st.bg};">
                         <i class="bx bx-file" style="font-size:1.8rem;"></i>
                         <span class="badge bg-white mt-1" style="font-size:.6rem;color:${st.bg};font-weight:700;">${st.label}</span>
                       </div>
                       <div class="text-truncate text-center text-dark mt-1" style="font-size:.7rem;max-width:80px;">${file.name}</div>`
              }
              <button type="button" class="btn btn-danger btn-remove-new-file position-absolute top-0 end-0" style="font-size:.8rem;line-height:1;padding:2px 5px;" title="ลบ"><i class="bx bx-x"></i></button>
            </div>`
          );
          $item.find('.btn-remove-new-file').on('click', function() {
            const dt = new DataTransfer();
            Array.from(input.files).forEach(function(f, i) {
              if (i !== idx) dt.items.add(f);
            });
            input.files = dt.files;
            renderFilePreviews(input, $preview);
          });
          $preview.append($item);
        });
      }

      $('#inspection_photos_input').on('change', function() {
        renderFilePreviews(this, $('#newPhotosPreview'));
      });

      $('#inspection_docs_input').on('change', function() {
        renderFilePreviews(this, $('#newDocsPreview'));
      });

      // ── Save ──
      $('#btnSaveInspection').on('click', function() {
        const salecarId = $('#modalSalecarId').val();
        const $btn = $(this);
        Swal.fire({
          title: 'กำลังบันทึกข้อมูล...',
          text: 'กรุณารอสักครู่',
          allowOutsideClick: false,
          didOpen: () => Swal.showLoading()
        });
        $btn.prop('disabled', true);

        const fd = new FormData();
        const accVal = $('input[name="accessories_complete"]:checked').val();
        if (accVal !== undefined) fd.append('accessories_complete', accVal);
        fd.append('accessories_incomplete_items', $('#accessories_incomplete_items').val());
        fd.append('accessories_note', $('#accessories_note').val());

        const extVal = $('input[name="exterior_clean"]:checked').val();
        if (extVal !== undefined) fd.append('exterior_clean', extVal);
        fd.append('exterior_incomplete_items', $('#exterior_incomplete_items').val());
        fd.append('exterior_note', $('#exterior_note').val());

        const intVal = $('input[name="interior_clean"]:checked').val();
        if (intVal !== undefined) fd.append('interior_clean', intVal);
        fd.append('interior_incomplete_items', $('#interior_incomplete_items').val());
        fd.append('interior_note', $('#interior_note').val());

        const issVal = $('input[name="issues_resolved"]:checked').val();
        if (issVal !== undefined) fd.append('issues_resolved', issVal);
        fd.append('issues_detail', $('#issues_detail').val());
        fd.append('issues_reason', $('#issues_reason').val());

        $('#existingDocs [data-url]').each(function() {
          fd.append('keep_docs[]', $(this).data('url'));
        });
        const docsFiles = $('#inspection_docs_input')[0].files;
        for (let i = 0; i < docsFiles.length; i++) {
          fd.append('inspection_docs[]', docsFiles[i]);
        }

        $('#existingPhotos [data-url]').each(function() {
          fd.append('keep_photos[]', $(this).data('url'));
        });
        const photoFiles = $('#inspection_photos_input')[0].files;
        for (let i = 0; i < photoFiles.length; i++) {
          fd.append('inspection_photos[]', photoFiles[i]);
        }

        $.ajax({
          url: `/pre-delivery-inspection/${salecarId}/save`,
          method: 'POST',
          data: fd,
          processData: false,
          contentType: false,
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          success: function(res) {
            Swal.fire({
              icon: 'success',
              title: 'สำเร็จ',
              text: res.message,
              timer: 1500,
              showConfirmButton: true
            });
            $('#modalInspection').modal('hide');
            table.ajax.reload(null, false);
          },
          error: function(xhr) {
            const msg = xhr.responseJSON?.message || 'เกิดข้อผิดพลาด';
            Swal.fire({
              icon: 'error',
              title: 'ผิดพลาด',
              text: msg
            });
          },
          complete: function() {
            $btn.prop('disabled', false).html('<i class="bx bx-save me-1"></i> บันทึก');
          }
        });
      });

      // ── Reset modal ──
      function resetModal() {
        $('input[name="accessories_complete"]').prop('checked', false);
        $('input[name="exterior_clean"]').prop('checked', false);
        $('input[name="interior_clean"]').prop('checked', false);
        $('input[name="issues_resolved"]').prop('checked', false);
        $('#accessories_incomplete_items, #accessories_note, #exterior_incomplete_items, #exterior_note, #interior_incomplete_items, #interior_note, #issues_detail, #issues_reason')
          .val('');
        $('#existingDocs, #existingPhotos, #newPhotosPreview, #newDocsPreview').empty();
        $('#inspection_docs_input, #inspection_photos_input').val('');
        $('#rowAccessoriesIncomplete, #rowExteriorIncomplete, #rowInteriorIncomplete').hide();
        $('#modalInspectionId').val('');
      }

      $('#modalInspection').on('hidden.bs.modal', resetModal);

      // Fix aria-hidden warning when modal closes with focused element
      $(document).on('hide.bs.modal', '#modalInspection, #modalViewInspection', function () {
        setTimeout(() => {
          document.activeElement.blur();
          $('body').trigger('focus');
        }, 1);
      });

      // ── View modal ──
      $('#preDeliveryTable').on('click', '.btn-view', function() {
        const salecarId = $(this).data('id');
        const name = $(this).data('name');

        $('#viewModalTitle').text('ดูข้อมูล - ' + name);
        $('#viewModalBody').html(
          '<div class="text-center py-5"><i class="bx bx-loader-alt bx-spin fs-2 text-secondary"></i></div>'
        );
        $('#modalViewInspection').modal('show');

        $.get(`/pre-delivery-inspection/${salecarId}/view-data`, function(data) {
          $('#viewModalBody').html(buildViewContent(data));
        });
      });

      function viewBadge(val) {
        if (val === null || val === undefined)
          return '<span class="badge bg-secondary">ยังไม่ระบุ</span>';
        return val ?
          '<span class="badge bg-success"><i class="bx bx-check me-1"></i>เรียบร้อย</span>' :
          '<span class="badge bg-danger"><i class="bx bx-x me-1"></i>ไม่เรียบร้อย</span>';
      }

      function buildViewMediaItem(f, inspId) {
        const isImg = /\.(jpg|jpeg|png|gif|webp|bmp)$/i.test(f.name);
        const proxyUrl =
          `/pre-delivery-inspection/${inspId}/proxy/${encodeURIComponent(f.name)}?url=${encodeURIComponent(f.url)}`;
        if (isImg) {
          return `<a href="${proxyUrl}" target="_blank" class="d-inline-block m-1" title="${f.name}">
            <img src="${proxyUrl}" class="rounded border" style="width:80px;height:80px;object-fit:cover;cursor:pointer;">
          </a>`;
        }
        const st = fileCardStyle(f.name);
        return `
        <a href="${proxyUrl}" target="_blank" class="d-inline-block m-1 text-decoration-none" title="${f.name}" style="width:80px;">
          <div class="d-flex flex-column align-items-center justify-content-center rounded text-white" style="width:80px;height:80px;background:${st.bg};">
            <i class="bx bx-file" style="font-size:1.8rem;"></i>
            <span class="badge bg-white mt-1" style="font-size:.6rem;color:${st.bg};font-weight:700;">${st.label}</span>
          </div>
          <div class="text-truncate text-center text-dark mt-1" style="font-size:.7rem;max-width:80px;">${f.name}</div>
        </a>`;
      }

      function buildLogEntry(log, no) {
        const items = [];
        if (log.accessories_complete === false) {
          let txt = '<span class="badge bg-danger me-1">อุปกรณ์ตกแต่ง</span>';
          if (log.accessories_incomplete_items) txt += `<span class="small text-secondary">${log.accessories_incomplete_items}</span>`;
          items.push(txt);
        }
        if (log.exterior_clean === false) {
          let txt = '<span class="badge bg-danger me-1">ความสะอาดภายนอก</span>';
          if (log.exterior_incomplete_items) txt += `<span class="small text-secondary">${log.exterior_incomplete_items}</span>`;
          items.push(txt);
        }
        if (log.interior_clean === false) {
          let txt = '<span class="badge bg-danger me-1">ความสะอาดภายใน</span>';
          if (log.interior_incomplete_items) txt += `<span class="small text-secondary">${log.interior_incomplete_items}</span>`;
          items.push(txt);
        }
        if (log.issues_resolved === false) {
          let txt = '<span class="badge bg-danger me-1">ปัญหาที่พบ/วิธีแก้ไข</span>';
          if (log.issues_detail) txt += `<span class="small text-secondary">${log.issues_detail}</span>`;
          items.push(txt);
        }
        return `
          <div class="p-3${no > 1 ? ' border-top' : ''}">
            <div class="d-flex align-items-center gap-2 mb-2">
              <span class="badge bg-secondary rounded-pill">#${no}</span>
              <small class="text-muted"><i class="bx bx-time-five me-1"></i>${log.created_at}</small>
            </div>
            ${items.map(item => `<div class="mb-1">${item}</div>`).join('')}
          </div>`;
      }

      function buildViewContent(data) {
        const c = data.customer;
        const car = data.car;
        const ins = data.inspection;

        const leftHtml = `
          <div class="po-section mb-3">
            <div class="po-section-header">
              <div class="po-section-icon sky"><i class="bx bx-user"></i></div>
              <h6 class="po-section-title">ข้อมูลลูกค้า</h6>
            </div>
            <div class="po-section-body p-0">
              <div class="vp-info-row border-bottom">
                <i class="bx bx-user vp-icon"></i>
                <div>
                  <div class="vp-field-label">ชื่อ - นามสกุล</div>
                  <div class="vp-field-value">${c.full_name}</div>
                </div>
              </div>
              <div class="vp-info-row">
                <i class="bx bx-phone vp-icon"></i>
                <div>
                  <div class="vp-field-label">เบอร์โทรศัพท์</div>
                  <div class="vp-field-value">${c.mobile}</div>
                </div>
              </div>
            </div>
          </div>
          <div class="po-section">
            <div class="po-section-header">
              <div class="po-section-icon emerald"><i class="bx bx-car"></i></div>
              <h6 class="po-section-title">ข้อมูลรถ</h6>
            </div>
            <div class="po-section-body p-0">
              <div class="row g-0">
                <div class="col-12 border-bottom vp-field">
                  <div class="vp-field-label"><i class="bx bx-car"></i>รุ่นหลัก</div>
                  <div class="vp-field-value">${car.model}</div>
                </div>
                <div class="col-12 border-bottom vp-field">
                  <div class="vp-field-label"><i class="bx bx-git-branch"></i>รุ่นย่อย</div>
                  <div class="vp-field-value">${car.sub_model}</div>
                </div>
                <div class="col-6 border-end border-bottom vp-field">
                  <div class="vp-field-label"><i class="bx bx-palette"></i>สี</div>
                  <div class="vp-field-value">${car.color}</div>
                </div>
                <div class="col-6 border-bottom vp-field">
                  <div class="vp-field-label"><i class="bx bx-calendar"></i>ปี</div>
                  <div class="vp-field-value">${car.year}</div>
                </div>
                <div class="col-12 border-bottom vp-field">
                  <div class="vp-field-label"><i class="bx bx-barcode"></i>VIN Number</div>
                  <div class="vp-field-value">${car.vin}</div>
                </div>
                <div class="col-7 border-end vp-field">
                  <div class="vp-field-label"><i class="bx bx-user-check"></i>ฝ่ายขาย</div>
                  <div class="vp-field-value">${car.sale_name}</div>
                </div>
                <div class="col-5 vp-field">
                  <div class="vp-field-label"><i class="bx bx-calendar-check"></i>วันที่ส่งมอบ</div>
                  <div class="vp-field-value">${car.delivery_date}</div>
                </div>
              </div>
            </div>
          </div>`;

        if (!ins) {
          const rightHtml = `<div class="alert alert-warning d-flex align-items-center gap-2">
            <i class="bx bx-info-circle fs-5"></i><span>ยังไม่มีข้อมูลการตรวจสอบ</span>
          </div>`;
          return `<div class="row g-3"><div class="col-md-4">${leftHtml}</div><div class="col-md-8">${rightHtml}</div></div>`;
        }

        const id = ins.id;

        const sec = (icon, color, title, statusVal, extras) => {
          const iconDiv = color.startsWith('#') ?
            `<div class="po-section-icon" style="background:${color};">${icon}</div>` :
            `<div class="po-section-icon ${color}">${icon}</div>`;
          return `
          <div class="po-section mb-3">
            <div class="po-section-header">
              ${iconDiv}
              <h6 class="po-section-title">${title}</h6>
            </div>
            <div class="po-section-body p-0">
              <div class="vp-status-strip">
                <i class="bx bx-check-shield text-muted" style="font-size:.9rem;"></i>
                <span class="vp-status-label">สถานะ</span>
                ${viewBadge(statusVal)}
              </div>
              ${extras}
            </div>
          </div>`;
        };

        const note = (label, val) => val ?
          `<div class="vp-note-field">
            <div class="vp-field-label">${label}</div>
            <div class="vp-note-value">${val}</div>
          </div>` :
          '';

        let rightHtml = '';

        rightHtml += sec(
          '<i class="bx bx-wrench"></i>', 'sky', '1. อุปกรณ์ตกแต่ง',
          ins.accessories_complete,
          (ins.accessories_complete === false && ins.accessories_incomplete_items ?
            `<div class="vp-note-field">
              <div class="vp-field-label"><i class="bx bx-list-ul"></i>ชิ้นงานที่ไม่เรียบร้อย</div>
              <div class="vp-note-value">${ins.accessories_incomplete_items}</div>
            </div>` :
            '') +
          note('หมายเหตุ', ins.accessories_note)
        );

        rightHtml += sec(
          '<i class="bx bx-sun"></i>', 'emerald', '2. ความสะอาดภายนอก',
          ins.exterior_clean,
          (ins.exterior_clean === false && ins.exterior_incomplete_items ?
            `<div class="vp-note-field">
              <div class="vp-field-label"><i class="bx bx-list-ul"></i>รายละเอียดที่ไม่เรียบร้อย</div>
              <div class="vp-note-value">${ins.exterior_incomplete_items}</div>
            </div>` : '') +
          note('หมายเหตุ', ins.exterior_note)
        );

        rightHtml += sec(
          '<i class="bx bx-shield-quarter"></i>', 'amber', '3. ความสะอาดภายใน',
          ins.interior_clean,
          (ins.interior_clean === false && ins.interior_incomplete_items ?
            `<div class="vp-note-field">
              <div class="vp-field-label"><i class="bx bx-list-ul"></i>รายละเอียดที่ไม่เรียบร้อย</div>
              <div class="vp-note-value">${ins.interior_incomplete_items}</div>
            </div>` : '') +
          note('หมายเหตุ', ins.interior_note)
        );

        rightHtml += sec(
          '<i class="bx bx-error-alt"></i>', 'rose', '4. ปัญหาที่พบ / วิธีแก้ไข',
          ins.issues_resolved,
          note('รายละเอียดปัญหาและวิธีแก้ไข', ins.issues_detail) +
          note('เหตุผล (ถ้าไม่ได้ตรวจ)', ins.issues_reason)
        );

        if (ins.docs && ins.docs.length) {
          rightHtml += `
          <div class="po-section mb-3">
            <div class="po-section-header">
              <div class="po-section-icon indigo"><i class="bx bx-file"></i></div>
              <h6 class="po-section-title">5. ใบตรวจสอบรถ</h6>
            </div>
            <div class="po-section-body d-flex flex-wrap">
              ${ins.docs.map(f => buildViewMediaItem(f, id)).join('')}
            </div>
          </div>`;
        }

        if (ins.photos && ins.photos.length) {
          rightHtml += `
          <div class="po-section">
            <div class="po-section-header">
              <div class="po-section-icon pink"><i class="bx bx-camera"></i></div>
              <h6 class="po-section-title">6. รูปถ่าย SC ที่ตรวจรถ</h6>
            </div>
            <div class="po-section-body d-flex flex-wrap">
              ${ins.photos.map(f => buildViewMediaItem(f, id)).join('')}
            </div>
          </div>`;
        }

        if (ins.logs && ins.logs.length) {
          rightHtml += `
          <div class="po-section mt-3">
            <div class="po-section-header">
              <div class="po-section-icon" style="background:#6b7280;"><i class="bx bx-history"></i></div>
              <h6 class="po-section-title">ประวัติรายการที่ไม่เรียบร้อย</h6>
            </div>
            <div class="po-section-body p-0">
              ${ins.logs.map((log, i) => buildLogEntry(log, i + 1)).join('')}
            </div>
          </div>`;
        }

        return `<div class="row g-3"><div class="col-md-4">${leftHtml}</div><div class="col-md-8">${rightHtml}</div></div>`;
      }

      // ── Export Excel ──
      $('#btnExportPdi').on('click', function () {
        const date = $('#pdiExportDate').val();
        if (!date) { alert('กรุณาเลือกวันที่'); return; }
        window.location.href = '{{ route('pre-delivery-inspection.export') }}?date=' + date;
      });

    });
  </script>
@endsection

@section('content')

  <div class="row">
    <div class="col-12">
      <div class="card tbl-card">

        {{-- ── Card header ── --}}
        <div class="po-card-header d-flex align-items-center gap-3">
          <div class="po-hd-icon">
            <i class="bx bx-check-shield fs-4 text-white"></i>
          </div>
          <div>
            <div class="text-white fw-bold mf-hd-title">รายการตรวจรถก่อนส่งมอบ</div>
            <div class="text-white mf-hd-sub">Pre-Delivery Inspection</div>
          </div>
        </div>

        <div class="card-body pt-3">

          {{-- ── Action bar ── --}}
          <div class="po-filter-bar d-flex align-items-center gap-2 justify-content-end">
            <input type="date" id="pdiExportDate" class="form-control form-control-sm"
              value="{{ now()->format('Y-m-d') }}" style="width:155px;">
            <button type="button" id="btnExportPdi" class="btn btn-warning btn-sm">
              <i class="bx bx-file me-1"></i> รายงาน
            </button>
          </div>

          <div class="table-responsive">
            <table class="table table-bordered tbl-table tbl-styled" id="preDeliveryTable">
              <thead>
                <tr>
                  <th class="tbl-th-no">No.</th>
                  <th>ชื่อ - นามสกุลลูกค้า</th>
                  <th>ฝ่ายขาย</th>
                  <th>รุ่นรถ</th>
                  <th class="text-center">วันที่ส่งมอบ</th>
                  <th class="text-center">สถานะ</th>
                  <th class="tbl-th-action" style="width:180px;">Action</th>
                </tr>
              </thead>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>

  {{-- Modal ตรวจรถก่อนส่งมอบ --}}
  {{-- <div class="modal fade" id="modalInspection" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content border-0 shadow mf-content mf-content--edit"> --}}

  <div class="modal fade" id="modalInspection" tabindex="-1" role="dialog" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content border-0 shadow mf-content mf-content--edit">

        <div class="modal-header mf-header mf-header--edit px-4">
          <div class="d-flex align-items-center gap-3">
            <div class="mf-hd-icon"><i class="bx bx-car fs-5 text-white"></i></div>
            <div>
              <h6 class="mb-0 fw-bold text-white mf-hd-title" id="modalTitle">ตรวจรถก่อนส่งมอบ</h6>
              <small class="text-white mf-hd-sub">Pre-Delivery Inspection</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <input type="hidden" id="modalSalecarId">
        <input type="hidden" id="modalInspectionId">

        <div class="modal-body px-4">

          {{-- ── 1. อุปกรณ์ตกแต่ง ── --}}
          <div class="po-section mb-3">
            <div class="po-section-header">
              <div class="po-section-icon sky"><i class="bx bx-wrench"></i></div>
              <h6 class="po-section-title">1. อุปกรณ์ตกแต่ง</h6>
            </div>
            <div class="po-section-body">
              <p class="po-label mb-2">รายการอุปกรณ์ตกแต่ง ครบตามเอกสาร และติดตั้งเรียบร้อยหรือไม่?</p>
              <div class="d-flex gap-4 mb-2">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="accessories_complete" id="acc_ok"
                    value="1">
                  <label class="form-check-label text-success fw-semibold" for="acc_ok">
                    <i class="bx bx-check-circle me-1"></i> เรียบร้อย
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="accessories_complete" id="acc_no"
                    value="0">
                  <label class="form-check-label text-danger fw-semibold" for="acc_no">
                    <i class="bx bx-x-circle me-1"></i> ไม่เรียบร้อย
                  </label>
                </div>
              </div>
              <div id="rowAccessoriesIncomplete" style="display:none;">
                <label class="po-label" for="accessories_incomplete_items">ระบุชิ้นงานที่ไม่เรียบร้อย</label>
                <textarea id="accessories_incomplete_items" class="form-control mb-2" rows="2" placeholder="ระบุรายการ..."></textarea>
              </div>
              <label class="po-label" for="accessories_note">หมายเหตุ</label>
              <textarea id="accessories_note" class="form-control" rows="2" placeholder="หมายเหตุเพิ่มเติม..."></textarea>
            </div>
          </div>

          {{-- ── 2. ความสะอาดภายนอก ── --}}
          <div class="po-section mb-3">
            <div class="po-section-header">
              <div class="po-section-icon emerald"><i class="bx bx-sun"></i></div>
              <h6 class="po-section-title">2. ความสะอาดภายนอก</h6>
            </div>
            <div class="po-section-body">
              <p class="po-label mb-2">ตรวจสอบความสะอาดตัวรถ ภายนอก กระจกไม่มีรอย เรียบร้อยหรือไม่?</p>
              <div class="d-flex gap-4 mb-2">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="exterior_clean" id="ext_ok" value="1">
                  <label class="form-check-label text-success fw-semibold" for="ext_ok">
                    <i class="bx bx-check-circle me-1"></i> เรียบร้อย
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="exterior_clean" id="ext_no" value="0">
                  <label class="form-check-label text-danger fw-semibold" for="ext_no">
                    <i class="bx bx-x-circle me-1"></i> ไม่เรียบร้อย
                  </label>
                </div>
              </div>
              <div id="rowExteriorIncomplete" style="display:none;">
                <label class="po-label" for="exterior_incomplete_items">ระบุรายละเอียดที่ไม่เรียบร้อย</label>
                <textarea id="exterior_incomplete_items" class="form-control mb-2" rows="2" placeholder="ระบุรายการ..."></textarea>
              </div>
              <label class="po-label" for="exterior_note">หมายเหตุ</label>
              <textarea id="exterior_note" class="form-control" rows="2" placeholder="หมายเหตุเพิ่มเติม..."></textarea>
            </div>
          </div>

          {{-- ── 3. ความสะอาดภายใน ── --}}
          <div class="po-section mb-3">
            <div class="po-section-header">
              <div class="po-section-icon amber"><i class="bx bx-shield-quarter"></i></div>
              <h6 class="po-section-title">3. ความสะอาดภายใน</h6>
            </div>
            <div class="po-section-body">
              <p class="po-label mb-2">ตรวจสอบความสะอาดภายใน ไม่มีฝุ่น และมีอุปกรณ์แตกหัก เรียบร้อยหรือไม่?</p>
              <div class="d-flex gap-4 mb-2">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="interior_clean" id="int_ok" value="1">
                  <label class="form-check-label text-success fw-semibold" for="int_ok">
                    <i class="bx bx-check-circle me-1"></i> เรียบร้อย
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="interior_clean" id="int_no" value="0">
                  <label class="form-check-label text-danger fw-semibold" for="int_no">
                    <i class="bx bx-x-circle me-1"></i> ไม่เรียบร้อย
                  </label>
                </div>
              </div>
              <div id="rowInteriorIncomplete" style="display:none;">
                <label class="po-label" for="interior_incomplete_items">ระบุรายละเอียดที่ไม่เรียบร้อย</label>
                <textarea id="interior_incomplete_items" class="form-control mb-2" rows="2" placeholder="ระบุรายการ..."></textarea>
              </div>
              <label class="po-label" for="interior_note">หมายเหตุ</label>
              <textarea id="interior_note" class="form-control" rows="2" placeholder="หมายเหตุเพิ่มเติม..."></textarea>
            </div>
          </div>

          {{-- ── 4. ปัญหาที่พบ / วิธีแก้ไข ── --}}
          <div class="po-section mb-3">
            <div class="po-section-header">
              <div class="po-section-icon rose"><i class="bx bx-error-alt"></i></div>
              <h6 class="po-section-title">4. ปัญหาที่พบ / วิธีแก้ไข</h6>
            </div>
            <div class="po-section-body">
              <p class="po-label mb-2">ระบุปัญหาที่พบ และวิธีแก้ไข (หรือถ้าไม่ได้ตรวจ ให้ระบุเหตุผล)</p>
              <div class="d-flex gap-4 mb-2">
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="issues_resolved" id="iss_ok"
                    value="1">
                  <label class="form-check-label text-success fw-semibold" for="iss_ok">
                    <i class="bx bx-check-circle me-1"></i> เรียบร้อย
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="issues_resolved" id="iss_no"
                    value="0">
                  <label class="form-check-label text-danger fw-semibold" for="iss_no">
                    <i class="bx bx-x-circle me-1"></i> ไม่เรียบร้อย / ไม่ได้ตรวจ
                  </label>
                </div>
              </div>
              <label class="po-label" for="issues_detail">รายละเอียดปัญหาและวิธีแก้ไข</label>
              <textarea id="issues_detail" class="form-control mb-2" rows="2" placeholder="ระบุปัญหาและวิธีแก้ไข..."></textarea>
              <label class="po-label" for="issues_reason">เหตุผล (ถ้าไม่ได้ตรวจ)</label>
              <textarea id="issues_reason" class="form-control" rows="2" placeholder="เหตุผลที่ไม่ได้ตรวจ..."></textarea>
            </div>
          </div>

          {{-- ── 5. ใบตรวจสอบรถ ── --}}
          <div class="po-section mb-3">
            <div class="po-section-header">
              <div class="po-section-icon indigo"><i class="bx bx-file"></i></div>
              <h6 class="po-section-title">5. แนบใบตรวจสอบรถ</h6>
            </div>
            <div class="po-section-body">
              <div id="existingDocs" class="d-flex flex-wrap mb-2"></div>
              <input type="file" id="inspection_docs_input" class="form-control mb-1" multiple
                accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
              <div id="newDocsPreview" class="d-flex flex-wrap mt-1"></div>
            </div>
          </div>

          {{-- ── 6. รูปถ่าย SC ── --}}
          <div class="po-section">
            <div class="po-section-header">
              <div class="po-section-icon pink"><i class="bx bx-camera"></i></div>
              <h6 class="po-section-title">6. รูปถ่าย SC ที่ตรวจรถ</h6>
            </div>
            <div class="po-section-body">
              <div id="existingPhotos" class="d-flex flex-wrap mb-2"></div>
              <input type="file" id="inspection_photos_input" class="form-control mb-1" multiple
                accept="image/*,.pdf">
              <div id="newPhotosPreview" class="d-flex flex-wrap mt-1"></div>
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
              <i class="bx bx-x me-1"></i> ปิด
            </button>
            <button type="button" class="btn btn-primary" id="btnSaveInspection">
              <i class="bx bx-save me-1"></i> บันทึก
            </button>
          </div>

        </div>{{-- /modal-body --}}
      </div>
    </div>
  </div>

  {{-- ── --}}
  {{-- Modal ดูข้อมูลตรวจรถก่อนส่งมอบ  --}}
  {{-- ── --}}
  <div class="modal fade" id="modalViewInspection" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content border-0 shadow mf-content mf-content--view">

        <div class="modal-header mf-header mf-header--view px-4">
          <div class="d-flex align-items-center gap-3">
            <div class="mf-hd-icon">
              <i class="bx bx-show fs-5 text-white"></i>
            </div>
            <div>
              <h6 class="mb-0 fw-bold text-white mf-hd-title" id="viewModalTitle">ดูข้อมูลตรวจรถก่อนส่งมอบ</h6>
              <small class="text-white mf-hd-sub">Pre-Delivery Inspection</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
            aria-label="Close"></button>
        </div>

        <div class="modal-body px-4" id="viewModalBody">
          <div class="text-center py-5">
            <i class="bx bx-loader-alt bx-spin fs-2 text-secondary"></i>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bx bx-x me-1"></i> ปิด
          </button>
        </div>

      </div>
    </div>
  </div>

@endsection
