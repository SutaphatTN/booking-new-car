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

              {{-- ไฟล์ที่แนบไว้แล้ว — กด X = เอาออกตอนกดบันทึก (hidden input หายไปพร้อมการ์ด) --}}
              <div id="claimExistingFiles" class="d-flex flex-wrap mb-2">
                @foreach ($attachments as $f)
                  @php
                    $fname = $f['name'] ?? 'file';
                    $furl = $f['url'] ?? '';
                    $isImage = (bool) preg_match('/\.(jpg|jpeg|png|gif|webp|bmp)$/i', $fname);
                    $proxy =
                        route('campaign.claim.proxy', ['id' => $sc->id, 'filename' => $fname]) .
                        '?url=' .
                        urlencode($furl);
                  @endphp
                  <div class="position-relative d-inline-block m-1" data-claim-file style="width:80px;">
                    <input type="hidden" name="keep_files[]" value="{{ $furl }}">
                    <a href="{{ $proxy }}" target="_blank" class="d-block text-decoration-none" title="{{ $fname }}">
                      @if ($isImage)
                        <img src="{{ $proxy }}" class="rounded border"
                          style="width:80px;height:80px;object-fit:cover;">
                      @else
                        <div class="d-flex flex-column align-items-center justify-content-center rounded text-white"
                          style="width:80px;height:80px;background:#64748b;">
                          <i class="bx bx-file" style="font-size:1.8rem;"></i>
                          <span class="badge bg-white mt-1" style="font-size:.6rem;color:#64748b;font-weight:700;">
                            {{ strtoupper(pathinfo($fname, PATHINFO_EXTENSION) ?: 'FILE') }}
                          </span>
                        </div>
                      @endif
                      <div class="text-truncate text-center text-dark mt-1" style="font-size:.7rem;max-width:80px;">
                        {{ $fname }}</div>
                    </a>
                    <button type="button"
                      class="btn btn-danger btn-claim-file-del position-absolute top-0 end-0"
                      style="font-size:.8rem;line-height:1;padding:2px 5px;" title="ลบไฟล์นี้">
                      <i class="bx bx-x"></i>
                    </button>
                  </div>
                @endforeach
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

    function toggleEmptyNote() {
      const hasFile = $('#claimExistingFiles [data-claim-file]').length > 0 || $input[0].files.length > 0;
      $('#claimNoFile').toggleClass('d-none', hasFile);
    }

    // ลบไฟล์เดิม — เอาการ์ดออก (hidden input keep_files[] หายตาม) ไฟล์จะหลุดออกตอนกดบันทึก
    $box.on('click', '.btn-claim-file-del', function () {
      $(this).closest('[data-claim-file]').remove();
      toggleEmptyNote();
    });

    // พรีวิวไฟล์ที่เพิ่งเลือก + กด X เอาออกก่อนบันทึกได้
    function renderNewFiles() {
      $newPreview.empty();

      Array.from($input[0].files).forEach(function (file, idx) {
        const isImg = /image/i.test(file.type);
        const ext = (file.name.split('.').pop() || '').toUpperCase();
        const thumb = isImg
          ? `<img src="${URL.createObjectURL(file)}" class="rounded border" style="width:80px;height:80px;object-fit:cover;">`
          : `<div class="d-flex flex-column align-items-center justify-content-center rounded text-white"
                  style="width:80px;height:80px;background:#0ea5e9;">
               <i class="bx bx-file" style="font-size:1.8rem;"></i>
               <span class="badge bg-white mt-1" style="font-size:.6rem;color:#0ea5e9;font-weight:700;">${ext || 'FILE'}</span>
             </div>`;

        const $item = $(`
          <div class="position-relative d-inline-block m-1" style="width:80px;vertical-align:top;">
            ${thumb}
            <div class="text-truncate text-center mt-1" style="font-size:.7rem;max-width:80px;">${file.name}</div>
            <button type="button" class="btn btn-danger position-absolute top-0 end-0"
              style="font-size:.8rem;line-height:1;padding:2px 5px;" title="เอาออก">
              <i class="bx bx-x"></i>
            </button>
          </div>`);

        // ตัดไฟล์ออกจาก input จริง ๆ ผ่าน DataTransfer ไม่งั้นมันยังถูกอัปโหลดไปด้วย
        $item.find('button').on('click', function () {
          const dt = new DataTransfer();
          Array.from($input[0].files).forEach((f, i) => {
            if (i !== idx) dt.items.add(f);
          });
          $input[0].files = dt.files;
          renderNewFiles();
        });

        $newPreview.append($item);
      });

      toggleEmptyNote();
    }

    $input.on('change', renderNewFiles);
  })();
</script>
