<div class="modal fade inputFilmPrice" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--input">

      <div class="modal-header mf-header mf-header--input px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-purchase-tag fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">เพิ่มข้อมูล ราคาฟิล์ม</h6>
            <small class="text-white mf-hd-sub">Add Film Price</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body mf-body">
        <form action="{{ route('film-price-list.store') }}" method="POST" id="formInputFilmPrice">
          @csrf

          {{-- Section : รุ่นรถ & ฟิล์ม --}}
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
                  <label for="fp_model_id" class="mf-label form-label">
                    <i class="bx bx-car"></i> รุ่นรถหลัก <span class="text-danger">*</span>
                  </label>
                  <select id="fp_model_id" name="model_id" class="form-select" required>
                    <option value="">— เลือกรุ่นรถ —</option>
                    @foreach ($models as $m)
                      <option value="{{ $m->id }}">{{ $m->Name_TH }}</option>
                    @endforeach
                  </select>
                </div>

                <div class="col-md-6">
                  <label for="fp_film_brand_id" class="mf-label form-label">
                    <i class="bx bx-layer"></i> ยี่ห้อฟิล์ม <span class="text-danger">*</span>
                  </label>
                  <select id="fp_film_brand_id" name="film_brand_id" class="form-select" required>
                    <option value="">— เลือกยี่ห้อ —</option>
                    @foreach ($filmBrands as $fb)
                      <option value="{{ $fb->id }}">{{ $fb->name }}</option>
                    @endforeach
                  </select>
                </div>

              </div>
            </div>
          </div>

          {{-- Section : ตำแหน่ง & ความเข้ม --}}
          <div class="mf-section mb-3">
            <div class="mf-section-hd">
              <div class="mf-section-icon sky">
                <i class="bx bx-sun"></i>
              </div>
              <span class="mf-section-title">ตำแหน่ง & ความเข้ม</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-4">
                  <label for="fp_position" class="mf-label form-label">
                    <i class="bx bx-map-pin"></i> ตำแหน่ง <span class="text-danger">*</span>
                  </label>
                  <select id="fp_position" name="position" class="form-select" required>
                    <option value="">— เลือกตำแหน่ง —</option>
                    <option value="รอบคัน">รอบคัน</option>
                    <option value="sunroof">Sunroof</option>
                  </select>
                </div>

                {{-- รอบคัน shades --}}
                <div id="fp_body_shades" class="col-md-8 d-none">
                  <div class="row g-3">
                    <div class="col-6">
                      <label for="fp_front_shade" class="mf-label form-label">
                        <i class="bx bx-sun"></i> ความเข้มบานหน้า
                        <small class="text-muted">(ไม่บังคับ)</small>
                      </label>
                      <select id="fp_front_shade" name="front_shade" class="form-select">
                        <option value="">— ไม่ระบุ —</option>
                        <option value="40">40</option>
                        <option value="60">60</option>
                        <option value="80">80</option>
                      </select>
                    </div>
                    <div class="col-6">
                      <label for="fp_body_shade" class="mf-label form-label">
                        <i class="bx bx-sun"></i> ความเข้มรอบคัน <span class="text-danger">*</span>
                      </label>
                      <select id="fp_body_shade" name="body_shade" class="form-select">
                        <option value="">— เลือก —</option>
                        <option value="40">40</option>
                        <option value="60">60</option>
                        <option value="80">80</option>
                      </select>
                    </div>
                  </div>
                </div>

                {{-- Sunroof shade --}}
                <div id="fp_sunroof_shades" class="col-md-4 d-none">
                  <label for="fp_sunroof_shade" class="mf-label form-label">
                    <i class="bx bx-sun"></i> ความเข้ม Sunroof <span class="text-danger">*</span>
                  </label>
                  <select id="fp_sunroof_shade" name="sunroof_shade" class="form-select">
                    <option value="">— เลือก —</option>
                    <option value="40">40</option>
                    <option value="60">60</option>
                    <option value="80">80</option>
                  </select>
                </div>

              </div>
            </div>
          </div>

          {{-- Section : ราคา --}}
          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon amber">
                <i class="bx bx-money"></i>
              </div>
              <span class="mf-section-title">ข้อมูลราคา</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-4">
                  <label for="fp_sqft" class="mf-label form-label">
                    <i class="bx bx-ruler ci-amber"></i> จำนวน ตร.ฟุต <span class="text-danger">*</span>
                  </label>
                  <input id="fp_sqft" type="text" name="sqft" class="form-control text-end"
                    min="0" step="0.01" placeholder="0.00" required>
                </div>

                <div class="col-md-4">
                  <label for="fp_price" class="mf-label form-label">
                    <i class="bx bx-receipt ci-amber"></i> ราคาขายรวมภาษี
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="fp_price" type="text" name="price" class="form-control text-end money-input"
                      autocomplete="off" placeholder="0.00">
                  </div>
                </div>

                <div class="col-md-4">
                  <label for="fp_commission" class="mf-label form-label">
                    <i class="bx bx-badge-check ci-amber"></i> ค่าคอม (SCom)
                  </label>
                  <div class="input-group">
                    <span class="input-group-text ig-amber">฿</span>
                    <input id="fp_commission" type="text" name="commission" class="form-control text-end money-input"
                      autocomplete="off" placeholder="0.00">
                  </div>
                </div>

              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2 mt-3">
            <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal">
              <i class="bx bx-x me-1"></i>ยกเลิก
            </button>
            <button type="button" class="btn btn-primary px-5 btnStoreFilmPrice">
              <i class="bx bx-save me-1"></i>บันทึก
            </button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>
