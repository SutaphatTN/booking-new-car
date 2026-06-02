<div class="modal fade inputFilm" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content border-0 shadow mf-content mf-content--input">

      <div class="modal-header mf-header mf-header--input px-4">
        <div class="d-flex align-items-center gap-3">
          <div class="mf-hd-icon">
            <i class="bx bx-layer fs-5 text-white"></i>
          </div>
          <div>
            <h6 class="mb-0 fw-bold text-white mf-hd-title">เพิ่มสต็อกฟิล์ม</h6>
            <small class="text-white mf-hd-sub">Add Film Stock — กลุ่ม {{ $brandGroup === 'G' ? 'GWM' : 'Mitsubishi / Wuling' }}</small>
          </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body mf-body">
        <form action="{{ route('stock-film.store') }}" method="POST" id="formInputFilm">
          @csrf
          <input type="hidden" name="brand_group" value="{{ $brandGroup }}">

          <div class="mf-section">
            <div class="mf-section-hd">
              <div class="mf-section-icon indigo">
                <i class="bx bx-film"></i>
              </div>
              <span class="mf-section-title">ข้อมูลฟิล์ม</span>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-12">
                  <label for="inp_film_brand_id" class="mf-label form-label">
                    <i class="bx bx-layer"></i> ยี่ห้อฟิล์ม <span class="text-danger">*</span>
                  </label>
                  <select id="inp_film_brand_id" name="film_brand_id" class="form-select" required>
                    <option value="">— เลือกยี่ห้อ —</option>
                    @foreach ($filmBrands as $fb)
                      <option value="{{ $fb->id }}" data-code="{{ $fb->code }}">{{ $fb->name }}</option>
                    @endforeach
                  </select>
                </div>

                <div class="col-md-6">
                  <label for="inp_shade" class="mf-label form-label">
                    <i class="bx bx-sun"></i> ความเข้ม <span class="text-danger">*</span>
                  </label>
                  <select id="inp_shade" name="shade" class="form-select" required>
                    <option value="">— เลือกความเข้ม —</option>
                    <option value="40">40</option>
                    <option value="60">60</option>
                    <option value="80">80</option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label for="inp_withdrawal_date" class="mf-label form-label">
                    <i class="bx bx-calendar"></i> วันที่เบิก <span class="text-danger">*</span>
                  </label>
                  <input id="inp_withdrawal_date" type="date" name="withdrawal_date" class="form-control" required>
                </div>

                <div class="col-md-6">
                  <label for="inp_initial_qty" class="mf-label form-label">
                    <i class="bx bx-ruler"></i> จำนวนเริ่มต้น (ตร.ฟุต) <span class="text-danger">*</span>
                  </label>
                  <input id="inp_initial_qty" type="number" name="initial_qty" class="form-control"
                    min="1" step="0.01" placeholder="เช่น 500" required>
                </div>

                <div class="col-md-6">
                  <label for="inp_part_no" class="mf-label form-label">
                    <i class="bx bx-barcode"></i> Part No.
                  </label>
                  <input id="inp_part_no" type="text" name="part_no" class="form-control"
                    placeholder="ระบุ Part No. (ถ้ามี)">
                </div>

                <div class="col-md-12">
                  <label for="preview_stock_no" class="mf-label form-label">
                    <i class="bx bx-barcode-reader"></i> Stock No. (อัตโนมัติ)
                  </label>
                  <input id="preview_stock_no" type="text" class="form-control form-control-plaintext-mf fw-bold text-primary"
                    readonly placeholder="จะแสดงหลังกรอกข้อมูล">
                  <div id="stock_no_warning" class="text-danger small mt-1 d-none">
                    <i class="bx bx-error-circle"></i> Stock No. นี้มีอยู่แล้วในระบบ
                  </div>
                </div>

              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end gap-2 mt-3">
            <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal"><i class="bx bx-x me-1"></i>ยกเลิก</button>
            <button type="button" class="btn btn-primary btnStoreFilm">
              <i class="bx bx-save me-1"></i> บันทึก
            </button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>
