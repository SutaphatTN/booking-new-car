<div class="modal fade inputFilmPrice" tabindex="-1" role="dialog" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl" role="document">
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
        <form id="formInputFilmPrice">
          @csrf

          {{-- Section 1 : รุ่นรถ & ตร.ฟุต --}}
          <div class="mf-section mb-3">
            <div class="mf-section-hd">
              <div class="mf-section-icon indigo">
                <i class="bx bx-car"></i>
              </div>
              <span class="mf-section-title">ข้อมูลรถ</span>
              <div class="ms-auto form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" role="switch"
                  id="fp_has_sunroof" name="has_sunroof" value="1">
                <label class="form-check-label mf-label" for="fp_has_sunroof">
                  <i class="bx bx-sun ci-amber me-1"></i>มีซันรูฟ
                </label>
              </div>
            </div>
            <div class="mf-section-body">
              <div class="row g-3">

                <div class="col-md-4">
                  <label for="fp_model_id" class="mf-label form-label">
                    <i class="bx bx-car"></i> รุ่นรถหลัก <span class="text-danger">*</span>
                  </label>
                  <select id="fp_model_id" name="model_id" class="form-select">
                    <option value="">— เลือกรุ่นรถ —</option>
                    @foreach ($models as $m)
                      <option value="{{ $m->id }}">{{ $m->Name_TH }}</option>
                    @endforeach
                  </select>
                </div>

                <div class="col-md-4">
                  <label for="fp_sqft" class="mf-label form-label">
                    <i class="bx bx-ruler ci-sky"></i> จำนวน ตร.ฟุต (รอบคัน+บานหน้า) <span class="text-danger">*</span>
                  </label>
                  <input id="fp_sqft" type="number" name="sqft" class="form-control text-end"
                    min="0" step="0.01" placeholder="0.00">
                </div>

                <div id="fp_sunroof_fields" class="col-md-4 d-none">
                  <label for="fp_sqft_sunroof" class="mf-label form-label">
                    <i class="bx bx-ruler ci-amber"></i> จำนวน ตร.ฟุต ซันรูฟ
                  </label>
                  <input id="fp_sqft_sunroof" type="number" name="sqft_sunroof" class="form-control text-end"
                    min="0" step="0.01" placeholder="0.00">
                </div>

              </div>
            </div>
          </div>

          {{-- Section 2 : ราคาตามยี่ห้อฟิล์ม --}}
          <div class="mf-section mb-3">
            <div class="mf-section-hd">
              <div class="mf-section-icon sky">
                <i class="bx bx-layer"></i>
              </div>
              <span class="mf-section-title">ราคาตามยี่ห้อฟิล์ม</span>
              <button type="button" class="btn btn-sm btn-outline-primary ms-auto btnAddBrandRow">
                <i class="bx bx-plus me-1"></i> เพิ่มยี่ห้อ
              </button>
            </div>
            <div class="mf-section-body p-0">
              <div class="table-responsive">
                <table class="table table-bordered mb-0" id="fpBrandTable">
                  <thead class="table-light">
                    <tr>
                      <th style="min-width:180px">ยี่ห้อฟิล์ม</th>
                      <th class="text-end" style="min-width:160px">ราคาขายรวมภาษี (฿)</th>
                      <th class="text-end" style="min-width:140px">ค่าคอม SCom (฿)</th>
                      <th class="col-sunroof d-none text-end" style="min-width:150px">ราคาซันรูฟ (฿)</th>
                      <th class="col-sunroof d-none text-end" style="min-width:140px">ค่าคอมซันรูฟ (฿)</th>
                      <th style="width:50px"></th>
                    </tr>
                  </thead>
                  <tbody id="fpBrandRows">
                    <tr id="fpNoBrandMsg">
                      <td colspan="6" class="text-center text-muted py-3">
                        <i class="bx bx-info-circle me-1"></i> กดปุ่ม "เพิ่มยี่ห้อ" เพื่อเพิ่มข้อมูล
                      </td>
                    </tr>
                  </tbody>
                </table>
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

<script>
  window.fpFilmBrands = @json($filmBrands->map(fn($fb) => ['id' => $fb->id, 'name' => $fb->name]));
</script>
