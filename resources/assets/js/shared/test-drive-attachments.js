// หลักฐานทดลองขับ — ตัวช่วยที่ใช้ร่วมกันระหว่างหน้า view-more ของการติดตาม และหน้าแก้ไขใบจอง
// อยู่ในโฟลเดอร์ shared/ เพราะ vite ทำ entry จาก resources/assets/js/*.js เท่านั้น (ไม่ไล่ subfolder)
// ไฟล์นี้จึงถูก import เข้าไปรวมในทั้งสอง bundle แทนที่จะกลายเป็น entry ของตัวเอง
//
// การ์ดไฟล์ + พรีวิว + ปุ่มลบ ใช้ตัวกลางของระบบ (../file-cards) ไม่ได้วาดมาร์กอัปเอง
// ที่เหลือในไฟล์นี้คือเรื่องเฉพาะของหลักฐานทดลองขับ : รวม FormData และวาดใหม่หลังบันทึก

import { renderFileCards, renderFilePreviews } from '../file-cards';

function renderList($wrap, items) {
  const list = items || [];
  const readonly = $wrap.data('readonly') === 1 || $wrap.data('readonly') === '1';

  renderFileCards(
    $wrap.find('.td-attach-list'),
    // data-items จากบเลดส่ง url มาเป็นลิงก์ proxy อยู่แล้ว ใช้เป็น href ตรง ๆ
    list.map(item => ({ name: item.name, href: item.url, url: item.url })),
    { deleteUrl: readonly ? null : $wrap.data('delete-url'), readonly }
  );

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
 * ผูก event ของ card หลักฐานทดลองขับทุกตัวในหน้า (preview ไฟล์ที่เลือก + ซ่อนข้อความ "ยังไม่มีไฟล์แนบ")
 * ปุ่มลบบนการ์ดเป็นของ file-cards.js — ที่นี่แค่ดัก 'fc:removed' เพื่ออัปเดตข้อความว่างเปล่า
 * เรียกครั้งเดียวตอนโหลดหน้า
 */
export function initTestDriveAttachments() {
  $('.td-attach-wrap').each(function () {
    renderList($(this), $(this).data('items') || []);
  });

  // preview ไฟล์ที่เพิ่งเลือก — ยังไม่อัปโหลดจนกว่าจะกดบันทึก
  $(document).on('change', '.td-attach-input', function () {
    renderFilePreviews(this, $(this).closest('.td-attach-wrap').find('.td-attach-preview'));
  });

  $(document).on('fc:removed', '.td-attach-list', function (e, info) {
    $(this).closest('.td-attach-wrap').find('.td-attach-empty').toggle(info.remaining === 0);
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
