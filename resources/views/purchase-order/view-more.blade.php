<div class="modal fade viewPurchase" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <h4 class="modal-title mb-2" id="viewPurchaseLabel">สรุปค่าใช้จ่าย</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">

        @if (!empty($saleCar->withdraw_attachment_url))
          <div class="mb-4">
            <h6 class="fw-semibold mb-2">หลักฐานการคืนเงินถอนจอง</h6>
            <div class="row g-2">
              @foreach ($saleCar->withdraw_attachment_url as $url)
                @php $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION)); @endphp
                <div class="col-md-3 col-sm-6 text-center">
                  @if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                    <a href="{{ $url }}" target="_blank">
                      <img src="{{ $url }}" class="img-fluid rounded border" style="max-height:160px;object-fit:cover;width:100%;">
                    </a>
                  @else
                    <a href="{{ $url }}" target="_blank" class="btn btn-outline-secondary w-100">
                      <i class="bx bx-file me-1"></i> ดูไฟล์
                    </a>
                  @endif
                </div>
              @endforeach
            </div>
          </div>
        @endif

      </div>
    </div>
  </div>
</div>
