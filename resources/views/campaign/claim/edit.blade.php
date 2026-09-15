@php
  $claim = $sc->claim;
  $cus = $sc->saleCar?->customer;
  $customer = $cus ? trim(($cus->FirstName ?? '') . ' ' . ($cus->LastName ?? '')) : '-';
  $customer = $customer !== '' ? $customer : '-';
  $used = (float) ($sc->CashSupportFinal ?? 0);
  // ไฟล์แนบเก็บบน OneDrive — เปิดผ่าน proxy ของระบบ (share link เป็นของ organization)
  $attachments = (array) ($claim?->attachments ?? []);
@endphp
<div class="modal fade editClaim" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--input">

      {{-- Header --}}
      <div class="modal-header mf-header mf-header--input px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-receipt fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">ข้อมูลการเคลมแคมเปญ</h6>
            <small class="text-white mf-hd-sub">Campaign Claim</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body mf-body">
        <form action="{{ route('campaign.claim.update', $sc->id) }}" method="POST" enctype="multipart/form-data">
          @csrf

          {{-- Section 1 : ข้อมูลแคมเปญ (อ่านอย่างเดียว) --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon indigo">
                <i class="bx bx-purchase-tag"></i>
              </div>
              <span class="mf-section-title">ข้อมูลแคมเปญ</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="mf-label form-label"><i class="bx bx-user"></i> ลูกค้า</label>
                  <input type="text" class="form-control" value="{{ $customer }}" readonly>
                </div>
                <div class="col-md-6">
                  <label class="mf-label form-label"><i class="bx bx-list-ul"></i> ประเภทแคมเปญ</label>
                  <input type="text" class="form-control" value="{{ $sc->campaignType?->name ?? '-' }}" readonly>
                </div>
                <div class="col-md-4">
                  <label class="mf-label form-label"><i class="bx bx-calendar"></i> วันที่ส่งมอบ</label>
                  <input type="text" class="form-control" value="{{ $sc->saleCar?->format_delivery_date ?? '-' }}" readonly>
                </div>
                <div class="col-md-4">
                  <label class="mf-label form-label"><i class="bx bx-barcode"></i> Vin Number</label>
                  <input type="text" class="form-control" value="{{ $sc->saleCar?->carOrder?->vin_number ?? '-' }}" readonly>
                </div>
                <div class="col-md-4">
                  <label class="mf-label form-label"><i class="bx bx-wallet"></i> ยอดแคมเปญที่ใช้</label>
                  <div class="input-group">
                    <span class="input-group-text ig-indigo">฿</span>
                    <input id="claim_used" type="text" class="form-control text-end"
                      value="{{ number_format($used, 2) }}" data-raw="{{ $used }}" readonly>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- Section 2 : ข้อมูลการเคลม --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon amber">
                <i class="bx bx-money"></i>
              </div>
              <span class="mf-section-title">ข้อมูลการเคลม</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-4">
                  <label for="claim_amount" class="mf-label form-label">
                    <i class="bx bx-wallet ci-amber"></i> ยอดรับเคลม
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="claim_amount" type="text" class="form-control text-end money-input claim-amount"
                      name="claim_amount" placeholder="0.00"
                      value="{{ $claim && $claim->claim_amount !== null ? number_format($claim->claim_amount, 2) : '' }}">
                  </div>
                </div>

                <div class="col-md-4">
                  <label for="claim_diff" class="mf-label form-label">
                    <i class="bx bx-transfer ci-amber"></i> ยอด Diff
                    <span class="mf-label-note">(คำนวณอัตโนมัติ)</span>
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-slate">฿</span>
                    <input id="claim_diff" type="text" class="form-control text-end" placeholder="0.00" readonly>
                  </div>
                </div>

                <div class="col-md-4">
                  <label for="received_date" class="mf-label form-label">
                    <i class="bx bx-calendar-check ci-amber"></i> วันที่รับเงิน
                  </label>
                  <input id="received_date" type="date" class="form-control" name="received_date"
                    value="{{ $claim?->received_date }}">
                </div>

                <div class="col-md-12">
                  <label for="status_id" class="mf-label form-label">
                    <i class="bx bx-check-shield ci-amber"></i> สรุปผลการตรวจสอบ
                  </label>
                  <select id="status_id" name="status_id" class="form-select">
                    <option value="">— เลือกสถานะ —</option>
                    @foreach ($status as $s)
                      <option value="{{ $s->id }}" {{ $claim && $claim->status_id == $s->id ? 'selected' : '' }}>
                        {{ $s->name }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div class="col-md-12">
                  <label for="note" class="mf-label form-label">
                    <i class="bx bx-note ci-amber"></i> หมายเหตุ
                  </label>
                  <textarea id="note" name="note" class="form-control" rows="2"
                    placeholder="หมายเหตุเพิ่มเติม">{{ $claim?->note }}</textarea>
                </div>

              </div>
            </div>
          </div>

          {{-- Section 3 : ไฟล์แนบ (เก็บบน OneDrive → New Car/{แบรนด์}/Campaign Claim) --}}
          <div class="mf-section" id="claimFilesBox">
            <div class="mf-section-hd">
              <div class="mf-section-icon emerald">
                <i class="bx bx-paperclip"></i>
              </div>
              <span class="mf-section-title">ไฟล์แนบ</span>
              <span class="mf-label-note ms-2">(รูปภาพ / PDF / Word / Excel — ไฟล์ละไม่เกิน 20 MB)</span>
            </div>
            <div class="mf-section-body">

              {{-- ไฟล์ที่แนบไว้แล้ว — การ์ดชุดกลางของระบบ (_partials/file-cards.blade.php)
                   ส่ง keepName มา = โหมดลบแบบรอกดบันทึก : กด X แล้ว hidden input keep_files[] หายไปพร้อมการ์ด --}}
              <div id="claimExistingFiles" class="d-flex flex-wrap mb-2">
                @include('_partials.file-cards', [
                    'files' => $attachments,
                    'proxyBase' => route('campaign.claim.proxy', ['id' => $sc->id]),
                    'keepName' => 'keep_files[]',
                ])
              </div>

              <div id="claimNoFile" class="text-muted mb-2 {{ count($attachments) ? 'd-none' : '' }}"
                style="font-size:.85rem;">
                <i class="bx bx-info-circle me-1"></i>ยังไม่มีไฟล์แนบ
              </div>

              <input type="file" id="claim_files_input" name="claim_files[]" class="form-control" multiple
                accept="image/*,.pdf,.doc,.docx,.xls,.xlsx">
              <div id="claimNewFiles" class="d-flex flex-wrap mt-2"></div>
            </div>
          </div>

          {{-- Actions --}}
          <div class="d-flex justify-content-end gap-2 pt-1">
            <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
              <i class="bx bx-x me-1"></i>ยกเลิก
            </button>
            <button type="button" class="btn btn-primary px-5 btnUpdateClaim">
              <i class="bx bx-save me-1"></i>บันทึก
            </button>
          </div>

        </form>
      </div>

    </div>
  </div>
</div>

{{-- สคริปต์อยู่ในตัว modal เพราะ partial นี้ถูกโหลดด้วย AJAX ทุกครั้งที่กดแก้ไข
     (DOM เก่าถูกแทนทั้งก้อน handler เก่าจึงตายไปพร้อมกัน ไม่ผูกซ้ำ) --}}
<script>
  (function () {
    const $box = $('#claimFilesBox');
    if (!$box.length) return;

    const $input = $('#claim_files_input');
    const $newPreview = $('#claimNewFiles');

    // การ์ด / พรีวิว / ปุ่มลบ เป็นของ resources/assets/js/file-cards.js ทั้งหมด
    // (โหลดมากับ campaign-claim.js ที่ import โมดูลนี้ไว้ จึงมี window.FileCards ให้ใช้)
    // ที่เหลือในนี้คือเรื่องเฉพาะของเคลม : ข้อความ "ยังไม่มีไฟล์แนบ"
    function toggleEmptyNote() {
      const hasFile = $('#claimExistingFiles .fc-item').length > 0 || $input[0].files.length > 0;
      $('#claimNoFile').toggleClass('d-none', hasFile);
    }

    function renderNewFiles() {
      FileCards.renderFilePreviews($input[0], $newPreview);
      toggleEmptyNote();
    }

    // ลบไฟล์เดิม = เอาการ์ดออก (hidden input keep_files[] หายตาม) ไฟล์หลุดออกจริงตอนกดบันทึก
    $box.on('fc:removed', '#claimExistingFiles', toggleEmptyNote);
    // กากบาทบนพรีวิวไฟล์ใหม่ — โมดูลตัดไฟล์ออกจาก input ให้แล้ว เหลือแค่อัปเดตข้อความ
    $input.on('fc:files-changed', toggleEmptyNote);
    $input.on('change', renderNewFiles);
  })();
</script>
