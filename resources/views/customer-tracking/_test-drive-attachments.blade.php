{{-- หลักฐานทดลองขับ — ใช้ร่วมกันระหว่างหน้า view-more ของการติดตาม และหน้าแก้ไขใบจอง
     ตัวแปรที่รับ : $tracking (CustomerTracking) , $tdReadonly (bool, ค่าเริ่มต้น false = แนบ/ลบได้)
     รายการไฟล์ถูกวาดด้วย JS จาก data-items เพื่อให้ตอนบันทึก/ลบ วาดใหม่ได้ด้วย renderer ตัวเดียวกัน --}}
@php
    $tdReadonly = $tdReadonly ?? false;
    $tdItems = collect(is_array($tracking->test_drive_attachments) ? $tracking->test_drive_attachments : [])
        ->values()
        ->map(function ($item, $i) use ($tracking) {
            $url = is_array($item) ? $item['url'] ?? '' : $item;
            $name = is_array($item) ? $item['name'] ?? null : null;
            $base = route('customer-tracking.test-drive.proxy', $tracking->id);

            return [
                'index' => $i,
                'name' => $name,
                'ext' => $name ? strtolower(pathinfo($name, PATHINFO_EXTENSION)) : null,
                'url' => $name
                    ? $base . '/' . rawurlencode($name) . '?url=' . urlencode($url)
                    : $base . '?url=' . urlencode($url),
            ];
        });
@endphp

<div class="td-attach-wrap" data-tracking-id="{{ $tracking->id }}"
    data-delete-url="{{ route('customer-tracking.test-drive.delete-attachment', $tracking->id) }}"
    data-readonly="{{ $tdReadonly ? '1' : '0' }}" data-items='@json($tdItems, JSON_UNESCAPED_UNICODE)'>

    <div class="po-label mb-2"><i class="bx bx-images me-1"></i> หลักฐานทดลองขับ</div>

    <div class="td-attach-list d-flex flex-wrap gap-2 mb-2"></div>
    <div class="td-attach-empty text-muted mb-2" style="font-size:.8rem;">
        <i class="bx bx-info-circle me-1"></i>ยังไม่มีไฟล์แนบ
    </div>

    @unless ($tdReadonly)
        <div class="upload-area" style="max-width:520px;">
            <input type="file" class="form-control border-0 bg-transparent p-0 td-attach-input" name="attachments[]"
                accept=".pdf,.jpg,.jpeg,.png" multiple>
            <small class="text-muted mt-1 d-block">
                <i class="bx bx-info-circle me-1"></i>รองรับ PDF, JPG, PNG — ไฟล์ละไม่เกิน 10 MB แนบได้หลายไฟล์
                (ไฟล์จะถูกอัปโหลดเมื่อกดปุ่มบันทึก)
            </small>
            <div class="td-attach-preview mt-2 d-flex flex-wrap gap-2"></div>
        </div>
    @endunless
</div>
