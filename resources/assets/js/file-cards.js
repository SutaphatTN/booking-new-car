// ──────────────────────────────────────────────────────────────────────────────
// การ์ดไฟล์แนบ — ตัวกลางตัวเดียวของทั้งระบบ
//
// ใช้คู่กับ blade partial `_partials.file-cards` (มาร์กอัปต้องตรงกันเป๊ะ)
//  - ฝั่ง blade  : ไฟล์ที่ render มาพร้อมหน้า
//  - ฝั่ง JS     : ไฟล์ที่วาดด้วย ajax / วาดใหม่หลังบันทึก / พรีวิวไฟล์ที่เพิ่งเลือก
//
// ไฟล์นี้อยู่ระดับบนสุดของ resources/assets/js จึงเป็น vite entry ในตัว
// (vite ทำ entry จาก resources/assets/js/*.js เท่านั้น) — เอาไปใช้ได้ 2 ทาง
//  1) หน้าที่มี bundle ของตัวเอง : import { ... } from './file-cards'
//  2) หน้าที่เขียน <script> สดในบเลด : @vite(['resources/assets/js/file-cards.js'])
//     แล้วเรียกผ่าน window.FileCards (โมดูลถูก defer จึงรันก่อน $(document).ready เสมอ)
//
// มาร์กอัปของการ์ด 1 ใบ :
//   <div class="fc-item ..." data-index data-url [data-delete-url] [data-stage]>
//     <a href=...>รูป 80px หรือการ์ดสีตามนามสกุล</a>
//     <div class="fc-name">ชื่อไฟล์</div>
//     <button class="fc-delete">×</button>
//   </div>
// ──────────────────────────────────────────────────────────────────────────────

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

const FALLBACK_BG = '#64748b';

function escapeHtml(text) {
  return $('<div>')
    .text(text == null ? '' : text)
    .html();
}

/** สี/ป้ายกำกับของไฟล์ตามนามสกุล — ใช้ชุดเดียวกันทั้งระบบ */
export function fileCardStyle(name) {
  const ext = String(name || '')
    .split('.')
    .pop()
    .toLowerCase();

  return {
    ext,
    bg: EXT_BG[ext] || FALLBACK_BG,
    label: ext ? ext.toUpperCase() : 'FILE',
    isImage: IMG_EXTS.includes(ext)
  };
}

/**
 * การ์ด 1 ใบของไฟล์ที่อัปโหลดแล้ว
 * @param {object} item  {url: ลิงก์จริงของไฟล์ (share url), href: ลิงก์ที่ใช้เปิด/แสดง (proxy), name, index}
 * @param {object} opts  {deleteUrl: ยิง ajax ลบ, stage: ลบแบบเอาออกจากจอรอกดบันทึก, readonly: ไม่มีปุ่มลบ}
 */
export function fileCardHtml(item, opts = {}) {
  const { deleteUrl = null, stage = false, readonly = false } = opts;

  const name = item.name || '';
  const href = item.href || item.url || '';
  const rawUrl = item.url || '';
  const index = item.index != null ? item.index : 0;
  const st = fileCardStyle(name);
  const uid = `fc${Math.random().toString(36).slice(2, 8)}`;
  const safeName = escapeHtml(name);

  const tile =
    `<i class="bx bx-file" style="font-size:1.8rem;"></i>` +
    `<span class="badge bg-white mt-1" style="font-size:.6rem;color:${st.bg};font-weight:700;">${st.label}</span>`;

  // รูป: โชว์ thumbnail ก่อน ถ้าโหลดไม่ขึ้น (ลิงก์หมดอายุ/ไฟล์เสีย) ค่อยสลับไปการ์ดไฟล์แทน
  const media = st.isImage
    ? `<a href="${href}" target="_blank" id="img-${uid}" style="display:block;" title="${safeName}">` +
      `<img src="${href}" class="rounded border" style="width:80px;height:80px;object-fit:cover;cursor:pointer;"` +
      ` onerror="document.getElementById('img-${uid}').style.display='none';document.getElementById('file-${uid}').style.display='flex';"></a>` +
      `<a href="${href}" target="_blank" id="file-${uid}" class="text-decoration-none"` +
      ` style="display:none;width:80px;height:80px;border-radius:.375rem;background:${st.bg};flex-direction:column;align-items:center;justify-content:center;color:#fff;">${tile}</a>`
    : `<a href="${href}" target="_blank" class="d-flex flex-column align-items-center justify-content-center rounded text-white text-decoration-none"` +
      ` style="width:80px;height:80px;background:${st.bg};" title="${safeName}">${tile}</a>`;

  const nameRow = safeName
    ? `<div class="fc-name text-truncate text-center text-dark mt-1" style="font-size:.7rem;max-width:80px;" title="${safeName}">${safeName}</div>`
    : '';

  const delBtn =
    readonly || (!deleteUrl && !stage)
      ? ''
      : `<button type="button" class="btn btn-danger fc-delete position-absolute top-0 end-0"` +
        ` style="font-size:.8rem;line-height:1;padding:2px 5px;" title="ลบไฟล์นี้"><i class="bx bx-x"></i></button>`;

  return (
    `<div class="fc-item position-relative d-inline-block m-1" style="width:80px;vertical-align:top;"` +
    ` data-index="${index}" data-url="${escapeHtml(rawUrl)}"` +
    (deleteUrl ? ` data-delete-url="${deleteUrl}"` : '') +
    (stage ? ' data-stage="1"' : '') +
    `>${media}${nameRow}${delBtn}</div>`
  );
}

/** วาดรายการไฟล์ทั้งชุดลงใน container (ล้างของเดิมทิ้ง) */
export function renderFileCards($container, items, opts = {}) {
  const list = items || [];

  $container.html(
    list.map((item, i) => fileCardHtml({ index: i, ...item }, opts)).join('')
  );

  return list.length;
}

/** ไล่เลข data-index ใหม่หลังมีการ์ดถูกลบ — ไม่งั้นรอบถัดไปจะลบผิดไฟล์ */
export function reindexFileCards($container) {
  $container.find('.fc-item').each(function (i) {
    $(this).attr('data-index', i).data('index', i);
  });
}

/**
 * พรีวิวไฟล์ที่ผู้ใช้เพิ่งเลือก (ยังไม่อัปโหลด) — กดกากบาทเอาออกทีละไฟล์ได้
 * ตัดไฟล์ออกจาก input จริงผ่าน DataTransfer ไม่งั้นมันยังถูกส่งไปด้วยตอนบันทึก
 */
export function renderFilePreviews(input, $preview) {
  $preview.empty();

  Array.from(input.files || []).forEach(function (file, idx) {
    const isImg = /image/i.test(file.type);
    const st = fileCardStyle(file.name);
    const safeName = escapeHtml(file.name);

    const media = isImg
      ? `<img src="${URL.createObjectURL(file)}" class="rounded border" style="width:80px;height:80px;object-fit:cover;">`
      : `<div class="d-flex flex-column align-items-center justify-content-center rounded text-white" style="width:80px;height:80px;background:${st.bg};">
           <i class="bx bx-file" style="font-size:1.8rem;"></i>
           <span class="badge bg-white mt-1" style="font-size:.6rem;color:${st.bg};font-weight:700;">${st.label}</span>
         </div>`;

    const $item = $(
      `<div class="fc-new position-relative d-inline-block m-1" style="width:80px;vertical-align:top;">
        ${media}
        <div class="fc-name text-truncate text-center text-dark mt-1" style="font-size:.7rem;max-width:80px;" title="${safeName}">${safeName}</div>
        <button type="button" class="btn btn-danger fc-remove-new position-absolute top-0 end-0" style="font-size:.8rem;line-height:1;padding:2px 5px;" title="เอาไฟล์นี้ออก"><i class="bx bx-x"></i></button>
      </div>`
    );

    $item.find('.fc-remove-new').on('click', function () {
      const dt = new DataTransfer();
      Array.from(input.files).forEach((f, i) => {
        if (i !== idx) dt.items.add(f);
      });
      input.files = dt.files;
      renderFilePreviews(input, $preview);
      $(input).trigger('fc:files-changed');
    });

    $preview.append($item);
  });
}

/**
 * ผูกช่องเลือกไฟล์กับกล่องพรีวิว — ผูกที่ document ช่องที่โหลดมาทีหลัง (โมดัล ajax) จึงใช้ได้ด้วย
 * @param {Array<[string,string]>} pairs  [[inputId, previewId], ...]
 */
export function bindFilePreviews(pairs) {
  pairs.forEach(function ([inputId, previewId]) {
    $(document).on('change', '#' + inputId, function () {
      renderFilePreviews(this, $('#' + previewId));
    });
  });
}

// ── ปุ่มลบบนการ์ด (ผูกครั้งเดียวทั้งหน้า) ──────────────────────────────────
// 2 โหมด :
//   data-stage="1"    = เอาออกจากจอเฉย ๆ มีผลจริงตอนกดบันทึก (หน้าที่ส่งรายการที่เหลือไปทั้งชุด)
//   data-delete-url   = ยิง DELETE ทันที พร้อม index ของไฟล์
// ทั้ง 2 โหมดจะ trigger 'fc:removed' ที่ container ให้หน้าที่ต้องทำอะไรต่อ (เช่นเก็บ url ที่ถูกลบ) ดักเอาเอง
//
// ใช้ namespace + off ก่อน on : หน้าไหนโหลดโมดูลนี้ 2 ทาง (จาก @vite ตรง ๆ และจาก bundle ของหน้า)
// จะได้ไม่ผูกซ้ำจนกดลบทีเดียวแล้วยิง DELETE สองครั้ง
$(document).off('click.fileCards', '.fc-delete');
$(document).on('click.fileCards', '.fc-delete', function () {
  const $item = $(this).closest('.fc-item');
  const $container = $item.parent();
  const url = $item.data('delete-url');
  const index = $item.data('index');
  const rawUrl = $item.data('url');

  const notify = () =>
    $container.trigger('fc:removed', [
      { url: rawUrl, index: index, remaining: $container.find('.fc-item').length }
    ]);

  if ($item.data('stage')) {
    $item.remove();
    reindexFileCards($container);
    notify();
    return;
  }

  if (!url) return;

  Swal.fire({
    title: 'ลบไฟล์นี้?',
    text: 'ไฟล์จะถูกเอาออกจากรายการ',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#6c5ffc',
    cancelButtonColor: '#d33',
    confirmButtonText: 'ใช่, ลบเลย!',
    cancelButtonText: 'ยกเลิก'
  }).then(function (result) {
    if (!result.isConfirmed) return;

    $.ajax({
      url: url,
      type: 'DELETE',
      data: { index: index },
      success: function () {
        $item.remove();
        reindexFileCards($container);
        notify();
        Swal.fire({ icon: 'success', title: 'ลบไฟล์แล้ว', timer: 1200, showConfirmButton: true });
      },
      error: function (xhr) {
        Swal.fire({
          icon: xhr.status === 422 ? 'warning' : 'error',
          title: xhr.status === 422 ? 'ลบไฟล์นี้ไม่ได้' : 'ลบไม่สำเร็จ',
          text: xhr.responseJSON?.message ?? 'กรุณาลองใหม่'
        });
      }
    });
  });
});

// หน้าที่เขียน <script> สดในบเลด (ตรวจรถก่อนส่งมอบ / เคลมแคมเปญ) เรียกผ่านตัวนี้
window.FileCards = {
  fileCardStyle,
  fileCardHtml,
  renderFileCards,
  reindexFileCards,
  renderFilePreviews,
  bindFilePreviews
};
