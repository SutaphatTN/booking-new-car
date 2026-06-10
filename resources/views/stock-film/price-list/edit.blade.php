<div class="modal fade editFilmPrice" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--edit">

      <div class="modal-header mf-header mf-header--edit px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-edit-alt fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">แก้ไข ราคาฟิล์ม</h6>
            <small class="text-white mf-hd-sub">Edit Film Price</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body mf-body">
        <form action="{{ route('film-price-list.update', $price->id) }}" method="POST" id="formEditFilmPrice">
          @csrf
          @method('PUT')

          {{-- Section 1 : รถ & ฟิล์ม --}}
          <div class="mf-section mb-3">
            <div class="mf-section-hd">
              <div class="mf-section-icon indigo">
                <i class="bx bx-car"></i>
              </div>
              <span class="mf-section-title">ข้อมูลรถ & ฟิล์ม</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-6">
                  <label for="ep_model_id" class="mf-label form-label">
                    <i class="bx bx-car"></i> รุ่นรถหลัก <span class="text-danger">*</span>
                  </label>
                  <select id="ep_model_id" name="model_id" class="form-select" required>
                    <option value="">— เลือกรุ่นรถ —</option>
                    @foreach ($models as $m)
                      <option value="{{ $m->id }}" {{ $price->model_id == $m->id ? 'selected' : '' }}>
                        {{ $m->Name_TH }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div class="col-md-6">
                  <label for="ep_film_brand_id" class="mf-label form-label">
                    <i class="bx bx-layer"></i> ยี่ห้อฟิล์ม <span class="text-danger">*</span>
                  </label>
                  <select id="ep_film_brand_id" name="film_brand_id" class="form-select" required>
                    <option value="">— เลือกยี่ห้อ —</option>
                    @foreach ($filmBrands as $fb)
                      <option value="{{ $fb->id }}" {{ $price->film_brand_id == $fb->id ? 'selected' : '' }}>
                        {{ $fb->name }}
                      </option>
                    @endforeach
                  </select>
                </div>

              </div>
            </div>
          </div>

          {{-- Section 2 : รอบคัน + บานหน้า --}}
          <div class="mf-section mb-3">
            <div class="mf-section-hd">
              <div class="mf-section-icon sky">
                <i class="bx bx-expand-alt"></i>
              </div>
              <span class="mf-section-title">รอบคัน + บานหน้า</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-4">
                  <label for="ep_sqft" class="mf-label form-label">
                    <i class="bx bx-ruler"></i> จำนวน ตร.ฟุต <span class="text-danger">*</span>
                  </label>
                  <input id="ep_sqft" type="number" name="sqft" class="form-control text-end"
                    value="{{ $price->sqft }}" min="0" step="0.01" required>
                </div>

                <div class="col-md-4">
                  <label for="ep_price" class="mf-label form-label">
                    <i class="bx bx-receipt ci-sky"></i> ราคาขายรวมภาษี
                  </label>
                  <div class="input-group">
                    <span class="input-group-text">฿</span>
                    <input id="ep_price" type="text" name="price" class="form-control text-end money-input"
                      value="{{ $price->price !== null ? number_format($price->price, 2) : '' }}" autocomplete="off">
                  </div>
                </div>

                <div class="col-md-4">
                  <label for="ep_commission" class="mf-label form-label">
                    <i class="bx bx-badge-check ci-sky"></i> ค่าคอม (SCom)
                  </label>
                  <div class="input-group">
                    <span class="input-group-text">฿</span>
                    <input id="ep_commission" type="text" name="commission" class="form-control text-end money-input"
                      value="{{ $price->commission !== null ? number_format($price->commission, 2) : '' }}" autocomplete="off">
                  </div>
                </div>

              </div>
            </div>
          </div>

          {{-- Section 3 : Sunroof (toggle) --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon amber">
                <i class="bx bx-sun"></i>
              </div>
              <span class="mf-section-title">Sunroof</span>
              <div class="ms-auto form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" role="switch"
                  id="ep_has_sunroof" name="has_sunroof" value="1"
                  {{ $price->has_sunroof ? 'checked' : '' }}>
                <label class="form-check-label mf-label" for="ep_has_sunroof">มีซันรูฟ</label>
              </div>
            </div>
            <div id="ep_sunroof_fields" class="mf-section-body {{ $price->has_sunroof ? '' : 'd-none' }}">
              <div class="row g-3">

                <div class="col-md-4">
                  <label for="ep_sqft_sunroof" class="mf-label form-label">
                    <i class="bx bx-ruler"></i> จำนวน ตร.ฟุต (ซันรูฟ)
                  </label>
                  <input id="ep_sqft_sunroof" type="number" name="sqft_sunroof" class="form-control text-end"
                    value="{{ $price->sqft_sunroof }}" min="0" step="0.01" placeholder="0.00">
                </div>

                <div class="col-md-4">
                  <label for="ep_price_sunroof" class="mf-label form-label">
                    <i class="bx bx-receipt ci-amber"></i> ราคาขาย (ซันรูฟ)
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="ep_price_sunroof" type="text" name="price_sunroof"
                      class="form-control text-end money-input" autocomplete="off"
                      value="{{ $price->price_sunroof !== null ? number_format($price->price_sunroof, 2) : '' }}">
                  </div>
                </div>

                <div class="col-md-4">
                  <label for="ep_commission_sunroof" class="mf-label form-label">
                    <i class="bx bx-badge-check ci-amber"></i> ค่าคอม (ซันรูฟ)
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="ep_commission_sunroof" type="text" name="commission_sunroof"
                      class="form-control text-end money-input" autocomplete="off"
                      value="{{ $price->commission_sunroof !== null ? number_format($price->commission_sunroof, 2) : '' }}">
                  </div>
                </div>

              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2 mt-3">
            <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
              <i class="bx bx-x me-1"></i>ยกเลิก
            </button>
            <button type="button" class="btn btn-primary px-5 btnUpdateFilmPrice">
              <i class="bx bx-save me-1"></i>บันทึก
            </button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>
