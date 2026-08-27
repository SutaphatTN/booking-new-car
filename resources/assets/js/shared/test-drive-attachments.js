// หลักฐานทดลองขับ — ตัวช่วยที่ใช้ร่วมกันระหว่างหน้า view-more ของการติดตาม และหน้าแก้ไขใบจอง
// อยู่ในโฟลเดอร์ shared/ เพราะ vite ทำ entry จาก resources/assets/js/*.js เท่านั้น (ไม่ไล่ subfolder)
// ไฟล์นี้จึงถูก import เข้าไปรวมในทั้งสอง bundle แทนที่จะกลายเป็น entry ของตัวเอง

const IMG_EXTS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];

const EXT_BG = {
  pdf: '#ef4444',
  xlsx: '#16a34a',
  xls: '#16a34a',
  csv: '#16a34a',
  doc: '#2563eb',
  docx: '#2563eb',
  ppt: '#ea580c',
  pptx: '#ea580c',
  zip: '#7c3aed',
  rar: '#7c3aed',
  '7z': '#7c3aed'
};

function escapeHtml(text) {
  return $('<div>').text(text == null ? '' : text).html();
}

function fileTile(url, ext, uid) {
  const bg = EXT_BG[ext] || '#6366f1';
  const label = ext ? ext.toUpperCase() : 'FILE';
  const tile =
    `<i class="bx bx-file" style="font-size:1.8rem;"></i>` +
    `<span class="badge bg-white mt-1" style="font-size:.6rem;color:${bg};font-weight:700;">${label}</span>`;

  // รูป: โชว์ thumbnail ก่อน ถ้าโหลดไม่ขึ้น (เช่นไฟล์เสีย) ค่อยสลับไปแสดงเป็นการ์ดไฟล์แทน
  if (IMG_EXTS.includes(ext)) {
    return (
      `<a href="${url}" target="_blank" id="tdimg-${uid}" style="display:block;">` +
      `<img src="${url}" class="rounded border" style="width:80px;height:80px;object-fit:cover;cursor:pointer;"` +
      ` onerror="document.getElementById('tdimg-${uid}').style.display='none';document.getElementById('tdfile-${uid}').style.display='flex';"></a>` +
      `<a href="${url}" target="_blank" id="tdfile-${uid}" class="text-decoration-none"` +
      ` style="display:none;width:80px;height:80px;border-radius:0.375rem;background:${bg};flex-direction:column;align-items:center;justify-content:center;color:white;">${tile}</a>`
    );
  }

  return (
    `<a href="${url}" target="_blank" class="d-flex flex-column align-items-center justify-content-center rounded text-white text-decoration-none"` +
    ` style="width:80px;height:80px;background:${bg};">${tile}</a>`
  );
}

function renderList($wrap, items) {
  const list = items || [];
  const readonly = $wrap.data('readonly') === 1 || $wrap.data('readonly') === '1';
  const trackingId = $wrap.data('tracking-id');

  const html = list
    .map((item, i) => {
      const uid = `${trackingId}-${i}`;
      const name = item.name ? escapeHtml(item.name) : '';
      const nameRow = name
        ? `<div class="text-truncate text-center text-dark mt-1" style="font-size:.7rem;max-width:80px;" title="${name}">${name}</div>`
        : '';
      const delBtn = readonly
        ? ''
        : `<button type="button" class="btn btn-danger td-attach-delete position-absolute top-0 end-0"` +
          ` style="font-size:.8rem;line-height:1;padding:2px 5px;" title="ลบไฟล์นี้"><i class="bx bx-x"></i></button>`;

      return (
        `<div class="td-attach-item position-relative d-inline-block m-1" style="width:80px;vertical-align:top;" data-index="${item.index != null ? item.index : i}">` +
        fileTile(item.url, item.ext, uid) +
        nameRow +
        delBtn +
        `</div>`
      );
    })
    .join('');

  $wrap.find('.td-attach-list').html(html);
  $wrap.find('.td-attach-empty').toggle(list.length === 0);
}

/** ไฟล์ที่ผู้ใช้เพิ่งเลือก (ยังไม่อัปโหลด) ของ card นี้ */
export function testDriveFiles($wrap) {
  const input = $wrap.find('.td-attach-input')[0];
  return input && input.files ? Array.from(input.files) : [];
}

/** ล้างช่องเลือกไฟล์ + preview หลังบันทึกสำเร็จ */
export function clearTestDriveInput($wrap) {
  $wrap.find('.td-attach-input').val('');
  $wrap.find('.td-attach-preview').empty();
}

/** วาดรายการไฟล์ใหม่จาก payload ที่ server ส่งกลับมา */
export function renderTestDriveAttachments($wrap, items) {
  renderList($wrap, items);
}

/**
 * ผูก event ของ card หลักฐานทดลองขับทุกตัวในหน้า (preview ไฟล์ที่เลือก + ปุ่มลบ)
 * เรียกครั้งเดียวตอนโหลดหน้า
 */
export function initTestDriveAttachments() {
  $('.td-attach-wrap').each(function () {
    renderList($(this), $(this).data('items') || []);
  });

  // preview ไฟล์ที่เพิ่งเลือก — ยังไม่อัปโหลดจนกว่าจะกดบันทึก
  $(document).on('change', '.td-attach-input', function () {
    const $preview = $(this).closest('.td-attach-wrap').find('.td-attach-preview');
    $preview.empty();

    Array.from(this.files || []).forEach(file => {
      const ext = (file.name.split('.').pop() || '').toLowerCase();
      const name = escapeHtml(file.name);

      if (IMG_EXTS.includes(ext)) {
        const url = URL.createObjectURL(file);
        $preview.append(
          `<div class="text-center" style="width:80px;">` +
            `<img src="${url}" class="rounded border" style="width:80px;height:80px;object-fit:cover;">` +
            `<div class="text-truncate text-muted mt-1" style="font-size:.7rem;" title="${name}">${name}</div>` +
            `</div>`
        );
      } else {
        const bg = EXT_BG[ext] || '#6366f1';
        $preview.append(
          `<div class="text-center" style="width:80px;">` +
            `<div class="d-flex flex-column align-items-center justify-content-center rounded text-white" style="width:80px;height:80px;background:${bg};">` +
            `<i class="bx bx-file" style="font-size:1.8rem;"></i>` +
            `<span class="badge bg-white mt-1" style="font-size:.6rem;color:${bg};font-weight:700;">${ext ? ext.toUpperCase() : 'FILE'}</span>` +
            `</div>` +
            `<div class="text-truncate text-muted mt-1" style="font-size:.7rem;" title="${name}">${name}</div>` +
            `</div>`
        );
      }
    });
  });

  $(document).on('click', '.td-attach-delete', function () {
    const $wrap = $(this).closest('.td-attach-wrap');
    const index = $(this).closest('.td-attach-item').data('index');

    Swal.fire({
      title: 'ลบไฟล์นี้?',
      text: 'ไฟล์จะถูกเอาออกจากรายการหลักฐานทดลองขับ',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'ลบ',
      cancelButtonText: 'ยกเลิก',
      confirmButtonColor: '#dc3545'
    }).then(result => {
      if (!result.isConfirmed) return;

      $.ajax({
        url: $wrap.data('delete-url'),
        type: 'DELETE',
        data: { index: index },
        success: function (res) {
          renderList($wrap, res.attachments || []);
          Swal.fire({ icon: 'success', title: 'ลบไฟล์แล้ว', timer: 1200, showConfirmButton: false });
        },
        error: function () {
          Swal.fire({ icon: 'error', title: 'ลบไม่สำเร็จ', text: 'กรุณาลองใหม่' });
        }
      });
    });
  });
}

/** รวมข้อมูล card ทดลองขับเป็น FormData (วันที่ + หมายเหตุ + ไฟล์ที่เพิ่งเลือก) */
export function testDriveFormData($wrap, { date, note }) {
  const fd = new FormData();
  fd.append('test_drive_date', date || '');
  fd.append('test_drive_note', note || '');
  testDriveFiles($wrap).forEach(file => fd.append('attachments[]', file));
  return fd;
}
